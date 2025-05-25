-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Versi server:                 11.7.2-MariaDB - mariadb.org binary distribution
-- OS Server:                    Win64
-- HeidiSQL Versi:               12.10.0.7000
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Membuang struktur basisdata untuk tgs3
CREATE DATABASE IF NOT EXISTS `tgs3` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci */;
USE `tgs3`;

-- membuang struktur untuk table tgs3.catatan
CREATE TABLE IF NOT EXISTS `catatan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `judul` text NOT NULL,
  `isi` longtext NOT NULL,
  `waktu_dibuat` timestamp NOT NULL DEFAULT current_timestamp(),
  `waktu_diubah` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
);

-- Pengeluaran data tidak dipilih.

-- membuang struktur untuk table tgs3.data_diri
CREATE TABLE IF NOT EXISTS `data_diri` (
  `nama_lengkap` text NOT NULL,
  `keterangan` longtext DEFAULT NULL,
  `foto` text DEFAULT NULL
);

-- Pengeluaran data tidak dipilih.

-- membuang struktur untuk table tgs3.karya
CREATE TABLE IF NOT EXISTS `karya` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `judul` text NOT NULL,
  `keterangan` longtext DEFAULT NULL,
  `tautan` text DEFAULT NULL,
  PRIMARY KEY (`id`)
);

-- Pengeluaran data tidak dipilih.

-- membuang struktur untuk table tgs3.komentar
CREATE TABLE IF NOT EXISTS `komentar` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `catatan` int(11) NOT NULL,
  `pembuat` int(11) NOT NULL,
  `membalas` int(11) DEFAULT NULL,
  `isi` longtext NOT NULL,
  `waktu_dibuat` timestamp NOT NULL DEFAULT current_timestamp(),
  `waktu_diubah` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `FK__catatan` (`catatan`),
  KEY `FK__pengguna` (`pembuat`),
  KEY `FK_komentar_komentar` (`membalas`),
  CONSTRAINT `FK__catatan` FOREIGN KEY (`catatan`) REFERENCES `catatan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK__pengguna` FOREIGN KEY (`pembuat`) REFERENCES `pengguna` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_komentar_komentar` FOREIGN KEY (`membalas`) REFERENCES `komentar` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

-- Pengeluaran data tidak dipilih.

-- membuang struktur untuk table tgs3.pengguna
CREATE TABLE IF NOT EXISTS `pengguna` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` text NOT NULL,
  `lengkap` text NOT NULL,
  `sandi` text NOT NULL,
  `jenis` enum('admin','user') NOT NULL DEFAULT 'user',
  `waktu_dibuat` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
);

-- Pengeluaran data tidak dipilih.

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
