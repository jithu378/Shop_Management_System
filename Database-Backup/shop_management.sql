-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 26, 2026 at 04:54 PM
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
-- Database: `shop_management`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`) VALUES
(1, 'admin', 'admin123');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `position` varchar(100) DEFAULT NULL,
  `joining_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `name`, `phone`, `email`, `position`, `joining_date`) VALUES
(1, 'Arun Kumar', '9876543210', 'arun@gmail.com', 'Sales Assistant', '2026-09-25'),
(2, 'Anjali Thomas', '9123456789', 'anjali@gmail.com', 'Cashier', '2026-09-25'),
(5, 'Rahul Menon', '9847012345', 'rahul.menon@gmail.com', 'Store Manager', '2026-01-10'),
(6, 'Meera Nair', '9876123456', 'meera.nair@gmail.com', 'Cashier', '2026-02-15'),
(7, 'Vishnu Raj', '9632587410', 'vishnu.raj@gmail.com', 'Inventory Manager', '2026-03-05'),
(8, 'Sneha Thomas', '9745123689', 'sneha.thomas@gmail.com', 'Sales Assistant', '2026-03-18'),
(9, 'Akash Suresh', '9895012347', 'akash.suresh@gmail.com', 'Delivery Staff', '2026-04-02'),
(10, 'Anu Joseph', '9567123480', 'anu.joseph@gmail.com', 'Cashier', '2026-04-12'),
(11, 'Nikhil Kumar', '9812345670', 'nikhil.kumar@gmail.com', 'Sales Assistant', '2026-04-25'),
(12, 'Devika Menon', '9526341780', 'devika.menon@gmail.com', 'Inventory Manager', '2026-05-08'),
(13, 'Arjun Das', '9946781234', 'arjun.das@gmail.com', 'Delivery Staff', '2026-05-20'),
(14, 'Fathima Shirin', '9078563412', 'fathima.shirin@gmail.com', 'Sales Assistant', '2026-06-03'),
(15, 'Sanjay Mohan', '9846123578', 'sanjay.mohan@gmail.com', 'Store Manager', '2026-06-15'),
(16, 'Lakshmi Priya', '9637412580', 'lakshmi.priya@gmail.com', 'Cashier', '2026-07-01'),
(17, 'Joel Mathew', '9748362510', 'joel.mathew@gmail.com', 'Delivery Staff', '2026-07-14'),
(18, 'Neha Rajan', '9897456123', 'neha.rajan@gmail.com', 'Sales Assistant', '2026-08-05'),
(19, 'Abhinav Krishnan', '9564781230', 'abhinav.krishnan@gmail.com', 'Inventory Manager', '2026-08-22');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `category` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `product_name`, `category`, `price`, `quantity`) VALUES
(1, 'Rice', 'Grocery', 60.00, 100),
(2, 'Milk', 'Dairy', 40.00, 0),
(5, 'Bread', 'Bakery', 55.00, 5),
(6, 'Water', 'Drinks', 20.00, 100),
(7, 'Sugar', 'Grocery', 48.00, 75),
(8, 'Wheat Flour', 'Grocery', 52.00, 60),
(9, 'Cooking Oil', 'Grocery', 145.00, 45),
(10, 'Salt', 'Grocery', 25.00, 90),
(11, 'Tea Powder', 'Beverages', 120.00, 35),
(12, 'Coffee Powder', 'Beverages', 180.00, 30),
(13, 'Biscuits', 'Snacks', 30.00, 80),
(14, 'Chips', 'Snacks', 40.00, 65),
(15, 'Eggs', 'Dairy', 7.00, 120),
(16, 'Curd', 'Dairy', 35.00, 50),
(17, 'Butter', 'Dairy', 58.00, 25),
(18, 'Apple', 'Fruits', 160.00, 40),
(19, 'Banana', 'Fruits', 60.00, 70),
(20, 'Orange', 'Fruits', 90.00, 55),
(21, 'Tomato', 'Vegetables', 45.00, 85),
(22, 'Potato', 'Vegetables', 40.00, 100),
(23, 'Onion', 'Vegetables', 50.00, 95),
(24, 'Shampoo', 'Personal Care', 210.00, 30),
(25, 'Toothpaste', 'Personal Care', 95.00, 45),
(26, 'Bath Soap', 'Personal Care', 42.00, 70),
(27, 'Basmati Rice', 'Grocery', 110.00, 45),
(28, 'Toor Dal', 'Grocery', 135.00, 35),
(29, 'Moong Dal', 'Grocery', 120.00, 50),
(30, 'Chana Dal', 'Grocery', 95.00, 65),
(31, 'Rava', 'Grocery', 55.00, 70),
(32, 'Coconut Oil', 'Cooking Oil', 210.00, 28),
(33, 'Sunflower Oil', 'Cooking Oil', 155.00, 40),
(34, 'Olive Oil', 'Cooking Oil', 480.00, 15),
(35, 'Green Tea', 'Beverages', 150.00, 25),
(36, 'Mango Juice', 'Beverages', 110.00, 40),
(37, 'Orange Juice', 'Beverages', 95.00, 55),
(38, 'Soft Drink', 'Beverages', 60.00, 80),
(39, 'Chocolate Bar', 'Snacks', 45.00, 90),
(40, 'Cookies', 'Snacks', 75.00, 60),
(41, 'Namkeen', 'Snacks', 85.00, 35),
(42, 'Popcorn', 'Snacks', 55.00, 50),
(43, 'Cheese', 'Dairy', 140.00, 20),
(44, 'Paneer', 'Dairy', 110.00, 30),
(45, 'Flavoured Milk', 'Dairy', 45.00, 60),
(46, 'Mango', 'Fruits', 120.00, 40),
(47, 'Grapes', 'Fruits', 100.00, 45),
(48, 'Pomegranate', 'Fruits', 180.00, 18),
(49, 'Carrot', 'Vegetables', 55.00, 70),
(50, 'Cabbage', 'Vegetables', 40.00, 80),
(51, 'Capsicum', 'Vegetables', 90.00, 35),
(52, 'Face Wash', 'Personal Care', 180.00, 25),
(53, 'Hand Wash', 'Personal Care', 110.00, 40),
(54, 'Body Lotion', 'Personal Care', 250.00, 20),
(55, 'Detergent Powder', 'Household', 180.00, 45),
(56, 'Dishwash Liquid', 'Household', 125.00, 30);

-- --------------------------------------------------------

--
-- Table structure for table `salary`
--

CREATE TABLE `salary` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `salary_month` varchar(20) NOT NULL,
  `basic_salary` decimal(10,2) NOT NULL,
  `bonus` decimal(10,2) DEFAULT 0.00,
  `deduction` decimal(10,2) DEFAULT 0.00,
  `net_salary` decimal(10,2) NOT NULL,
  `payment_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `salary`
--

INSERT INTO `salary` (`id`, `employee_id`, `salary_month`, `basic_salary`, `bonus`, `deduction`, `net_salary`, `payment_date`) VALUES
(1, 1, '2026-09', 20000.00, 2000.00, 500.00, 21500.00, '2026-09-25'),
(2, 1, '2026-09', 25000.00, 2500.00, 500.00, 27000.00, '2026-09-04');

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `sale_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`id`, `product_id`, `quantity`, `total_amount`, `sale_date`) VALUES
(1, 1, 3, 180.00, '2026-09-25 03:30:57'),
(2, 2, 5, 200.00, '2026-09-26 05:13:36'),
(3, 6, 10, 200.00, '2026-09-26 11:28:03'),
(4, 5, 3, 165.00, '2026-09-26 11:28:45'),
(5, 2, 2, 80.00, '2026-09-26 11:34:22'),
(6, 1, 5, 300.00, '2026-09-26 11:37:00'),
(7, 1, 5, 300.00, '2026-09-26 11:38:30'),
(8, 6, 90, 1800.00, '2026-09-26 11:50:22'),
(9, 1, 2, 120.00, '2026-09-26 11:58:05'),
(10, 2, 100, 4000.00, '2026-09-26 11:59:58'),
(11, 5, 95, 5225.00, '2026-09-26 13:30:05');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `salary`
--
ALTER TABLE `salary`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `salary`
--
ALTER TABLE `salary`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `salary`
--
ALTER TABLE `salary`
  ADD CONSTRAINT `salary_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`);

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
