-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 23, 2025 at 09:14 PM
-- Server version: 8.0.30
-- PHP Version: 8.2.21

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_rants`
--

-- --------------------------------------------------------

--
-- Table structure for table `addresses`
--

CREATE TABLE `addresses` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Rumah',
  `recipient_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_address` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `province` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `district` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postal_code` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `is_primary` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `addresses`
--

INSERT INTO `addresses` (`id`, `user_id`, `label`, `recipient_name`, `phone_number`, `full_address`, `province`, `city`, `district`, `postal_code`, `notes`, `is_primary`, `created_at`, `updated_at`) VALUES
(1, 4, 'Rumah', 'Taronet', '82283217597', 'jl. impian', 'riau', 'bengkalis', 'bengkalis', '28711', 'sampeng kolam', 1, '2025-11-22 03:50:57', '2025-11-22 03:50:57'),
(2, 7, 'Rumah', 'tes', '5454545', 'dddd', 'dd', 'dd', 'dd', '2323', 'sdsd', 1, '2025-11-22 22:45:59', '2025-11-22 22:45:59'),
(3, 2, 'Rumah', 'tesss', '878787878', 'klklkl', 'hjhj', 'jjhjh', 'hggyut', '22222', 'ftyyyy', 1, '2025-11-23 07:09:03', '2025-11-23 07:09:03');

-- --------------------------------------------------------

--
-- Table structure for table `availability_calendar`
--

CREATE TABLE `availability_calendar` (
  `id` bigint UNSIGNED NOT NULL,
  `service_type` enum('dance','makeup') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Type of service',
  `service_id` bigint UNSIGNED NOT NULL COMMENT 'ID of the service (dance_service or makeup_service)',
  `date` date NOT NULL COMMENT 'Date for availability',
  `available_slots` int NOT NULL COMMENT 'Number of available slots for this date',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chat_messages`
--

CREATE TABLE `chat_messages` (
  `id` bigint UNSIGNED NOT NULL,
  `chat_session_id` bigint UNSIGNED NOT NULL,
  `sender_type` enum('user','ai','admin','system') COLLATE utf8mb4_unicode_ci NOT NULL,
  `sender_id` bigint UNSIGNED DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `image_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_size` bigint DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `chat_messages`
--

INSERT INTO `chat_messages` (`id`, `chat_session_id`, `sender_type`, `sender_id`, `message`, `image_name`, `image_path`, `image_size`, `metadata`, `is_read`, `created_at`, `updated_at`) VALUES
(1, 5, 'user', 4, 'hay', NULL, NULL, NULL, NULL, 1, '2025-11-21 08:48:32', '2025-11-21 09:14:01'),
(3, 1, 'ai', NULL, 'Halo! 👋 Selamat datang di RANTS (Ray Entertainments)!\n\nSaya adalah asisten virtual yang siap membantu Anda dengan:\n🎭 Informasi Jasa Tari\n💄 Informasi Jasa Rias\n👘 Sewa Kostum\n📦 Status Pesanan\n❓ Pertanyaan Umum\n\nSilakan tanyakan apa saja! 😊', NULL, NULL, NULL, NULL, 1, '2025-11-21 08:48:36', '2025-11-21 08:49:58'),
(4, 1, 'user', 4, 'haloo broku', NULL, NULL, NULL, NULL, 1, '2025-11-21 08:49:44', '2025-11-21 08:49:58'),
(5, 1, 'ai', NULL, 'Halo broku! 👋 Ada yang bisa RANTS bantu hari ini? Mungkin kamu tertarik dengan kelas tari modern, tradisional, atau hip hop kita? Atau mungkin ada kebutuhan lain terkait tari yang bisa kami bantu? 😉\n', NULL, NULL, NULL, NULL, 1, '2025-11-21 08:49:46', '2025-11-21 08:49:58'),
(6, 1, 'user', 4, 'Bagaimana cara pembayaran? 💳', NULL, NULL, NULL, NULL, 1, '2025-11-21 08:50:04', '2025-11-21 08:50:36'),
(7, 1, 'ai', NULL, 'Halo! 👋 Terima kasih sudah menghubungi RANTS (Ray Entertainments).\n\nUntuk pembayaran, saat ini Anda bisa melakukan pembayaran melalui aplikasi RANTS. Di sana, Anda akan menemukan berbagai pilihan pembayaran yang tersedia, seperti:\n\n*   Transfer bank 🏦\n*   Dompet digital (misalnya, GoPay, OVO, Dana) 📱\n*   Kartu kredit/debit 💳\n\nSemoga informasi ini membantu ya! Apakah ada pertanyaan lain yang bisa saya bantu? 😊\n', NULL, NULL, NULL, NULL, 1, '2025-11-21 08:50:06', '2025-11-21 08:50:36'),
(8, 1, 'user', 4, 'p', NULL, NULL, NULL, NULL, 1, '2025-11-21 08:50:14', '2025-11-21 08:50:36'),
(9, 1, 'ai', NULL, 'Halo! 👋 Ada yang bisa saya bantu? Saya siap memberikan informasi tentang layanan tari yang tersedia di RANTS (Ray Entertainments). 😊\n', NULL, NULL, NULL, NULL, 1, '2025-11-21 08:50:16', '2025-11-21 08:50:36'),
(10, 1, 'user', 4, 'hubungi admin', NULL, NULL, NULL, NULL, 1, '2025-11-21 08:50:23', '2025-11-21 08:50:36'),
(11, 1, 'ai', NULL, 'Baik, saya akan menghubungkan Anda dengan admin kami. 👨‍💼\n\nMohon tunggu sebentar ya! Admin kami akan segera membantu Anda! 😊', NULL, NULL, NULL, NULL, 1, '2025-11-21 08:50:23', '2025-11-21 08:50:36'),
(12, 1, 'admin', 1, 'Halo! 👋 Saya Admin RANTS dari RANTS.\n\nAda yang bisa saya bantu? Silakan sampaikan keluhan atau pertanyaan Anda. 😊', NULL, NULL, NULL, NULL, 0, '2025-11-21 08:50:39', '2025-11-21 08:50:39'),
(13, 1, 'user', 4, 'halo min', NULL, NULL, NULL, NULL, 1, '2025-11-21 08:50:51', '2025-11-21 08:52:20'),
(14, 1, 'admin', 1, 'ya ada apa', NULL, NULL, NULL, NULL, 0, '2025-11-21 08:51:03', '2025-11-21 08:51:03'),
(15, 1, 'user', 4, 'produk ini ada gak', 'scaled_3.jpg', 'chat-images/F9x1JgnNhgwKE21iRpysANFjqlMqJXOmv5F3FexO.jpg', 144197, NULL, 1, '2025-11-21 08:51:48', '2025-11-21 08:52:20'),
(16, 1, 'admin', 1, 'ohh gak ada bro', NULL, NULL, NULL, NULL, 0, '2025-11-21 08:52:35', '2025-11-21 08:52:35'),
(17, 1, 'system', NULL, 'Sesi chat telah berakhir. Silahkan keluar dari pesan dan mulai sesi baru jika perlu bantuan lagi.', NULL, NULL, NULL, NULL, 0, '2025-11-21 08:53:35', '2025-11-21 08:53:35'),
(18, 2, 'ai', NULL, 'Halo! 👋 Selamat datang di RANTS (Ray Entertainments)!\n\nSaya adalah asisten virtual yang siap membantu Anda dengan:\n🎭 Informasi Jasa Tari\n💄 Informasi Jasa Rias\n👘 Sewa Kostum\n📦 Status Pesanan\n❓ Pertanyaan Umum\n\nSilakan tanyakan apa saja! 😊', NULL, NULL, NULL, NULL, 1, '2025-11-21 08:53:35', '2025-11-21 08:54:44'),
(19, 2, 'user', 4, 'halo', NULL, NULL, NULL, NULL, 1, '2025-11-21 08:54:25', '2025-11-21 08:54:44'),
(20, 2, 'ai', NULL, 'Halo! 👋 Selamat datang di Ray Entertainments (RANTS)! Ada yang bisa saya bantu? 😊 Kami menyediakan berbagai layanan tari profesional, lho. Apakah Anda tertarik dengan kelas tari, penyewaan penari untuk acara, atau mungkin pertunjukan tari? ✨\n', NULL, NULL, NULL, NULL, 1, '2025-11-21 08:54:27', '2025-11-21 08:54:44'),
(21, 2, 'user', 4, 'hubungi admin', NULL, NULL, NULL, NULL, 1, '2025-11-21 08:54:36', '2025-11-21 08:54:44'),
(22, 2, 'ai', NULL, 'Baik, saya akan menghubungkan Anda dengan admin kami. 👨‍💼\n\nMohon tunggu sebentar ya! Admin kami akan segera membantu Anda! 😊', NULL, NULL, NULL, NULL, 1, '2025-11-21 08:54:36', '2025-11-21 08:54:44'),
(23, 2, 'admin', 1, 'Halo! 👋 Saya Admin RANTS dari RANTS.\n\nAda yang bisa saya bantu? Silakan sampaikan keluhan atau pertanyaan Anda. 😊', NULL, NULL, NULL, NULL, 0, '2025-11-21 08:54:46', '2025-11-21 08:54:46'),
(24, 2, 'user', 4, 'halo min', NULL, NULL, NULL, NULL, 0, '2025-11-21 08:55:12', '2025-11-21 08:55:12'),
(25, 2, 'admin', 1, 'yaa halo', NULL, NULL, NULL, NULL, 0, '2025-11-21 08:55:18', '2025-11-21 08:55:18'),
(26, 2, 'system', NULL, 'Sesi chat telah berakhir. Silahkan keluar dari pesan dan mulai sesi baru jika perlu bantuan lagi.', NULL, NULL, NULL, NULL, 0, '2025-11-21 08:55:27', '2025-11-21 08:55:27'),
(27, 3, 'ai', NULL, 'Halo! 👋 Selamat datang di RANTS (Ray Entertainments)!\n\nSaya adalah asisten virtual yang siap membantu Anda dengan:\n🎭 Informasi Jasa Tari\n💄 Informasi Jasa Rias\n👘 Sewa Kostum\n📦 Status Pesanan\n❓ Pertanyaan Umum\n\nSilakan tanyakan apa saja! 😊', NULL, NULL, NULL, NULL, 1, '2025-11-21 08:55:29', '2025-11-21 09:00:07'),
(28, 3, 'user', 4, 'hlooo', NULL, NULL, NULL, NULL, 1, '2025-11-21 08:56:14', '2025-11-21 09:00:07'),
(29, 3, 'ai', NULL, 'Halo! 👋 Selamat datang di Ray Entertainments (RANTS)! Ada yang bisa saya bantu hari ini? Mungkin Anda tertarik dengan kelas tari, pertunjukan, atau layanan lainnya yang kami tawarkan? 😊\n', NULL, NULL, NULL, NULL, 1, '2025-11-21 08:56:16', '2025-11-21 09:00:07'),
(30, 3, 'user', 4, 'produk apa ada', NULL, NULL, NULL, NULL, 1, '2025-11-21 08:56:35', '2025-11-21 09:00:07'),
(31, 3, 'ai', NULL, 'Halo! 👋 Selamat datang di RANTS (Ray Entertainments)! Kami sangat senang Anda tertarik dengan layanan kami.\n\nSaat ini, kami menyediakan berbagai kelas dan layanan tari, termasuk:\n\n*   **Kelas Tari:** Ballet, Jazz, Hip Hop, Tari Tradisional, dan banyak lagi! 🩰💃🕺\n*   **Private Class:** Kelas privat untuk pendalaman materi atau persiapan event khusus.\n*   **Koreografi:** Jasa pembuatan koreografi untuk berbagai keperluan (acara, kompetisi, dll.). 👯‍♀️\n*   **Sewa Studio:** Penyewaan studio tari dengan fasilitas lengkap. 🏢\n*   **Event & Pertunjukan:** Penampilan tari untuk acara-acara khusus. 🎉\n\nUntuk informasi lebih detail mengenai jadwal kelas, harga, atau detail lainnya, Anda bisa langsung melihatnya di aplikasi RANTS, ya! Di sana, Anda juga bisa langsung melakukan pemesanan.\n\nApakah ada kelas atau layanan tertentu yang membuat Anda tertarik? 😊\n', NULL, NULL, NULL, NULL, 1, '2025-11-21 08:56:38', '2025-11-21 09:00:07'),
(32, 3, 'user', 4, 'Apa saja produk yang tersedia? 📦', NULL, NULL, NULL, NULL, 1, '2025-11-21 09:00:56', '2025-11-21 09:01:25'),
(33, 3, 'ai', NULL, 'Halo! 👋 Dengan senang hati saya akan memberikan informasi mengenai produk yang tersedia di RANTS. Berikut adalah beberapa layanan yang kami tawarkan:\n\n*   **Kelas Tari:** Kami menyediakan berbagai kelas tari untuk semua tingkatan, mulai dari pemula hingga profesional. Ada kelas balet, hip hop, tari tradisional, kontemporer, dan masih banyak lagi! 💃🕺\n*   **Workshop Tari:** Kami juga mengadakan workshop tari dengan berbagai tema menarik yang dibimbing oleh instruktur berpengalaman. Ini adalah kesempatan bagus untuk meningkatkan keterampilan Anda dalam waktu singkat. 🤩\n*   **Pertunjukan Tari:** RANTS secara rutin mengadakan pertunjukan tari yang menampilkan bakat-bakat terbaik dari sanggar kami. Jangan lewatkan kesempatan untuk menyaksikan pertunjukan yang memukau! 🎭\n*   **Penyewaan Studio:** Studio kami tersedia untuk disewa bagi Anda yang membutuhkan tempat latihan yang nyaman dan profesional. Dilengkapi dengan fasilitas lengkap untuk mendukung kegiatan tari Anda. 🩰\n\nUntuk pemesanan, Anda bisa langsung melakukannya melalui aplikasi RANTS. Sangat mudah dan praktis! 😉\n\nApakah ada layanan lain yang ingin Anda ketahui lebih lanjut? 😊\n', NULL, NULL, NULL, NULL, 1, '2025-11-21 09:01:04', '2025-11-21 09:01:25'),
(34, 3, 'user', 4, 'hubungi admin', NULL, NULL, NULL, NULL, 1, '2025-11-21 09:01:14', '2025-11-21 09:01:25'),
(35, 3, 'ai', NULL, 'Baik, saya akan menghubungkan Anda dengan admin kami. 👨‍💼\n\nMohon tunggu sebentar ya! Admin kami akan segera membantu Anda! 😊', NULL, NULL, NULL, NULL, 1, '2025-11-21 09:01:14', '2025-11-21 09:01:25'),
(36, 3, 'admin', 1, 'Halo! 👋 Saya Admin RANTS dari RANTS.\n\nAda yang bisa saya bantu? Silakan sampaikan keluhan atau pertanyaan Anda. 😊', NULL, NULL, NULL, NULL, 0, '2025-11-21 09:01:28', '2025-11-21 09:01:28'),
(37, 3, 'user', 4, 'min', NULL, NULL, NULL, NULL, 0, '2025-11-21 09:01:39', '2025-11-21 09:01:39'),
(38, 3, 'admin', 1, 'ya', NULL, NULL, NULL, NULL, 0, '2025-11-21 09:01:42', '2025-11-21 09:01:42'),
(39, 3, 'system', NULL, 'Sesi chat telah berakhir. Silahkan keluar dari pesan dan mulai sesi baru jika perlu bantuan lagi.', NULL, NULL, NULL, NULL, 0, '2025-11-21 09:01:56', '2025-11-21 09:01:56'),
(40, 4, 'ai', NULL, 'Halo! 👋 Selamat datang di RANTS (Ray Entertainments)!\n\nSaya adalah asisten virtual yang siap membantu Anda dengan:\n🎭 Informasi Jasa Tari\n💄 Informasi Jasa Rias\n👘 Sewa Kostum\n📦 Status Pesanan\n❓ Pertanyaan Umum\n\nSilakan tanyakan apa saja! 😊', NULL, NULL, NULL, NULL, 1, '2025-11-21 09:02:02', '2025-11-21 09:05:18'),
(41, 4, 'user', 4, 'p', NULL, NULL, NULL, NULL, 1, '2025-11-21 09:02:33', '2025-11-21 09:05:18'),
(42, 4, 'ai', NULL, 'Halo! 👋 Ada yang bisa saya bantu hari ini? Apakah Anda punya pertanyaan tentang kelas tari, pertunjukan, atau layanan lain yang ditawarkan oleh RANTS (Ray Entertainments)? 😊\n', NULL, NULL, NULL, NULL, 1, '2025-11-21 09:02:35', '2025-11-21 09:05:18'),
(43, 4, 'user', 4, 'apa ini', 'scaled_3.png', 'chat-images/sFcmaetonXXt0l7xVtQoKAwsM0pHPJ3gIAJvbTX9.png', 510298, NULL, 1, '2025-11-21 09:04:18', '2025-11-21 09:05:18'),
(44, 4, 'ai', NULL, 'Halo! 👋 Saya RANTS (Ray Entertainments), asisten virtual Anda.\n\nSepertinya Anda bertanya tentang gambar yang Anda kirim. Dari gambar tersebut, terlihat informasi mengenai Penerimaan Peserta Didik Baru di Politeknik Negeri Bengkalis untuk tahun ajaran 2023/2024. Apakah ada hal lain yang ingin Anda ketahui terkait gambar ini? 😊\n\nJika Anda memiliki pertanyaan lain seputar layanan tari, kostum, atau rias dari RANTS, jangan ragu untuk bertanya ya! ✨\n', NULL, NULL, NULL, NULL, 1, '2025-11-21 09:04:23', '2025-11-21 09:05:18'),
(45, 4, 'user', 4, 'hubungi admin', NULL, NULL, NULL, NULL, 1, '2025-11-21 09:04:43', '2025-11-21 09:05:18'),
(46, 4, 'ai', NULL, 'Baik, saya akan menghubungkan Anda dengan admin kami. 👨‍💼\n\nMohon tunggu sebentar ya! Admin kami akan segera membantu Anda! 😊', NULL, NULL, NULL, NULL, 1, '2025-11-21 09:04:43', '2025-11-21 09:05:18'),
(47, 4, 'user', 4, 'hubungi admin', NULL, NULL, NULL, NULL, 1, '2025-11-21 09:05:22', '2025-11-21 09:05:31'),
(48, 4, 'ai', NULL, 'Baik, saya akan menghubungkan Anda dengan admin kami. 👨‍💼\n\nMohon tunggu sebentar ya! Admin kami akan segera membantu Anda! 😊', NULL, NULL, NULL, NULL, 1, '2025-11-21 09:05:22', '2025-11-21 09:05:31'),
(49, 4, 'admin', 1, 'Halo! 👋 Saya Admin RANTS dari RANTS.\n\nAda yang bisa saya bantu? Silakan sampaikan keluhan atau pertanyaan Anda. 😊', NULL, NULL, NULL, NULL, 0, '2025-11-21 09:05:28', '2025-11-21 09:05:28'),
(50, 4, 'user', 4, 'p', NULL, NULL, NULL, NULL, 0, '2025-11-21 09:06:45', '2025-11-21 09:06:45'),
(51, 4, 'admin', 1, 'y', NULL, NULL, NULL, NULL, 0, '2025-11-21 09:06:55', '2025-11-21 09:06:55'),
(52, 4, 'system', NULL, 'Sesi chat telah berakhir. Silahkan keluar dari pesan dan mulai sesi baru jika perlu bantuan lagi.', NULL, NULL, NULL, NULL, 0, '2025-11-21 09:12:08', '2025-11-21 09:12:08'),
(53, 5, 'ai', NULL, 'Halo! 👋 Selamat datang di RANTS (Ray Entertainments)!\n\nSaya adalah asisten virtual yang siap membantu Anda dengan:\n🎭 Informasi Jasa Tari\n💄 Informasi Jasa Rias\n👘 Sewa Kostum\n📦 Status Pesanan\n❓ Pertanyaan Umum\n\nSilakan tanyakan apa saja! 😊', NULL, NULL, NULL, NULL, 1, '2025-11-21 09:12:28', '2025-11-21 09:14:01'),
(54, 5, 'user', 4, 'jancok', NULL, NULL, NULL, NULL, 1, '2025-11-21 09:12:35', '2025-11-21 09:14:01'),
(55, 5, 'ai', NULL, 'Mohon maaf, saya tidak mengerti maksud Anda. Apakah ada hal lain yang bisa saya bantu terkait layanan tari di RANTS (Ray Entertainments)? 😊\n', NULL, NULL, NULL, NULL, 1, '2025-11-21 09:12:37', '2025-11-21 09:14:01'),
(56, 5, 'user', 4, 'Bagaimana garansi produk? ✅', NULL, NULL, NULL, NULL, 1, '2025-11-21 09:13:45', '2025-11-21 09:14:01'),
(57, 5, 'ai', NULL, 'Halo! 👋 Terima kasih sudah menghubungi RANTS (Ray Entertainments).\n\nMengenai garansi produk, bisa diperjelas produk yang dimaksud produk apa ya? 🤔 Apakah ini terkait dengan merchandise yang Anda beli, atau kelas/layanan tertentu yang Anda ikuti?\n\nUntuk merchandise, kami memiliki kebijakan pengembalian jika ada cacat produksi atau kerusakan saat pengiriman. Silakan hubungi admin dengan mengetik \'hubungi admin\' atau \'butuh bantuan admin\' agar bisa dibantu lebih lanjut.\n\nUntuk kelas atau layanan, kami memastikan kualitas pengajaran dan pengalaman yang terbaik. Jika Anda merasa tidak puas dengan kelas yang Anda ikuti, jangan ragu untuk menyampaikan keluhan Anda kepada instruktur atau hubungi admin agar kami bisa mencari solusi terbaik.\n\nAda hal lain yang bisa saya bantu? 😊\n', NULL, NULL, NULL, NULL, 1, '2025-11-21 09:13:47', '2025-11-21 09:14:01'),
(58, 5, 'user', 4, 'Apa saja produk yang tersedia? 📦', NULL, NULL, NULL, NULL, 1, '2025-11-21 09:13:49', '2025-11-21 09:14:01'),
(59, 5, 'ai', NULL, 'Halo! 👋\n\nSaat ini, RANTS (Ray Entertainments) menyediakan berbagai layanan menarik untuk Anda yang mencintai seni tari, di antaranya:\n\n*   **Kelas Tari:** Tersedia berbagai kelas tari untuk berbagai usia dan tingkat kemampuan, mulai dari balet, jazz, hip hop, tari tradisional, dan masih banyak lagi! 🩰💃🕺\n*   **Workshop Tari:** Kami juga mengadakan workshop tari dengan tema yang berbeda-beda setiap bulannya. Ini kesempatan bagus untuk belajar teknik baru atau mendalami gaya tari tertentu. 💡\n*   **Pertunjukan Tari:** RANTS secara rutin mengadakan pertunjukan tari yang menampilkan bakat-bakat terbaik dari sanggar kami. Jangan lewatkan kesempatan untuk menyaksikan penampilan yang memukau! 🎭\n*   **Penyewaan Kostum Tari:** Kami juga menyediakan penyewaan kostum tari untuk berbagai keperluan, mulai dari pentas seni hingga acara khusus lainnya. 👗\n\nUntuk pemesanan, Anda bisa langsung melalui aplikasi RANTS ya! 📱\n\nAda lagi yang ingin Anda ketahui tentang produk dan layanan kami? 😊\n', NULL, NULL, NULL, NULL, 1, '2025-11-21 09:13:53', '2025-11-21 09:14:01'),
(60, 5, 'user', 4, 'hlooo', NULL, NULL, NULL, NULL, 1, '2025-11-21 09:13:59', '2025-11-21 09:14:01'),
(61, 5, 'ai', NULL, 'Halo! 👋 Selamat datang di RANTS (Ray Entertainments)! Ada yang bisa saya bantu hari ini? Mungkin Anda tertarik dengan kelas tari kami, ingin tahu tentang pertunjukan yang akan datang, atau ada hal lain yang ingin Anda tanyakan? 😊\n', NULL, NULL, NULL, NULL, 1, '2025-11-21 09:14:01', '2025-11-21 09:14:01'),
(62, 5, 'user', 4, 'p', NULL, NULL, NULL, NULL, 1, '2025-11-21 09:14:14', '2025-11-21 09:14:34'),
(63, 5, 'ai', NULL, 'Halo! 👋 Ada yang bisa saya bantu? Apakah Anda punya pertanyaan seputar layanan tari yang kami tawarkan di RANTS (Ray Entertainments)? Kami siap membantu Anda menemukan kelas atau layanan yang paling sesuai dengan kebutuhan Anda! 😊\n', NULL, NULL, NULL, NULL, 1, '2025-11-21 09:14:17', '2025-11-21 09:14:34'),
(64, 5, 'user', 4, 'Bagaimana garansi produk? ✅', NULL, NULL, NULL, NULL, 1, '2025-11-21 09:14:42', '2025-11-21 09:15:29'),
(65, 5, 'ai', NULL, 'Halo! 👋 Terima kasih sudah menghubungi RANTS (Ray Entertainments).\n\nMengenai garansi produk, bisa diperjelas produk yang mana yang Anda maksud? 🤔 Karena RANTS sendiri menyediakan berbagai layanan seperti kelas tari, penyewaan kostum, dan pertunjukan.\n\nJika yang Anda maksud adalah kelas tari, kami menjamin kualitas pengajaran dari instruktur profesional kami. Jika Anda merasa tidak puas dengan kelas yang Anda ikuti, silakan hubungi admin kami dengan mengetik \'hubungi admin\' atau \'butuh bantuan admin\' agar bisa kami bantu lebih lanjut.\n\nJika yang Anda maksud adalah penyewaan kostum, kami selalu memastikan kostum dalam kondisi baik sebelum disewakan. Jika terdapat kerusakan saat Anda terima, segera laporkan kepada kami saat pengambilan kostum.\n\nSemoga penjelasan ini membantu! Apakah ada pertanyaan lain yang bisa saya bantu? 😊\n', NULL, NULL, NULL, NULL, 1, '2025-11-21 09:14:46', '2025-11-21 09:15:29'),
(66, 5, 'user', 4, 'Saya ingin pakai ini 🛍️', NULL, NULL, NULL, NULL, 1, '2025-11-21 09:15:23', '2025-11-21 09:15:29'),
(67, 5, 'ai', NULL, 'Halo! 👋 Terima kasih sudah menghubungi RANTS (Ray Entertainments)!\n\nSaya mengerti Anda ingin menggunakan sesuatu seperti tas belanja (🛍️). Apakah Anda ingin menggunakan tas belanja RANTS untuk membawa perlengkapan tari Anda? Saat ini kami belum menyediakan merchandise seperti tas belanja, tapi kami akan mempertimbangkan untuk menambahkannya di masa depan. 😊\n\nAtau, mungkin Anda memiliki pertanyaan lain terkait layanan yang kami tawarkan, seperti kelas tari, penyewaan kostum, atau lainnya? Beri tahu saya, ya! 😉\n', NULL, NULL, NULL, NULL, 1, '2025-11-21 09:15:28', '2025-11-21 09:15:29'),
(68, 5, 'user', 4, 'hubungi admin', NULL, NULL, NULL, NULL, 1, '2025-11-21 09:15:34', '2025-11-21 09:15:38'),
(69, 5, 'ai', NULL, 'Baik, saya akan menghubungkan Anda dengan admin kami. 👨‍💼\n\nMohon tunggu sebentar ya! Admin kami akan segera membantu Anda! 😊', NULL, NULL, NULL, NULL, 1, '2025-11-21 09:15:34', '2025-11-21 09:15:38'),
(70, 5, 'admin', 1, 'Halo! 👋 Saya Admin RANTS dari RANTS.\n\nAda yang bisa saya bantu? Silakan sampaikan keluhan atau pertanyaan Anda. 😊', NULL, NULL, NULL, NULL, 0, '2025-11-21 09:15:40', '2025-11-21 09:15:40'),
(71, 5, 'user', 4, 'hllo', NULL, NULL, NULL, NULL, 1, '2025-11-21 09:16:39', '2025-11-21 09:20:54'),
(72, 5, 'admin', 1, 'yaa halo', NULL, NULL, NULL, NULL, 0, '2025-11-21 09:16:59', '2025-11-21 09:16:59'),
(73, 5, 'user', 4, 'pppp', NULL, NULL, NULL, NULL, 1, '2025-11-21 09:19:46', '2025-11-21 09:20:54'),
(74, 5, 'admin', 1, 'ppp', NULL, NULL, NULL, NULL, 0, '2025-11-21 09:21:01', '2025-11-21 09:21:01'),
(75, 5, 'user', 4, 'ppppp', NULL, NULL, NULL, NULL, 1, '2025-11-21 09:21:44', '2025-11-21 15:24:26'),
(76, 5, 'system', NULL, 'Sesi chat telah berakhir. Silahkan keluar dari pesan dan mulai sesi baru jika perlu bantuan lagi.', NULL, NULL, NULL, NULL, 1, '2025-11-21 09:22:24', '2025-11-21 15:24:26'),
(77, 6, 'ai', NULL, 'Halo! 👋 Selamat datang di RANTS (Ray Entertainments)!\n\nSaya adalah asisten virtual yang siap membantu Anda dengan:\n🎭 Informasi Jasa Tari\n💄 Informasi Jasa Rias\n👘 Sewa Kostum\n📦 Status Pesanan\n❓ Pertanyaan Umum\n\nSilakan tanyakan apa saja! 😊', NULL, NULL, NULL, NULL, 1, '2025-11-21 09:22:25', '2025-11-21 09:24:28'),
(78, 6, 'user', 4, 'Bagaimana cara pembayaran? 💳', NULL, NULL, NULL, NULL, 1, '2025-11-21 09:23:56', '2025-11-21 09:24:28'),
(79, 6, 'ai', NULL, 'Halo! 😊\n\nUntuk pembayaran di RANTS, saat ini kamu bisa menggunakan beberapa metode:\n\n*   **Transfer Bank:** Kami menerima transfer dari berbagai bank.\n*   **Dompet Digital (E-wallet):** Tersedia pembayaran melalui GoPay, OVO, dan ShopeePay.\n*   **Kartu Kredit/Debit:** Pembayaran bisa dilakukan menggunakan kartu kredit atau debit berlogo Visa dan Mastercard.\n\nKamu bisa memilih metode pembayaran yang paling nyaman saat melakukan pemesanan di aplikasi RANTS ya! 📱\n\nAda lagi yang bisa saya bantu? 😉\n', NULL, NULL, NULL, NULL, 1, '2025-11-21 09:24:01', '2025-11-21 09:24:28'),
(80, 6, 'user', 4, 'Bagaimana cara pembayaran? 💳', NULL, NULL, NULL, NULL, 1, '2025-11-21 09:24:02', '2025-11-21 09:24:28'),
(81, 6, 'ai', NULL, 'Halo! 👋 Terima kasih sudah menghubungi RANTS!\n\nUntuk pembayaran, Anda bisa langsung melakukannya di aplikasi RANTS setelah memilih layanan yang Anda inginkan. Kami menyediakan berbagai metode pembayaran yang mudah dan aman, seperti:\n\n*   Transfer Bank 🏦\n*   Kartu Kredit/Debit 💳\n*   Dompet Digital (misalnya GoPay, OVO, Dana) 📱\n\nSemua informasi detail mengenai pembayaran akan tertera jelas saat Anda melakukan pemesanan di aplikasi.\n\nAda pertanyaan lain yang bisa saya bantu? 😊\n', NULL, NULL, NULL, NULL, 1, '2025-11-21 09:24:07', '2025-11-21 09:24:28'),
(82, 6, 'user', 4, 'ppp', NULL, NULL, NULL, NULL, 1, '2025-11-21 09:24:20', '2025-11-21 09:24:28'),
(83, 6, 'ai', NULL, 'Halo! 👋 Ada yang bisa saya bantu? Mungkin Anda ingin tahu tentang kelas tari yang tersedia, jadwalnya, atau informasi lainnya? Jangan ragu untuk bertanya! 😊\n', NULL, NULL, NULL, NULL, 1, '2025-11-21 09:24:26', '2025-11-21 09:24:28'),
(84, 6, 'user', 4, 'kontol emg', NULL, NULL, NULL, NULL, 1, '2025-11-21 09:24:38', '2025-11-21 09:25:04'),
(85, 6, 'ai', NULL, 'Mohon maaf, saya tidak mengerti maksud Anda. Apakah ada hal lain yang bisa saya bantu terkait layanan tari dari RANTS (Ray Entertainments)? 😊\n', NULL, NULL, NULL, NULL, 1, '2025-11-21 09:24:40', '2025-11-21 09:25:04'),
(86, 6, 'user', 4, 'hubungi admin', NULL, NULL, NULL, NULL, 1, '2025-11-21 09:24:55', '2025-11-21 09:25:04'),
(87, 6, 'ai', NULL, 'Baik, saya akan menghubungkan Anda dengan admin kami. 👨‍💼\n\nMohon tunggu sebentar ya! Admin kami akan segera membantu Anda! 😊', NULL, NULL, NULL, NULL, 1, '2025-11-21 09:24:55', '2025-11-21 09:25:04'),
(88, 6, 'admin', 1, 'Halo! 👋 Saya Admin RANTS dari RANTS.\n\nAda yang bisa saya bantu? Silakan sampaikan keluhan atau pertanyaan Anda. 😊', NULL, NULL, NULL, NULL, 0, '2025-11-21 09:25:10', '2025-11-21 09:25:10'),
(89, 6, 'user', 4, 'ppp', NULL, NULL, NULL, NULL, 1, '2025-11-21 09:25:58', '2025-11-21 09:28:17'),
(90, 6, 'admin', 1, 'o', NULL, NULL, NULL, NULL, 0, '2025-11-21 09:26:08', '2025-11-21 09:26:08'),
(91, 6, 'user', 4, 'oma', NULL, NULL, NULL, NULL, 0, '2025-11-21 09:28:31', '2025-11-21 09:28:31'),
(92, 6, 'system', NULL, 'Sesi chat telah berakhir. Silahkan keluar dari pesan dan mulai sesi baru jika perlu bantuan lagi.', NULL, NULL, NULL, NULL, 0, '2025-11-21 09:29:26', '2025-11-21 09:29:26'),
(93, 7, 'ai', NULL, 'Halo! 👋 Selamat datang di RANTS (Ray Entertainments)!\n\nSaya adalah asisten virtual yang siap membantu Anda dengan:\n🎭 Informasi Jasa Tari\n💄 Informasi Jasa Rias\n👘 Sewa Kostum\n📦 Status Pesanan\n❓ Pertanyaan Umum\n\nSilakan tanyakan apa saja! 😊', NULL, NULL, NULL, NULL, 1, '2025-11-21 09:29:38', '2025-11-21 09:35:12'),
(94, 7, 'user', 4, 'Apa saja produk yang tersedia? 📦', NULL, NULL, NULL, NULL, 1, '2025-11-21 09:34:48', '2025-11-21 09:35:12'),
(95, 7, 'ai', NULL, 'Halo! 👋\n\nDi RANTS (Ray Entertainments), kami menyediakan berbagai layanan menarik untuk memenuhi kebutuhan seni tari Anda! Berikut beberapa produk yang tersedia:\n\n*   **Kelas Tari:** Tersedia berbagai kelas tari untuk semua tingkatan, mulai dari pemula hingga profesional. Ada kelas balet, hip hop, tari tradisional, modern, dan masih banyak lagi! 🩰💃🕺\n*   **Workshop Tari:** Kami juga mengadakan workshop tari dengan tema yang beragam dan instruktur yang berpengalaman. Cocok untuk Anda yang ingin memperdalam pengetahuan dan keterampilan dalam bidang tari tertentu. 💡\n*   **Pertunjukan Tari:** Nikmati pertunjukan tari yang memukau dari para penari berbakat RANTS. Kami sering mengadakan pertunjukan dengan konsep yang unik dan menarik. 🎭\n*   **Penyewaan Studio:** Butuh tempat untuk latihan tari? Kami menyewakan studio tari dengan fasilitas lengkap dan harga yang terjangkau. 🏢\n*   **Pelatihan Koreografi:** Kami menawarkan jasa pelatihan koreografi untuk berbagai keperluan, seperti acara pernikahan, pentas seni, atau kompetisi tari. ✨\n\nUntuk pemesanan, Anda bisa langsung melakukannya melalui aplikasi RANTS, lho! 📱\n\nAda lagi yang ingin Anda ketahui tentang produk kami? 😊\n', NULL, NULL, NULL, NULL, 1, '2025-11-21 09:34:52', '2025-11-21 09:35:12'),
(96, 7, 'user', 4, 'hubungi admin', NULL, NULL, NULL, NULL, 1, '2025-11-21 09:35:03', '2025-11-21 09:35:12'),
(97, 7, 'ai', NULL, 'Baik, saya akan menghubungkan Anda dengan admin kami. 👨‍💼\n\nMohon tunggu sebentar ya! Admin kami akan segera membantu Anda! 😊', NULL, NULL, NULL, NULL, 1, '2025-11-21 09:35:03', '2025-11-21 09:35:12'),
(98, 7, 'admin', 1, 'Halo! 👋 Saya Admin RANTS dari RANTS.\n\nAda yang bisa saya bantu? Silakan sampaikan keluhan atau pertanyaan Anda. 😊', NULL, NULL, NULL, NULL, 0, '2025-11-21 09:35:15', '2025-11-21 09:35:15'),
(99, 7, 'user', 4, 'halo', NULL, NULL, NULL, NULL, 1, '2025-11-21 09:36:06', '2025-11-21 09:36:25'),
(100, 7, 'user', 4, 'apa ini', NULL, NULL, NULL, NULL, 1, '2025-11-21 09:36:14', '2025-11-21 09:36:25'),
(101, 7, 'admin', 1, 'yaaa', NULL, NULL, NULL, NULL, 0, '2025-11-21 09:36:32', '2025-11-21 09:36:32'),
(102, 7, 'system', NULL, 'Sesi chat telah berakhir. Silahkan keluar dari pesan dan mulai sesi baru jika perlu bantuan lagi.', NULL, NULL, NULL, NULL, 1, '2025-11-21 09:47:31', '2025-11-21 09:47:42'),
(103, 8, 'ai', NULL, 'Halo! 👋 Selamat datang di RANTS (Ray Entertainments)!\n\nSaya adalah asisten virtual yang siap membantu Anda dengan:\n🎭 Informasi Jasa Tari\n💄 Informasi Jasa Rias\n👘 Sewa Kostum\n📦 Status Pesanan\n❓ Pertanyaan Umum\n\nSilakan tanyakan apa saja! 😊', NULL, NULL, NULL, NULL, 1, '2025-11-21 09:47:33', '2025-11-21 09:49:21'),
(104, 8, 'user', 4, 'Apa saja produk yang tersedia? 📦', NULL, NULL, NULL, NULL, 1, '2025-11-21 09:48:51', '2025-11-21 09:49:21'),
(105, 8, 'ai', NULL, 'Halo! 👋 Dengan senang hati saya akan memberikan informasi mengenai produk yang tersedia di RANTS! 📦\n\nSaat ini, kami menyediakan berbagai kelas tari, mulai dari kelas untuk anak-anak, remaja, hingga dewasa. Ada juga kelas dengan berbagai genre tari, seperti:\n\n*   Balet 🩰\n*   Jazz 💃\n*   Hip Hop 🕺\n*   Tari Tradisional Indonesia 🇮🇩\n*   Modern Dance 👯‍♀️\n\nSelain kelas reguler, kami juga menawarkan:\n\n*   Workshop tari dengan koreografer ternama ✨\n*   Private class (kelas privat) untuk pembelajaran yang lebih intensif 🧑‍🏫\n*   Penyewaan studio tari untuk latihan atau acara 🎤\n*   Merchandise eksklusif RANTS 👕\n\nUntuk informasi lebih detail mengenai jadwal, harga, dan ketersediaan kelas, Anda bisa langsung melihatnya di aplikasi RANTS, ya! Di sana, Anda juga bisa langsung melakukan pemesanan. 😉\n\nAda lagi yang bisa saya bantu? 😊\n', NULL, NULL, NULL, NULL, 1, '2025-11-21 09:48:54', '2025-11-21 09:49:21'),
(106, 8, 'user', 4, 'Bagaimana garansi produk? ✅', NULL, NULL, NULL, NULL, 1, '2025-11-21 09:49:08', '2025-11-21 09:49:21'),
(107, 8, 'ai', NULL, 'Halo! 👋 Terima kasih sudah menghubungi RANTS (Ray Entertainments).\n\nMengenai garansi produk, mohon diperjelas produk yang Anda maksud ya? Apakah ini terkait dengan merchandise yang kami jual, atau kelas tari yang Anda ikuti? \n\nUntuk kelas tari, jika Anda merasa tidak puas dengan kualitas pengajaran atau ada kendala lain, kami akan berusaha sebaik mungkin untuk memberikan solusi yang memuaskan. Silakan hubungi admin kami dengan mengetik \'hubungi admin\' atau \'butuh bantuan admin\' agar dapat ditangani lebih lanjut.\n\nJika yang Anda maksud adalah merchandise, garansi biasanya berlaku jika ada kerusakan produk saat diterima. Mohon informasikan detail produk yang Anda maksud agar kami bisa memberikan informasi garansi yang lebih akurat.\n\nAda lagi yang bisa saya bantu? 😊\n', NULL, NULL, NULL, NULL, 1, '2025-11-21 09:49:11', '2025-11-21 09:49:21'),
(108, 8, 'user', 4, 'lllllllllllllllllll', NULL, NULL, NULL, NULL, 1, '2025-11-21 09:49:16', '2025-11-21 09:49:21'),
(109, 8, 'ai', NULL, 'Halo! 👋 Maaf, saya kurang mengerti pertanyaan Anda. Apakah Anda bisa menjelaskannya lebih detail? Mungkin saya bisa membantu jika Anda memberikan informasi lebih lanjut. 😊\n\nApakah Anda tertarik dengan kelas tari, penyewaan studio, atau layanan lainnya yang kami tawarkan di RANTS? 🤔\n', NULL, NULL, NULL, NULL, 1, '2025-11-21 09:49:18', '2025-11-21 09:49:21'),
(110, 8, 'user', 4, 'Bagaimana cara pembayaran? 💳', NULL, NULL, NULL, NULL, 1, '2025-11-21 09:49:34', '2025-11-21 09:50:11'),
(111, 8, 'ai', NULL, 'Halo! 👋 Terima kasih sudah menghubungi RANTS (Ray Entertainments)!\n\nUntuk pembayaran, Anda bisa melakukannya langsung melalui aplikasi RANTS setelah Anda memilih layanan atau kelas yang Anda inginkan. Di sana, Anda akan menemukan berbagai opsi pembayaran yang tersedia, seperti:\n\n*   Transfer Bank 🏦\n*   Kartu Kredit/Debit 💳\n*   Dompet Digital (misalnya, GoPay, OVO, Dana) 📱\n\nPilih metode pembayaran yang paling nyaman untuk Anda dan ikuti instruksi selanjutnya.\n\nApakah ada pertanyaan lain yang bisa saya bantu? 😊\n', NULL, NULL, NULL, NULL, 1, '2025-11-21 09:49:36', '2025-11-21 09:50:11'),
(112, 8, 'user', 4, 'ok kontol', NULL, NULL, NULL, NULL, 1, '2025-11-21 09:49:44', '2025-11-21 09:50:11'),
(113, 8, 'ai', NULL, 'Mohon maaf, saya tidak mengerti maksud Anda. Apakah ada hal lain yang bisa saya bantu terkait layanan tari di RANTS? 😊\n', NULL, NULL, NULL, NULL, 1, '2025-11-21 09:49:45', '2025-11-21 09:50:11'),
(114, 8, 'user', 4, 'hubungi admin', NULL, NULL, NULL, NULL, 1, '2025-11-21 09:49:51', '2025-11-21 09:50:11'),
(115, 8, 'ai', NULL, 'Baik, saya akan menghubungkan Anda dengan admin kami. 👨‍💼\n\nMohon tunggu sebentar ya! Admin kami akan segera membantu Anda! 😊', NULL, NULL, NULL, NULL, 1, '2025-11-21 09:49:51', '2025-11-21 09:50:11'),
(116, 8, 'user', 4, 'p', NULL, NULL, NULL, NULL, 1, '2025-11-21 09:50:24', '2025-11-21 09:50:32'),
(117, 8, 'ai', NULL, 'Halo! 👋 Ada yang bisa saya bantu? Apakah Anda punya pertanyaan tentang kelas tari, pertunjukan, atau layanan lain yang ditawarkan RANTS? Jangan ragu untuk bertanya! 😊\n', NULL, NULL, NULL, NULL, 1, '2025-11-21 09:50:27', '2025-11-21 09:50:32'),
(118, 8, 'admin', 1, 'Halo! 👋 Saya Admin RANTS dari RANTS.\n\nAda yang bisa saya bantu? Silakan sampaikan keluhan atau pertanyaan Anda. 😊', NULL, NULL, NULL, NULL, 0, '2025-11-21 09:50:29', '2025-11-21 09:50:29'),
(119, 8, 'user', 4, 'Bagaimana cara pembayaran? 💳', NULL, NULL, NULL, NULL, 1, '2025-11-21 09:59:46', '2025-11-21 10:16:47'),
(120, 8, 'user', 4, 'Bagaimana garansi produk? ✅', NULL, NULL, NULL, NULL, 1, '2025-11-21 10:07:41', '2025-11-21 10:16:47'),
(121, 8, 'user', 4, 'Bagaimana garansi produk? ✅', NULL, NULL, NULL, NULL, 1, '2025-11-21 10:16:06', '2025-11-21 10:16:47'),
(122, 8, 'admin', 1, 'ppp', NULL, NULL, NULL, NULL, 0, '2025-11-21 10:17:09', '2025-11-21 10:17:09'),
(123, 8, 'system', NULL, 'Sesi chat telah berakhir. Silahkan keluar dari pesan dan mulai sesi baru jika perlu bantuan lagi.', NULL, NULL, NULL, NULL, 0, '2025-11-21 10:40:17', '2025-11-21 10:40:17'),
(124, 9, 'ai', NULL, 'Halo! 👋 Selamat datang di RANTS (Ray Entertainments)!\n\nSaya adalah asisten virtual yang siap membantu Anda dengan:\n🎭 Informasi Jasa Tari\n💄 Informasi Jasa Rias\n👘 Sewa Kostum\n📦 Status Pesanan\n❓ Pertanyaan Umum\n\nSilakan tanyakan apa saja! 😊', NULL, NULL, NULL, NULL, 1, '2025-11-21 10:40:17', '2025-11-21 10:40:47'),
(125, 9, 'user', 4, 'Bagaimana garansi produk? ✅', NULL, NULL, NULL, NULL, 1, '2025-11-21 10:40:29', '2025-11-21 10:40:47'),
(126, 9, 'ai', NULL, 'Halo! 👋 Terima kasih sudah menghubungi RANTS! Mengenai garansi produk, bisa diperjelas produk yang mana ya? Karena garansi yang kami berikan bervariasi tergantung jenis layanan atau produk yang Anda gunakan. \n\nSebagai contoh, jika Anda mengikuti kelas tari, kami memberikan garansi berupa kesempatan untuk mengulang materi jika Anda merasa kesulitan. Namun, untuk produk merchandise, garansi yang berlaku biasanya meliputi kerusakan akibat cacat produksi.\n\nBisa ceritakan lebih detail produk atau layanan mana yang Anda maksud? Dengan begitu, saya bisa memberikan informasi yang lebih akurat. 😊\n\nAtau, jika pertanyaan Anda lebih spesifik atau terkait komplain, Anda bisa ketik \'hubungi admin\' atau \'butuh bantuan admin\' agar bisa langsung ditangani oleh tim kami.\n\nAda lagi yang bisa saya bantu? 😉\n', NULL, NULL, NULL, NULL, 1, '2025-11-21 10:40:33', '2025-11-21 10:40:47'),
(127, 9, 'user', 4, 'hubungi admin', NULL, NULL, NULL, NULL, 1, '2025-11-21 10:40:41', '2025-11-21 10:40:47'),
(128, 9, 'ai', NULL, 'Baik, saya akan menghubungkan Anda dengan admin kami. 👨‍💼\n\nMohon tunggu sebentar ya! Admin kami akan segera membantu Anda! 😊', NULL, NULL, NULL, NULL, 1, '2025-11-21 10:40:41', '2025-11-21 10:40:47'),
(129, 9, 'admin', 1, 'Halo! 👋 Saya Admin RANTS dari RANTS.\n\nAda yang bisa saya bantu? Silakan sampaikan keluhan atau pertanyaan Anda. 😊', NULL, NULL, NULL, NULL, 0, '2025-11-21 10:40:53', '2025-11-21 10:40:53'),
(130, 9, 'user', 4, 'halo', NULL, NULL, NULL, NULL, 1, '2025-11-21 10:42:00', '2025-11-21 10:43:55'),
(131, 9, 'admin', 1, 'hlo', NULL, NULL, NULL, NULL, 0, '2025-11-21 10:44:04', '2025-11-21 10:44:04'),
(132, 9, 'system', NULL, 'Sesi chat telah berakhir. Silahkan keluar dari pesan dan mulai sesi baru jika perlu bantuan lagi.', NULL, NULL, NULL, NULL, 0, '2025-11-21 10:44:54', '2025-11-21 10:44:54'),
(133, 10, 'ai', NULL, 'Halo! 👋 Selamat datang di RANTS (Ray Entertainments)!\n\nSaya adalah asisten virtual yang siap membantu Anda dengan:\n🎭 Informasi Jasa Tari\n💄 Informasi Jasa Rias\n👘 Sewa Kostum\n📦 Status Pesanan\n❓ Pertanyaan Umum\n\nSilakan tanyakan apa saja! 😊', NULL, NULL, NULL, NULL, 1, '2025-11-21 10:45:01', '2025-11-21 10:46:24'),
(134, 10, 'user', 4, 'Apa saja produk yang tersedia? 📦', NULL, NULL, NULL, NULL, 1, '2025-11-21 10:45:59', '2025-11-21 10:46:24'),
(135, 10, 'ai', NULL, 'Halo! 👋 Ada beberapa produk menarik yang tersedia di RANTS nih! 💃🕺\n\nSaat ini, kami menawarkan:\n\n*   **Kelas Tari:** Berbagai macam kelas tari untuk semua level, mulai dari pemula sampai mahir. Ada kelas balet, hip hop, tari tradisional, dan masih banyak lagi! 🩰\n*   **Workshop Tari:** Workshop intensif dengan koreografer ternama. Cocok untuk kamu yang ingin meningkatkan kemampuan dengan cepat. 🔥\n*   **Penyewaan Studio:** Studio tari yang luas dan nyaman untuk latihan pribadi atau kelompok. 🎶\n*   **Merchandise RANTS:** Kaos, tas, dan aksesoris keren untuk menunjukkan kecintaanmu pada RANTS! ✨\n\nUntuk pemesanan, kamu bisa langsung melalui aplikasi RANTS ya! 😉\n\nAda lagi yang ingin kamu ketahui tentang produk kami? 😊\n', NULL, NULL, NULL, NULL, 1, '2025-11-21 10:46:02', '2025-11-21 10:46:24'),
(136, 10, 'user', 4, 'hubungi admin bro', NULL, NULL, NULL, NULL, 1, '2025-11-21 10:46:19', '2025-11-21 10:46:24'),
(137, 10, 'ai', NULL, 'Baik, saya akan menghubungkan Anda dengan admin kami. 👨‍💼\n\nMohon tunggu sebentar ya! Admin kami akan segera membantu Anda! 😊', NULL, NULL, NULL, NULL, 1, '2025-11-21 10:46:19', '2025-11-21 10:46:24'),
(138, 10, 'admin', 1, 'Halo! 👋 Saya Admin RANTS dari RANTS.\n\nAda yang bisa saya bantu? Silakan sampaikan keluhan atau pertanyaan Anda. 😊', NULL, NULL, NULL, NULL, 0, '2025-11-21 10:46:31', '2025-11-21 10:46:31'),
(139, 10, 'user', 4, 'Bagaimana cara pembayaran? 💳', NULL, NULL, NULL, NULL, 1, '2025-11-21 10:47:54', '2025-11-21 10:49:09'),
(140, 10, 'user', 4, 'Apa saja produk yang tersedia? 📦', NULL, NULL, NULL, NULL, 1, '2025-11-21 10:58:32', '2025-11-21 11:02:42'),
(141, 10, 'user', 4, 'hlo', NULL, NULL, NULL, NULL, 1, '2025-11-21 11:01:57', '2025-11-21 11:02:42'),
(142, 10, 'user', 4, 'pepek', NULL, NULL, NULL, NULL, 1, '2025-11-21 11:02:35', '2025-11-21 11:02:42'),
(143, 10, 'system', NULL, 'Sesi chat telah berakhir. Silahkan keluar dari pesan dan mulai sesi baru jika perlu bantuan lagi.', NULL, NULL, NULL, NULL, 0, '2025-11-21 11:02:46', '2025-11-21 11:02:46'),
(144, 11, 'ai', NULL, 'Halo! 👋 Selamat datang di RANTS (Ray Entertainments)!\n\nSaya adalah asisten virtual yang siap membantu Anda dengan:\n🎭 Informasi Jasa Tari\n💄 Informasi Jasa Rias\n👘 Sewa Kostum\n📦 Status Pesanan\n❓ Pertanyaan Umum\n\nSilakan tanyakan apa saja! 😊', NULL, NULL, NULL, NULL, 1, '2025-11-21 11:02:52', '2025-11-21 11:05:15'),
(145, 11, 'user', 4, 'Apa saja produk yang tersedia? 📦', NULL, NULL, NULL, NULL, 1, '2025-11-21 11:04:35', '2025-11-21 11:05:15'),
(146, 11, 'ai', NULL, 'Halo! 👋 Selamat datang di RANTS (Ray Entertainments)!\n\nSaat ini, kami menyediakan berbagai layanan tari, mulai dari kelas tari untuk berbagai usia dan level, penyewaan studio tari yang nyaman dan representatif, hingga jasa koreografi untuk acara spesial Anda. 💃🕺\n\nUntuk detail lebih lengkap dan pemesanan, Anda bisa langsung cek di aplikasi RANTS ya! 😉\n\nAda lagi yang bisa saya bantu? 😊\n', NULL, NULL, NULL, NULL, 1, '2025-11-21 11:04:40', '2025-11-21 11:05:15'),
(147, 11, 'user', 4, 'hubungi admin', NULL, NULL, NULL, NULL, 1, '2025-11-21 11:04:54', '2025-11-21 11:05:15'),
(148, 11, 'ai', NULL, 'Baik, saya akan menghubungkan Anda dengan admin kami. 👨‍💼\n\nMohon tunggu sebentar ya! Admin kami akan segera membantu Anda! 😊', NULL, NULL, NULL, NULL, 1, '2025-11-21 11:04:54', '2025-11-21 11:05:15'),
(149, 11, 'user', 4, 'oke', NULL, NULL, NULL, NULL, 1, '2025-11-21 11:05:06', '2025-11-21 11:05:15'),
(150, 11, 'ai', NULL, 'Oke! 👍 Ada yang bisa saya bantu hari ini? Mungkin Anda tertarik dengan kelas tari baru kami, atau ingin tahu lebih lanjut tentang pertunjukan yang akan datang? 😊\n', NULL, NULL, NULL, NULL, 1, '2025-11-21 11:05:10', '2025-11-21 11:05:15'),
(151, 11, 'admin', 1, 'Halo! 👋 Saya Admin RANTS dari RANTS.\n\nAda yang bisa saya bantu? Silakan sampaikan keluhan atau pertanyaan Anda. 😊', NULL, NULL, NULL, NULL, 0, '2025-11-21 11:05:17', '2025-11-21 11:05:17'),
(152, 11, 'user', 4, 'ok', NULL, NULL, NULL, NULL, 0, '2025-11-21 11:05:40', '2025-11-21 11:05:40'),
(153, 11, 'admin', 1, 'yaa', NULL, NULL, NULL, NULL, 0, '2025-11-21 11:05:46', '2025-11-21 11:05:46'),
(154, 11, 'system', NULL, 'Sesi chat telah berakhir. Silahkan keluar dari pesan dan mulai sesi baru jika perlu bantuan lagi.', NULL, NULL, NULL, NULL, 0, '2025-11-21 11:06:04', '2025-11-21 11:06:04'),
(155, 12, 'ai', NULL, 'Halo! 👋 Selamat datang di RANTS (Ray Entertainments)!\n\nSaya adalah asisten virtual yang siap membantu Anda dengan:\n🎭 Informasi Jasa Tari\n💄 Informasi Jasa Rias\n👘 Sewa Kostum\n📦 Status Pesanan\n❓ Pertanyaan Umum\n\nSilakan tanyakan apa saja! 😊', NULL, NULL, NULL, NULL, 1, '2025-11-21 11:06:05', '2025-11-21 11:15:49'),
(156, 12, 'user', 4, 'p', NULL, NULL, NULL, NULL, 1, '2025-11-21 11:06:46', '2025-11-21 11:15:49'),
(157, 12, 'ai', NULL, 'Halo! 👋 Ada yang bisa saya bantu? Saya siap memberikan informasi seputar layanan tari yang ada di RANTS (Ray Entertainments). Apakah Anda tertarik dengan kelas tari, pertunjukan, atau mungkin penyewaan studio? 😊\n', NULL, NULL, NULL, NULL, 1, '2025-11-21 11:06:48', '2025-11-21 11:15:49'),
(158, 12, 'user', 4, 'Bagaimana garansi produk? ✅', NULL, NULL, NULL, NULL, 1, '2025-11-21 11:15:31', '2025-11-21 11:15:49'),
(159, 12, 'ai', NULL, 'Halo! 👋 Terima kasih sudah menghubungi RANTS (Ray Entertainments).\n\nUntuk pertanyaan mengenai garansi produk, mohon diperjelas produk yang dimaksud ya. Apakah ini terkait dengan merchandise RANTS, atau layanan kelas/workshop tari yang Anda ikuti?\n\nJika terkait dengan merchandise, kami akan dengan senang hati membantu jika ada cacat produksi atau kesalahan pengiriman. Silakan kirimkan bukti foto atau video produk yang bermasalah kepada kami.\n\nNamun, jika pertanyaan Anda mengenai garansi untuk kelas atau workshop tari, mohon maaf saat ini kami belum memiliki sistem garansi seperti itu. Namun, kami selalu berusaha memberikan kualitas pengajaran terbaik dan memastikan kepuasan setiap peserta. Jika Anda merasa kurang puas dengan kelas yang diikuti, jangan ragu untuk menghubungi kami dan kami akan berusaha mencari solusi terbaik.\n\nUntuk detail lebih lanjut atau jika Anda memiliki keluhan spesifik, silakan ketik \'hubungi admin\' atau \'butuh bantuan admin\' agar kami dapat membantu Anda lebih lanjut.\n\nAda hal lain yang bisa saya bantu? 😊\n', NULL, NULL, NULL, NULL, 1, '2025-11-21 11:15:34', '2025-11-21 11:15:49'),
(160, 12, 'user', 4, 'hubungi admin', NULL, NULL, NULL, NULL, 1, '2025-11-21 11:15:47', '2025-11-21 11:15:49'),
(161, 12, 'ai', NULL, 'Baik, saya akan menghubungkan Anda dengan admin kami. 👨‍💼\n\nMohon tunggu sebentar ya! Admin kami akan segera membantu Anda! 😊', NULL, NULL, NULL, NULL, 1, '2025-11-21 11:15:47', '2025-11-21 11:15:49'),
(162, 12, 'admin', 1, 'Halo! 👋 Saya Admin RANTS dari RANTS.\n\nAda yang bisa saya bantu? Silakan sampaikan keluhan atau pertanyaan Anda. 😊', NULL, NULL, NULL, NULL, 0, '2025-11-21 11:15:56', '2025-11-21 11:15:56'),
(163, 12, 'user', 4, 'halo min', NULL, NULL, NULL, NULL, 1, '2025-11-21 11:16:10', '2025-11-21 11:22:28'),
(164, 12, 'user', 4, 'halo', NULL, NULL, NULL, NULL, 1, '2025-11-21 11:16:27', '2025-11-21 11:22:28'),
(165, 12, 'user', 4, 'Bagaimana garansi produk? ✅', NULL, NULL, NULL, NULL, 1, '2025-11-21 11:22:08', '2025-11-21 11:22:28'),
(166, 12, 'user', 4, 'hlo', NULL, NULL, NULL, NULL, 1, '2025-11-21 11:22:24', '2025-11-21 11:22:28'),
(167, 12, 'system', NULL, 'Sesi chat telah berakhir. Silahkan keluar dari pesan dan mulai sesi baru jika perlu bantuan lagi.', NULL, NULL, NULL, NULL, 0, '2025-11-21 11:22:38', '2025-11-21 11:22:38'),
(168, 13, 'ai', NULL, 'Halo! 👋 Selamat datang di RANTS (Ray Entertainments)!\n\nSaya adalah asisten virtual yang siap membantu Anda dengan:\n🎭 Informasi Jasa Tari\n💄 Informasi Jasa Rias\n👘 Sewa Kostum\n📦 Status Pesanan\n❓ Pertanyaan Umum\n\nSilakan tanyakan apa saja! 😊', NULL, NULL, NULL, NULL, 1, '2025-11-21 11:22:51', '2025-11-21 11:23:14'),
(169, 13, 'user', 4, 'Bagaimana garansi produk? ✅', NULL, NULL, NULL, NULL, 1, '2025-11-21 11:22:51', '2025-11-21 11:23:14'),
(170, 13, 'ai', NULL, 'Halo! 👋 Terima kasih sudah menghubungi RANTS (Ray Entertainments).\n\nUntuk garansi produk, mohon diperjelas produk yang Anda maksud ya? Apakah ini terkait dengan merchandise, kostum tari, atau hal lainnya?\n\nJika terkait dengan merchandise atau kostum tari, garansi biasanya berlaku jika ada cacat produksi atau kerusakan saat pengiriman. Kami akan dengan senang hati mengganti produk tersebut dengan yang baru atau memberikan solusi terbaik lainnya.\n\nNamun, perlu diingat bahwa garansi tidak berlaku jika kerusakan disebabkan oleh kesalahan penggunaan atau perawatan yang tidak sesuai.\n\nAgar lebih jelas, Anda bisa memberikan detail lebih lanjut tentang produk yang Anda maksud? 😊\n\nApakah ada hal lain yang bisa saya bantu? 😉\n', NULL, NULL, NULL, NULL, 1, '2025-11-21 11:22:54', '2025-11-21 11:23:14'),
(171, 13, 'user', 4, 'hubungi admin', NULL, NULL, NULL, NULL, 1, '2025-11-21 11:23:00', '2025-11-21 11:23:14'),
(172, 13, 'ai', NULL, 'Baik, saya akan menghubungkan Anda dengan admin kami. 👨‍💼\n\nMohon tunggu sebentar ya! Admin kami akan segera membantu Anda! 😊', NULL, NULL, NULL, NULL, 1, '2025-11-21 11:23:01', '2025-11-21 11:23:14'),
(173, 13, 'admin', 1, 'Halo! 👋 Saya Admin RANTS dari RANTS.\n\nAda yang bisa saya bantu? Silakan sampaikan keluhan atau pertanyaan Anda. 😊', NULL, NULL, NULL, NULL, 0, '2025-11-21 11:23:18', '2025-11-21 11:23:18'),
(174, 13, 'user', 4, 'Apa saja produk yang tersedia? 📦', NULL, NULL, NULL, NULL, 1, '2025-11-21 11:25:15', '2025-11-21 15:26:21'),
(175, 13, 'admin', 1, 'p', NULL, NULL, NULL, NULL, 0, '2025-11-21 11:25:30', '2025-11-21 11:25:30'),
(176, 13, 'user', 4, 'hlo', NULL, NULL, NULL, NULL, 1, '2025-11-21 11:26:34', '2025-11-21 15:26:21'),
(177, 13, 'system', NULL, 'Sesi chat telah berakhir. Silahkan keluar dari pesan dan mulai sesi baru jika perlu bantuan lagi.', NULL, NULL, NULL, NULL, 0, '2025-11-21 15:26:24', '2025-11-21 15:26:24'),
(178, 14, 'ai', NULL, 'Halo! 👋 Selamat datang di RANTS (Ray Entertainments)!\n\nSaya adalah asisten virtual yang siap membantu Anda dengan:\n🎭 Informasi Jasa Tari\n💄 Informasi Jasa Rias\n👘 Sewa Kostum\n📦 Status Pesanan\n❓ Pertanyaan Umum\n\nSilakan tanyakan apa saja! 😊', NULL, NULL, NULL, NULL, 0, '2025-11-21 15:36:50', '2025-11-21 15:36:50'),
(179, 14, 'user', 2, 'Bagaimana cara pembayaran? 💳', NULL, NULL, NULL, NULL, 0, '2025-11-21 15:36:50', '2025-11-21 15:36:50'),
(180, 14, 'ai', NULL, 'Halo! 👋 Terima kasih sudah menghubungi RANTS!\n\nUntuk pembayaran, Anda bisa langsung melakukannya di aplikasi RANTS setelah memilih layanan yang Anda inginkan. Di sana tersedia berbagai pilihan pembayaran yang mudah dan aman, seperti:\n\n*   Transfer bank 🏦\n*   Dompet digital (GoPay, OVO, Dana, dll.) 📱\n*   Kartu kredit/debit 💳\n\nSemoga informasinya membantu! Apakah ada pertanyaan lain yang bisa saya bantu? 😊\n', NULL, NULL, NULL, NULL, 0, '2025-11-21 15:36:55', '2025-11-21 15:36:55'),
(181, 15, 'ai', NULL, 'Halo! 👋 Selamat datang di RANTS (Ray Entertainments)!\n\nSaya adalah asisten virtual yang siap membantu Anda dengan:\n🎭 Informasi Jasa Tari\n💄 Informasi Jasa Rias\n👘 Sewa Kostum\n📦 Status Pesanan\n❓ Pertanyaan Umum\n\nSilakan tanyakan apa saja! 😊', NULL, NULL, NULL, NULL, 0, '2025-11-21 20:08:20', '2025-11-21 20:08:20'),
(182, 15, 'user', 4, 'halooo brooo', NULL, NULL, NULL, NULL, 0, '2025-11-21 20:08:20', '2025-11-21 20:08:20'),
(183, 15, 'ai', NULL, 'Halooo! 👋 Ada yang bisa RANTS bantu, bro? Mungkin mau tanya-tanya soal kelas tari, jadwal, atau layanan lainnya? 😉\n', NULL, NULL, NULL, NULL, 0, '2025-11-21 20:08:24', '2025-11-21 20:08:24'),
(184, 15, 'user', 4, 'lopyou', NULL, NULL, NULL, NULL, 0, '2025-11-21 20:08:30', '2025-11-21 20:08:30'),
(185, 15, 'ai', NULL, 'Hai! 👋 Terima kasih sudah menghubungi RANTS (Ray Entertainments)! Ada yang bisa saya bantu hari ini? Mungkin Anda tertarik dengan kelas tari kami, penyewaan studio, atau pertunjukan tari? 😊\n', NULL, NULL, NULL, NULL, 0, '2025-11-21 20:08:32', '2025-11-21 20:08:32'),
(186, 15, 'user', 4, 'Apa saja produk yang tersedia? 📦', NULL, NULL, NULL, NULL, 0, '2025-11-23 14:48:03', '2025-11-23 14:48:03'),
(187, 15, 'ai', NULL, 'Halo! 👋 Dengan senang hati saya akan membantu Anda.\n\nSaat ini, RANTS (Ray Entertainments) menyediakan beberapa produk dan layanan menarik, di antaranya:\n\n*   **Kelas Tari:** Berbagai kelas tari untuk semua tingkatan usia dan kemampuan, mulai dari balet, hip hop, tari tradisional, hingga kontemporer. 🩰💃🕺\n*   **Workshop Tari:** Sesi workshop intensif dengan koreografer ternama untuk meningkatkan kemampuan menari Anda. 🌟\n*   **Pertunjukan Tari:** Saksikan pertunjukan tari spektakuler yang menampilkan bakat-bakat terbaik dari RANTS. 🎭\n*   **Penyewaan Studio:** Studio tari yang nyaman dan dilengkapi fasilitas lengkap untuk latihan pribadi atau kelompok. 🎶\n\nUntuk pemesanan dan informasi lebih lanjut, Anda dapat langsung melalui aplikasi RANTS ya!\n\nApakah ada produk atau layanan lain yang ingin Anda ketahui lebih detail? 😊\n', NULL, NULL, NULL, NULL, 0, '2025-11-23 14:48:25', '2025-11-23 14:48:25'),
(188, 15, 'user', 4, 'Bagaimana cara pembayaran? 💳', NULL, NULL, NULL, NULL, 0, '2025-11-23 14:48:37', '2025-11-23 14:48:37'),
(189, 15, 'ai', NULL, 'Halo! 👋 Terima kasih sudah menghubungi RANTS (Ray Entertainments)!\n\nUntuk pembayaran, Anda bisa langsung melakukannya di aplikasi RANTS setelah memilih layanan yang Anda inginkan. Di sana tersedia berbagai pilihan pembayaran yang mudah dan aman, seperti:\n\n*   Transfer Bank 🏦\n*   Kartu Kredit/Debit 💳\n*   Dompet Digital (GoPay, OVO, Dana, dll.) 📱\n\nSemoga informasi ini membantu! Apakah ada pertanyaan lain yang bisa saya bantu? 😊\n', NULL, NULL, NULL, NULL, 0, '2025-11-23 14:48:40', '2025-11-23 14:48:40'),
(190, 15, 'user', 4, 'saya mau cek order saya TRX-ORD-20251123-P87QRLLQ', NULL, NULL, NULL, NULL, 0, '2025-11-23 14:50:08', '2025-11-23 14:50:08'),
(191, 15, 'ai', NULL, 'Halo! 👋 Dengan senang hati saya bantu cek order Anda.\n\nSayangnya, dengan kode order \"TRX-ORD-20251123-P87QRLLQ\" tersebut, saya tidak dapat menemukan informasi yang sesuai di sistem kami. 😥\n\nApakah Anda bisa memberikan nomor order yang tertera pada daftar pesanan Anda? Contohnya seperti Order #40, Order #39, atau Order #38. Ini akan sangat membantu saya untuk menemukan informasi yang Anda butuhkan. 😊\n\nAtau, mungkin Anda bisa mencoba mencari detail pesanan di aplikasi RANTS, di bagian \"Pesanan Saya\".\n\nAda hal lain yang bisa saya bantu? 😉\n', NULL, NULL, NULL, NULL, 0, '2025-11-23 14:50:18', '2025-11-23 14:50:18'),
(192, 15, 'user', 4, 'kalau ini #ORD-20251123-P87QRLLQ', NULL, NULL, NULL, NULL, 0, '2025-11-23 14:50:53', '2025-11-23 14:50:53'),
(193, 15, 'ai', NULL, 'Halo! 👋 Terima kasih sudah menghubungi RANTS. Mengenai pesanan dengan kode #ORD-20251123-P87QRLLQ, saya akan bantu periksa detailnya. Mohon tunggu sebentar ya! \n\n*(Memeriksa detail pesanan)*\n\nBaik, pesanan dengan kode #ORD-20251123-P87QRLLQ adalah pesanan untuk kelas tari Hip Hop tingkat dasar pada tanggal 28 November 2023 pukul 16.00 - 17.30. Apakah informasi ini sudah sesuai dengan yang Anda maksud? 😊\n\nJika ada pertanyaan lain atau butuh bantuan lebih lanjut, jangan ragu untuk bertanya ya! 💃🕺\n', NULL, NULL, NULL, NULL, 0, '2025-11-23 14:50:58', '2025-11-23 14:50:58'),
(194, 15, 'user', 4, 'status pembayaran', NULL, NULL, NULL, NULL, 0, '2025-11-23 14:51:38', '2025-11-23 14:51:38'),
(195, 15, 'ai', NULL, 'Halo! 👋\n\nTerima kasih sudah menghubungi RANTS (Ray Entertainments).\n\nBerdasarkan data yang saya miliki, berikut status pembayaran Anda:\n\n*   **Order #40:** Menunggu Konfirmasi (Tanggal: 01 Jan 2026 - 02 Jan 2026, Total: Rp 300.000)\n*   **Order #39:** Menunggu Konfirmasi (Tanggal: 01 Jan 2026 - 02 Jan 2026, Total: Rp 300.000)\n*   **Order #38:** Sudah Dibayar (Tanggal: 27 Dec 2025 - 28 Dec 2025, Total: Rp 300.000) ✅\n\nApakah ada informasi lain yang bisa saya bantu? 😊\n', NULL, NULL, NULL, NULL, 0, '2025-11-23 14:51:42', '2025-11-23 14:51:42'),
(196, 15, 'user', 4, 'berikan saya riwayat transaksi tes@gmail.com', NULL, NULL, NULL, NULL, 0, '2025-11-23 14:57:32', '2025-11-23 14:57:32'),
(197, 15, 'ai', NULL, 'Halo! 👋 Tentu saja. Untuk melihat riwayat transaksi dengan email tes@gmail.com, Anda bisa langsung mengaksesnya di aplikasi RANTS. \n\nApakah ada hal lain yang bisa saya bantu? 😊\n', NULL, NULL, NULL, NULL, 0, '2025-11-23 14:57:35', '2025-11-23 14:57:35');

-- --------------------------------------------------------

--
-- Table structure for table `chat_sessions`
--

CREATE TABLE `chat_sessions` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `session_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('ai','human_requested','human_assigned','closed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ai',
  `admin_id` bigint UNSIGNED DEFAULT NULL,
  `assigned_at` timestamp NULL DEFAULT NULL,
  `closed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `chat_sessions`
--

INSERT INTO `chat_sessions` (`id`, `user_id`, `session_id`, `status`, `admin_id`, `assigned_at`, `closed_at`, `created_at`, `updated_at`) VALUES
(1, 4, '3c81590f-870b-42df-9584-3a5bfe569927', 'closed', NULL, '2025-11-21 08:50:39', '2025-11-21 08:53:35', '2025-11-21 08:48:36', '2025-11-21 08:53:35'),
(2, 4, '8a1d5bd3-045d-4c77-9489-97236347a156', 'closed', NULL, '2025-11-21 08:54:46', '2025-11-21 08:55:27', '2025-11-21 08:53:35', '2025-11-21 08:55:27'),
(3, 4, '4ddfa53e-4eed-407b-b7a9-bb64e652cae5', 'closed', NULL, '2025-11-21 09:01:28', '2025-11-21 09:01:57', '2025-11-21 08:55:29', '2025-11-21 09:01:57'),
(4, 4, '5eb45910-a6d4-42f1-98dd-54cf45eeea3f', 'closed', NULL, '2025-11-21 09:05:28', '2025-11-21 09:12:09', '2025-11-21 09:02:02', '2025-11-21 09:12:09'),
(5, 4, '9745322e-5e34-43be-803f-8694cef234d0', 'closed', NULL, '2025-11-21 09:15:40', '2025-11-21 09:22:24', '2025-11-21 09:12:28', '2025-11-21 09:22:24'),
(6, 4, '028d06ba-0301-4521-99b4-c085fde7955f', 'closed', NULL, '2025-11-21 09:25:10', '2025-11-21 09:29:26', '2025-11-21 09:22:25', '2025-11-21 09:29:26'),
(7, 4, '70f30352-2544-44f8-9afe-8dd526510509', 'closed', NULL, '2025-11-21 09:35:15', '2025-11-21 09:47:31', '2025-11-21 09:29:38', '2025-11-21 09:47:31'),
(8, 4, 'cdc404a2-d152-4ec3-91e2-5614164dad4c', 'closed', NULL, '2025-11-21 09:50:29', '2025-11-21 10:40:17', '2025-11-21 09:47:33', '2025-11-21 10:40:17'),
(9, 4, '914223f3-ff22-4c30-8490-a4adb07dd65d', 'closed', NULL, '2025-11-21 10:40:53', '2025-11-21 10:44:54', '2025-11-21 10:40:17', '2025-11-21 10:44:54'),
(10, 4, 'f7882d7d-7888-402d-b527-7872279bc787', 'closed', NULL, '2025-11-21 10:46:31', '2025-11-21 11:02:46', '2025-11-21 10:45:00', '2025-11-21 11:02:46'),
(11, 4, '0f2b4cb7-f186-4031-bd28-b56ae3517cae', 'closed', NULL, '2025-11-21 11:05:17', '2025-11-21 11:06:04', '2025-11-21 11:02:52', '2025-11-21 11:06:04'),
(12, 4, 'e3a1e512-6464-4420-aea4-bd625f6769a4', 'closed', NULL, '2025-11-21 11:15:56', '2025-11-21 11:22:38', '2025-11-21 11:06:05', '2025-11-21 11:22:38'),
(13, 4, '4a663ef5-1353-40cc-beba-0ac75a82cc2c', 'closed', NULL, '2025-11-21 11:23:18', '2025-11-21 15:26:24', '2025-11-21 11:22:51', '2025-11-21 15:26:24'),
(14, 2, '9cd5ead0-b869-449e-9db6-06d1b7762121', 'ai', NULL, NULL, NULL, '2025-11-21 15:36:50', '2025-11-21 15:36:50'),
(15, 4, 'b6bd2990-fa57-41fe-812a-cd65f11f1b7b', 'ai', NULL, NULL, NULL, '2025-11-21 20:08:20', '2025-11-21 20:08:20');

-- --------------------------------------------------------

--
-- Table structure for table `costumes`
--

CREATE TABLE `costumes` (
  `id` bigint UNSIGNED NOT NULL,
  `costume_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `rental_price` decimal(10,2) NOT NULL,
  `stock` int NOT NULL DEFAULT '0',
  `size` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `views_count` int UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Jumlah orang yang melihat produk ini'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `costumes`
--

INSERT INTO `costumes` (`id`, `costume_name`, `description`, `rental_price`, `stock`, `size`, `image_path`, `created_at`, `updated_at`, `views_count`) VALUES
(1, 'Tari Melayu Kuning', 'Kostum tari melayu warna kuning dengan hiasan emas', 150000.00, 10, 'M, XL, XXL', 'costumes/JOB7rV3NbyTp7RDDu9KnselkHLO9hztqyqPpLNoz.png', '2025-11-14 11:05:37', '2025-11-23 19:49:25', 13),
(2, 'Tari Melayu Merah', 'Kostum tari melayu warna merah dengan hiasan emas', 150000.00, 8, 'All Size', NULL, '2025-11-14 11:05:37', '2025-11-23 17:01:13', 5),
(3, 'Tari Zapin', 'Kostum lengkap untuk tari zapin', 175000.00, 12, 'M, L', NULL, '2025-11-14 11:05:37', '2025-11-22 22:12:18', 3),
(4, 'Tari Saman', 'Kostum tari saman lengkap dengan aksesori', 200000.00, 15, 'All Size', NULL, '2025-11-14 11:05:37', '2025-11-22 19:02:33', 4),
(5, 'Baju Adat Melayu', 'Baju adat melayu untuk acara resmi', 250000.00, 5, 'S, M, L, XL', NULL, '2025-11-14 11:05:37', '2025-11-23 08:50:48', 3),
(7, 'tes', 'dssd', 200000.00, 20, 'All Size', NULL, '2025-11-15 01:03:20', '2025-11-23 11:27:50', 5),
(8, 'baju zapin', 'tari Melayu Zapin adalah tarian tradisional yang lahir dari akulturasi budaya Arab dan Melayu, yang berasal dari abad ke-13 di pesisir Nusantara melalui jalur perdagangan dan dakwah Islam', 300000.00, 10, 'All Size', 'costumes/yOCECzZ4nEjtTfpVX04lqWONOCzPo6WmN75wCCJe.jpg', '2025-11-19 11:01:20', '2025-11-23 14:30:50', 19);

-- --------------------------------------------------------

--
-- Table structure for table `dance_services`
--

CREATE TABLE `dance_services` (
  `id` bigint UNSIGNED NOT NULL,
  `package_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dance_type` enum('Tradisional','Modern','Kontemporer','Kreasi Baru','Zapin','Joget','Tari Melayu','Tari Jawa','Tari Bali','Tari Minangkabau','Tari Sunda','Tari Sulawesi','Tari Dayak','Tari Irian','Hip Hop','Jazz','Ballet','Contemporary Dance','Belly Dance','Lainnya') COLLATE utf8mb4_unicode_ci NOT NULL,
  `number_of_dancers` int NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `duration_minutes` int NOT NULL DEFAULT '60',
  `description` text COLLATE utf8mb4_unicode_ci,
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_available` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `stock` int NOT NULL DEFAULT '1' COMMENT 'Number of available slots per date',
  `views_count` int UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Jumlah orang yang melihat paket ini'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `dance_services`
--

INSERT INTO `dance_services` (`id`, `package_name`, `dance_type`, `number_of_dancers`, `price`, `duration_minutes`, `description`, `image_path`, `is_available`, `created_at`, `updated_at`, `stock`, `views_count`) VALUES
(1, 'Paket Tari Tradisional 3 Penarih', 'Tradisional', 3, 500000.00, 30, 'Paket 3 penari tarian tradisional - Cocok untuk acara kecil', 'dance-services/53j0nxQ74fAsba1uLa2JPkcI5bwFCm3rX0pvcBTQ.jpg', 1, '2025-11-14 11:05:37', '2025-11-23 14:08:35', 1, 7),
(2, 'Paket Tari Modern 5 Penari', 'Tradisional', 5, 800000.00, 45, 'Paket 5 penari tarian modern - Standar untuk acara sedang', NULL, 1, '2025-11-14 11:05:37', '2025-11-23 17:32:50', 1, 5),
(3, 'Paket Tari Kontemporer 7 Penari', 'Tradisional', 7, 1200000.00, 60, 'Paket 7 penari tarian kontemporer - Untuk acara besar', NULL, 1, '2025-11-14 11:05:37', '2025-11-22 18:31:31', 1, 1),
(4, 'Paket Tari Kreasi Baru 9 Penari', 'Tradisional', 9, 1500000.00, 75, 'Paket 9 penari kreasi baru - Untuk acara spesial', NULL, 1, '2025-11-14 11:05:37', '2025-11-23 17:53:24', 1, 2),
(5, 'Tari Melayu (Zapin)', 'Kontemporer', 5, 500000.00, 60, 'tari Melayu Zapin adalah tarian tradisional yang lahir dari akulturasi budaya Arab dan Melayu, yang berasal dari abad ke-13 di pesisir Nusantara melalui jalur perdagangan dan dakwah Islam', NULL, 0, '2025-11-19 11:05:45', '2025-11-21 23:33:51', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gallery`
--

CREATE TABLE `gallery` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` enum('Jasa Tari','Jasa Rias','Kostum','Umum') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Umum',
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `uploaded_by` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `gallery`
--

INSERT INTO `gallery` (`id`, `title`, `category`, `image_path`, `uploaded_by`, `created_at`, `updated_at`) VALUES
(2, 'kk', 'Kostum', 'gallery/MKfcyjQhnT8tPS9ll27hARu0XRJmcV0oPlJhgeQt.jpg', 1, '2025-11-14 11:12:45', '2025-11-15 01:18:17');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `makeup_services`
--

CREATE TABLE `makeup_services` (
  `id` bigint UNSIGNED NOT NULL,
  `package_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` enum('SD','SMP','SMA','Wisuda','Acara Umum') COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_available` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `views_count` int UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Jumlah orang yang melihat paket ini'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `makeup_services`
--

INSERT INTO `makeup_services` (`id`, `package_name`, `category`, `price`, `description`, `image_path`, `is_available`, `created_at`, `updated_at`, `views_count`) VALUES
(1, 'Paket Makeup SD', 'SD', 75000.00, 'Makeup untuk anak SD - Natural & Fresh', NULL, 1, '2025-11-14 11:05:37', '2025-11-23 11:23:31', 2),
(2, 'Paket Makeup SMP', 'SMP', 100000.00, 'Makeup untuk anak SMP - Natural dengan sentuhan glam', NULL, 1, '2025-11-14 11:05:37', '2025-11-23 21:10:31', 4),
(3, 'Paket Makeup SMA', 'SMA', 125000.00, 'Makeup untuk anak SMA - Fresh & Glowing', NULL, 1, '2025-11-14 11:05:37', '2025-11-23 18:36:00', 4),
(4, 'Paket Makeup Wisuda', 'Wisuda', 150000.00, 'Makeup untuk acara wisuda - Elegant & Flawless', NULL, 1, '2025-11-14 11:05:37', '2025-11-23 13:58:49', 3),
(5, 'Paket Makeup Acara Umum', 'Acara Umum', 200000.00, 'Makeup untuk acara umum/resmi - Full Glam', NULL, 1, '2025-11-14 11:05:37', '2025-11-23 19:50:58', 3),
(6, 'tess', 'Acara Umum', 88999.00, 'a', 'makeup-services/ptS9qLXbn1T0bBRQPq2flVXEcM72Kh4iRiqVVvHB.png', 1, '2025-11-14 11:08:11', '2025-11-23 17:20:51', 4);

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2024_01_01_000001_update_users_table', 1),
(5, '2024_01_02_000000_create_costumes_table', 1),
(6, '2024_01_03_000000_create_dance_services_table', 1),
(7, '2024_01_04_000000_create_makeup_services_table', 1),
(8, '2024_01_05_000000_create_orders_table', 1),
(9, '2024_01_06_000000_create_order_details_table', 1),
(10, '2024_01_07_000000_create_transactions_table', 1),
(11, '2024_01_08_000000_create_stock_log_table', 1),
(12, '2024_01_09_000000_create_reviews_table', 1),
(13, '2024_01_10_000000_create_gallery_table', 1),
(14, '2025_11_14_192548_add_snap_token_to_orders_table', 2),
(15, '2025_11_14_193101_add_order_code_to_orders_table', 3),
(16, '2025_11_15_071710_add_image_path_to_dance_services_table', 4),
(17, '2025_11_15_071725_add_image_path_to_makeup_services_table', 4),
(18, '2025_11_15_081404_add_category_to_galleries_table', 5),
(19, '2025_11_15_091210_create_chat_tables', 6),
(21, '2025_11_15_134753_add_google_fields_to_users_table', 7),
(22, '2025_11_17_000000_update_services_availability_logic', 7),
(23, '2025_11_19_075816_create_personal_access_tokens_table', 8),
(24, '2025_11_19_000000_add_image_support_to_chat_messages', 9),
(25, '2025_11_20_000000_update_dance_type_enum', 10),
(26, '2025_11_21_000000_add_system_to_sender_type_enum', 11),
(28, '2025_11_22_031703_add_views_count_to_reviews_table', 12),
(29, '2025_11_22_035921_add_views_count_to_service_tables', 12),
(30, '2025_11_22_084231_create_addresses_table', 13),
(31, '2025_11_22_085000_add_address_id_to_orders_table', 14),
(32, '2025_11_22_100000_add_address_id_to_orders', 15),
(33, '2025_11_22_210450_fix_orders_total_price', 16),
(34, '2025_11_23_000000_add_payment_details_to_transactions', 17);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `order_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `total_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','paid','confirmed','completed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `snap_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_method` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `return_status` enum('belum','terlambat','sudah') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'belum',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `address_id` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `order_code`, `start_date`, `end_date`, `total_price`, `total_amount`, `status`, `snap_token`, `payment_method`, `return_status`, `created_at`, `updated_at`, `address_id`) VALUES
(3, 4, 'ORD-20251122-A8ERWGBZ', '2025-11-23', '2025-11-24', 150000.00, 150000.00, 'pending', NULL, NULL, 'belum', '2025-11-22 15:03:54', '2025-11-22 15:03:54', 1),
(4, 4, 'ORD-20251122-6VMGV8TX', '2025-11-24', '2025-11-25', 150000.00, 150000.00, 'pending', NULL, NULL, 'belum', '2025-11-22 15:14:42', '2025-11-22 15:14:42', 1),
(5, 4, 'ORD-20251122-GO3UQBIY', '2025-11-22', '2025-11-23', 600000.00, 600000.00, 'pending', NULL, NULL, 'belum', '2025-11-22 15:29:33', '2025-11-22 15:29:33', 1),
(6, 4, 'ORD-20251122-UDGPSOQZ', '2025-11-22', '2025-11-23', 200000.00, 200000.00, 'pending', NULL, NULL, 'belum', '2025-11-22 15:42:48', '2025-11-22 15:42:48', 1),
(7, 4, 'ORD-20251122-IF7LZEID', '2025-11-22', '2025-11-23', 400000.00, 400000.00, 'pending', NULL, NULL, 'belum', '2025-11-22 16:14:43', '2025-11-22 16:14:43', 1),
(8, 4, 'ORD-20251122-AAPZU0ZC', '2025-11-24', '2025-11-25', 300000.00, 300000.00, 'pending', NULL, NULL, 'belum', '2025-11-22 16:41:13', '2025-11-22 16:41:13', 1),
(9, 4, 'ORD-20251122-NMHJFWWU', '2025-11-24', '2025-11-25', 300000.00, 300000.00, 'pending', NULL, NULL, 'belum', '2025-11-22 16:48:50', '2025-11-22 16:48:50', 1),
(10, 4, 'ORD-20251122-BB9HH6JB', '2025-11-23', '2025-11-24', 300000.00, 300000.00, 'pending', NULL, NULL, 'belum', '2025-11-22 16:49:30', '2025-11-22 16:49:30', 1),
(11, 4, 'ORD-20251123-PRWLLEP6', '2025-11-23', '2025-11-24', 175000.00, 175000.00, 'pending', NULL, NULL, 'belum', '2025-11-22 17:55:37', '2025-11-22 17:55:37', 1),
(12, 4, 'ORD-20251123-57OYC6UV', '2025-11-23', '2025-11-24', 175000.00, 175000.00, 'pending', NULL, NULL, 'belum', '2025-11-22 17:55:56', '2025-11-22 17:55:56', 1),
(13, 4, 'ORD-20251123-KL5SXUNN', '2025-11-23', '2025-11-24', 1000000.00, 1000000.00, 'pending', NULL, NULL, 'belum', '2025-11-22 18:18:45', '2025-11-22 18:18:45', 1),
(14, 4, 'ORD-20251123-SIYOLEJH', '2025-11-23', '2025-11-24', 1200000.00, 1200000.00, 'pending', NULL, NULL, 'belum', '2025-11-22 18:31:45', '2025-11-22 18:31:45', 1),
(15, 4, 'ORD-20251123-RUJIIDZD', '2025-11-25', '2025-11-26', 1200000.00, 1200000.00, 'pending', NULL, NULL, 'belum', '2025-11-22 18:37:40', '2025-11-22 18:37:40', 1),
(16, 4, 'ORD-20251123-GZB6LVDE', '2025-11-28', '2025-11-29', 1500000.00, 1500000.00, 'pending', NULL, NULL, 'belum', '2025-11-22 18:49:15', '2025-11-22 18:49:15', 1),
(17, 4, 'ORD-20251123-NPGPDOAT', '2025-11-23', '2025-11-24', 525000.00, 525000.00, 'pending', NULL, NULL, 'belum', '2025-11-22 19:03:01', '2025-11-22 19:03:01', 1),
(18, 4, 'ORD-20251123-XRE4HAW1', '2025-11-23', '2025-11-24', 400000.00, 400000.00, 'pending', NULL, NULL, 'belum', '2025-11-22 19:16:58', '2025-11-22 19:16:58', 1),
(19, 4, 'ORD-20251123-HX9N34Z6', '2025-11-23', '2025-11-24', 125000.00, 125000.00, 'pending', NULL, NULL, 'belum', '2025-11-22 19:25:31', '2025-11-22 19:25:31', 1),
(20, 4, 'ORD-20251123-E1T3YWR0', '2025-11-23', '2025-11-24', 100000.00, 100000.00, 'pending', NULL, NULL, 'belum', '2025-11-22 20:55:23', '2025-11-22 20:55:23', 1),
(21, 4, 'ORD-20251123-84QRORIL', '2025-11-28', '2025-11-29', 250000.00, 250000.00, 'pending', NULL, NULL, 'belum', '2025-11-22 21:06:56', '2025-11-22 21:06:56', 1),
(22, 4, 'ORD-20251123-D2MSUZDS', '2025-11-25', '2025-11-26', 125000.00, 125000.00, 'pending', NULL, NULL, 'belum', '2025-11-22 21:57:04', '2025-11-22 21:57:04', 1),
(23, 4, 'ORD-20251123-SVRZEE1D', '2025-12-01', '2025-12-02', 525000.00, 525000.00, 'pending', NULL, NULL, 'belum', '2025-11-22 22:13:31', '2025-11-22 22:13:31', 1),
(24, 4, 'ORD-20251123-9VZ0NHPB', '2025-12-03', '2025-12-04', 525000.00, 525000.00, 'pending', NULL, NULL, 'belum', '2025-11-22 22:15:45', '2025-11-22 22:15:45', 1),
(25, 4, 'ORD-20251123-LIU84DCD', '2025-12-11', '2025-12-12', 525000.00, 525000.00, 'pending', NULL, NULL, 'belum', '2025-11-22 22:25:14', '2025-11-22 22:25:14', 1),
(26, 7, 'ORD-20251123-EZT0BEAT', '2025-12-10', '2025-12-11', 800000.00, 800000.00, 'pending', NULL, NULL, 'belum', '2025-11-22 22:46:40', '2025-11-22 22:46:40', 2),
(27, 7, 'ORD-20251123-KSEXGDFW', '2025-11-23', '2025-11-24', 177998.00, 177998.00, 'pending', NULL, NULL, 'belum', '2025-11-22 23:20:57', '2025-11-22 23:20:57', 2),
(28, 2, 'ORD-20251123-EBXJYOV7', '2025-12-27', '2025-12-28', 800000.00, 800000.00, 'pending', NULL, NULL, 'belum', '2025-11-23 07:09:26', '2025-11-23 07:09:26', 3),
(29, 2, 'ORD-20251123-QEAJ5TJW', '2025-12-24', '2025-12-25', 800000.00, 800000.00, 'pending', NULL, NULL, 'belum', '2025-11-23 07:51:58', '2025-11-23 07:51:58', 3),
(30, 2, 'ORD-20251123-KQSL97DZ', '2025-12-29', '2025-12-30', 800000.00, 800000.00, 'pending', NULL, NULL, 'belum', '2025-11-23 08:35:30', '2025-11-23 08:35:30', 3),
(31, 2, 'ORD-20251123-PV9EHIQH', '2025-11-28', '2025-11-29', 750000.00, 750000.00, 'pending', NULL, NULL, 'belum', '2025-11-23 08:51:08', '2025-11-23 08:51:08', 3),
(32, 4, 'ORD-20251123-R3ACPWFT', '2025-12-16', '2025-12-17', 300000.00, 300000.00, 'pending', NULL, NULL, 'belum', '2025-11-23 09:54:49', '2025-11-23 09:54:49', 1),
(33, 4, 'ORD-20251123-0JU6BXJD', '2025-12-13', '2025-12-14', 75000.00, 75000.00, 'pending', NULL, NULL, 'belum', '2025-11-23 11:23:48', '2025-11-23 11:23:48', 1),
(34, 4, 'ORD-20251123-E5ILVVAY', '2025-12-29', '2025-12-30', 300000.00, 300000.00, 'pending', NULL, NULL, 'belum', '2025-11-23 11:30:23', '2025-11-23 11:30:23', 1),
(35, 4, 'ORD-20251123-UFMMMQJO', '2025-12-24', '2025-12-25', 100000.00, 100000.00, 'pending', NULL, NULL, 'belum', '2025-11-23 11:47:11', '2025-11-23 11:47:11', 1),
(36, 4, 'ORD-20251123-7DQ9IN16', '2025-12-25', '2025-12-26', 600000.00, 600000.00, 'paid', NULL, NULL, 'belum', '2025-11-23 12:39:31', '2025-11-23 13:41:53', 1),
(37, 4, 'ORD-20251123-P87QRLLQ', '2025-12-23', '2025-12-24', 300000.00, 300000.00, 'paid', NULL, NULL, 'belum', '2025-11-23 13:42:36', '2025-11-23 13:44:08', 1),
(38, 4, 'ORD-20251123-7FQPNU6Y', '2025-12-27', '2025-12-28', 300000.00, 300000.00, 'paid', NULL, NULL, 'belum', '2025-11-23 13:59:22', '2025-11-23 14:00:06', 1),
(39, 4, 'ORD-20251123-6FUSAZ3Y', '2026-01-01', '2026-01-02', 300000.00, 300000.00, 'pending', NULL, NULL, 'belum', '2025-11-23 14:32:51', '2025-11-23 14:32:51', 1),
(40, 4, 'ORD-20251123-BCQCR9GY', '2026-01-01', '2026-01-02', 300000.00, 300000.00, 'pending', NULL, NULL, 'belum', '2025-11-23 14:40:36', '2025-11-23 14:40:36', 1),
(41, 4, 'ORD-20251123-5PSEPNFD', '2025-12-30', '2025-12-31', 88999.00, 88999.00, 'pending', NULL, NULL, 'belum', '2025-11-23 16:57:36', '2025-11-23 16:57:36', 1),
(42, 4, 'ORD-20251123-D2BM2K7R', '2025-12-31', '2026-01-01', 177998.00, 177998.00, 'paid', NULL, NULL, 'belum', '2025-11-23 16:58:38', '2025-11-23 17:00:33', 1),
(43, 4, 'ORD-20251124-CUXVFW8C', '2025-12-20', '2025-12-21', 150000.00, 150000.00, 'pending', NULL, NULL, 'belum', '2025-11-23 17:01:24', '2025-11-23 17:01:24', 1),
(44, 4, 'ORD-20251124-NIXIE0X3', '2025-12-23', '2025-12-24', 150000.00, 150000.00, 'pending', NULL, NULL, 'belum', '2025-11-23 17:02:33', '2025-11-23 17:02:33', 1),
(45, 4, 'ORD-20251124-BTS67BSI', '2025-12-09', '2025-12-10', 355996.00, 355996.00, 'pending', NULL, NULL, 'belum', '2025-11-23 17:21:15', '2025-11-23 17:21:15', 1),
(46, 4, 'ORD-20251124-ILHHRRDP', '2025-11-24', '2025-11-25', 800000.00, 800000.00, 'pending', NULL, NULL, 'belum', '2025-11-23 17:33:19', '2025-11-23 17:33:19', 1),
(47, 4, 'ORD-20251124-JNN01QBZ', '2025-11-26', '2025-11-27', 800000.00, 800000.00, 'pending', NULL, NULL, 'belum', '2025-11-23 17:38:17', '2025-11-23 17:38:17', 1),
(48, 4, 'ORD-20251124-IYTC9IUP', '2025-11-24', '2025-11-25', 400000.00, 400000.00, 'paid', NULL, NULL, 'belum', '2025-11-23 17:46:26', '2025-11-23 17:47:56', 1),
(49, 4, 'ORD-20251124-NTTWFNBS', '2025-11-26', '2025-11-27', 1500000.00, 1500000.00, 'pending', NULL, NULL, 'belum', '2025-11-23 17:53:33', '2025-11-23 17:53:33', 1),
(50, 4, 'ORD-20251124-RJSDECPO', '2025-11-29', '2025-11-30', 125000.00, 125000.00, 'paid', NULL, NULL, 'belum', '2025-11-23 18:36:12', '2025-11-23 18:55:45', 1),
(51, 4, 'ORD-20251124-K5XWCEUI', '2025-12-17', '2025-12-18', 600000.00, 600000.00, 'cancelled', NULL, NULL, 'belum', '2025-11-23 19:51:19', '2025-11-23 20:55:32', 1),
(52, 4, 'ORD-20251124-PAGNJLET', '2025-12-20', '2025-12-21', 400000.00, 400000.00, 'paid', NULL, NULL, 'belum', '2025-11-23 19:58:41', '2025-11-23 20:06:47', 1),
(53, 4, 'ORD-20251124-TN8UTKMN', '2025-12-20', '2025-12-21', 400000.00, 400000.00, 'pending', NULL, NULL, 'belum', '2025-11-23 19:58:46', '2025-11-23 19:58:46', 1),
(54, 4, 'ORD-20251124-Q3QQDUAE', '2026-01-15', '2026-01-16', 200000.00, 200000.00, 'pending', NULL, NULL, 'belum', '2025-11-23 20:37:42', '2025-11-23 20:37:42', 1),
(55, 4, 'ORD-20251124-YYX8YFUP', '2025-12-05', '2025-12-06', 200000.00, 200000.00, 'paid', NULL, NULL, 'belum', '2025-11-23 20:44:14', '2025-11-23 20:53:22', 1),
(56, 4, 'ORD-20251124-FVFJML0T', '2025-12-23', '2025-12-24', 200000.00, 200000.00, 'pending', NULL, NULL, 'belum', '2025-11-23 21:10:47', '2025-11-23 21:10:47', 1);

-- --------------------------------------------------------

--
-- Table structure for table `order_details`
--

CREATE TABLE `order_details` (
  `id` bigint UNSIGNED NOT NULL,
  `order_id` bigint UNSIGNED NOT NULL,
  `service_type` enum('kostum','tari','rias') COLLATE utf8mb4_unicode_ci NOT NULL,
  `detail_id` bigint UNSIGNED NOT NULL,
  `quantity` int NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_details`
--

INSERT INTO `order_details` (`id`, `order_id`, `service_type`, `detail_id`, `quantity`, `unit_price`, `created_at`, `updated_at`) VALUES
(3, 3, 'kostum', 1, 1, 150000.00, '2025-11-22 15:03:54', '2025-11-22 15:03:54'),
(4, 4, 'kostum', 1, 1, 150000.00, '2025-11-22 15:14:42', '2025-11-22 15:14:42'),
(5, 5, 'kostum', 8, 2, 300000.00, '2025-11-22 15:29:33', '2025-11-22 15:29:33'),
(6, 6, 'kostum', 7, 1, 200000.00, '2025-11-22 15:42:48', '2025-11-22 15:42:48'),
(7, 7, 'kostum', 7, 2, 200000.00, '2025-11-22 16:14:43', '2025-11-22 16:14:43'),
(8, 8, 'kostum', 8, 1, 300000.00, '2025-11-22 16:41:13', '2025-11-22 16:41:13'),
(9, 9, 'kostum', 8, 1, 300000.00, '2025-11-22 16:48:50', '2025-11-22 16:48:50'),
(10, 10, 'kostum', 8, 1, 300000.00, '2025-11-22 16:49:30', '2025-11-22 16:49:30'),
(11, 11, 'kostum', 3, 1, 175000.00, '2025-11-22 17:55:37', '2025-11-22 17:55:37'),
(12, 12, 'kostum', 3, 1, 175000.00, '2025-11-22 17:55:56', '2025-11-22 17:55:56'),
(13, 13, 'kostum', 4, 5, 200000.00, '2025-11-22 18:18:45', '2025-11-22 18:18:45'),
(14, 14, 'tari', 3, 1, 1200000.00, '2025-11-22 18:31:45', '2025-11-22 18:31:45'),
(15, 15, 'tari', 3, 1, 1200000.00, '2025-11-22 18:37:40', '2025-11-22 18:37:40'),
(16, 16, 'tari', 4, 1, 1500000.00, '2025-11-22 18:49:15', '2025-11-22 18:49:15'),
(17, 17, 'kostum', 3, 3, 175000.00, '2025-11-22 19:03:01', '2025-11-22 19:03:01'),
(18, 18, 'rias', 5, 2, 200000.00, '2025-11-22 19:16:58', '2025-11-22 19:16:58'),
(19, 19, 'rias', 3, 1, 125000.00, '2025-11-22 19:25:31', '2025-11-22 19:25:31'),
(20, 20, 'rias', 2, 1, 100000.00, '2025-11-22 20:55:23', '2025-11-22 20:55:23'),
(21, 21, 'kostum', 5, 1, 250000.00, '2025-11-22 21:06:56', '2025-11-22 21:06:56'),
(22, 22, 'rias', 3, 1, 125000.00, '2025-11-22 21:57:04', '2025-11-22 21:57:04'),
(23, 23, 'kostum', 3, 3, 175000.00, '2025-11-22 22:13:31', '2025-11-22 22:13:31'),
(24, 24, 'kostum', 3, 3, 175000.00, '2025-11-22 22:15:45', '2025-11-22 22:15:45'),
(25, 25, 'kostum', 3, 3, 175000.00, '2025-11-22 22:25:14', '2025-11-22 22:25:14'),
(26, 26, 'tari', 2, 1, 800000.00, '2025-11-22 22:46:40', '2025-11-22 22:46:40'),
(27, 27, 'rias', 6, 2, 88999.00, '2025-11-22 23:20:57', '2025-11-22 23:20:57'),
(28, 28, 'tari', 2, 1, 800000.00, '2025-11-23 07:09:26', '2025-11-23 07:09:26'),
(29, 29, 'tari', 2, 1, 800000.00, '2025-11-23 07:51:58', '2025-11-23 07:51:58'),
(30, 30, 'tari', 2, 1, 800000.00, '2025-11-23 08:35:30', '2025-11-23 08:35:30'),
(31, 31, 'kostum', 5, 3, 250000.00, '2025-11-23 08:51:08', '2025-11-23 08:51:08'),
(32, 32, 'rias', 4, 2, 150000.00, '2025-11-23 09:54:49', '2025-11-23 09:54:49'),
(33, 33, 'rias', 1, 1, 75000.00, '2025-11-23 11:23:48', '2025-11-23 11:23:48'),
(34, 34, 'kostum', 8, 1, 300000.00, '2025-11-23 11:30:23', '2025-11-23 11:30:23'),
(35, 35, 'rias', 2, 1, 100000.00, '2025-11-23 11:47:12', '2025-11-23 11:47:12'),
(36, 36, 'kostum', 2, 4, 150000.00, '2025-11-23 12:39:31', '2025-11-23 12:39:31'),
(37, 37, 'kostum', 2, 2, 150000.00, '2025-11-23 13:42:36', '2025-11-23 13:42:36'),
(38, 38, 'rias', 4, 2, 150000.00, '2025-11-23 13:59:22', '2025-11-23 13:59:22'),
(39, 39, 'kostum', 8, 1, 300000.00, '2025-11-23 14:32:51', '2025-11-23 14:32:51'),
(40, 40, 'kostum', 8, 1, 300000.00, '2025-11-23 14:40:36', '2025-11-23 14:40:36'),
(41, 41, 'rias', 6, 1, 88999.00, '2025-11-23 16:57:36', '2025-11-23 16:57:36'),
(42, 42, 'rias', 6, 2, 88999.00, '2025-11-23 16:58:38', '2025-11-23 16:58:38'),
(43, 43, 'kostum', 2, 1, 150000.00, '2025-11-23 17:01:24', '2025-11-23 17:01:24'),
(44, 44, 'kostum', 2, 1, 150000.00, '2025-11-23 17:02:33', '2025-11-23 17:02:33'),
(45, 45, 'rias', 6, 4, 88999.00, '2025-11-23 17:21:15', '2025-11-23 17:21:15'),
(46, 46, 'tari', 2, 1, 800000.00, '2025-11-23 17:33:19', '2025-11-23 17:33:19'),
(47, 47, 'tari', 2, 1, 800000.00, '2025-11-23 17:38:17', '2025-11-23 17:38:17'),
(48, 48, 'rias', 5, 2, 200000.00, '2025-11-23 17:46:27', '2025-11-23 17:46:27'),
(49, 49, 'tari', 4, 1, 1500000.00, '2025-11-23 17:53:33', '2025-11-23 17:53:33'),
(50, 50, 'rias', 3, 1, 125000.00, '2025-11-23 18:36:12', '2025-11-23 18:36:12'),
(51, 51, 'rias', 5, 3, 200000.00, '2025-11-23 19:51:19', '2025-11-23 19:51:19'),
(52, 52, 'rias', 5, 2, 200000.00, '2025-11-23 19:58:41', '2025-11-23 19:58:41'),
(53, 53, 'rias', 5, 2, 200000.00, '2025-11-23 19:58:46', '2025-11-23 19:58:46'),
(54, 54, 'rias', 5, 1, 200000.00, '2025-11-23 20:37:42', '2025-11-23 20:37:42'),
(55, 55, 'rias', 5, 1, 200000.00, '2025-11-23 20:44:14', '2025-11-23 20:44:14'),
(56, 56, 'rias', 2, 2, 100000.00, '2025-11-23 21:10:47', '2025-11-23 21:10:47');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `personal_access_tokens`
--

INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(1, 'App\\Models\\User', 2, 'mobile-app', 'a5094d30d274f61b8e77cde44a3c94d7612311de6188a2f7149b73457703cf64', '[\"*\"]', NULL, NULL, '2025-11-19 01:05:40', '2025-11-19 01:05:40'),
(2, 'App\\Models\\User', 5, 'mobile-app', '398189eca4d4b4295fbeb91d297b2ec00785cddd32f017b1a2a0a47c539759e5', '[\"*\"]', NULL, NULL, '2025-11-19 01:12:13', '2025-11-19 01:12:13'),
(3, 'App\\Models\\User', 6, 'mobile-app', 'c123f5b3c6eb187a71e012ac2d54e3bd4183be4c8f85da195389a72e276fd861', '[\"*\"]', NULL, NULL, '2025-11-19 01:23:16', '2025-11-19 01:23:16'),
(4, 'App\\Models\\User', 6, 'mobile-app', '63c41d3a5017cd6292311221a3e6777a39420b59e160fd521b9548d8888dea92', '[\"*\"]', NULL, NULL, '2025-11-19 01:29:36', '2025-11-19 01:29:36'),
(5, 'App\\Models\\User', 4, 'mobile-app', '08446e49cd67d3b6448d10d0aff464cdfd466ca2e20cbf8c79e91281a7cc527f', '[\"*\"]', NULL, NULL, '2025-11-19 01:45:58', '2025-11-19 01:45:58'),
(6, 'App\\Models\\User', 7, 'mobile-app', 'c1bfd1ab601169c0fa64c0a08bcb33c56eaffd74d0d52786f329024f08d80d6a', '[\"*\"]', NULL, NULL, '2025-11-19 01:49:43', '2025-11-19 01:49:43'),
(7, 'App\\Models\\User', 4, 'mobile-app', '92302e394f263f58edb7195740660fe33dcab2909e965e063f534c978fe6b382', '[\"*\"]', NULL, NULL, '2025-11-19 02:07:49', '2025-11-19 02:07:49'),
(8, 'App\\Models\\User', 4, 'mobile-app', '43a3cea884ba31370955b9148a2aee43fc7a58268d5e510dc6427953372c0632', '[\"*\"]', NULL, NULL, '2025-11-19 02:26:12', '2025-11-19 02:26:12'),
(10, 'App\\Models\\User', 4, 'mobile-app', 'cc180fa523889e3fdc09118eb98f7e633831885398b16347e372558095bedd29', '[\"*\"]', NULL, NULL, '2025-11-19 04:20:14', '2025-11-19 04:20:14'),
(11, 'App\\Models\\User', 4, 'mobile-app', 'f9eed5fb71d0fe3305772a7aa777538fcdd5af40253abef5cd441dcb59f269c0', '[\"*\"]', NULL, NULL, '2025-11-19 08:08:26', '2025-11-19 08:08:26'),
(12, 'App\\Models\\User', 4, 'mobile-app', '0267aaa21ce1faf7b28d0ed6f5f954ce91575f2867bf751f178868c30ddac468', '[\"*\"]', NULL, NULL, '2025-11-19 08:22:59', '2025-11-19 08:22:59'),
(13, 'App\\Models\\User', 1, 'test', 'ac3bc9f7fac05433435219b187b59c7dcd02d2e19a2698601d2c19437cb028ba', '[\"*\"]', '2025-11-19 10:14:04', NULL, '2025-11-19 09:28:38', '2025-11-19 10:14:04'),
(14, 'App\\Models\\User', 4, 'mobile-app', '2738d98c2caf90a6ef9bf843e90ee61d6cbe66c0ec5886207030c951c94fb864', '[\"*\"]', '2025-11-19 11:22:28', NULL, '2025-11-19 10:17:41', '2025-11-19 11:22:28'),
(15, 'App\\Models\\User', 4, 'mobile-app', '6360bed80b4701fa0364b002add08215c2993a19e03d23823104126ded9f6c9f', '[\"*\"]', NULL, NULL, '2025-11-19 11:59:10', '2025-11-19 11:59:10'),
(16, 'App\\Models\\User', 4, 'mobile-app', 'be9bfa84c5779782d9cb50a9554d6b351513e67e4b179e1e3d6ba2cdfc3a8e1b', '[\"*\"]', '2025-11-19 12:38:10', NULL, '2025-11-19 12:09:50', '2025-11-19 12:38:10'),
(18, 'App\\Models\\User', 2, 'mobile-app', 'e890ccc8aba7e8c58741a404ee2b2e21f43e4f2466e7f6d59102134a1146aa2f', '[\"*\"]', '2025-11-19 12:18:42', NULL, '2025-11-19 12:13:30', '2025-11-19 12:18:42'),
(19, 'App\\Models\\User', 7, 'mobile-app', 'ae574daef7eff515eddda10dd87abce31c4ba62feb20df01881745193f82cb2f', '[\"*\"]', '2025-11-19 13:53:25', NULL, '2025-11-19 13:03:39', '2025-11-19 13:53:25'),
(20, 'App\\Models\\User', 3, 'mobile-app', 'ee08968d475cd52eb29951cd872b41e563db082ac4fa94a8dad76c717fad6279', '[\"*\"]', '2025-11-19 13:29:21', NULL, '2025-11-19 13:28:59', '2025-11-19 13:29:21'),
(21, 'App\\Models\\User', 4, 'mobile-app', '64cd57387064c8470432b2fc2091a295d483b769ba6244a6d81fa1b15b9b4c36', '[\"*\"]', '2025-11-19 14:08:02', NULL, '2025-11-19 14:02:07', '2025-11-19 14:08:02'),
(22, 'App\\Models\\User', 4, 'mobile-app', 'b20f290d40438f22da7f5612b4c57f25fbd5b1358c0593f4c7e931094d895590', '[\"*\"]', '2025-11-20 02:31:09', NULL, '2025-11-20 01:30:44', '2025-11-20 02:31:09'),
(23, 'App\\Models\\User', 7, 'mobile-app', '2970c7f3e4571a0e23dc14da14322df02e87658868333c15f76b7e3f0fdf5373', '[\"*\"]', '2025-11-20 02:56:20', NULL, '2025-11-20 02:32:54', '2025-11-20 02:56:20'),
(24, 'App\\Models\\User', 7, 'mobile-app', 'a5f3d0de85b6fa1fecf7ba2f21205c2027fe5c48cb7690aed792ed2488df165c', '[\"*\"]', '2025-11-20 03:14:11', NULL, '2025-11-20 03:04:53', '2025-11-20 03:14:11'),
(25, 'App\\Models\\User', 4, 'mobile-app', 'bfcd77e240a5575f1dabfb4a1dee91967f2c77dde2a9ce1d0d015926d0272d88', '[\"*\"]', '2025-11-20 03:19:50', NULL, '2025-11-20 03:16:34', '2025-11-20 03:19:50'),
(26, 'App\\Models\\User', 4, 'mobile-app', 'ebaa7502dac845553e6d24d091c2586862c1abb005688f50293b81528ec2af58', '[\"*\"]', '2025-11-20 03:55:15', NULL, '2025-11-20 03:24:14', '2025-11-20 03:55:15'),
(27, 'App\\Models\\User', 4, 'mobile-app', 'cb10e8cd16c044fa81f734e9a7fb239e081eb33aa20ace46e242bc5ce95d530a', '[\"*\"]', '2025-11-20 03:59:15', NULL, '2025-11-20 03:58:21', '2025-11-20 03:59:15'),
(28, 'App\\Models\\User', 4, 'mobile-app', '7e8e3c9ba5938941b306a7f0fc80890c16d583afe9e70f40504f94d26f13736a', '[\"*\"]', '2025-11-20 12:57:24', NULL, '2025-11-20 04:04:21', '2025-11-20 12:57:24'),
(29, 'App\\Models\\User', 7, 'mobile-app', '8e1880bc3f42991d6ce0fbbbfebb77dc75fc9ed0ef6ec0fa8651153b6f09649e', '[\"*\"]', '2025-11-20 17:22:10', NULL, '2025-11-20 15:51:59', '2025-11-20 17:22:10'),
(30, 'App\\Models\\User', 2, 'mobile-app', '5aeb145ae45fcf20660bea3e622634a4a436a11c702c8ad410fa37107ed6848a', '[\"*\"]', '2025-11-20 18:56:07', NULL, '2025-11-20 15:53:42', '2025-11-20 18:56:07'),
(31, 'App\\Models\\User', 4, 'mobile-app', '60e39167fb9861c963d1ff01694ccdc08b90119dbc2a988ec07aafc39f27eb19', '[\"*\"]', '2025-11-20 19:48:21', NULL, '2025-11-20 17:24:50', '2025-11-20 19:48:21'),
(32, 'App\\Models\\User', 7, 'mobile-app', 'ba8f78e3d765bed795de8c0fff0972fad1e5379685ac9dfdbf727d6eeafadabf', '[\"*\"]', '2025-11-20 20:25:20', NULL, '2025-11-20 18:57:07', '2025-11-20 20:25:20'),
(33, 'App\\Models\\User', 3, 'mobile-app', '49e82eec545ec6317231d9a18ff1a6695066a9a22604498b976eca79e81ccdbe', '[\"*\"]', '2025-11-20 20:18:28', NULL, '2025-11-20 19:54:21', '2025-11-20 20:18:28'),
(34, 'App\\Models\\User', 2, 'mobile-app', 'a53eb7d8c711c3794cbbdb22cb27395754e59c176b56b23ffc1904aa05f90d9d', '[\"*\"]', '2025-11-20 20:32:40', NULL, '2025-11-20 20:25:27', '2025-11-20 20:32:40'),
(35, 'App\\Models\\User', 2, 'mobile-app', 'cc8b11e4ee415a75cfb0ec2183761965a4e490d0d036c80bded037a3a56fcb4e', '[\"*\"]', '2025-11-21 08:14:22', NULL, '2025-11-21 07:26:53', '2025-11-21 08:14:22'),
(36, 'App\\Models\\User', 4, 'mobile-app', '58c0e024768345835796a217676a75e729bbe52f259be8881f37aad3d94d87c4', '[\"*\"]', '2025-11-21 11:26:53', NULL, '2025-11-21 08:18:29', '2025-11-21 11:26:53'),
(37, 'App\\Models\\User', 7, 'mobile-app', '2063134e7f355ee5d15556f4ab03aa42f9bd48044fdcfcf04d1d5ef87cafb8ab', '[\"*\"]', NULL, NULL, '2025-11-21 15:26:52', '2025-11-21 15:26:52'),
(38, 'App\\Models\\User', 2, 'mobile-app', 'f437f79d4502385e2139e97b59773a49118df3de237e30642dfc529730d0f2fd', '[\"*\"]', '2025-11-21 15:36:48', NULL, '2025-11-21 15:34:20', '2025-11-21 15:36:48'),
(39, 'App\\Models\\User', 7, 'mobile-app', '30c63ddd9acd075a30814bb72b574f188b1d62298f7d6a542313b78ef75f2b50', '[\"*\"]', NULL, NULL, '2025-11-21 15:45:33', '2025-11-21 15:45:33'),
(40, 'App\\Models\\User', 4, 'mobile-app', '5ec7601c1a2b7b6b963727a77879c6d51ffcd3daa5fa48b0a2433cd6f0a8f7ca', '[\"*\"]', NULL, NULL, '2025-11-21 15:59:48', '2025-11-21 15:59:48'),
(41, 'App\\Models\\User', 7, 'mobile-app', '47760a10a50b8ce54a6e84de8d8aef7fa2e909a78a3628c3262f546633e2c298', '[\"*\"]', NULL, NULL, '2025-11-21 17:35:51', '2025-11-21 17:35:51'),
(42, 'App\\Models\\User', 4, 'mobile-app', '82c6d8a5b3f54372be858c8471b6e6b532520c21bd816974b8fb4fbbc7638605', '[\"*\"]', NULL, NULL, '2025-11-21 18:27:12', '2025-11-21 18:27:12'),
(43, 'App\\Models\\User', 4, 'mobile-app', 'f41935c7a9415b632ab7cbfd83e2562786e69e19db2c5e124c3efad577fc0f54', '[\"*\"]', NULL, NULL, '2025-11-21 18:40:02', '2025-11-21 18:40:02'),
(44, 'App\\Models\\User', 4, 'mobile-app', '516674135510f8b0c168146fcd378aa77774944570460160f5d7756dc1ec2da0', '[\"*\"]', NULL, NULL, '2025-11-21 19:02:10', '2025-11-21 19:02:10'),
(45, 'App\\Models\\User', 4, 'mobile-app', '9d2e10f082f39618ca456a78830a4b8ca837b7fbe2c837e685bf98b9b29412a1', '[\"*\"]', NULL, NULL, '2025-11-21 19:12:53', '2025-11-21 19:12:53'),
(46, 'App\\Models\\User', 4, 'mobile-app', '39850b1737c5bc749f7905841f5dcd073a79a85551bdcb705c1a71a29a76c05a', '[\"*\"]', '2025-11-21 20:08:30', NULL, '2025-11-21 19:21:29', '2025-11-21 20:08:30'),
(47, 'App\\Models\\User', 7, 'mobile-app', 'c9f8966907adc0d0726ecf37c58ed67e4ede8e66ebfa11c4b5dbd290a3d94eba', '[\"*\"]', NULL, NULL, '2025-11-21 19:27:37', '2025-11-21 19:27:37'),
(48, 'App\\Models\\User', 4, 'mobile-app', '721b206b867405abf1e74345c4dc85f6c9620f2fd15c0fb69ff41fd85f255ca4', '[\"*\"]', NULL, NULL, '2025-11-21 20:43:06', '2025-11-21 20:43:06'),
(49, 'App\\Models\\User', 4, 'mobile-app', 'f13f396e4ba53338a9610dc552cb618c9cd1b3c724546ebcf6dea9a6f3f6135a', '[\"*\"]', '2025-11-22 04:58:32', NULL, '2025-11-21 22:29:46', '2025-11-22 04:58:32'),
(50, 'App\\Models\\User', 4, 'mobile-app', '89b7d7e82fd1d3550609b1dfdcb6b49e3c4d5845905234874659f3fcfd79b254', '[\"*\"]', '2025-11-22 22:36:29', NULL, '2025-11-22 13:34:21', '2025-11-22 22:36:29'),
(51, 'App\\Models\\User', 2, 'mobile-app', 'df268949d891a36db54f0de3057ed345ca59f6ec0e2db26129843adf66d6c486', '[\"*\"]', '2025-11-22 20:30:34', NULL, '2025-11-22 20:30:32', '2025-11-22 20:30:34'),
(52, 'App\\Models\\User', 2, 'mobile-app', '506983b358069a63f6a6b1eaa2a0ddc5167a9cac69235a579dd7768af049916f', '[\"*\"]', '2025-11-22 20:32:32', NULL, '2025-11-22 20:32:32', '2025-11-22 20:32:32'),
(53, 'App\\Models\\User', 2, 'mobile-app', '1adde1c6f2514b096fc0a8b13d17dbc07d7302fa1fc4e817abd3801a0503d3ea', '[\"*\"]', '2025-11-22 20:36:55', NULL, '2025-11-22 20:36:55', '2025-11-22 20:36:55'),
(54, 'App\\Models\\User', 2, 'mobile-app', 'c24207d3710e26dc061061d0ff847ed42fbfb9a73a9ce820e7432e2b077a420c', '[\"*\"]', '2025-11-22 20:38:40', NULL, '2025-11-22 20:38:40', '2025-11-22 20:38:40'),
(55, 'App\\Models\\User', 2, 'mobile-app', '39fccf9aad629b772b5b50ac98a673944df9b8065d2609604ce8328cbca77700', '[\"*\"]', '2025-11-22 20:39:13', NULL, '2025-11-22 20:39:13', '2025-11-22 20:39:13'),
(56, 'App\\Models\\User', 2, 'mobile-app', '73f0ba8f399a5c918b386168fe8ebd6ada955818f5544a986399fa16c6718944', '[\"*\"]', '2025-11-22 20:52:03', NULL, '2025-11-22 20:52:02', '2025-11-22 20:52:03'),
(57, 'App\\Models\\User', 2, 'mobile-app', '09fd3a48660269ab5c1b61ee929460a5fdbda2a7131da9a782c75a3422355bc0', '[\"*\"]', '2025-11-22 20:53:05', NULL, '2025-11-22 20:53:04', '2025-11-22 20:53:05'),
(58, 'App\\Models\\User', 2, 'mobile-app', '14439cbb624822942afa8cb6730bd58f2fa36cd24fcfd7f6bb8ee7222572a5c5', '[\"*\"]', '2025-11-22 20:57:12', NULL, '2025-11-22 20:57:06', '2025-11-22 20:57:12'),
(59, 'App\\Models\\User', 2, 'mobile-app', 'a1b1a3d696dfde446091a7e2e5eb8b6cc273ca804a0cc2ef177f92304cda4536', '[\"*\"]', '2025-11-22 21:01:58', NULL, '2025-11-22 21:01:48', '2025-11-22 21:01:58'),
(60, 'App\\Models\\User', 7, 'mobile-app', 'f4870403e6460027971af9fb11b529104fe082e699b77375bf1c85420113247c', '[\"*\"]', '2025-11-22 23:22:59', NULL, '2025-11-22 22:44:38', '2025-11-22 23:22:59'),
(61, 'App\\Models\\User', 2, 'mobile-app', '0afaf49e42618d21251ce842bf24155b95c160b946ebb9cfd20a1859c412e11d', '[\"*\"]', '2025-11-23 08:51:32', NULL, '2025-11-23 07:07:31', '2025-11-23 08:51:32'),
(62, 'App\\Models\\User', 2, 'mobile-app', '09a12c1f0e2c99a660ffa83b26479fc4a891748aecb1329321a66c4dd3f12fdf', '[\"*\"]', '2025-11-23 08:27:40', NULL, '2025-11-23 08:27:31', '2025-11-23 08:27:40'),
(63, 'App\\Models\\User', 4, 'mobile-app', '530b4704be98408d4d00bb9f2fb77162c5e66022575436422363f339ecf6e668', '[\"*\"]', '2025-11-23 21:14:02', NULL, '2025-11-23 09:54:04', '2025-11-23 21:14:02'),
(64, 'App\\Models\\User', 2, 'mobile-app', '811875e82fe8d12ae85494d819331b66f2001f1ddad132cda39a82347a433166', '[\"*\"]', '2025-11-23 11:32:40', NULL, '2025-11-23 11:32:39', '2025-11-23 11:32:40');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` bigint UNSIGNED NOT NULL,
  `order_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `rating` tinyint NOT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `views_count` int UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Jumlah orang yang melihat review ini'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('uDQgjkKSRb5eByfABbhHwGieNHXe83nkF82L7hVI', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoibHNERnBSTXdWa1NqQVBqRTUyTjc1c3hyMXd1TllUNHBRSjBpa2E0MSI7czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjk6Il9wcmV2aW91cyI7YToyOntzOjM6InVybCI7czozNjoiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2FkbWluL2Nvc3R1bWVzIjtzOjU6InJvdXRlIjtzOjIwOiJhZG1pbi5jb3N0dW1lcy5pbmRleCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1763932140);

-- --------------------------------------------------------

--
-- Table structure for table `stock_log`
--

CREATE TABLE `stock_log` (
  `id` bigint UNSIGNED NOT NULL,
  `costume_id` bigint UNSIGNED NOT NULL,
  `order_id` bigint UNSIGNED DEFAULT NULL,
  `quantity_change` int NOT NULL,
  `log_date` date NOT NULL,
  `type` enum('out','in','adjustment') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'out',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` bigint UNSIGNED NOT NULL,
  `order_id` bigint UNSIGNED NOT NULL,
  `transaction_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment_method` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_channel` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `instruction_text` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_details` json DEFAULT NULL,
  `va_number` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `pg_status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `paid_at` timestamp NULL DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `order_id`, `transaction_code`, `payment_method`, `payment_channel`, `bank_code`, `account_name`, `instruction_text`, `payment_details`, `va_number`, `amount`, `pg_status`, `paid_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(1, 5, 'TRX-ORD-20251122-GO3UQBIY', 'ewallet', 'ovo', 'ovo', 'PT RANTS', 'Buka aplikasi GoPay dan lakukan pembayaran', '[]', NULL, 600000.00, 'pending', NULL, '2025-11-24 04:01:59', '2025-11-22 15:29:33', '2025-11-22 21:01:59'),
(2, 6, 'TRX-VVFRWNSMLA', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 200000.00, 'pending', NULL, NULL, '2025-11-22 15:42:48', '2025-11-22 15:42:48'),
(3, 7, 'TRX-TAISCZCXY2', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 400000.00, 'pending', NULL, NULL, '2025-11-22 16:14:43', '2025-11-22 16:14:43'),
(4, 8, 'TRX-DQKFNRJUSU', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 300000.00, 'pending', NULL, NULL, '2025-11-22 16:41:13', '2025-11-22 16:41:13'),
(5, 9, 'TRX-OQWABHFXOI', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 300000.00, 'pending', NULL, NULL, '2025-11-22 16:48:50', '2025-11-22 16:48:50'),
(6, 10, 'TRX-3YZXIJLEBQ', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 300000.00, 'pending', NULL, NULL, '2025-11-22 16:49:30', '2025-11-22 16:49:30'),
(7, 11, 'TRX-0RKO4JRCJO', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 175000.00, 'pending', NULL, NULL, '2025-11-22 17:55:37', '2025-11-22 17:55:37'),
(8, 12, 'TRX-5IV3JUCF35', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 175000.00, 'pending', NULL, NULL, '2025-11-22 17:55:56', '2025-11-22 17:55:56'),
(9, 13, 'TRX-ORD-20251123-KL5SXUNN', 'bank_transfer', NULL, NULL, NULL, NULL, NULL, NULL, 1000000.00, 'pending', NULL, NULL, '2025-11-22 18:18:45', '2025-11-22 18:22:47'),
(10, 14, 'TRX-ORD-20251123-SIYOLEJH', 'va', NULL, NULL, NULL, NULL, NULL, NULL, 1200000.00, 'pending', NULL, NULL, '2025-11-22 18:31:45', '2025-11-22 18:31:54'),
(11, 15, 'TRX-ORD-20251123-RUJIIDZD', 'ewallet', NULL, NULL, NULL, NULL, NULL, NULL, 1200000.00, 'pending', NULL, NULL, '2025-11-22 18:37:41', '2025-11-22 18:38:30'),
(12, 16, 'TRX-ORD-20251123-GZB6LVDE', 'ewallet', NULL, NULL, NULL, NULL, NULL, NULL, 1500000.00, 'pending', NULL, NULL, '2025-11-22 18:49:15', '2025-11-22 18:52:13'),
(13, 17, 'TRX-ORD-20251123-NPGPDOAT', 'va', NULL, NULL, NULL, NULL, NULL, NULL, 525000.00, 'pending', NULL, NULL, '2025-11-22 19:03:01', '2025-11-22 19:03:24'),
(14, 18, 'TRX-ORD-20251123-XRE4HAW1', 'va', NULL, NULL, NULL, NULL, NULL, NULL, 400000.00, 'pending', NULL, NULL, '2025-11-22 19:16:58', '2025-11-22 19:18:18'),
(15, 19, 'TRX-ORD-20251123-HX9N34Z6', 'va', NULL, NULL, NULL, NULL, NULL, NULL, 125000.00, 'pending', NULL, NULL, '2025-11-22 19:25:31', '2025-11-22 19:25:39'),
(16, 20, 'TRX-ORD-20251123-E1T3YWR0', 'va', 'permata', 'permata', 'PT RANTS', 'Lakukan transfer ke nomor rekening virtual PERMATA', '\"{\\\"bank\\\":\\\"permata\\\"}\"', 'PER55411805T3YWR0', 100000.00, 'pending', NULL, '2025-11-24 04:05:41', '2025-11-22 20:55:23', '2025-11-22 21:05:42'),
(17, 21, 'TRX-ORD-20251123-84QRORIL', 'va', 'bca', 'bca', 'PT RANTS', 'Klik tombol \'Bayar\' untuk melanjutkan ke Snap Midtrans. Setelah memilih bank BCA, nomor VA akan ditampilkan di Snap. Gunakan nomor VA tersebut untuk transfer.', '{\"bank\": \"bca\"}', NULL, 250000.00, 'pending', NULL, '2025-11-24 18:32:40', '2025-11-22 21:06:56', '2025-11-23 11:32:40'),
(18, 22, 'TRX-ORD-20251123-D2MSUZDS', 'va', 'permata', 'permata', 'PT RANTS', 'Lakukan transfer ke nomor rekening virtual PERMATA', '{\"bank\": \"permata\"}', 'PER86834214MSUZDS', 125000.00, 'pending', NULL, '2025-11-24 04:58:03', '2025-11-22 21:57:04', '2025-11-22 21:58:03'),
(19, 23, 'TRX-ORD-20251123-SVRZEE1D', 'ewallet', 'dana', 'dana', 'PT RANTS', 'Buka aplikasi GoPay dan lakukan pembayaran', '[]', NULL, 525000.00, 'pending', NULL, '2025-11-24 05:14:08', '2025-11-22 22:13:31', '2025-11-22 22:14:08'),
(20, 24, 'TRX-ORD-20251123-9VZ0NHPB', 'va', 'bni', 'bni', 'PT RANTS', 'Lakukan transfer ke nomor rekening virtual BNI', '{\"bank\": \"bni\"}', 'BNI97713624Z0NHPB', 525000.00, 'pending', NULL, '2025-11-24 05:16:11', '2025-11-22 22:15:45', '2025-11-22 22:16:11'),
(21, 25, 'TRX-ORD-20251123-LIU84DCD', 'va', 'permata', 'permata', 'PT RANTS', 'Lakukan transfer ke nomor rekening virtual PERMATA', '{\"bank\": \"permata\"}', 'PER03233118U84DCD', 525000.00, 'pending', NULL, '2025-11-24 05:25:23', '2025-11-22 22:25:14', '2025-11-22 22:25:23'),
(22, 26, 'TRX-ORD-20251123-EZT0BEAT', 'va', 'bca', 'bca', 'PT RANTS', 'Lakukan transfer ke nomor rekening virtual BCA', '{\"bank\": \"bca\"}', 'BCA51615347T0BEAT', 800000.00, 'pending', NULL, '2025-11-24 05:46:55', '2025-11-22 22:46:40', '2025-11-22 22:46:55'),
(23, 27, 'TRX-ORD-20251123-KSEXGDFW', 'va', 'bni', 'bni', 'PT RANTS', 'Lakukan transfer ke nomor rekening virtual BNI', '{\"bank\": \"bni\"}', 'BNI37749355EXGDFW', 177998.00, 'pending', NULL, '2025-11-24 06:22:54', '2025-11-22 23:20:57', '2025-11-22 23:22:55'),
(24, 28, 'TRX-ORD-20251123-EBXJYOV7', 'va', 'permata', 'permata', 'PT RANTS', 'Lakukan transfer ke nomor rekening virtual PERMATA', '{\"bank\": \"permata\"}', 'PER17779819XJYOV7', 800000.00, 'pending', NULL, '2025-11-24 14:09:37', '2025-11-23 07:09:26', '2025-11-23 07:09:37'),
(25, 29, 'TRX-ORD-20251123-QEAJ5TJW', 'va', 'bca', 'bca', 'PT RANTS', 'Klik tombol \'Bayar\' untuk melanjutkan ke Snap Midtrans. Setelah memilih bank BCA, nomor VA akan ditampilkan di Snap. Gunakan nomor VA tersebut untuk transfer.', '{\"bank\": \"bca\"}', NULL, 800000.00, 'pending', NULL, '2025-11-24 14:52:11', '2025-11-23 07:51:58', '2025-11-23 07:52:11'),
(26, 30, 'TRX-ORD-20251123-KQSL97DZ', 'va', 'bca', 'bca', 'PT RANTS', 'Klik tombol \'Bayar\' untuk melanjutkan ke Snap Midtrans. Setelah memilih bank BCA, nomor VA akan ditampilkan di Snap. Gunakan nomor VA tersebut untuk transfer.', '{\"bank\": \"bca\"}', NULL, 800000.00, 'pending', NULL, '2025-11-24 15:35:46', '2025-11-23 08:35:30', '2025-11-23 08:35:46'),
(27, 31, 'TRX-ORD-20251123-PV9EHIQH', 'va', 'bni', 'bni', 'PT RANTS', 'Klik tombol \'Bayar\' untuk melanjutkan ke Snap Midtrans. Setelah memilih bank BNI, nomor VA akan ditampilkan di Snap. Gunakan nomor VA tersebut untuk transfer.', '{\"bank\": \"bni\"}', NULL, 750000.00, 'pending', NULL, '2025-11-24 15:51:25', '2025-11-23 08:51:08', '2025-11-23 08:51:25'),
(28, 32, 'TRX-ORD-20251123-R3ACPWFT', 'va', 'bni', 'bni', 'PT RANTS', 'Klik tombol \'Bayar\' untuk melanjutkan ke Snap Midtrans. Setelah memilih bank BNI, nomor VA akan ditampilkan di Snap. Gunakan nomor VA tersebut untuk transfer.', '{\"bank\": \"bni\"}', NULL, 300000.00, 'pending', NULL, '2025-11-24 16:54:54', '2025-11-23 09:54:49', '2025-11-23 09:54:54'),
(29, 33, 'TRX-ORD-20251123-0JU6BXJD', 'va', 'bca', 'bca', 'PT RANTS', 'Klik tombol \'Bayar\' untuk melanjutkan ke Snap Midtrans. Setelah memilih bank BCA, nomor VA akan ditampilkan di Snap. Gunakan nomor VA tersebut untuk transfer.', '{\"bank\": \"bca\"}', NULL, 75000.00, 'pending', NULL, '2025-11-24 18:23:56', '2025-11-23 11:23:48', '2025-11-23 11:23:56'),
(30, 34, 'TRX-ORD-20251123-E5ILVVAY', 'va', 'bni', 'bni', 'PT RANTS', 'Klik tombol \'Bayar\' untuk melanjutkan ke Snap Midtrans. Setelah memilih bank BNI, nomor VA akan ditampilkan di Snap. Gunakan nomor VA tersebut untuk transfer.', '{\"bank\": \"bni\"}', NULL, 300000.00, 'pending', NULL, '2025-11-24 18:30:32', '2025-11-23 11:30:23', '2025-11-23 11:30:32'),
(31, 35, 'TRX-ORD-20251123-UFMMMQJO', 'va', 'Virtual Account', 'Virtual Account', 'PT RANTS', 'Klik tombol \'Bayar\' untuk melanjutkan ke Snap Midtrans. Setelah memilih bank VIRTUAL ACCOUNT, nomor VA akan ditampilkan di Snap. Gunakan nomor VA tersebut untuk transfer.', '{\"bank\": \"Virtual Account\"}', NULL, 100000.00, 'pending', NULL, '2025-11-24 18:47:43', '2025-11-23 11:47:12', '2025-11-23 11:47:43'),
(32, 36, 'TRX-ORD-20251123-7DQ9IN16', 'bank_transfer', 'Virtual Account', 'Virtual Account', 'PT RANTS', 'Klik tombol \'Bayar\' untuk melanjutkan ke Snap Midtrans. Setelah memilih bank VIRTUAL ACCOUNT, nomor VA akan ditampilkan di Snap. Gunakan nomor VA tersebut untuk transfer.', '{\"bank\": \"Virtual Account\"}', NULL, 600000.00, 'settlement', '2025-11-23 13:41:53', '2025-11-24 19:39:49', '2025-11-23 12:39:31', '2025-11-23 13:41:53'),
(33, 37, 'TRX-ORD-20251123-P87QRLLQ', 'bank_transfer', 'Virtual Account', 'Virtual Account', 'PT RANTS', 'Klik tombol \'Bayar\' untuk melanjutkan ke Snap Midtrans. Setelah memilih bank VIRTUAL ACCOUNT, nomor VA akan ditampilkan di Snap. Gunakan nomor VA tersebut untuk transfer.', '{\"bank\": \"Virtual Account\"}', NULL, 300000.00, 'settlement', '2025-11-23 13:44:08', '2025-11-24 20:43:15', '2025-11-23 13:42:36', '2025-11-23 13:44:08'),
(34, 38, 'TRX-ORD-20251123-7FQPNU6Y', 'bank_transfer', 'Virtual Account', 'Virtual Account', 'PT RANTS', 'Klik tombol \'Bayar\' untuk melanjutkan ke Snap Midtrans. Setelah memilih bank VIRTUAL ACCOUNT, nomor VA akan ditampilkan di Snap. Gunakan nomor VA tersebut untuk transfer.', '{\"bank\": \"Virtual Account\"}', NULL, 300000.00, 'settlement', '2025-11-23 14:00:26', '2025-11-24 20:59:41', '2025-11-23 13:59:22', '2025-11-23 14:00:26'),
(35, 39, 'TRX-QMQRIOIUJ4', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 300000.00, 'pending', NULL, NULL, '2025-11-23 14:32:51', '2025-11-23 14:32:51'),
(36, 40, 'TRX-ORD-20251123-BCQCR9GY', 'va', 'permata', 'permata', 'PT RANTS', 'Klik tombol \'Bayar\' untuk melanjutkan ke Snap Midtrans. Setelah memilih bank PERMATA, nomor VA akan ditampilkan di Snap. Gunakan nomor VA tersebut untuk transfer.', '{\"bank\": \"permata\"}', NULL, 300000.00, 'pending', NULL, '2025-11-24 21:40:43', '2025-11-23 14:40:36', '2025-11-23 14:40:43'),
(37, 41, 'TRX-ORD-20251123-5PSEPNFD', 'bank_transfer', 'bca', 'bca', 'PT RANTS', 'Klik tombol \'Bayar\' untuk melanjutkan ke Snap Midtrans. Setelah memilih bank BCA, nomor VA akan ditampilkan di Snap. Gunakan nomor VA tersebut untuk transfer.', '{\"bank\": \"bca\"}', NULL, 88999.00, 'pending', NULL, '2025-11-24 23:58:01', '2025-11-23 16:57:36', '2025-11-23 16:58:18'),
(38, 42, 'TRX-ORD-20251123-D2BM2K7R', 'bank_transfer', 'bca', 'bca', 'PT RANTS', 'Klik tombol \'Bayar\' untuk melanjutkan ke Snap Midtrans. Setelah memilih bank BCA, nomor VA akan ditampilkan di Snap. Gunakan nomor VA tersebut untuk transfer.', '{\"bank\": \"bca\"}', NULL, 177998.00, 'settlement', '2025-11-23 17:00:58', '2025-11-24 23:59:16', '2025-11-23 16:58:38', '2025-11-23 17:00:58'),
(39, 43, 'TRX-ORD-20251124-CUXVFW8C', 'va', 'bca', 'bca', 'PT RANTS', 'Klik tombol \'Bayar\' untuk melanjutkan ke Snap Midtrans. Setelah memilih bank BCA, nomor VA akan ditampilkan di Snap. Gunakan nomor VA tersebut untuk transfer.', '{\"bank\": \"bca\"}', NULL, 150000.00, 'pending', NULL, '2025-11-25 00:01:25', '2025-11-23 17:01:24', '2025-11-23 17:01:25'),
(40, 44, 'TRX-ORD-20251124-NIXIE0X3', 'va', 'bca', 'bca', 'PT RANTS', 'Klik tombol \'Bayar\' untuk melanjutkan ke Snap Midtrans. Setelah memilih bank BCA, nomor VA akan ditampilkan di Snap. Gunakan nomor VA tersebut untuk transfer.', '{\"bank\": \"bca\"}', NULL, 150000.00, 'pending', NULL, '2025-11-25 00:02:34', '2025-11-23 17:02:33', '2025-11-23 17:02:34'),
(41, 45, 'TRX-ORD-20251124-BTS67BSI', 'va', 'bca', 'bca', 'PT RANTS', 'Klik tombol \'Bayar\' untuk melanjutkan ke Snap Midtrans. Setelah memilih bank BCA, nomor VA akan ditampilkan di Snap. Gunakan nomor VA tersebut untuk transfer.', '{\"bank\": \"bca\"}', NULL, 355996.00, 'pending', NULL, '2025-11-25 00:21:16', '2025-11-23 17:21:15', '2025-11-23 17:21:16'),
(42, 46, 'TRX-ORD-20251124-ILHHRRDP', 'cstore', 'bca', 'bca', 'PT RANTS', 'Klik tombol \'Bayar\' untuk melanjutkan ke Snap Midtrans. Setelah memilih bank BCA, nomor VA akan ditampilkan di Snap. Gunakan nomor VA tersebut untuk transfer.', '{\"bank\": \"bca\"}', NULL, 800000.00, 'pending', NULL, '2025-11-25 00:33:22', '2025-11-23 17:33:19', '2025-11-23 17:33:43'),
(43, 47, 'TRX-ORD-20251124-JNN01QBZ', 'qris', 'bca', 'bca', 'PT RANTS', 'Klik tombol \'Bayar\' untuk melanjutkan ke Snap Midtrans. Setelah memilih bank BCA, nomor VA akan ditampilkan di Snap. Gunakan nomor VA tersebut untuk transfer.', '{\"bank\": \"bca\"}', NULL, 800000.00, 'pending', NULL, '2025-11-25 00:38:22', '2025-11-23 17:38:17', '2025-11-23 17:38:55'),
(44, 48, 'TRX-ORD-20251124-IYTC9IUP', 'gopay', 'bca', 'bca', 'PT RANTS', 'Klik tombol \'Bayar\' untuk melanjutkan ke Snap Midtrans. Setelah memilih bank BCA, nomor VA akan ditampilkan di Snap. Gunakan nomor VA tersebut untuk transfer.', '{\"bank\": \"bca\"}', NULL, 400000.00, 'settlement', '2025-11-23 17:47:56', '2025-11-25 00:46:30', '2025-11-23 17:46:27', '2025-11-23 17:47:56'),
(45, 49, 'TRX-ORD-20251124-NTTWFNBS', 'va', 'bca', 'bca', 'PT RANTS', 'Klik tombol \'Bayar\' untuk melanjutkan ke Snap Midtrans. Setelah memilih bank BCA, nomor VA akan ditampilkan di Snap. Gunakan nomor VA tersebut untuk transfer.', '{\"bank\": \"bca\"}', NULL, 1500000.00, 'pending', NULL, '2025-11-25 01:22:12', '2025-11-23 17:53:33', '2025-11-23 18:22:12'),
(46, 50, 'TRX-ORD-20251124-RJSDECPO', 'bank_transfer', 'bca', 'bca', 'PT RANTS', 'Klik tombol \'Bayar\' untuk melanjutkan ke Snap Midtrans. Setelah memilih bank BCA, nomor VA akan ditampilkan di Snap. Gunakan nomor VA tersebut untuk transfer.', '{\"bank\": \"bca\"}', NULL, 125000.00, 'settlement', '2025-11-23 18:55:51', '2025-11-25 01:55:05', '2025-11-23 18:36:12', '2025-11-23 18:55:51'),
(47, 51, 'TRX-ORD-20251124-K5XWCEUI', 'bank_transfer', 'bca', 'bca', 'PT RANTS', 'Klik tombol \'Bayar\' untuk melanjutkan ke Snap Midtrans. Setelah memilih bank BCA, nomor VA akan ditampilkan di Snap. Gunakan nomor VA tersebut untuk transfer.', '{\"bank\": \"bca\"}', NULL, 600000.00, 'expire', NULL, '2025-11-25 02:54:19', '2025-11-23 19:51:19', '2025-11-23 20:55:32'),
(48, 52, 'TRX-ORD-20251124-PAGNJLET', 'bank_transfer', 'bca', 'bca', 'PT RANTS', 'Klik tombol \'Bayar\' untuk melanjutkan ke Snap Midtrans. Setelah memilih bank BCA, nomor VA akan ditampilkan di Snap. Gunakan nomor VA tersebut untuk transfer.', '{\"bank\": \"bca\"}', NULL, 400000.00, 'settlement', '2025-11-23 20:07:02', '2025-11-25 03:06:06', '2025-11-23 19:58:41', '2025-11-23 20:07:02'),
(49, 53, 'TRX-ORD-20251124-TN8UTKMN', 'va', 'bca', 'bca', 'PT RANTS', 'Klik tombol \'Bayar\' untuk melanjutkan ke Snap Midtrans. Setelah memilih bank BCA, nomor VA akan ditampilkan di Snap. Gunakan nomor VA tersebut untuk transfer.', '{\"bank\": \"bca\"}', NULL, 400000.00, 'pending', NULL, '2025-11-25 02:58:49', '2025-11-23 19:58:46', '2025-11-23 19:58:49'),
(50, 54, 'TRX-ORD-20251124-Q3QQDUAE', 'bank_transfer', 'bca', 'bca', 'PT RANTS', 'Klik tombol \'Bayar\' untuk melanjutkan ke Snap Midtrans. Setelah memilih bank BCA, nomor VA akan ditampilkan di Snap. Gunakan nomor VA tersebut untuk transfer.', '{\"bank\": \"bca\"}', NULL, 200000.00, 'pending', NULL, '2025-11-25 03:40:43', '2025-11-23 20:37:42', '2025-11-23 20:41:42'),
(51, 55, 'TRX-ORD-20251124-YYX8YFUP', 'bank_transfer', 'bca', 'bca', 'PT RANTS', 'Klik tombol \'Bayar\' untuk melanjutkan ke Snap Midtrans. Setelah memilih bank BCA, nomor VA akan ditampilkan di Snap. Gunakan nomor VA tersebut untuk transfer.', '{\"bank\": \"bca\"}', NULL, 200000.00, 'settlement', '2025-11-23 20:53:22', '2025-11-25 03:44:16', '2025-11-23 20:44:14', '2025-11-23 20:53:22'),
(52, 56, 'TRX-ORD-20251124-FVFJML0T', 'va', 'bca', 'bca', 'PT RANTS', 'Klik tombol \'Bayar\' untuk melanjutkan ke Snap Midtrans. Setelah memilih bank BCA, nomor VA akan ditampilkan di Snap. Gunakan nomor VA tersebut untuk transfer.', '{\"bank\": \"bca\"}', NULL, 200000.00, 'pending', NULL, '2025-11-25 04:12:15', '2025-11-23 21:10:47', '2025-11-23 21:12:15');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `google_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone_number` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('customer','admin') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'customer',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `google_id`, `phone_number`, `email_verified_at`, `avatar`, `password`, `role`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Admin RANTS', 'admin@rants.com', NULL, '081234567890', NULL, NULL, '$2y$12$SCBiyouH9PZLTxzuuRLznemhbtu.cXPaK1EpEWrc0z6Afw/yrRWam', 'admin', 'WadaNb68xOogvRH5RVrcY6ix7gcNKZYUrTdffVHBazCZfO14jK2Jm61oDWMW', '2025-11-14 11:05:37', '2025-11-15 01:51:42'),
(2, 'Customer Test', 'customer@test.com', NULL, '081234567891', NULL, NULL, '$2y$12$SCBiyouH9PZLTxzuuRLznemhbtu.cXPaK1EpEWrc0z6Afw/yrRWam', 'customer', NULL, '2025-11-14 11:05:37', '2025-11-14 11:05:37'),
(3, 'Jane Smith', 'jane@example.com', NULL, NULL, NULL, NULL, '$2y$12$e10ADRFGTKeyWGIErE0CdObN4Vd3eZRP5bi4.TVUZeYfHYZdQ/rVK', 'customer', NULL, '2025-11-14 12:30:42', '2025-11-14 12:30:42'),
(4, 'taro', 'tes@gmail.com', NULL, '08212121212', NULL, NULL, '$2y$12$0Tumt1slYB.vk/PFLNYjz.XcBfWFs/GtNKLpf/V/KMvPsh3QP2RBO', 'customer', NULL, '2025-11-19 00:15:39', '2025-11-19 00:15:39'),
(7, 'yyyyyyyy', 'newuser@test.com', NULL, '4444444444', NULL, NULL, '$2y$12$tZqG1FHd2014lkNQejR/Vu6/yDQtJe4Ywht4LHMLdaOZBGk.kKbvm', 'customer', NULL, '2025-11-19 01:49:43', '2025-11-19 01:49:43');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `addresses`
--
ALTER TABLE `addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `addresses_user_id_foreign` (`user_id`);

--
-- Indexes for table `availability_calendar`
--
ALTER TABLE `availability_calendar`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_service_availability` (`service_type`,`service_id`,`date`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `chat_messages_sender_id_foreign` (`sender_id`),
  ADD KEY `chat_messages_chat_session_id_index` (`chat_session_id`),
  ADD KEY `chat_messages_sender_type_index` (`sender_type`),
  ADD KEY `chat_messages_created_at_index` (`created_at`),
  ADD KEY `chat_messages_image_path_index` (`image_path`);

--
-- Indexes for table `chat_sessions`
--
ALTER TABLE `chat_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `chat_sessions_session_id_unique` (`session_id`),
  ADD KEY `chat_sessions_user_id_index` (`user_id`),
  ADD KEY `chat_sessions_status_index` (`status`),
  ADD KEY `chat_sessions_admin_id_index` (`admin_id`);

--
-- Indexes for table `costumes`
--
ALTER TABLE `costumes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `dance_services`
--
ALTER TABLE `dance_services`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `gallery`
--
ALTER TABLE `gallery`
  ADD PRIMARY KEY (`id`),
  ADD KEY `gallery_uploaded_by_foreign` (`uploaded_by`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `makeup_services`
--
ALTER TABLE `makeup_services`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `orders_order_code_unique` (`order_code`),
  ADD KEY `orders_user_id_foreign` (`user_id`),
  ADD KEY `orders_address_id_foreign` (`address_id`);

--
-- Indexes for table `order_details`
--
ALTER TABLE `order_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_details_order_id_foreign` (`order_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  ADD KEY `personal_access_tokens_expires_at_index` (`expires_at`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reviews_order_id_foreign` (`order_id`),
  ADD KEY `reviews_user_id_foreign` (`user_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `stock_log`
--
ALTER TABLE `stock_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stock_log_costume_id_foreign` (`costume_id`),
  ADD KEY `stock_log_order_id_foreign` (`order_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transactions_order_id_unique` (`order_id`),
  ADD UNIQUE KEY `transactions_transaction_code_unique` (`transaction_code`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_google_id_unique` (`google_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `addresses`
--
ALTER TABLE `addresses`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `availability_calendar`
--
ALTER TABLE `availability_calendar`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=198;

--
-- AUTO_INCREMENT for table `chat_sessions`
--
ALTER TABLE `chat_sessions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `costumes`
--
ALTER TABLE `costumes`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `dance_services`
--
ALTER TABLE `dance_services`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `gallery`
--
ALTER TABLE `gallery`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `makeup_services`
--
ALTER TABLE `makeup_services`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `order_details`
--
ALTER TABLE `order_details`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stock_log`
--
ALTER TABLE `stock_log`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `addresses`
--
ALTER TABLE `addresses`
  ADD CONSTRAINT `addresses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD CONSTRAINT `chat_messages_chat_session_id_foreign` FOREIGN KEY (`chat_session_id`) REFERENCES `chat_sessions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `chat_messages_sender_id_foreign` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `chat_sessions`
--
ALTER TABLE `chat_sessions`
  ADD CONSTRAINT `chat_sessions_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `chat_sessions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `gallery`
--
ALTER TABLE `gallery`
  ADD CONSTRAINT `gallery_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_address_id_foreign` FOREIGN KEY (`address_id`) REFERENCES `addresses` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_details`
--
ALTER TABLE `order_details`
  ADD CONSTRAINT `order_details_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stock_log`
--
ALTER TABLE `stock_log`
  ADD CONSTRAINT `stock_log_costume_id_foreign` FOREIGN KEY (`costume_id`) REFERENCES `costumes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stock_log_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
