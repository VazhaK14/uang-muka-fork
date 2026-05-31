-- ============================================================
-- DATABASE SCHEMA: uang_muka
-- Generated from PHP source code scan
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+07:00";

CREATE DATABASE IF NOT EXISTS `uang_muka`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `uang_muka`;

-- ============================================================
-- Table: users
-- ============================================================
CREATE TABLE `users` (
  `id`           INT AUTO_INCREMENT PRIMARY KEY,
  `username`     VARCHAR(100) NOT NULL,
  `password`     VARCHAR(255) NOT NULL COMMENT 'MD5 hash',
  `nama_lengkap` VARCHAR(255),
  `role`         ENUM('admin','manager','director','finance','partner') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default users (password: admin123)
INSERT INTO `users` (`username`, `password`, `nama_lengkap`, `role`) VALUES
('admin',    MD5('admin123'),   'Administrator',  'admin'),
('manager',  MD5('admin123'),   'Budi Santoso',   'manager'),
('director', MD5('admin123'),   'Direktur Utama', 'director'),
('finance',  MD5('admin123'),   'Finance Staff',  'finance'),
('partner',  MD5('admin123'),   'Partner KAP',    'partner');

-- ============================================================
-- Table: karyawan
-- ============================================================
CREATE TABLE `karyawan` (
  `id`            INT AUTO_INCREMENT PRIMARY KEY,
  `id_karyawan`   VARCHAR(20),
  `nama_karyawan` VARCHAR(255),
  `bank`          VARCHAR(100),
  `rekening`      VARCHAR(100)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Table: klien
-- ============================================================
CREATE TABLE `klien` (
  `id`         INT AUTO_INCREMENT PRIMARY KEY,
  `id_klien`   VARCHAR(20),
  `nama_klien` VARCHAR(255),
  `bidang`     VARCHAR(255),
  `alamat`     TEXT,
  `kontak`     VARCHAR(100)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Table: penugasan
-- ============================================================
CREATE TABLE `penugasan` (
  `id`              INT AUTO_INCREMENT PRIMARY KEY,
  `id_penugasan`    VARCHAR(20),
  `jenis_penugasan` VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Table: rab
-- ============================================================
CREATE TABLE `rab` (
  `id`              INT AUTO_INCREMENT PRIMARY KEY,
  `no_rab`          VARCHAR(100),
  `nama_klien`      VARCHAR(255),
  `jenis_penugasan` VARCHAR(255),
  `tahun_buku`      YEAR,
  `periode_awal`    DATE,
  `periode_akhir`   DATE,
  `signing_partner` VARCHAR(255),
  `partner_review`  VARCHAR(255),
  `manager_ic`      VARCHAR(255),
  `auditor_ic`      VARCHAR(255),
  `total_anggaran`  BIGINT DEFAULT 0,
  `status`          VARCHAR(50) DEFAULT 'Submitted',
  `pencairan`       VARCHAR(50) DEFAULT 'Pending',
  `created_by`      INT,
  `submitted_by`    VARCHAR(255),
  `submitted_at`    DATETIME,
  `approved_by`     VARCHAR(255),
  `approved_at`     DATETIME,
  `rejected_note`   TEXT,
  `rejected_by`     VARCHAR(255),
  `rejected_at`     DATETIME
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Table: rab_detail
-- ============================================================
CREATE TABLE `rab_detail` (
  `id`        INT AUTO_INCREMENT PRIMARY KEY,
  `rab_id`    INT,
  `kategori`  VARCHAR(100),
  `deskripsi` TEXT,
  `qty`       INT DEFAULT 0,
  `hari`      INT DEFAULT 0,
  `nominal`   BIGINT DEFAULT 0,
  `total`     BIGINT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Table: rab_assistant
-- ============================================================
CREATE TABLE `rab_assistant` (
  `id`     INT AUTO_INCREMENT PRIMARY KEY,
  `rab_id` INT,
  `nama`   VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Table: lpj
-- ============================================================
CREATE TABLE `lpj` (
  `id`              INT AUTO_INCREMENT PRIMARY KEY,
  `rab_id`          INT,
  `no_lpj`          VARCHAR(100),
  `tanggal`         DATETIME,
  `total_realisasi` BIGINT DEFAULT 0,
  `surplus_defisit` BIGINT DEFAULT 0,
  `status`          VARCHAR(50) DEFAULT 'Submitted',
  `created_by`      INT,
  `submitted_by`    VARCHAR(255),
  `submitted_at`    DATETIME,
  `approved_by`     VARCHAR(255),
  `approved_at`     DATETIME,
  `rejected_note`   TEXT,
  `rejected_by`     VARCHAR(255),
  `rejected_at`     DATETIME
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Table: lpj_detail
-- ============================================================
CREATE TABLE `lpj_detail` (
  `id`        INT AUTO_INCREMENT PRIMARY KEY,
  `lpj_id`    INT,
  `kategori`  VARCHAR(100),
  `tanggal`   DATE,
  `deskripsi` TEXT,
  `nominal`   BIGINT DEFAULT 0,
  `bukti`     VARCHAR(255),
  `bukti_ref` VARCHAR(50)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Table: pencairan
-- ============================================================
CREATE TABLE `pencairan` (
  `id`            INT AUTO_INCREMENT PRIMARY KEY,
  `rab_id`        INT,
  `no_pencairan`  VARCHAR(100),
  `tanggal`       DATE,
  `total_dibayar` BIGINT DEFAULT 0,
  `penerima`      VARCHAR(255),
  `bank`          VARCHAR(100),
  `rekening`      VARCHAR(100),
  `approved_by`   VARCHAR(255),
  `approved_at`   DATETIME
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Table: akun (Chart of Accounts)
-- ============================================================
CREATE TABLE `akun` (
  `id`         INT AUTO_INCREMENT PRIMARY KEY,
  `kode_akun`  VARCHAR(50),
  `nama_akun`  VARCHAR(255),
  `keterangan` TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default akun sesuai mapping di pencairan_proses.php & lpj_approve.php
INSERT INTO `akun` (`id`, `kode_akun`, `nama_akun`, `keterangan`) VALUES
(1, '1-001', 'Kas',          'Akun kas umum'),
(2, '1-002', 'Cash/Bank',    'Akun kas dan bank'),
(3, '1-003', 'Cash Advance', 'Akun uang muka / cash advance'),
(4, '1-004', 'Reimburse',    'Akun reimburse kelebihan');

-- ============================================================
-- Table: jurnal_umum
-- ============================================================
CREATE TABLE `jurnal_umum` (
  `id`           INT AUTO_INCREMENT PRIMARY KEY,
  `tanggal`      DATE,
  `ref_no`       VARCHAR(100),
  `akun_id`      INT,
  `debit`        BIGINT DEFAULT 0,
  `kredit`       BIGINT DEFAULT 0,
  `keterangan`   TEXT,
  `pencairan_id` INT,
  `lpj_id`       INT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Table: notifikasi
-- ============================================================
CREATE TABLE `notifikasi` (
  `id`          INT AUTO_INCREMENT PRIMARY KEY,
  `role_tujuan` VARCHAR(50),
  `pesan`       TEXT,
  `status_baca` VARCHAR(20) DEFAULT 'belum',
  `created_at`  DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
