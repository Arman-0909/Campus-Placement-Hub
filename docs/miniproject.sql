-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 15, 2025 at 07:38 PM
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
-- Database: `miniproject`
--

-- --------------------------------------------------------

--
-- Table structure for table `applications`
--

CREATE TABLE `applications` (
  `application_id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `student_regdno` varchar(11) NOT NULL,
  `application_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(50) NOT NULL DEFAULT 'Applied'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `applications`
--

INSERT INTO `applications` (`application_id`, `job_id`, `student_regdno`, `application_date`, `status`) VALUES
(1, 1, '230102', '2025-10-15 17:04:05', 'Applied'),
(2, 1, '230108', '2025-10-15 17:04:05', 'Shortlisted'),
(3, 1, '230111', '2025-10-15 17:04:05', 'Applied'),
(4, 1, '230113', '2025-10-15 17:04:05', 'Interviewing'),
(5, 2, '230102', '2025-10-15 17:04:05', 'Applied'),
(6, 2, '230108', '2025-10-15 17:04:05', 'Applied'),
(7, 2, '230501', '2025-10-15 17:04:05', 'Rejected'),
(8, 2, '230113', '2025-10-15 17:04:05', 'Shortlisted'),
(9, 3, '230101', '2025-10-15 17:04:05', 'Applied'),
(10, 3, '230105', '2025-10-15 17:04:05', 'Applied'),
(11, 3, '230201', '2025-10-15 17:04:05', 'Applied'),
(12, 3, '230401', '2025-10-15 17:04:05', 'Applied'),
(13, 4, '230102', '2025-10-15 17:04:05', 'Shortlisted'),
(14, 4, '230108', '2025-10-15 17:04:05', 'Applied'),
(15, 5, '230103', '2025-10-15 17:04:05', 'Applied'),
(16, 5, '230106', '2025-10-15 17:04:05', 'Applied'),
(17, 8, '230105', '2025-10-15 17:04:05', 'Applied'),
(18, 8, '230203', '2025-10-15 17:04:05', 'Applied'),
(19, 9, '230101', '2025-10-15 17:04:05', 'Interviewing'),
(20, 9, '230111', '2025-10-15 17:04:05', 'Applied'),
(21, 10, '230103', '2025-10-15 17:04:05', 'Applied'),
(22, 8, '2334869', '2025-10-15 17:36:13', 'Shortlisted');

-- --------------------------------------------------------

--
-- Table structure for table `company`
--

CREATE TABLE `company` (
  `company_id` int(11) NOT NULL,
  `companyname` varchar(100) NOT NULL,
  `website` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `company`
--

INSERT INTO `company` (`company_id`, `companyname`, `website`, `description`, `created_at`) VALUES
(1, 'Amazon', 'https://amazon.jobs', 'Global e-commerce and cloud computing giant.', '2025-10-15 17:04:05'),
(2, 'Google', 'https://careers.google.com', 'Leading in search, advertising, and OS.', '2025-10-15 17:04:05'),
(3, 'Microsoft', 'https://careers.microsoft.com', 'Software, services, and devices.', '2025-10-15 17:04:05'),
(4, 'Infosys', 'https://www.infosys.com/careers', 'Global leader in next-generation digital services.', '2025-10-15 17:04:05'),
(5, 'TCS', 'https://www.tcs.com/careers', 'IT services, consulting and business solutions.', '2025-10-15 17:04:05'),
(6, 'Wipro', 'https://careers.wipro.com', 'Leading technology services and consulting company.', '2025-10-15 17:04:05'),
(7, 'Tech Mahindra', 'https://careers.techmahindra.com', 'Representing the connected world.', '2025-10-15 17:04:05'),
(8, 'HCL Tech', 'https://www.hcltech.com/careers', 'Technology company for the next decade.', '2025-10-15 17:04:05'),
(9, 'Accenture', 'https://www.accenture.com/in-en/careers', 'Global professional services company.', '2025-10-15 17:04:05'),
(10, 'Capgemini', 'https://www.capgemini.com/careers', 'Consulting, technology services, and digital transformation.', '2025-10-15 17:04:05'),
(11, 'Cognizant', 'https://careers.cognizant.com', 'Engineering modern businesses.', '2025-10-15 17:04:05'),
(12, 'Mindtree', 'https://www.mindtree.com/careers', 'Global technology consulting and services company.', '2025-10-15 17:04:05'),
(13, 'L&T Infotech', 'https://www.lntinfotech.com/careers', 'Global technology consulting and digital solutions company.', '2025-10-15 17:04:05'),
(14, 'Paytm', 'https://paytm.com/careers', 'India’s leading digital payments platform.', '2025-10-15 17:04:05'),
(15, 'Zomato', 'https://www.zomato.com/careers', 'Online food delivery and restaurant discovery platform.', '2025-10-15 17:04:05'),
(16, 'Freshworks', 'https://www.freshworks.com/company/careers', 'Customer engagement software for businesses.', '2025-10-15 17:04:05');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `job_id` int(11) NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `job_title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `package_lpa` decimal(5,2) DEFAULT NULL,
  `required_cgpa` decimal(4,2) NOT NULL DEFAULT 6.00,
  `max_backlogs` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`job_id`, `company_name`, `job_title`, `description`, `package_lpa`, `required_cgpa`, `max_backlogs`, `created_at`) VALUES
(1, 'Google', 'Software Engineer', 'Design, develop, test, deploy, maintain and improve software.', 25.00, 8.50, 0, '2025-10-15 17:04:05'),
(2, 'Microsoft', 'Software Engineer', 'Build software to empower every person on the planet.', 21.00, 8.00, 0, '2025-10-15 17:04:05'),
(3, 'Amazon', 'SDE', 'Create products and services used by millions.', 18.00, 7.50, 0, '2025-10-15 17:04:05'),
(4, 'Infosys', 'Specialist Programmer', 'Develop and validate complex software.', 9.50, 8.00, 0, '2025-10-15 17:04:05'),
(5, 'TCS', 'Digital Trainee', 'Work on client-facing digital projects.', 7.00, 7.00, 1, '2025-10-15 17:04:05'),
(6, 'Wipro', 'Project Engineer', 'Join a team of engineers to work on a variety of projects.', 4.50, 6.50, 2, '2025-10-15 17:04:05'),
(7, 'Tech Mahindra', 'Associate Software Engineer', 'A role for fresh graduates to kick-start their IT career.', 4.00, 6.00, 2, '2025-10-15 17:04:05'),
(8, 'Accenture', 'App Development Associate', 'Design, build, test, and configure applications.', 6.50, 6.50, 1, '2025-10-15 17:04:05'),
(9, 'Paytm', 'Graduate Engineer Trainee', 'Build India’s largest payments platform.', 12.00, 7.50, 0, '2025-10-15 17:04:05'),
(10, 'Zomato', 'Backend Engineer', 'Develop and maintain backend services.', 15.00, 7.00, 0, '2025-10-15 17:04:05');

-- --------------------------------------------------------

--
-- Table structure for table `marks`
--

CREATE TABLE `marks` (
  `regdno` varchar(11) NOT NULL,
  `cgpa` float(10,2) DEFAULT 0.00,
  `backlogs` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `marks`
--

INSERT INTO `marks` (`regdno`, `cgpa`, `backlogs`) VALUES
('230101', 8.50, 0),
('230102', 9.10, 0),
('230103', 7.80, 1),
('230104', 8.20, 0),
('230105', 8.90, 0),
('230106', 7.20, 0),
('230107', 6.50, 3),
('230108', 9.50, 0),
('230110', 9.10, 0),
('230111', 9.30, 0),
('230113', 9.60, 0),
('230121', 8.10, 0),
('230122', 9.20, 0),
('230123', 6.50, 3),
('230124', 8.80, 0),
('230125', 7.80, 0),
('230126', 9.50, 0),
('230127', 8.20, 0),
('230128', 7.30, 1),
('230129', 9.00, 0),
('230130', 8.40, 0),
('230131', 6.80, 2),
('230132', 8.70, 0),
('230133', 8.30, 0),
('230134', 6.70, 1),
('230135', 9.00, 0),
('230136', 8.20, 0),
('230137', 7.40, 0),
('230138', 9.50, 0),
('230201', 8.20, 0),
('230202', 7.50, 1),
('230203', 9.00, 0),
('230210', 7.90, 1),
('230211', 7.60, 1),
('230212', 6.90, 2),
('230213', 7.00, 0),
('230214', 7.50, 0),
('230215', 8.90, 0),
('230216', 7.80, 0),
('230301', 8.10, 0),
('230302', 7.30, 2),
('230308', 6.80, 2),
('230309', 8.30, 0),
('230310', 8.50, 0),
('230311', 8.90, 0),
('230312', 9.20, 0),
('230313', 7.60, 0),
('230314', 8.60, 1),
('230401', 8.80, 0),
('230402', 7.00, 0),
('230405', 9.00, 0),
('230406', 7.10, 0),
('230407', 7.70, 0),
('230408', 7.20, 1),
('230409', 8.00, 1),
('230410', 9.40, 0),
('230411', 9.10, 0),
('230501', 9.20, 0),
('230505', 8.70, 0),
('230506', 9.40, 0),
('230507', 9.10, 0),
('230508', 9.30, 0),
('230509', 7.40, 0),
('230510', 8.50, 0),
('230511', 7.90, 0),
('230601', 8.40, 0),
('230605', 7.50, 0),
('230606', 8.00, 1),
('230607', 8.60, 0),
('230608', 8.10, 0),
('230609', 9.60, 0),
('230610', 7.10, 2),
('230611', 8.80, 0),
('2334869', 7.22, 0);

-- --------------------------------------------------------

--
-- Table structure for table `package`
--

CREATE TABLE `package` (
  `regdno` varchar(11) NOT NULL,
  `id` int(11) NOT NULL,
  `companyname` varchar(30) NOT NULL,
  `package` float NOT NULL,
  `file` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `placements`
--

CREATE TABLE `placements` (
  `placement_id` int(11) NOT NULL,
  `student_regdno` varchar(11) NOT NULL,
  `job_id` int(11) DEFAULT NULL,
  `placement_date` datetime NOT NULL DEFAULT current_timestamp(),
  `package_lpa` decimal(5,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `placements`
--

INSERT INTO `placements` (`placement_id`, `student_regdno`, `job_id`, `placement_date`, `package_lpa`, `created_at`) VALUES
(1, '230101', 9, '2025-10-15', 12.00, '2025-10-15 17:04:05'),
(2, '230102', 1, '2025-10-15', 25.00, '2025-10-15 17:04:05'),
(3, '230105', 8, '2025-10-15', 10.00, '2025-10-15 17:04:05'),
(4, '230108', 2, '2025-10-15', 21.00, '2025-10-15 17:04:05'),
(5, '230201', 3, '2025-10-15', 18.00, '2025-10-15 17:04:05'),
(6, '230301', 6, '2025-10-15', 4.50, '2025-10-15 17:04:05'),
(7, '230401', 5, '2025-10-15', 7.00, '2025-10-15 17:04:05'),
(8, '230501', 7, '2025-10-15', 4.00, '2025-10-15 17:04:05'),
(9, '230601', 10, '2025-10-15', 15.00, '2025-10-15 17:04:05'),
(10, '230110', 4, '2025-10-15', 9.50, '2025-10-15 17:04:05'),
(11, '230113', 2, '2025-10-15', 21.00, '2025-10-15 17:04:05');

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `role` varchar(20) NOT NULL,
  `staff_name` varchar(40) NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`role`, `staff_name`, `password`) VALUES
('Admin', 'Admin', '$2y$10$qNC2TCwSVeMeltdcT1/XP.EXYvkAFnTR0bCgUdylWuNWD23hRTgL6');

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

CREATE TABLE `student` (
  `regdno` varchar(11) NOT NULL,
  `name` varchar(40) NOT NULL,
  `department` varchar(50) DEFAULT NULL,
  `email` varchar(40) NOT NULL,
  `contact` bigint(11) NOT NULL,
  `dob` date NOT NULL,
  `password` varchar(255) NOT NULL,
  `placement_status` varchar(50) NOT NULL DEFAULT 'Not Placed',
  `last_updated_by` varchar(255) DEFAULT NULL,
  `last_updated_on` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student`
--

INSERT INTO `student` (`regdno`, `name`, `department`, `email`, `contact`, `dob`, `password`, `placement_status`, `last_updated_by`, `last_updated_on`) VALUES
('230101', 'Aarav Sharma', 'CSE', 'aarav.sharma@example.com', 9876543210, '2003-05-15', '$2y$10$WyYXDxT1iwgCBL2Tt.gp0ewew2eYR/0McJDobkh9VkjnjvN.RhVf6', 'Placed', NULL, NULL),
('230102', 'Aditi Gupta', 'CSE', 'aditi.gupta@example.com', 9876543211, '2003-08-22', '$2y$10$3zQQ9/1634LaM7x9ebakzePat.oCgxkXFgF192aXEo1CHFVLjXHgy', 'Placed', NULL, NULL),
('230103', 'Rohan Verma', 'CSE', 'rohan.verma@example.com', 9876543212, '2003-01-10', '$2y$10$kCrPVG3ELNsdKkuvhOsj8OZ4M.J16uOHDmYOevtHdUIuv38NL/Sbi', 'Not Placed', NULL, NULL),
('230104', 'Priya Singh', 'CSE', 'priya.singh@example.com', 9876543213, '2003-11-30', '$2y$10$9/DJpWBM5gl1mdrL8yMBfOgng3vzoGRd77fel5yJJh.FDWGUkpf/u', 'Seeking Better Offer', NULL, NULL),
('230105', 'Vikram Rathore', 'CSE', 'vikram.rathore@example.com', 9876543214, '2003-04-05', '$2y$10$v8rkYhsZVTjo3gq42TRq3u1VqY2DwNFFiyW67kd1u2ed6W15yIGdK', 'Placed', NULL, NULL),
('230106', 'Sneha Reddy', 'CSE', 'sneha.reddy@example.com', 9876543215, '2003-09-18', '$2y$10$xYUCMx5M4qs/rySJAE7xT.uXYADmnugwW1IURm8TbeRj./gLQ4HIq', 'Not Placed', NULL, NULL),
('230107', 'Arjun Kumar', 'CSE', 'arjun.kumar@example.com', 9876543216, '2003-02-25', '$2y$10$6n4Ek59aCVqYwqRtrsVW6.b1oX24zyncK/SUyDuOAbR6NEnOmsYta', 'Not Placed', NULL, NULL),
('230108', 'Ishaan Patel', 'CSE', 'ishaan.patel@example.com', 9876543217, '2003-07-12', '$2y$10$4H710U1yzammwQtbvBQ8R.vfAhGdaXhYWfDIRjGeL4osO0FxfCina', 'Placed', NULL, NULL),
('230110', 'Kabir Das', 'CSE', 'kabir.d@example.com', 9871112221, '2003-02-02', '$2y$10$kbCmar5H2L7wjHPsWuXT0eCtm1hanee97BCKz4SO/E45STqtY7Bca', 'Placed', NULL, NULL),
('230111', 'Zara Ali', 'CSE', 'zara.a@example.com', 9871112222, '2003-03-03', '$2y$10$P3DYGMd9AyXC0wQ/cpGb0eVwALjyaPQBM1RUtmf7pjC1syBbuHpIy', 'Not Placed', NULL, NULL),
('230113', 'Shanaya Patel', 'CSE', 'shanaya.p@example.com', 9871112229, '2003-10-10', '$2y$10$MXJAdzN6RMV1zyjdfsInC.1z0oygB9pVVWFqJOD/VXrvu.55/QofS', 'Placed', NULL, NULL),
('230121', 'Vihaan Singh', 'CSE', 'vihaan.s@example.com', 9871113301, '2003-03-01', '$2y$10$wYLkJzRKPkpTUpNd6KsmbuJCaNU.92nfEm01HbfScgqV6PrZ1M432', 'Not Placed', NULL, NULL),
('230122', 'Advika Iyer', 'CSE', 'advika.i@example.com', 9871113307, '2003-09-07', '$2y$10$PJAymno2bbN6PDvMXTHwZ.O2nzUAYIFDUggm.skwHCAs9T0UdEHWa', 'Not Placed', NULL, NULL),
('230123', 'Kabir Shah', 'CSE', 'kabir.shah@example.com', 9871113308, '2003-10-08', '$2y$10$Bj4m9cZ/izCzQxeQaV4Xh.2o0OjDX8x3YEV9CrW/8VURemzFQvyVO', 'Not Placed', NULL, NULL),
('230124', 'Anvi Gupta', 'CSE', 'anvi.g@example.com', 9871113309, '2003-11-09', '$2y$10$Cw0m2Xkd5haZEAlZo1cJG.WCTRsagT42l0DMJ.Ef.f0/dVrliWawS', 'Not Placed', NULL, NULL),
('230125', 'Ira Reddy', 'CSE', 'ira.r@example.com', 9871113315, '2003-05-15', '$2y$10$djwZEcaEYND1xNNKNMMh2.Mlgu2lvYYR.e6EQ0EquJOA3bjXt2iIG', 'Not Placed', NULL, NULL),
('230126', 'Neel Menon', 'CSE', 'neel.m@example.com', 9871113316, '2003-06-16', '$2y$10$qWyxAeHoUEo7WcbLdid5z.7CTpQGQK4xZ2VPZOPVwz9J9JeYoy4uS', 'Not Placed', NULL, NULL),
('230127', 'Pari Sharma', 'CSE', 'pari.s@example.com', 9871113317, '2003-07-17', '$2y$10$YnU0pyOx6WuFrdI79PAN2erhF0g60iH2cvc8ZrIYe3GP9ubyVsvgC', 'Not Placed', NULL, NULL),
('230128', 'Zara Ahmed', 'CSE', 'zara.ahmed@example.com', 9871113323, '2003-01-23', '$2y$10$haJ3/TsEKuk14BnhlpQhLeiWE95q0.nqVmoqTHoNjfWRGtbLfjy4S', 'Not Placed', NULL, NULL),
('230129', 'Aarav Mishra', 'CSE', 'aarav.m@example.com', 9871113324, '2003-02-24', '$2y$10$FaTVpaIq5Mup1fH4/cBZ8eQd.npoV5rdbF2rc3or7u2PQ35XUnsVa', 'Not Placed', NULL, NULL),
('230130', 'Anaya Singh', 'CSE', 'anaya.s@example.com', 9871113325, '2003-03-25', '$2y$10$kL1ZKj2e.sb/DzLCvO9lNOexF1SVZV6X4kx5xkDXHB2ORc0ecYpuW', 'Not Placed', NULL, NULL),
('230131', 'Mira Choudhury', 'CSE', 'mira.c@example.com', 9871113331, '2003-09-01', '$2y$10$ZZSe5qoD.IDptQFVA7rt5uS6SS2/hMTRkVkbHqOgNCOFqpDZQ2wxC', 'Not Placed', NULL, NULL),
('230132', 'Vivaan Kumar', 'CSE', 'vivaan.k@example.com', 9871113332, '2003-10-02', '$2y$10$werQwlRokI2qI1ivbznLme.AIAESUpEhZuxdZDL5OSgU8m9slWFP2', 'Not Placed', NULL, NULL),
('230133', 'Ishaan Sharma', 'CSE', 'ishaan.sharma@example.com', 9871113338, '2003-04-08', '$2y$10$I0Spm2JdLeP22P1KhcSP4OARcPfc2RtLU0zqg8/Os/J5w2UZK8z9q', 'Not Placed', NULL, NULL),
('230134', 'Aditi Verma', 'CSE', 'aditi.verma@example.com', 9871113339, '2003-05-09', '$2y$10$e0NktFdluxyEv0Dce2kJ7.BV.fGFuT4oZ0SJlR0ICTgrKsehfI3EG', 'Not Placed', NULL, NULL),
('230135', 'Priya Gupta', 'CSE', 'priya.gupta@example.com', 9871113345, '2003-11-15', '$2y$10$UGYuYfH/S3lkKtAgIREnKuta6laZhto9PK12z6Dl0kH36xTgPAeFi', 'Not Placed', NULL, NULL),
('230136', 'Rohan Kumar', 'CSE', 'rohan.kumar@example.com', 9871113346, '2003-12-16', '$2y$10$EToi/hClhZg5rPP9KNfjnuRMVf/HzVuRKY6b1CV2tscLXb..YWIw2', 'Not Placed', NULL, NULL),
('230137', 'Kabir Verma', 'CSE', 'kabir.v@example.com', 9871113352, '2003-06-22', '$2y$10$OSxB.N6ATWhpjsyXc7obn.DghnwtF/uppotpViz3N1RLmfeVR0Rrm', 'Not Placed', NULL, NULL),
('230138', 'Myra Patel', 'CSE', 'myra.p@example.com', 9871113353, '2003-07-23', '$2y$10$5m2AwyOxe6w36uuAfrwy4e9JswvCih.YoLVu3RF/yHfhdNP93uHka', 'Not Placed', NULL, NULL),
('230201', 'Neha Sharma', 'ECE', 'neha.sharma@example.com', 9876543220, '2003-06-20', '$2y$10$Ece9NlaOVXl5HRN1dW9UheM8NSA863azcx0oeisIGhFXTRKui9cOu', 'Placed', NULL, NULL),
('230202', 'Rajesh Singh', 'ECE', 'rajesh.singh@example.com', 9876543221, '2003-10-01', '$2y$10$8asayZ0waRnUhDFYgYMB3utIxmJ6wr.uP7lwl30tiHWNxBb0xIs5y', 'Not Placed', NULL, NULL),
('230203', 'Anjali Menon', 'ECE', 'anjali.menon@example.com', 9876543222, '2003-03-14', '$2y$10$494EkQX01CWQBAcq0z464uz1oquz2s2R2nO9GPwaJxPhy.pIowQgS', 'Not Placed', NULL, NULL),
('230210', 'Ishika Patel', 'ECE', 'ishika.p@example.com', 9871113302, '2003-04-02', '$2y$10$Ac/5nHAC/MBSurTgZvzDwed8Z0UP5jWKNFxo5392ZCFGKzdZJjgse', 'Not Placed', NULL, NULL),
('230211', 'Rudra Patel', 'ECE', 'rudra.p@example.com', 9871113310, '2003-12-10', '$2y$10$31PsSeaI5lzvKN1qjQ1Ksu6bhnLQjKKJ1PPduBcIgciWeDcZi28ZS', 'Not Placed', NULL, NULL),
('230212', 'Shaurya Singh', 'ECE', 'shaurya.s@example.com', 9871113318, '2003-08-18', '$2y$10$qJAP2sBo5.726iHryNl8cuZEi2Qffun2k.cmhK8hqnToQVE22fYKa', 'Not Placed', NULL, NULL),
('230213', 'Arjun Verma', 'ECE', 'arjun.verma@example.com', 9871113326, '2003-04-26', '$2y$10$azcaKUnAt77DiZVZ3hjibe1FA/d/5X5EfcRwtVTtjgMxK7JW22H.e', 'Not Placed', NULL, NULL),
('230214', 'Nisha Sharma', 'ECE', 'nisha.s@example.com', 9871113333, '2003-11-03', '$2y$10$7BHuPevg/fbHkMvN7mOcMuQfiVuHXBGD6eiYs7VCbI5I..UPqziB.', 'Not Placed', NULL, NULL),
('230215', 'Kabir Reddy', 'ECE', 'kabir.reddy@example.com', 9871113340, '2003-06-10', '$2y$10$0b4WweHCfj/yRshBVIYJo.ysUlLb.pAEfN6xmTXXplfRSJN4W23/a', 'Not Placed', NULL, NULL),
('230216', 'Sneha Menon', 'ECE', 'sneha.menon@example.com', 9871113347, '2003-01-17', '$2y$10$uko36M/.jjR/3/VE3QASyO0KKBW8L8GojI6BzcP1svgfgA.O9Tuc2', 'Not Placed', NULL, NULL),
('230301', 'Amit Patel', 'ME', 'amit.patel@example.com', 9876543230, '2003-04-19', '$2y$10$3wvJGbds4Xvabx5RWg5Y0.rk7ifymK7wIN0mXGc1PsmHkvvqcIfHO', 'Placed', NULL, NULL),
('230302', 'Pooja Desai', 'ME', 'pooja.desai@example.com', 9876543231, '2003-09-03', '$2y$10$wTbv2IC1nhgH3Ijefj58JuyqrfPEPa.2YsSs3LwHlALXCi4Vd2JVa', 'Not Placed', NULL, NULL),
('230308', 'Arjun Reddy', 'ME', 'arjun.r@example.com', 9871113303, '2003-05-03', '$2y$10$Na3qTlfpeJcqzf4qnF2NDOlPTt1L.UZaqEkTnYrNUMvLhnJjpyW4W', 'Not Placed', NULL, NULL),
('230309', 'Aarohi Das', 'ME', 'aarohi.d@example.com', 9871113311, '2003-01-11', '$2y$10$K2y/zrc4CRhuZXemAvXbeeRw/Rnj.bf5.vDV.vsDlrxEO.BpeGSBW', 'Not Placed', NULL, NULL),
('230310', 'Anika Mehta', 'ME', 'anika.mehta@example.com', 9871113319, '2003-09-19', '$2y$10$4xnhYXPuFvKaAHkB/RVJreqocw1WFxybe2qnyqwDYk3rZtr7xwv8y', 'Not Placed', NULL, NULL),
('230311', 'Rhea Kapoor', 'ME', 'rhea.k@example.com', 9871113327, '2003-05-27', '$2y$10$9XketFgKsO1D.b1xwGoT3e5B3w3jevZOhtMc7FM.aFNi7jKesTzCa', 'Not Placed', NULL, NULL),
('230312', 'Rohan Gupta', 'ME', 'rohan.g@example.com', 9871113334, '2003-12-04', '$2y$10$ALGWZTDKDKxELqPYGFzlpeprcisAWS2vzw/gYJsmeVmDttU2GPQWS', 'Not Placed', NULL, NULL),
('230313', 'Ananya Patel', 'ME', 'ananya.patel@example.com', 9871113341, '2003-07-11', '$2y$10$/AYaDt55Ei.cRfb0Za9Kf.a2ahHX8vTOE7XW.gTxpAD6i3y6alAcu', 'Not Placed', NULL, NULL),
('230314', 'Vikram Singh', 'ME', 'vikram.singh@example.com', 9871113348, '2003-02-18', '$2y$10$HPb6yXdCFrO0nJL48cC7lebVJJLNY5TVcla55fNO7fkFRLLXLCTaS', 'Not Placed', NULL, NULL),
('230401', 'Sunita Rao', 'CE', 'sunita.rao@example.com', 9876543240, '2003-08-11', '$2y$10$VSjamVT72q.VsJWTo8lPD.mDuQXCvqdBgMgquD64Owlc4dUr0H9TW', 'Placed', NULL, NULL),
('230402', 'Manish Reddy', 'CE', 'manish.reddy@example.com', 9876543241, '2003-11-05', '$2y$10$Ue9.HBTt1b25bShXBkpR1OUfnyS7tAPW/GS3Jv3y93UgqkRm1u35i', 'Not Placed', NULL, NULL),
('230405', 'Kyra Kumar', 'CE', 'kyra.k@example.com', 9871113304, '2003-06-04', '$2y$10$iPdtQx/9tIBYIUlxfNmmj.C7dR.j0l5.3DEmmy4PKavetFT/rzP2G', 'Not Placed', NULL, NULL),
('230406', 'Veer Singh', 'CE', 'veer.s@example.com', 9871113312, '2003-02-12', '$2y$10$AWzKR3zU4iyTclbb2O90l.7NJ6K6PNo3trRvlv6rbnRuMtO7C.HQW', 'Not Placed', NULL, NULL),
('230407', 'Yuvan Gupta', 'CE', 'yuvan.g@example.com', 9871113320, '2003-10-20', '$2y$10$YNDDV34DeWk/9T4vRobbcekjryPzv22coYLKw5xwBD3.u5yp7dWLi', 'Not Placed', NULL, NULL),
('230408', 'Kian Patel', 'CE', 'kian.p@example.com', 9871113328, '2003-06-28', '$2y$10$LCOX3pGZgaiXKRwH2Zez1.GQdWBhWcM//2bwi1P10qMK3O7HOGegy', 'Not Placed', NULL, NULL),
('230409', 'Sia Singh', 'CE', 'sia.s@example.com', 9871113335, '2003-01-05', '$2y$10$SE7bAGG6f6XzK0qXi9chjuu83wX.PjWHwSHn0rtKCQbWoOXrDegJ.', 'Not Placed', NULL, NULL),
('230410', 'Rudra Singh', 'CE', 'rudra.singh@example.com', 9871113342, '2003-08-12', '$2y$10$014MzgpcMGCpNviQHoL50e/hg4Ys.WBAZ/s.rOIZVXJ4Txj0SIEXW', 'Not Placed', NULL, NULL),
('230411', 'Aditi Rao', 'CE', 'aditi.rao@example.com', 9871113349, '2003-03-19', '$2y$10$PoKLjuQMTZF2YTG61RnprOsCcO.mhvdyocn1PEIK2lmbro2DX9e.G', 'Not Placed', NULL, NULL),
('230501', 'Deepak Joshi', 'EEE', 'deepak.joshi@example.com', 9876543250, '2003-02-17', '$2y$10$f.a2wpdVKCgnL7QglPnSAOP1xmJPafQoFAWh9aggjb4/QciIpOR/i', 'Placed', NULL, NULL),
('230505', 'Zoya Khan', 'EEE', 'zoya.k@example.com', 9871113305, '2003-07-05', '$2y$10$MV5vURbkT2BGpBUlVF6rau4ib5KRaqbf5yxADeFeQclXktygfTsZG', 'Not Placed', NULL, NULL),
('230506', 'Saanvi Verma', 'EEE', 'saanvi.v@example.com', 9871113313, '2003-03-13', '$2y$10$d7Cu4fwUj1OYBFTAXTGmAuJYeyYbW7c5aJrYMOWSPfkncRVzX0WQe', 'Not Placed', NULL, NULL),
('230507', 'Aisha Khan', 'EEE', 'aisha.k@example.com', 9871113321, '2003-11-21', '$2y$10$KywgFI0OBRXCm2h1ig.Jp.N2KgodikyJqEiPvil8ZpZ8byW164ppi', 'Not Placed', NULL, NULL),
('230508', 'Eva Reddy', 'EEE', 'eva.r@example.com', 9871113329, '2003-07-29', '$2y$10$TXTC6SYk2ETLcLxE2/LSFOpg2LqdOc3w8EAoMLcnN6lpfXd0fvfcG', 'Not Placed', NULL, NULL),
('230509', 'Ayaan Ali', 'EEE', 'ayaan.ali@example.com', 9871113336, '2003-02-06', '$2y$10$XP9Y3kMxMgFJwCzAq1FN4uNoEt6hEQv574VniwhZcVl7ZpdOJfBcG', 'Not Placed', NULL, NULL),
('230510', 'Zara Das', 'EEE', 'zara.das@example.com', 9871113343, '2003-09-13', '$2y$10$ExgaPW56pR0bdJ77NY3tLOEYdgWXjvNx3pShbUspeRXmpZujKOqFy', 'Not Placed', NULL, NULL),
('230511', 'Arjun Desai', 'EEE', 'arjun.desai@example.com', 9871113350, '2003-04-20', '$2y$10$j6aNX4pMRd4p28PFWR2tTuy5pY4.bISy55lvAv8i63XXelrNaERV2', 'Not Placed', NULL, NULL),
('230601', 'Riya Sharma', 'BCA', 'riya.sharma@example.com', 9876543260, '2003-10-15', '$2y$10$cSeYUtxR21gHhQHWR5/GL.7ajUyt7oZXOjdcFYl27VkVe5zFhPLT2', 'Placed', NULL, NULL),
('230605', 'Krish Sharma', 'BCA', 'krish.s@example.com', 9871113306, '2003-08-06', '$2y$10$azVHbrnj23saDeWzT9hFyeMs.2Zzj5yaaOL.i6vY.qxMI9InYoMAa', 'Not Placed', NULL, NULL),
('230606', 'Aryan Kumar', 'BCA', 'aryan.kumar@example.com', 9871113314, '2003-04-14', '$2y$10$7WogBEp4s.dumnXDqN9nmeGOokTfIqWVqfrfsLyR1lhRnXt/eLKha', 'Not Placed', NULL, NULL),
('230607', 'Dev Sharma', 'BCA', 'dev.s@example.com', 9871113322, '2003-12-22', '$2y$10$XEyN36.vybLYbvg4M5dHZuASbE2a.mypITXNm7hsYJ3JI2zRcgEzG', 'Not Placed', NULL, NULL),
('230608', 'Leo Das', 'BCA', 'leo.d@example.com', 9871113330, '2003-08-30', '$2y$10$Fe4OZ2D6Ur.b.5yhH7nTHOLtcMdUl/uot6PTwf7/adhQTpy6mCdcq', 'Not Placed', NULL, NULL),
('230609', 'Tara Mehta', 'BCA', 'tara.m@example.com', 9871113337, '2003-03-07', '$2y$10$Q3Ho9/a89OAVI1d2u/XS2OkxyhBpB8HWFQEAMDsHqLOp46mJNnPNS', 'Not Placed', NULL, NULL),
('230610', 'Aryan Shah', 'BCA', 'aryan.shah@example.com', 9871113344, '2003-10-14', '$2y$10$5R7pJFOTHzw0jwDHkld0J.qBZ.Z56nc6BLv.057mkFP0IK7vyl4QS', 'Not Placed', NULL, NULL),
('230611', 'Isha Joshi', 'BCA', 'isha.joshi@example.com', 9871113351, '2003-05-21', '$2y$10$PP8nGIkJ63eBJ/zCje7Gw.EPSOvnElwgM.CG/07q.1KWBMRF8b/Ui', 'Not Placed', NULL, NULL),
('2334869', 'Armandeep', 'BCA', 'arman@example.com', 7009833561, '2005-11-26', '$2y$10$LwpKol6eV62Xc7Zr5UhtDOvbKDrupyVGiixdXm9ynYcGTgMbVQ2iO', 'Placed', 'Admin', '2025-10-15 23:05:31');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`application_id`),
  ADD UNIQUE KEY `one_application_per_student_per_job` (`job_id`,`student_regdno`),
  ADD KEY `student_regdno` (`student_regdno`);

--
-- Indexes for table `company`
--
ALTER TABLE `company`
  ADD PRIMARY KEY (`company_id`),
  ADD UNIQUE KEY `companyname` (`companyname`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`job_id`),
  ADD KEY `company_name` (`company_name`);

--
-- Indexes for table `marks`
--
ALTER TABLE `marks`
  ADD PRIMARY KEY (`regdno`),
  ADD KEY `regdno` (`regdno`);

--
-- Indexes for table `package`
--
ALTER TABLE `package`
  ADD PRIMARY KEY (`id`),
  ADD KEY `regdno` (`regdno`);

--
-- Indexes for table `placements`
--
ALTER TABLE `placements`
  ADD PRIMARY KEY (`placement_id`),
  ADD UNIQUE KEY `student_is_placed_once` (`student_regdno`),
  ADD KEY `job_id` (`job_id`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`role`);

--
-- Indexes for table `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`regdno`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `applications`
--
ALTER TABLE `applications`
  MODIFY `application_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `company`
--
ALTER TABLE `company`
  MODIFY `company_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `job_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `package`
--
ALTER TABLE `package`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=987669;

--
-- AUTO_INCREMENT for table `placements`
--
ALTER TABLE `placements`
  MODIFY `placement_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `applications`
--
ALTER TABLE `applications`
  ADD CONSTRAINT `applications_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`job_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `applications_ibfk_2` FOREIGN KEY (`student_regdno`) REFERENCES `student` (`regdno`) ON DELETE CASCADE;

--
-- Constraints for table `jobs`
--
ALTER TABLE `jobs`
  ADD CONSTRAINT `jobs_ibfk_1` FOREIGN KEY (`company_name`) REFERENCES `company` (`companyname`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `marks`
--
ALTER TABLE `marks`
  ADD CONSTRAINT `marks` FOREIGN KEY (`regdno`) REFERENCES `student` (`regdno`);

--
-- Constraints for table `package`
--
ALTER TABLE `package`
  ADD CONSTRAINT `regdno` FOREIGN KEY (`regdno`) REFERENCES `student` (`regdno`);

--
-- Constraints for table `placements`
--
ALTER TABLE `placements`
  ADD CONSTRAINT `placements_ibfk_1` FOREIGN KEY (`student_regdno`) REFERENCES `student` (`regdno`) ON DELETE CASCADE,
  ADD CONSTRAINT `placements_ibfk_2` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`job_id`) ON DELETE SET NULL;

-- --------------------------------------------------------

--
-- Table structure for table `notifications` (student notifications)
--

CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_regdno` varchar(11) NOT NULL,
  `type` varchar(50) NOT NULL DEFAULT 'general',
  `title` varchar(255) NOT NULL,
  `message` text DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `student_regdno` (`student_regdno`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`student_regdno`) REFERENCES `student` (`regdno`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin_notifications`
--

CREATE TABLE IF NOT EXISTS `admin_notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(50) NOT NULL DEFAULT 'general',
  `title` varchar(255) NOT NULL,
  `message` text DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
