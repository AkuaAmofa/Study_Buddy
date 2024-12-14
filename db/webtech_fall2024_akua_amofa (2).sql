-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 14, 2024 at 12:49 AM
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

--
-- Dumping data for table `assignments`
--

INSERT INTO `assignments` (`assignment_id`, `user_id`, `title`, `description`, `course`, `due_date`, `priority`, `status`, `created_at`) VALUES
(2, 10, 'Webtech Final Project', 'My final webtech project worth 40%', 'Webtech', '2024-12-17', 'High', 'In Progress', '2024-12-10 23:14:53'),
(4, 10, 'Leadership Reflection', 'Leadership final Reflection', 'Leadership', '2024-12-16', 'Medium', 'Not Started', '2024-12-11 14:22:15');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `message_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `recipient_id` int(11) NOT NULL,
  `message_text` text NOT NULL,
  `sent_at` datetime NOT NULL,
  `read_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

--
-- Dumping data for table `resources`
--

INSERT INTO `resources` (`resource_id`, `user_id`, `title`, `description`, `file_path`, `file_type`, `category`, `downloads`, `rating`, `uploaded_at`) VALUES
(3, 10, 'SYSTEMS', 'Systems textbook', '675c61ed18458_1734107629.pdf', 'pdf', 'Textbook', 0, 0.00, '2024-12-13 16:33:49');

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_admin` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password`, `first_name`, `last_name`, `major`, `interests`, `profile_picture`, `created_at`, `is_admin`) VALUES
(1, 'AAkua', 'amofaakuadede@gmail.com', '$2y$10$OUlWtNFbqsvNo/RIlCeROuFrTiF.1hXQurjDnTaeRU2JkNzl4Z3gm', 'akua', 'amofa', NULL, NULL, NULL, '2024-12-03 22:42:17', 0),
(2, 'nanaamoako', 'nana.amoako@ashesi.edu.gh', '$2y$10$ciH/.5hDLY6taHt7ju7BpugEaEt71H19V5czOb8WAzzbPsoICqh3C', 'Nana', 'Amoako', NULL, NULL, NULL, '2024-12-04 22:44:37', 0),
(3, 'maameyaa', 'maame@ashesi.edu.gh', '$2y$10$sdrSSWonuAuj8rer7I7/XO6bXSihGYVrQvPV6lSVOmHWkxxp1Gr7u', 'Maame', 'Yaa', NULL, NULL, NULL, '2024-12-04 22:46:39', 0),
(4, 'elsielar', 'elsielartey0@gmail.com', '$2y$10$O3iF4WFwgevoFg0ppHTsoOCB1PbLPCAKxY8Tj144bylUQAFM7M0di', 'Elsie', 'Lartey', NULL, NULL, NULL, '2024-12-04 22:52:49', 0),
(5, 'kwakuamoako', 'nana.kwaku@kumi.edu', '$2y$10$HG2h24OMoID/huIxCakkoOY6bgxa/5leSLH8xnGeooOcJXZHYD7uG', 'Kwaku', 'Amoako', NULL, NULL, NULL, '2024-12-04 23:11:50', 0),
(6, 'AngelB', 'angelafia@gmail.com', '$2y$10$y.YwrfKFYsajwewdDIDWy.UfzkVDtOUpqzPXdu7FwLqzN4DA9LAPK', 'Angel', 'Amofa', NULL, NULL, NULL, '2024-12-04 23:19:10', 0),
(7, 'akua', 'akuaserwaaamofa16@gmail.com', '$2y$10$D.6qIVp/zzaewIACG9q9KuumNp0ktKToNPYxs7adJu4yk1eJlEjf.', 'Akua', 'Amofa', NULL, NULL, NULL, '2024-12-06 11:52:30', 0),
(8, 'king', 'king.acquah@ashesi.edu.gh', '$2y$10$dKENxeQTvkO9k9RutVqTE.q/YEZ0hOQYaBjcuCNFXYv34UXXAB73S', 'King', 'Acquah', NULL, NULL, NULL, '2024-12-09 21:23:29', 0),
(9, 'Kiki', 'kiki@ashesi.edu.gh', '$2y$10$pOqlXir1HuECOLcJ406cZO3piV0x4UW6nEFaeg3rLq5cC8Rue9bkO', 'KAkua', 'Birago', NULL, NULL, NULL, '2024-12-09 22:32:29', 0),
(10, 'maame1', 'maame@asheci.com', '$2y$10$tky/sMio54QMrV4WOM/WfeQ5KefgYrnKXWKdTZ6tDZ.RrPyhkL7yu', 'Maame', 'Sarps', 'MIS', 'Coding', NULL, '2024-12-10 12:01:31', 0),
(11, 'kyekye', 'maamedanquah@gmai8l.com', '$2y$10$TW6.zBtbjc57Y4DqqeLcreO/tMytkku2DijGbwbTk03VoxkEkYmqK', 'Rabby', 'Danquah', NULL, NULL, NULL, '2024-12-10 20:52:14', 0),
(13, 'admin', 'admin@studybuddy.com', '$2y$10$tH7KWupLPCRY2n81Yvcceexv5wZCHGsEIEJXQ9sIBkXgMxCSnl7YG', 'admin', 'user', NULL, NULL, NULL, '2024-12-11 21:15:03', 1),
(14, 'pati1', 'pati@ashesi.edu.gh', '$2y$10$xtt4EkJQuF/phMHT.OvD/eiJ5.ciAWbkFld4qGbPYIX/ixv/NqMka', 'Pati', 'Dufie', NULL, NULL, NULL, '2024-12-12 16:21:57', 0),
(15, 'Zoie1', 'zoie@ashesi.edu.gh', '$2y$10$lIu/jCvSFN6b/LsFb0WdAObNjaernikRcRhz0f6TyDNeTf80En0i2', 'Zioe', 'Atta', NULL, NULL, NULL, '2024-12-12 16:31:51', 0),
(16, 'Naana', 'naana@gmail.com', '$2y$10$cvyhXjKhK3O3WFnFeIeYk.C.tUi6J0blGHkXUl48e.bV4LvKD/ZWa', 'Naana', 'Araba', NULL, NULL, NULL, '2024-12-12 16:33:46', 0),
(17, 'Clifford', 'clifford@ashesi.edu.gh', '$2y$10$wKq3pyvNJwZ.8zfrDbhGuuZCxzFRKkx/omjFoDcmn1cREbTSAOwAK', 'Clifford', 'System', NULL, NULL, NULL, '2024-12-12 16:37:59', 0),
(18, 'PrincessC', 'pricheryl10@gmail.com', '$2y$10$F0T1rqFtTx1MiSz5XWr4BOqk3taPPHD8aG3nxQcdqUIq5FcvRvVl.', 'Princess', 'Donkor', 'MIS', 'Coding', 'pexels-pixabay-301920.jpg', '2024-12-13 21:12:34', 0);

-- --------------------------------------------------------

--
-- Table structure for table `user_connections`
--

CREATE TABLE `user_connections` (
  `connection_id` int(11) NOT NULL,
  `follower_id` int(11) NOT NULL,
  `following_id` int(11) NOT NULL,
  `connection_type` enum('friend','follower') NOT NULL,
  `status` enum('pending','accepted','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `idx_sender` (`sender_id`),
  ADD KEY `idx_recipient` (`recipient_id`),
  ADD KEY `idx_sent_at` (`sent_at`);

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
-- Indexes for table `user_connections`
--
ALTER TABLE `user_connections`
  ADD PRIMARY KEY (`connection_id`),
  ADD KEY `follower_id` (`follower_id`),
  ADD KEY `following_id` (`following_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `assignments`
--
ALTER TABLE `assignments`
  MODIFY `assignment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `resources`
--
ALTER TABLE `resources`
  MODIFY `resource_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `user_connections`
--
ALTER TABLE `user_connections`
  MODIFY `connection_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `assignments`
--
ALTER TABLE `assignments`
  ADD CONSTRAINT `assignments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`recipient_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

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

--
-- Constraints for table `user_connections`
--
ALTER TABLE `user_connections`
  ADD CONSTRAINT `user_connections_ibfk_1` FOREIGN KEY (`follower_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `user_connections_ibfk_2` FOREIGN KEY (`following_id`) REFERENCES `users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
