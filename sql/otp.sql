-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 27, 2025 at 05:13 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `otp`
--

-- --------------------------------------------------------

--
-- Table structure for table `otp_settings`
--

CREATE TABLE `otp_settings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `email_otp_enabled` tinyint(1) DEFAULT 1,
  `phone_otp_enabled` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `names` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `auth_code` varchar(100) NOT NULL,
  `otp` varchar(100) NOT NULL,
  `trials` int(3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `names`, `email`, `phone`, `password`, `is_verified`, `created_at`, `auth_code`, `otp`, `trials`) VALUES
(6, 'Mwimule Bienvenu', 'bienvenugashema@gmail.com', '250781300739', '$2y$10$yeoxS/eZhd6P6OeAVZN8xuKRKWMLoHIn/H/H3pn91fEvMZ2t4yZSm', 1, '2025-04-25 14:30:59', 'C4W5KIARBLEU6RIC', '', 0),
(7, 'GISUBIZO Erneste', 'ernestegisubizo15@gmail.com', '25078130055', '$2y$10$XY7WWTWT4SiH.ni.ItwXmuT67mk3N5Q3sDI5CI9CglUlVzCASZEP2', 1, '2025-04-25 14:45:12', 'EP47WHXYXN3U5SR7', '215075', 0);

-- --------------------------------------------------------

--
-- Table structure for table `waiting_users`
--

CREATE TABLE `waiting_users` (
  `waiting_id` int(11) NOT NULL,
  `names` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email_otp` varchar(6) NOT NULL,
  `phone_otp` varchar(6) NOT NULL,
  `auth_code` varchar(100) NOT NULL,
  `otp_expiry` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `waiting_users`
--

INSERT INTO `waiting_users` (`waiting_id`, `names`, `email`, `phone`, `password`, `email_otp`, `phone_otp`, `auth_code`, `otp_expiry`, `created_at`) VALUES
(36, 'Mwimule Bienvenu', 'mrdamour001@gmail.com', '250781300731', '$2y$10$tKbvABk7XWcnzEtIWeg/Ger280G4dKzJM55H3/QoXu72kHPw32hD6', '822763', '852472', 'VX4OBI3JTL66HXDR', '2025-04-25 09:59:40', '2025-04-25 09:59:40');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `otp_settings`
--
ALTER TABLE `otp_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `phone` (`phone`);

--
-- Indexes for table `waiting_users`
--
ALTER TABLE `waiting_users`
  ADD PRIMARY KEY (`waiting_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `phone` (`phone`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `otp_settings`
--
ALTER TABLE `otp_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `waiting_users`
--
ALTER TABLE `waiting_users`
  MODIFY `waiting_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `otp_settings`
--
ALTER TABLE `otp_settings`
  ADD CONSTRAINT `otp_settings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
