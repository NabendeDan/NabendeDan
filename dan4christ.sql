-- phpMyAdmin SQL Dump
-- version 4.6.4
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 29, 2026 at 09:30 AM
-- Server version: 5.7.14
-- PHP Version: 5.6.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dan4christ`
--

-- --------------------------------------------------------

--
-- Table structure for table `auto_payment_settings`
--

CREATE TABLE `auto_payment_settings` (
  `id` int(11) NOT NULL,
  `staff_id` varchar(20) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_day` int(11) DEFAULT '28',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `auto_payment_settings`
--

INSERT INTO `auto_payment_settings` (`id`, `staff_id`, `amount`, `payment_day`, `is_active`, `created_at`, `updated_at`) VALUES
(1, '5', '340000.00', 28, 1, '2026-06-29 06:43:19', '2026-06-29 06:43:19');

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `course_id` int(11) NOT NULL,
  `course_name` varchar(100) NOT NULL,
  `dept_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`course_id`, `course_name`, `dept_id`) VALUES
(1, 'DIPLOMA IN INFORMATION TECHNOLOGY', 1),
(2, 'DIPLOMA IN COMPUTER SCIENCE', 1),
(3, 'NATIONAL CERTIFICATE IN INFORMATION COMMUNICATION TECHNOLOGY', 1),
(4, 'NATIONAL CERTIFICATE IN COMPUTER REPAIR AND MAINTENANCE', 1),
(5, 'CERTIFICATE IN NURSING AND MIDWIFERY', 3),
(6, 'CERTIFICATE IN LABORATORY ASSISTANT', 1),
(7, 'DIPLOMA IN CLINICAL MEDICINE', 3),
(8, 'NATIONAL CERTIFICATE IN ELECTRICAL INSTALLATION', 4),
(9, 'NATIONAL CERTIFICATE IN CIVIL ENGINEERING', 4),
(10, 'NATIONAL DIPLOMA IN CIVIL ENGINEERING', 4),
(11, 'NATIONAL DIPLOMA IN ELECTRICAL INSTALLATION', 4),
(12, 'DIPLOMA IN GRADE THREE TEACHEING', 2),
(13, 'DIPLOMA IN SOCIAL WORKS AND SOCIAL ADMINISTRATION', 2),
(14, 'NATIONAL CERTIFICATE IN JOURNELISM  MASS COMMUNICATION ', 2),
(15, 'CERTIFCATE IN WELDING AND ASSEMBELING', 5),
(16, 'DIPLOMA IN CARPENTRY', 5),
(17, 'DIT', 4),
(18, 'NATIONAL CERTIFICATE IN MECHANICLE ENGINEERING', 4);

-- --------------------------------------------------------

--
-- Table structure for table `course_units`
--

CREATE TABLE `course_units` (
  `unit_id` int(11) NOT NULL,
  `unit_name` varchar(100) NOT NULL,
  `course_id` int(11) DEFAULT NULL,
  `year` int(11) NOT NULL,
  `semester` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `course_units`
--

INSERT INTO `course_units` (`unit_id`, `unit_name`, `course_id`, `year`, `semester`) VALUES
(1, 'BRICK LAYING', 17, 0, 0),
(2, '3 MODELING', 17, 0, 0),
(3, 'FUNDAMENTALS OF BUILDING', 17, 0, 0),
(4, 'COMPUTER LITERACY 1', 1, 1, 1),
(5, 'COMPUTER APPLICATIONS', 1, 1, 1),
(6, 'COMPUTER REPAIR AND MAINTENACE', 1, 1, 1),
(7, 'FUNDAMENTALS OF PROGRAMING', 1, 1, 1),
(8, 'COMMUNICATION SKILS', 1, 1, 1),
(9, 'DATA BASE DESIGN', 1, 1, 2),
(10, 'E-COMMERCE', 1, 1, 2),
(11, 'SYSTEM ANALYSIS', 1, 1, 2),
(12, 'BASIC KISWAHILI', 1, 1, 2),
(13, 'WEB DEVELOPMENT', 1, 2, 1),
(14, 'SOFTWARE ENGENEERING', 1, 2, 1),
(15, 'DATABASE MANAGEMENT', 1, 2, 1),
(16, 'INDUSTRIAL TRANING', 1, 2, 1),
(17, 'NETWOKING AND DATA COMMUNICATION', 1, 2, 2),
(18, 'HUMAN RESOURCE MANAGEMENT', 1, 2, 2),
(19, 'RESAEARCH PROPOSAL', 1, 2, 2),
(20, 'MULTIMEDIA', 1, 2, 2),
(21, 'INTRODUCTION TO DRIVING', 18, 1, 1),
(22, 'FUNDAMENTALS OF MECHANICS', 18, 1, 1),
(23, 'ENGINEERING MATHMATICS', 18, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `dept_id` int(11) NOT NULL,
  `dept_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`dept_id`, `dept_name`) VALUES
(1, 'SCIENCE & TECHNOLOGY'),
(2, 'ARTS AND HUMMANITIES'),
(3, 'ALLIED HEALTH'),
(4, 'ENGINEERING'),
(5, 'TECHNICAL AND INDUSTRIAL TRANNING'),
(7, 'POLITICAL EDUCATION');

-- --------------------------------------------------------

--
-- Table structure for table `directors`
--

CREATE TABLE `directors` (
  `director_id` int(11) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `dob` date DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `directors`
--

INSERT INTO `directors` (`director_id`, `fullname`, `dob`, `phone`, `username`, `password`, `photo`) VALUES
(1, 'NABENDE DAN', '1997-04-20', '0787014829', 'Dan', 'Dan123', NULL),
(3, 'System Administrator', '1980-01-01', '0700000000', 'admin', '$2y$10$ikBhkAoj70crspcJLNsNI.bMhpaWtcldo7BF/BRQMc3EPR5Er6ns6', NULL),
(4, 'System Director', NULL, NULL, 'director1', '$2y$10$EixZaYVK1fsbw1ZfbX3OXePaWxn96p36xWf5q5a8Y3zFQ2z6lYf5K', NULL),
(5, 'NABWIRE DEBORAH', '2002-12-22', '0704581773', 'Debra', '$2y$10$fK24Y.mHk92oJZZ2v8N9pO3IYv414OnEy6ELv43jynvjd4cHpJ.72', NULL),
(6, 'CATHERINE', '2000-10-10', '0714172590', 'admin1', '$2y$10$y0ulfTpuyczfK3nb8bAqweFmkFOgxcR.BsrRIndI3igWgYhPF74e6', 'uploads/staff/1782392224_6a3d25a0a78c8.jpeg');

-- --------------------------------------------------------

--
-- Table structure for table `marks`
--

CREATE TABLE `marks` (
  `regno` varchar(50) DEFAULT NULL,
  `unit_id` int(11) DEFAULT NULL,
  `semester` int(11) DEFAULT NULL,
  `year` int(11) DEFAULT NULL,
  `score` decimal(5,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `marks`
--

INSERT INTO `marks` (`regno`, `unit_id`, `semester`, `year`, `score`) VALUES
('26/DIT', 1, 1, 1, '76.00'),
('26/DIT', 2, 1, 1, '80.00'),
('26/DIT', 3, 1, 1, '65.00'),
('26/DICT', 4, 1, 1, '75.00'),
('26/DICT', 5, 1, 1, '90.00'),
('26/DICT', 6, 1, 1, '64.00'),
('26/DICT', 7, 1, 1, '77.00'),
('26/DICT', 8, 1, 1, '78.00'),
('26/DICT', 12, 2, 2026, '55.00'),
('26/DICT', 9, 2, 2026, '55.00'),
('26/DICT', 10, 2, 2026, '55.00'),
('26/DICT', 18, 2, 2026, '10.00'),
('26/DICT', 20, 2, 2026, '74.00'),
('26/DICT', 17, 2, 2026, '44.00'),
('26/DICT', 19, 2, 2026, '44.00'),
('26/DICT', 11, 2, 2026, '76.00'),
('26/DICT', 8, 1, 2, '77.00'),
('26/DICT', 5, 1, 2, '87.80'),
('26/DICT', 4, 1, 2, '88.00'),
('26/DICT', 6, 1, 2, '58.00'),
('26/DICT', 15, 1, 2, '87.00'),
('26/DICT', 7, 1, 2, '77.90'),
('26/DICT', 16, 1, 2, '88.00'),
('26/DICT', 14, 1, 2, '87.80'),
('26/DICT', 13, 1, 2, '85.00');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `code_id` int(11) DEFAULT NULL,
  `regno` varchar(50) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `purpose` varchar(100) DEFAULT NULL,
  `semester` int(11) DEFAULT NULL,
  `year` int(11) DEFAULT NULL,
  `date_paid` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `code_id`, `regno`, `amount`, `purpose`, `semester`, `year`, `date_paid`) VALUES
(1, 2, '26/ICT', '500000.00', 'feee', 1, 2, '2026-06-02 22:59:09'),
(2, 7, '26/DIT', '500000.00', 'FEEE', 1, 2000, '2026-06-24 13:19:47'),
(3, 7, '26/DIT', '150000.00', 'INTERNSHIP', 2, 2000, '2026-06-24 13:20:20'),
(4, 7, '26/DIT', '80000.00', 'MEDICACAL', 1, 2, '2026-06-24 13:30:51'),
(5, 6, '26/DICT', '50000.00', 'MEDICACAL', 1, 1, '2026-06-24 13:49:26');

-- --------------------------------------------------------

--
-- Table structure for table `payment_codes`
--

CREATE TABLE `payment_codes` (
  `code_id` int(11) NOT NULL,
  `regno` varchar(50) DEFAULT NULL,
  `code` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `payment_codes`
--

INSERT INTO `payment_codes` (`code_id`, `regno`, `code`, `created_at`) VALUES
(2, '26/ICT', '', '2026-06-02 21:02:00'),
(3, '26/DPE/001/IST', '', '2026-06-02 21:21:17'),
(6, '26/DICT', '1002', '2026-06-24 12:27:25'),
(7, '26/DIT', '1003', '2026-06-24 13:05:28');

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `staff_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `national_id` varchar(50) DEFAULT NULL,
  `marital_status` varchar(20) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `district` varchar(100) DEFAULT NULL,
  `emergency_contact` varchar(100) DEFAULT NULL,
  `emergency_relationship` varchar(50) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `position` varchar(100) DEFAULT NULL,
  `qualifications` text,
  `date_hired` date DEFAULT NULL,
  `salary` decimal(10,2) DEFAULT NULL,
  `contract_type` varchar(20) DEFAULT NULL,
  `role` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`staff_id`, `username`, `password`, `fullname`, `gender`, `dob`, `national_id`, `marital_status`, `phone`, `email`, `address`, `district`, `emergency_contact`, `emergency_relationship`, `department_id`, `position`, `qualifications`, `date_hired`, `salary`, `contract_type`, `role`, `created_at`) VALUES
(1, 'levi', '$2y$10$j92vBOCG500QQrZsxIXLeue0y6GHehIYoc.ByXNe.nPIYYYaQrO82', 'MAFABI LEVI', 'Male', '1997-02-20', 'CM970245786CMHJ', 'Single', '0714172590', 'cnumulwasira2@gmail.com', 'KAMPALA', 'SIRONKO', '09777777', 'WIFE', 4, 'LECTURER', 'BCE', '2001-02-02', '800000.00', 'Contract', 'Lecturer', '2026-06-25 08:29:24'),
(2, 'namata', '$2y$10$7lWMRPMRKjBPMJp4DMwTbu2x8ynq/ZnYOWehdwCaXppBMDnS9HiTq', 'NAMATAKA', 'Female', '1995-05-02', 'CF870245786CMHJ', 'Married', '0714172590', 'jamesatuhura@gmail.com', 'NAKAWA-NAGURU', 'MBALE', '0787014859', 'HUSBAND', 3, 'LECTURER', 'BCM', '2026-07-20', '1200000.00', 'Full-time', 'Lecturer', '2026-06-25 08:33:03'),
(3, 'hhhhh', '$2y$10$N/Lwm2PvWxZJgOOqGLPyguqieimmNLKjWLzblvj2jYJuZShUq8fhm', 'MAFABI LEVI', 'Male', '0005-05-04', 'CM970245786CMHJ', 'Married', '57644', 'mafabilevi@gmail.com', '3543553', 'rr', '54354544', '6464', 3, '45', '6543646', '2028-06-02', '455555.00', 'Full-time', 'Staff', '2026-06-25 08:37:02'),
(5, 'LUSHA', '$2y$10$33vsWTcEPu52PtMOivB0ruVBxE/2pzXRU/iylTLJU9CIAPyLmLUnW', 'GALUSHA', 'Male', '2026-06-25', 'CM970245786CMHJ', 'Single', '0714172590', 'merdantrust@gmail.com', 'MANZE', 'SIRONKO', '0787014859', 'HUSBAND', 3, 'LECTURER', 'DPH', '2200-03-02', '80000000.00', 'Part-time', 'Admin', '2026-06-25 09:31:08');

-- --------------------------------------------------------

--
-- Table structure for table `staff_payments`
--

CREATE TABLE `staff_payments` (
  `payment_id` int(11) NOT NULL,
  `staff_id` varchar(20) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) DEFAULT 'Mobile Money',
  `phone_number` varchar(20) NOT NULL,
  `payment_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `payment_type` enum('manual','auto') DEFAULT 'manual',
  `status` enum('pending','completed','failed') DEFAULT 'pending',
  `reference` varchar(100) DEFAULT NULL,
  `notes` text,
  `paid_by` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `staff_payments`
--

INSERT INTO `staff_payments` (`payment_id`, `staff_id`, `amount`, `payment_method`, `phone_number`, `payment_date`, `payment_type`, `status`, `reference`, `notes`, `paid_by`) VALUES
(1, '5', '340000.00', 'Mobile Money', '0714172590', '2026-06-29 09:52:12', 'manual', 'completed', 'PAY-20260629065212-5040', NULL, 'System Administrator');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `student_id` int(11) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `regno` varchar(50) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `email` varchar(60) NOT NULL,
  `district` varchar(50) DEFAULT NULL,
  `course_id` int(11) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `year_of_study` int(11) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_id`, `fullname`, `regno`, `dob`, `phone`, `username`, `password`, `email`, `district`, `course_id`, `department_id`, `year_of_study`, `photo`) VALUES
(1, 'NABUDUWA CATHERINE', '26/DPE/001/IST', '1997-07-28', '0714172590', 'cathy', '$2y$10$OBb0OdZrzryvQ6YFDJJsj.C2lASPWE61lfaUVTy7fyXeSd4uMD4c6', '', 'SIRONKO', 12, 4, 1, 'uploads/1780005543_wife.png'),
(2, 'ZAIN WIWO', '26/ICT', '2001-02-20', '0714114907', 'Za', '$2y$10$LN7lJ/W3dLJNZfmL/rOjN.WsSDrq56DW0tyNlbNV565A8hOmnkNV2', '', 'MBALE', 10, 4, 3, 'uploads/1780434042_wife.png'),
(4, 'KEVIN ATENYO', '26/DIT', '2000-03-02', '04142347665', 'KEV', '$2y$10$wRirGfuScJMZcMYDoVxxmO5690ftgFutVmS3T1AeSG2yMcpYnU/9S', '', 'KAPCHORWA', 17, 4, 1, 'uploads/1780484722_wife.png'),
(5, 'MAGOMU MARK', '26/DICT', '1998-02-19', '0763536364747', 'Mark', '$2y$10$tPMh9omTTb/u2QTVwPBaTuMdTUZ9/ciFiI4vTFTN3UxV.iLMxycRW', '', 'SIRONKO', 1, 1, 1, 'uploads/1780487959_wife.png'),
(6, 'NAMATAKA', '26/cit', '2005-03-20', '0714172590', 'ursee', '$2y$10$M7oB5HfWIc0e1IhHnz1qE.AwHRIXBopNbn2z1ny5b632wAYeaROwm', 'merdantrust@gmail.com', 'rtrt', 15, 3, 1, ''),
(7, 'NAMATAKA', '26/DMC', '2005-03-20', '0714172590', 'dddan', '$2y$10$kMzW/l1Vys3CsxL9nD.SB.TiQ7.5FpLMwiPH3wPVPn9FYx.KEUCk2', 'merdantrust@gmail.com', 'rtrt', 15, 3, 1, '');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `transaction_id` int(11) NOT NULL,
  `type` enum('income','expense') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `description` text,
  `reference` varchar(100) DEFAULT NULL,
  `transaction_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `category` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`transaction_id`, `type`, `amount`, `description`, `reference`, `transaction_date`, `category`) VALUES
(1, 'expense', '340000.00', 'Staff Payment - GALUSHA', 'PAY-20260629064433-9609', '2026-06-29 09:44:33', 'Staff Payment'),
(2, 'expense', '340000.00', 'Staff Payment - GALUSHA', 'PAY-20260629064450-7552', '2026-06-29 09:44:50', 'Staff Payment'),
(3, 'expense', '340000.00', 'Staff Payment - GALUSHA', 'PAY-20260629064529-9835', '2026-06-29 09:45:30', 'Staff Payment'),
(4, 'expense', '340000.00', 'Staff Payment - GALUSHA', 'PAY-20260629065212-5040', '2026-06-29 09:52:12', 'Staff Payment');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `auto_payment_settings`
--
ALTER TABLE `auto_payment_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `staff_id` (`staff_id`),
  ADD KEY `idx_staff` (`staff_id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`course_id`),
  ADD KEY `dept_id` (`dept_id`);

--
-- Indexes for table `course_units`
--
ALTER TABLE `course_units`
  ADD PRIMARY KEY (`unit_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`dept_id`);

--
-- Indexes for table `directors`
--
ALTER TABLE `directors`
  ADD PRIMARY KEY (`director_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `marks`
--
ALTER TABLE `marks`
  ADD KEY `regno` (`regno`),
  ADD KEY `unit_id` (`unit_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `code_id` (`code_id`),
  ADD KEY `regno` (`regno`);

--
-- Indexes for table `payment_codes`
--
ALTER TABLE `payment_codes`
  ADD PRIMARY KEY (`code_id`),
  ADD KEY `regno` (`regno`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`staff_id`),
  ADD UNIQUE KEY `idx_staff_id` (`staff_id`),
  ADD UNIQUE KEY `idx_username` (`username`),
  ADD UNIQUE KEY `idx_email` (`email`);

--
-- Indexes for table `staff_payments`
--
ALTER TABLE `staff_payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `idx_staff` (`staff_id`),
  ADD KEY `idx_date` (`payment_date`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`),
  ADD UNIQUE KEY `regno` (`regno`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `idx_type` (`type`),
  ADD KEY `idx_date` (`transaction_date`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `auto_payment_settings`
--
ALTER TABLE `auto_payment_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `course_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;
--
-- AUTO_INCREMENT for table `course_units`
--
ALTER TABLE `course_units`
  MODIFY `unit_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;
--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `dept_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
--
-- AUTO_INCREMENT for table `directors`
--
ALTER TABLE `directors`
  MODIFY `director_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT for table `payment_codes`
--
ALTER TABLE `payment_codes`
  MODIFY `code_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `staff_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT for table `staff_payments`
--
ALTER TABLE `staff_payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`dept_id`) REFERENCES `departments` (`dept_id`);

--
-- Constraints for table `course_units`
--
ALTER TABLE `course_units`
  ADD CONSTRAINT `course_units_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`);

--
-- Constraints for table `marks`
--
ALTER TABLE `marks`
  ADD CONSTRAINT `marks_ibfk_1` FOREIGN KEY (`regno`) REFERENCES `students` (`regno`),
  ADD CONSTRAINT `marks_ibfk_2` FOREIGN KEY (`unit_id`) REFERENCES `course_units` (`unit_id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`code_id`) REFERENCES `payment_codes` (`code_id`),
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`regno`) REFERENCES `students` (`regno`);

--
-- Constraints for table `payment_codes`
--
ALTER TABLE `payment_codes`
  ADD CONSTRAINT `payment_codes_ibfk_1` FOREIGN KEY (`regno`) REFERENCES `students` (`regno`);

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`),
  ADD CONSTRAINT `students_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `departments` (`dept_id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
