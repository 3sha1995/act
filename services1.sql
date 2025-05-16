-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 15, 2025 at 02:05 PM
-- Server version: 11.4.5-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `services`
--

-- --------------------------------------------------------

--
-- Table structure for table `about`
--

CREATE TABLE `about` (
  `id` int(11) NOT NULL,
  `section` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL DEFAULT 'Health Services',
  `description` text DEFAULT NULL,
  `image_type` varchar(10) DEFAULT 'file',
  `image_path` varchar(255) DEFAULT NULL,
  `is_visible` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `about`
--

INSERT INTO `about` (`id`, `section`, `title`, `description`, `image_type`, `image_path`, `is_visible`, `created_at`, `updated_at`) VALUES
(1, 'health', 'Health Services', 'Welcome to WMSU Health Services', 'file', NULL, 1, '2025-05-14 10:52:32', '2025-05-14 10:52:32');

-- --------------------------------------------------------

--
-- Table structure for table `af_page`
--

CREATE TABLE `af_page` (
  `id` int(11) NOT NULL,
  `ontop_title` varchar(255) DEFAULT NULL,
  `main_title` varchar(255) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_visible` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `af_page`
--

INSERT INTO `af_page` (`id`, `ontop_title`, `main_title`, `image_path`, `description`, `is_visible`) VALUES
(1, 'ABOUT US', 'STUDENT AFFAIRS ', 'uploads/682461792df16.jpg', '<div style=\"text-align: center;\"><p data-sourcepos=\"3:1-3:464\"></p><div style=\"text-align: left;\">The Western Mindanao State University (WMSU) Student Affairs Services (SAS) is a dynamic and student-centered office dedicated to fostering a vibrant and supportive university experience for all WMSU students. <span class=\"citation-0 recitation citation-end-0\">Committed to the holistic development of individuals, the SAS provides a comprehensive range of programs and services designed to empower students, enhance their well-being, and facilitate their success both inside and outside the classroom.<source-footnote ng-version=\"0.0.0-PLACEHOLDER\" _nghost-ng-c1316579223=\"\"><sup _ngcontent-ng-c1316579223=\"\" class=\"superscript\" data-turn-source-index=\"1\"></sup></source-footnote></span><sources-carousel-inline ng-version=\"0.0.0-PLACEHOLDER\" _nghost-ng-c2924767884=\"\"><span _ngcontent-ng-c2924767884=\"\" hide-from-message-actions=\"\" class=\"button-container hide-from-message-actions ng-star-inserted\">&nbsp; &nbsp;</span></sources-carousel-inline></div></div>', 1);

-- --------------------------------------------------------

--
-- Table structure for table `af_pagec_info_header`
--

CREATE TABLE `af_pagec_info_header` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `visibility` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `af_pagec_info_header`
--

INSERT INTO `af_pagec_info_header` (`id`, `title`, `description`, `visibility`) VALUES
(1, 'Student Affairsvcvxcddc', 'Welcome to Student Affairs', 1);

-- --------------------------------------------------------

--
-- Table structure for table `af_page_activities`
--

CREATE TABLE `af_page_activities` (
  `id` int(11) NOT NULL,
  `section_title` varchar(255) NOT NULL DEFAULT 'ACTIVITIES CALENDAR',
  `event_title` varchar(255) NOT NULL,
  `event_description` text NOT NULL,
  `event_date` date NOT NULL,
  `event_time` varchar(50) NOT NULL,
  `event_location` varchar(255) NOT NULL,
  `event_image` varchar(255) DEFAULT NULL,
  `is_visible` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `image_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `af_page_activities`
--

INSERT INTO `af_page_activities` (`id`, `section_title`, `event_title`, `event_description`, `event_date`, `event_time`, `event_location`, `event_image`, `is_visible`, `created_at`, `updated_at`, `image_path`) VALUES
(2, 'ACTIVITIES CALENDAR', 'fdfd', 'dfdfdf', '2025-05-06', '04:44', 'fdfdffd', '6824a761025f6.jpg', 1, '2025-05-14 14:23:29', '2025-05-14 14:23:29', NULL),
(5, 'ACTIVITIES CALENDAR', 'nnjnjjnj', 'jnjnjnjnjnj', '2025-05-16', '22:44', 'njnjnj', NULL, 1, '2025-05-14 14:45:25', '2025-05-14 14:45:25', NULL),
(6, 'ACTIVITIES CALENDAR', 'fdfdfd', 'dfdfd', '2025-05-08', '12:00', 'fdfdfdf', NULL, 1, '2025-05-14 15:00:39', '2025-05-14 15:00:39', NULL),
(7, 'ACTIVITIES CALENDAR', 'fffdxcx', 'vcvxcvxcv', '2025-05-31', '23:06', 'dfdfdffds', NULL, 1, '2025-05-14 15:03:40', '2025-05-14 15:21:47', NULL),
(8, 'ACTIVITIES CALENDAR', 'dfdfds', 'dfdfsd', '2025-05-23', '15:06', 'dfdfsdf', NULL, 1, '2025-05-14 15:07:06', '2025-05-14 15:07:06', NULL),
(9, 'ACTIVITIES CALENDAR', 'gfgdfg', 'fgfdg', '2025-05-30', '16:24', 'fgfdg', NULL, 1, '2025-05-14 15:24:16', '2025-05-14 15:24:16', NULL),
(10, 'ACTIVITIES CALENDAR', 'gfdgdf', 'fggdf', '2025-05-15', '23:28', 'fgdfg', NULL, 1, '2025-05-14 15:24:52', '2025-05-14 15:24:52', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `af_page_activities_settings`
--

CREATE TABLE `af_page_activities_settings` (
  `id` int(11) NOT NULL,
  `section_title` varchar(255) NOT NULL,
  `is_visible` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `af_page_activities_settings`
--

INSERT INTO `af_page_activities_settings` (`id`, `section_title`, `is_visible`, `created_at`, `updated_at`) VALUES
(1, 'ACTIVITIES CALENDAR', 1, '2025-05-15 08:13:21', '2025-05-15 08:13:21');

-- --------------------------------------------------------

--
-- Table structure for table `af_page_contact`
--

CREATE TABLE `af_page_contact` (
  `id` int(11) NOT NULL,
  `section_title` varchar(255) NOT NULL DEFAULT 'Get in Touch',
  `contact_type` enum('phone','email','location','facebook') NOT NULL,
  `label` varchar(100) NOT NULL,
  `value` text NOT NULL,
  `display_text` varchar(255) DEFAULT NULL,
  `icon_path` varchar(255) NOT NULL,
  `is_visible` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `af_page_downloadable`
--

CREATE TABLE `af_page_downloadable` (
  `id` int(11) NOT NULL,
  `section_title` varchar(255) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `visibility` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `af_page_facilities`
--

CREATE TABLE `af_page_facilities` (
  `id` int(11) NOT NULL,
  `main_title` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `operating_hours` text DEFAULT NULL,
  `is_visible` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `af_page_facilities`
--

INSERT INTO `af_page_facilities` (`id`, `main_title`, `title`, `description`, `image`, `category`, `operating_hours`, `is_visible`) VALUES
(1, NULL, 'fgfgdf', 'fgdfgfg', 'uploads/facilities/6824bde0a9c7d.png', 'wellness', 'gfdgfdgdfg', 1),
(2, NULL, 'vcvxcvcv', 'vcxvxc', 'uploads/facilities/6824c0ddd4123.jpg', NULL, 'vcxvxc', 1);

-- --------------------------------------------------------

--
-- Table structure for table `af_page_facilities_settings`
--

CREATE TABLE `af_page_facilities_settings` (
  `id` int(11) NOT NULL,
  `section_visible` tinyint(1) DEFAULT 1,
  `main_title` varchar(255) DEFAULT 'OUR FACILITIES'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `af_page_facilities_settings`
--

INSERT INTO `af_page_facilities_settings` (`id`, `section_visible`, `main_title`) VALUES
(1, 1, 'OUR FACILITIES');

-- --------------------------------------------------------

--
-- Table structure for table `af_page_funct`
--

CREATE TABLE `af_page_funct` (
  `id` int(11) NOT NULL,
  `section_title` varchar(255) DEFAULT 'KEY FUNCTIONS',
  `description` text NOT NULL,
  `list` text NOT NULL,
  `is_visible` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `af_page_funct`
--

INSERT INTO `af_page_funct` (`id`, `section_title`, `description`, `list`, `is_visible`, `created_at`, `updated_at`) VALUES
(1, 'KEY FUNCTIONS', '<p data-sourcepos=\"3:1-3:464\" style=\"font-weight: 700; text-align: center;\">The Western Mindanao State University (WMSU) Student Affairs Services (SAS) is a dynamic and student-centered office dedicated to fostering a vibrant and supportive university experience for all WMSU students.&nbsp;<span class=\"citation-0 recitation citation-end-0\">Committed to the holistic development of individuals, the SAS provides a comprehensive range of programs and services designed to empower students, enhance their well-being, and facilitate their success both inside and outside the classroom.<source-footnote ng-version=\"0.0.0-PLACEHOLDER\" _nghost-ng-c1316579223=\"\"><sup _ngcontent-ng-c1316579223=\"\" class=\"superscript\" data-turn-source-index=\"1\"></sup></source-footnote></span><sources-carousel-inline ng-version=\"0.0.0-PLACEHOLDER\" _nghost-ng-c2924767884=\"\"><span _ngcontent-ng-c2924767884=\"\" hide-from-message-actions=\"\" class=\"button-container hide-from-message-actions ng-star-inserted\">&nbsp;&nbsp;</span><sources-carousel _ngcontent-ng-c2924767884=\"\" hide-from-message-actions=\"\" id=\"sources\" _nghost-ng-c2280442857=\"\" class=\"ng-tns-c2280442857-75 hide-from-message-actions ng-star-inserted\" style=\"display: flex; visibility: hidden;\"></sources-carousel></sources-carousel-inline></p><div _ngcontent-ng-c2280442857=\"\" class=\"container ng-tns-c2280442857-75 hide\" jslog=\"220997;BardVeMetadataKey:[null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,[1,null,1]]\" style=\"font-weight: 700; text-align: justify;\"><div _ngcontent-ng-c2280442857=\"\" class=\"carousel-container ng-tns-c2280442857-75\"><div _ngcontent-ng-c2280442857=\"\" class=\"carousel-content ng-tns-c2280442857-75\"><div _ngcontent-ng-c2280442857=\"\" data-test-id=\"sources-carousel-source\" class=\"sources-carousel-source ng-tns-c2280442857-75 hide ng-star-inserted\"></div></div></div></div><p style=\"font-weight: 700; text-align: justify;\"></p><p data-sourcepos=\"5:1-5:462\" style=\"font-weight: 700; text-align: justify;\"></p><ul style=\"font-weight: 700; text-align: justify;\"><li>The Western Mindanao State University (WMSU) Student Affairs Services (SAS) is a dynamic and student-centered office dedicated to fostering a vibrant and supportive university experience for all WMSU students.&nbsp;<span class=\"citation-0 recitation citation-end-0\">Committed to the holistic development of individuals, the SAS provides a comprehensive range of programs and services designed to empower students, enhance their well-being, and facilitate their success both inside and outside the classroom.<source-footnote ng-version=\"0.0.0-PLACEHOLDER\" _nghost-ng-c1316579223=\"\"><sup _ngcontent-ng-c1316579223=\"\" class=\"superscript\" data-turn-source-index=\"1\"></sup></source-footnote></span><sources-carousel-inline ng-version=\"0.0.0-PLACEHOLDER\" _nghost-ng-c2924767884=\"\">&nbsp;&nbsp;</sources-carousel-inline></li><li><sources-carousel-inline ng-version=\"0.0.0-PLACEHOLDER\" _nghost-ng-c2924767884=\"\">The Western Mindanao State University (WMSU) Student Affairs Services (SAS) is a dynamic and student-centered office dedicated to fostering a vibrant and supportive university experience for all WMSU students.&nbsp;<span class=\"citation-0 recitation citation-end-0\">Committed to the holistic development of individuals, the SAS provides a comprehensive range of programs and services designed to empower students, enhance their well-being, and facilitate their success both inside and outside the classroom.<source-footnote ng-version=\"0.0.0-PLACEHOLDER\" _nghost-ng-c1316579223=\"\"><sup _ngcontent-ng-c1316579223=\"\" class=\"superscript\" data-turn-source-index=\"1\"></sup></source-footnote></span><sources-carousel-inline ng-version=\"0.0.0-PLACEHOLDER\" _nghost-ng-c2924767884=\"\"><span _ngcontent-ng-c2924767884=\"\" hide-from-message-actions=\"\" class=\"button-container hide-from-message-actions ng-star-inserted\">&nbsp;&nbsp;</span></sources-carousel-inline></sources-carousel-inline></li><li><sources-carousel-inline ng-version=\"0.0.0-PLACEHOLDER\" _nghost-ng-c2924767884=\"\"><sources-carousel-inline ng-version=\"0.0.0-PLACEHOLDER\" _nghost-ng-c2924767884=\"\"><span _ngcontent-ng-c2924767884=\"\" hide-from-message-actions=\"\" class=\"button-container hide-from-message-actions ng-star-inserted\">The Western Mindanao State University (WMSU) Student Affairs Services (SAS) is a dynamic and student-centered office dedicated to fostering a vibrant and supportive university experience for all WMSU students.&nbsp;<span class=\"citation-0 recitation citation-end-0\">Committed to the holistic development of individuals, the SAS provides a comprehensive range of programs and services designed to empower students, enhance their well-being, and facilitate their success both inside and outside the classroom.<source-footnote ng-version=\"0.0.0-PLACEHOLDER\" _nghost-ng-c1316579223=\"\"><sup _ngcontent-ng-c1316579223=\"\" class=\"superscript\" data-turn-source-index=\"1\"></sup></source-footnote></span><sources-carousel-inline ng-version=\"0.0.0-PLACEHOLDER\" _nghost-ng-c2924767884=\"\"><span _ngcontent-ng-c2924767884=\"\" hide-from-message-actions=\"\" class=\"button-container hide-from-message-actions ng-star-inserted\">&nbsp;&nbsp;</span></sources-carousel-inline></span></sources-carousel-inline></sources-carousel-inline></li><li></li></ul>', '', 1, '2025-05-14 10:44:32', '2025-05-14 12:51:15');

-- --------------------------------------------------------

--
-- Table structure for table `af_page_info`
--

CREATE TABLE `af_page_info` (
  `id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `visible` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `af_page_info`
--

INSERT INTO `af_page_info` (`id`, `title`, `description`, `visible`) VALUES
(1, 'hghgfh', 'hghfgcvcv', 0);

-- --------------------------------------------------------

--
-- Table structure for table `af_page_mv`
--

CREATE TABLE `af_page_mv` (
  `id` int(11) NOT NULL,
  `section_title` varchar(255) NOT NULL DEFAULT 'MISSION AND VISION',
  `mission_title` varchar(255) NOT NULL DEFAULT 'MISSION',
  `mission_image_url` varchar(255) NOT NULL,
  `mission_description` text NOT NULL,
  `mission_show_more_text` varchar(255) DEFAULT 'SHOW MORE',
  `vision_title` varchar(255) NOT NULL DEFAULT 'VISION',
  `vision_image_url` varchar(255) NOT NULL,
  `vision_description` text NOT NULL,
  `vision_show_more_text` varchar(255) DEFAULT 'SHOW MORE',
  `is_visible` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `af_page_obj`
--

CREATE TABLE `af_page_obj` (
  `id` int(11) NOT NULL,
  `section_title` varchar(255) DEFAULT 'OUR OBJECTIVES',
  `description` text NOT NULL,
  `list` text NOT NULL,
  `is_visible` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `af_page_obj`
--

INSERT INTO `af_page_obj` (`id`, `section_title`, `description`, `list`, `is_visible`, `created_at`, `updated_at`) VALUES
(1, 'OBJECTIVE', '<div style=\"text-align: justify;\"><div style=\"\"><p data-sourcepos=\"3:1-3:464\" style=\"text-align: center;\">The Western Mindanao State University (WMSU) Student Affairs Services (SAS) is a dynamic and student-centered office dedicated to fostering a vibrant and supportive university experience for all WMSU students. <span class=\"citation-0 recitation citation-end-0\" style=\"\">Committed to the holistic development of individuals, the SAS provides a comprehensive range of programs and services designed to empower students, enhance their well-being, and facilitate their success both inside and outside the classroom.<source-footnote ng-version=\"0.0.0-PLACEHOLDER\" _nghost-ng-c1316579223=\"\"><sup _ngcontent-ng-c1316579223=\"\" class=\"superscript\" data-turn-source-index=\"1\"><!----></sup></source-footnote></span><sources-carousel-inline ng-version=\"0.0.0-PLACEHOLDER\" _nghost-ng-c2924767884=\"\" style=\"\"><!----><span _ngcontent-ng-c2924767884=\"\" hide-from-message-actions=\"\" class=\"button-container hide-from-message-actions ng-star-inserted\">&nbsp;&nbsp;<!----></span><!----><sources-carousel _ngcontent-ng-c2924767884=\"\" hide-from-message-actions=\"\" id=\"sources\" _nghost-ng-c2280442857=\"\" class=\"ng-tns-c2280442857-75 hide-from-message-actions ng-star-inserted\" style=\"display: flex; visibility: hidden;\"></sources-carousel></sources-carousel-inline></p><div _ngcontent-ng-c2280442857=\"\" class=\"container ng-tns-c2280442857-75 hide\" jslog=\"220997;BardVeMetadataKey:[null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,[1,null,1]]\" style=\"\"><!----><div _ngcontent-ng-c2280442857=\"\" class=\"carousel-container ng-tns-c2280442857-75\"><div _ngcontent-ng-c2280442857=\"\" class=\"carousel-content ng-tns-c2280442857-75\"><div _ngcontent-ng-c2280442857=\"\" data-test-id=\"sources-carousel-source\" class=\"sources-carousel-source ng-tns-c2280442857-75 hide ng-star-inserted\"><!----></div><!----><!----></div></div><!----></div><!----><!----><!----><!----><p style=\"\"></p><p data-sourcepos=\"5:1-5:462\" style=\"\"></p><ul style=\"\"><li>The Western Mindanao State University (WMSU) Student Affairs Services (SAS) is a dynamic and student-centered office dedicated to fostering a vibrant and supportive university experience for all WMSU students.&nbsp;<span class=\"citation-0 recitation citation-end-0\">Committed to the holistic development of individuals, the SAS provides a comprehensive range of programs and services designed to empower students, enhance their well-being, and facilitate their success both inside and outside the classroom.<source-footnote ng-version=\"0.0.0-PLACEHOLDER\" _nghost-ng-c1316579223=\"\"><sup _ngcontent-ng-c1316579223=\"\" class=\"superscript\" data-turn-source-index=\"1\"></sup></source-footnote></span><sources-carousel-inline ng-version=\"0.0.0-PLACEHOLDER\" _nghost-ng-c2924767884=\"\">&nbsp;&nbsp;</sources-carousel-inline></li><li><sources-carousel-inline ng-version=\"0.0.0-PLACEHOLDER\" _nghost-ng-c2924767884=\"\">The Western Mindanao State University (WMSU) Student Affairs Services (SAS) is a dynamic and student-centered office dedicated to fostering a vibrant and supportive university experience for all WMSU students.&nbsp;<span class=\"citation-0 recitation citation-end-0\">Committed to the holistic development of individuals, the SAS provides a comprehensive range of programs and services designed to empower students, enhance their well-being, and facilitate their success both inside and outside the classroom.<source-footnote ng-version=\"0.0.0-PLACEHOLDER\" _nghost-ng-c1316579223=\"\"><sup _ngcontent-ng-c1316579223=\"\" class=\"superscript\" data-turn-source-index=\"1\"></sup></source-footnote></span><sources-carousel-inline ng-version=\"0.0.0-PLACEHOLDER\" _nghost-ng-c2924767884=\"\"><span _ngcontent-ng-c2924767884=\"\" hide-from-message-actions=\"\" class=\"button-container hide-from-message-actions ng-star-inserted\">&nbsp;&nbsp;</span></sources-carousel-inline></sources-carousel-inline></li><li><sources-carousel-inline ng-version=\"0.0.0-PLACEHOLDER\" _nghost-ng-c2924767884=\"\"><sources-carousel-inline ng-version=\"0.0.0-PLACEHOLDER\" _nghost-ng-c2924767884=\"\"><span _ngcontent-ng-c2924767884=\"\" hide-from-message-actions=\"\" class=\"button-container hide-from-message-actions ng-star-inserted\">The Western Mindanao State University (WMSU) Student Affairs Services (SAS) is a dynamic and student-centered office dedicated to fostering a vibrant and supportive university experience for all WMSU students.&nbsp;<span class=\"citation-0 recitation citation-end-0\">Committed to the holistic development of individuals, the SAS provides a comprehensive range of programs and services designed to empower students, enhance their well-being, and facilitate their success both inside and outside the classroom.<source-footnote ng-version=\"0.0.0-PLACEHOLDER\" _nghost-ng-c1316579223=\"\"><sup _ngcontent-ng-c1316579223=\"\" class=\"superscript\" data-turn-source-index=\"1\"></sup></source-footnote></span><sources-carousel-inline ng-version=\"0.0.0-PLACEHOLDER\" _nghost-ng-c2924767884=\"\"><span _ngcontent-ng-c2924767884=\"\" hide-from-message-actions=\"\" class=\"button-container hide-from-message-actions ng-star-inserted\">&nbsp;&nbsp;</span></sources-carousel-inline></span></sources-carousel-inline></sources-carousel-inline></li></ul><p style=\"\"></p></div></div><br>', '<br>\r\n<b>Warning</b>:  Trying to access array offset on value of type bool in <b>C:\\xampp\\htdocs\\osa\\cms\\student_affairs\\cms_af_page_obj_funct.php</b> on line <b>282</b><br>\r\n', 1, '2025-05-14 10:42:21', '2025-05-14 12:51:28');

-- --------------------------------------------------------

--
-- Table structure for table `af_page_officer`
--

CREATE TABLE `af_page_officer` (
  `id` int(11) NOT NULL,
  `section_title` varchar(255) NOT NULL DEFAULT 'Meet Our Officers',
  `name` varchar(255) NOT NULL,
  `position` varchar(255) NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `is_visible` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `af_page_process_info`
--

CREATE TABLE `af_page_process_info` (
  `id` int(11) NOT NULL,
  `section_title` varchar(255) NOT NULL DEFAULT 'OUR OBJECTIVES',
  `section_description` text NOT NULL,
  `is_visible` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `af_page_process_steps`
--

CREATE TABLE `af_page_process_steps` (
  `id` int(11) NOT NULL,
  `section_title` varchar(255) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `visibility` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `af_page_process_steps`
--

INSERT INTO `af_page_process_steps` (`id`, `section_title`, `title`, `description`, `visibility`) VALUES
(1, 'How to Apply for Our Servicescc', 'bvbvc', 'vcbcvb', 1);

-- --------------------------------------------------------

--
-- Table structure for table `af_page_services`
--

CREATE TABLE `af_page_services` (
  `id` int(11) NOT NULL,
  `section_title` varchar(255) NOT NULL DEFAULT 'Our Services',
  `section_description` text NOT NULL DEFAULT 'We provide a range of healthcare services to support the well-being of our university community.',
  `icon_class` varchar(255) NOT NULL,
  `service_title` varchar(255) NOT NULL,
  `service_description` text NOT NULL,
  `is_visible` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `af_page_services`
--

INSERT INTO `af_page_services` (`id`, `section_title`, `section_description`, `icon_class`, `service_title`, `service_description`, `is_visible`, `created_at`, `updated_at`) VALUES
(1, 'fdfdfsdf', 'fdffdfdf', 'fdfdf', 'fdfdsf', 'dfdsfd', 1, '2025-05-14 11:14:36', '2025-05-14 11:14:36'),
(4, 'Our Services', 'We provide a range of healthcare services to support the well-being of our university community.', 'http://localhost/osa/cms/uploads/services/682485e2efbdf.png', 'fdfdfdsfdfd', 'fdfdfdsfds', 1, '2025-05-14 12:00:34', '2025-05-14 12:37:59'),
(5, 'Our Services', 'We provide a range of healthcare services to support the well-being of our university community.', 'http://localhost/osa/cms/uploads/services/6824883c0d357.png', 'fdfdfdfd', 'fddfd', 1, '2025-05-14 12:10:36', '2025-05-14 12:39:46'),
(6, 'Our Services', 'We provide a range of healthcare services to support the well-being of our university community.', 'http://localhost/osa/cms/uploads/services/68248e772e377.png', 'dssdsddfdfdfd', 'fdfdfdfdsf', 1, '2025-05-14 12:37:11', '2025-05-14 12:38:17'),
(7, 'Our Services', 'We provide a range of healthcare services to support the well-being of our university community.', 'http://localhost/osa/cms/uploads/services/68248ee8f1c11.png', 'fdfdfdf', 'fdfdfdfdfdfdfdf', 1, '2025-05-14 12:39:04', '2025-05-14 12:39:15'),
(9, 'Our Services', 'We provide a range of healthcare services to support the well-being of our university community.', 'http://localhost/osa/cms/uploads/services/682493ff1b94b.png', 'FFFF', 'FFFF', 1, '2025-05-14 13:00:47', '2025-05-14 13:00:54'),
(10, 'Our Services', 'We provide a range of healthcare services to support the well-being of our university community.', 'http://localhost/osa/cms/uploads/services/68249efe37928.png', 'cxxzcxc', '<div><ul><li>gffgfgfgfg</li><li>gffgfgfgfg</li><li>gffgfgfgfg</li></ul></div>', 1, '2025-05-14 13:47:42', '2025-05-14 14:54:36');

-- --------------------------------------------------------

--
-- Table structure for table `af_page_services_main`
--

CREATE TABLE `af_page_services_main` (
  `id` int(11) NOT NULL DEFAULT 1,
  `section_title` varchar(255) DEFAULT 'Our Services',
  `section_description` text DEFAULT NULL,
  `is_visible` tinyint(1) DEFAULT 1,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `af_page_services_main`
--

INSERT INTO `af_page_services_main` (`id`, `section_title`, `section_description`, `is_visible`, `updated_at`) VALUES
(1, 'OUR SERVICES', '<h2 data-sourcepos=\"1:1-1:63\"><span style=\"font-size: 16px; font-weight: normal;\">Under the leadership of the Vice President for Student Affairs and Services and the Director of the Office of Student Affairs, the SAS plays a crucial role in shaping student life at WMSU.&nbsp;</span></h2><p data-sourcepos=\"7:1-7:269\"><br></p>', 1, '2025-05-14 13:50:05');

-- --------------------------------------------------------

--
-- Table structure for table `health_about`
--

CREATE TABLE `health_about` (
  `id` int(11) NOT NULL,
  `ontop_title` varchar(255) DEFAULT NULL,
  `main_title` varchar(255) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_visible` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `health_about`
--

INSERT INTO `health_about` (`id`, `ontop_title`, `main_title`, `image_path`, `description`, `is_visible`) VALUES
(1, 'ABOUT US', 'HEALTH SERVICESdc', 'uploads/6824b45178712.jpg', 'Default content. Please update this in the CMS.cxczxzcc', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `about`
--
ALTER TABLE `about`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `section_unique` (`section`);

--
-- Indexes for table `af_page`
--
ALTER TABLE `af_page`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `af_pagec_info_header`
--
ALTER TABLE `af_pagec_info_header`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `af_page_activities`
--
ALTER TABLE `af_page_activities`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `af_page_activities_settings`
--
ALTER TABLE `af_page_activities_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `af_page_contact`
--
ALTER TABLE `af_page_contact`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `af_page_downloadable`
--
ALTER TABLE `af_page_downloadable`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `af_page_facilities`
--
ALTER TABLE `af_page_facilities`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `af_page_facilities_settings`
--
ALTER TABLE `af_page_facilities_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `af_page_funct`
--
ALTER TABLE `af_page_funct`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `af_page_info`
--
ALTER TABLE `af_page_info`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `af_page_mv`
--
ALTER TABLE `af_page_mv`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `af_page_obj`
--
ALTER TABLE `af_page_obj`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `af_page_officer`
--
ALTER TABLE `af_page_officer`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `af_page_process_info`
--
ALTER TABLE `af_page_process_info`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `af_page_process_steps`
--
ALTER TABLE `af_page_process_steps`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `af_page_services`
--
ALTER TABLE `af_page_services`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `af_page_services_main`
--
ALTER TABLE `af_page_services_main`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `health_about`
--
ALTER TABLE `health_about`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `about`
--
ALTER TABLE `about`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `af_page`
--
ALTER TABLE `af_page`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `af_page_activities`
--
ALTER TABLE `af_page_activities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `af_page_activities_settings`
--
ALTER TABLE `af_page_activities_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `af_page_contact`
--
ALTER TABLE `af_page_contact`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `af_page_downloadable`
--
ALTER TABLE `af_page_downloadable`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `af_page_facilities`
--
ALTER TABLE `af_page_facilities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `af_page_facilities_settings`
--
ALTER TABLE `af_page_facilities_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `af_page_funct`
--
ALTER TABLE `af_page_funct`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `af_page_info`
--
ALTER TABLE `af_page_info`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `af_page_mv`
--
ALTER TABLE `af_page_mv`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `af_page_obj`
--
ALTER TABLE `af_page_obj`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `af_page_officer`
--
ALTER TABLE `af_page_officer`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `af_page_process_info`
--
ALTER TABLE `af_page_process_info`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `af_page_process_steps`
--
ALTER TABLE `af_page_process_steps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `af_page_services`
--
ALTER TABLE `af_page_services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `health_about`
--
ALTER TABLE `health_about`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
