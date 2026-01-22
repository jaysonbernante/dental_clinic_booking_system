-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 18, 2026 at 06:29 PM
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
-- Database: `dbclinic`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `dentist_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `status` enum('Pending','Confirmed','Completed','Canceled') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `user_id`, `dentist_id`, `service_id`, `appointment_date`, `appointment_time`, `status`, `created_at`) VALUES
(1, 1, 1, 1, '2026-01-20', '09:00:00', 'Canceled', '2026-01-17 16:38:28'),
(2, 1, 2, 4, '2026-01-22', '10:00:00', 'Canceled', '2026-01-17 16:38:28'),
(3, 2, 1, 2, '2026-01-21', '11:00:00', 'Pending', '2026-01-17 16:38:28'),
(4, 1, 1, 1, '2025-12-29', '11:00:00', 'Canceled', '2026-01-17 17:05:34'),
(5, 1, 1, 2, '2026-01-02', '10:00:00', 'Canceled', '2026-01-17 17:07:58'),
(6, 1, 2, 1, '2026-01-22', '09:00:00', 'Pending', '2026-01-17 17:23:18'),
(7, 1, 1, 2, '2026-01-19', '09:00:00', 'Confirmed', '2026-01-17 17:25:28'),
(8, 1, 1, 0, '2026-01-19', '10:00:00', 'Pending', '2026-01-17 17:42:17'),
(9, 1, 5, 0, '2026-01-29', '11:00:00', 'Confirmed', '2026-01-17 19:54:40'),
(10, 1, 6, 0, '2026-01-20', '14:00:00', 'Confirmed', '2026-01-17 19:55:31'),
(11, 1, 5, 0, '2026-01-21', '13:00:00', 'Confirmed', '2026-01-17 20:05:58'),
(12, 1, 5, 0, '2026-01-18', '09:00:00', 'Confirmed', '2026-01-17 20:16:08');

-- --------------------------------------------------------

--
-- Table structure for table `appointment_services`
--

CREATE TABLE `appointment_services` (
  `id` int(11) NOT NULL,
  `appointment_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointment_services`
--

INSERT INTO `appointment_services` (`id`, `appointment_id`, `service_id`) VALUES
(1, 8, 1),
(2, 9, 1),
(3, 10, 1),
(4, 11, 1),
(5, 12, 1);

-- --------------------------------------------------------

--
-- Table structure for table `dentists`
--

CREATE TABLE `dentists` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `specialization` varchar(100) DEFAULT NULL,
  `status` enum('Available','Unavailable') DEFAULT 'Available',
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dentists`
--

INSERT INTO `dentists` (`id`, `name`, `specialization`, `status`, `email`, `password`) VALUES
(5, 'Dr. Smith', 'General Dentistry', 'Available', 'dentist@peterdental.com', '$2y$10$9VEf6ojap.ckHiZGIm/LQeJafY0.y.caSFo7cl.S2fBEe7.Ru0JWy'),
(6, 'Dr. Johnson', 'Orthodontics', 'Available', 'dentist2@peterdental.com', '$2y$10$fOCyJuY1dH6qZAkXivhl2uIkULKapIkKVip9oUabNcwPhXLX3mA8O');

-- --------------------------------------------------------

--
-- Table structure for table `dentist_schedules`
--

CREATE TABLE `dentist_schedules` (
  `id` int(11) NOT NULL,
  `dentist_id` int(11) NOT NULL,
  `schedule_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `is_off` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dentist_schedules`
--

INSERT INTO `dentist_schedules` (`id`, `dentist_id`, `schedule_date`, `start_time`, `end_time`, `is_off`) VALUES
(1, 5, '2026-01-22', '08:06:00', '04:11:00', 1);

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `service_name` varchar(100) NOT NULL,
  `duration` int(11) NOT NULL COMMENT 'Duration in minutes',
  `price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `service_name`, `duration`, `price`) VALUES
(1, 'Cleaning', 30, 500.00),
(2, 'Filling', 45, 1200.00),
(3, 'Extraction', 60, 2500.00),
(4, 'Braces Consultation', 60, 2000.00);

-- --------------------------------------------------------

--
-- Table structure for table `time_slots`
--

CREATE TABLE `time_slots` (
  `id` int(11) NOT NULL,
  `slot_time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `contact` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `dental_history` text DEFAULT NULL,
  `last_dental_visit` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reset_token` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `contact`, `password`, `dental_history`, `last_dental_visit`, `created_at`, `reset_token`) VALUES
(1, 'test', 'test@gmail.com', 'asdasdasd', '$2y$10$VKsSwNjSOopeU/Xx5xN.qORoSE1KhzlV5WJHgsMvRikmBP3QOTD0a', 'asdasdasdasd', '2026-01-20', '2026-01-13 20:19:24', NULL),
(2, 'asd', 'asdasdasasdd@gmail.com', NULL, '$2y$10$18Jf/hrvje7HovF8ZOVZWuBzkQSPuC3nNyfCKZ4DJYBcbgJhvcgJy', NULL, NULL, '2026-01-15 11:46:33', NULL),
(4, 'jaysonbernante', 'jaysongame27@gmail.com', NULL, '$2y$10$ghpHZvjG7bf2gtltVMmA7uW.vGj28TwaAtZCmfYkBpJHyhRT13sM2', NULL, NULL, '2026-01-15 13:28:24', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users_managent`
--

CREATE TABLE `users_managent` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','staff') DEFAULT 'staff',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users_managent`
--

INSERT INTO `users_managent` (`id`, `name`, `email`, `password`, `role`, `created_at`) VALUES
(7, 'Admin User', 'admin@peterdental.com', '$2y$10$LsZOI8d1Bm2zGxhAS04dX.boL4NDyp57nq93HXKt0kP7.724QJrWa', 'admin', '2026-01-17 18:46:11'),
(9, 'staff', 'staff@peterdental.com', '$2y$10$GadZFud3gUsOUICq/vLQoewpEFiXym.2ckZoN5WQsXmlMuF/djYgi', 'staff', '2026-01-17 19:42:16');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `dentist_id` (`dentist_id`,`appointment_date`,`appointment_time`);

--
-- Indexes for table `appointment_services`
--
ALTER TABLE `appointment_services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `appointment_id` (`appointment_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Indexes for table `dentists`
--
ALTER TABLE `dentists`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `dentist_schedules`
--
ALTER TABLE `dentist_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dentist_id` (`dentist_id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `time_slots`
--
ALTER TABLE `time_slots`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slot_time` (`slot_time`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `users_managent`
--
ALTER TABLE `users_managent`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `appointment_services`
--
ALTER TABLE `appointment_services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `dentists`
--
ALTER TABLE `dentists`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `dentist_schedules`
--
ALTER TABLE `dentist_schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `time_slots`
--
ALTER TABLE `time_slots`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users_managent`
--
ALTER TABLE `users_managent`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointment_services`
--
ALTER TABLE `appointment_services`
  ADD CONSTRAINT `appointment_services_ibfk_1` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`),
  ADD CONSTRAINT `appointment_services_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`);

--
-- Constraints for table `dentist_schedules`
--
ALTER TABLE `dentist_schedules`
  ADD CONSTRAINT `dentist_schedules_ibfk_1` FOREIGN KEY (`dentist_id`) REFERENCES `dentists` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
