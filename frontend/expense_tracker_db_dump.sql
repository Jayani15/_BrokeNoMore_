-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 08, 2025 at 09:34 PM
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
-- Database: `expense_tracker`
--

-- --------------------------------------------------------

--
-- Table structure for table `budget_details`
--

DROP TABLE IF EXISTS `budget_details`;

CREATE TABLE `budget_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `category` varchar(100) NOT NULL,
  `allocated_amount` bigint(20) NOT NULL,
  `spent_amount` bigint(20) NOT NULL,
  `remaining_amount` bigint(20) NOT NULL,
  `budget_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


--
-- Dumping data for table `budget_details`
--

INSERT INTO `budget_details` (`id`, `user_id`, `category`, `allocated_amount`, `spent_amount`, `remaining_amount`, `budget_id`) VALUES
(41, 18, 'Food', 89, 0, 89, 23),
(42, 18, 'Food', 98, 0, 98, 23),
(43, 18, 'rth', 69, 0, 69, 23),
(44, 18, 'erht', 69, 0, 69, 23);

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
--

DROP TABLE IF EXISTS `expenses`;

CREATE TABLE `expenses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `category` varchar(100) NOT NULL,
  `amount` bigint(20) NOT NULL,
  `description` text NOT NULL,
  `budget_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


--
-- Dumping data for table `expenses`
--

INSERT INTO `expenses` (`id`, `user_id`, `date`, `category`, `amount`, `description`, `budget_id`) VALUES
(16, 18, '2025-05-15', 'Food', 350, 'pizza', 0),
(17, 18, '2025-06-04', 'Necessary Items', 100, 'Stationary', 0),
(18, 18, '2025-05-09', 'Necessary Items', 100, 'Stationary', 0),
(19, 18, '2025-06-03', 'Food', 987654, 'lkiuyt', 0),
(20, 0, '2025-06-01', 'Necessary Items', 1000, 'pizza', 0),
(21, 0, '2025-06-17', 'Necessary Items', 1234, 'Snacks', 0),
(22, 18, '2025-06-04', 'erht', 876, 'k', 0),
(23, 18, '2025-06-03', 'Necessary Items', 69, 'Snacks', 0);

-- --------------------------------------------------------

--
-- Table structure for table `user_details`
--

DROP TABLE IF EXISTS `user_details`;

CREATE TABLE `user_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(35) NOT NULL,
  `email` varchar(45) NOT NULL,
  `password` varchar(255) NOT NULL,
  `userName` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


--
-- Dumping data for table `user_details`
--

INSERT INTO `user_details` (`id`, `name`, `email`, `password`, `userName`) VALUES
(18, 'Yasasri', 'yasasrivalluri@gmail.com', '$2y$10$0Zuep649a7/yzhQShVw.WuF3tEAC.MDvxHetfj76dJlqm8nQ4CkE6', 'Yasasri16');

-- --------------------------------------------------------

--
-- Table structure for table `your_budgets`
--

DROP TABLE IF EXISTS `your_budgets`;

CREATE TABLE `your_budgets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `budget_name` varchar(100) NOT NULL,
  `monthly_income` bigint(20) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


--
-- Dumping data for table `your_budgets`
--

INSERT INTO `your_budgets` (`id`, `budget_name`, `monthly_income`, `start_date`, `end_date`, `user_id`) VALUES
(23, 'Bud 1', 10000, '2025-05-08', '2025-05-21', 18),
(24, 'Bud 2', 100000, '2025-06-01', '2025-07-01', 18),
(25, 'Bud 2', 100000, '2025-06-01', '2025-07-01', 18),
(26, 'Hui', 345, '2025-06-03', '2025-07-09', 18),
(27, 'Hui', 1000, '2025-06-01', '2025-07-01', 18);

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
