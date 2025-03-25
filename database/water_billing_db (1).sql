-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 25, 2025 at 10:28 PM
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
-- Database: `water_billing_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `bill_rate`
--

CREATE TABLE `bill_rate` (
  `billrate_id` bigint(20) UNSIGNED NOT NULL,
  `consumer_type` varchar(255) DEFAULT NULL,
  `cubic_meter` decimal(10,2) NOT NULL,
  `value` decimal(10,2) NOT NULL,
  `excess_value_per_cubic` decimal(8,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bill_rate`
--

INSERT INTO `bill_rate` (`billrate_id`, `consumer_type`, `cubic_meter`, `value`, `excess_value_per_cubic`, `created_at`, `updated_at`) VALUES
(2, 'Residential', 10.00, 170.00, 25.00, '2025-01-22 18:03:22', '2025-02-13 20:40:31'),
(3, 'Commercial', 10.00, 200.00, 30.00, '2025-01-22 18:03:54', '2025-02-27 04:43:26'),
(10, 'Industrial', 10.00, 250.00, 35.00, '2025-02-27 04:56:59', '2025-02-27 04:56:59');

-- --------------------------------------------------------

--
-- Table structure for table `blocks`
--

CREATE TABLE `blocks` (
  `block_id` bigint(20) UNSIGNED NOT NULL,
  `barangays` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `blocks`
--

INSERT INTO `blocks` (`block_id`, `barangays`, `created_at`, `updated_at`) VALUES
(1, '[\"Poblacion\",\"Guinobatan\",\"Tagum Sur\",\"Tagum Norte\",\"Panab-an\"]', '2025-01-22 21:53:48', '2025-02-01 23:36:16'),
(2, '[\"Capayas\",\"San Jose\",\"Mahayag\",\"Bugang\"]', '2025-01-22 22:23:40', '2025-02-27 08:14:29'),
(3, '[\"Sample\",\"BarangayTest\",\"Testonlybg\"]', '2025-02-27 01:23:35', '2025-02-27 01:23:35'),
(4, '[\"gfa\",\"dfgf\",\"dagds\",\"sfstg\",\"hrsgrgsrset\",\"sdfdegs\"]', '2025-02-14 21:53:49', '2025-02-27 08:14:44'),
(5, '[\"Kagawasan\",\"Danao\",\"Kantipak\",\"Kauswagan\",\"Masanao\"]', '2025-02-28 02:52:55', '2025-03-23 12:13:35');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `conn_payment`
--

CREATE TABLE `conn_payment` (
  `connpay_id` bigint(20) UNSIGNED NOT NULL,
  `customer_id` varchar(255) NOT NULL,
  `application_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `conn_amount_paid` decimal(10,2) NOT NULL DEFAULT 0.00,
  `conn_pay_status` enum('paid','unpaid') NOT NULL DEFAULT 'unpaid',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `conn_payment`
--

INSERT INTO `conn_payment` (`connpay_id`, `customer_id`, `application_fee`, `conn_amount_paid`, `conn_pay_status`, `created_at`, `updated_at`) VALUES
(31, 'B01-01', 1050.00, 1050.00, 'paid', '2025-02-20 04:46:35', '2025-02-20 04:48:10'),
(39, 'B01-03', 1050.00, 1050.00, 'paid', '2025-02-28 03:00:23', '2025-03-01 07:10:28'),
(42, 'B02-01', 1050.00, 1050.00, 'paid', '2025-03-24 03:52:56', '2025-03-24 04:39:29'),
(43, 'B03-01', 1050.00, 1050.00, 'paid', '2025-03-24 04:44:27', '2025-03-24 04:44:41');

-- --------------------------------------------------------

--
-- Table structure for table `consumer_bill_pay`
--

CREATE TABLE `consumer_bill_pay` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `consread_id` bigint(20) UNSIGNED NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `bill_tendered_amount` decimal(10,2) NOT NULL,
  `bill_status` enum('paid','unpaid') NOT NULL DEFAULT 'unpaid',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `consumer_reading`
--

CREATE TABLE `consumer_reading` (
  `consread_id` bigint(20) UNSIGNED NOT NULL,
  `customer_id` varchar(255) NOT NULL,
  `covdate_id` bigint(20) UNSIGNED NOT NULL,
  `reading_date` date NOT NULL,
  `due_date` date NOT NULL,
  `previous_reading` decimal(8,2) NOT NULL,
  `present_reading` decimal(8,2) NOT NULL,
  `consumption` decimal(8,2) NOT NULL,
  `meter_reader` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `consumer_reading`
--

INSERT INTO `consumer_reading` (`consread_id`, `customer_id`, `covdate_id`, `reading_date`, `due_date`, `previous_reading`, `present_reading`, `consumption`, `meter_reader`, `created_at`, `updated_at`) VALUES
(6, 'B01-01', 11, '2025-03-25', '2025-04-05', 150.00, 170.00, 10.00, 'John Doe', '2025-03-25 06:49:29', '2025-03-25 06:49:29'),
(7, 'B01-02', 11, '2025-03-25', '2025-04-05', 170.00, 195.00, 11.00, 'John Doe', '2025-03-25 06:49:29', '2025-03-25 06:49:29'),
(8, 'B01-03', 11, '2025-03-25', '2025-04-05', 275.00, 375.00, 15.00, 'John Doe', '2025-03-25 06:49:29', '2025-03-25 06:49:29'),
(9, 'B02-01', 11, '2025-03-25', '2025-04-05', 165.00, 220.00, 12.00, 'John Doe', '2025-03-25 06:49:29', '2025-03-25 06:49:29'),
(10, 'B03-01', 11, '2025-03-25', '2025-04-05', 190.00, 200.00, 10.00, 'John Doe', '2025-03-25 06:49:29', '2025-03-25 06:49:29');

-- --------------------------------------------------------

--
-- Table structure for table `coverage_date`
--

CREATE TABLE `coverage_date` (
  `covdate_id` bigint(20) UNSIGNED NOT NULL,
  `coverage_date_from` date NOT NULL,
  `coverage_date_to` date NOT NULL,
  `reading_date` date NOT NULL,
  `due_date` date NOT NULL,
  `status` enum('Open','Close') NOT NULL DEFAULT 'Open',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `coverage_date`
--

INSERT INTO `coverage_date` (`covdate_id`, `coverage_date_from`, `coverage_date_to`, `reading_date`, `due_date`, `status`, `created_at`, `updated_at`) VALUES
(3, '2025-02-01', '2025-03-01', '2025-02-24', '2025-02-28', 'Close', '2025-01-23 17:59:23', '2025-03-23 12:39:28'),
(4, '2025-03-01', '2025-04-01', '2025-03-25', '2025-03-31', 'Open', '2025-01-23 18:18:01', '2025-03-25 05:56:46'),
(5, '2025-04-01', '2025-05-01', '2025-04-25', '2025-04-30', 'Close', '2025-01-23 18:24:53', '2025-03-24 03:18:39'),
(10, '2025-01-01', '2025-02-01', '2025-01-25', '2025-01-31', 'Close', '2025-03-24 03:10:45', '2025-03-25 05:56:46'),
(11, '2025-03-01', '2025-03-31', '2025-03-25', '2025-04-05', 'Open', '2025-03-25 06:04:07', '2025-03-25 06:04:07');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fees`
--

CREATE TABLE `fees` (
  `fee_id` bigint(20) UNSIGNED NOT NULL,
  `fee_type` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `fees`
--

INSERT INTO `fees` (`fee_id`, `fee_type`, `amount`, `created_at`, `updated_at`) VALUES
(1, 'Application Fee', 1050.00, NULL, '2025-03-23 10:43:06'),
(2, 'Reconnection Fee', 700.00, NULL, '2025-02-26 13:05:01');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `manage_notice`
--

CREATE TABLE `manage_notice` (
  `notice_id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(255) NOT NULL,
  `announcement` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `manage_notice`
--

INSERT INTO `manage_notice` (`notice_id`, `type`, `announcement`, `created_at`, `updated_at`) VALUES
(1, 'Schedule', 'Routine maintenance check in Block C zone.', '2025-03-09 07:56:51', '2025-03-09 07:56:51'),
(3, 'Notice', 'Water pressure might be low in Phase 1 area for 6 hours.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(4, 'Maintenance', 'System upgrade notification for Uptown region.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(6, 'Service', 'Quality check scheduled for Lower district at 12:00.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(7, 'Notice', 'Water pressure might be low in Southwest area for 6 hours.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(8, 'Update', 'Quality check scheduled for Block A district at 4:00.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(9, 'Schedule', 'Water pressure might be low in Phase 1 area for 12 hours.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(10, 'Advisory', 'Important advisory for residents of Block B zone.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(11, 'Schedule', 'Quality check scheduled for Block C district at 10:00.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(12, 'Notice', 'System flushing activity in Uptown region.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(13, 'Emergency', 'Emergency repair needed in Block C district.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(14, 'Schedule', 'Routine maintenance check in Phase 1 zone.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(15, 'Advisory', 'System flushing activity in Block C region.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(16, 'Advisory', 'Important advisory for residents of Mid zone.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(17, 'Emergency', 'System upgrade notification for Mid region.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(18, 'Update', 'System upgrade notification for Phase 2 region.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(19, 'Update', 'Service improvement in North area. Duration: 5 hours.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(20, 'Service', 'System upgrade notification for West region.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(21, 'Maintenance', 'Emergency repair needed in West district.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(22, 'Update', 'Water pressure might be low in Central area for 12 hours.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(23, 'Update', 'System flushing activity in Block C region.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(24, 'Service', 'Scheduled maintenance for Uptown starting at 5:00.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(25, 'Notice', 'Water interruption in Lower area. Expected duration: 3 hours.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(26, 'Notice', 'Water pressure might be low in Block C area for 10 hours.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(27, 'Schedule', 'Emergency repair needed in Mid district.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(28, 'Notice', 'Routine maintenance check in West zone.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(29, 'Schedule', 'Quality check scheduled for Northwest district at 10:00.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(30, 'Update', 'Water interruption in Block A area. Expected duration: 9 hours.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(31, 'Update', 'Scheduled maintenance for Lower starting at 5:00.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(32, 'Maintenance', 'Important advisory for residents of South zone.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(33, 'Advisory', 'Routine maintenance check in Block B zone.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(34, 'Maintenance', 'Water interruption in Downtown area. Expected duration: 3 hours.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(35, 'Service', 'Scheduled maintenance for South starting at 1:00.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(36, 'Update', 'System upgrade notification for Southwest region.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(37, 'Notice', 'Scheduled maintenance for West starting at 12:00.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(38, 'Notice', 'Important advisory for residents of Block C zone.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(39, 'Schedule', 'Water interruption in Northwest area. Expected duration: 2 hours.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(40, 'Schedule', 'Water interruption in Phase 1 area. Expected duration: 11 hours.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(41, 'Notice', 'Water interruption in Upper area. Expected duration: 12 hours.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(42, 'Advisory', 'Emergency repair needed in North district.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(43, 'Service', 'Service improvement in Southwest area. Duration: 3 hours.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(44, 'Schedule', 'System flushing activity in West region.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(45, 'Service', 'Routine maintenance check in Northwest zone.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(46, 'Emergency', 'Important advisory for residents of Upper zone.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(47, 'Emergency', 'Quality check scheduled for Uptown district at 1:00.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(48, 'Emergency', 'Emergency repair needed in Northwest district.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(49, 'Maintenance', 'System upgrade notification for Block B region.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(50, 'Notice', 'System upgrade notification for North region.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(51, 'Notice', 'Water pressure might be low in Block C area for 6 hours.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(52, 'Update', 'Emergency repair needed in Block B district.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(53, 'Schedule', 'Quality check scheduled for Phase 2 district at 12:00.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(54, 'Emergency', 'Scheduled maintenance for Southeast starting at 9:00.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(55, 'Notice', 'System flushing activity in Upper region.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(56, 'Service', 'Quality check scheduled for Mid district at 8:00.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(57, 'Notice', 'Water pressure might be low in Uptown area for 1 hours.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(58, 'Update', 'Water interruption in North area. Expected duration: 10 hours.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(59, 'Emergency', 'Scheduled maintenance for South starting at 12:00.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(60, 'Advisory', 'Water interruption in Block C area. Expected duration: 7 hours.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(61, 'Maintenance', 'Water interruption in Block C area. Expected duration: 8 hours.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(62, 'Service', 'Water pressure might be low in Block C area for 4 hours.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(63, 'Notice', 'Water pressure might be low in Central area for 2 hours.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(64, 'Maintenance', 'Important advisory for residents of Uptown zone.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(65, 'Maintenance', 'Emergency repair needed in Central district.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(66, 'Emergency', 'System upgrade notification for Block C region.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(67, 'Maintenance', 'Important advisory for residents of Northwest zone.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(68, 'Service', 'Quality check scheduled for Uptown district at 11:00.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(69, 'Schedule', 'Important advisory for residents of Block C zone.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(70, 'Notice', 'Scheduled maintenance for Northeast starting at 4:00.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(71, 'Service', 'Quality check scheduled for Southwest district at 6:00.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(72, 'Service', 'Quality check scheduled for Central district at 9:00.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(73, 'Service', 'Service improvement in Phase 2 area. Duration: 8 hours.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(74, 'Advisory', 'System upgrade notification for East region.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(75, 'Service', 'System upgrade notification for Mid region.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(76, 'Maintenance', 'System upgrade notification for Southwest region.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(77, 'Update', 'Emergency repair needed in South district.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(78, 'Schedule', 'Routine maintenance check in Lower zone.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(79, 'Maintenance', 'Water interruption in Southeast area. Expected duration: 12 hours.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(80, 'Update', 'System flushing activity in Northeast region.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(81, 'Schedule', 'Routine maintenance check in Downtown zone.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(82, 'Advisory', 'Water interruption in Block A area. Expected duration: 8 hours.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(83, 'Notice', 'Routine maintenance check in East zone.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(84, 'Schedule', 'Routine maintenance check in Phase 3 zone.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(85, 'Notice', 'System upgrade notification for Central region.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(86, 'Update', 'Quality check scheduled for Phase 2 district at 6:00.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(87, 'Schedule', 'Water interruption in Northwest area. Expected duration: 9 hours.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(88, 'Emergency', 'Emergency repair needed in West district.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(89, 'Service', 'Water interruption in Central area. Expected duration: 10 hours.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(90, 'Maintenance', 'Water interruption in Block A area. Expected duration: 12 hours.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(91, 'Advisory', 'Routine maintenance check in Northwest zone.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(92, 'Emergency', 'Water interruption in East area. Expected duration: 3 hours.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(93, 'Service', 'Service improvement in Block A area. Duration: 12 hours.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(94, 'Advisory', 'Water interruption in Block A area. Expected duration: 5 hours.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(95, 'Advisory', 'System upgrade notification for Block B region.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(96, 'Notice', 'Service improvement in Upper area. Duration: 4 hours.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(97, 'Maintenance', 'Routine maintenance check in Uptown zone.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(98, 'Notice', 'Water interruption in Central area. Expected duration: 10 hours.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(99, 'Notice', 'Important advisory for residents of Block B zone.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(100, 'Notice', 'Important advisory for residents of Phase 3 zone.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(101, 'Maintenance', 'Scheduled maintenance for Block A starting at 3:00.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(102, 'Maintenance', 'Water interruption in Block C area. Expected duration: 3 hours.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(103, 'Maintenance', 'Quality check scheduled for Phase 2 district at 7:00.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(104, 'Update', 'Important advisory for residents of Mid zone.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(105, 'Advisory', 'Routine maintenance check in Southwest zone.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(106, 'Update', 'Routine maintenance check in Block C zone.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(107, 'Update', 'Routine maintenance check in Northwest zone.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(108, 'Update', 'Service improvement in East area. Duration: 8 hours.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(109, 'Update', 'System upgrade notification for Mid region.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(110, 'Advisory', 'System flushing activity in Northwest region.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(111, 'Schedule', 'Water interruption in Downtown area. Expected duration: 6 hours.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(112, 'Schedule', 'System flushing activity in North region.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(113, 'Maintenance', 'Routine maintenance check in Phase 1 zone.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(114, 'Emergency', 'Important advisory for residents of Mid zone.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(115, 'Emergency', 'System flushing activity in Uptown region.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(116, 'Notice', 'System upgrade notification for Uptown region.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(117, 'Emergency', 'Scheduled maintenance for Lower starting at 2:00.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(118, 'Service', 'Routine maintenance check in Phase 2 zone.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(119, 'Schedule', 'Service improvement in Block A area. Duration: 11 hours.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(120, 'Notice', 'Water interruption in West area. Expected duration: 12 hours.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(121, 'Schedule', 'Quality check scheduled for South district at 1:00.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(122, 'Schedule', 'Water interruption in Block C area. Expected duration: 12 hours.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(123, 'Service', 'Emergency repair needed in Southwest district.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(124, 'Update', 'Emergency repair needed in Phase 3 district.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(125, 'Maintenance', 'Quality check scheduled for Downtown district at 12:00.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(126, 'Service', 'Water pressure might be low in Lower area for 12 hours.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(127, 'Maintenance', 'System upgrade notification for Upper region.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(128, 'Emergency', 'System flushing activity in Lower region.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(129, 'Schedule', 'System flushing activity in South region.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(130, 'Schedule', 'Emergency repair needed in Mid district.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(131, 'Maintenance', 'Quality check scheduled for Northeast district at 8:00.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(132, 'Maintenance', 'Scheduled maintenance for Mid starting at 11:00.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(133, 'Advisory', 'Important advisory for residents of East zone.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(134, 'Schedule', 'Water interruption in Southeast area. Expected duration: 9 hours.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(135, 'Notice', 'Emergency repair needed in Lower district.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(136, 'Emergency', 'Quality check scheduled for Block A district at 1:00.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(137, 'Notice', 'Service improvement in Phase 2 area. Duration: 1 hours.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(138, 'Update', 'Water pressure might be low in Block C area for 3 hours.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(139, 'Advisory', 'Emergency repair needed in Lower district.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(140, 'Maintenance', 'Important advisory for residents of Block B zone.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(141, 'Update', 'Service improvement in Block C area. Duration: 12 hours.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(142, 'Schedule', 'Water interruption in Block C area. Expected duration: 9 hours.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(143, 'Service', 'System upgrade notification for Phase 2 region.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(144, 'Update', 'Emergency repair needed in Mid district.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(145, 'Service', 'System flushing activity in Phase 1 region.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(146, 'Schedule', 'Emergency repair needed in Uptown district.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(147, 'Update', 'Scheduled maintenance for North starting at 11:00.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(148, 'Service', 'System flushing activity in Southeast region.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(149, 'Advisory', 'System upgrade notification for Southwest region.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(150, 'Emergency', 'System flushing activity in North region.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(151, 'Maintenance', 'System upgrade notification for North region.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(152, 'Notice', 'Water interruption in Southwest area. Expected duration: 2 hours.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(153, 'Notice', 'System flushing activity in Phase 2 region.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(154, 'Emergency', 'Service improvement in Phase 3 area. Duration: 10 hours.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(155, 'Advisory', 'Water pressure might be low in Phase 1 area for 3 hours.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(156, 'Schedule', 'Service improvement in Phase 3 area. Duration: 7 hours.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(157, 'Update', 'Scheduled maintenance for Northwest starting at 1:00.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(158, 'Schedule', 'Water pressure might be low in Southeast area for 7 hours.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(159, 'Update', 'Water interruption in West area. Expected duration: 12 hours.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(160, 'Emergency', 'System upgrade notification for Mid region.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(161, 'Maintenance', 'Important advisory for residents of Block C zone.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(162, 'Update', 'Water interruption in North area. Expected duration: 5 hours.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(163, 'Advisory', 'Important advisory for residents of Phase 2 zone.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(164, 'Service', 'System upgrade notification for Block C region.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(165, 'Schedule', 'Water interruption in Downtown area. Expected duration: 8 hours.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(166, 'Notice', 'Emergency repair needed in Block A district.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(167, 'Update', 'Routine maintenance check in Phase 2 zone.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(168, 'Notice', 'Scheduled maintenance for Northwest starting at 10:00.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(169, 'Notice', 'System upgrade notification for North region.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(170, 'Update', 'Scheduled maintenance for East starting at 4:00.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(171, 'Service', 'System flushing activity in Northwest region.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(172, 'Maintenance', 'Quality check scheduled for Phase 1 district at 5:00.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(173, 'Advisory', 'Important advisory for residents of Phase 1 zone.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(174, 'Update', 'Scheduled maintenance for Downtown starting at 7:00.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(175, 'Update', 'System upgrade notification for Lower region.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(176, 'Service', 'System upgrade notification for West region.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(177, 'Schedule', 'System upgrade notification for South region.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(178, 'Maintenance', 'Water interruption in Phase 1 area. Expected duration: 9 hours.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(179, 'Update', 'Important advisory for residents of North zone.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(180, 'Maintenance', 'Routine maintenance check in Block C zone.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(181, 'Maintenance', 'Water pressure might be low in Lower area for 5 hours.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(182, 'Schedule', 'Water pressure might be low in Uptown area for 6 hours.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(183, 'Maintenance', 'Quality check scheduled for Downtown district at 2:00.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(184, 'Update', 'Quality check scheduled for Central district at 5:00.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(185, 'Notice', 'Quality check scheduled for East district at 11:00.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(186, 'Update', 'Water pressure might be low in Block A area for 8 hours.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(187, 'Notice', 'Water pressure might be low in Mid area for 11 hours.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(188, 'Update', 'Service improvement in Phase 1 area. Duration: 10 hours.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(189, 'Maintenance', 'Water interruption in Central area. Expected duration: 12 hours.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(190, 'Advisory', 'Scheduled maintenance for Northeast starting at 8:00.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(191, 'Advisory', 'Important advisory for residents of Upper zone.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(192, 'Advisory', 'Water interruption in Block A area. Expected duration: 7 hours.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(193, 'Update', 'Scheduled maintenance for Phase 3 starting at 9:00.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(194, 'Maintenance', 'Emergency repair needed in Phase 3 district.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(195, 'Emergency', 'Water pressure might be low in Phase 2 area for 11 hours.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(196, 'Schedule', 'Water pressure might be low in Uptown area for 7 hours.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(197, 'Maintenance', 'System flushing activity in Block C region.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(198, 'Update', 'Water pressure might be low in Southwest area for 12 hours.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(199, 'Maintenance', 'Quality check scheduled for Block B district at 5:00.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(200, 'Schedule', 'Important advisory for residents of Southwest zone.', '2025-03-09 07:56:52', '2025-03-09 07:56:52'),
(201, 'Test1', 'gghjfhdfsdfhnfgdfvsdfsd', '2025-03-23 10:54:47', '2025-03-23 10:54:47');

-- --------------------------------------------------------

--
-- Table structure for table `meter_reader_blocks`
--

CREATE TABLE `meter_reader_blocks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `block_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `meter_reader_blocks`
--

INSERT INTO `meter_reader_blocks` (`id`, `user_id`, `block_id`, `created_at`, `updated_at`) VALUES
(26, 22, 1, '2025-03-25 14:02:46', '2025-03-25 14:02:46'),
(28, 2, 2, '2025-03-25 14:16:44', '2025-03-25 14:16:44');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2024_01_10_000000_create_roles_table', 1),
(5, '2024_12_21_001801_update_users_table', 1),
(7, '2024_12_21_004403_update_users_table', 2),
(8, '2024_12_21_011703_add_timestamp_user_table', 3),
(9, '2025_01_08_112544_add_role_to_users_table', 4),
(10, '2025_01_09_042929_add_status_to_users_table', 5),
(11, '2025_01_10_000000_create_roles_table', 5),
(12, '2025_01_10_000001_create_users_table', 6),
(13, '2025_01_10_000002_create_permissions_table', 7),
(15, '2025_01_19_010506_add_is_enabled_to_permissions_table', 9),
(16, '2025_01_10_000003_truncate_permissions_table', 10),
(17, '2025_01_10_000005_drop_permissions_table', 11),
(18, '2025_01_10_000006_create_permissions_table', 12),
(19, '2025_01_10_000007_create_role_permission_table', 13),
(20, '2024_01_19_123456_modify_users_role_column', 14),
(21, '2024_01_19_reset_permissions', 15),
(24, '2025_01_10_000004_create_password_resets_table', 16),
(25, '2025_01_10_000005_add_role_column_to_users_table', 16),
(26, '2025_01_10_000007_drop_role_id_from_users_table', 16),
(27, '2025_01_10_000006_drop_data_from_role_permission_table', 17),
(28, '2025_01_10_000007_add_new_user_permission', 18),
(29, '2025_01_21_003956_manage_notice_table', 19),
(30, '2025_01_10_000000_create_bill_rate_table', 20),
(31, '[timestamp]_modify_bill_rate_cubic_meter', 21),
(32, '2025_01_10_000008_create_blocks_table', 22),
(34, '2025_01_23_073232_add_user_id_to_users_table', 23),
(35, '2024_01_23_000004_fix_role_permission_relationship', 24),
(36, '2024_01_23_000005_modify_user_id_column', 25),
(37, '2024_01_23_000007_modify_manage_notice_id_column', 26),
(38, '2024_01_23_000008_modify_bill_rate_id_column', 27),
(39, '2025_01_10_000009_add_block_management_permissions', 28),
(40, '2025_01_24_001645_coverage_date_table', 29),
(41, '2024_01_20_143000_add_coverage_date_management_permissions', 30),
(42, '2024_01_20_143500_create_coverage_date_permissions', 31),
(43, '2025_01_24_090100_add_consumer_type_foreign_key', 32),
(44, '2025_01_24_090200_add_fees_to_water_consumers', 33),
(45, '2025_01_24_090300_create_fees_table', 34),
(46, '2025_01_26_101953_modify_password_resets_table_for_phone_numbers', 35),
(47, '2025_01_10_000001_update_fees_table', 36),
(48, '2025_01_11_000010_add_fee_management_permissions', 37),
(49, '2025_02_14_012109_consumer_reading_table', 38),
(50, '2025_02_14_023557_excess_value_per_cubic', 38),
(51, '2025_02_14_042337_consumer_id_refer_support', 39),
(52, '2025_02_14_065436_consumer_permissions', 40),
(53, '2025_02_17_034014_service_amount_paid', 41),
(54, '2025_02_17_034529_service_paid_status', 42),
(55, '2025_02_20_104224_service_fee_table', 43),
(56, '2025_02_20_105223_edit_conn_payment_table', 44),
(57, '2025_02_20_105743_modify_payment_tables', 45),
(58, '2025_02_20_112133_modify_service_fee_table', 46),
(60, '2025_03_25_132614_modify-consumer-reading-table', 47),
(61, '2025_03_25_211155_create_meter_reader_blocks_table', 48);

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) DEFAULT NULL,
  `phone_number` varchar(11) DEFAULT NULL COMMENT 'For SMS-based password resets',
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`email`, `phone_number`, `token`, `created_at`) VALUES
(NULL, '09708122926', '$2y$12$Wbd4UApuL1p0LFZJfaCsJ.ws5eoWcjeg0YVx/SNAf67I5z5ml3TT2', '2025-01-26 04:21:12'),
('luisej55@gmail.com', NULL, '$2y$12$McA7EcK5NFBkYyUhKTT/2upwEGHIFDS0GWlL.Qgq/GEi6Sfsad5Hq', '2025-02-28 15:08:53');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `password_reset_tokens`
--

INSERT INTO `password_reset_tokens` (`email`, `token`, `created_at`) VALUES
('luisej258@gmail.com', '$2y$12$EvCzuhr2LYPcprwFF4b4SOxGfk2c.MMke7BLa.ruZrjAxsiyXlaGK', '2025-02-28 14:34:06');

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `permission_id` bigint(20) NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`permission_id`, `name`, `slug`, `description`, `created_at`, `updated_at`) VALUES
(2, 'Add New User', 'add-new-user', 'Permission to add a new user', '2025-01-19 01:54:19', '2025-01-19 01:54:19'),
(3, 'Update User', 'update-user', 'Permission to update user account information', '2025-01-19 18:43:17', '2025-01-19 18:43:17'),
(4, 'Deactivate User', 'deactivate-user', 'Permission to activate/deactivate user accounts', '2025-01-19 18:43:17', '2025-01-19 18:43:17'),
(5, 'Access Dashboard', 'access-dashboard', 'Permission to access the dashboard tab', '2025-01-19 19:00:49', '2025-01-19 19:00:49'),
(6, 'Access Consumers', 'access-consumers', 'Permission to access the consumers management tab', '2025-01-19 19:00:49', '2025-01-19 19:00:49'),
(7, 'Access Connection Payment', 'access-connection-payment', 'Permission to access connection payment features', '2025-01-19 19:00:49', '2025-01-19 19:00:49'),
(8, 'Access Billing', 'access-billing', 'Permission to access billing features', '2025-01-19 19:00:49', '2025-01-19 19:00:49'),
(9, 'Access Meter Reading', 'access-meter-reading', 'Permission to access meter reading features', '2025-01-19 19:00:49', '2025-01-19 19:00:49'),
(10, 'Access Reports', 'access-reports', 'Permission to access reports section', '2025-01-19 19:00:49', '2025-01-19 19:00:49'),
(11, 'Access Settings', 'access-settings', 'Permission to access general settings', '2025-01-19 19:00:49', '2025-01-19 19:00:49'),
(12, 'Access Utilities', 'access-utilities', 'Permission to access utilities section', '2025-01-19 19:00:49', '2025-01-19 19:00:49'),
(13, 'View User Management', 'view-user-management', 'Access to user accounts section', '2025-01-19 19:04:01', '2025-01-19 19:04:01'),
(14, 'View Role Management', 'view-role-management', 'Access to role management section', '2025-01-19 19:04:01', '2025-01-19 19:04:01'),
(15, 'View Permissions', 'view-permissions', 'Access to permissions section', '2025-01-19 19:04:01', '2025-01-19 19:04:01'),
(16, 'View Dashboard', 'view-dashboard', 'Access to dashboard section', '2025-01-19 19:04:01', '2025-01-19 19:04:01'),
(17, 'View Settings', 'view-settings', 'Access to general settings', '2025-01-19 19:04:01', '2025-01-19 19:04:01'),
(18, 'View Reports', 'view-reports', 'Access to reports section', '2025-01-19 19:04:01', '2025-01-19 19:04:01'),
(19, 'Add New Role', 'add-new-role', 'Permission to create new roles in the system', '2025-01-19 20:39:29', '2025-01-19 20:39:29'),
(20, 'Edit Role', 'edit-role', 'Permission to modify existing roles', '2025-01-19 20:39:29', '2025-01-19 20:39:29'),
(21, 'Manage Role Permissions', 'manage-role-permissions', 'Permission to assign or remove permissions from roles', '2025-01-19 20:39:29', '2025-01-19 20:39:29'),
(22, 'Delete Role', 'delete-role', 'Permission to remove roles from the system', '2025-01-19 20:41:50', '2025-01-19 20:41:50'),
(23, 'View Notification Management', 'view-notification-management', 'Can view notification management page', '2025-01-20 20:03:43', '2025-01-20 20:03:43'),
(24, 'Add New Notice', 'add-new-notice', 'Can create new notifications', '2025-01-20 20:03:43', '2025-01-20 20:03:43'),
(25, 'Edit Notice', 'edit-notice', 'Can edit existing notifications', '2025-01-20 20:03:43', '2025-01-20 20:03:43'),
(26, 'Delete Notice', 'delete-notice', 'Can delete notifications', '2025-01-20 20:03:43', '2025-01-20 20:03:43'),
(27, 'Access Bill Rate', 'access-bill-rate', 'Can access bill rate management page', '2025-01-22 18:34:19', '2025-01-22 18:34:19'),
(28, 'Add Bill Rate', 'add-bill-rate', 'Can create new bill rates', '2025-01-22 18:34:19', '2025-01-22 18:34:19'),
(29, 'Edit Bill Rate', 'edit-bill-rate', 'Can edit existing bill rates', '2025-01-22 18:34:19', '2025-01-22 18:34:19'),
(30, 'Delete Bill Rate', 'delete-bill-rate', 'Can delete bill rates', '2025-01-22 18:34:19', '2025-01-22 18:34:19'),
(31, 'Access Block Management', 'access-block-management', 'Permission to access block management', '2025-01-23 06:52:31', '2025-01-23 06:52:31'),
(32, 'Add New Block', 'add-new-block', 'Permission to add a new block', '2025-01-23 06:52:31', '2025-01-23 06:52:31'),
(33, 'Edit Block', 'edit-block', 'Permission to edit a block', '2025-01-23 06:52:31', '2025-01-23 06:52:31'),
(34, 'Delete Block', 'delete-block', 'Permission to delete a block', '2025-01-23 06:52:31', '2025-01-23 06:52:31'),
(35, 'Access Coverage Date', 'access-coverage-date', 'Permission to access coverage date management', '2025-01-23 21:12:40', '2025-01-23 21:12:40'),
(36, 'Add Coverage Date', 'add-coverage-date', 'Permission to add new coverage date', '2025-01-23 21:12:40', '2025-01-23 21:12:40'),
(37, 'Edit Coverage Date', 'edit-coverage-date', 'Permission to edit coverage date', '2025-01-23 21:12:40', '2025-01-23 21:12:40'),
(38, 'Delete Coverage Date', 'delete-coverage-date', 'Permission to delete coverage date', '2025-01-23 21:12:40', '2025-01-23 21:12:40'),
(39, 'Edit Fees', 'edit-fees', 'Permission to edit application and reconnection fees', '2025-02-06 05:50:21', '2025-02-06 05:50:21'),
(40, 'Add New Consumer', 'add-new-consumer', 'Permission to add new water consumers', '2025-02-14 00:00:05', '2025-02-14 00:00:05'),
(41, 'Edit Consumer', 'edit-consumer', 'Permission to edit existing water consumers', '2025-02-14 00:00:05', '2025-02-14 00:00:05'),
(42, 'View Consumer Billings', 'view-consumer-billings', 'Permission to view consumer billing history', '2025-02-14 00:00:05', '2025-02-14 00:00:05'),
(43, 'Delete Consumer', 'delete-consumer', 'Permission to delete water consumers', '2025-02-14 00:00:05', '2025-02-14 00:00:05'),
(44, 'Reconnect Consumer', 'reconnect-consumer', 'Permission to reconnect inactive water consumers', '2025-02-14 00:00:05', '2025-02-14 00:00:05'),
(45, 'Access Application Fee', 'access-application-fee', 'Can access application fee management page', '2025-02-16 18:45:04', '2025-02-16 18:45:04'),
(46, 'Process Payment', 'process-application-payment', 'Can process application fee payments', '2025-02-16 18:45:04', '2025-02-16 18:45:04'),
(47, 'Print Application', 'print-application', 'Can print application documents', '2025-02-16 18:45:04', '2025-02-16 18:45:04'),
(48, 'Billing Payment', 'billing-payment', 'Can manage billing payments', '2025-02-16 19:36:52', '2025-02-16 19:36:52'),
(49, 'Access Service Fee', 'service-fee-access', 'Can access service fee section', '2025-02-16 22:32:40', '2025-02-16 22:32:40'),
(50, 'Service Pay', 'service-pay', 'Can process service payments', '2025-02-16 22:32:40', '2025-02-16 22:32:40'),
(51, 'Service Print', 'service-print', 'Can print service documents', '2025-02-16 22:32:40', '2025-02-16 22:32:40');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`role_id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'Meter Reader', NULL, NULL),
(2, 'Treasurer', NULL, NULL),
(3, 'Administrator', NULL, '2025-03-24 05:44:01'),
(34, 'Guest', '2025-01-17 06:51:11', '2025-03-23 08:44:55');

-- --------------------------------------------------------

--
-- Table structure for table `role_permission`
--

CREATE TABLE `role_permission` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_permission`
--

INSERT INTO `role_permission` (`role_id`, `permission_id`, `created_at`, `updated_at`) VALUES
(1, 5, NULL, NULL),
(1, 6, NULL, NULL),
(1, 8, NULL, NULL),
(1, 9, NULL, NULL),
(1, 10, NULL, NULL),
(2, 5, NULL, NULL),
(2, 6, NULL, NULL),
(2, 7, NULL, NULL),
(2, 10, NULL, NULL),
(2, 11, NULL, NULL),
(2, 13, NULL, NULL),
(2, 14, NULL, NULL),
(2, 27, NULL, NULL),
(2, 28, NULL, NULL),
(2, 29, NULL, NULL),
(2, 30, NULL, NULL),
(2, 31, NULL, NULL),
(2, 32, NULL, NULL),
(2, 33, NULL, NULL),
(2, 34, NULL, NULL),
(2, 35, NULL, NULL),
(2, 36, NULL, NULL),
(2, 37, NULL, NULL),
(2, 38, NULL, NULL),
(2, 39, NULL, NULL),
(2, 40, NULL, NULL),
(2, 41, NULL, NULL),
(2, 42, NULL, NULL),
(2, 44, NULL, NULL),
(2, 45, NULL, NULL),
(2, 46, NULL, NULL),
(2, 47, NULL, NULL),
(2, 48, NULL, NULL),
(2, 49, NULL, NULL),
(2, 50, NULL, NULL),
(2, 51, NULL, NULL),
(3, 2, NULL, NULL),
(3, 3, NULL, NULL),
(3, 4, NULL, NULL),
(3, 5, NULL, NULL),
(3, 6, NULL, NULL),
(3, 8, NULL, NULL),
(3, 9, NULL, NULL),
(3, 10, NULL, NULL),
(3, 11, NULL, NULL),
(3, 12, NULL, NULL),
(3, 13, NULL, NULL),
(3, 14, NULL, NULL),
(3, 19, NULL, NULL),
(3, 20, NULL, NULL),
(3, 21, NULL, NULL),
(3, 22, NULL, NULL),
(3, 23, NULL, NULL),
(3, 24, NULL, NULL),
(3, 25, NULL, NULL),
(3, 26, NULL, NULL),
(3, 27, NULL, NULL),
(3, 28, NULL, NULL),
(3, 29, NULL, NULL),
(3, 30, NULL, NULL),
(3, 31, NULL, NULL),
(3, 32, NULL, NULL),
(3, 33, NULL, NULL),
(3, 34, NULL, NULL),
(3, 35, NULL, NULL),
(3, 36, NULL, NULL),
(3, 37, NULL, NULL),
(3, 38, NULL, NULL),
(3, 39, NULL, NULL),
(3, 40, NULL, NULL),
(3, 41, NULL, NULL),
(3, 42, NULL, NULL),
(3, 43, NULL, NULL),
(3, 44, NULL, NULL),
(3, 45, NULL, NULL),
(3, 46, NULL, NULL),
(3, 47, NULL, NULL),
(3, 49, NULL, NULL),
(3, 50, NULL, NULL),
(3, 51, NULL, NULL),
(34, 5, NULL, NULL),
(34, 6, NULL, NULL),
(34, 8, NULL, NULL),
(34, 9, NULL, NULL),
(34, 10, NULL, NULL),
(34, 11, NULL, NULL),
(34, 12, NULL, NULL),
(34, 13, NULL, NULL),
(34, 14, NULL, NULL),
(34, 23, NULL, NULL),
(34, 27, NULL, NULL),
(34, 31, NULL, NULL),
(34, 35, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `service_fee_payment`
--

CREATE TABLE `service_fee_payment` (
  `service_pay_id` bigint(20) UNSIGNED NOT NULL,
  `customer_id` varchar(255) NOT NULL,
  `service_amount_paid` decimal(10,2) NOT NULL,
  `reconnection_fee` decimal(10,2) DEFAULT NULL,
  `service_paid_status` enum('paid','unpaid') NOT NULL DEFAULT 'unpaid',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `service_fee_payment`
--

INSERT INTO `service_fee_payment` (`service_pay_id`, `customer_id`, `service_amount_paid`, `reconnection_fee`, `service_paid_status`, `created_at`, `updated_at`) VALUES
(2, 'B01-02', 700.00, 700.00, 'paid', '2025-02-20 04:51:06', '2025-02-20 04:51:29'),
(6, 'B01-01', 700.00, 700.00, 'paid', '2025-02-20 05:29:00', '2025-02-20 05:29:19'),
(8, 'B01-02', 700.00, 700.00, 'paid', '2025-02-20 05:43:39', '2025-02-20 05:43:57'),
(10, 'B01-03', 700.00, 700.00, 'paid', '2025-03-01 07:12:26', '2025-03-01 07:12:42'),
(11, 'B01-03', 700.00, 700.00, 'paid', '2025-03-16 02:41:12', '2025-03-16 02:42:34'),
(13, 'B02-01', 700.00, 700.00, 'paid', '2025-03-24 04:43:52', '2025-03-24 04:54:46');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('3hKTiUBtSwnDlV1A89YBbcr8h9KzTq05j9TEqwKz', NULL, '192.168.254.105', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/133.0.0.0 Mobile Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoicnVEajRSdFpIZk01TGt1MlRJcE5xbVF1Z0JsamFFWnBmM29ZaHRZRSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzY6Imh0dHA6Ly8xOTIuMTY4LjI1NC4xMDc6ODAwMC9tcl9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1740642123),
('55pgn9kV0RYPObVbO0PJFPvDTYwlUlNH1WHXhoHL', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/133.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiak1Bb21OWGlyWkFIV01IWndjaEVyZzRGOENQQlV5bkF6N3hNdjdsTyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1739672966),
('bR6ZMhkCvuCFNNWyUGXs5TgFIJ0qxFIOQ7vhfaD3', NULL, '192.168.254.105', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/133.0.0.0 Mobile Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiWWl5UnZSWnVQdk5YWHpnRGNhbWxZUkFlUlpJWHl1YjNkc3JlMk5OTCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xOTIuMTY4LjI1NC4xMDc6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1739678042);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `firstname` varchar(255) NOT NULL,
  `lastname` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `contactnum` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'user',
  `status` enum('activate','deactivate') NOT NULL DEFAULT 'activate',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `firstname`, `lastname`, `username`, `email`, `password`, `contactnum`, `role`, `status`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Ejay Luis', 'Rebucas', 'chillZY', 'luisej258@gmail.com', '$2y$12$fCX6Kv14r9B9P9q2.g41ces1HMHp0fbPZQH4oJrIh2my1AkMj0imK', '09275824005', 'Administrator', 'activate', 'gkas3TbiDYLdxvLfdRBO4SQl9CxokgH4SV5vi5i2gee7Mu9vpmq2maY9Va8u', '2025-01-14 00:16:33', '2025-03-25 00:24:21'),
(2, 'Clyde Mark', 'Rebucas', 'herogods tzy', 'clyde217@gmail.com', '$2y$12$4FkXp9utOxOlxOcKwQ.UMOk.wKLi2tJUn1z.jhJADElcakvz3PQU2', '09876543211', 'Meter Reader', 'activate', NULL, '2025-01-16 04:19:05', '2025-03-25 12:40:14'),
(3, 'Ronald James', 'Rebucas', 'rjrhebz', 'none@gmail.com', '$2y$12$rbLg/9/tnMNSqKFf8nRrsezo37BiGK/lPtsN4pzJGTU8XT.UH.J0i', '09708122926', 'Treasurer', 'activate', NULL, '2025-01-19 19:24:05', '2025-02-04 02:04:08'),
(12, 'shina', 'melendres', 'shin', 'melendresshina@gmail.com', '$2y$12$ID8oWAKlDdeR8tImYMXFhuKjnW.YGYGQBgP21Q2f92u5B7ZBINcfS', '09636455571', 'Administrator', 'activate', NULL, '2025-02-06 19:40:17', '2025-03-24 05:44:01'),
(13, 'John Bernard', 'Manzano', 'admin@m', 'manzanojohnb11@gmail.com', '$2y$12$240ZVJfklf3Qx94HmIzyF.2VoWg9wUqTVvuEbFALmjeVKx.2yYyzG', '09458338246', 'Administrator', 'activate', NULL, '2025-02-06 21:19:03', '2025-03-24 05:44:01'),
(22, 'Jean Edrean', 'Buyan', 'jean123', 'luisejay55@gmail.com', '$2y$12$mkxaOg5mgdSDAlir072.ZeDodQ0HX.fipPnhoHJH8se14AgcyY.3O', '09876543266', 'Meter Reader', 'activate', NULL, '2025-03-16 00:59:48', '2025-03-25 14:12:11');

-- --------------------------------------------------------

--
-- Table structure for table `water_consumers`
--

CREATE TABLE `water_consumers` (
  `watercon_id` bigint(20) UNSIGNED NOT NULL,
  `block_id` bigint(20) UNSIGNED NOT NULL,
  `customer_id` varchar(10) NOT NULL,
  `firstname` varchar(100) NOT NULL,
  `middlename` varchar(100) DEFAULT NULL,
  `lastname` varchar(100) NOT NULL,
  `address` text NOT NULL,
  `contact_no` varchar(20) NOT NULL,
  `consumer_type` varchar(255) NOT NULL,
  `status` enum('Active','Inactive','Pending') NOT NULL DEFAULT 'Pending',
  `application_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `service_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `water_consumers`
--

INSERT INTO `water_consumers` (`watercon_id`, `block_id`, `customer_id`, `firstname`, `middlename`, `lastname`, `address`, `contact_no`, `consumer_type`, `status`, `application_fee`, `service_fee`, `created_at`, `updated_at`) VALUES
(45, 1, 'B01-01', 'Juan', 'Santos', 'Dela Cruz', 'Block 1, Street 1', '09123456789', 'Residential', 'Pending', 1050.00, 0.00, '2025-02-27 02:15:46', '2025-02-27 02:15:46'),
(46, 1, 'B01-02', 'Maria', 'Garcia', 'Santos', 'Block 1, Street 2', '09234567890', 'Residential', 'Pending', 1050.00, 0.00, '2025-02-27 02:15:46', '2025-02-27 02:15:46'),
(53, 1, 'B01-03', 'Clyde Mark', 'Corro', 'Rebucas', 'Purok 6, Panab-an', '09275824654', 'Industrial', 'Active', 0.00, 0.00, '2025-02-28 03:00:23', '2025-03-16 02:44:40'),
(56, 2, 'B02-01', 'Ejay Luis', 'Corro', 'Rebucas', 'Purok 6, Capayas', '09275824005', 'Residential', 'Inactive', 0.00, 0.00, '2025-03-24 03:52:56', '2025-03-24 04:54:46'),
(57, 3, 'B03-01', 'Jean Edrean', 'Luis', 'Buyan', 'Purok 6, BarangayTest', '09545932543', 'Commercial', 'Active', 0.00, 0.00, '2025-03-24 04:44:27', '2025-03-24 04:44:41');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bill_rate`
--
ALTER TABLE `bill_rate`
  ADD PRIMARY KEY (`billrate_id`),
  ADD UNIQUE KEY `consumer_type` (`consumer_type`);

--
-- Indexes for table `blocks`
--
ALTER TABLE `blocks`
  ADD PRIMARY KEY (`block_id`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `conn_payment`
--
ALTER TABLE `conn_payment`
  ADD PRIMARY KEY (`connpay_id`);

--
-- Indexes for table `consumer_bill_pay`
--
ALTER TABLE `consumer_bill_pay`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `consumer_reading`
--
ALTER TABLE `consumer_reading`
  ADD PRIMARY KEY (`consread_id`),
  ADD KEY `consumer_reading_customer_id_foreign` (`customer_id`),
  ADD KEY `consumer_reading_covdate_id_foreign` (`covdate_id`),
  ADD KEY `consumer_reading_reading_date_index` (`reading_date`),
  ADD KEY `consumer_reading_due_date_index` (`due_date`);

--
-- Indexes for table `coverage_date`
--
ALTER TABLE `coverage_date`
  ADD PRIMARY KEY (`covdate_id`),
  ADD KEY `idx_coverage_dates` (`coverage_date_from`,`coverage_date_to`),
  ADD KEY `idx_reading_date` (`reading_date`),
  ADD KEY `idx_due_date` (`due_date`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `fees`
--
ALTER TABLE `fees`
  ADD PRIMARY KEY (`fee_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `manage_notice`
--
ALTER TABLE `manage_notice`
  ADD PRIMARY KEY (`notice_id`);

--
-- Indexes for table `meter_reader_blocks`
--
ALTER TABLE `meter_reader_blocks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `meter_reader_blocks_user_id_block_id_unique` (`user_id`,`block_id`),
  ADD KEY `meter_reader_blocks_block_id_foreign` (`block_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`permission_id`),
  ADD UNIQUE KEY `permissions_name_unique` (`name`),
  ADD UNIQUE KEY `permissions_slug_unique` (`slug`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_id`),
  ADD UNIQUE KEY `roles_name_unique` (`name`);

--
-- Indexes for table `role_permission`
--
ALTER TABLE `role_permission`
  ADD PRIMARY KEY (`role_id`,`permission_id`),
  ADD KEY `role_permission_permission_id_foreign` (`permission_id`);

--
-- Indexes for table `service_fee_payment`
--
ALTER TABLE `service_fee_payment`
  ADD PRIMARY KEY (`service_pay_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `users_username_unique` (`username`),
  ADD UNIQUE KEY `users_contactnum_unique` (`contactnum`),
  ADD KEY `user_role` (`role`);

--
-- Indexes for table `water_consumers`
--
ALTER TABLE `water_consumers`
  ADD PRIMARY KEY (`watercon_id`),
  ADD KEY `water_consumers_consumer_type_foreign` (`consumer_type`),
  ADD KEY `Consumer_block` (`block_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bill_rate`
--
ALTER TABLE `bill_rate`
  MODIFY `billrate_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `blocks`
--
ALTER TABLE `blocks`
  MODIFY `block_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `conn_payment`
--
ALTER TABLE `conn_payment`
  MODIFY `connpay_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `consumer_bill_pay`
--
ALTER TABLE `consumer_bill_pay`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `consumer_reading`
--
ALTER TABLE `consumer_reading`
  MODIFY `consread_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `coverage_date`
--
ALTER TABLE `coverage_date`
  MODIFY `covdate_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fees`
--
ALTER TABLE `fees`
  MODIFY `fee_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `manage_notice`
--
ALTER TABLE `manage_notice`
  MODIFY `notice_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=202;

--
-- AUTO_INCREMENT for table `meter_reader_blocks`
--
ALTER TABLE `meter_reader_blocks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `service_fee_payment`
--
ALTER TABLE `service_fee_payment`
  MODIFY `service_pay_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `water_consumers`
--
ALTER TABLE `water_consumers`
  MODIFY `watercon_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `consumer_reading`
--
ALTER TABLE `consumer_reading`
  ADD CONSTRAINT `consumer_reading_covdate_id_foreign` FOREIGN KEY (`covdate_id`) REFERENCES `coverage_date` (`covdate_id`) ON DELETE CASCADE;

--
-- Constraints for table `meter_reader_blocks`
--
ALTER TABLE `meter_reader_blocks`
  ADD CONSTRAINT `meter_reader_blocks_block_id_foreign` FOREIGN KEY (`block_id`) REFERENCES `blocks` (`block_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `meter_reader_blocks_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `role_permission`
--
ALTER TABLE `role_permission`
  ADD CONSTRAINT `role_permission_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`) ON DELETE CASCADE;

--
-- Constraints for table `water_consumers`
--
ALTER TABLE `water_consumers`
  ADD CONSTRAINT `Consumer_block` FOREIGN KEY (`block_id`) REFERENCES `blocks` (`block_id`),
  ADD CONSTRAINT `water_consumers_consumer_type_foreign` FOREIGN KEY (`consumer_type`) REFERENCES `bill_rate` (`consumer_type`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
