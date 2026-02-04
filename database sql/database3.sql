-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 01, 2026 at 11:50 PM
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
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `status` varchar(50) DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `appointment_services`
--

CREATE TABLE `appointment_services` (
  `id` int(11) NOT NULL,
  `appointment_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `medical_answers`
--

CREATE TABLE `medical_answers` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `answer` enum('Yes','No') DEFAULT 'No',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medical_answers`
--

INSERT INTO `medical_answers` (`id`, `user_id`, `question_id`, `answer`, `updated_at`) VALUES
(22, 9, 6, 'Yes', '2026-02-01 22:38:32');

-- --------------------------------------------------------

--
-- Table structure for table `medical_history`
--

CREATE TABLE `medical_history` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `good_health` varchar(10) DEFAULT NULL,
  `heart_condition` varchar(10) DEFAULT NULL,
  `allergies` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `medical_questions`
--

CREATE TABLE `medical_questions` (
  `id` int(11) NOT NULL,
  `question_text` text NOT NULL,
  `question_type` enum('yes_no','text','checkbox') DEFAULT 'yes_no',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medical_questions`
--

INSERT INTO `medical_questions` (`id`, `question_text`, `question_type`, `created_at`) VALUES
(6, 'did you drink alcohol ?', 'yes_no', '2026-02-01 22:36:54');

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `service_name` varchar(100) NOT NULL,
  `price` decimal(10,2) DEFAULT NULL
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
  `user_type` varchar(20) DEFAULT 'patient',
  `dental_history` text DEFAULT NULL,
  `last_dental_visit` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reset_token` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `contact`, `password`, `user_type`, `dental_history`, `last_dental_visit`, `created_at`, `reset_token`) VALUES
(8, 'test', 'test@gmail.com', NULL, '$2y$10$Y5oIVvUAqdBdKUS3lx5FvOL84/xmuhyht1UblacWX8CuhSr7.fyxO', 'patient', NULL, NULL, '2026-02-01 10:06:26', 'e8531abe16cdd780e6ab2ddcf7ae423ce2fa104b970dd08a30d15da7f3da7c4932f9be7d3c7a01daae5b9db500a0b0db4251'),
(9, 'asdasd', 'asdasdasd@gmail.com', NULL, '$2y$10$2zVcEjV77i/EDq7uug8VxOeJ4NHzBoLeEnauUs3w.zJFei1E5mnje', 'patient', NULL, NULL, '2026-02-01 14:24:40', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users_managent`
--

CREATE TABLE `users_managent` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `birthday` date DEFAULT NULL,
  `mobile_number` varchar(20) DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `address` text DEFAULT NULL,
  `position` varchar(100) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `profile_pix` varchar(255) DEFAULT 'default_profile.png',
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expiry` datetime DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','staff') DEFAULT 'staff',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users_managent`
--

INSERT INTO `users_managent` (`id`, `name`, `first_name`, `middle_name`, `last_name`, `birthday`, `mobile_number`, `gender`, `address`, `position`, `remarks`, `profile_pix`, `reset_token`, `reset_expiry`, `email`, `password`, `role`, `created_at`) VALUES
(7, 'Admin User', 'peterdental', 'v', 'peter', '2000-06-23', '09887867', 'Male', 'asdasdasdasdasd', NULL, NULL, 'user_7_1769984129.png', 'c375e7fdba0bc52e5602ed1f71f1159780b90e58d5834a6fc54579e724d35eada472c8da646f19dbc7640a63f9f8edac039d', NULL, 'jaysongame27@gmail.com', '$2y$10$SzcMSXdxENsjurWsacMBPuEyy0p9zeyfx6338/peD5HavkjFnbnR2', 'admin', '2026-01-17 18:46:11'),
(9, 'staff', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'default_profile.png', NULL, NULL, 'staff@peterdental.com', '$2y$10$GadZFud3gUsOUICq/vLQoewpEFiXym.2ckZoN5WQsXmlMuF/djYgi', 'staff', '2026-01-17 19:42:16');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `appointment_services`
--
ALTER TABLE `appointment_services`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `medical_answers`
--
ALTER TABLE `medical_answers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_question` (`user_id`,`question_id`);

--
-- Indexes for table `medical_history`
--
ALTER TABLE `medical_history`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `medical_questions`
--
ALTER TABLE `medical_questions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `appointment_services`
--
ALTER TABLE `appointment_services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `medical_answers`
--
ALTER TABLE `medical_answers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `medical_history`
--
ALTER TABLE `medical_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `medical_questions`
--
ALTER TABLE `medical_questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `users_managent`
--
ALTER TABLE `users_managent`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
