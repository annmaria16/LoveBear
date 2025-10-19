-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 19, 2025 at 02:27 PM
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
-- Database: `lovebear`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `items` text NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `user_id` int(11) DEFAULT NULL,
  `razorpay_order_id` varchar(255) DEFAULT NULL,
  `razorpay_payment_id` varchar(255) DEFAULT NULL,
  `payment_status` varchar(50) DEFAULT 'pending',
  `shipping_address` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `items`, `total`, `created_at`, `user_id`, `razorpay_order_id`, `razorpay_payment_id`, `payment_status`, `shipping_address`) VALUES
(1, '[{\"id\":2,\"name\":\"Stitch Soft Toy\",\"price\":\"850.00\",\"qty\":1,\"subtotal\":850}]', 850.00, '2025-10-18 07:14:08', NULL, 'order_RUqif7ukoLYwNB', 'pay_RUqj76MBHIqJhI', 'paid', '{\"name\":\"Ann\",\"phone\":\"9778180951\",\"address\":\"abc\",\"state\":\"kerala\",\"district\":\"Idukki\",\"pincode\":\"686509\"}'),
(2, '[{\"id\":2,\"name\":\"Stitch Soft Toy\",\"price\":\"850.00\",\"qty\":1,\"subtotal\":850}]', 850.00, '2025-10-18 08:32:54', 6, 'order_RUs4EM4ese8qdP', 'pay_RUs4RaEQ1SOc5g', 'paid', '{\"name\":\"Ann\",\"phone\":\"9778180951\",\"address\":\"abc\",\"state\":\"kerala\",\"district\":\"Idukki\",\"pincode\":\"686509\"}'),
(3, '[{\"id\":6,\"name\":\"Breathing Teddy\",\"price\":\"1500.00\",\"qty\":1,\"subtotal\":1500}]', 1500.00, '2025-10-18 08:39:27', 6, 'order_RUsBB87illzb0l', 'pay_RUsBMfhu2hXDRN', 'paid', '{\"name\":\"Ann\",\"phone\":\"9778180951\",\"address\":\"abc\",\"state\":\"kerala\",\"district\":\"Idukki\",\"pincode\":\"686509\"}'),
(4, '[{\"id\":7,\"name\":\"Bunny\",\"price\":\"550.00\",\"qty\":1,\"subtotal\":550}]', 550.00, '2025-10-18 08:59:37', 6, 'order_RUsWVQ8Vn08tMY', 'pay_RUsWg26sxHrK2X', 'paid', '{\"name\":\"Ann\",\"phone\":\"9778180951\",\"address\":\"abc\",\"state\":\"kerala\",\"district\":\"Idukki\",\"pincode\":\"686509\"}'),
(5, '[{\"id\":8,\"name\":\"Floppy ears Rabbit\",\"price\":\"800.00\",\"qty\":3,\"subtotal\":2400}]', 2400.00, '2025-10-19 11:36:52', 6, 'order_RVJkYhb7Mocu6H', 'pay_RVJkprFauajGLq', 'paid', '{\"name\":\"Ann\",\"phone\":\"9778180951\",\"address\":\"abc\",\"state\":\"kerala\",\"district\":\"Idukki\",\"pincode\":\"686509\"}'),
(6, '[{\"id\":8,\"name\":\"Floppy ears Rabbit\",\"price\":\"800.00\",\"qty\":1,\"subtotal\":800}]', 800.00, '2025-10-19 11:54:19', 6, 'order_RVK35bzuyT32OC', 'pay_RVK3FbhMivCcve', 'paid', '{\"name\":\"Ann\",\"phone\":\"9778180951\",\"address\":\"abc\",\"state\":\"kerala\",\"district\":\"Idukki\",\"pincode\":\"686509\"}'),
(7, '[{\"id\":8,\"name\":\"Floppy ears Rabbit\",\"price\":\"800.00\",\"qty\":2,\"subtotal\":1600}]', 1600.00, '2025-10-19 11:57:42', 6, 'order_RVK6g2PE0alKaD', 'pay_RVK6q22ou7s1aw', 'paid', '{\"name\":\"Ann\",\"phone\":\"9778180951\",\"address\":\"abc\",\"state\":\"kerala\",\"district\":\"Idukki\",\"pincode\":\"686509\"}'),
(8, '[{\"id\":6,\"name\":\"Breathing Teddy\",\"price\":\"1500.00\",\"qty\":1,\"subtotal\":1500}]', 1500.00, '2025-10-19 12:02:03', 6, 'order_RVKBIbQ1jaKD6V', 'pay_RVKBQL5YwhKoXb', 'paid', '{\"name\":\"Ann\",\"phone\":\"9778180951\",\"address\":\"abc\",\"state\":\"kerala\",\"district\":\"Idukki\",\"pincode\":\"686509\"}');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `image` varchar(255) DEFAULT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `image`, `stock`, `created_at`) VALUES
(1, 'Unicon', 'Babyhug Unicorn Soft Plush Toy', 650.00, 'p_68f086094e74a.jpg', 8, '2025-10-16 05:43:37'),
(2, 'Stitch Soft Toy', 'Tinytotem Stitch Soft Toy for Kids Alien Koala 30 cm Huggable Plush Cute and Comfortable Stuffed Animal Plushie Birthday Gifts', 850.00, 'p_68f0867ac0b82.webp', 8, '2025-10-16 05:45:30'),
(3, 'Cute Rabbit', 'Hug Comfy cute Rabbit', 680.00, 'p_68f0870647b07.jpg', 8, '2025-10-16 05:47:50'),
(4, 'Labubu', 'Labubu toys are collectible plush figures created by Hong Kong artist Kasing Lung, featuring a mischievous monster character', 2800.00, 'p_68f08780e945c.webp', 4, '2025-10-16 05:49:52'),
(5, 'Little Elephant', 'Invite your toddler to join the fun with cute little LoveBear Elephant', 890.00, 'p_68f087d46cab0.webp', 5, '2025-10-16 05:51:16'),
(6, 'Breathing Teddy', 'Breathing teddy bear mimics a calming breathing rhythm, making it the perfect baby sleeping toy. A soothing companion for newborns, this breathing buddy helps infants feel secure and relaxed during sleep.', 1500.00, 'p_68f0882149c55.webp', 8, '2025-10-16 05:52:33'),
(7, 'Bunny', 'Crafted from preminum eco-friendly materials.', 550.00, 'p_68f0888422afe.webp', 6, '2025-10-16 05:54:12'),
(8, 'Floppy ears Rabbit', 'This ultra-soft stuffed rabbit features charming floppy ears, a sweet embroidered face, and a perfectly huggable size that kids and babies will love.', 800.00, 'p_68f088cf4074f.jpg', 7, '2025-10-16 05:55:27'),
(9, 'Cute Bunny', 'These bunnies often feature a gentle expression with cute, floppy ears that can be bent or adjusted. The body is typically soft and huggable, sometimes with a puffy, cotton-like tail.', 670.00, 'p_68f353966db43.jpg', 5, '2025-10-18 08:44:26');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `phone` varchar(30) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `district` varchar(100) DEFAULT NULL,
  `pincode` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `created_at`, `phone`, `address`, `state`, `district`, `pincode`) VALUES
(5, 'Admin', 'admin@gmail.com', '$2y$10$.A7D25x6v5NxaCGIQYRr6OWzc6OLOplw7OZeFWc09AdCcGZP4CuOC', 'admin', '2025-10-18 08:28:26', NULL, NULL, NULL, NULL, NULL),
(6, 'Ann', 'ann@gmail.com', '$2y$10$GZNhl7Gdh0wqfm2SzxUTQeRXUPdXkTfOb1j1mWGU3ohPBITvvM5cm', 'user', '2025-10-18 08:29:37', '9778180951', 'abc', 'kerala', 'Idukki', '686509');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_orders_user_id` (`user_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username_2` (`username`),
  ADD UNIQUE KEY `username_3` (`username`),
  ADD UNIQUE KEY `username_4` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
