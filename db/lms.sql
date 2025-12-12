-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 29, 2025 at 09:34 AM
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
-- Database: `lms`
--

-- --------------------------------------------------------

--
-- Table structure for table `leave_available_balance`
--

CREATE TABLE `leave_available_balance` (
  `id` int(11) NOT NULL,
  `employee_email` varchar(100) NOT NULL,
  `leave_month` varchar(6) NOT NULL,
  `casual_leave` int(11) DEFAULT 0,
  `sick_leave` int(11) DEFAULT 0,
  `earned_leave` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leave_available_balance`
--

INSERT INTO `leave_available_balance` (`id`, `employee_email`, `leave_month`, `casual_leave`, `sick_leave`, `earned_leave`) VALUES
(2, 'dayaram9878551694@gmail.com', '202504', 3, 3, 0);

-- --------------------------------------------------------

--
-- Table structure for table `leave_requests`
--

CREATE TABLE `leave_requests` (
  `id` int(11) NOT NULL,
  `employee_email` varchar(100) NOT NULL,
  `leave_type` enum('casual','earned','medical') NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `reason` text NOT NULL,
  `proof_image` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `applied_on` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leave_requests`
--

INSERT INTO `leave_requests` (`id`, `employee_email`, `leave_type`, `start_date`, `end_date`, `reason`, `proof_image`, `status`, `applied_on`, `updated_at`) VALUES
(1, 'dayaram9878551694@gmail.com', 'casual', '2025-04-30', '2025-04-30', 'asfvby', '', 'pending', '2025-04-29 07:25:51', NULL),
(2, 'dayaram9878551694@gmail.com', 'casual', '2025-04-30', '2025-04-30', 'asfvby', '', 'pending', '2025-03-19 07:26:22', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('employee','manager') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`) VALUES
(41, 'anuj', 'anujkumar2727272@gmail.com', '$2y$10$JxKO1rpPUGB2.El2tlQl9.7H6c9romPB22c9FqVpnWVIztFZoYZ/.', 'manager', '2025-04-29 07:21:01'),
(45, 'daya', 'dayaram9878551694@gmail.com', '$2y$10$yZtHm.eY93EgjTZJRgQ/K.1YpI6Ogf73dotCsnMiJ4qaZJqymRByC', 'employee', '2025-04-29 07:24:12');

--
-- Triggers `users`
--
DELIMITER $$
CREATE TRIGGER `after_employee_insert` AFTER INSERT ON `users` FOR EACH ROW BEGIN
    DECLARE current_month VARCHAR(6);
    SET current_month = DATE_FORMAT(CURRENT_DATE, '%Y%m');

    INSERT INTO leave_available_balance (employee_email, leave_month, casual_leave, sick_leave, earned_leave)
    VALUES (NEW.email, current_month, 3, 3, 0);
END
$$
DELIMITER ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `leave_available_balance`
--
ALTER TABLE `leave_available_balance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employee_email` (`employee_email`,`leave_month`);

--
-- Indexes for table `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_email` (`employee_email`);

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
-- AUTO_INCREMENT for table `leave_available_balance`
--
ALTER TABLE `leave_available_balance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `leave_requests`
--
ALTER TABLE `leave_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `leave_available_balance`
--
ALTER TABLE `leave_available_balance`
  ADD CONSTRAINT `leave_available_balance_ibfk_1` FOREIGN KEY (`employee_email`) REFERENCES `users` (`email`) ON DELETE CASCADE;

--
-- Constraints for table `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD CONSTRAINT `leave_requests_ibfk_1` FOREIGN KEY (`employee_email`) REFERENCES `users` (`email`);

DELIMITER $$
--
-- Events
--
CREATE DEFINER=`root`@`localhost` EVENT `add_monthly_leave_balance` ON SCHEDULE EVERY 1 MONTH STARTS '2025-05-01 00:00:00' ON COMPLETION NOT PRESERVE ENABLE DO BEGIN
    INSERT INTO leave_available_balance (employee_email, leave_month, casual_leave, sick_leave, earned_leave)
    SELECT email, DATE_FORMAT(CURRENT_DATE, '%Y%m'), 3, 10, 0
    FROM users
    WHERE NOT EXISTS (
        SELECT 1 FROM leave_available_balance 
        WHERE employee_email = users.email 
        AND leave_month = DATE_FORMAT(CURRENT_DATE, '%Y%m')
    );
END$$

DELIMITER ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
