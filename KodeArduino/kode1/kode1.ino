#include <Wire.h>
#include <TinyGPS++.h>
#include <WiFi.h>
#include <PubSubClient.h>  // MQTT Client library (by Nick O'Leary)
#include <WiFiManager.h>   // Library WiFiManager oleh tzapu
#include <Preferences.h>   // Library internal ESP32 untuk NVS

// ======================================
// KONFIGURASI MQTT & WiFi (Dinamis via WiFiManager)
// ======================================
Preferences preferences;
String mqttBrokerIP;  // IP komputer yang menjalankan Mosquitto

// MQTT Topics
#define TOPIC_GPS   "pasien/gps/ta_pbl"
#define TOPIC_MPU   "pasien/mpu/ta_pbl"

// MQTT Client
WiFiClient   espWifiClient;
PubSubClient mqtt(espWifiClient);


// ======================================
// MULTITASKING & DATA SHARING (FreeRTOS)
// ======================================
struct SensorData {
  double gpsLat;
  double gpsLng;
  int gpsSat;
  double gpsHdop;
  bool hasNewGps;

  float mpuX;
  float mpuY;
  float mpuZ;
  bool mpuJatuh;
  bool hasNewMpu;
};

SensorData sharedData = {0.0, 0.0, 0, 0.0, false, 0.0f, 0.0f, 0.0f, false, false};
SemaphoreHandle_t dataMutex;

void mqttTask(void * parameter);


// ======================================
// GPS BN-220
// ======================================
TinyGPSPlus gps;
HardwareSerial GPS_Serial(2);

unsigned long gpsLastCharTime   = 0;   // kapan terakhir ada karakter NMEA masuk
unsigned long gpsCharsSeenBoot  = 0;   // total karakter sejak boot (untuk cek wiring)
bool gpsModuleAlive             = false;


// ======================================
// MPU6050 — Direct I2C (tanpa library)
// ======================================
#define MPU_ADDR 0x68

float offX = 0, offY = 0, offZ = 0;  // offset kalibrasi (vektor gravitasi penuh)

// --- Parameter deteksi gerak (SESUAIKAN sesuai kebutuhan) ---
const float MOTION_THRESHOLD      = 2.50f;  // Ambang batas perubahan percepatan (g) untuk dianggap bergerak kasar
const int   MOTION_CONFIRM_COUNT  = 3;       // Jumlah sampel berturut-turut di atas ambang batas (3 * 100ms = 300ms)
const unsigned long MPU_SAMPLE_INTERVAL_MS = 100; // sampling MPU tiap 100ms (bukan cuma tiap 2 detik)

int   aboveThresholdStreak = 0;
bool  fallDetectedInWindow = false;
float lastAccX = 0, lastAccY = 0, lastAccZ = 0;
unsigned long mpuSampleTimer = 0;

void mpuWakeUp() {
  Wire.beginTransmission(MPU_ADDR);
  Wire.write(0x6B);   // register PWR_MGMT_1
  Wire.write(0x00);   // wake up (hapus sleep bit)
  Wire.endTransmission(true);
  delay(100);
}

bool mpuDetected() {
  Wire.beginTransmission(MPU_ADDR);
  return Wire.endTransmission() == 0;
}

void mpuReadAccel(float &ax, float &ay, float &az) {
  Wire.beginTransmission(MPU_ADDR);
  Wire.write(0x3B);   // ACCEL_XOUT_H (register pertama dari 6 byte)
  Wire.endTransmission(false);
  Wire.requestFrom(MPU_ADDR, 6, 1);

  int16_t rawX = (Wire.read() << 8) | Wire.read();
  int16_t rawY = (Wire.read() << 8) | Wire.read();
  int16_t rawZ = (Wire.read() << 8) | Wire.read();

  // Skala default ±2g → sensitivitas 16384 LSB/g
  ax = rawX / 16384.0f - offX;
  ay = rawY / 16384.0f - offY;
  az = rawZ / 16384.0f - offZ;
}

// Kalibrasi berbasis VEKTOR GRAVITASI PENUH (bukan hanya sumbu Z).
// Ini penting kalau sensor tidak terpasang benar-benar rata/horizontal —
// versi lama cuma mengompensasi 1g di sumbu Z sehingga sisa gravitasi
// "bocor" ke X/Y dan bikin status gerak salah terus saat diam.
void mpuCalibrate() {
  Serial.println("Kalibrasi MPU6050... (JANGAN disentuh/digerakkan)");
  const int N = 200;
  long sumX = 0, sumY = 0, sumZ = 0;

  for (int i = 0; i < N; i++) {
    Wire.beginTransmission(MPU_ADDR);
    Wire.write(0x3B);
    Wire.endTransmission(false);
    Wire.requestFrom(MPU_ADDR, 6, 1);

    int16_t rx = (Wire.read() << 8) | Wire.read();
    int16_t ry = (Wire.read() << 8) | Wire.read();
    int16_t rz = (Wire.read() << 8) | Wire.read();

    sumX += rx;
    sumY += ry;
    sumZ += rz;
    delay(5);
  }

  // Offset = rata-rata vektor gravitasi apa adanya (tanpa asumsi sumbu mana yang "atas")
  offX = (sumX / (float)N) / 16384.0f;
  offY = (sumY / (float)N) / 16384.0f;
  offZ = (sumZ / (float)N) / 16384.0f;

  Serial.println("Kalibrasi Selesai");
  Serial.print("  offX="); Serial.print(offX, 4);
  Serial.print("  offY="); Serial.print(offY, 4);
  Serial.print("  offZ="); Serial.println(offZ, 4);
}

// Dipanggil sesering mungkin (setiap MPU_SAMPLE_INTERVAL_MS) di loop().
// Menandai motionDetectedInWindow = true HANYA jika percepatan di luar
// gravitasi melewati ambang batas selama beberapa sample BERTURUT-TURUT,
// sehingga getaran halus/noise sesaat tidak dianggap "bergerak", dan
// hanya guncangan yang benar-benar kasar/berkelanjutan yang terdeteksi.
void mpuSampleMotion() {
  if (!mpuDetected()) return;

  float ax, ay, az;
  mpuReadAccel(ax, ay, az);

  // Mencegah spike (lonjakan nilai) pada pembacaan pertama saat booting
  static bool firstSample = true;
  if (firstSample) {
    lastAccX = ax;
    lastAccY = ay;
    lastAccZ = az;
    firstSample = false;
    return;
  }

  // Menggunakan selisih nilai percepatan sekarang dan sebelumnya (delta).
  // Ini mencegah deteksi gerak palsu akibat sensor diputar/miring saat diam.
  float diffX = ax - lastAccX;
  float diffY = ay - lastAccY;
  float diffZ = az - lastAccZ;

  // Simpan pembacaan untuk iterasi berikutnya
  lastAccX = ax; 
  lastAccY = ay; 
  lastAccZ = az;

  // Magnitude dari selisih percepatan
  float deltaMag = sqrtf(diffX * diffX + diffY * diffY + diffZ * diffZ);

  if (deltaMag > MOTION_THRESHOLD) {
    aboveThresholdStreak++;
    if (aboveThresholdStreak >= MOTION_CONFIRM_COUNT) {
      fallDetectedInWindow = true;
    }
  } else {
    aboveThresholdStreak = 0;
  }
}

// ======================================
// TIMER
// ======================================
unsigned long timer = 0;

// ======================================
// FUNGSI PUBLISH DATA KE MQTT BROKER
// Menggunakan PubSubClient — jauh lebih cepat dari HTTP POST.
// QoS 0 (at-most-once) untuk performa terbaik pada sensor data.
// ======================================
void publishGpsData(double lat, double lng, int satelit, double hdop) {
  if (!mqtt.connected()) {
    Serial.println("[GPS] BATAL: MQTT tidak terhubung.");
    return;
  }

  String payload = "{";
  payload += "\"latitude\":"  + String(lat, 6)  + ",";
  payload += "\"longitude\":" + String(lng, 6)  + ",";
  payload += "\"satelit\":"   + String(satelit) + ",";
  payload += "\"hdop\":"      + String(hdop, 2);
  payload += "}";

  bool ok = mqtt.publish(TOPIC_GPS, payload.c_str());
  Serial.println(ok ? "[GPS] MQTT Publish OK → " TOPIC_GPS
                    : "[GPS] MQTT Publish GAGAL");
}


void publishMpuData(float accX, float accY, float accZ, bool jatuh) {
  if (!mqtt.connected()) {
    Serial.println("[MPU] BATAL: MQTT tidak terhubung.");
    return;
  }

  String payload = "{";
  payload += "\"accX\":"    + String(accX, 4)  + ",";
  payload += "\"accY\":"    + String(accY, 4)  + ",";
  payload += "\"accZ\":"    + String(accZ, 4)  + ",";
  payload += "\"jatuh\":" + String(jatuh ? "true" : "false");
  payload += "}";

  bool ok = mqtt.publish(TOPIC_MPU, payload.c_str());
  Serial.println(ok ? ("[MPU] MQTT Publish OK → " TOPIC_MPU " | jatuh=" + String(jatuh ? "YA" : "TIDAK"))
                    : "[MPU] MQTT Publish GAGAL");
}

// ======================================
// SETUP
// ======================================
void setup() {
  Serial.begin(115200);

  // GPS BN-220 pada UART2 (pin RX=16, TX=17)
  // Catatan: pastikan kabel TX modul GPS -> pin 16 (RX ESP32), dan
  // RX modul GPS -> pin 17 (TX ESP32). Kabel tertukar = tidak ada data sama sekali.
  GPS_Serial.begin(9600, SERIAL_8N1, 16, 17);

  // I2C untuk MLX90614 + MPU6050
  Wire.begin(21, 22);

  Serial.println();
  Serial.println("========================================");
  Serial.println("      MONITORING PASIEN IoT");
  Serial.println("========================================");

  // ----- Connect WiFi menggunakan WiFiManager -----
  preferences.begin("iot-pasien", false);
  // Baca IP Mosquitto broker tersimpan, default ke 23.20.74.33
  mqttBrokerIP = preferences.getString("mqttBroker", "23.20.74.33");

  WiFiManager wm;

  // -------------------------------------------------------
  // TOMBOL RESET KONFIGURASI (GPIO 0 = tombol BOOT ESP32)
  // Tunggu 3 detik saat booting: Tahan tombol BOOT untuk reset
  // -------------------------------------------------------
  #define RESET_BTN_PIN 0
  pinMode(RESET_BTN_PIN, INPUT_PULLUP);
  
  Serial.println("\n[System] Menunggu 3 detik... Tahan tombol BOOT sekarang jika ingin RESET konfigurasi.");
  bool triggerReset = false;
  for (int i = 3; i > 0; i--) {
    Serial.print(String(i) + "... ");
    if (digitalRead(RESET_BTN_PIN) == LOW) {
      triggerReset = true;
    }
    delay(1000);
  }
  Serial.println("Mulai.");

  if (triggerReset) {
    Serial.println(">>> RESET TERDETEKSI: Menghapus konfigurasi WiFi & Broker...");
    wm.resetSettings();
    preferences.remove("mqttBroker");
    mqttBrokerIP = "";
    Serial.println(">>> Konfigurasi dihapus. Membuka portal...");
  }

  // Siapkan kolom input custom untuk MQTT Broker IP di portal web
  char mqtt_broker_buf[64];
  mqttBrokerIP.toCharArray(mqtt_broker_buf, 64);
  WiFiManagerParameter mqtt_broker_param("mqtt", "IP Mosquitto Broker (contoh: 192.168.1.10)", mqtt_broker_buf, 64);
  wm.addParameter(&mqtt_broker_param);

  // Batas waktu koneksi ke WiFi: 30 detik. Jika gagal, portal tetap terbuka.
  wm.setConnectTimeout(30);

  // Batas waktu portal: 0 = tidak ada batas (portal terbuka sampai berhasil konek)
  wm.setConfigPortalTimeout(0);

  Serial.println("Menghubungkan ke WiFi via WiFiManager...");
  Serial.println("Jika gagal, sambungkan ke WiFi 'ESP32_Config_Portal' dan buka 192.168.4.1");

  wm.autoConnect("ESP32_Config_Portal");

  if (WiFi.status() == WL_CONNECTED) {
    Serial.println();
    Serial.println("WiFi Terhubung!");
    Serial.print("IP Address: ");
    Serial.println(WiFi.localIP());
  } else {
    Serial.println();
    Serial.println("WiFi Tidak Terhubung. Sensor tetap berjalan tanpa pengiriman data.");
  }

  // Ambil IP Broker baru dari portal jika diubah
  String newBroker = String(mqtt_broker_param.getValue());
  newBroker.trim();
  if (newBroker.length() > 0 && newBroker != mqttBrokerIP) {
    mqttBrokerIP = newBroker;
    preferences.putString("mqttBroker", mqttBrokerIP);
    Serial.print("Menyimpan MQTT Broker IP baru: ");
    Serial.println(mqttBrokerIP);
  }
  preferences.end();

  // Inisialisasi MQTT Client
  mqtt.setServer(mqttBrokerIP.c_str(), 1883);
  mqtt.setKeepAlive(60);
  mqtt.setSocketTimeout(5);
  Serial.print("MQTT Broker diset ke: ");
  Serial.print(mqttBrokerIP);
  Serial.println(":1883");


  // ----- MPU6050 direct I2C -----
  int mpuRetry = 0;
  while (!mpuDetected() && mpuRetry < 10) {
    Serial.println("MPU6050 Tidak Terdeteksi...");
    delay(1000);
    mpuRetry++;
  }

  if (mpuDetected()) {
    mpuWakeUp();
    Serial.println("MPU6050 OK");
    mpuCalibrate();
  } else {
    Serial.println("MPU6050 Gagal! Lanjut tanpa MPU...");
  }

  Serial.println();
  Serial.println("Catatan GPS: modul butuh pandangan langsung ke langit.");
  Serial.println("Di dalam rumah/beton biasanya sinyal sangat lemah/tidak ada.");
  Serial.println("Tunggu 30s-2menit di luar ruangan/dekat jendela untuk fix pertama.");
  Serial.println();

  // Initialize Mutex
  dataMutex = xSemaphoreCreateMutex();

  // Jalankan background MQTT task di Core 0 (prioritas 1)
  xTaskCreatePinnedToCore(
    mqttTask,          // Fungsi task
    "MQTTTask",        // Nama task
    8192,              // Stack size
    NULL,              // Parameter input
    1,                 // Prioritas
    NULL,              // Task handle
    0                  // Core 0
  );

  delay(500);
}

// ======================================
// LOOP
// ======================================
void loop() {
  // ---- Baca GPS terus-menerus (non-blocking) ----
  while (GPS_Serial.available()) {
    char c = GPS_Serial.read();
    gps.encode(c);
    gpsCharsSeenBoot++;
    gpsLastCharTime = millis();
  }
  gpsModuleAlive = (millis() - gpsLastCharTime) < 3000; // ada data NMEA masuk dlm 3 detik terakhir

  // ---- Sampling MPU sesering mungkin (independen dari timer laporan 2 detik) ----
  if (millis() - mpuSampleTimer >= MPU_SAMPLE_INTERVAL_MS) {
    mpuSampleTimer = millis();
    mpuSampleMotion();
  }

  if (millis() - timer > 2000) {
    timer = millis();

    Serial.println();
    Serial.println("========================================");
    Serial.println("      MONITORING PASIEN IoT");
    Serial.println("========================================");

    // ---- WiFi Diagnostic Status ----
    Serial.print("WiFi Status : ");
    if (WiFi.status() == WL_CONNECTED) {
      Serial.print("TERHUBUNG (IP: ");
      Serial.print(WiFi.localIP());
      Serial.println(")");
    } else {
      Serial.print("TERPUTUS (Status Code: ");
      Serial.print(WiFi.status());
      Serial.println(") << DATA SENSOR TIDAK DIKIRIM KE DATABASE!");
      Serial.print("  * SSID Hotspot: "); Serial.println(WiFi.SSID());
      Serial.print("  * MQTT Broker: "); Serial.println(mqttBrokerIP);
    }
    Serial.println("----------------------------------------");

    // ---- GPS ----
    Serial.println("GPS");
    Serial.println("----------------------------------------");

    double lat = 0, lng = 0, hdop = 0;
    int sat = 0;
    bool gpsValid = false;

    if (gps.location.isValid()) {
      lat  = gps.location.lat();
      lng  = gps.location.lng();
      sat  = gps.satellites.value();
      hdop = gps.hdop.hdop();
      gpsValid = true;

      Serial.print("Latitude  : "); Serial.println(lat, 6);
      Serial.print("Longitude : "); Serial.println(lng, 6);
      Serial.print("Satelit   : "); Serial.println(sat);
      Serial.print("HDOP      : "); Serial.println(hdop);
      Serial.print("Maps      : https://www.google.com/maps?q=");
      Serial.print(lat, 6); Serial.print(","); Serial.println(lng, 6);
    } else if (!gpsModuleAlive) {
      // Tidak ada karakter NMEA masuk sama sekali -> kemungkinan wiring/baud salah,
      // BUKAN masalah sinyal satelit.
      Serial.println("TIDAK ADA DATA dari modul GPS!");
      Serial.println("Cek: kabel TX/RX (mungkin tertukar), power 3.3V/5V, baud rate.");
    } else {
      // Modul mengirim data (wiring OK), tapi belum dapat fix -> ini biasanya
      // karena posisi (dalam ruangan/atap beton), bukan bug kode.
      Serial.print("Modul GPS aktif, menunggu fix satelit... (karakter diterima: ");
      Serial.print(gpsCharsSeenBoot);
      Serial.println(")");
      if (gps.satellites.isValid()) {
        Serial.print("Satelit terdeteksi (belum fix): ");
        Serial.println(gps.satellites.value());
      }
      if (gps.charsProcessed() > 10 && gps.sentencesWithFix() == 0) {
        Serial.println("Coba pindah dekat jendela/luar ruangan untuk fix pertama.");
      }
    }

    Serial.println();


    // ---- MPU6050: status gerak berdasar jendela pengamatan 2 detik ----
    Serial.println("STATUS PASIEN");
    Serial.println("----------------------------------------");

    bool jatuh = fallDetectedInWindow;

    if (jatuh) {
      Serial.println("Status    : JATUH");
    } else {
      Serial.println("Status    : AMAN");
    }

    Serial.println();
    Serial.print("Acc X : "); Serial.println(lastAccX, 4);
    Serial.print("Acc Y : "); Serial.println(lastAccY, 4);
    Serial.print("Acc Z : "); Serial.println(lastAccZ, 4);

    // Kirim data ke Shared Struct menggunakan Mutex
    // Tunggu mutex maksimal 200ms agar data sensor tidak terlewat/terbuang
    if (xSemaphoreTake(dataMutex, pdMS_TO_TICKS(200)) == pdTRUE) {
      if (gpsValid) {
        sharedData.gpsLat = lat;
        sharedData.gpsLng = lng;
        sharedData.gpsSat = sat;
        sharedData.gpsHdop = hdop;
        sharedData.hasNewGps = true;
      }

      sharedData.mpuX = lastAccX;
      sharedData.mpuY = lastAccY;
      sharedData.mpuZ = lastAccZ;
      sharedData.mpuJatuh = jatuh;
      sharedData.hasNewMpu = true;

      xSemaphoreGive(dataMutex);
    } else {
      Serial.println("[Mutex] Timeout saat update shared data — mqttTask sedang sibuk.");
    }

    // Reset jendela pengamatan untuk siklus 2 detik berikutnya
    fallDetectedInWindow = false;
    aboveThresholdStreak = 0;

    Serial.println();
    Serial.println("========================================");
  }
}

// ======================================
// Background Task: MQTT Connect + Publish
// Berjalan di Core 0 secara paralel dengan pembacaan sensor
// ======================================
void mqttTask(void * parameter) {
  for (;;) {
    // ---- Auto Reconnect WiFi ----
    if (WiFi.status() != WL_CONNECTED) {
      Serial.println("[WiFi] Koneksi terputus! Mencoba menghubungkan kembali...");
      WiFi.disconnect();
      WiFi.begin();
      int retry = 0;
      while (WiFi.status() != WL_CONNECTED && retry < 20) {
        vTaskDelay(pdMS_TO_TICKS(500));
        retry++;
      }
      if (WiFi.status() == WL_CONNECTED) {
        Serial.println("[WiFi] Terhubung Kembali! IP: " + WiFi.localIP().toString());
      } else {
        Serial.println("[WiFi] Reconnect Gagal.");
        vTaskDelay(pdMS_TO_TICKS(5000));
        continue;
      }
    }

    // ---- Auto Reconnect MQTT ----
    if (!mqtt.connected()) {
      Serial.print("[MQTT] Menghubungkan ke broker ");
      Serial.print(mqttBrokerIP);
      Serial.print(" dari IP ESP32 ");
      Serial.print(WiFi.localIP());
      Serial.println(":1883 ...");

      // clientId unik berbasis MAC address
      String clientId = "ESP32-Pasien-" + String((uint32_t)ESP.getEfuseMac(), HEX);

      if (mqtt.connect(clientId.c_str())) {
        Serial.println("[MQTT] Terhubung ke Mosquitto! ✓");
      } else {
        Serial.print("[MQTT] Gagal, state=");
        Serial.println(mqtt.state());
        // -4=TIMEOUT, -3=DISCONNECTED, -2=CONNECT_FAILED, -1=DISCONNECTED, 1=BAD_PROTOCOL
        // 2=ID_REJECTED, 3=UNAVAILABLE, 4=BAD_CREDENTIALS, 5=UNAUTHORIZED
        vTaskDelay(pdMS_TO_TICKS(3000));
        continue;
      }
    }

    // ---- mqtt.loop() wajib dipanggil agar koneksi tetap hidup ----
    mqtt.loop();

    // ---- Ambil dan kirim data sensor jika ada yang baru ----
    if (mqtt.connected()) {
      double gpsLat = 0, gpsLng = 0, gpsHdop = 0;
      int    gpsSat = 0;
      bool   sendGps = false;

      float mpuX = 0, mpuY = 0, mpuZ = 0;
      bool  mpuJatuh = false;
      bool  sendMpu     = false;

      // Ambil data terbaru dari shared struct dengan Mutex
      if (xSemaphoreTake(dataMutex, pdMS_TO_TICKS(50)) == pdTRUE) {
        if (sharedData.hasNewGps) {
          gpsLat  = sharedData.gpsLat;
          gpsLng  = sharedData.gpsLng;
          gpsSat  = sharedData.gpsSat;
          gpsHdop = sharedData.gpsHdop;
          sendGps = true;
          sharedData.hasNewGps = false;
        }
        if (sharedData.hasNewMpu) {
          mpuX       = sharedData.mpuX;
          mpuY       = sharedData.mpuY;
          mpuZ       = sharedData.mpuZ;
          mpuJatuh   = sharedData.mpuJatuh;
          sendMpu    = true;
          sharedData.hasNewMpu = false;
        }
        xSemaphoreGive(dataMutex);
      }

      if (sendGps)  publishGpsData(gpsLat, gpsLng, gpsSat, gpsHdop);
      if (sendMpu)  publishMpuData(mpuX, mpuY, mpuZ, mpuJatuh);
    }

    vTaskDelay(pdMS_TO_TICKS(100)); // Cek data baru setiap 100ms
  }
}
