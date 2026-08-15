# 📡 MQTT Bridge — Monitoring Pasien IoT

Service Node.js yang bertugas **menerima data sensor dari ESP32 via MQTT** dan **menyimpannya ke database MySQL**.

## Cara Kerja

```
ESP32 → publish → Mosquitto Broker (:1883) → subscribe → MQTT-Bridge → INSERT → MySQL
```

---

## ✅ Prasyarat

- [x] **Node.js** v18+ terinstall → cek: `node --version`
- [x] **MySQL** running dengan database `TA-Pasien`
- [x] **Mosquitto** MQTT Broker running di port 1883

---

## 🚀 Cara Menjalankan

### 1. Install dependencies
```bash
cd MQTT-Bridge
npm install
```

### 2. Sesuaikan konfigurasi `.env`
```env
MQTT_HOST=127.0.0.1       # IP komputer yang running Mosquitto
MQTT_PORT=1883
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=TA-Pasien
DB_USER=root
DB_PASS=                  # kosong jika tidak ada password
```

### 3. Jalankan service
```bash
npm start
```

Output yang benar:
```
╔════════════════════════════════════════╗
║     MQTT BRIDGE — Monitoring Pasien    ║
╚════════════════════════════════════════╝

[10:00:01] [DB]   Terhubung ke MySQL (127.0.0.1:3306/TA-Pasien)
[10:00:01] [MQTT] Menghubungkan ke broker: mqtt://127.0.0.1:1883
[10:00:01] [MQTT] Terhubung ke Mosquitto broker! ✓
[10:00:01] [MQTT] Subscribe berhasil ke topics: pasien/gps, pasien/suhu, pasien/mpu
[10:00:01] [HTTP] Health check tersedia di: http://localhost:3001/health
```

---

## Topics MQTT

| Topic | Payload JSON | Deskripsi |
|---|---|---|
| `pasien/gps` | `{"latitude":x,"longitude":y,"satelit":z,"hdop":w}` | Data GPS BN-220 |
| `pasien/suhu` | `{"suhuDahi":36.5}` | Suhu tubuh MLX90614 |
| `pasien/mpu` | `{"accX":0,"accY":0,"accZ":0,"bergerak":false}` | Data MPU6050 |

---

## Health Check
Setelah service berjalan, buka browser: `http://localhost:3001/health`
```json
{
  "status": "ok",
  "uptime": 120,
  "messages": { "gps": 5, "suhu": 10, "mpu": 10 },
  "timestamp": "..."
}
```
