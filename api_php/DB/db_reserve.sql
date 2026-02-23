-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 23, 2026 at 09:54 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_reserve`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `booking_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `purpose` varchar(255) NOT NULL,
  `status` enum('Pending','Approved','Rejected','Cancelled') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`booking_id`, `user_id`, `room_id`, `start_time`, `end_time`, `purpose`, `status`, `created_at`) VALUES
(6, 6, 1, '2026-02-23 08:30:00', '2026-02-23 12:00:00', 'ประชุม', 'Approved', '2026-02-22 17:50:13'),
(7, 6, 1, '2026-02-23 13:00:00', '2026-02-23 16:00:00', 'ประชุม', 'Approved', '2026-02-22 17:51:25'),
(8, 7, 1, '2026-02-23 18:00:00', '2026-02-23 20:00:00', 'ประชุม', 'Cancelled', '2026-02-22 17:53:16'),
(9, 6, 1, '2026-02-24 15:52:00', '2026-02-25 15:55:00', 'ประชุม', 'Pending', '2026-02-23 08:52:46');

-- --------------------------------------------------------

--
-- Table structure for table `facilities`
--

CREATE TABLE `facilities` (
  `facility_id` int(11) NOT NULL,
  `facility_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `total_quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `facilities`
--

INSERT INTO `facilities` (`facility_id`, `facility_name`, `description`, `total_quantity`) VALUES
(1, 'โปรเจคเตอร์', 'โปรเจคเตอร์ Full HD สำหรับนำเสนองาน', 10),
(2, 'ไมโครโฟน', 'ไมโครโฟนไร้สายพร้อมขาตั้ง', 15),
(3, 'กระดานไวท์บอร์ด', 'กระดานไวท์บอร์ดพร้อมปากกาและลบกระดาน', 20),
(4, 'ลำโพง', 'ระบบเสียงสำหรับห้องประชุม', 8),
(5, 'ระบบวิดีโอคอล', 'อุปกรณ์ประชุมออนไลน์ Zoom/Teams', 5);

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `role_id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`role_id`, `role_name`, `description`) VALUES
(1, 'Admin', 'ผู้ดูแลระบบ มีสิทธิ์เต็ม'),
(2, 'Staff', 'เจ้าหน้าที่ดูแลการจองห้อง'),
(3, 'Student', 'นักศึกษาทั่วไป'),
(4, 'Teacher', 'อาจารย์ผู้สอน');

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `room_id` int(11) NOT NULL,
  `room_code` varchar(50) NOT NULL,
  `room_name` varchar(100) NOT NULL,
  `capacity` int(11) NOT NULL,
  `location` varchar(255) NOT NULL,
  `status` enum('Available','Unavailable','Maintenance') DEFAULT 'Available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`room_id`, `room_code`, `room_name`, `capacity`, `location`, `status`) VALUES
(1, 'RM-101', 'ห้องประชุม A', 20, 'อาคาร 1 ชั้น 1', 'Available'),
(2, 'RM-201', 'ห้องประชุม B', 30, 'อาคาร 2 ชั้น 1', 'Available'),
(3, 'RM-301', 'ห้องสัมมนาใหญ่', 100, 'อาคาร 3 ชั้น 2', 'Available'),
(4, 'RM-401', 'ห้องประชุมคณะ', 15, 'อาคาร 4 ชั้น 3', 'Available'),
(5, 'RM-501', 'ห้องปฏิบัติการคอม', 40, 'อาคาร 5 ชั้น 1', 'Maintenance');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `role_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password_hash`, `first_name`, `last_name`, `email`, `phone`, `role_id`, `created_at`) VALUES
(6, 'admin01', '$2y$10$e8/33ruGDnA5fyJvFa8lbOn7QQAeTzx86fdJUpegK8opLfIihgQby', 'สมชาย', 'ใจดี', 'admin@uni.ac.th', '0812345678', 1, '2026-02-22 17:47:38'),
(7, 'staff01', '$2y$10$DA07zV3W0SY6HU/W3HhPweG9IFQalrW6KgozHYojLgjtfW3aSzD4C', 'วิไล', 'รักงาน', 'staff@uni.ac.th', '0823456789', 2, '2026-02-22 17:47:38'),
(8, 'student01', '$2y$10$B18cDnzjyQYUBNMM3K1NK.Aj900kXhc8SJrwej.9.yRm29iTqleUq', 'ธนกร', 'มานะดี', 'student01@uni.ac.th', '0834567890', 3, '2026-02-22 17:47:38'),
(9, 'student02', '$2y$10$QU6oxFtUiZvajXeqQ9UayugBgHbg31vWcoH3QH7YPFf2p4W697P5S', 'พิมพ์ใจ', 'สุขสันต์', 'student02@uni.ac.th', '0845678901', 3, '2026-02-22 17:47:39'),
(10, 'teacher01', '$2y$10$7TJ99z.NsxtyJ7FXrInlduzhuWeQn8oQBibauL4Ht6T7yQrioz/2u', 'วรพล', 'ปัญญาดี', 'teacher@uni.ac.th', '0856789012', 4, '2026-02-22 17:47:39');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `room_id` (`room_id`);

--
-- Indexes for table `facilities`
--
ALTER TABLE `facilities`
  ADD PRIMARY KEY (`facility_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_id`),
  ADD UNIQUE KEY `role_name` (`role_name`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`room_id`),
  ADD UNIQUE KEY `room_code` (`room_code`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role_id` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `facilities`
--
ALTER TABLE `facilities`
  MODIFY `facility_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `room_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`room_id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
