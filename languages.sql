-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jun 30, 2026 at 04:45 PM
-- Server version: 10.4.19-MariaDB
-- PHP Version: 8.0.7

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `calamus_test`
--

-- --------------------------------------------------------

--
-- Table structure for table `languages`
--

CREATE TABLE `languages` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `certificate_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `code` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `module_code` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `primary_color` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `secondary_color` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_path` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `seal` varchar(1000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp(),
  `firebase_topic_user` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `firebase_topic_admin` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `languages`
--

INSERT INTO `languages` (`id`, `name`, `display_name`, `certificate_title`, `code`, `module_code`, `primary_color`, `secondary_color`, `image_path`, `seal`, `is_active`, `sort_order`, `created_at`, `updated_at`, `firebase_topic_user`, `firebase_topic_admin`) VALUES
(3, 'English', 'Easy English', 'English For Myanmar', 'english', 'ee', '#2196F3', '#1976D2', 'http://localhost/uploads/icons/1776752503_69e717771913d.png', 'http://localhost/uploads/icons/1777137345_69ecf6c1d8c96.png', 1, 1, '2026-01-02 06:38:34', '2026-01-02 06:38:34', 'englishUsers', 'calamusAdmin'),
(4, 'Korean', 'Easy Korean', 'Korean For Myanmar', 'korea', 'ko', '#FF9800', '#F57C00', '/img/easykorean.png', NULL, 1, 2, '2026-01-02 06:38:34', '2026-01-02 06:38:34', 'koreaUsers', 'calamusAdmin'),
(5, 'Chinese', 'Easy Chinese', NULL, 'chinese', 'cn', '#F44336', '#D32F2F', '/img/easychinese.png', NULL, 1, 3, '2026-01-02 06:38:34', '2026-01-02 06:38:34', 'chineseUsers', NULL),
(6, 'Japanese', 'Easy Japanese', NULL, 'japanese', 'jp', '#9C27B0', '#7B1FA2', '/img/easyjapanese.png', NULL, 1, 4, '2026-01-02 06:38:34', '2026-01-02 06:38:34', 'japaneseUsers', NULL),
(7, 'Russian', 'Easy Russian', NULL, 'russian', 'ru', '#4CAF50', '#388E3C', '/img/easyrussian.png', NULL, 1, 5, '2026-01-02 06:38:34', '2026-01-02 06:38:34', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `languages`
--
ALTER TABLE `languages`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `languages`
--
ALTER TABLE `languages`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
