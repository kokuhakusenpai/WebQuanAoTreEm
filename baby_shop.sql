-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 28, 2025 at 04:50 PM
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
-- Database: `baby_shop`
--

-- --------------------------------------------------------

--
-- Table structure for table `article`
--

CREATE TABLE `article` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `article`
--

INSERT INTO `article` (`id`, `title`, `content`, `created_at`) VALUES
(1, 'àdfadfa', 'âfagsdgs', '2025-04-07 14:54:06'),
(2, 'àdfadfa', 'âfagsdgs', '2025-04-07 14:55:33'),
(6, 'fuyuyg', 'tfygugu', '2025-04-07 15:07:51'),
(7, 'uguuhui', 'fyfy', '2025-04-07 15:14:41'),
(8, 'hbui', 'hghbbu', '2025-04-07 15:17:47');

-- --------------------------------------------------------

--
-- Table structure for table `banner`
--

CREATE TABLE `banner` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `is_active` tinyint(4) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart_item`
--

CREATE TABLE `cart_item` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `parent_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`id`, `name`, `parent_id`) VALUES
(1, 'Bé Gái', NULL),
(2, 'Váy', 1),
(3, 'Áo', 1),
(4, 'Quần', 1),
(5, 'Đồ bộ', 1),
(6, 'Đồ bơi', 1),
(7, 'Bé Trai', NULL),
(8, 'Áo', 7),
(9, 'Quần', 7),
(10, 'Đồ bộ', 7),
(11, 'Đồ bơi', 7),
(12, 'Sơ Sinh', NULL),
(13, 'Bộ liền thân', 12),
(14, 'Tã lót', 12),
(15, 'Yếm', 12),
(16, 'Bao tay', 12),
(17, 'Bình sữa', 12),
(18, 'Phụ Kiện', NULL),
(19, 'Nơ tóc', 18),
(20, 'Vớ', 18),
(21, 'Túi xách nhỏ', 18),
(22, 'Gấu bông', 18),
(23, 'Giày thể thao', 18),
(24, 'Dép', 18);

-- --------------------------------------------------------

--
-- Table structure for table `contact_info`
--

CREATE TABLE `contact_info` (
  `id` int(11) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `facebook_url` varchar(255) DEFAULT NULL,
  `instagram_url` varchar(255) DEFAULT NULL,
  `youtube_url` varchar(255) DEFAULT NULL,
  `google_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `news`
--

CREATE TABLE `news` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `tag` varchar(50) NOT NULL,
  `tag_color` varchar(50) NOT NULL,
  `published_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `news`
--

INSERT INTO `news` (`id`, `title`, `content`, `image_url`, `tag`, `tag_color`, `published_at`, `created_at`) VALUES
(1, 'Top 10 sản phẩm mẹ và bé hot nhất tháng này', 'Khám phá danh sách sản phẩm được các mẹ bỉm yêu thích nhất tháng 4. Đừng bỏ lỡ cơ hội sở hữu những sản phẩm chất lượng cho bé yêu!', 'images/news1.jpg', 'Mới', 'bg-blue-500', '2025-03-31 17:00:00', '2025-04-24 17:26:47'),
(2, 'Chương trình SALE Hè 2025 - Giảm đến 50%', 'BABY Store tung ưu đãi lớn nhất mùa hè! Thời gian có hạn, nhanh tay săn deal ngay hôm nay!', 'images/news2.jpg', 'Khuyến mãi', 'bg-red-500', '2025-04-09 17:00:00', '2025-04-24 17:26:47'),
(3, 'Mẹo chọn đồ cho bé theo từng độ tuổi', 'Việc chọn trang phục phù hợp theo độ tuổi sẽ giúp bé thoải mái hơn khi vận động và phát triển.', 'images/news3.jpg', 'Mẹo hay', 'bg-green-500', '2025-04-14 17:00:00', '2025-04-24 17:26:47');

-- --------------------------------------------------------

--
-- Table structure for table `order`
--

CREATE TABLE `order` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `address` text NOT NULL,
  `total_price` decimal(10,2) NOT NULL CHECK (`total_price` >= 0),
  `voucher_id` int(11) DEFAULT NULL,
  `payment_method` enum('cod','credit_card','paypal') DEFAULT 'cod',
  `status` enum('pending','confirmed','shipped','delivered','canceled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_item`
--

CREATE TABLE `order_item` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL CHECK (`quantity` > 0),
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `discount_price` decimal(10,2) DEFAULT NULL,
  `image_url` varchar(500) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `category_id` int(11) DEFAULT NULL,
  `sizes` varchar(500) NOT NULL,
  `colors` varchar(500) NOT NULL,
  `status` enum('available','out_of_stock','discontinued') DEFAULT 'available',
  `is_featured` tinyint(1) DEFAULT 0,
  `is_best_seller` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `discount_price`, `image_url`, `stock`, `category_id`, `sizes`, `colors`, `status`, `is_featured`, `is_best_seller`) VALUES
(1, 'Áo thun ngắn tay bé trai', 'Áo thun chất cotton mềm mại', 179000.00, 125300.00, 'images/aothun1.jpg', 50, NULL, 'S,M,L,XL', 'Xanh lá cây nhạt', 'available', 1, 0),
(2, 'Áo thun ngắn tay phi hành gia', 'Áo thun in hình phi hành gia dễ thương', 179000.00, 125300.00, 'images/aothun2.jpg', 30, NULL, 'S,M,L,XL', 'trắng', 'available', 1, 0),
(3, 'Váy Elsa', 'Đầm váy họa tiết Elsa cực xinh', 339000.00, 288150.00, 'images/vay1.jpg', 20, NULL, 'S,M,L', 'Xanh', 'available', 1, 0),
(4, 'Đầm ngắn tay', 'Đầm cotton thoáng mát ', 199000.00, 139300.00, 'images/vay2.jpg', 40, NULL, 'S,M,L', 'Hồng', 'available', 1, 0),
(5, 'Áo khoác gió bé trai', 'Áo khoác gió chống nước, chống bụi', 399000.00, 299000.00, 'images/aokhoacgio1.jpg', 25, NULL, 'S,M,L', 'Xanh', 'available', 0, 1),
(6, 'Áo khoác thể thao ', 'Áo khoác phong cách thể thao', 359000.00, 279000.00, 'images/aokhoac1.jpg', 15, NULL, 'S,M,L', 'Đỏ', 'available', 0, 1),
(7, 'Áo thun ngắn tay bé gái', 'Áo thun đáng yêu', 259000.00, 199000.00, 'images/aothun3.jpg', 35, NULL, 'S,M,L', 'Hồng, Trắng', 'available', 0, 1),
(8, 'Đầm váy bé gái', 'Đầm dễ thương cho bé gái', 129000.00, 99000.00, 'images/vay3.jpg', 50, NULL, 'S,M,L', 'Xanh lá cây nhạt', 'available', 0, 1),
(9, 'quần áo hè nam', NULL, 195000.00, 30000.00, '', 300, NULL, '', '', 'available', 0, 0),
(10, 'váy bé gái', NULL, 150000.00, 147000.00, '', 450, NULL, '', '', 'available', 0, 0),
(11, 'áo thun hè', NULL, 250000.00, 200000.00, '', 1300, NULL, '', '', 'available', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `review`
--

CREATE TABLE `review` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `site_config`
--

CREATE TABLE `site_config` (
  `id` int(11) NOT NULL,
  `background_color` varchar(7) DEFAULT NULL,
  `text_color` varchar(7) DEFAULT NULL,
  `font_family` varchar(100) DEFAULT NULL,
  `background_image_url` varchar(255) NOT NULL,
  `font_size` int(11) DEFAULT 16,
  `page_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` enum('customer','admin') DEFAULT 'customer'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `username`, `email`, `password`, `phone`, `role`) VALUES
(28, 'admin123', 'admin@example.com', 'Thucuc0903@', '0987654321', 'customer'),
(29, 'admin01', 'chithuy30092006@gmail.com', '$2y$10$D/d5Tc4ybdG8dQiXaEzHTeTM/DPVhswRSR4AkSt6Gv0wYgGdP8.O.', '0971187020', 'customer'),
(30, 'Cuc123', 'ahshdj@gmail.com', '$2y$10$8jI578ezlcpE54ZYyyevZOZtsD5kSDgTa7Sx2qDgU4zsARO7/k9lm', '0234567891', 'customer'),
(31, 'cucko', 'sdksf@gmail.com', '$2y$10$uHuIOyt74eZ8d7J19uMTzOA4irqy.03zpT4PwxBi7YLRcJSxZgQIC', '0494728594', 'customer'),
(32, 'hieu', 'h1@gmail.com', '$2y$10$Yd82ZpeSgVFGklo9U2dRaO/RnepwYL2BROB2tPq6AdA4ipbGu28tC', '048295783', 'admin'),
(33, 'tien', 'don@gmail.com', '$2y$10$W7Q/DQewmShoIUNn6PWRa.VvwrjDG931ecDvpWtaL2cwQxP2g.brW', '0284736483', 'admin'),
(34, 'loc', 'hi2@gmail.com', '$2y$10$sg8zRqGouxTvWnJ/bJvG7OdmLmh87pFhqeWGgfEDX5pXu3W9izrZa', '0183295738', 'admin'),
(35, 'cuc', 'ajhdua@gmail.com', '$2y$10$cF2wwyCver7lfqXIUf1rkucLXMqHQv3UhKFR9FlJJWB9A08UuX3qe', '0248718947', 'customer'),
(36, 'trang', 't2@gmail.com', '$2y$10$QooWpovE035OUJs6Y4WoK.quzTWHi0hiDFlJsFGsNrMqlUXtwcGiG', '038294618', 'admin'),
(37, 'ngọc', 'cuc10@gmail.com', '$2y$10$NYxID/6.snwUvkUXExZrmuh8FDpZ7OiFGYp53ueZABiY.YaT3CVG2', '014894924', 'admin'),
(38, 'tuyet', 'tuyet@gmail.com', '$2y$10$42KKg.BjeyopxBWVmhANIOx3wZ1XununxGcdpQHmQ6GX6u3lu6wYq', '0972819473', 'admin'),
(39, 'nguyet', 'nguyet@gmail.com', '$2y$10$9TGIyoQoCgJlDzP7CRBE5OMjzVytxOmM1xJVHDvJDceYm0n8T.F0.', NULL, 'customer'),
(40, 'nga', 'nga@gmail.com', '$2y$10$ArH3CEJeUu9JvY4R8WuBdORs2/5hbCDFUl2OYUS6VhF5xGvM9JAK6', NULL, 'customer'),
(41, 'men', 'men@gmail.com', '$2y$10$FZoBHd/Yl6QPOyAsnNhooePb09XApjnRqPCMTR70mAn8Eu/7vuagq', NULL, 'customer'),
(42, 'nghiem', 'nghiem@gmail.com', '$2y$10$ViynxH83DttvEPME.Bw4reWBR6I3XuR71EXSI8hTd6M6cubywmWJ6', NULL, 'customer'),
(43, 'van anh', 'vananh@gmail.com', '$2y$10$rruLKzIrAYnHC6RkJx48AuptxzgvHjzxUFnOwDqkAjbT8mH6LQJz.', NULL, 'customer');

-- --------------------------------------------------------

--
-- Table structure for table `user_log`
--

CREATE TABLE `user_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `user_log`
--

INSERT INTO `user_log` (`id`, `user_id`, `action`, `created_at`, `ip_address`) VALUES
(1, 32, 'Truy cập vào quản lý thống kê', '2025-04-06 15:02:11', '::1'),
(2, 32, 'Truy cập vào quản lý thống kê', '2025-04-06 15:02:34', '::1'),
(3, 31, 'Cố gắng truy cập vào thống kê', '2025-04-06 15:02:45', '::1'),
(4, 32, 'Truy cập vào quản lý thống kê', '2025-04-06 15:02:53', '::1'),
(5, 32, 'Truy cập vào quản lý thống kê', '2025-04-06 15:11:19', '::1'),
(6, 32, 'Truy cập vào quản lý thống kê', '2025-04-06 15:18:22', '::1'),
(7, 37, 'Đăng nhập', '2025-04-21 09:01:57', '::1'),
(8, 37, 'Đăng nhập', '2025-04-21 09:02:01', '::1'),
(9, 37, 'Đăng nhập', '2025-04-21 09:05:27', '::1'),
(10, 37, 'Đăng nhập', '2025-04-21 11:16:17', '::1'),
(11, 37, 'Đăng nhập', '2025-04-21 11:26:10', '::1'),
(12, 37, 'Đăng nhập', '2025-04-22 15:43:58', '::1'),
(13, 42, 'Đăng nhập', '2025-04-28 20:36:55', '::1'),
(14, 42, 'Đăng nhập', '2025-04-28 21:21:50', '::1');

-- --------------------------------------------------------

--
-- Table structure for table `voucher`
--

CREATE TABLE `voucher` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `discount_value` decimal(10,2) NOT NULL,
  `min_order_value` decimal(10,2) NOT NULL,
  `expires_at` date NOT NULL,
  `image` varchar(255) NOT NULL DEFAULT 'assets/images/default-voucher.jpg'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `voucher`
--

INSERT INTO `voucher` (`id`, `code`, `discount_value`, `min_order_value`, `expires_at`, `image`) VALUES
(1, 'SUMMER20', 20.00, 500000.00, '2025-12-31', 'assets/images/voucher1.jpg'),
(2, 'FREESHIP', 0.00, 500000.00, '2025-12-31', 'assets/images/voucher1.jpg'),
(3, 'BUY2GET1', 33.33, 0.00, '2025-12-31', 'assets/images/voucher1.jpg');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `article`
--
ALTER TABLE `article`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `banner`
--
ALTER TABLE `banner`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cart_item`
--
ALTER TABLE `cart_item`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indexes for table `contact_info`
--
ALTER TABLE `contact_info`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `news`
--
ALTER TABLE `news`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order`
--
ALTER TABLE `order`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `voucher_id` (`voucher_id`);

--
-- Indexes for table `order_item`
--
ALTER TABLE `order_item`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `review`
--
ALTER TABLE `review`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `site_config`
--
ALTER TABLE `site_config`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_log`
--
ALTER TABLE `user_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `voucher`
--
ALTER TABLE `voucher`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `article`
--
ALTER TABLE `article`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `banner`
--
ALTER TABLE `banner`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cart_item`
--
ALTER TABLE `cart_item`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `contact_info`
--
ALTER TABLE `contact_info`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `news`
--
ALTER TABLE `news`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `order`
--
ALTER TABLE `order`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_item`
--
ALTER TABLE `order_item`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `review`
--
ALTER TABLE `review`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `site_config`
--
ALTER TABLE `site_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `user_log`
--
ALTER TABLE `user_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `voucher`
--
ALTER TABLE `voucher`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart_item`
--
ALTER TABLE `cart_item`
  ADD CONSTRAINT `cart_item_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_item_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `category`
--
ALTER TABLE `category`
  ADD CONSTRAINT `category_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `category` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order`
--
ALTER TABLE `order`
  ADD CONSTRAINT `order_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_ibfk_2` FOREIGN KEY (`voucher_id`) REFERENCES `voucher` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_item`
--
ALTER TABLE `order_item`
  ADD CONSTRAINT `order_item_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `order` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_item_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `review`
--
ALTER TABLE `review`
  ADD CONSTRAINT `review_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `review_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
