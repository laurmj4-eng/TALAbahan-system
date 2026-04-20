-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 20, 2026 at 12:13 PM
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
-- Database: `mj_chatbot`
--

-- --------------------------------------------------------

--
-- Table structure for table `firebase_users_tracking`
--

CREATE TABLE `firebase_users_tracking` (
  `uid` varchar(255) NOT NULL,
  `prompt_count` int(11) DEFAULT 0,
  `last_reset` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `firebase_users_tracking`
--

INSERT INTO `firebase_users_tracking` (`uid`, `prompt_count`, `last_reset`) VALUES
('user_bioysswhm', 0, '2026-04-20');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` varchar(20) DEFAULT 'staff',
  `password` varchar(255) NOT NULL,
  `prompt_count` int(11) DEFAULT 0,
  `last_reset` date DEFAULT curdate()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `role`, `password`, `prompt_count`, `last_reset`) VALUES
(2, 'admin_user', 'admin12345@gmail.com', 'admin', '$2y$10$I3pGQiDVIKDfV.KixpkdTuhr6JhaH/s7mPZ2PFRtWi6iSDSLI3.HC', 0, '2026-04-08'),
(3, 'staff_member', 'staff12345@gmail.com', 'staff', '$2y$10$xYNpeBlyRK/8/E5/I8nbsupnzPUN40OkbZf145UmiVEu2L/6gSgti', 0, '2026-04-08'),
(8, 'Mj Laurito', 'laurmj4@gmail.com', 'customer', '', 0, '2026-04-09'),
(13, 'laurito12345@gmail.com', 'laurito12345@gmail.com', 'customer', '$2y$10$XAuY70ZMSzxLcUXqTtARNuMSz5UuqK13GKX/WY6bbTelTRWXJIG7O', 0, '2026-04-20');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `firebase_users_tracking`
--
ALTER TABLE `firebase_users_tracking`
  ADD PRIMARY KEY (`uid`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
