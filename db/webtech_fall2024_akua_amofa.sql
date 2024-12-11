-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 03, 2024 at 11:05 PM
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
-- Database: `webtech_fall2024_akua_amofa`
--

-- --------------------------------------------------------

--
-- Table structure for table `assignments`
--

CREATE TABLE `assignments` (
  `assignment_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `course` varchar(100) DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `priority` enum('Low','Medium','High') DEFAULT 'Medium',
  `status` enum('Not Started','In Progress','Completed') DEFAULT 'Not Started',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `resources`
--

CREATE TABLE `resources` (
  `resource_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `file_type` varchar(50) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `downloads` int(11) DEFAULT 0,
  `rating` decimal(3,2) DEFAULT 0.00,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `studybuddyconnections`
--

CREATE TABLE `studybuddyconnections` (
  `connection_id` int(11) NOT NULL,
  `user_id1` int(11) DEFAULT NULL,
  `user_id2` int(11) DEFAULT NULL,
  `status` enum('Pending','Accepted','Rejected') DEFAULT 'Pending',
  `matched_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `studyprogress`
--

CREATE TABLE `studyprogress` (
  `progress_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `study_date` date DEFAULT NULL,
  `study_duration` int(11) DEFAULT NULL,
  `assignments_completed` int(11) DEFAULT 0,
  `productivity_score` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `major` varchar(100) DEFAULT NULL,
  `interests` text DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for messages
--

CREATE TABLE messages (
    message_id INT PRIMARY KEY AUTO_INCREMENT,
    sender_id INT NOT NULL,
    recipient_id INT NOT NULL,
    message_text TEXT NOT NULL,
    sent_at DATETIME NOT NULL,
    read_at DATETIME DEFAULT NULL,
    FOREIGN KEY (sender_id) REFERENCES users(user_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY (recipient_id) REFERENCES users(user_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    INDEX idx_sender (sender_id),
    INDEX idx_recipient (recipient_id),
    INDEX idx_sent_at (sent_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `assignments`
--
ALTER TABLE `assignments`
  ADD PRIMARY KEY (`assignment_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `resources`
--
ALTER TABLE `resources`
  ADD PRIMARY KEY (`resource_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_resources_category` (`category`);

--
-- Indexes for table `studybuddyconnections`
--
ALTER TABLE `studybuddyconnections`
  ADD PRIMARY KEY (`connection_id`),
  ADD KEY `user_id1` (`user_id1`),
  ADD KEY `user_id2` (`user_id2`);

--
-- Indexes for table `studyprogress`
--
ALTER TABLE `studyprogress`
  ADD PRIMARY KEY (`progress_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `assignments`
--
ALTER TABLE `assignments`
  MODIFY `assignment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `resources`
--
ALTER TABLE `resources`
  MODIFY `resource_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `studybuddyconnections`
--
ALTER TABLE `studybuddyconnections`
  MODIFY `connection_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `studyprogress`
--
ALTER TABLE `studyprogress`
  MODIFY `progress_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `assignments`
--
ALTER TABLE `assignments`
  ADD CONSTRAINT `assignments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `resources`
--
ALTER TABLE `resources`
  ADD CONSTRAINT `resources_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `studybuddyconnections`
--
ALTER TABLE `studybuddyconnections`
  ADD CONSTRAINT `studybuddyconnections_ibfk_1` FOREIGN KEY (`user_id1`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `studybuddyconnections_ibfk_2` FOREIGN KEY (`user_id2`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `studyprogress`
--
ALTER TABLE `studyprogress`
  ADD CONSTRAINT `studyprogress_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
