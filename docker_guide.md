# Panduan Deployment Menggunakan Docker

Dokumen ini berisi panduan untuk menjalankan dan mengelola seluruh microservices dan frontend aplikasi **Monitoring Pasien IoT** menggunakan Docker.

---

## 1. Persiapan
Sebelum menjalankan Docker, pastikan:
1. **Docker Desktop** sudah terinstall dan berjalan di sistem Anda (Windows/Linux/Mac).
2. Port **80** (untuk Web Frontend), **8080** (API Gateway), **8085** (phpMyAdmin), **8761** (Eureka), dan **1883** (MQTT) sedang tidak digunakan oleh aplikasi lain.

---

## 2. Cara Menjalankan Aplikasi

Jalankan perintah berikut di PowerShell atau Command Prompt pada root folder project (`c:\Semester6\TA`):

### A. Menjalankan Semua Service (Pertama kali / Build Baru)
Untuk membuild image dan menjalankan kontainer di background:
```bash
docker-compose up -d --build
```

### B. Menjalankan Service Tanpa Rebuild
Jika tidak ada perubahan pada file konfigurasi Dockerfile atau dependencies (seperti `composer.json` atau Spring Boot `pom.xml`):
```bash
docker-compose up -d
```

---

## 3. Perintah Pengelolaan Penting

### A. Melihat Status Kontainer yang Sedang Berjalan
```bash
docker-compose ps
```

### B. Melihat Log dari Kontainer Tertentu
Jika ingin memantau log aplikasi frontend Laravel:
```bash
docker-compose logs -f frontend-app
```
Jika ingin memantau log API Gateway atau Auth Service:
```bash
docker-compose logs -f api-gateway
docker-compose logs -f auth-service
```

### C. Menghentikan Kontainer (Stop)
Untuk menghentikan sementara tanpa menghapus data database:
```bash
docker-compose stop
```

### D. Mematikan Kontainer dan Menghapus Network (Down)
```bash
docker-compose down
```

---

## 4. Troubleshooting & Pemeliharaan Laravel di Docker

Jika Anda mengubah file `.env` atau ada error "419 Page Expired" atau view tidak terupdate, Anda dapat membersihkan cache Laravel di dalam kontainer Docker dengan perintah berikut:

```bash
# Membersihkan cache konfigurasi
docker-compose exec frontend-app php artisan config:clear

# Membersihkan cache route
docker-compose exec frontend-app php artisan route:clear

# Membersihkan cache view
docker-compose exec frontend-app php artisan view:clear

# Membersihkan cache aplikasi secara keseluruhan
docker-compose exec frontend-app php artisan cache:clear
```
