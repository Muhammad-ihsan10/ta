-- ============================================================
-- DATABASE SETUP: Microservice per-database
-- Setiap service memiliki database-nya SENDIRI
-- Jalankan script ini di MySQL sebelum menghidupkan microservice
-- ============================================================

-- ============================================================
-- 1. DB AUTH-SERVICE  →  db_auth  →  tabel: users
-- ============================================================
CREATE DATABASE IF NOT EXISTS `db_auth`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `db_auth`;

CREATE TABLE IF NOT EXISTS `users` (
  `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`              VARCHAR(255)    NOT NULL,
  `email`             VARCHAR(255)    NOT NULL UNIQUE,
  `email_verified_at` TIMESTAMP       NULL DEFAULT NULL,
  `password`          VARCHAR(255)    NOT NULL,
  `role`              VARCHAR(50)     NOT NULL DEFAULT 'USER',
  `remember_token`    VARCHAR(100)    NULL,
  `created_at`        TIMESTAMP       NULL DEFAULT NULL,
  `updated_at`        TIMESTAMP       NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 2. DB BN220-SERVICE (GPS)  →  db_gps  →  tabel: gps_data
-- ============================================================
CREATE DATABASE IF NOT EXISTS `db_gps`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `db_gps`;

CREATE TABLE IF NOT EXISTS `gps_data` (
  `id`        BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `latitude`  DOUBLE          NOT NULL,
  `longitude` DOUBLE          NOT NULL,
  `satelit`   INT             NULL,
  `hdop`      DOUBLE          NULL,
  `maps_url`  VARCHAR(500)    NULL,
  `timestamp` DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 3. DB MPU-SERVICE  →  db_mpu  →  tabel: mpu_data
-- ============================================================
CREATE DATABASE IF NOT EXISTS `db_mpu`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `db_mpu`;

CREATE TABLE IF NOT EXISTS `mpu_data` (
  `id`        BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `acc_x`     DOUBLE          NULL,
  `acc_y`     DOUBLE          NULL,
  `acc_z`     DOUBLE          NULL,
  `gerakan`   VARCHAR(20)     NULL,
  `timestamp` DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 4. DB WEB-FRONTEND (Laravel)  →  db_frontend
-- CATATAN: Tabel di db_frontend dibuat oleh Laravel via
--          "php artisan migrate", BUKAN di sini.
--          Init SQL ini hanya membuat database-nya saja.
-- ============================================================
CREATE DATABASE IF NOT EXISTS `db_frontend`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

-- ============================================================
-- SELESAI
-- ============================================================
SELECT 'db_auth    → database Auth-Service   siap!' AS status;
SELECT 'db_gps     → database GPS-Service    siap!' AS status;
SELECT 'db_mpu     → database MPU-Service    siap!' AS status;
SELECT 'db_frontend → database Web-Frontend  siap!' AS status;
