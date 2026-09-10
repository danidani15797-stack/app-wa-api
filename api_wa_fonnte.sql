-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 10, 2026 at 08:54 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `api_wa_fonnte`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `created_at`) VALUES
(1, 'admin', '$2b$12$phhZ8dhJSDEIivEiudTDa.E33GsH7Owzaxyfh94gskPWEX3DXRxzy', '2026-09-10 06:13:20');

-- --------------------------------------------------------

--
-- Table structure for table `log_whatsapp`
--

CREATE TABLE `log_whatsapp` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `no_tujuan` varchar(20) NOT NULL,
  `pesan` text NOT NULL,
  `status` enum('success','failed','pending') NOT NULL DEFAULT 'pending',
  `response` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `log_whatsapp`
--

INSERT INTO `log_whatsapp` (`id`, `user_id`, `no_tujuan`, `pesan`, `status`, `response`, `created_at`) VALUES
(1, NULL, '6285731637000', '━━━━━━━━━━━━━━━━━━━━━━\n📣 REGISTRASI AKUN BERHASIL\n━━━━━━━━━━━━━━━━━━━━━━\nWaktu: 10 September 2026 / 13:22\n\nHalo ahmad,\n\nSelamat datang di sistem kami. Registrasi akun Anda telah berhasil dilakukan dengan baik.\n\n📧 Email: ahmad@gmail.com\n📍 Alamat: ponorogo\n📱 Nomor WhatsApp: 6285731637000\n\nTerima kasih atas kepercayaan Anda untuk mendaftar dan menjadi bagian dari layanan kami.', 'success', '{\"detail\":\"success! message in queue\",\"id\":[178425550],\"process\":\"pending\",\"quota\":{\"083834626922\":{\"details\":\"deduced from total quota\",\"quota\":1000,\"remaining\":999,\"used\":1}},\"requestid\":724939073,\"status\":true,\"target\":[\"6285731637000\"]}', '2026-09-10 06:22:43'),
(2, NULL, '6285731637000', '━━━━━━━━━━━━━━━━━━━━━━\n📣 LOGIN BERHASIL\n━━━━━━━━━━━━━━━━━━━━━━\nWaktu: 10 September 2026 / 13:23\n\nHalo ahmad,\n\nKegiatan login akun Anda berhasil dilakukan pada sistem kami.\n\n📧 Email: ahmad@gmail.com\n📱 Nomor WhatsApp: 6285731637000\n🕒 Status: Login berhasil\n\nJika Anda merasa tidak melakukan login ini, segera hubungi administrator untuk keamanan akun Anda.', 'success', '{\"detail\":\"success! message in queue\",\"id\":[178425694],\"process\":\"pending\",\"quota\":{\"083834626922\":{\"details\":\"deduced from total quota\",\"quota\":999,\"remaining\":998,\"used\":1}},\"requestid\":724939751,\"status\":true,\"target\":[\"6285731637000\"]}', '2026-09-10 06:23:09'),
(3, NULL, '6285731637000', '━━━━━━━━━━━━━━━━━━━━━━\n📣 PEMBARUAN DATA AKUN\n━━━━━━━━━━━━━━━━━━━━━━\nWaktu: 10 September 2026 / 13:23\n\nHalo ahmad,\n\nData akun Anda telah diperbarui secara resmi oleh sistem kami.\n\n📧 Email: ahmadramadan@gmail.com\n📝 Perubahan yang dilakukan:\n- Email: ahmad@gmail.com → ahmadramadan@gmail.com\n\nJika Anda tidak melakukan perubahan ini, segera hubungi administrator untuk verifikasi lebih lanjut.', 'success', '{\"detail\":\"success! message in queue\",\"id\":[178425759],\"process\":\"pending\",\"quota\":{\"083834626922\":{\"details\":\"deduced from total quota\",\"quota\":998,\"remaining\":997,\"used\":1}},\"requestid\":724939965,\"status\":true,\"target\":[\"6285731637000\"]}', '2026-09-10 06:23:24'),
(4, NULL, '6285731637000', '━━━━━━━━━━━━━━━━━━━━━━\n📣 AKUN BERHASIL DIHAPUS\n━━━━━━━━━━━━━━━━━━━━━━\nWaktu: 10 September 2026 / 13:23\n\nHalo ahmad,\n\nDengan hormat, akun Anda telah berhasil dihapus dari sistem kami sesuai permintaan yang Anda ajukan.\n\n📧 Email: ahmadramadan@gmail.com\n📱 Nomor WhatsApp: 6285731637000\n\nTerima kasih telah menjadi bagian dari layanan kami. Semoga hari Anda selalu dalam keadaan baik.', 'success', '{\"detail\":\"success! message in queue\",\"id\":[178425796],\"process\":\"pending\",\"quota\":{\"083834626922\":{\"details\":\"deduced from total quota\",\"quota\":997,\"remaining\":996,\"used\":1}},\"requestid\":724940111,\"status\":true,\"target\":[\"6285731637000\"]}', '2026-09-10 06:23:34'),
(5, 2, '6285731637000', '━━━━━━━━━━━━━━━━━━━━━━\n📣 REGISTRASI AKUN BERHASIL\n━━━━━━━━━━━━━━━━━━━━━━\nWaktu: 10 September 2026 / 13:29\n\nHalo ahmad,\n\nSelamat datang di sistem kami. Registrasi akun Anda telah berhasil dilakukan dengan baik.\n\n📧 Email: ahmad@gmail.com\n📍 Alamat: ponorogo\n📱 Nomor WhatsApp: 6285731637000\n\nTerima kasih atas kepercayaan Anda untuk mendaftar dan menjadi bagian dari layanan kami.', 'success', '{\"detail\":\"success! message in queue\",\"id\":[178427289],\"process\":\"pending\",\"quota\":{\"083834626922\":{\"details\":\"deduced from total quota\",\"quota\":996,\"remaining\":995,\"used\":1}},\"requestid\":724948700,\"status\":true,\"target\":[\"6285731637000\"]}', '2026-09-10 06:29:57'),
(6, 2, '6285731637000', '━━━━━━━━━━━━━━━━━━━━━━\n📣 LOGIN BERHASIL\n━━━━━━━━━━━━━━━━━━━━━━\nWaktu: 10 September 2026 / 13:36\n\nHalo ahmad,\n\nKegiatan login akun Anda berhasil dilakukan pada sistem kami.\n\n📧 Email: ahmad@gmail.com\n📱 Nomor WhatsApp: 6285731637000\n🕒 Status: Login berhasil\n\nJika Anda merasa tidak melakukan login ini, segera hubungi administrator untuk keamanan akun Anda.', 'success', '{\"detail\":\"success! message in queue\",\"id\":[178428743],\"process\":\"pending\",\"quota\":{\"083834626922\":{\"details\":\"deduced from total quota\",\"quota\":995,\"remaining\":994,\"used\":1}},\"requestid\":724959919,\"status\":true,\"target\":[\"6285731637000\"]}', '2026-09-10 06:36:45'),
(7, 2, '6285731637000', '━━━━━━━━━━━━━━━━━━━━━━\n📣 LOGIN BERHASIL\n━━━━━━━━━━━━━━━━━━━━━━\nWaktu: 10 September 2026 / 13:44\n\nHalo ahmad,\n\nKegiatan login akun Anda berhasil dilakukan pada sistem kami.\n\n📧 Email: ahmad@gmail.com\n📱 Nomor WhatsApp: 6285731637000\n🕒 Status: Login berhasil\n\nJika Anda merasa tidak melakukan login ini, segera hubungi administrator untuk keamanan akun Anda.', 'success', '{\"detail\":\"success! message in queue\",\"id\":[178430554],\"process\":\"pending\",\"quota\":{\"083834626922\":{\"details\":\"deduced from total quota\",\"quota\":994,\"remaining\":993,\"used\":1}},\"requestid\":724972506,\"status\":true,\"target\":[\"6285731637000\"]}', '2026-09-10 06:44:28'),
(8, 2, '6285731637000', '━━━━━━━━━━━━━━━━━━━━━━\n📣 LOGIN BERHASIL\n━━━━━━━━━━━━━━━━━━━━━━\nWaktu: 10 September 2026 / 13:45\n\nHalo ahmad,\n\nKegiatan login akun Anda berhasil dilakukan pada sistem kami.\n\n📧 Email: ahmad@gmail.com\n📱 Nomor WhatsApp: 6285731637000\n🕒 Status: Login berhasil\n\nJika Anda merasa tidak melakukan login ini, segera hubungi administrator untuk keamanan akun Anda.', 'success', '{\"detail\":\"success! message in queue\",\"id\":[178430695],\"process\":\"pending\",\"quota\":{\"083834626922\":{\"details\":\"deduced from total quota\",\"quota\":993,\"remaining\":992,\"used\":1}},\"requestid\":724973990,\"status\":true,\"target\":[\"6285731637000\"]}', '2026-09-10 06:45:08');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `no_hp` varchar(20) NOT NULL,
  `alamat` text NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nama`, `email`, `no_hp`, `alamat`, `password`, `created_at`) VALUES
(2, 'ahmad', 'ahmad@gmail.com', '6285731637000', 'ponorogo', '$2y$10$kzhRGhxm3AVdz6dox.Q7aOVjZoC0rwXMqSR7DoY6jJzNi4Gnd8RtK', '2026-09-10 06:29:56');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `log_whatsapp`
--
ALTER TABLE `log_whatsapp`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_log_whatsapp_user` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `no_hp` (`no_hp`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `log_whatsapp`
--
ALTER TABLE `log_whatsapp`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `log_whatsapp`
--
ALTER TABLE `log_whatsapp`
  ADD CONSTRAINT `fk_log_whatsapp_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
