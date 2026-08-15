// ============================================================
// MQTT Bridge Service — Monitoring Pasien IoT
// Subscribe data sensor dari ESP32 via Mosquitto → Simpan MySQL
// Setiap service memiliki database sendiri (db_gps, db_mpu)
// ============================================================

require('dotenv').config();
const mqtt   = require('mqtt');
const mysql2 = require('mysql2/promise');
const http   = require('http');

// -------------------------------------------------------
// Warna untuk log terminal
// -------------------------------------------------------
const C = {
  reset:  '\x1b[0m',
  green:  '\x1b[32m',
  red:    '\x1b[31m',
  yellow: '\x1b[33m',
  cyan:   '\x1b[36m',
  blue:   '\x1b[34m',
  gray:   '\x1b[90m',
};

function log(tag, color, msg) {
  const time = new Date().toLocaleTimeString('id-ID');
  console.log(`${C.gray}[${time}]${C.reset} ${color}[${tag}]${C.reset} ${msg}`);
}

// -------------------------------------------------------
// Koneksi MySQL — db_gps (untuk Bn220/GPS-Service)
// -------------------------------------------------------
const poolGps = mysql2.createPool({
  host:               process.env.DB_HOST      || '127.0.0.1',
  port:               parseInt(process.env.DB_PORT || '3306'),
  database:           process.env.DB_GPS_NAME  || 'db_gps',
  user:               process.env.DB_USER      || 'root',
  password:           process.env.DB_PASS      || '',
  waitForConnections: true,
  connectionLimit:    10,
  queueLimit:         0,
});

// -------------------------------------------------------
// Koneksi MySQL — db_mpu (untuk Mpu-Service)
// -------------------------------------------------------
const poolMpu = mysql2.createPool({
  host:               process.env.DB_HOST      || '127.0.0.1',
  port:               parseInt(process.env.DB_PORT || '3306'),
  database:           process.env.DB_MPU_NAME  || 'db_mpu',
  user:               process.env.DB_USER      || 'root',
  password:           process.env.DB_PASS      || '',
  waitForConnections: true,
  connectionLimit:    10,
  queueLimit:         0,
});

async function testDbConnections() {
  let allOk = true;

  // Test koneksi db_gps
  try {
    const conn = await poolGps.getConnection();
    log('DB-GPS', C.green, `Terhubung ke db_gps (${process.env.DB_HOST}:${process.env.DB_PORT}/${process.env.DB_GPS_NAME || 'db_gps'})`);
    conn.release();
  } catch (err) {
    log('DB-GPS', C.red, `Gagal terhubung ke db_gps: ${err.message}`);
    allOk = false;
  }

  // Test koneksi db_mpu
  try {
    const conn = await poolMpu.getConnection();
    log('DB-MPU', C.green, `Terhubung ke db_mpu (${process.env.DB_HOST}:${process.env.DB_PORT}/${process.env.DB_MPU_NAME || 'db_mpu'})`);
    conn.release();
  } catch (err) {
    log('DB-MPU', C.red, `Gagal terhubung ke db_mpu: ${err.message}`);
    allOk = false;
  }

  return allOk;
}


// -------------------------------------------------------
// Handler per topic MQTT
// -------------------------------------------------------
async function handleGps(payload) {
  const d = JSON.parse(payload);
  const { latitude, longitude, satelit, hdop } = d;
  const mapsUrl = `https://www.google.com/maps?q=${latitude},${longitude}`;

  // Simpan ke db_gps → tabel gps_data (milik Bn220/GPS-Service)
  const [result] = await poolGps.execute(
    `INSERT INTO gps_data (latitude, longitude, satelit, hdop, maps_url, timestamp)
     VALUES (?, ?, ?, ?, ?, NOW())`,
    [latitude, longitude, satelit ?? null, hdop ?? null, mapsUrl]
  );
  log('GPS', C.green, `Tersimpan ke db_gps ✓ | lat=${latitude} lng=${longitude} sat=${satelit} hdop=${hdop} | id=${result.insertId}`);
}


async function handleMpu(payload) {
  const d = JSON.parse(payload);
  const { accX, accY, accZ, jatuh, bergerak } = d;
  const isJatuh = (jatuh !== undefined) ? jatuh : bergerak;
  const gerakan = isJatuh ? 'JATUH' : 'AMAN';

  // Simpan ke db_mpu → tabel mpu_data (milik Mpu-Service)
  const [result] = await poolMpu.execute(
    `INSERT INTO mpu_data (acc_x, acc_y, acc_z, gerakan, timestamp)
     VALUES (?, ?, ?, ?, NOW())`,
    [accX ?? null, accY ?? null, accZ ?? null, gerakan]
  );
  log('MPU', C.blue, `Tersimpan ke db_mpu ✓ | accX=${accX?.toFixed(4)} accY=${accY?.toFixed(4)} accZ=${accZ?.toFixed(4)} | gerakan=${gerakan} | id=${result.insertId}`);

  // JIKA PASIEN JATUH: Kirim notifikasi secara otomatis 24/7 di latar belakang (tanpa butuh browser)
  if (gerakan === 'JATUH') {
    let lat = null;
    let lng = null;
    let mapsUrl = '';

    try {
      // Ambil data lokasi GPS terakhir dari db_gps
      const [rows] = await poolGps.execute(
        `SELECT latitude, longitude, maps_url FROM gps_data ORDER BY timestamp DESC LIMIT 1`
      );
      if (rows && rows.length > 0) {
        lat = parseFloat(rows[0].latitude);
        lng = parseFloat(rows[0].longitude);
        mapsUrl = rows[0].maps_url || '';
      }
    } catch (err) {
      log('MQTT', C.red, `Gagal mengambil GPS terakhir untuk notifikasi: ${err.message}`);
    }

    const totalAcc = Math.abs(accX ?? 0) + Math.abs(accY ?? 0) + Math.abs(accZ ?? 0);

    // Kirim HTTP POST ke API Gateway -> NotifTele-Service secara internal di dalam Docker network
    try {
      const gatewayUrl = process.env.API_GATEWAY_URL || 'http://api-gateway:8080';
      const response = await fetch(`${gatewayUrl}/api/notif/fall`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ lat, lng, mapsUrl, totalAcc }),
      });
      const resData = await response.json();
      if (resData.success) {
        log('NOTIF', C.green, `✅ Notifikasi jatuh sukses terkirim ke Telegram via Microservice!`);
      } else {
        log('NOTIF', C.yellow, `⚠️ Notifikasi Telegram dilewati/gagal: ${resData.message}`);
      }
    } catch (err) {
      log('NOTIF', C.red, `❌ Gagal menghubungi NotifTele-Service: ${err.message}`);
    }
  }
}

// -------------------------------------------------------
// Koneksi MQTT
// -------------------------------------------------------
const MQTT_URL = `mqtt://${process.env.MQTT_HOST || '127.0.0.1'}:${process.env.MQTT_PORT || 1883}`;
const CLIENT_ID = process.env.MQTT_CLIENT_ID || 'mqtt-bridge-pasien';

const TOPICS = [
  'pasien/gps/ta_pbl',
  'pasien/mpu/ta_pbl',
];

let messageCount = { gps: 0, mpu: 0 };

function startMqtt() {
  log('MQTT', C.yellow, `Menghubungkan ke broker: ${MQTT_URL} (clientId: ${CLIENT_ID})`);

  const client = mqtt.connect(MQTT_URL, {
    clientId:     CLIENT_ID,
    clean:        true,
    reconnectPeriod: 3000, // auto reconnect setiap 3 detik jika terputus
    connectTimeout: 10000,
  });

  client.on('connect', () => {
    log('MQTT', C.green, `Terhubung ke Mosquitto broker! ✓`);
    client.subscribe(TOPICS, { qos: 1 }, (err) => {
      if (err) {
        log('MQTT', C.red, `Gagal subscribe: ${err.message}`);
      } else {
        log('MQTT', C.green, `Subscribe berhasil ke topics: ${TOPICS.join(', ')}`);
      }
    });
  });

  client.on('message', async (topic, message) => {
    const payload = message.toString();
    log('MQTT', C.gray, `← Pesan masuk [${topic}]: ${payload}`);

    try {
      if (topic === 'pasien/gps/ta_pbl') {
        await handleGps(payload);
        messageCount.gps++;

      } else if (topic === 'pasien/mpu/ta_pbl') {
        await handleMpu(payload);
        messageCount.mpu++;
      }
    } catch (err) {
      log('ERROR', C.red, `Gagal proses topic [${topic}]: ${err.message}`);
      log('ERROR', C.red, `Payload: ${payload}`);
    }
  });

  client.on('reconnect', () => {
    log('MQTT', C.yellow, `Mencoba reconnect ke broker...`);
  });

  client.on('offline', () => {
    log('MQTT', C.red, `Broker MQTT tidak dapat dijangkau. Menunggu reconnect...`);
  });

  client.on('error', (err) => {
    log('MQTT', C.red, `Error: ${err.message}`);
  });

  return client;
}

// -------------------------------------------------------
// Health Check HTTP Server (opsional)
// -------------------------------------------------------
function startHealthServer() {
  const port = parseInt(process.env.BRIDGE_PORT || '3001');
  const server = http.createServer((req, res) => {
    if (req.url === '/health') {
      res.writeHead(200, { 'Content-Type': 'application/json' });
      res.end(JSON.stringify({
        status: 'ok',
        uptime: Math.floor(process.uptime()),
        messages: messageCount,
        databases: {
          gps: process.env.DB_GPS_NAME || 'db_gps',
          mpu: process.env.DB_MPU_NAME || 'db_mpu',
        },
        timestamp: new Date().toISOString(),
      }));
    } else {
      res.writeHead(404);
      res.end('Not Found');
    }
  });
  server.listen(port, () => {
    log('HTTP', C.gray, `Health check tersedia di: http://localhost:${port}/health`);
  });
}

// -------------------------------------------------------
// Main
// -------------------------------------------------------
async function main() {
  console.log('');
  console.log(`${C.cyan}╔════════════════════════════════════════════════╗${C.reset}`);
  console.log(`${C.cyan}║     MQTT BRIDGE — Monitoring Pasien IoT        ║${C.reset}`);
  console.log(`${C.cyan}║     GPS → db_gps  |  MPU → db_mpu              ║${C.reset}`);
  console.log(`${C.cyan}╚════════════════════════════════════════════════╝${C.reset}`);
  console.log('');

  // Cek koneksi ke semua database
  const dbOk = await testDbConnections();
  if (!dbOk) {
    log('FATAL', C.red, 'Tidak bisa koneksi ke salah satu database. Pastikan MySQL running dan .env benar.');
    process.exit(1);
  }

  // Mulai MQTT subscriber
  startMqtt();

  // Mulai health check server
  startHealthServer();

  // Log statistik setiap 30 detik
  setInterval(() => {
    log('STATS', C.yellow,
      `Total pesan diterima → GPS: ${messageCount.gps} | MPU: ${messageCount.mpu}`
    );
  }, 30000);
}

main();
