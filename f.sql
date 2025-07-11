-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 12, 2025 at 03:28 PM
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
-- Database: `final`
--

-- --------------------------------------------------------

--
-- Table structure for table `chat`
--

CREATE TABLE `chat` (
  `id` int(11) NOT NULL,
  `owner_email` varchar(255) NOT NULL,
  `renter_email` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `item_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chat`
--

INSERT INTO `chat` (`id`, `owner_email`, `renter_email`, `message`, `timestamp`, `item_id`) VALUES
(1, '', 'kk@gmail.com', 'hello', '2025-03-12 13:55:48', 1),
(2, 'user@gmail.com', 'kk@gmail.com', 'hello', '2025-03-12 13:56:43', 2),
(3, 'user@gmail.com', 'kk@gmail.com', 'bye', '2025-03-12 13:56:52', 2),
(4, 'user@gmail.com', 'kk@gmail.com', 'kk', '2025-03-12 13:59:16', 2);

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_email` varchar(255) NOT NULL,
  `comment` text NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`id`, `product_id`, `user_email`, `comment`, `timestamp`) VALUES
(1, 20, 'user@gmail.com', 'hello', '2025-03-12 14:26:02'),
(2, 20, 'kk@gmail.com', 'sdfvdv', '2025-03-12 14:26:48'),
(3, 5, 'kk@gmail.com', 'vdd', '2025-03-12 14:27:18');

-- --------------------------------------------------------

--
-- Table structure for table `groups`
--

CREATE TABLE `groups` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `user_email` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `groups`
--

INSERT INTO `groups` (`id`, `name`, `description`, `user_email`, `created_at`) VALUES
(1, 'this is a group', 'hello', 'you@gmail.com', '2025-03-12 11:44:38'),
(2, 'this is a group', 'hhh', 'you@gmail.com', '2025-03-12 11:49:49');

-- --------------------------------------------------------

--
-- Table structure for table `group_chat`
--

CREATE TABLE `group_chat` (
  `id` int(11) NOT NULL,
  `group_id` int(11) DEFAULT NULL,
  `user_email` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `group_chat`
--

INSERT INTO `group_chat` (`id`, `group_id`, `user_email`, `message`, `created_at`) VALUES
(1, 1, 'you@gmail.com', 'hello\n', '2025-03-12 12:52:45'),
(2, 1, 'kk@gmail.com', 'hello', '2025-03-12 12:53:09');

-- --------------------------------------------------------

--
-- Table structure for table `group_memberships`
--

CREATE TABLE `group_memberships` (
  `user_email` varchar(255) NOT NULL,
  `group_id` int(11) NOT NULL,
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `group_memberships`
--

INSERT INTO `group_memberships` (`user_email`, `group_id`, `joined_at`) VALUES
('kk@gmail.com', 1, '2025-03-12 12:53:02'),
('kk@gmail.com', 2, '2025-03-12 13:37:24'),
('you@gmail.com', 1, '2025-03-12 12:26:19'),
('you@gmail.com', 2, '2025-03-12 12:22:05');

-- --------------------------------------------------------

--
-- Table structure for table `listings`
--

CREATE TABLE `listings` (
  `id` int(11) NOT NULL,
  `item_type` enum('item','service') NOT NULL,
  `name` varchar(255) NOT NULL,
  `price_per_day` decimal(10,2) NOT NULL,
  `quantity` int(11) DEFAULT NULL,
  `district` varchar(255) NOT NULL,
  `city` varchar(255) NOT NULL,
  `user_email` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image` longblob DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `listings`
--

INSERT INTO `listings` (`id`, `item_type`, `name`, `price_per_day`, `quantity`, `district`, `city`, `user_email`, `description`, `image`) VALUES
(5, 'service', 'dvsdv', 1.00, 0, 'gampaha', 'gampaha', 'kk@gmail.com', 'sdvvv ', NULL),
(6, 'service', 'hello', 1.00, 0, 'gampaha', 'Gampaha', 'kk@gmail.com', 'sv vfsfd', NULL),
(7, 'service', 'sdvdsv', 1.00, 0, 'gampaha', 'Gampaha', 'kk@gmail.com', NULL, NULL),
(8, 'service', 'this is a service', 1.00, 0, 'gampaha', 'Gampaha', 'kk@gmail.com', NULL, NULL),
(9, 'service', 'okay', 1.00, 0, 'gampaha', 'Gampaha', 'kk@gmail.com', NULL, NULL),
(10, 'service', 'okay', 1.00, 0, 'gampaha', 'Gampaha', 'user@gmail.com', NULL, NULL),
(11, 'item', 'qq', 5.00, 5, 'gampaha', 'Gampaha', 'user@gmail.com', NULL, NULL),
(12, 'service', 'okayyyyyyyyyy', 5.00, NULL, 'gampaha', 'Gampaha', 'user@gmail.com', 'hello ', NULL),
(13, 'service', 'okokoo', 10.00, NULL, 'gampaha', 'Kiribathgoda', 'user@gmail.com', 'this is a service', NULL),
(14, 'service', 'this is a service', 6.00, NULL, 'gampaha', 'Kiribathgoda', 'user@gmail.com', 'hello', NULL),
(15, 'service', 'ksddgvnsfvn', 8.00, NULL, 'gampaha', 'Kiribathgoda', 'user@gmail.com', 'wsgvrbrbg', NULL),
(16, 'item', 'okay', 5.00, 10, 'gampaha', 'Kiribathgoda', 'user@gmail.com', 'sdvfsb', NULL),
(17, 'service', 'sggre', 7.00, NULL, 'gampaha', 'Kiribathgoda', 'user@gmail.com', 'sgreeg', ''),
(18, 'service', 'hello this is a service', 13.00, NULL, 'gampaha', 'Kiribathgoda', 'user@gmail.com', 'hello this is a service', ''),
(19, 'service', 'dfffffffffffffffffffffffff', 25.00, NULL, 'gampaha', 'Kiribathgoda', 'user@gmail.com', 'dfffffffffffffffffffffffffffffffffffff', ''),
(20, 'service', 'jjj', 2.00, NULL, 'gampaha', 'Kiribathgoda', 'kk@gmail.com', 'dgvg', '');

-- --------------------------------------------------------

--
-- Table structure for table `message_votes`
--

CREATE TABLE `message_votes` (
  `id` int(11) NOT NULL,
  `message_id` int(11) DEFAULT NULL,
  `user_email` varchar(255) DEFAULT NULL,
  `vote_type` enum('upvote','downvote') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `message_votes`
--

INSERT INTO `message_votes` (`id`, `message_id`, `user_email`, `vote_type`) VALUES
(1, 1, 'kk@gmail.com', 'downvote'),
(2, 2, 'kk@gmail.com', 'upvote');

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` int(11) NOT NULL,
  `renter_email` varchar(255) NOT NULL,
  `lister_email` varchar(255) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `item_type` enum('item','service') NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `rent_date` date NOT NULL,
  `total_price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`id`, `renter_email`, `lister_email`, `item_name`, `item_type`, `price`, `quantity`, `rent_date`, `total_price`) VALUES
(1, 'kk@gmail.com', '', 'hello', 'item', 2.00, 1, '2025-03-12', 2.00),
(2, 'kk@gmail.com', 'user@gmail.com', 'qq', 'item', 5.00, 1, '2025-03-12', 5.00);

-- --------------------------------------------------------

--
-- Table structure for table `threads`
--

CREATE TABLE `threads` (
  `thread_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`) VALUES
(1, 'user1', 'user1@gmail.com', '$2y$10$Yz.6sR0/VZGjfX8scXKMUO9Gju2/HgkwZb9nRJH0CKMCYca5G/RWC'),
(2, 'kk', 'kk@gmail.com', '$2y$10$43QgpIL.ELrc75DdWnCSi.LvCuyO94VshyMX9sh4imOdlm/CX91iC'),
(3, 'me', 'me@gamil.com', '$2y$10$RyBr9aKPgDB0UcktCinhj.QvjYvWjB/L5HsydFTMGlLSRBMlvEzMm'),
(4, 'user', 'user@gmail.com', '$2y$10$sY2NHybxf5ul0t.I50H7AOpsv2EPgMLH91GS5pwxp1pHuvwwvWjr6'),
(5, 'qq', 'qq@gmail.com', '$2y$10$ZA8sXUG7bQhHO.BV5BHVqeH004Hq59CN.zNF7bVE3xnZaTtM8Q2qK'),
(6, 'you', 'you@gmail.com', '$2y$10$WGmcIPpP5aoGpqeBWnTuwepLabYUSmwtAL22vswPJalAYT8tCDvWC');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `chat`
--
ALTER TABLE `chat`
  ADD PRIMARY KEY (`id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `groups`
--
ALTER TABLE `groups`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `group_chat`
--
ALTER TABLE `group_chat`
  ADD PRIMARY KEY (`id`),
  ADD KEY `group_id` (`group_id`);

--
-- Indexes for table `group_memberships`
--
ALTER TABLE `group_memberships`
  ADD PRIMARY KEY (`user_email`,`group_id`),
  ADD KEY `group_id` (`group_id`);

--
-- Indexes for table `listings`
--
ALTER TABLE `listings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `message_votes`
--
ALTER TABLE `message_votes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `message_id` (`message_id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `threads`
--
ALTER TABLE `threads`
  ADD PRIMARY KEY (`thread_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `chat`
--
ALTER TABLE `chat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `groups`
--
ALTER TABLE `groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `group_chat`
--
ALTER TABLE `group_chat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `listings`
--
ALTER TABLE `listings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `message_votes`
--
ALTER TABLE `message_votes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `threads`
--
ALTER TABLE `threads`
  MODIFY `thread_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `chat`
--
ALTER TABLE `chat`
  ADD CONSTRAINT `chat_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `sales` (`id`);

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `listings` (`id`);

--
-- Constraints for table `group_chat`
--
ALTER TABLE `group_chat`
  ADD CONSTRAINT `group_chat_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `group_memberships`
--
ALTER TABLE `group_memberships`
  ADD CONSTRAINT `group_memberships_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`);

--
-- Constraints for table `message_votes`
--
ALTER TABLE `message_votes`
  ADD CONSTRAINT `message_votes_ibfk_1` FOREIGN KEY (`message_id`) REFERENCES `group_chat` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `threads`
--
ALTER TABLE `threads`
  ADD CONSTRAINT `threads_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
