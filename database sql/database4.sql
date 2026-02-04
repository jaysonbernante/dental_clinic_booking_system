-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 03, 2026 at 11:11 AM
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
(22, 9, 6, 'Yes', '2026-02-01 22:38:32'),
(23, 10, 10, 'Yes', '2026-02-02 13:31:38'),
(24, 10, 11, 'No', '2026-02-02 13:31:38'),
(25, 10, 12, 'No', '2026-02-02 13:31:38'),
(26, 12, 10, 'Yes', '2026-02-03 05:25:50'),
(27, 12, 11, 'No', '2026-02-03 05:25:50'),
(28, 12, 12, 'No', '2026-02-03 05:25:50'),
(29, 13, 10, 'Yes', '2026-02-03 05:47:53'),
(30, 13, 11, 'Yes', '2026-02-03 05:46:38'),
(31, 13, 12, 'No', '2026-02-03 05:44:40'),
(44, 9, 10, 'No', '2026-02-03 09:07:34'),
(45, 9, 11, 'Yes', '2026-02-03 06:10:41'),
(46, 9, 12, 'Yes', '2026-02-03 06:10:41'),
(47, 14, 10, 'No', '2026-02-03 05:51:26'),
(48, 14, 11, 'No', '2026-02-03 05:51:26'),
(49, 14, 12, 'No', '2026-02-03 05:51:26');

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
(10, 'asdasd', 'yes_no', '2026-02-02 11:50:06'),
(11, 'did you drink alcohol ?', 'yes_no', '2026-02-02 11:54:00'),
(12, 'Are You gay ?', 'yes_no', '2026-02-02 11:54:05');

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
  `reset_token` varchar(255) DEFAULT NULL,
  `lockout_time` datetime DEFAULT NULL,
  `reset_token_expires` datetime DEFAULT NULL,
  `login_attempts` int(11) DEFAULT 0,
  `gender` enum('Male','Female') DEFAULT NULL,
  `birthday` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `contact`, `password`, `user_type`, `dental_history`, `last_dental_visit`, `created_at`, `reset_token`, `lockout_time`, `reset_token_expires`, `login_attempts`, `gender`, `birthday`) VALUES
(8, 'test', 'test@gmail.com', NULL, '$2y$10$Y5oIVvUAqdBdKUS3lx5FvOL84/xmuhyht1UblacWX8CuhSr7.fyxO', 'patient', NULL, NULL, '2026-02-01 10:06:26', '1efd960144db6a229dccb358e0a849b62986b91c0a9955f3ebcdd6445dec23c7625a9c91f3cbf326732c8567b94a3b01ea76', NULL, '2026-02-02 15:12:07', 2, NULL, NULL),
(9, 'asdasd', 'asdasdasd@gmail.com', NULL, '$2y$10$2zVcEjV77i/EDq7uug8VxOeJ4NHzBoLeEnauUs3w.zJFei1E5mnje', 'patient', NULL, NULL, '2026-02-01 14:24:40', NULL, NULL, NULL, 0, NULL, NULL),
(15, 'patient1 patient1', 'patient1@gmail.com', '098765434222', '$2y$10$IwDUpTQoBN2b4y62mBHlk.8t6d1W.rQnSvybFa094Ee6n1qgHtDXK', 'patient', 'asdasd', NULL, '2026-02-03 05:51:45', NULL, NULL, NULL, 0, 'Female', '2026-02-03');

-- --------------------------------------------------------

--
-- Table structure for table `users_management`
--

CREATE TABLE `users_management` (
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
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','staff') DEFAULT 'staff',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reset_token_expires` datetime DEFAULT NULL,
  `login_attempts` int(11) DEFAULT 0,
  `lockout_time` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users_management`
--

INSERT INTO `users_management` (`id`, `name`, `first_name`, `middle_name`, `last_name`, `birthday`, `mobile_number`, `gender`, `address`, `position`, `remarks`, `profile_pix`, `reset_token`, `email`, `password`, `role`, `created_at`, `reset_token_expires`, `login_attempts`, `lockout_time`) VALUES
(7, 'Admin User', 'peterdental', 'v', 'peter', '2000-06-23', '09887867', 'Male', 'asdasdasdasdasdasd', NULL, NULL, 'user_7_1770032345.jpg', NULL, 'jaysongame27@gmail.com', '$2y$10$LWf./r2tPXOMwnv2qjXzgue7xeHjejW6QeM7otM5PHTfxanFjmpIe', 'admin', '2026-01-17 18:46:11', NULL, 0, NULL),
(9, 'staff', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'default_profile.png', NULL, 'staff@peterdental.com', '$2y$10$GadZFud3gUsOUICq/vLQoewpEFiXym.2ckZoN5WQsXmlMuF/djYgi', 'staff', '2026-01-17 19:42:16', NULL, 0, NULL);

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
-- Indexes for table `users_management`
--
ALTER TABLE `users_management`
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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `medical_history`
--
ALTER TABLE `medical_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `medical_questions`
--
ALTER TABLE `medical_questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `users_management`
--
ALTER TABLE `users_management`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
