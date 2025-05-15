-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 14, 2025 at 03:26 PM
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
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
-- Table structure for table `af_page_mission`
--

CREATE TABLE `af_page_mission` (
  `id` int(11) NOT NULL,
  `section_title` varchar(255) NOT NULL DEFAULT 'MISSION',
  `image_url` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `show_more_text` varchar(255) DEFAULT 'SHOW MORE',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `af_page_mission`
--

INSERT INTO `af_page_mission` (`id`, `section_title`, `image_url`, `description`, `show_more_text`, `created_at`, `updated_at`) VALUES
(1, 'MISSION', 'uploads/682492e57fdca.jpg', '<h2 data-sourcepos=\"1:1-1:63\"><span style=\"font-size: 16px;\">Under the leadership of the Vice President for Student Affairs and Services and the Director of the Office of Student Affairs, the SAS plays a crucial role in shaping student life at WMSU.&nbsp;</span></h2><p data-sourcepos=\"7:1-7:269\"><br></p>', 'SHOW MORE+', '2025-05-14 10:19:57', '2025-05-14 12:56:05');

-- --------------------------------------------------------

--
-- Table structure for table `af_page_mv`
--

CREATE TABLE `af_page_mv` (
  `id` int(11) NOT NULL,
  `section_title` varchar(255) NOT NULL DEFAULT 'MISSION AND VISION',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_visible` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `af_page_mv`
--

INSERT INTO `af_page_mv` (`id`, `section_title`, `created_at`, `updated_at`, `is_visible`) VALUES
(1, 'MISSION & VISION', '2025-05-14 10:16:44', '2025-05-14 12:54:42', 1);

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
(8, 'Our Services', 'We provide a range of healthcare services to support the well-being of our university community.', 'http://localhost/osa/cms/uploads/services/682492039ef43.png', 'DFDFDFD', 'FFFF', 1, '2025-05-14 12:52:19', '2025-05-14 12:52:27'),
(9, 'Our Services', 'We provide a range of healthcare services to support the well-being of our university community.', 'http://localhost/osa/cms/uploads/services/682493ff1b94b.png', 'FFFF', 'FFFF', 1, '2025-05-14 13:00:47', '2025-05-14 13:00:54');

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
(1, 'OUR SERVICES', 'ddfdfsdf', 1, '2025-05-14 13:05:33');

-- --------------------------------------------------------

--
-- Table structure for table `af_page_vision`
--

CREATE TABLE `af_page_vision` (
  `id` int(11) NOT NULL,
  `section_title` varchar(255) NOT NULL DEFAULT 'VISION',
  `image_url` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `show_more_text` varchar(255) DEFAULT 'SHOW MORE',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `af_page_vision`
--

INSERT INTO `af_page_vision` (`id`, `section_title`, `image_url`, `description`, `show_more_text`, `created_at`, `updated_at`) VALUES
(1, 'VISION', '', '<h2 data-sourcepos=\"1:1-1:63\"><span style=\"font-size: 16px;\">Under the leadership of the Vice President for Student Affairs and Services and the Director of the Office of Student Affairs, the SAS plays a crucial role in shaping student life at WMSU.&nbsp;</span></h2><p data-sourcepos=\"7:1-7:269\"><br></p>', 'SHOW MORE+', '2025-05-14 12:55:43', '2025-05-14 12:56:31');

-- --------------------------------------------------------

--
-- Table structure for table `mission_vision`
--

CREATE TABLE `mission_vision` (
  `id` int(11) NOT NULL,
  `section` varchar(50) NOT NULL,
  `mission_title` varchar(255) DEFAULT 'MISSION',
  `mission_content` text DEFAULT NULL,
  `mission_image_type` varchar(10) DEFAULT 'file',
  `mission_image_path` varchar(255) DEFAULT NULL,
  `vision_title` varchar(255) DEFAULT 'VISION',
  `vision_content` text DEFAULT NULL,
  `vision_image_type` varchar(10) DEFAULT 'file',
  `vision_image_path` varchar(255) DEFAULT NULL,
  `is_visible` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mission_vision`
--

INSERT INTO `mission_vision` (`id`, `section`, `mission_title`, `mission_content`, `mission_image_type`, `mission_image_path`, `vision_title`, `vision_content`, `vision_image_type`, `vision_image_path`, `is_visible`, `created_at`, `updated_at`) VALUES
(1, 'health', 'MISSION', 'We are committed to creating a healthy campus environment through regular health awareness programs, wellness initiatives, and emergency response services. Our modern facilities are equipped with essential medical equipment to ensure prompt and effective healthcare delivery.', 'file', '../imgs/cte.jpg', 'VISION', 'We envision Western Mindanao State University to be a leading institution in health and wellness, fostering a vibrant, healthy community through innovative healthcare services.', 'file', '../imgs/cte-field.png', 1, '2025-05-14 10:54:03', '2025-05-14 10:54:03');

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
-- Indexes for table `af_page_activities`
--
ALTER TABLE `af_page_activities`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `af_page_funct`
--
ALTER TABLE `af_page_funct`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `af_page_mission`
--
ALTER TABLE `af_page_mission`
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
-- Indexes for table `af_page_vision`
--
ALTER TABLE `af_page_vision`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mission_vision`
--
ALTER TABLE `mission_vision`
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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `af_page_funct`
--
ALTER TABLE `af_page_funct`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `af_page_mission`
--
ALTER TABLE `af_page_mission`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `af_page_mv`
--
ALTER TABLE `af_page_mv`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `af_page_obj`
--
ALTER TABLE `af_page_obj`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `af_page_services`
--
ALTER TABLE `af_page_services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `af_page_vision`
--
ALTER TABLE `af_page_vision`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `mission_vision`
--
ALTER TABLE `mission_vision`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
