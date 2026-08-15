# 🦟 Mosquitto MQTT Broker — Monitoring Pasien IoT

Konfigurasi Mosquitto broker untuk proyek monitoring pasien IoT.

---

## ✅ Prasyarat: Install Mosquitto

1. Download installer dari: **https://mosquitto.org/download/**
2. Pilih **Windows (64-bit)** → `mosquitto-2.x.x-install-windows-x64.exe`
3. Install dengan pengaturan default
4. Setelah install, Mosquitto ada di: `C:\Program Files\mosquitto\`

---

## 🚀 Cara Menjalankan

Buka **Command Prompt sebagai Administrator**, lalu jalankan:

```cmd
"C:\Program Files\mosquitto\mosquitto.exe" -c "C:\Semester6\TA\Mosquitto-Broker\mosquitto.conf" -v
```

Output yang benar:
```
mosquitto version 2.x.x starting
Config loaded from mosquitto.conf
Opening ipv4 listen socket on port 1883.
Opening ipv6 listen socket on port 1883.
mosquitto version 2.x.x running
```

> **Tips:** Biarkan jendela CMD ini tetap terbuka selama sistem berjalan.

---

## 🔍 Cek Koneksi (Opsional)

Jika ingin memantau pesan MQTT secara langsung:

1. Buka CMD **baru**, jalankan subscriber test:
```cmd
"C:\Program Files\mosquitto\mosquitto_sub.exe" -h 127.0.0.1 -p 1883 -t "pasien/#" -v
```

2. Setiap data dari ESP32 akan muncul seperti:
```
pasien/gps {"latitude":-0.932255,"longitude":100.427185,"satelit":12,"hdop":0.81}
pasien/suhu {"suhuDahi":36.50}
pasien/mpu {"accX":0.0012,"accY":-0.1144,"accZ":0.0021,"bergerak":false}
```

---

## ⚙️ Konfigurasi (mosquitto.conf)

| Parameter | Nilai | Keterangan |
|---|---|---|
| `listener` | `1883 0.0.0.0` | Dengarkan semua interface di port 1883 |
| `allow_anonymous` | `true` | Tanpa username/password (development) |
| `persistence` | `true` | Session tersimpan saat restart |

---

## ❗ Troubleshooting

**Error: `Address already in use`**
→ Mosquitto sudah berjalan. Cek di Task Manager → processes → `mosquitto.exe`.

**ESP32 tidak bisa connect ke broker**
→ Pastikan:
1. Firewall Windows tidak memblokir port 1883
2. ESP32 dan komputer terhubung ke WiFi yang **sama**
3. IP yang dimasukkan di portal WiFiManager sudah benar
