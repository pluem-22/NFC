-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 12, 2025 at 03:44 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `nano_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `transaction_id` varchar(255) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price_per_unit` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `transaction_id`, `product_id`, `quantity`, `price_per_unit`, `total_price`, `created_at`) VALUES
(1, 'TXN68576e8bf2840', 1, 1, 100.00, 100.00, '2025-06-22 02:46:35'),
(2, 'TXN68576ea047c2e', 1, 1, 20.00, 20.00, '2025-06-22 02:46:56'),
(4, 'TXN685941e7e6dd9', 1, 1, 100.00, 100.00, '2025-06-23 12:00:39'),
(5, 'TXN689b25b0c2850', 1, 1, 100.00, 100.00, '2025-08-12 11:29:52');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `product_name`, `price`, `stock`, `created_at`) VALUES
(1, 'น้ำ', 20.00, 198, '2025-06-22 02:37:19');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `transaction_id` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `transaction_date` datetime DEFAULT current_timestamp(),
  `status` varchar(50) DEFAULT 'completed',
  `customer_name` varchar(255) DEFAULT NULL,
  `type` varchar(50) DEFAULT 'buy',
  `is_paid` tinyint(1) DEFAULT 0,
  `is_confirmed` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `transaction_id`, `amount`, `transaction_date`, `status`, `customer_name`, `type`, `is_paid`, `is_confirmed`) VALUES
(1, 'TXN001', 50.00, '2025-06-22 07:36:41', 'completed', 'สมชาย ใจดี', 'buy', 0, 0),
(2, 'TXN002', 75.50, '2025-06-22 07:36:41', 'completed', 'สมหญิง สวยงาม', 'buy', 0, 0),
(3, 'TXN003', 120.00, '2025-06-22 07:36:41', 'completed', 'อดิศักดิ์ มั่นคง', 'buy', 0, 0),
(4, 'TXN004', 30.25, '2025-06-22 07:36:41', 'completed', 'สุภาพร อ่อนหวาน', 'buy', 0, 0),
(5, 'TXN005', 90.00, '2025-06-22 07:36:41', 'completed', 'บัญชา แข็งแรง', 'buy', 0, 0),
(9, 'TXN68575c8776a15', 100.00, '2025-06-22 08:29:43', 'completed', '0', 'buy', 0, 0),
(10, 'TXN68575c9107dcd', 500.00, '2025-06-22 08:29:53', 'completed', '0', 'topup', 0, 0),
(11, 'TXN68576cc2a04da', 100.00, '2025-06-22 09:38:58', 'completed', '0', 'buy', 0, 0),
(14, 'TXN68576e8bf2840', 100.00, '2025-06-22 09:46:35', 'completed', '0', 'topup', 0, 0),
(15, 'TXN68576e8e83f62', 100.00, '2025-06-22 09:46:38', 'completed', 'ปลื้ม', 'buy', 1, 0),
(16, 'TXN68576ea047c2e', 0.00, '2025-06-22 09:46:56', 'completed', '0', 'buy', 1, 0),
(18, 'TXN685812b761fe7', 500.00, '2025-06-22 21:27:03', 'completed', '0', 'topup', 0, 0),
(19, 'TXN685941e7e6dd9', 100.00, '2025-06-23 19:00:39', 'completed', '0', 'buy', 0, 0),
(20, 'TXN689b25b0c2850', 100.00, '2025-08-12 18:29:52', 'completed', '0', 'buy', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `name`, `email`, `role`, `created_at`) VALUES
(1, 'pluem', '48264826', 'Pluem Admin', 'pluem@example.com', 'admin', '2025-08-12 11:37:19');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transaction_id` (`transaction_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transaction_id` (`transaction_id`),
  ADD UNIQUE KEY `transaction_id_2` (`transaction_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`transaction_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
