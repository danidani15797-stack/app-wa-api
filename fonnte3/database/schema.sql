-- =========================================================
-- Database: api_wa_fonnte
-- Project  : Integrasi Fonnte untuk Notifikasi Registrasi
-- =========================================================

CREATE DATABASE IF NOT EXISTS api_wa_fonnte
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE api_wa_fonnte;

-- ---------------------------------------------------------
-- Tabel: users
-- Menyimpan data pengguna hasil registrasi
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    no_hp VARCHAR(20) NOT NULL UNIQUE,
    alamat TEXT NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------
-- Tabel: log_whatsapp
-- Menyimpan riwayat & status setiap pengiriman WhatsApp
-- (Fitur tambahan bagian Q.2 dan tantangan bagian X)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS log_whatsapp (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    no_tujuan VARCHAR(20) NOT NULL,
    pesan TEXT NOT NULL,
    status ENUM('success', 'failed', 'pending') NOT NULL DEFAULT 'pending',
    response TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_log_whatsapp_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------
-- Tabel: admins
-- Akun untuk login ke halaman Dashboard / Kelola User
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Akun admin default untuk pertama kali login.
-- Username : admin
-- Password : admin123
-- PENTING: Segera login lalu ganti password ini sebelum project dipakai
-- sungguhan / dipublikasikan.
INSERT INTO admins (username, password)
VALUES ('admin', '$2b$12$phhZ8dhJSDEIivEiudTDa.E33GsH7Owzaxyfh94gskPWEX3DXRxzy')
ON DUPLICATE KEY UPDATE username = username;
