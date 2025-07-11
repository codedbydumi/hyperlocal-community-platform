-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jul 11, 2025 at 08:43 AM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

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

DROP TABLE IF EXISTS `chat`;
CREATE TABLE IF NOT EXISTS `chat` (
  `id` int NOT NULL AUTO_INCREMENT,
  `owner_email` varchar(255) NOT NULL,
  `renter_email` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `item_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `item_id` (`item_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `chat`
--

INSERT INTO `chat` (`id`, `owner_email`, `renter_email`, `message`, `timestamp`, `item_id`) VALUES
(1, '', 'kk@gmail.com', 'hello', '2025-03-12 13:55:48', 1),
(2, 'user@gmail.com', 'kk@gmail.com', 'hello', '2025-03-12 13:56:43', 2),
(3, 'user@gmail.com', 'kk@gmail.com', 'bye', '2025-03-12 13:56:52', 2),
(4, 'user@gmail.com', 'kk@gmail.com', 'kk', '2025-03-12 13:59:16', 2),
(5, '', 'kk@gmail.com', 'hello', '2025-03-18 11:59:55', 1);

-- --------------------------------------------------------

--
-- Table structure for table `groups`
--

DROP TABLE IF EXISTS `groups`;
CREATE TABLE IF NOT EXISTS `groups` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `user_email` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `groups`
--

INSERT INTO `groups` (`id`, `name`, `description`, `user_email`, `created_at`) VALUES
(4, 'idk', 'idk', 'qq@gmail.com', '2025-03-18 12:55:19'),
(10, 'music lovers', 'sd', 'ss@gmail.com', '2025-06-26 08:59:46'),
(11, 'Fitness & Wellness Tribe', 'Join local fitness coaches, yoga instructors, and wellness experts for sessions and advice.', 'new@gmail.com', '2025-07-11 07:34:45'),
(12, 'Skill Swap', 'Exchange skills like graphic design, writing, coding, or music lessons with others nearby.', 'new@gmail.com', '2025-07-11 07:46:11'),
(13, 'Game Night Crew', 'Connect with people for online and in-person gaming sessions and tournaments.', 'new@gmail.com', '2025-07-11 07:46:57'),
(14, 'Comedy & Memes Lounge', 'Laugh together! Share memes, comedy skits, and funny content daily.', 'new@gmail.com', '2025-07-11 07:48:23'),
(15, 'Theater & Arts Circle', 'A space for fans of live performances, theater, and artistic expression.', 'new@gmail.com', '2025-07-11 07:48:43');

-- --------------------------------------------------------

--
-- Table structure for table `group_chat`
--

DROP TABLE IF EXISTS `group_chat`;
CREATE TABLE IF NOT EXISTS `group_chat` (
  `id` int NOT NULL AUTO_INCREMENT,
  `group_id` int DEFAULT NULL,
  `user_email` varchar(255) DEFAULT NULL,
  `message` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `group_id` (`group_id`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `group_chat`
--

INSERT INTO `group_chat` (`id`, `group_id`, `user_email`, `message`, `created_at`) VALUES
(30, 12, 'new@gmail.com', 'hii', '2025-07-11 07:47:18'),
(31, 12, 'new@gmail.com', 'h', '2025-07-11 08:17:33');

-- --------------------------------------------------------

--
-- Table structure for table `group_memberships`
--

DROP TABLE IF EXISTS `group_memberships`;
CREATE TABLE IF NOT EXISTS `group_memberships` (
  `user_email` varchar(255) NOT NULL,
  `group_id` int NOT NULL,
  `joined_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_email`,`group_id`),
  KEY `group_id` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `group_memberships`
--

INSERT INTO `group_memberships` (`user_email`, `group_id`, `joined_at`) VALUES
('new@gmail.com', 12, '2025-07-11 07:47:04');

-- --------------------------------------------------------

--
-- Table structure for table `listings`
--

DROP TABLE IF EXISTS `listings`;
CREATE TABLE IF NOT EXISTS `listings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `item_type` enum('item','service') NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `price_per_day` decimal(10,2) NOT NULL,
  `quantity` int DEFAULT NULL,
  `district` varchar(255) NOT NULL,
  `city` varchar(255) NOT NULL,
  `user_email` varchar(255) DEFAULT NULL,
  `description` text,
  `image` longblob,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `listings`
--

INSERT INTO `listings` (`id`, `item_type`, `category`, `name`, `price_per_day`, `quantity`, `district`, `city`, `user_email`, `description`, `image`) VALUES
(44, 'service', 'cleaning_services', 'House Cleaning Service', 15.00, NULL, 'texas', 'Houston', 'new@gmail.com', 'Professional weekly cleaning services', 0x75706c6f6164732f363837306233613235346638655f696d616765732e6a7067),
(46, 'item', 'Tools & Equipment', 'Electric Drill', 6.00, 3, 'texas', 'Austin', 'user11@example.com', 'Heavy-duty drill suitable for wood and metal work.', 0x75706c6f6164732f6472696c6c2e6a7067),
(48, 'item', 'Tools & Equipment', 'Power Screwdriver', 4.00, 2, 'texas', 'Dallas', 'tools@example.com', 'Cordless power screwdriver, lightweight and rechargeable.', 0x75706c6f6164732f7363722e6a7067),
(49, 'item', 'Tools & Equipment', 'Circular Saw', 7.50, 1, 'california', 'Los Angeles', 'builder@example.com', 'Electric circular saw perfect for woodworking and framing.', 0x75706c6f6164732f63697263756c61725f7361772e6a7067),
(50, 'item', 'Fitness Equipment', 'Adjustable Dumbbell Set (50 lbs)', 6.00, 2, 'texas', 'Houston', 'fitrent@example.com', 'Space-saving adjustable dumbbells for strength training at home.', 0x75706c6f6164732f64756d6262656c6c732e6a7067),
(51, 'item', 'Clothing & Accessories', 'Formal Tuxedo Set (M Size)', 8.00, 1, 'new_york', 'New York City', 'stylishrent@example.com', 'Complete black tuxedo set perfect for weddings and formal events.', 0x75706c6f6164732f74757865646f2e6a7067),
(52, 'item', 'Vehicles', 'Cargo Van (Ford Transit)', 55.00, 1, 'florida', 'Tampa', 'vanshare@example.com', 'Spacious cargo van ideal for moving, delivery, or travel.', 0x75706c6f6164732f636172676f5f76616e2e6a7067),
(53, 'item', 'Musical Instruments', 'Acoustic Guitar (Yamaha F310)', 6.00, 1, 'california', 'San Jose', 'musicgear@example.com', 'Steel-string acoustic guitar with a warm, full tone — great for beginners and performers.', 0x75706c6f6164732f61636f75737469635f6775697461722e6a7067),
(54, 'item', 'Kitchen & Appliances', 'Commercial Blender (1500W)', 4.50, 1, 'california', 'San Diego', 'cookrent@example.com', 'Heavy-duty blender great for smoothies, soups, and sauces.', 0x75706c6f6164732f626c656e6465722e6a7067),
(55, 'service', 'Home Services', 'Handyman for Small Repairs', 60.00, 1, 'california', 'San Francisco', 'homeservice@example.com', 'Experienced handyman for furniture assembly, curtain installation, and general fixes.', 0x75706c6f6164732f68616e64796d616e2e6a7067),
(56, 'service', 'Transportation', 'Moving Van with Driver', 90.00, 1, 'texas', 'Dallas', 'transport@example.com', 'Cargo van with experienced driver, available for local and long-distance moves.', 0x75706c6f6164732f6d6f76696e675f76616e2e6a7067),
(57, 'service', 'Event Services', 'Birthday Party Decoration Service', 120.00, 1, 'florida', 'Miami', 'events@example.com', 'Balloon setup, banners, lighting, and themes tailored for any party occasion.', 0x75706c6f6164732f70617274795f6465636f7261746f722e6a7067),
(58, 'service', 'Professional Services', 'Certified Tax Filing Assistance', 75.00, 1, 'new_york', 'New York City', 'taxpro@example.com', 'Filing federal and state tax returns for individuals and small businesses.', 0x75706c6f6164732f7461785f736572766963652e6a7067),
(59, 'service', 'Personal Care', 'Licensed Massage Therapist (1-Hour)', 85.00, 1, 'california', 'Los Angeles', 'wellness@example.com', 'Swedish and deep tissue massage at your home or office. Certified and insured.', 0x75706c6f6164732f6d6173736167652e6a7067);

-- --------------------------------------------------------

--
-- Table structure for table `message_votes`
--

DROP TABLE IF EXISTS `message_votes`;
CREATE TABLE IF NOT EXISTS `message_votes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `message_id` int DEFAULT NULL,
  `user_email` varchar(255) DEFAULT NULL,
  `vote_type` enum('upvote','downvote') DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `message_id` (`message_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_email` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_email` (`user_email`),
  KEY `is_read` (`is_read`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_confirmations`
--

DROP TABLE IF EXISTS `order_confirmations`;
CREATE TABLE IF NOT EXISTS `order_confirmations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sale_id` int NOT NULL,
  `renter_email` varchar(255) NOT NULL,
  `lister_email` varchar(255) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `delivery_method` varchar(20) DEFAULT NULL,
  `status` enum('pending','confirmed','cancelled') DEFAULT 'pending',
  `confirmation_date` timestamp NULL DEFAULT NULL,
  `cancellation_date` timestamp NULL DEFAULT NULL,
  `read_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sale_id` (`sale_id`)
) ENGINE=MyISAM AUTO_INCREMENT=51 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `order_confirmations`
--

INSERT INTO `order_confirmations` (`id`, `sale_id`, `renter_email`, `lister_email`, `item_name`, `delivery_method`, `status`, `confirmation_date`, `cancellation_date`, `read_date`) VALUES
(1, 28, 'duminduthushan9@gmail.com', '', 'okay', 'pickup', 'pending', NULL, NULL, NULL),
(2, 29, 'duminduthushan9@gmail.com', '', 'okay', 'pickup', 'pending', NULL, NULL, NULL),
(3, 30, 'duminduthushan9@gmail.com', '', 'okay', 'pickup', 'pending', NULL, NULL, NULL),
(4, 31, 'duminduthushan9@gmail.com', '', 'okay', 'pickup', 'pending', NULL, NULL, NULL),
(5, 32, 'duminduthushan9@gmail.com', '', 'okay', 'pickup', 'pending', NULL, NULL, NULL),
(6, 33, 'duminduthushan9@gmail.com', '', 'okay', 'pickup', 'pending', NULL, NULL, NULL),
(7, 34, 'duminduthushan9@gmail.com', '', 'okay', 'pickup', 'pending', NULL, NULL, NULL),
(8, 35, 'duminduthushan9@gmail.com', '', 'okay', 'pickup', 'pending', NULL, NULL, NULL),
(9, 36, 'duminduthushan9@gmail.com', '', 'okay', 'pickup', 'pending', NULL, NULL, NULL),
(10, 37, 'duminduthushan9@gmail.com', '', 'okay', 'pickup', 'pending', NULL, NULL, NULL),
(11, 38, 'duminduthushan9@gmail.com', '', 'okay', 'pickup', 'pending', NULL, NULL, NULL),
(12, 39, 'duminduthushan9@gmail.com', '', 'okay', 'pickup', 'pending', NULL, NULL, NULL),
(13, 40, 'duminduthushan9@gmail.com', '', 'okay', 'pickup', 'pending', NULL, NULL, NULL),
(14, 41, 'duminduthushan9@gmail.com', '', 'okay', 'pickup', 'pending', NULL, NULL, NULL),
(15, 42, 'duminduthushan9@gmail.com', '', 'okay', 'pickup', 'pending', NULL, NULL, NULL),
(16, 43, 'duminduthushan9@gmail.com', '', 'sdvdsv', 'pickup', 'pending', NULL, NULL, NULL),
(17, 44, 'duminduthushan9@gmail.com', '', 'hello', 'delivery', 'pending', NULL, NULL, NULL),
(18, 45, 'duminduthushan9@gmail.com', '', 'hello', 'pickup', 'pending', NULL, NULL, NULL),
(19, 46, 'duminduthushan9@gmail.com', '', 'dvsdv', 'pickup', 'pending', NULL, NULL, NULL),
(20, 47, 'duminduthushan9@gmail.com', '', 'de', 'pickup', 'pending', NULL, NULL, NULL),
(21, 48, 'ss@gmail.com', '', 'de', 'pickup', 'pending', NULL, NULL, NULL),
(22, 49, 'ss@gmail.com', 'asd1@gmail.com', '12', 'pickup', 'confirmed', NULL, NULL, NULL),
(23, 50, 'ss@gmail.com', '', 'hello', 'pickup', 'pending', NULL, NULL, NULL),
(24, 51, 'ss@gmail.com', '', 'okayyyyyyyyyy', 'pickup', 'pending', NULL, NULL, NULL),
(25, 52, 'ss@gmail.com', 'ss@gmail.com', 'hello', 'delivery', 'cancelled', NULL, NULL, NULL),
(27, 54, 'asd1@gmail.com', '', 'ppppppppppppppppp', 'pickup', 'pending', NULL, NULL, NULL),
(28, 55, 'asd1@gmail.com', '', 'idk', 'pickup', 'pending', NULL, NULL, NULL),
(29, 56, 'asd1@gmail.com', '', '12', 'pickup', 'pending', NULL, NULL, NULL),
(30, 57, 'asd1@gmail.com', '', 'dum', 'pickup', 'pending', NULL, NULL, NULL),
(31, 58, 'ss@gmail.com', '', 'dum', 'pickup', 'pending', NULL, NULL, NULL),
(32, 59, 'ss@gmail.com', '', 'okayyyyyyyyyy', 'pickup', 'pending', NULL, NULL, NULL),
(33, 60, 'ss@gmail.com', '', 'okayyyyyyyyyy', 'pickup', 'pending', NULL, NULL, NULL),
(34, 61, 'ss@gmail.com', 'user@gmail.com', 'okayyyyyyyyyy', 'pickup', 'pending', NULL, NULL, NULL),
(35, 62, 'ss@gmail.com', 'user@gmail.com', 'okayyyyyyyyyy', 'delivery', 'pending', NULL, NULL, NULL),
(36, 63, 'ss@gmail.com', 'asd1@gmail.com', '12', 'pickup', 'pending', NULL, NULL, NULL),
(37, 64, 'ss@gmail.com', 'ss@gmail.com', 'dumwww', 'pickup', 'confirmed', NULL, NULL, NULL),
(38, 65, 'ss@gmail.com', 'ss@gmail.com', 'dumwww', 'pickup', 'confirmed', NULL, NULL, NULL),
(39, 66, 'asd1@gmail.com', 'kk@gmail.com', 'hello', 'delivery', 'pending', NULL, NULL, NULL),
(40, 67, 'nogove7447@nab4.com', 'user@gmail.com', 'okay', 'pickup', 'pending', NULL, NULL, NULL),
(41, 68, 'ss@gmail.com', 'my@gmail.com', 'Dumindu Thushan', 'pickup', 'pending', NULL, NULL, NULL),
(42, 69, 'ss@gmail.com', 'kk@gmail.com', 'dvsdv', 'delivery', 'pending', NULL, NULL, NULL),
(43, 70, 'new@gmail.com', 'kk@gmail.com', 'dvsdv', 'pickup', 'confirmed', NULL, NULL, NULL),
(44, 71, 'new@gmail.com', 'kk@gmail.com', 'dvsdv', 'delivery', '', NULL, NULL, NULL),
(45, 72, 'new@gmail.com', 'user@gmail.com', 'qq', 'pickup', 'confirmed', NULL, NULL, NULL),
(46, 73, 'new@gmail.com', 'asd1@gmail.com', '12', 'pickup', '', NULL, NULL, NULL),
(47, 74, 'new@gmail.com', 'new@gmail.com', 'aadf', 'pickup', '', NULL, NULL, NULL),
(48, 75, 'new@gmail.com', 'new@gmail.com', 'drr', 'delivery', 'confirmed', NULL, NULL, NULL),
(49, 76, 'new@gmail.com', 'new@gmail.com', 'drr', 'pickup', 'confirmed', NULL, NULL, NULL),
(50, 77, 'new@gmail.com', 'fitrent@example.com', 'Adjustable Dumbbell Set (50 lbs)', 'pickup', '', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

DROP TABLE IF EXISTS `sales`;
CREATE TABLE IF NOT EXISTS `sales` (
  `id` int NOT NULL AUTO_INCREMENT,
  `renter_email` varchar(255) NOT NULL,
  `lister_email` varchar(255) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `item_type` enum('item','service') NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int DEFAULT '1',
  `rent_date` date NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `rental_time` time DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `delivery_method` varchar(20) DEFAULT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `status` enum('pending','confirmed','cancelled') DEFAULT 'pending',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=78 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`id`, `renter_email`, `lister_email`, `item_name`, `item_type`, `price`, `quantity`, `rent_date`, `start_date`, `end_date`, `rental_time`, `phone_number`, `delivery_method`, `total_price`, `status`) VALUES
(1, 'kk@gmail.com', 'asd1@gmail.com', 'hello', 'item', 2.00, 1, '2025-03-12', NULL, NULL, NULL, NULL, NULL, 2.00, 'pending'),
(2, 'kk@gmail.com', 'user@gmail.com', 'qq', 'item', 5.00, 1, '2025-03-12', NULL, NULL, NULL, NULL, NULL, 5.00, 'pending'),
(3, 'qq@gmail.com', '', 'dvsdv', 'service', 1.00, 0, '2025-03-18', NULL, NULL, NULL, NULL, NULL, 1.00, 'pending'),
(4, 'qq@gmail.com', '', 'dvsdv', 'service', 1.00, 1, '2025-03-19', NULL, NULL, NULL, NULL, NULL, 1.00, 'pending'),
(5, 'qq@gmail.com', '', 'ppppppppppppppp', 'service', 11.00, 1, '2025-03-26', NULL, NULL, NULL, NULL, NULL, 11.00, 'pending'),
(6, 'qq@gmail.com', '', 'dvsdv', 'service', 1.00, 1, '2025-03-26', NULL, NULL, NULL, NULL, NULL, 1.00, 'pending'),
(7, 'qq@gmail.com', 'kk@gmail.com', 'dvsdv', 'service', 1.00, 2, '2025-03-19', NULL, NULL, NULL, NULL, NULL, 2.00, 'pending'),
(8, 'asd@gmail.com', 'kk@gmail.com', 'dvsdv', 'service', 1.00, 1, '2025-03-12', NULL, NULL, NULL, NULL, NULL, 1.00, 'pending'),
(9, 'asd@gmail.com', 'kk@gmail.com', 'jjj', 'service', 2.00, 1, '2025-03-12', NULL, NULL, NULL, NULL, NULL, 2.00, 'pending'),
(10, 'kk@gmail.com', '', 'dvsdv', 'service', 1.00, 0, '2025-03-19', NULL, NULL, NULL, NULL, NULL, 1.00, 'pending'),
(11, 'my@gmail.com', '', 'Dumindu Thushan', 'item', 10.00, 12, '2025-04-29', NULL, NULL, NULL, NULL, NULL, 10.00, 'pending'),
(12, 'asd@gmail.com', 'my@gmail.com', 'Dumindu Thushan', 'item', 10.00, 2, '2025-04-10', NULL, NULL, NULL, NULL, NULL, 20.00, 'pending'),
(13, 'my@gmail.com', '', 'Dumindu Thushan', 'item', 10.00, 12, '2025-05-11', NULL, NULL, NULL, NULL, NULL, 10.00, 'pending'),
(14, 'my@gmail.com', '', 'Dumindu Thushan', 'item', 10.00, 12, '2025-05-11', NULL, NULL, NULL, NULL, NULL, 10.00, 'pending'),
(17, 'duminduthushan9@gmail.com', '', 'hello', 'service', 1.00, 1, '2025-06-16', '2025-07-01', '2025-07-30', '18:58:00', '0761172158', NULL, 30.00, 'pending'),
(18, 'duminduthushan9@gmail.com', '', 'Dumindu Thushan', 'item', 10.00, 1, '2025-06-16', '2025-06-25', '2025-06-28', '06:06:00', '0761172158', NULL, 40.00, 'pending'),
(19, 'duminduthushan9@gmail.com', '', 'Dumindu Thushan', 'item', 10.00, 1, '2025-06-16', '2025-06-25', '2025-06-28', '06:06:00', '0761172158', NULL, 40.00, 'pending'),
(20, 'duminduthushan9@gmail.com', '', 'Dumindu Thushan', 'item', 10.00, 1, '2025-06-16', '2025-06-25', '2025-06-28', '06:06:00', '0761172158', NULL, 40.00, 'pending'),
(21, 'duminduthushan9@gmail.com', '', 'Dumindu Thushan', 'item', 10.00, 1, '2025-06-16', '2025-06-25', '2025-06-28', '06:06:00', '0761172158', NULL, 40.00, 'pending'),
(22, 'duminduthushan9@gmail.com', '', 'Dumindu Thushan', 'item', 10.00, 1, '2025-06-16', '2025-06-25', '2025-06-28', '06:06:00', '0761172158', NULL, 40.00, 'pending'),
(23, 'duminduthushan9@gmail.com', '', 'Dumindu Thushan', 'item', 10.00, 1, '2025-06-16', '2025-06-25', '2025-06-28', '06:06:00', '0761172158', NULL, 40.00, 'pending'),
(24, 'duminduthushan9@gmail.com', '', 'Dumindu Thushan', 'item', 10.00, 1, '2025-06-16', '2025-06-25', '2025-06-28', '06:06:00', '0761172158', NULL, 40.00, 'pending'),
(25, 'duminduthushan9@gmail.com', '', 'this is a service', 'service', 1.00, 1, '2025-06-16', '2025-06-17', '2025-06-18', '07:40:00', '0761172158', NULL, 2.00, 'pending'),
(26, 'duminduthushan9@gmail.com', '', 'this is a service', 'service', 1.00, 1, '2025-06-16', '2025-06-17', '2025-06-18', '07:40:00', '0761172158', NULL, 2.00, 'pending'),
(27, 'duminduthushan9@gmail.com', '', 'this is a service', 'service', 1.00, 1, '2025-06-16', '2025-06-17', '2025-06-18', '07:40:00', '0761172158', NULL, 2.00, 'pending'),
(28, 'duminduthushan9@gmail.com', '', 'okay', 'service', 1.00, 1, '2025-06-16', '2025-06-26', '2025-06-30', '12:21:00', '0761172158', 'pickup', 5.00, 'pending'),
(29, 'duminduthushan9@gmail.com', '', 'okay', 'service', 1.00, 1, '2025-06-16', '2025-06-26', '2025-06-30', '12:21:00', '0761172158', 'pickup', 5.00, 'pending'),
(30, 'duminduthushan9@gmail.com', '', 'okay', 'service', 1.00, 1, '2025-06-16', '2025-06-26', '2025-06-30', '12:21:00', '0761172158', 'pickup', 5.00, 'pending'),
(31, 'duminduthushan9@gmail.com', '', 'okay', 'service', 1.00, 1, '2025-06-16', '2025-06-26', '2025-06-30', '12:21:00', '0761172158', 'pickup', 5.00, 'pending'),
(32, 'duminduthushan9@gmail.com', '', 'okay', 'service', 1.00, 1, '2025-06-16', '2025-06-26', '2025-06-30', '12:21:00', '0761172158', 'pickup', 5.00, 'pending'),
(33, 'duminduthushan9@gmail.com', '', 'okay', 'service', 1.00, 1, '2025-06-16', '2025-06-26', '2025-06-30', '12:21:00', '0761172158', 'pickup', 5.00, 'pending'),
(34, 'duminduthushan9@gmail.com', '', 'okay', 'service', 1.00, 1, '2025-06-16', '2025-06-26', '2025-06-30', '12:21:00', '0761172158', 'pickup', 5.00, 'pending'),
(35, 'duminduthushan9@gmail.com', '', 'okay', 'service', 1.00, 1, '2025-06-16', '2025-06-26', '2025-06-30', '12:21:00', '0761172158', 'pickup', 5.00, 'pending'),
(36, 'duminduthushan9@gmail.com', '', 'okay', 'service', 1.00, 1, '2025-06-16', '2025-06-26', '2025-06-30', '12:21:00', '0761172158', 'pickup', 5.00, 'pending'),
(37, 'duminduthushan9@gmail.com', '', 'okay', 'service', 1.00, 1, '2025-06-16', '2025-06-26', '2025-06-30', '12:21:00', '0761172158', 'pickup', 5.00, 'pending'),
(38, 'duminduthushan9@gmail.com', '', 'okay', 'service', 1.00, 1, '2025-06-16', '2025-06-26', '2025-06-30', '12:21:00', '0761172158', 'pickup', 5.00, 'pending'),
(39, 'duminduthushan9@gmail.com', '', 'okay', 'service', 1.00, 1, '2025-06-16', '2025-06-26', '2025-06-30', '12:21:00', '0761172158', 'pickup', 5.00, 'pending'),
(40, 'duminduthushan9@gmail.com', '', 'okay', 'service', 1.00, 1, '2025-06-16', '2025-06-26', '2025-06-30', '12:21:00', '0761172158', 'pickup', 5.00, 'pending'),
(41, 'duminduthushan9@gmail.com', '', 'okay', 'service', 1.00, 1, '2025-06-16', '2025-06-26', '2025-06-30', '12:21:00', '0761172158', 'pickup', 5.00, 'pending'),
(42, 'duminduthushan9@gmail.com', '', 'okay', 'service', 1.00, 1, '2025-06-16', '2025-06-26', '2025-06-30', '12:21:00', '0761172158', 'pickup', 5.00, 'pending'),
(43, 'duminduthushan9@gmail.com', '', 'sdvdsv', 'service', 1.00, 1, '2025-06-16', '2025-07-04', '2025-08-06', '23:29:00', '0761172158', 'pickup', 34.00, 'pending'),
(44, 'duminduthushan9@gmail.com', '', 'hello', 'service', 1.00, 1, '2025-06-16', '2025-06-26', '2025-06-26', '23:38:00', '0761172158', 'delivery', 1.00, 'pending'),
(45, 'duminduthushan9@gmail.com', '', 'hello', 'service', 1.00, 1, '2025-06-16', '2025-07-03', '2025-08-13', '12:49:00', '0761172158', 'pickup', 42.00, 'pending'),
(46, 'duminduthushan9@gmail.com', '', 'dvsdv', 'service', 1.00, 1, '2025-06-16', '2025-06-26', '2025-06-30', '23:54:00', '0761172158', 'pickup', 5.00, 'pending'),
(47, 'duminduthushan9@gmail.com', '', 'de', 'item', 12.00, 1, '2025-06-18', '2025-06-26', '2025-06-27', '04:41:00', '0761172158', 'pickup', 24.00, 'pending'),
(48, 'ss@gmail.com', '', 'de', 'item', 12.00, 1, '2025-06-18', '2025-07-01', '2025-07-16', '04:43:00', '0761172158', 'pickup', 192.00, 'pending'),
(49, 'ss@gmail.com', '', '12', 'item', 12.00, 1, '2025-06-18', '2025-06-25', '2025-06-28', '16:48:00', '0761172158', 'pickup', 48.00, 'pending'),
(50, 'ss@gmail.com', '', 'hello', 'service', 1.00, 1, '2025-06-18', '2025-06-25', '2025-06-28', '05:09:00', '0761172158', 'pickup', 4.00, 'pending'),
(51, 'ss@gmail.com', 'asd1@gmail.com', 'okayyyyyyyyyy', 'service', 5.00, 1, '2025-06-19', '2025-07-01', '2025-08-28', '03:43:00', '0761172158', 'pickup', 295.00, 'pending'),
(52, 'ss@gmail.com', '', 'hello', 'service', 1.00, 1, '2025-06-19', '2025-06-25', '2025-06-30', '03:45:00', '0761172158', 'delivery', 6.00, 'pending'),
(53, 'ss@gmail.com', '', 'hello', 'service', 1.00, 1, '2025-06-19', '2025-06-26', '2025-06-30', '02:06:00', '0761172158', 'pickup', 5.00, 'pending'),
(54, 'asd1@gmail.com', '', 'ppppppppppppppppp', 'service', 9.00, 1, '2025-06-19', '2025-06-25', '2025-06-30', '16:23:00', '0761172158', 'pickup', 54.00, 'pending'),
(55, 'asd1@gmail.com', '', 'idk', 'service', 2.00, 1, '2025-06-19', '2025-07-01', '2025-08-06', '17:31:00', '0761172158', 'pickup', 74.00, 'pending'),
(56, 'asd1@gmail.com', '', '12', 'service', 12.00, 1, '2025-06-19', '2025-06-25', '2025-07-01', '16:08:00', '0761172158', 'pickup', 84.00, 'pending'),
(57, 'asd1@gmail.com', '', 'dum', 'item', 12.00, 1, '2025-06-19', '2025-06-24', '2025-06-30', '16:33:00', '0761172158', 'pickup', 84.00, 'pending'),
(58, 'ss@gmail.com', '', 'dum', 'item', 12.00, 1, '2025-06-19', '2025-06-24', '2025-06-26', '03:34:00', '0761172158', 'pickup', 36.00, 'pending'),
(59, 'ss@gmail.com', '', 'okayyyyyyyyyy', 'service', 5.00, 1, '2025-06-19', '2025-07-01', '2025-08-13', '16:53:00', '0761172158', 'pickup', 220.00, 'pending'),
(60, 'ss@gmail.com', '', 'okayyyyyyyyyy', 'service', 5.00, 1, '2025-06-19', '2025-07-01', '2025-08-13', '16:53:00', '0761172158', 'pickup', 220.00, 'pending'),
(61, 'ss@gmail.com', 'user@gmail.com', 'okayyyyyyyyyy', 'service', 5.00, 1, '2025-06-19', '2025-07-01', '2025-08-13', '16:53:00', '0761172158', 'pickup', 220.00, 'pending'),
(62, 'ss@gmail.com', 'user@gmail.com', 'okayyyyyyyyyy', 'service', 5.00, 1, '2025-06-19', '2025-07-01', '2025-08-13', '16:53:00', '0761172158', 'delivery', 220.00, 'pending'),
(63, 'ss@gmail.com', 'asd1@gmail.com', '12', 'item', 12.00, 1, '2025-06-19', '2025-06-23', '2025-06-26', '04:03:00', '0761172158', 'pickup', 48.00, 'pending'),
(64, 'ss@gmail.com', 'ss@gmail.com', 'dumwww', 'item', 122.00, 1, '2025-06-19', '2025-07-01', '2025-08-14', '04:04:00', '0761172158', 'pickup', 5490.00, 'pending'),
(65, 'ss@gmail.com', 'ss@gmail.com', 'dumwww', 'item', 122.00, 1, '2025-06-19', '2025-07-03', '2025-08-07', '04:34:00', '0761172158', 'pickup', 4392.00, 'pending'),
(66, 'asd1@gmail.com', 'kk@gmail.com', 'hello', 'service', 1.00, 1, '2025-06-21', '2025-06-24', '2025-06-28', '01:02:00', '66767', 'delivery', 5.00, 'pending'),
(67, 'nogove7447@nab4.com', 'user@gmail.com', 'okay', 'service', 1.00, 1, '2025-06-21', '2025-06-25', '2025-07-03', '01:07:00', '0761172158', 'pickup', 9.00, 'pending'),
(68, 'ss@gmail.com', 'my@gmail.com', 'Dumindu Thushan', 'item', 10.00, 1, '2025-06-27', '2025-06-30', '2025-07-08', '13:21:00', '213', 'pickup', 90.00, 'pending'),
(69, 'ss@gmail.com', 'kk@gmail.com', 'dvsdv', 'service', 1.00, 1, '2025-06-27', '2025-07-01', '2025-07-23', '14:48:00', '213', 'delivery', 23.00, 'pending'),
(70, 'new@gmail.com', 'kk@gmail.com', 'dvsdv', 'service', 1.00, 1, '2025-07-01', '2025-07-23', '2025-07-27', '03:39:00', '213', 'pickup', 5.00, 'confirmed'),
(71, 'new@gmail.com', 'kk@gmail.com', 'dvsdv', 'service', 1.00, 1, '2025-07-01', '2025-07-31', '2025-08-13', '15:49:00', '213', 'delivery', 14.00, 'pending'),
(72, 'new@gmail.com', 'user@gmail.com', 'qq', 'item', 5.00, 1, '2025-07-01', '2025-07-22', '2025-07-31', '01:52:00', '213', 'pickup', 50.00, 'pending'),
(73, 'new@gmail.com', 'asd1@gmail.com', '12', 'item', 12.00, 1, '2025-07-01', '2025-07-29', '2025-08-07', '03:04:00', '213', 'pickup', 120.00, 'pending'),
(74, 'new@gmail.com', 'new@gmail.com', 'aadf', 'item', 1.00, 1, '2025-07-01', '2025-07-30', '2025-08-05', '03:23:00', '213', 'pickup', 7.00, 'pending'),
(75, 'new@gmail.com', 'new@gmail.com', 'drr', 'item', 12.00, 1, '2025-07-01', '2025-07-29', '2025-07-31', '03:25:00', '213', 'delivery', 36.00, 'confirmed'),
(76, 'new@gmail.com', 'new@gmail.com', 'drr', 'item', 12.00, 1, '2025-07-01', '2025-07-15', '2025-07-21', '04:42:00', '213', 'pickup', 84.00, 'confirmed'),
(77, 'new@gmail.com', 'fitrent@example.com', 'Adjustable Dumbbell Set (50 lbs)', 'item', 6.00, 1, '2025-07-11', '2025-07-30', '2025-08-08', '03:48:00', '213', 'pickup', 60.00, 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `threads`
--

DROP TABLE IF EXISTS `threads`;
CREATE TABLE IF NOT EXISTS `threads` (
  `thread_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`thread_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`) VALUES
(2, 'kk', 'kk@gmail.com', '$2y$10$43QgpIL.ELrc75DdWnCSi.LvCuyO94VshyMX9sh4imOdlm/CX91iC'),
(3, 'me', 'me@gamil.com', '$2y$10$RyBr9aKPgDB0UcktCinhj.QvjYvWjB/L5HsydFTMGlLSRBMlvEzMm'),
(4, 'user', 'user@gmail.com', '$2y$10$sY2NHybxf5ul0t.I50H7AOpsv2EPgMLH91GS5pwxp1pHuvwwvWjr6'),
(5, 'qq', 'qq@gmail.com', '$2y$10$ZA8sXUG7bQhHO.BV5BHVqeH004Hq59CN.zNF7bVE3xnZaTtM8Q2qK'),
(6, 'you', 'you@gmail.com', '$2y$10$WGmcIPpP5aoGpqeBWnTuwepLabYUSmwtAL22vswPJalAYT8tCDvWC'),
(7, 'mm@gmail.com', 'mm@gmail.com', '$2y$10$DoY/kY..l9Fb4Zx5/N8AoukWnTLMZXwCYq7jGJ/Bx4qGfeC/OgHmK'),
(8, 'hello', 'hh@gmail', '$2y$10$EPFKlK2k/82QkmMeEyAmHOfeZmuaTQ8sIot5t7S8uhiUzZ4TqP5bW'),
(10, 'my', 'my@gmail.com', '$2y$10$QLtOMXR6VcXwHoeoaATN8.triyx5kVjBa7sojapL7oWD.7a2R0HHe'),
(11, 'Dumindu', 'duminduthushan9@gmail.com', 'dumindu'),
(13, 'asd', 'asd1@gmail.com', '$2y$10$xU5I9aX5vBJ9828zecZjgONKKZuuUYvCBelW4iYJEs0by5UiBBLRi'),
(14, 'kkkk', 'nogove7447@nab4.com', '$2y$10$QxEuImot8X7vadtaXb8df.cIcwivwwlmkPaKve0LVdSLsg2wKL9WC'),
(15, 'asdd@gmail.com', 'asdd@gmail.com', '$2y$10$sMeX6./qSop48wie7vXur.eMgb2YxJ56jKx1TYRElKQE2IFO03ClS'),
(16, 'new', 'new@gmail.com', '$2y$10$lzadN/ip4ZHlsh0pO0fUrOJ/sZPbr0eH0RumRLjJqUJmQm5iOk72m');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `chat`
--
ALTER TABLE `chat`
  ADD CONSTRAINT `chat_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `sales` (`id`);

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
