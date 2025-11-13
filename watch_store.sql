-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 11, 2025 at 08:40 PM
-- Server version: 8.4.3
-- PHP Version: 8.2.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `watch_store`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `employee_id` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `is_super` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1=Active, 0=Deactivated',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `name`, `employee_id`, `password`, `is_super`, `is_active`, `created_at`) VALUES
(1, 'Super Admin', 'super', '$2y$10$QVcwyLwTaKeAm0FDAXf/SutyBwGStWXTkwF7qSjWra.TZesqx8Dm.', 1, 1, '2025-11-03 15:48:25'),
(2, 'Nasim', '1', '$2y$10$q0WWHeKkYWurOPuvyE3U3.YjryAm1.cPBCMbX9JDrIvJxoqV7oNYy', 0, 1, '2025-11-03 16:06:21');

-- --------------------------------------------------------

--
-- Table structure for table `landing_pages`
--

CREATE TABLE `landing_pages` (
  `id` int NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `product_id` int DEFAULT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `images` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'JSON array of image paths',
  `button_text` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `landing_pages`
--

INSERT INTO `landing_pages` (`id`, `slug`, `product_id`, `title`, `description`, `images`, `button_text`, `created_at`) VALUES
(8, 'rolex', 1, 'rolex', 'tet dp', '[\"products\\/page_690a144b1ee621762268235_0.jpg\"]', 'confirm', '2025-11-04 14:57:15'),
(9, 'iugds', 1, 'iugds', 'hdisakdisakdisakdisak\r\nfgbso', '[\"products\\/page_690a1497b14901762268311_0.jpg\"]', 'অর্ডার করতে কল করুন', '2025-11-04 14:58:31'),
(10, '345678', 1, '345678', 'sdfgh', '[\"products\\/page_691276c1bd31e1762817729_0.png\"]', 'অর্ডার করতে কল করুন', '2025-11-10 23:35:29'),
(11, 'aa', NULL, 'aa', 'aaa', '[\"products\\/page_6913857c511301762887036_0.png\"]', 'অর্ডার করতে কল করুন', '2025-11-11 18:50:36');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int NOT NULL,
  `product_id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `address` text COLLATE utf8mb4_general_ci NOT NULL,
  `status` enum('pending','confirmed','shipped') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'pending',
  `is_verified` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0=Not Verified, 1=Verified',
  `note` text COLLATE utf8mb4_general_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `product_id`, `name`, `phone`, `address`, `status`, `is_verified`, `note`, `created_at`) VALUES
(12, 1, 'MD.ASHIKUL ISLAM ONIK', '01776078091', 'Block-c', 'pending', 1, NULL, '2025-11-04 21:42:58'),
(13, 1, 'Brittany Lott', '01111111111', 'Distinctio Nostrum', 'pending', 0, NULL, '2025-11-04 21:44:13'),
(14, 1, 'ygivby', '01111111111', 'zxsdrfvgyb', 'pending', 0, NULL, '2025-11-06 11:51:34');

-- --------------------------------------------------------

--
-- Table structure for table `page_orders`
--

CREATE TABLE `page_orders` (
  `id` int NOT NULL,
  `landing_page_id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `note` text COLLATE utf8mb4_general_ci,
  `quantity` int NOT NULL DEFAULT '1',
  `delivery_option` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `total_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `is_verified` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `main_price` decimal(10,2) DEFAULT NULL,
  `description` text COLLATE utf8mb4_general_ci NOT NULL,
  `images` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `is_visible` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `title`, `price`, `main_price`, `description`, `images`, `is_visible`) VALUES
(1, 'রলেক্স প্রিমিয়াম ঘড়ি', 999.00, 1750.00, '✅ ১ বছরের রিপ্লেসমেন্ট গ্যারান্টি – ঘড়িতে কোনো সমস্যা হলে ফ্রি রিপ্লেসমেন্ট সুবিধা।\r\n\r\n💧 ওয়াটার প্রুফ ডিজাইন – বৃষ্টি, ঘাম বা পানির ছিটে থেকেও সম্পূর্ণ নিরাপদ।\r\n\r\n💸 ক্যাশ অন ডেলিভারি সুবিধা – ঘড়ি হাতে পেয়ে তারপরই পেমেন্ট করুন।\r\n\r\n⌚ প্রিমিয়াম কোয়ালিটি ও স্টাইলিশ লুক – প্রতিদিনের ব্যবহার ও বিশেষ মুহূর্তের জন্য উপযুক্ত।', 'products/prod_6908dfeb301031762189291.jpg', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employee_id` (`employee_id`);

--
-- Indexes for table `landing_pages`
--
ALTER TABLE `landing_pages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `page_orders`
--
ALTER TABLE `page_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `landing_page_id` (`landing_page_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `landing_pages`
--
ALTER TABLE `landing_pages`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `page_orders`
--
ALTER TABLE `page_orders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
