-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 10, 2026 at 04:00 PM
-- Server version: 10.11.14-MariaDB-cll-lve
-- PHP Version: 8.4.21

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sitalcom_mmp`
--

-- --------------------------------------------------------

--
-- Table structure for table `academic_sessions`
--

CREATE TABLE `academic_sessions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `name_bs` varchar(255) DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('upcoming','active','ended') NOT NULL DEFAULT 'upcoming',
  `is_locked` tinyint(1) NOT NULL DEFAULT 0,
  `activated_at` timestamp NULL DEFAULT NULL,
  `ended_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `academic_sessions`
--

INSERT INTO `academic_sessions` (`id`, `name`, `name_bs`, `start_date`, `end_date`, `is_active`, `status`, `is_locked`, `activated_at`, `ended_at`, `notes`, `created_at`, `updated_at`) VALUES
(1, '2081-2082', '2081-2082', '2026-04-05', '2027-04-05', 1, 'active', 0, '2026-06-05 19:29:55', NULL, 'Active academic session.', '2026-06-03 03:10:23', '2026-06-05 19:29:55');

-- --------------------------------------------------------

--
-- Table structure for table `academic_session_semesters`
--

CREATE TABLE `academic_session_semesters` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `academic_session_id` bigint(20) UNSIGNED NOT NULL,
  `semester_number` tinyint(3) UNSIGNED NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('upcoming','running','delayed','completed') NOT NULL DEFAULT 'upcoming',
  `delay_reason` enum('exam_late','holidays','internal_delay','admin_decision') DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `alumni`
--

CREATE TABLE `alumni` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `student_id` bigint(20) UNSIGNED DEFAULT NULL,
  `department_id` bigint(20) UNSIGNED NOT NULL,
  `program_id` bigint(20) UNSIGNED NOT NULL,
  `roll_number` varchar(255) DEFAULT NULL,
  `admission_year` varchar(4) DEFAULT NULL,
  `graduation_year` varchar(4) NOT NULL,
  `graduation_date` date DEFAULT NULL,
  `current_status` varchar(255) NOT NULL DEFAULT 'recent_graduate',
  `current_job` varchar(255) DEFAULT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `employment_status` varchar(255) NOT NULL DEFAULT 'unknown',
  `work_location` varchar(255) DEFAULT NULL,
  `achievements` text DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `skills` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`skills`)),
  `linkedin_url` varchar(255) DEFAULT NULL,
  `github_url` varchar(255) DEFAULT NULL,
  `portfolio_url` varchar(255) DEFAULT NULL,
  `cv_path` varchar(255) DEFAULT NULL,
  `profile_completion` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `visibility` varchar(255) NOT NULL DEFAULT 'public',
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_verified` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `alumni_achievements`
--

CREATE TABLE `alumni_achievements` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `alumni_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `certificate_path` varchar(255) DEFAULT NULL,
  `year` varchar(4) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `alumni_employments`
--

CREATE TABLE `alumni_employments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `alumni_id` bigint(20) UNSIGNED NOT NULL,
  `job_title` varchar(255) NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `is_current` tinyint(1) NOT NULL DEFAULT 0,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `alumni_projects`
--

CREATE TABLE `alumni_projects` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `alumni_id` bigint(20) UNSIGNED NOT NULL,
  `type` enum('minor','major') NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `supervisor` varchar(255) DEFAULT NULL,
  `technologies` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`technologies`)),
  `team_members` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`team_members`)),
  `report_path` varchar(255) DEFAULT NULL,
  `screenshots` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`screenshots`)),
  `github_url` varchar(255) DEFAULT NULL,
  `demo_url` varchar(255) DEFAULT NULL,
  `cover_image` varchar(255) DEFAULT NULL,
  `status` enum('in_progress','completed') NOT NULL DEFAULT 'completed',
  `is_visible` tinyint(1) NOT NULL DEFAULT 1,
  `year` varchar(4) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `assignments`
--

CREATE TABLE `assignments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `teacher_id` bigint(20) UNSIGNED NOT NULL,
  `subject_id` bigint(20) UNSIGNED NOT NULL,
  `program_id` bigint(20) UNSIGNED NOT NULL,
  `semester` tinyint(3) UNSIGNED NOT NULL,
  `section` varchar(255) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `due_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `assignments`
--

INSERT INTO `assignments` (`id`, `teacher_id`, `subject_id`, `program_id`, `semester`, `section`, `title`, `description`, `attachment`, `due_date`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 1, NULL, '1 Assesmnet', NULL, 'assignments/g9xN8jUF2UUxX5gB26NrWvtKbiOHMayqgvEc5pWR.png', '2026-06-06', '2026-06-06 07:28:21', '2026-06-06 07:28:21');

-- --------------------------------------------------------

--
-- Table structure for table `assignment_submissions`
--

CREATE TABLE `assignment_submissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `assignment_id` bigint(20) UNSIGNED NOT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `student_note` text DEFAULT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `status` enum('submitted','graded','late') NOT NULL DEFAULT 'submitted',
  `marks_obtained` decimal(5,2) DEFAULT NULL,
  `teacher_feedback` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `assignment_submissions`
--

INSERT INTO `assignment_submissions` (`id`, `assignment_id`, `student_id`, `student_note`, `attachment`, `status`, `marks_obtained`, `teacher_feedback`, `created_at`, `updated_at`) VALUES
(1, 1, 1, NULL, 'assignments/submissions/1780743336_Screenshot 2026-05-15 204517.png', 'submitted', NULL, NULL, '2026-06-06 10:55:35', '2026-06-06 10:55:36');

-- --------------------------------------------------------

--
-- Table structure for table `attendances`
--

CREATE TABLE `attendances` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `attendance_session_id` bigint(20) UNSIGNED NOT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `status` enum('present','absent','late','excused') NOT NULL DEFAULT 'present',
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `attendances`
--

INSERT INTO `attendances` (`id`, `attendance_session_id`, `student_id`, `status`, `remarks`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'present', NULL, '2026-06-06 07:24:43', '2026-06-06 07:24:43'),
(2, 2, 1, 'present', NULL, '2026-06-06 07:25:10', '2026-06-06 07:25:10');

-- --------------------------------------------------------

--
-- Table structure for table `attendance_sessions`
--

CREATE TABLE `attendance_sessions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `academic_session_id` bigint(20) UNSIGNED NOT NULL,
  `teacher_id` bigint(20) UNSIGNED NOT NULL,
  `subject_id` bigint(20) UNSIGNED NOT NULL,
  `program_id` bigint(20) UNSIGNED NOT NULL,
  `semester` tinyint(3) UNSIGNED NOT NULL,
  `section` varchar(255) DEFAULT NULL,
  `date` date NOT NULL,
  `period` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `attendance_sessions`
--

INSERT INTO `attendance_sessions` (`id`, `academic_session_id`, `teacher_id`, `subject_id`, `program_id`, `semester`, `section`, `date`, `period`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 1, 1, NULL, '2026-06-03', '1 (Class)', '2026-06-06 07:24:43', '2026-06-06 07:24:43'),
(2, 1, 1, 1, 1, 1, NULL, '2026-06-06', '3 (Lab)', '2026-06-06 07:25:10', '2026-06-06 07:25:10');

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `model_type` varchar(255) DEFAULT NULL,
  `model_id` bigint(20) UNSIGNED DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `model_type`, `model_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES
(1, 1, 'login', NULL, NULL, NULL, NULL, '27.34.68.179', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-03 03:12:17', '2026-06-03 03:12:17'),
(2, 1, 'post:login', NULL, NULL, NULL, NULL, '27.34.68.179', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-03 03:12:17', '2026-06-03 03:12:17'),
(3, 1, 'patch:admin/settings/profile', NULL, NULL, NULL, NULL, '27.34.68.179', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-03 03:12:39', '2026-06-03 03:12:39'),
(4, 1, 'login', NULL, NULL, NULL, NULL, '27.34.68.226', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36', '2026-06-03 05:26:01', '2026-06-03 05:26:01'),
(5, 1, 'post:login', NULL, NULL, NULL, NULL, '27.34.68.226', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36', '2026-06-03 05:26:01', '2026-06-03 05:26:01'),
(6, 1, 'login', NULL, NULL, NULL, NULL, '27.34.68.226', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-03 05:32:43', '2026-06-03 05:32:43'),
(7, 1, 'post:login', NULL, NULL, NULL, NULL, '27.34.68.226', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-03 05:32:43', '2026-06-03 05:32:43'),
(8, 1, 'post:admin/web-control', NULL, NULL, NULL, NULL, '27.34.68.226', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-03 05:35:04', '2026-06-03 05:35:04'),
(9, 1, 'post:admin/banners', NULL, NULL, NULL, NULL, '27.34.68.226', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-03 05:36:31', '2026-06-03 05:36:31'),
(10, 1, 'post:admin/web-control', NULL, NULL, NULL, NULL, '27.34.68.226', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-03 05:37:18', '2026-06-03 05:37:18'),
(11, 1, 'post:admin/web-control', NULL, NULL, NULL, NULL, '45.64.163.143', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36', '2026-06-03 06:18:15', '2026-06-03 06:18:15'),
(12, 1, 'post:admin/web-control', NULL, NULL, NULL, NULL, '45.64.163.143', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36', '2026-06-03 06:19:04', '2026-06-03 06:19:04'),
(13, 1, 'delete:admin/executives/1', NULL, NULL, NULL, NULL, '45.64.163.143', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36', '2026-06-03 06:19:55', '2026-06-03 06:19:55'),
(14, 1, 'login', NULL, NULL, NULL, NULL, '27.34.116.146', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-03 07:56:30', '2026-06-03 07:56:30'),
(15, 1, 'post:login', NULL, NULL, NULL, NULL, '27.34.116.146', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-03 07:56:30', '2026-06-03 07:56:30'),
(16, 1, 'post:admin/web-control', NULL, NULL, NULL, NULL, '27.34.116.146', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-03 07:57:13', '2026-06-03 07:57:13'),
(17, 1, 'put:admin/banners/1', NULL, NULL, NULL, NULL, '27.34.116.146', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-03 08:00:29', '2026-06-03 08:00:29'),
(18, 1, 'post:admin/web-control', NULL, NULL, NULL, NULL, '27.34.116.146', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-03 08:02:43', '2026-06-03 08:02:43'),
(19, 1, 'login', NULL, NULL, NULL, NULL, '45.64.163.143', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36', '2026-06-03 09:29:01', '2026-06-03 09:29:01'),
(20, 1, 'post:login', NULL, NULL, NULL, NULL, '45.64.163.143', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36', '2026-06-03 09:29:01', '2026-06-03 09:29:01'),
(21, 1, 'login', NULL, NULL, NULL, NULL, '45.123.220.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-05 19:13:14', '2026-06-05 19:13:14'),
(22, 1, 'post:login', NULL, NULL, NULL, NULL, '45.123.220.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-05 19:13:14', '2026-06-05 19:13:14'),
(23, 1, 'login', NULL, NULL, NULL, NULL, '45.123.220.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0', '2026-06-05 19:16:11', '2026-06-05 19:16:11'),
(24, 1, 'post:login', NULL, NULL, NULL, NULL, '45.123.220.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0', '2026-06-05 19:16:11', '2026-06-05 19:16:11'),
(25, 1, 'login', NULL, NULL, NULL, NULL, '45.123.220.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-05 19:37:03', '2026-06-05 19:37:03'),
(26, 1, 'post:login', NULL, NULL, NULL, NULL, '45.123.220.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-05 19:37:03', '2026-06-05 19:37:03'),
(27, 1, 'post:admin/departments', NULL, NULL, NULL, NULL, '45.123.220.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-05 19:38:47', '2026-06-05 19:38:47'),
(28, 1, 'program.created', 'App\\Models\\Program', 1, NULL, '{\"name\":\"Department in Information Technology\"}', '45.123.220.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-05 19:39:14', '2026-06-05 19:39:14'),
(29, 1, 'post:admin/programs', NULL, NULL, NULL, NULL, '45.123.220.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-05 19:39:14', '2026-06-05 19:39:14'),
(30, 1, 'post:admin/programs', NULL, NULL, NULL, NULL, '45.123.220.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-05 19:39:14', '2026-06-05 19:39:14'),
(31, 1, 'post:admin/programs', NULL, NULL, NULL, NULL, '45.123.220.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-05 19:39:36', '2026-06-05 19:39:36'),
(32, 1, 'post:admin/programs', NULL, NULL, NULL, NULL, '45.123.220.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-05 19:39:52', '2026-06-05 19:39:52'),
(33, 1, 'put:admin/departments/1', NULL, NULL, NULL, NULL, '45.123.220.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-05 19:40:31', '2026-06-05 19:40:31'),
(34, 1, 'post:admin/students', NULL, NULL, NULL, NULL, '45.123.220.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-05 19:42:36', '2026-06-05 19:42:36'),
(35, 1, 'put:admin/students/1', NULL, NULL, NULL, NULL, '45.123.220.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-05 19:43:23', '2026-06-05 19:43:23'),
(36, 2, 'login', NULL, NULL, NULL, NULL, '45.123.220.156', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36', '2026-06-05 20:19:25', '2026-06-05 20:19:25'),
(37, 2, 'post:login', NULL, NULL, NULL, NULL, '45.123.220.156', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36', '2026-06-05 20:19:25', '2026-06-05 20:19:25'),
(38, 1, 'login', NULL, NULL, NULL, NULL, '45.123.220.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-06 03:40:47', '2026-06-06 03:40:47'),
(39, 1, 'post:login', NULL, NULL, NULL, NULL, '45.123.220.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-06 03:40:47', '2026-06-06 03:40:47'),
(40, 1, 'logout', NULL, NULL, NULL, NULL, '45.123.220.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-06 03:41:41', '2026-06-06 03:41:41'),
(41, 1, 'login', NULL, NULL, NULL, NULL, '45.123.220.107', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-06 07:20:13', '2026-06-06 07:20:13'),
(42, 1, 'post:login', NULL, NULL, NULL, NULL, '45.123.220.107', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-06 07:20:13', '2026-06-06 07:20:13'),
(43, 1, 'post:admin/hods', NULL, NULL, NULL, NULL, '45.123.220.107', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-06 07:21:10', '2026-06-06 07:21:10'),
(44, 1, 'logout', NULL, NULL, NULL, NULL, '45.123.220.107', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-06 07:21:34', '2026-06-06 07:21:34'),
(45, 3, 'login', NULL, NULL, NULL, NULL, '45.123.220.107', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-06 07:21:43', '2026-06-06 07:21:43'),
(46, 3, 'post:login', NULL, NULL, NULL, NULL, '45.123.220.107', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-06 07:21:43', '2026-06-06 07:21:43'),
(47, 3, 'post:hod/subjects', NULL, NULL, NULL, NULL, '45.123.220.107', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-06 07:22:27', '2026-06-06 07:22:27'),
(48, 3, 'post:hod/teachers', NULL, NULL, NULL, NULL, '45.123.220.107', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-06 07:24:07', '2026-06-06 07:24:07'),
(49, 3, 'post:hod/attendance/store', NULL, NULL, NULL, NULL, '45.123.220.107', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-06 07:24:43', '2026-06-06 07:24:43'),
(50, 3, 'post:hod/attendance/store', NULL, NULL, NULL, NULL, '45.123.220.107', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-06 07:25:10', '2026-06-06 07:25:10'),
(51, 3, 'logout', NULL, NULL, NULL, NULL, '45.123.220.107', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-06 07:27:24', '2026-06-06 07:27:24'),
(52, 4, 'login', NULL, NULL, NULL, NULL, '45.123.220.107', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-06 07:27:36', '2026-06-06 07:27:36'),
(53, 4, 'post:login', NULL, NULL, NULL, NULL, '45.123.220.107', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-06 07:27:36', '2026-06-06 07:27:36'),
(54, 4, 'post:teacher/assignments', NULL, NULL, NULL, NULL, '45.123.220.107', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-06 07:28:21', '2026-06-06 07:28:21'),
(55, 4, 'logout', NULL, NULL, NULL, NULL, '45.123.220.107', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-06 07:29:29', '2026-06-06 07:29:29'),
(56, 3, 'login', NULL, NULL, NULL, NULL, '45.123.220.107', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-06 07:29:41', '2026-06-06 07:29:41'),
(57, 3, 'post:login', NULL, NULL, NULL, NULL, '45.123.220.107', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-06 07:29:41', '2026-06-06 07:29:41'),
(58, 3, 'post:hod/exams', NULL, NULL, NULL, NULL, '45.123.220.107', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-06 07:30:33', '2026-06-06 07:30:33'),
(59, 3, 'post:hod/exams/save-marks', NULL, NULL, NULL, NULL, '45.123.220.107', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-06 07:31:19', '2026-06-06 07:31:19'),
(60, 3, 'post:hod/exams/verify-marks', NULL, NULL, NULL, NULL, '45.123.220.107', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-06 07:31:39', '2026-06-06 07:31:39'),
(61, 3, 'logout', NULL, NULL, NULL, NULL, '45.123.220.107', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-06 07:32:52', '2026-06-06 07:32:52'),
(62, 3, 'login', NULL, NULL, NULL, NULL, '45.123.220.107', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-06 07:33:12', '2026-06-06 07:33:12'),
(63, 3, 'post:login', NULL, NULL, NULL, NULL, '45.123.220.107', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-06 07:33:12', '2026-06-06 07:33:12'),
(64, 3, 'logout', NULL, NULL, NULL, NULL, '45.123.220.107', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-06 07:35:11', '2026-06-06 07:35:11'),
(65, 1, 'login', NULL, NULL, NULL, NULL, '45.123.220.107', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-06 07:35:21', '2026-06-06 07:35:21'),
(66, 1, 'post:login', NULL, NULL, NULL, NULL, '45.123.220.107', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-06 07:35:21', '2026-06-06 07:35:21'),
(67, 1, 'patch:admin/exams/1/publish', NULL, NULL, NULL, NULL, '45.123.220.107', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-06 07:35:35', '2026-06-06 07:35:35'),
(68, 1, 'logout', NULL, NULL, NULL, NULL, '45.123.220.107', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-06 07:36:29', '2026-06-06 07:36:29'),
(69, 2, 'login', NULL, NULL, NULL, NULL, '45.123.220.107', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-06 07:36:53', '2026-06-06 07:36:53'),
(70, 2, 'post:login', NULL, NULL, NULL, NULL, '45.123.220.107', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-06 07:36:53', '2026-06-06 07:36:53'),
(71, 2, 'logout', NULL, NULL, NULL, NULL, '45.123.220.107', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-06 07:39:02', '2026-06-06 07:39:02'),
(72, 4, 'login', NULL, NULL, NULL, NULL, '45.123.220.107', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-06 07:39:12', '2026-06-06 07:39:12'),
(73, 4, 'post:login', NULL, NULL, NULL, NULL, '45.123.220.107', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-06 07:39:12', '2026-06-06 07:39:12'),
(74, 4, 'logout', NULL, NULL, NULL, NULL, '45.123.220.107', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-06 07:42:50', '2026-06-06 07:42:50'),
(75, 1, 'login', NULL, NULL, NULL, NULL, '45.123.220.107', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-06 07:43:14', '2026-06-06 07:43:14'),
(76, 1, 'post:login', NULL, NULL, NULL, NULL, '45.123.220.107', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-06 07:43:14', '2026-06-06 07:43:14'),
(77, 1, 'logout', NULL, NULL, NULL, NULL, '45.123.220.107', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-06 07:44:52', '2026-06-06 07:44:52'),
(78, 3, 'login', NULL, NULL, NULL, NULL, '45.123.220.107', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-06 07:47:05', '2026-06-06 07:47:05'),
(79, 3, 'post:login', NULL, NULL, NULL, NULL, '45.123.220.107', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-06 07:47:05', '2026-06-06 07:47:05'),
(80, 3, 'post:hod/timetable', NULL, NULL, NULL, NULL, '45.123.220.107', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-06 07:48:43', '2026-06-06 07:48:43'),
(81, 3, 'logout', NULL, NULL, NULL, NULL, '45.123.220.107', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-06 07:49:00', '2026-06-06 07:49:00'),
(82, 2, 'login', NULL, NULL, NULL, NULL, '45.123.220.107', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-06 07:49:24', '2026-06-06 07:49:24'),
(83, 2, 'post:login', NULL, NULL, NULL, NULL, '45.123.220.107', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-06 07:49:24', '2026-06-06 07:49:24'),
(84, 2, 'logout', NULL, NULL, NULL, NULL, '45.123.220.107', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-06 07:50:51', '2026-06-06 07:50:51'),
(85, 4, 'login', NULL, NULL, NULL, NULL, '45.123.220.107', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-06 09:53:29', '2026-06-06 09:53:29'),
(86, 4, 'post:login', NULL, NULL, NULL, NULL, '45.123.220.107', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-06 09:53:29', '2026-06-06 09:53:29'),
(87, 4, 'logout', NULL, NULL, NULL, NULL, '45.123.220.107', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-06 10:12:57', '2026-06-06 10:12:57'),
(88, 2, 'login', NULL, NULL, NULL, NULL, '45.123.220.107', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-06 10:13:07', '2026-06-06 10:13:07'),
(89, 2, 'post:login', NULL, NULL, NULL, NULL, '45.123.220.107', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-06 10:13:07', '2026-06-06 10:13:07'),
(90, 2, 'post:student/assignments/1/submit', NULL, NULL, NULL, NULL, '27.34.68.238', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-06 10:55:35', '2026-06-06 10:55:35'),
(91, 2, 'post:student/assignments/1/submit', NULL, NULL, NULL, NULL, '27.34.68.238', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-06 10:55:36', '2026-06-06 10:55:36'),
(92, 2, 'login', NULL, NULL, NULL, NULL, '27.34.68.238', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36', '2026-06-06 10:56:33', '2026-06-06 10:56:33'),
(93, 2, 'post:login', NULL, NULL, NULL, NULL, '27.34.68.238', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36', '2026-06-06 10:56:33', '2026-06-06 10:56:33'),
(94, 3, 'login', NULL, NULL, NULL, NULL, '45.123.220.139', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 03:45:52', '2026-06-07 03:45:52'),
(95, 3, 'post:login', NULL, NULL, NULL, NULL, '45.123.220.139', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 03:45:52', '2026-06-07 03:45:52'),
(96, 3, 'post:hod/timetable/1/check-teacher-conflicts', NULL, NULL, NULL, NULL, '45.123.220.139', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 03:49:05', '2026-06-07 03:49:05'),
(97, 3, 'post:hod/timetable/1/slots', NULL, NULL, NULL, NULL, '45.123.220.139', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 03:49:18', '2026-06-07 03:49:18'),
(98, 3, 'post:hod/timetable/1/slots', NULL, NULL, NULL, NULL, '45.123.220.139', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 03:49:37', '2026-06-07 03:49:37'),
(99, 3, 'post:hod/timetable/1/check-teacher-conflicts', NULL, NULL, NULL, NULL, '45.123.220.139', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 03:50:47', '2026-06-07 03:50:47'),
(100, 3, 'post:hod/timetable/1/slots', NULL, NULL, NULL, NULL, '45.123.220.139', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 03:50:51', '2026-06-07 03:50:51'),
(101, 3, 'post:hod/timetable/1/slots', NULL, NULL, NULL, NULL, '45.123.220.139', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 03:50:59', '2026-06-07 03:50:59'),
(102, 3, 'post:hod/timetable/1/check-teacher-conflicts', NULL, NULL, NULL, NULL, '45.123.220.139', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 03:51:18', '2026-06-07 03:51:18'),
(103, 3, 'post:hod/timetable/1/slots', NULL, NULL, NULL, NULL, '45.123.220.139', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 03:51:21', '2026-06-07 03:51:21'),
(104, 3, 'post:hod/timetable/1/slots', NULL, NULL, NULL, NULL, '45.123.220.139', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 03:51:26', '2026-06-07 03:51:26'),
(105, 3, 'post:hod/timetable/1/check-teacher-conflicts', NULL, NULL, NULL, NULL, '45.123.220.139', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 03:51:33', '2026-06-07 03:51:33'),
(106, 3, 'delete:hod/timetable/1/slots/2', NULL, NULL, NULL, NULL, '45.123.220.139', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 03:52:51', '2026-06-07 03:52:51'),
(107, 3, 'put:hod/timetable/1', NULL, NULL, NULL, NULL, '45.123.220.139', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 03:52:55', '2026-06-07 03:52:55'),
(108, 3, 'delete:hod/timetable/1', NULL, NULL, NULL, NULL, '45.123.220.139', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 03:53:24', '2026-06-07 03:53:24'),
(109, 3, 'post:hod/timetable', NULL, NULL, NULL, NULL, '45.123.220.139', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 03:53:42', '2026-06-07 03:53:42'),
(110, 3, 'post:hod/timetable/2/check-teacher-conflicts', NULL, NULL, NULL, NULL, '45.123.220.139', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 03:53:59', '2026-06-07 03:53:59'),
(111, 3, 'post:hod/timetable/2/slots', NULL, NULL, NULL, NULL, '45.123.220.139', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 03:54:01', '2026-06-07 03:54:01'),
(112, 3, 'post:hod/timetable/2/slots', NULL, NULL, NULL, NULL, '45.123.220.139', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 03:54:10', '2026-06-07 03:54:10'),
(113, 3, 'put:hod/timetable/2', NULL, NULL, NULL, NULL, '45.123.220.139', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 03:54:16', '2026-06-07 03:54:16'),
(114, 3, 'post:hod/timetable', NULL, NULL, NULL, NULL, '45.123.220.119', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 04:01:12', '2026-06-07 04:01:12'),
(115, 3, 'post:hod/timetable/3/check-teacher-conflicts', NULL, NULL, NULL, NULL, '45.123.220.119', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 04:01:20', '2026-06-07 04:01:20'),
(116, 3, 'post:hod/timetable/3/slots', NULL, NULL, NULL, NULL, '45.123.220.119', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 04:01:25', '2026-06-07 04:01:25'),
(117, 3, 'post:hod/timetable/3/check-teacher-conflicts', NULL, NULL, NULL, NULL, '45.123.220.119', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 04:01:31', '2026-06-07 04:01:31'),
(118, 3, 'post:hod/timetable/3/slots', NULL, NULL, NULL, NULL, '45.123.220.119', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 04:01:37', '2026-06-07 04:01:37'),
(119, 3, 'delete:hod/timetable/2', NULL, NULL, NULL, NULL, '45.123.220.119', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 04:01:56', '2026-06-07 04:01:56'),
(120, 3, 'post:hod/timetable/3/check-teacher-conflicts', NULL, NULL, NULL, NULL, '45.123.220.119', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 04:03:11', '2026-06-07 04:03:11'),
(121, 3, 'post:hod/timetable/3/slots', NULL, NULL, NULL, NULL, '45.123.220.119', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 04:03:19', '2026-06-07 04:03:19'),
(122, 3, 'put:hod/timetable/3', NULL, NULL, NULL, NULL, '45.123.220.119', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 04:03:27', '2026-06-07 04:03:27'),
(123, 3, 'post:hod/timetable/3/check-teacher-conflicts', NULL, NULL, NULL, NULL, '45.123.220.119', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 04:03:48', '2026-06-07 04:03:48'),
(124, 3, 'post:hod/timetable/3/check-teacher-conflicts', NULL, NULL, NULL, NULL, '45.123.220.119', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 04:04:07', '2026-06-07 04:04:07'),
(125, 3, 'post:hod/timetable/3/slots', NULL, NULL, NULL, NULL, '45.123.220.119', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 04:04:11', '2026-06-07 04:04:11'),
(126, 3, 'logout', NULL, NULL, NULL, NULL, '45.123.220.119', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 04:08:42', '2026-06-07 04:08:42'),
(127, 2, 'login', NULL, NULL, NULL, NULL, '45.123.220.119', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 04:08:52', '2026-06-07 04:08:52'),
(128, 2, 'post:login', NULL, NULL, NULL, NULL, '45.123.220.119', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 04:08:52', '2026-06-07 04:08:52'),
(129, 2, 'logout', NULL, NULL, NULL, NULL, '45.64.163.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 04:46:14', '2026-06-07 04:46:14'),
(130, 3, 'login', NULL, NULL, NULL, NULL, '45.64.163.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 04:46:30', '2026-06-07 04:46:30'),
(131, 3, 'post:login', NULL, NULL, NULL, NULL, '45.64.163.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 04:46:30', '2026-06-07 04:46:30'),
(132, 3, 'delete:hod/timetable/3/slots/8', NULL, NULL, NULL, NULL, '45.64.163.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 04:46:46', '2026-06-07 04:46:46'),
(133, 3, 'put:hod/timetable/3', NULL, NULL, NULL, NULL, '45.64.163.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 04:46:50', '2026-06-07 04:46:50'),
(134, 3, 'post:hod/timetable/3/check-teacher-conflicts', NULL, NULL, NULL, NULL, '45.64.163.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 04:46:56', '2026-06-07 04:46:56'),
(135, 3, 'post:hod/timetable/3/slots', NULL, NULL, NULL, NULL, '45.64.163.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 04:47:03', '2026-06-07 04:47:03'),
(136, 3, 'post:hod/timetable/3/check-teacher-conflicts', NULL, NULL, NULL, NULL, '45.64.163.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 04:47:07', '2026-06-07 04:47:07'),
(137, 3, 'post:hod/timetable/3/slots', NULL, NULL, NULL, NULL, '45.64.163.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 04:47:11', '2026-06-07 04:47:11'),
(138, 3, 'put:hod/timetable/3', NULL, NULL, NULL, NULL, '45.64.163.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 04:47:21', '2026-06-07 04:47:21'),
(139, 3, 'post:hod/notices', NULL, NULL, NULL, NULL, '45.64.163.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 04:55:06', '2026-06-07 04:55:06'),
(140, 3, 'logout', NULL, NULL, NULL, NULL, '45.64.163.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 04:55:30', '2026-06-07 04:55:30'),
(141, 4, 'login', NULL, NULL, NULL, NULL, '45.64.163.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 04:55:40', '2026-06-07 04:55:40'),
(142, 4, 'post:login', NULL, NULL, NULL, NULL, '45.64.163.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 04:55:40', '2026-06-07 04:55:40'),
(143, 4, 'logout', NULL, NULL, NULL, NULL, '45.64.163.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 04:56:09', '2026-06-07 04:56:09'),
(144, 2, 'login', NULL, NULL, NULL, NULL, '45.64.163.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 04:56:18', '2026-06-07 04:56:18'),
(145, 2, 'post:login', NULL, NULL, NULL, NULL, '45.64.163.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 04:56:18', '2026-06-07 04:56:18'),
(146, 2, 'logout', NULL, NULL, NULL, NULL, '45.123.220.134', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 05:37:47', '2026-06-07 05:37:47'),
(147, 3, 'login', NULL, NULL, NULL, NULL, '45.123.220.134', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 05:38:04', '2026-06-07 05:38:04'),
(148, 3, 'post:login', NULL, NULL, NULL, NULL, '45.123.220.134', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 05:38:04', '2026-06-07 05:38:04'),
(149, 3, 'post:hod/timetable', NULL, NULL, NULL, NULL, '45.123.220.134', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 05:38:51', '2026-06-07 05:38:51'),
(150, 3, 'delete:hod/timetable/4', NULL, NULL, NULL, NULL, '45.123.220.134', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 05:39:04', '2026-06-07 05:39:04'),
(151, 3, 'logout', NULL, NULL, NULL, NULL, '45.123.220.134', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 05:46:14', '2026-06-07 05:46:14'),
(152, 4, 'login', NULL, NULL, NULL, NULL, '45.123.220.134', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 05:46:21', '2026-06-07 05:46:21'),
(153, 4, 'post:login', NULL, NULL, NULL, NULL, '45.123.220.134', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 05:46:21', '2026-06-07 05:46:21'),
(154, 4, 'logout', NULL, NULL, NULL, NULL, '45.123.220.134', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 05:46:46', '2026-06-07 05:46:46'),
(155, 2, 'login', NULL, NULL, NULL, NULL, '45.123.220.134', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 05:47:25', '2026-06-07 05:47:25'),
(156, 2, 'post:login', NULL, NULL, NULL, NULL, '45.123.220.134', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 05:47:25', '2026-06-07 05:47:25'),
(157, 2, 'logout', NULL, NULL, NULL, NULL, '45.123.220.134', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 05:49:53', '2026-06-07 05:49:53'),
(158, 3, 'login', NULL, NULL, NULL, NULL, '45.123.220.134', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 05:50:16', '2026-06-07 05:50:16'),
(159, 3, 'post:login', NULL, NULL, NULL, NULL, '45.123.220.134', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 05:50:16', '2026-06-07 05:50:16'),
(160, 3, 'post:hod/timetable/3/check-teacher-conflicts', NULL, NULL, NULL, NULL, '45.123.220.134', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 05:50:48', '2026-06-07 05:50:48'),
(161, 3, 'post:hod/timetable/3/slots', NULL, NULL, NULL, NULL, '45.123.220.134', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 05:50:56', '2026-06-07 05:50:56'),
(162, 2, 'login', NULL, NULL, NULL, NULL, '45.123.220.134', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36', '2026-06-07 05:53:38', '2026-06-07 05:53:38'),
(163, 2, 'post:login', NULL, NULL, NULL, NULL, '45.123.220.134', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36', '2026-06-07 05:53:38', '2026-06-07 05:53:38'),
(164, 3, 'put:hod/timetable/3', NULL, NULL, NULL, NULL, '45.123.220.134', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 05:54:25', '2026-06-07 05:54:25'),
(165, 3, 'put:hod/timetable/3', NULL, NULL, NULL, NULL, '45.123.220.134', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 05:54:47', '2026-06-07 05:54:47'),
(166, 3, 'post:hod/timetable/3/check-teacher-conflicts', NULL, NULL, NULL, NULL, '45.123.220.134', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 05:57:17', '2026-06-07 05:57:17'),
(167, 3, 'post:hod/timetable/3/slots', NULL, NULL, NULL, NULL, '45.123.220.134', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 05:57:17', '2026-06-07 05:57:17'),
(168, 3, 'post:hod/timetable/3/slots', NULL, NULL, NULL, NULL, '45.123.220.134', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 05:57:24', '2026-06-07 05:57:24'),
(169, 3, 'post:hod/timetable/3/slots', NULL, NULL, NULL, NULL, '45.123.220.134', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 05:57:41', '2026-06-07 05:57:41'),
(170, 2, 'post:notifications/read-all', NULL, NULL, NULL, NULL, '45.123.220.134', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36', '2026-06-07 05:59:08', '2026-06-07 05:59:08'),
(171, 3, 'delete:hod/timetable/3', NULL, NULL, NULL, NULL, '45.123.220.134', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 06:26:04', '2026-06-07 06:26:04'),
(172, 3, 'post:hod/timetable', NULL, NULL, NULL, NULL, '45.123.220.134', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 06:26:16', '2026-06-07 06:26:16'),
(173, 3, 'post:hod/timetable/5/check-teacher-conflicts', NULL, NULL, NULL, NULL, '45.123.220.134', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 06:26:31', '2026-06-07 06:26:31'),
(174, 3, 'post:hod/timetable/5/check-teacher-conflicts', NULL, NULL, NULL, NULL, '45.123.220.134', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 06:26:54', '2026-06-07 06:26:54'),
(175, 3, 'put:hod/timetable/5', NULL, NULL, NULL, NULL, '45.123.220.134', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 06:28:14', '2026-06-07 06:28:14'),
(176, 3, 'post:hod/timetable/5/check-teacher-conflicts', NULL, NULL, NULL, NULL, '45.123.220.134', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 06:28:30', '2026-06-07 06:28:30'),
(177, 3, 'put:hod/timetable/5', NULL, NULL, NULL, NULL, '45.123.220.134', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 06:28:34', '2026-06-07 06:28:34'),
(178, 3, 'post:hod/timetable/5/check-teacher-conflicts', NULL, NULL, NULL, NULL, '45.123.220.134', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 06:29:38', '2026-06-07 06:29:38'),
(179, 3, 'post:hod/timetable/5/check-teacher-conflicts', NULL, NULL, NULL, NULL, '45.123.220.134', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 06:59:59', '2026-06-07 06:59:59'),
(180, 3, 'post:hod/timetable/5/check-teacher-conflicts', NULL, NULL, NULL, NULL, '45.123.220.134', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 07:00:04', '2026-06-07 07:00:04'),
(181, 3, 'post:hod/timetable/5/check-teacher-conflicts', NULL, NULL, NULL, NULL, '45.123.220.134', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-07 07:00:28', '2026-06-07 07:00:28'),
(182, 2, 'login', NULL, NULL, NULL, NULL, '150.107.106.55', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36', '2026-06-07 13:26:04', '2026-06-07 13:26:04'),
(183, 2, 'post:login', NULL, NULL, NULL, NULL, '150.107.106.55', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36', '2026-06-07 13:26:04', '2026-06-07 13:26:04'),
(184, 2, 'logout', NULL, NULL, NULL, NULL, '45.123.220.130', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36', '2026-06-07 14:57:53', '2026-06-07 14:57:53'),
(185, 1, 'login', NULL, NULL, NULL, NULL, '45.123.220.130', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36', '2026-06-07 14:58:20', '2026-06-07 14:58:20'),
(186, 1, 'post:login', NULL, NULL, NULL, NULL, '45.123.220.130', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36', '2026-06-07 14:58:20', '2026-06-07 14:58:20'),
(187, 1, 'login', NULL, NULL, NULL, NULL, '45.123.220.130', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36', '2026-06-07 14:58:36', '2026-06-07 14:58:36'),
(188, 1, 'post:login', NULL, NULL, NULL, NULL, '45.123.220.130', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36', '2026-06-07 14:58:36', '2026-06-07 14:58:36'),
(189, 1, 'login', NULL, NULL, NULL, NULL, '45.123.220.130', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36', '2026-06-07 14:59:19', '2026-06-07 14:59:19'),
(190, 1, 'post:login', NULL, NULL, NULL, NULL, '45.123.220.130', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36', '2026-06-07 14:59:19', '2026-06-07 14:59:19'),
(191, 1, 'login', NULL, NULL, NULL, NULL, '45.123.220.130', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36', '2026-06-07 14:59:47', '2026-06-07 14:59:47'),
(192, 1, 'post:login', NULL, NULL, NULL, NULL, '45.123.220.130', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36', '2026-06-07 14:59:47', '2026-06-07 14:59:47'),
(193, 1, 'post:admin/departments', NULL, NULL, NULL, NULL, '45.123.220.130', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36', '2026-06-07 15:00:58', '2026-06-07 15:00:58'),
(194, 1, 'post:admin/departments', NULL, NULL, NULL, NULL, '45.123.220.130', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36', '2026-06-07 15:01:56', '2026-06-07 15:01:56'),
(195, 1, 'post:admin/departments', NULL, NULL, NULL, NULL, '45.123.220.130', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36', '2026-06-07 15:02:24', '2026-06-07 15:02:24'),
(196, 1, 'post:admin/departments', NULL, NULL, NULL, NULL, '45.123.220.130', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36', '2026-06-07 15:02:52', '2026-06-07 15:02:52'),
(197, 1, 'put:admin/users/3', NULL, NULL, NULL, NULL, '45.123.220.130', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36', '2026-06-07 15:08:24', '2026-06-07 15:08:24'),
(198, 1, 'login', NULL, NULL, NULL, NULL, '163.47.148.93', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-09 03:41:10', '2026-06-09 03:41:10'),
(199, 1, 'post:login', NULL, NULL, NULL, NULL, '163.47.148.93', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-09 03:41:10', '2026-06-09 03:41:10'),
(200, 1, 'delete:admin/executives/2', NULL, NULL, NULL, NULL, '163.47.148.93', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-09 03:42:37', '2026-06-09 03:42:37'),
(201, 1, 'login', NULL, NULL, NULL, NULL, '45.64.163.154', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36', '2026-06-09 13:56:09', '2026-06-09 13:56:09'),
(202, 1, 'post:login', NULL, NULL, NULL, NULL, '45.64.163.154', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36', '2026-06-09 13:56:09', '2026-06-09 13:56:09'),
(203, 1, 'login', NULL, NULL, NULL, NULL, '163.47.148.180', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-10 02:28:13', '2026-06-10 02:28:13'),
(204, 1, 'post:login', NULL, NULL, NULL, NULL, '163.47.148.180', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-10 02:28:13', '2026-06-10 02:28:13'),
(205, 1, 'logout', NULL, NULL, NULL, NULL, '163.47.148.180', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-10 02:30:25', '2026-06-10 02:30:25'),
(206, 2, 'login', NULL, NULL, NULL, NULL, '163.47.148.180', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-10 02:30:40', '2026-06-10 02:30:40'),
(207, 2, 'post:login', NULL, NULL, NULL, NULL, '163.47.148.180', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-10 02:30:40', '2026-06-10 02:30:40'),
(208, 2, 'logout', NULL, NULL, NULL, NULL, '163.47.148.180', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-10 02:32:37', '2026-06-10 02:32:37'),
(209, 3, 'login', NULL, NULL, NULL, NULL, '163.47.148.180', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-10 02:32:58', '2026-06-10 02:32:58'),
(210, 3, 'post:login', NULL, NULL, NULL, NULL, '163.47.148.180', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-10 02:32:58', '2026-06-10 02:32:58'),
(211, 3, 'post:hod/timetable/5/check-teacher-conflicts', NULL, NULL, NULL, NULL, '163.47.148.180', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-10 02:33:38', '2026-06-10 02:33:38'),
(212, 3, 'put:hod/timetable/5', NULL, NULL, NULL, NULL, '163.47.148.180', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-10 02:33:42', '2026-06-10 02:33:42'),
(213, 3, 'post:hod/timetable/5/check-teacher-conflicts', NULL, NULL, NULL, NULL, '163.47.148.180', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-10 02:34:00', '2026-06-10 02:34:00'),
(214, 3, 'put:hod/timetable/5', NULL, NULL, NULL, NULL, '163.47.148.180', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-10 02:34:05', '2026-06-10 02:34:05'),
(215, 3, 'post:hod/timetable/5/check-teacher-conflicts', NULL, NULL, NULL, NULL, '163.47.148.180', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-10 02:34:18', '2026-06-10 02:34:18');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `model_type`, `model_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES
(216, 3, 'put:hod/timetable/5', NULL, NULL, NULL, NULL, '163.47.148.180', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-10 02:34:24', '2026-06-10 02:34:24'),
(217, 3, 'post:hod/timetable', NULL, NULL, NULL, NULL, '163.47.148.180', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-10 02:34:47', '2026-06-10 02:34:47'),
(218, 3, 'post:hod/timetable/5/check-teacher-conflicts', NULL, NULL, NULL, NULL, '163.47.148.180', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-10 02:35:12', '2026-06-10 02:35:12'),
(219, 3, 'put:hod/timetable/5', NULL, NULL, NULL, NULL, '163.47.148.180', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-10 02:35:18', '2026-06-10 02:35:18'),
(220, 3, 'delete:hod/timetable/6', NULL, NULL, NULL, NULL, '163.47.148.180', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-10 02:35:40', '2026-06-10 02:35:40'),
(221, 3, 'post:hod/timetable/5/check-teacher-conflicts', NULL, NULL, NULL, NULL, '163.47.148.180', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-10 02:35:58', '2026-06-10 02:35:58'),
(222, 3, 'put:hod/timetable/5', NULL, NULL, NULL, NULL, '163.47.148.180', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-10 02:36:06', '2026-06-10 02:36:06'),
(223, 3, 'post:hod/timetable/5/check-teacher-conflicts', NULL, NULL, NULL, NULL, '163.47.148.180', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-10 02:36:32', '2026-06-10 02:36:32'),
(224, 3, 'put:hod/timetable/5', NULL, NULL, NULL, NULL, '163.47.148.180', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-10 02:36:45', '2026-06-10 02:36:45'),
(225, 3, 'put:hod/timetable/5', NULL, NULL, NULL, NULL, '163.47.148.180', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-10 02:36:50', '2026-06-10 02:36:50'),
(226, 3, 'post:hod/timetable/5/check-teacher-conflicts', NULL, NULL, NULL, NULL, '163.47.148.180', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-10 02:37:01', '2026-06-10 02:37:01'),
(227, 3, 'put:hod/timetable/5', NULL, NULL, NULL, NULL, '163.47.148.180', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-10 02:37:05', '2026-06-10 02:37:05'),
(228, 3, 'post:hod/timetable/5/check-teacher-conflicts', NULL, NULL, NULL, NULL, '163.47.148.180', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-10 02:37:18', '2026-06-10 02:37:18'),
(229, 3, 'post:hod/timetable/5/check-teacher-conflicts', NULL, NULL, NULL, NULL, '163.47.148.180', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-10 02:37:45', '2026-06-10 02:37:45'),
(230, 3, 'put:hod/timetable/5', NULL, NULL, NULL, NULL, '163.47.148.180', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-10 02:37:58', '2026-06-10 02:37:58'),
(231, 3, 'post:hod/timetable/5/check-teacher-conflicts', NULL, NULL, NULL, NULL, '163.47.148.180', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-10 02:38:22', '2026-06-10 02:38:22'),
(232, 3, 'post:hod/timetable/5/check-teacher-conflicts', NULL, NULL, NULL, NULL, '163.47.148.180', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-10 02:38:32', '2026-06-10 02:38:32'),
(233, 3, 'put:hod/timetable/5', NULL, NULL, NULL, NULL, '163.47.148.180', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-10 02:38:35', '2026-06-10 02:38:35'),
(234, 3, 'logout', NULL, NULL, NULL, NULL, '163.47.148.180', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-10 02:49:19', '2026-06-10 02:49:19'),
(235, 1, 'login', NULL, NULL, NULL, NULL, '163.47.148.180', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-10 02:49:47', '2026-06-10 02:49:47'),
(236, 1, 'post:login', NULL, NULL, NULL, NULL, '163.47.148.180', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-10 02:49:47', '2026-06-10 02:49:47'),
(237, 1, 'logout', NULL, NULL, NULL, NULL, '27.34.68.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-10 02:57:27', '2026-06-10 02:57:27'),
(238, 3, 'login', NULL, NULL, NULL, NULL, '27.34.68.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-10 02:57:37', '2026-06-10 02:57:37'),
(239, 3, 'post:login', NULL, NULL, NULL, NULL, '27.34.68.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-10 02:57:37', '2026-06-10 02:57:37'),
(240, 3, 'logout', NULL, NULL, NULL, NULL, '27.34.68.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-10 03:03:24', '2026-06-10 03:03:24'),
(241, 2, 'login', NULL, NULL, NULL, NULL, '27.34.68.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-10 03:03:40', '2026-06-10 03:03:40'),
(242, 2, 'post:login', NULL, NULL, NULL, NULL, '27.34.68.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-10 03:03:40', '2026-06-10 03:03:40'),
(243, 2, 'logout', NULL, NULL, NULL, NULL, '163.47.148.180', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-10 03:09:16', '2026-06-10 03:09:16'),
(244, 1, 'login', NULL, NULL, NULL, NULL, '163.47.148.180', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-10 03:09:32', '2026-06-10 03:09:32'),
(245, 1, 'post:login', NULL, NULL, NULL, NULL, '163.47.148.180', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-10 03:09:32', '2026-06-10 03:09:32'),
(246, 1, 'login', NULL, NULL, NULL, NULL, '45.123.220.87', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36', '2026-06-10 14:05:37', '2026-06-10 14:05:37'),
(247, 1, 'post:login', NULL, NULL, NULL, NULL, '45.123.220.87', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36', '2026-06-10 14:05:37', '2026-06-10 14:05:37'),
(248, 1, 'login', NULL, NULL, NULL, NULL, '45.123.220.87', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36', '2026-06-10 14:06:17', '2026-06-10 14:06:17'),
(249, 1, 'post:login', NULL, NULL, NULL, NULL, '45.123.220.87', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36', '2026-06-10 14:06:18', '2026-06-10 14:06:18'),
(250, 1, 'login', NULL, NULL, NULL, NULL, '45.123.220.87', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36', '2026-06-10 14:06:29', '2026-06-10 14:06:29'),
(251, 1, 'post:login', NULL, NULL, NULL, NULL, '45.123.220.87', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36', '2026-06-10 14:06:29', '2026-06-10 14:06:29'),
(252, 1, 'login', NULL, NULL, NULL, NULL, '45.123.220.151', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-10 15:50:30', '2026-06-10 15:50:30'),
(253, 1, 'post:login', NULL, NULL, NULL, NULL, '45.123.220.151', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-10 15:50:30', '2026-06-10 15:50:30'),
(254, 1, 'login', NULL, NULL, NULL, NULL, '45.123.220.151', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-10 15:50:47', '2026-06-10 15:50:47'),
(255, 1, 'post:login', NULL, NULL, NULL, NULL, '45.123.220.151', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-10 15:50:47', '2026-06-10 15:50:47'),
(256, 1, 'logout', NULL, NULL, NULL, NULL, '45.123.220.151', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-10 15:54:48', '2026-06-10 15:54:48'),
(257, 1, 'login', NULL, NULL, NULL, NULL, '45.123.220.151', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-10 15:54:56', '2026-06-10 15:54:56'),
(258, 1, 'post:login', NULL, NULL, NULL, NULL, '45.123.220.151', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-10 15:54:56', '2026-06-10 15:54:56');

-- --------------------------------------------------------

--
-- Table structure for table `banners`
--

CREATE TABLE `banners` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `subtitle` varchar(255) DEFAULT NULL,
  `image` varchar(255) NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `button_text` varchar(50) DEFAULT NULL,
  `button_link` varchar(255) DEFAULT NULL,
  `order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `banners`
--

INSERT INTO `banners` (`id`, `title`, `subtitle`, `image`, `link`, `button_text`, `button_link`, `order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'ADMISSION OPEN FOR 2083/84', 'CTEVT', 'banners/UzdZJMELB1zMcPmFgHYVCoVTAThpPWTjG4Vp7ghh.jpg', NULL, NULL, NULL, 0, 1, '2026-06-03 05:36:31', '2026-06-03 08:00:29');

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
-- Table structure for table `communications`
--

CREATE TABLE `communications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `sender_id` bigint(20) UNSIGNED NOT NULL,
  `receiver_id` bigint(20) UNSIGNED NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `syllabus` varchar(255) DEFAULT NULL,
  `seat_capacity` int(10) UNSIGNED NOT NULL DEFAULT 40,
  `hod_id` bigint(20) UNSIGNED DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `name`, `code`, `slug`, `description`, `photo`, `syllabus`, `seat_capacity`, `hod_id`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Information Technology', 'IT', 'department-of-information-technology', NULL, NULL, NULL, 40, 3, 1, '2026-06-05 19:38:47', '2026-06-06 07:21:10', NULL),
(2, 'Civil Engineering', 'CE', 'civil-engineering', NULL, NULL, NULL, 40, NULL, 1, '2026-06-07 15:00:58', '2026-06-07 15:00:58', NULL),
(3, 'Electronic Engineering', 'Ex', 'electronic-engineering', NULL, NULL, NULL, 40, NULL, 1, '2026-06-07 15:01:56', '2026-06-07 15:01:56', NULL),
(4, 'Electrical Engineering', 'EE', 'electrical-engineering', NULL, NULL, NULL, 40, NULL, 1, '2026-06-07 15:02:24', '2026-06-07 15:02:24', NULL),
(5, 'Mechanical Engineering', 'Me', 'mechanical-engineering', NULL, NULL, NULL, 40, NULL, 1, '2026-06-07 15:02:52', '2026-06-07 15:02:52', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `downloads`
--

CREATE TABLE `downloads` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `file_type` varchar(20) DEFAULT NULL,
  `file_size` bigint(20) UNSIGNED DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL,
  `department_id` bigint(20) UNSIGNED DEFAULT NULL,
  `subject_id` bigint(20) UNSIGNED DEFAULT NULL,
  `program_id` bigint(20) UNSIGNED DEFAULT NULL,
  `semester` int(10) UNSIGNED DEFAULT NULL,
  `is_public` tinyint(1) NOT NULL DEFAULT 1,
  `visibility` enum('public','students','private') NOT NULL DEFAULT 'students',
  `uploaded_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `exams`
--

CREATE TABLE `exams` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `academic_session_id` bigint(20) UNSIGNED NOT NULL,
  `department_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `category` varchar(255) NOT NULL DEFAULT 'ctevt_final',
  `assessment_number` tinyint(3) UNSIGNED DEFAULT NULL,
  `assessment_full_marks` decimal(6,2) DEFAULT NULL,
  `assessment_pass_marks` decimal(6,2) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('upcoming','ongoing','completed','results_published') NOT NULL DEFAULT 'upcoming',
  `marks_open` tinyint(1) NOT NULL DEFAULT 0,
  `is_published` tinyint(1) NOT NULL DEFAULT 0,
  `published_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `exams`
--

INSERT INTO `exams` (`id`, `academic_session_id`, `department_id`, `name`, `type`, `category`, `assessment_number`, `assessment_full_marks`, `assessment_pass_marks`, `start_date`, `end_date`, `status`, `marks_open`, `is_published`, `published_at`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, 'Montly assesment 1', 'theory', 'monthly_assessment', 1, 20.00, 8.00, '2026-06-06', '2026-06-06', 'results_published', 0, 1, '2026-06-06 07:35:35', '2026-06-06 07:30:33', '2026-06-06 07:35:35', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `exam_program`
--

CREATE TABLE `exam_program` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `exam_id` bigint(20) UNSIGNED NOT NULL,
  `program_id` bigint(20) UNSIGNED NOT NULL,
  `semester` tinyint(3) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `exam_program`
--

INSERT INTO `exam_program` (`id`, `exam_id`, `program_id`, `semester`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, '2026-06-06 07:30:33', '2026-06-06 07:30:33');

-- --------------------------------------------------------

--
-- Table structure for table `exam_subject_marking_schemes`
--

CREATE TABLE `exam_subject_marking_schemes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `exam_id` bigint(20) UNSIGNED NOT NULL,
  `subject_id` bigint(20) UNSIGNED NOT NULL,
  `full_marks_internal_theory` decimal(6,2) NOT NULL DEFAULT 0.00,
  `pass_marks_internal_theory` decimal(6,2) NOT NULL DEFAULT 0.00,
  `full_marks_external_theory` decimal(6,2) NOT NULL DEFAULT 0.00,
  `pass_marks_external_theory` decimal(6,2) NOT NULL DEFAULT 0.00,
  `full_marks_internal_practical` decimal(6,2) NOT NULL DEFAULT 0.00,
  `pass_marks_internal_practical` decimal(6,2) NOT NULL DEFAULT 0.00,
  `full_marks_external_practical` decimal(6,2) NOT NULL DEFAULT 0.00,
  `pass_marks_external_practical` decimal(6,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `executives`
--

CREATE TABLE `executives` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'principal',
  `designation` varchar(255) DEFAULT NULL,
  `start_date_bs` varchar(255) DEFAULT NULL,
  `end_date_bs` varchar(255) DEFAULT NULL,
  `is_current` tinyint(1) NOT NULL DEFAULT 0,
  `avatar` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `facilities`
--

CREATE TABLE `facilities` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `category` varchar(255) NOT NULL,
  `department_id` bigint(20) UNSIGNED DEFAULT NULL,
  `program_id` bigint(20) UNSIGNED DEFAULT NULL,
  `description` text DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`images`)),
  `documents` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`documents`)),
  `videos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`videos`)),
  `capacity` int(11) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
-- Table structure for table `marks`
--

CREATE TABLE `marks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `exam_id` bigint(20) UNSIGNED NOT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `subject_id` bigint(20) UNSIGNED NOT NULL,
  `program_id` bigint(20) UNSIGNED NOT NULL,
  `teacher_id` bigint(20) UNSIGNED DEFAULT NULL,
  `semester` tinyint(3) UNSIGNED NOT NULL,
  `internal_theory_marks` decimal(5,2) DEFAULT NULL,
  `external_theory_marks` decimal(5,2) DEFAULT NULL,
  `internal_practical_marks` decimal(5,2) DEFAULT NULL,
  `external_practical_marks` decimal(5,2) DEFAULT NULL,
  `assessment_attendance_percent` decimal(5,2) DEFAULT NULL,
  `assessment_full_marks` decimal(6,2) DEFAULT NULL,
  `assessment_pass_marks` decimal(6,2) DEFAULT NULL,
  `assessment_obtained_marks` decimal(6,2) DEFAULT NULL,
  `marks_obtained` decimal(6,2) DEFAULT NULL,
  `total_marks` decimal(6,2) DEFAULT NULL,
  `pass_marks` decimal(6,2) DEFAULT NULL,
  `ctevt_full_marks_internal_theory` decimal(6,2) DEFAULT NULL,
  `ctevt_pass_marks_internal_theory` decimal(6,2) DEFAULT NULL,
  `ctevt_full_marks_external_theory` decimal(6,2) DEFAULT NULL,
  `ctevt_pass_marks_external_theory` decimal(6,2) DEFAULT NULL,
  `ctevt_full_marks_internal_practical` decimal(6,2) DEFAULT NULL,
  `ctevt_pass_marks_internal_practical` decimal(6,2) DEFAULT NULL,
  `ctevt_full_marks_external_practical` decimal(6,2) DEFAULT NULL,
  `ctevt_pass_marks_external_practical` decimal(6,2) DEFAULT NULL,
  `exam_attendance_date` date DEFAULT NULL,
  `was_present_on_exam_date` tinyint(1) NOT NULL DEFAULT 1,
  `attendance_remarks` text DEFAULT NULL,
  `is_absent` tinyint(1) NOT NULL DEFAULT 0,
  `is_withheld` tinyint(1) NOT NULL DEFAULT 0,
  `is_delayed` tinyint(1) NOT NULL DEFAULT 0,
  `delay_reason` text DEFAULT NULL,
  `status` enum('draft','submitted','approved','published') NOT NULL DEFAULT 'draft',
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `marks`
--

INSERT INTO `marks` (`id`, `exam_id`, `student_id`, `subject_id`, `program_id`, `teacher_id`, `semester`, `internal_theory_marks`, `external_theory_marks`, `internal_practical_marks`, `external_practical_marks`, `assessment_attendance_percent`, `assessment_full_marks`, `assessment_pass_marks`, `assessment_obtained_marks`, `marks_obtained`, `total_marks`, `pass_marks`, `ctevt_full_marks_internal_theory`, `ctevt_pass_marks_internal_theory`, `ctevt_full_marks_external_theory`, `ctevt_pass_marks_external_theory`, `ctevt_full_marks_internal_practical`, `ctevt_pass_marks_internal_practical`, `ctevt_full_marks_external_practical`, `ctevt_pass_marks_external_practical`, `exam_attendance_date`, `was_present_on_exam_date`, `attendance_remarks`, `is_absent`, `is_withheld`, `is_delayed`, `delay_reason`, `status`, `remarks`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 1, NULL, 1, NULL, NULL, NULL, NULL, NULL, 20.00, 8.00, 15.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 0, 0, 0, NULL, 'approved', NULL, '2026-06-06 07:31:19', '2026-06-06 07:31:39');

-- --------------------------------------------------------

--
-- Table structure for table `media`
--

CREATE TABLE `media` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_type` varchar(255) NOT NULL,
  `mime_type` varchar(255) DEFAULT NULL,
  `size` bigint(20) UNSIGNED DEFAULT NULL,
  `department_id` bigint(20) UNSIGNED DEFAULT NULL,
  `uploaded_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(4, '2026_04_14_000001_create_departments_table', 1),
(5, '2026_04_14_000002_create_academic_sessions_table', 1),
(6, '2026_04_14_000003_create_programs_subjects_tables', 1),
(7, '2026_04_14_000004_create_students_table', 1),
(8, '2026_04_14_000005_create_teachers_table', 1),
(9, '2026_04_14_000006_create_parents_table', 1),
(10, '2026_04_14_000007_create_alumni_table', 1),
(11, '2026_04_14_000008_create_exams_table', 1),
(12, '2026_04_14_000009_create_marks_table', 1),
(13, '2026_04_14_000010_create_attendances_table', 1),
(14, '2026_04_14_000011_create_timetables_table', 1),
(15, '2026_04_14_000012_create_notices_table', 1),
(16, '2026_04_14_000013_create_audit_logs_table', 1),
(17, '2026_04_14_000014_create_assignments_table', 1),
(18, '2026_04_14_000015_create_media_table', 1),
(19, '2026_04_14_000016_create_cms_tables', 1),
(20, '2026_04_14_000017_create_communications_table', 1),
(21, '2026_04_14_110732_create_personal_access_tokens_table', 1),
(22, '2026_04_14_110738_create_permission_tables', 1),
(23, '2026_04_15_032325_create_staff_table', 1),
(24, '2026_04_15_032326_create_site_settings_table', 1),
(25, '2026_04_15_034017_create_facilities_table', 1),
(26, '2026_04_15_035859_create_executives_table', 1),
(27, '2026_04_18_101500_create_academic_session_semesters_table', 1),
(28, '2026_04_24_000003_create_notifications_table', 1),
(29, '2026_04_24_100000_create_otps_table', 1);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 1),
(2, 'App\\Models\\User', 3),
(3, 'App\\Models\\User', 4),
(4, 'App\\Models\\User', 2);

-- --------------------------------------------------------

--
-- Table structure for table `notices`
--

CREATE TABLE `notices` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `type` enum('general','department','program','teachers','exam','news','event','ctevt') NOT NULL DEFAULT 'general',
  `department_id` bigint(20) UNSIGNED DEFAULT NULL,
  `program_id` bigint(20) UNSIGNED DEFAULT NULL,
  `semester` tinyint(3) UNSIGNED DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT 1,
  `published_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notices`
--

INSERT INTO `notices` (`id`, `title`, `slug`, `content`, `attachment`, `type`, `department_id`, `program_id`, `semester`, `created_by`, `is_published`, `published_at`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Internal Exam', 'internal-exam-1780808105', 'Internal Assesment start from 2080-02-22', 'notices/pioEXAVCIoZPfwerQb0QEKDKKJKyo6EXSVqteODm.png', 'department', 1, NULL, NULL, 3, 1, '2026-06-07 04:55:05', '2026-06-07 04:55:05', '2026-06-07 04:55:05', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `notice_attachments`
--

CREATE TABLE `notice_attachments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `notice_id` bigint(20) UNSIGNED NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_type` varchar(20) DEFAULT NULL,
  `file_size` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` char(36) NOT NULL,
  `type` varchar(255) NOT NULL,
  `notifiable_type` varchar(255) NOT NULL,
  `notifiable_id` bigint(20) UNSIGNED NOT NULL,
  `data` text NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, `created_at`, `updated_at`) VALUES
('0e3b32d3-8102-48d6-a47f-53d5244a3576', 'App\\Notifications\\ExamPublishedNotification', 'App\\Models\\User', 3, '{\"title\":\"Results published\",\"message\":\"Montly assesment 1 results are now available.\",\"action_url\":\"https:\\/\\/mmp.sital00.com.np\\/hod\\/exams\\/results\",\"action_label\":\"View results\",\"category\":\"exam\",\"icon\":\"chart-bar\",\"color\":\"emerald\",\"scope_label\":\"Information Technology\",\"occurred_at\":\"2026-06-06T07:35:35+00:00\"}', '2026-06-06 07:47:39', '2026-06-06 07:35:35', '2026-06-06 07:47:39'),
('1e9a3abb-ba6c-4a39-be5a-c0609ff954d1', 'App\\Notifications\\PortalNoticeNotification', 'App\\Models\\User', 4, '{\"title\":\"Internal Exam\",\"message\":\"Internal Assesment start from 2080-02-22\",\"action_url\":\"https:\\/\\/mmp.sital00.com.np\\/teacher\\/notices\\/1\",\"action_label\":\"Open notice\",\"category\":\"notice\",\"icon\":\"bell\",\"color\":\"blue\",\"scope_label\":\"Information Technology\",\"occurred_at\":\"2026-06-07T04:55:05+00:00\"}', NULL, '2026-06-07 04:55:05', '2026-06-07 04:55:05'),
('26976048-f87c-4803-ae81-0c404efd93f4', 'App\\Notifications\\ExamPublishedNotification', 'App\\Models\\User', 4, '{\"title\":\"Results published\",\"message\":\"Montly assesment 1 results are now available.\",\"action_url\":\"https:\\/\\/mmp.sital00.com.np\\/teacher\\/exams\",\"action_label\":\"View results\",\"category\":\"exam\",\"icon\":\"chart-bar\",\"color\":\"emerald\",\"scope_label\":\"Information Technology\",\"occurred_at\":\"2026-06-06T07:35:35+00:00\"}', NULL, '2026-06-06 07:35:35', '2026-06-06 07:35:35'),
('839b3ec8-9001-4246-b515-95e50879bb58', 'App\\Notifications\\PortalNoticeNotification', 'App\\Models\\User', 3, '{\"title\":\"Internal Exam\",\"message\":\"Internal Assesment start from 2080-02-22\",\"action_url\":\"https:\\/\\/mmp.sital00.com.np\\/hod\\/notices\\/1\",\"action_label\":\"Open notice\",\"category\":\"notice\",\"icon\":\"bell\",\"color\":\"blue\",\"scope_label\":\"Information Technology\",\"occurred_at\":\"2026-06-07T04:55:05+00:00\"}', NULL, '2026-06-07 04:55:05', '2026-06-07 04:55:05'),
('85354c0e-b342-40eb-ae23-466c57e973dc', 'App\\Notifications\\ExamPublishedNotification', 'App\\Models\\User', 1, '{\"title\":\"Results published\",\"message\":\"Montly assesment 1 results are now available.\",\"action_url\":\"https:\\/\\/mmp.sital00.com.np\\/admin\\/exams\\/1\",\"action_label\":\"View results\",\"category\":\"exam\",\"icon\":\"chart-bar\",\"color\":\"emerald\",\"scope_label\":\"Information Technology\",\"occurred_at\":\"2026-06-06T07:35:35+00:00\"}', NULL, '2026-06-06 07:35:35', '2026-06-06 07:35:35'),
('bcd2dfc3-24fc-497f-b8da-b9dea4d6a23f', 'App\\Notifications\\ExamPublishedNotification', 'App\\Models\\User', 2, '{\"title\":\"Results published\",\"message\":\"Montly assesment 1 results are now available.\",\"action_url\":\"https:\\/\\/mmp.sital00.com.np\\/student\\/marks\",\"action_label\":\"View results\",\"category\":\"exam\",\"icon\":\"chart-bar\",\"color\":\"emerald\",\"scope_label\":\"Information Technology\",\"occurred_at\":\"2026-06-06T07:35:35+00:00\"}', '2026-06-06 07:38:45', '2026-06-06 07:35:35', '2026-06-06 07:38:45'),
('ca8ac283-f429-4717-af69-05dbe9c3c424', 'App\\Notifications\\PortalNoticeNotification', 'App\\Models\\User', 2, '{\"title\":\"Internal Exam\",\"message\":\"Internal Assesment start from 2080-02-22\",\"action_url\":\"https:\\/\\/mmp.sital00.com.np\\/student\\/notices\\/1\",\"action_label\":\"Open notice\",\"category\":\"notice\",\"icon\":\"bell\",\"color\":\"blue\",\"scope_label\":\"Information Technology\",\"occurred_at\":\"2026-06-07T04:55:05+00:00\"}', '2026-06-07 05:59:08', '2026-06-07 04:55:05', '2026-06-07 05:59:08'),
('f9725f55-9fc5-4b44-87fa-b78b5be5518b', 'App\\Notifications\\PortalNoticeNotification', 'App\\Models\\User', 1, '{\"title\":\"Internal Exam\",\"message\":\"Internal Assesment start from 2080-02-22\",\"action_url\":\"https:\\/\\/mmp.sital00.com.np\\/admin\\/notices\\/1\",\"action_label\":\"Open notice\",\"category\":\"notice\",\"icon\":\"bell\",\"color\":\"blue\",\"scope_label\":\"Information Technology\",\"occurred_at\":\"2026-06-07T04:55:05+00:00\"}', NULL, '2026-06-07 04:55:05', '2026-06-07 04:55:05');

-- --------------------------------------------------------

--
-- Table structure for table `otps`
--

CREATE TABLE `otps` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `phone` varchar(255) NOT NULL,
  `otp` varchar(255) NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `expires_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pages`
--

CREATE TABLE `pages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `content` longtext DEFAULT NULL,
  `featured_image` varchar(255) DEFAULT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT 1,
  `category` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `capacity` int(11) DEFAULT NULL,
  `availability` varchar(255) DEFAULT NULL,
  `features` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `parents`
--

CREATE TABLE `parents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `occupation` varchar(255) DEFAULT NULL,
  `relation_to_student` varchar(255) NOT NULL DEFAULT 'parent',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `parent_student`
--

CREATE TABLE `parent_student` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `parent_id` bigint(20) UNSIGNED NOT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` text NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `personal_access_tokens`
--

INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(1, 'App\\Models\\User', 1, 'mobile-app', '0143166b74ae59a05d8e962e719c89a977b4ba6015a01ccf7f442a7455f658ea', '[\"*\"]', NULL, NULL, '2026-06-05 19:07:33', '2026-06-05 19:07:33'),
(2, 'App\\Models\\User', 1, 'mobile-app', '59fa2c5786ef69dbed350c34515ab16d4810ffb701ce2f61e3d129612b071c26', '[\"*\"]', NULL, NULL, '2026-06-05 19:12:25', '2026-06-05 19:12:25'),
(3, 'App\\Models\\User', 2, 'mobile-app', '240cf07d0f2c8c3be81fb38a8e1fde5af66fae6f0d738e08e9f19688c465df1c', '[\"*\"]', NULL, NULL, '2026-06-05 19:54:15', '2026-06-05 19:54:15'),
(4, 'App\\Models\\User', 2, 'mobile-app', '70c75c6e0769ce1abd829ad65726ab8b7ee4344ae3583661000eb247d68b973b', '[\"*\"]', '2026-06-05 20:09:28', NULL, '2026-06-05 19:57:32', '2026-06-05 20:09:28'),
(5, 'App\\Models\\User', 1, 'mobile-app', 'd5802bdcbc54d41b578be1413a774d6153c4b45a3c0e0894167dd2ccf9c1c81b', '[\"*\"]', NULL, NULL, '2026-06-05 20:10:29', '2026-06-05 20:10:29'),
(6, 'App\\Models\\User', 2, 'mobile-app', '800c56d3957b88db8068b98499e00fdbd50f5b90fd3c7ebac890dbba23e6a862', '[\"*\"]', '2026-06-06 01:58:05', NULL, '2026-06-05 20:10:44', '2026-06-06 01:58:05'),
(7, 'App\\Models\\User', 1, 'mobile-app', '77a44d840011c47f5f075bc79883fe4e4cbbf6c3e80796103ce895da0807def8', '[\"*\"]', NULL, NULL, '2026-06-06 01:42:09', '2026-06-06 01:42:09'),
(8, 'App\\Models\\User', 2, 'mobile-app', '53f48a079039c8f60d9682936cfc751dba73e48e67b7020cb75a1ea192005179', '[\"*\"]', '2026-06-06 01:42:55', NULL, '2026-06-06 01:42:54', '2026-06-06 01:42:55'),
(9, 'App\\Models\\User', 2, 'mobile-app', '6a6e5d03143908bfc6220fbc6670590772d62fddd522f8724c6bbae0fb54a28d', '[\"*\"]', '2026-06-06 06:48:49', NULL, '2026-06-06 01:58:30', '2026-06-06 06:48:49'),
(10, 'App\\Models\\User', 2, 'mobile-app', 'f9a6bbe6f55c4fb4f7a6a3337317ccf6754dd50924104cdd3636e7100c2bf497', '[\"*\"]', '2026-06-06 12:02:51', NULL, '2026-06-06 07:12:40', '2026-06-06 12:02:51'),
(11, 'App\\Models\\User', 2, 'mobile-app', '0621c9fc4f43c01294739c0e41f62bcf375f42992dd5ef5df3bb4009c002f8a3', '[\"*\"]', '2026-06-10 03:05:34', NULL, '2026-06-06 12:03:23', '2026-06-10 03:05:34');

-- --------------------------------------------------------

--
-- Table structure for table `programs`
--

CREATE TABLE `programs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `department_id` bigint(20) UNSIGNED NOT NULL,
  `coordinator_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `ctevt_code` varchar(50) DEFAULT NULL,
  `affiliation_type` varchar(50) NOT NULL DEFAULT 'CTEVT',
  `slug` varchar(255) NOT NULL,
  `total_semesters` tinyint(3) UNSIGNED NOT NULL DEFAULT 6,
  `duration` tinyint(3) UNSIGNED NOT NULL DEFAULT 3,
  `duration_years` tinyint(3) UNSIGNED NOT NULL DEFAULT 3,
  `description` text DEFAULT NULL,
  `eligibility` text DEFAULT NULL,
  `syllabus` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `programs`
--

INSERT INTO `programs` (`id`, `department_id`, `coordinator_id`, `name`, `code`, `ctevt_code`, `affiliation_type`, `slug`, `total_semesters`, `duration`, `duration_years`, `description`, `eligibility`, `syllabus`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, NULL, 'Department in Information Technology', 'DIT', NULL, 'CTEVT', 'department-in-information-technology', 6, 3, 3, NULL, NULL, NULL, 1, '2026-06-05 19:39:14', '2026-06-05 19:39:14', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'principal', 'web', '2026-06-03 03:10:23', '2026-06-03 03:10:23'),
(2, 'hod', 'web', '2026-06-03 03:10:23', '2026-06-03 03:10:23'),
(3, 'teacher', 'web', '2026-06-03 03:10:23', '2026-06-03 03:10:23'),
(4, 'student', 'web', '2026-06-03 03:10:23', '2026-06-03 03:10:23'),
(5, 'parent', 'web', '2026-06-03 03:10:23', '2026-06-03 03:10:23'),
(6, 'alumni', 'web', '2026-06-03 03:10:23', '2026-06-03 03:10:23'),
(7, 'admin', 'web', '2026-06-05 19:22:56', '2026-06-05 19:22:56');

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

-- --------------------------------------------------------

--
-- Table structure for table `site_settings`
--

CREATE TABLE `site_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `key` varchar(255) NOT NULL,
  `group` varchar(255) NOT NULL DEFAULT 'general',
  `label` varchar(255) DEFAULT NULL,
  `value` longtext DEFAULT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'text',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `site_settings`
--

INSERT INTO `site_settings` (`id`, `key`, `group`, `label`, `value`, `type`, `created_at`, `updated_at`) VALUES
(1, 'site_logo', 'about', 'Site Logo (Public + Admin)', 'site-settings/iZE0zeGF4I7OajPAv1NANt8NhpudwCYSuI3NOTsz.jpg', 'image', '2026-06-03 03:11:50', '2026-06-03 06:18:15'),
(2, 'college_name', 'about', 'College / Institution Full Name', 'Manmohan Memorial Polytechnic', 'text', '2026-06-03 03:11:50', '2026-06-03 08:02:43'),
(3, 'college_affiliation', 'about', 'Affiliated To (e.g. CTEVT)', 'CTEVT', 'text', '2026-06-03 03:11:50', '2026-06-03 08:02:43'),
(4, 'what_is_mmp', 'about', 'What is MMP', 'Manmohan Memorial Polytechnic is a constituent college of Manmohan Technical University, established by Koshi Province Government of Nepal in 2076 B.S.\r\n\r\nDiploma in Engineering and Pre-Diploma in Engineering courses are run in this polytechnic in partnership with Council for Technical Education and Vocational Training (CTEVT). \r\n\r\nFuture plans of MTU include running of Bachelor and Master courses in Engineering, Paramedical and other technical courses.\r\n\r\nHistory of MMP\r\n\r\nManmohan Memorial Polytechnic was the first polytechnic of its kind established in 2065 B.S. with the generous assistance of the Government of India as a result of an agreement among the Government of India, Government of Nepal and Manmohan Memorial Foundation that aims to cater employable skills to its students which is most essential for employability and further to help develop the nation. It started running Diploma in Engineering programs in partnership with Council for Technical Education and Vocational Training (CTEVT), the umbrella organization formulated for the development of TEVT in Nepal.\r\n\r\n\r\n\r\nMMP used to be an autonomous institute empowered to provide sound technical skills based on the practical knowledge to prepare the students to meet the challenges of the fast changing world. The need of our country is to provide with such an education and training to the up coming generation that could foster with creativity in the cross section of lives. MMP promises for continuous striving to fulfill the need of the society by offering programs that provides life skills to the youths of the nation to become self-dependent.\r\n\r\nA Judicious mix of the approaches (e.g. Institution-based Project oriented study, Laboratory and Workshop-centered teaching, and Practice School type of instruction) is employed for the education and training of students in Diploma courses of developing the desired ability of quality technicians most suited for executive, supervising and managing shop floor/field activities in an industry. The instructional approaches outlined as above are student-centered. The program of study includes considerable number of assignments in addition to lectures, laboratory work and workshop exercises.\r\n\r\nMMP was managed by a Board of Governors (BOG) that has a wide range of representation from Nepalese industries and industrial professionals, employing agencies, technical educators, engineering professionals, representatives of MMF, University, CTEVT, Ministry of Education, Engineering Association and MMP faculty. It is being financed by the government of Nepal, Province 1 Government, MTU, MMF and the Polytechnic itself.', 'richtext', '2026-06-03 03:11:50', '2026-06-03 08:02:43'),
(5, 'objectives', 'about', 'Objectives', 'The MMP has set the following objectives:\r\n\r\nTo produce middle level technical workforce (with technical and supervisory skills) in the engineering field to strengthen various sector of the economy.\r\nTo undertake continuing education/training program for professional and career development of the technicians/ skilled workers in the industry.\r\nTo train disadvantaged/unemployed/underemployed groups of people in specific skills through short-term modularized training programs.\r\nThe main purpose is to provide the students with hands on skills that enables them securing wage employment and/or self-employment or raise the competency levels of the students so that they do not have to remain unemployed or underemployed.', 'richtext', '2026-06-03 03:11:50', '2026-06-03 08:02:43'),
(6, 'welcome_message', 'about', 'Welcome Message', 'This website is designed to introduce you to Manmohan Memorial Polytechnic. We at MMP are confident that you will explore and attain your maximum potential with our inputs and assistance.\r\n\r\nWe provide considerable number of laboratory work, workshop exercises and assignments that will refine the skills of the students further to compete the labor market nationally and internationally.\r\n\r\nMMP is a name that is surely to be established as the pioneer and center of excellence in the technical education sector of Nepal.\r\n\r\nWe offer the latest and the most relevant curriculum designed by CTEVT for MMP involving the most experienced professionals. We provide state-of-art facilities and services to our students and faculty. We the faculty are eager to enhance your practical skills and learning experience at MMP.', 'richtext', '2026-06-03 03:11:50', '2026-06-03 08:02:43'),
(7, 'principals_message', 'about', 'Principal message', 'This website is designed to introduce you to Manmohan Memorial Polytechnic. We at MMP are confident that you will explore and attain your maximum potential with our inputs and assistance.\r\n\r\nWe provide considerable number of laboratory work, workshop exercises and assignments that will refine the skills of the students further to compete the labor market nationally and internationally.\r\n\r\nMMP is a name that is surely to be established as the pioneer and center of excellence in the technical education sector of Nepal.\r\n\r\nWe offer the latest and the most relevant curriculum designed by CTEVT for MMP involving the most experienced professionals. We provide state-of-art facilities and services to our students and faculty. We the faculty are eager to enhance your practical skills and learning experience at MMP.', 'richtext', '2026-06-03 03:11:50', '2026-06-03 08:02:43'),
(8, 'principal_photo', 'about', 'Principal Photo', 'site-settings/A6mQMlG7TdINhI2nhdyypxp8nRMCtjzv65R0lzSC.jpg', 'image', '2026-06-03 03:11:50', '2026-06-03 05:35:04'),
(9, 'principal_message_media', 'about', 'Principal Message — Media Attachment (Image / Video / PDF)', '', 'file', '2026-06-03 03:11:50', '2026-06-03 03:11:50'),
(10, 'president_name', 'about', 'President Name', 'Hon. President Name', 'text', '2026-06-03 03:11:50', '2026-06-03 08:02:43'),
(11, 'principal_name', 'about', 'Principal Name', '- Er. Sudip Adhikary', 'text', '2026-06-03 03:11:50', '2026-06-03 08:02:43'),
(12, 'classrooms_labs', 'facilities', 'Classrooms & Labs', 'We offer state of the art...', 'richtext', '2026-06-03 03:11:50', '2026-06-03 03:11:50'),
(13, 'workshops', 'facilities', 'Workshops', 'Our workshops include...', 'richtext', '2026-06-03 03:11:50', '2026-06-03 03:11:50'),
(14, 'transportation', 'facilities', 'Transportation', 'We provide bus service...', 'richtext', '2026-06-03 03:11:50', '2026-06-03 03:11:50'),
(15, 'scholarship_schemes', 'student_affairs', 'Scholarship Schemes', 'Scholarship Schemes\r\nGovernment of Nepal scholarships as per government norms and CTEVT selection criteria (to be provided by GON)\r\nScholarships for a minimum of ten percent students as per CTEVT norms and selection criteria (to be provided by MMP)\r\nManmohan Scholarship for the needy students as per MMP norms, selection criteria and available funds\r\nEarn to Learn\r\nBesides the scholarships, MMP has developed a scheme under which poor, deprived, and needy students with thrust of education and training can benefit. The students will be provided adequate land where they can work and earn that ultimately helps them to proceed with their studies. This scheme is designed in a way that helps to change the mindset of the youths and they will begin to respect the labor and will earn to learn.', 'richtext', '2026-06-03 03:11:50', '2026-06-03 08:02:43'),
(16, 'internships_placements', 'student_affairs', 'Internships & Placements', '100% placement rate...', 'richtext', '2026-06-03 03:11:50', '2026-06-03 08:02:43'),
(17, 'contact_us_content', 'contact', 'Contact Us', 'Reach out to us for admissions, academic information, facility visits, and institutional support.', 'richtext', '2026-06-03 03:11:50', '2026-06-03 08:02:43'),
(18, 'contact_email', 'contact', 'Contact Email', 'info@mmp.edu.np', 'text', '2026-06-03 03:11:50', '2026-06-03 08:02:43'),
(19, 'contact_phone', 'contact', 'Contact Phone', '+977 21 590696, +977 21 590697', 'text', '2026-06-03 03:11:50', '2026-06-03 08:02:43'),
(20, 'contact_address', 'contact', 'Contact Address', 'Koshi Province, Nepal', 'text', '2026-06-03 03:11:50', '2026-06-03 08:02:43'),
(21, 'facebook_url', 'contact', 'Facebook URL', '', 'text', '2026-06-03 03:11:50', '2026-06-03 03:11:50'),
(22, 'twitter_url', 'contact', 'Twitter URL', '', 'text', '2026-06-03 03:11:50', '2026-06-03 03:11:50'),
(23, 'instagram_url', 'contact', 'Instagram URL', '', 'text', '2026-06-03 03:11:50', '2026-06-03 03:11:50'),
(24, 'youtube_url', 'contact', 'YouTube URL', '', 'text', '2026-06-03 03:11:50', '2026-06-03 03:11:50'),
(25, 'linkedin_url', 'contact', 'LinkedIn URL', '', 'text', '2026-06-03 03:11:50', '2026-06-03 03:11:50'),
(26, 'google_maps_iframe', 'contact', 'Google Maps Embed', '<iframe src=\"https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d13570.238629183787!2d87.2916702!3d26.54717065!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x39ef72217d254661%3A0x231fd54332e0e36c!2sManmohan%20Memorial%20Polytechnic!5e1!3m2!1sen!2snp!4v1780465015604!5m2!1sen!2snp\" width=\"600\" height=\"450\" style=\"border:0;\" allowfullscreen=\"\" loading=\"lazy\" referrerpolicy=\"no-referrer-when-downgrade\"></iframe>', 'textarea', '2026-06-03 03:11:50', '2026-06-03 08:02:43');

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `staff_code` varchar(255) DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `designation` varchar(255) DEFAULT NULL,
  `department` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `gender` varchar(255) DEFAULT NULL,
  `employment_type` varchar(255) DEFAULT NULL,
  `employment_status` varchar(255) NOT NULL DEFAULT 'active',
  `join_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `salary_amount` decimal(12,2) DEFAULT NULL,
  `working_schedule` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`working_schedule`)),
  `assigned_roles` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`assigned_roles`)),
  `responsibilities` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`responsibilities`)),
  `bio` text DEFAULT NULL,
  `public_visible` tinyint(1) NOT NULL DEFAULT 0,
  `featured` tinyint(1) NOT NULL DEFAULT 0,
  `show_email_public` tinyint(1) NOT NULL DEFAULT 0,
  `show_phone_public` tinyint(1) NOT NULL DEFAULT 0,
  `photo` varchar(255) DEFAULT NULL,
  `order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `staff_attendances`
--

CREATE TABLE `staff_attendances` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `staff_id` bigint(20) UNSIGNED NOT NULL,
  `attendance_date` date NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'present',
  `check_in` varchar(255) DEFAULT NULL,
  `check_out` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `staff_documents`
--

CREATE TABLE `staff_documents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `staff_id` bigint(20) UNSIGNED NOT NULL,
  `document_type` varchar(255) NOT NULL,
  `label` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `mime_type` varchar(255) DEFAULT NULL,
  `file_size` bigint(20) UNSIGNED DEFAULT NULL,
  `issued_at` date DEFAULT NULL,
  `is_public` tinyint(1) NOT NULL DEFAULT 0,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `department_id` bigint(20) UNSIGNED NOT NULL,
  `program_id` bigint(20) UNSIGNED NOT NULL,
  `academic_session_id` bigint(20) UNSIGNED NOT NULL,
  `student_no` varchar(50) DEFAULT NULL,
  `registration_number` varchar(255) DEFAULT NULL,
  `current_semester` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  `semester` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  `section` varchar(255) DEFAULT NULL,
  `batch` varchar(255) DEFAULT NULL,
  `admission_date` date DEFAULT NULL,
  `guardian_name` varchar(255) DEFAULT NULL,
  `guardian_phone` varchar(20) DEFAULT NULL,
  `blood_group` varchar(5) DEFAULT NULL,
  `status` enum('active','inactive','graduated','dropped','suspended') NOT NULL DEFAULT 'active',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_archived` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `roll_number` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `user_id`, `department_id`, `program_id`, `academic_session_id`, `student_no`, `registration_number`, `current_semester`, `semester`, `section`, `batch`, `admission_date`, `guardian_name`, `guardian_phone`, `blood_group`, `status`, `is_active`, `is_archived`, `created_at`, `updated_at`, `deleted_at`, `roll_number`) VALUES
(1, 2, 1, 1, 1, 'STU-0001', NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, 'active', 1, 0, '2026-06-05 19:42:36', '2026-06-05 19:42:36', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `program_id` bigint(20) UNSIGNED NOT NULL,
  `semester` tinyint(3) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'theory',
  `full_marks_internal_theory` smallint(5) UNSIGNED NOT NULL DEFAULT 20,
  `full_marks_external_theory` smallint(5) UNSIGNED NOT NULL DEFAULT 80,
  `pass_marks_internal_theory` smallint(5) UNSIGNED NOT NULL DEFAULT 8,
  `pass_marks_external_theory` smallint(5) UNSIGNED NOT NULL DEFAULT 32,
  `full_marks_internal_practical` smallint(5) UNSIGNED NOT NULL DEFAULT 30,
  `full_marks_external_practical` smallint(5) UNSIGNED NOT NULL DEFAULT 20,
  `pass_marks_internal_practical` smallint(5) UNSIGNED NOT NULL DEFAULT 15,
  `pass_marks_external_practical` smallint(5) UNSIGNED NOT NULL DEFAULT 10,
  `credit_hours` smallint(5) UNSIGNED NOT NULL DEFAULT 3,
  `details` text DEFAULT NULL,
  `syllabus` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`id`, `program_id`, `semester`, `name`, `code`, `type`, `full_marks_internal_theory`, `full_marks_external_theory`, `pass_marks_internal_theory`, `pass_marks_external_theory`, `full_marks_internal_practical`, `full_marks_external_practical`, `pass_marks_internal_practical`, `pass_marks_external_practical`, `credit_hours`, `details`, `syllabus`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'c programming', 'CS01', 'both', 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, 1, '2026-06-06 07:22:27', '2026-06-06 07:22:27');

-- --------------------------------------------------------

--
-- Table structure for table `subject_teacher`
--

CREATE TABLE `subject_teacher` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `teacher_id` bigint(20) UNSIGNED NOT NULL,
  `subject_id` bigint(20) UNSIGNED NOT NULL,
  `academic_session_id` bigint(20) UNSIGNED NOT NULL,
  `section` varchar(255) DEFAULT NULL,
  `role` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `subject_teacher`
--

INSERT INTO `subject_teacher` (`id`, `teacher_id`, `subject_id`, `academic_session_id`, `section`, `role`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, NULL, 'teacher', '2026-06-06 07:24:07', '2026-06-06 07:24:07');

-- --------------------------------------------------------

--
-- Table structure for table `teachers`
--

CREATE TABLE `teachers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `department_id` bigint(20) UNSIGNED DEFAULT NULL,
  `employee_id` varchar(255) DEFAULT NULL,
  `designation` varchar(255) DEFAULT NULL,
  `qualification` varchar(255) DEFAULT NULL,
  `specialization` varchar(255) DEFAULT NULL,
  `join_date` date DEFAULT NULL,
  `employment_type` enum('permanent','contract','part-time') NOT NULL DEFAULT 'permanent',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `teachers`
--

INSERT INTO `teachers` (`id`, `user_id`, `department_id`, `employee_id`, `designation`, `qualification`, `specialization`, `join_date`, `employment_type`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 4, 1, 'EMP-001', 'Teacher', NULL, NULL, NULL, 'permanent', 1, '2026-06-06 07:24:07', '2026-06-06 07:24:07', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `timetables`
--

CREATE TABLE `timetables` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `academic_session_id` bigint(20) UNSIGNED NOT NULL,
  `program_id` bigint(20) UNSIGNED NOT NULL,
  `semester` tinyint(3) UNSIGNED NOT NULL,
  `section` varchar(255) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `effective_from` date DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `timetables`
--

INSERT INTO `timetables` (`id`, `academic_session_id`, `program_id`, `semester`, `section`, `start_date`, `effective_from`, `is_active`, `created_at`, `updated_at`) VALUES
(5, 1, 1, 1, NULL, NULL, '2026-06-07', 1, '2026-06-07 06:26:16', '2026-06-07 06:26:16');

-- --------------------------------------------------------

--
-- Table structure for table `timetable_slots`
--

CREATE TABLE `timetable_slots` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `timetable_id` bigint(20) UNSIGNED NOT NULL,
  `subject_id` bigint(20) UNSIGNED NOT NULL,
  `teacher_id` bigint(20) UNSIGNED NOT NULL,
  `day_of_week` enum('sunday','monday','tuesday','wednesday','thursday','friday','saturday') NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `room_number` varchar(255) DEFAULT NULL,
  `type` enum('theory','practical','lab','library','break') NOT NULL DEFAULT 'theory',
  `group` varchar(255) DEFAULT NULL,
  `duration` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `timetable_slots`
--

INSERT INTO `timetable_slots` (`id`, `timetable_id`, `subject_id`, `teacher_id`, `day_of_week`, `start_time`, `end_time`, `room_number`, `type`, `group`, `duration`, `created_at`, `updated_at`) VALUES
(18, 5, 1, 1, 'monday', '06:30:00', '07:15:00', NULL, 'practical', 'A', 1, '2026-06-10 02:36:50', '2026-06-10 02:36:50');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `address` text DEFAULT NULL,
  `preferences` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`preferences`)),
  `notification_preferences` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`notification_preferences`)),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `two_factor_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `two_factor_method` varchar(255) NOT NULL DEFAULT 'email',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `avatar`, `gender`, `dob`, `address`, `preferences`, `notification_preferences`, `is_active`, `email_verified_at`, `password`, `two_factor_enabled`, `two_factor_method`, `remember_token`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Admin', 'sitalmahato077@gmail.com', NULL, 'avatars/XpAQaE0F45AY5ibo2g6JErTNHueijrWI8ZglnKYv.jpg', NULL, NULL, NULL, NULL, NULL, 1, '2026-06-05 19:29:55', '$2y$12$mkVkf0RGAR7jfkk51CTll.EoTfGbSW4zBPLFO0CK0ZmizYwabqJHu', 0, 'email', NULL, '2026-06-03 03:10:23', '2026-06-05 19:29:55', NULL),
(2, 'sital kumar mahato', 'hellogoog94@gmail.com', '9704191610', 'avatars/DrXJhpaOPDEOgE2bEO8X3faXBXjmEtXe1QAWWcRC.jpg', NULL, NULL, 'golbazar-10', NULL, NULL, 1, NULL, '$2y$12$PXHL618BXnHO1LYaotJl4e8I1N5Sd6xXd2i7xTPMPISzSfHm56Vg6', 0, 'email', NULL, '2026-06-05 19:42:36', '2026-06-05 19:43:23', NULL),
(3, 'yubraj chaudhary', 'sitalmahato00@gmail.com', NULL, 'avatars/v9IAWQ9QKMzckXZOraNS4Nbb5Rf6BVLZnce3hjOg.jpg', NULL, NULL, NULL, NULL, NULL, 1, NULL, '$2y$12$hbJjR5IJcYA4Y.jIHZjbmu.GsZmVy/sm13N6oa0VYRyKLHPBf9bZS', 0, 'email', NULL, '2026-06-06 07:21:10', '2026-06-07 15:08:24', NULL),
(4, 'Binay Pokeral', 'itstudentsital@gmail.com', NULL, NULL, 'male', NULL, NULL, NULL, NULL, 1, NULL, '$2y$12$X8Dk0td.zdvYhpTfOFYLE.mfh43nOd8KPEyMGTKRy8vJ2BHVxW0QC', 0, 'email', NULL, '2026-06-06 07:24:07', '2026-06-06 07:24:07', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `academic_sessions`
--
ALTER TABLE `academic_sessions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `academic_session_semesters`
--
ALTER TABLE `academic_session_semesters`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_session_semester_number` (`academic_session_id`,`semester_number`),
  ADD KEY `idx_session_semester_status` (`academic_session_id`,`status`),
  ADD KEY `idx_session_semester_active` (`academic_session_id`,`is_active`);

--
-- Indexes for table `alumni`
--
ALTER TABLE `alumni`
  ADD PRIMARY KEY (`id`),
  ADD KEY `alumni_user_id_foreign` (`user_id`),
  ADD KEY `alumni_student_id_foreign` (`student_id`),
  ADD KEY `alumni_department_id_foreign` (`department_id`),
  ADD KEY `alumni_program_id_foreign` (`program_id`);

--
-- Indexes for table `alumni_achievements`
--
ALTER TABLE `alumni_achievements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `alumni_achievements_alumni_id_foreign` (`alumni_id`);

--
-- Indexes for table `alumni_employments`
--
ALTER TABLE `alumni_employments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `alumni_employments_alumni_id_foreign` (`alumni_id`);

--
-- Indexes for table `alumni_projects`
--
ALTER TABLE `alumni_projects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `alumni_projects_alumni_id_type_unique` (`alumni_id`,`type`);

--
-- Indexes for table `assignments`
--
ALTER TABLE `assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assignments_teacher_id_foreign` (`teacher_id`),
  ADD KEY `assignments_subject_id_foreign` (`subject_id`),
  ADD KEY `assignments_program_id_foreign` (`program_id`);

--
-- Indexes for table `assignment_submissions`
--
ALTER TABLE `assignment_submissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `assignment_submissions_assignment_id_student_id_unique` (`assignment_id`,`student_id`),
  ADD KEY `assignment_submissions_student_id_foreign` (`student_id`);

--
-- Indexes for table `attendances`
--
ALTER TABLE `attendances`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `attendances_attendance_session_id_student_id_unique` (`attendance_session_id`,`student_id`),
  ADD KEY `idx_att_student_status` (`student_id`,`status`);

--
-- Indexes for table `attendance_sessions`
--
ALTER TABLE `attendance_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `attendance_sessions_academic_session_id_foreign` (`academic_session_id`),
  ADD KEY `attendance_sessions_subject_id_foreign` (`subject_id`),
  ADD KEY `attendance_sessions_program_id_foreign` (`program_id`),
  ADD KEY `idx_attsess_date_program` (`date`,`program_id`,`semester`),
  ADD KEY `idx_attsess_teacher_date` (`teacher_id`,`date`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_audit_user_action` (`user_id`,`action`),
  ADD KEY `idx_audit_model` (`model_type`,`model_id`),
  ADD KEY `idx_audit_created` (`created_at`);

--
-- Indexes for table `banners`
--
ALTER TABLE `banners`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `communications`
--
ALTER TABLE `communications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `communications_sender_id_foreign` (`sender_id`),
  ADD KEY `communications_receiver_id_foreign` (`receiver_id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `departments_code_unique` (`code`),
  ADD UNIQUE KEY `departments_slug_unique` (`slug`);

--
-- Indexes for table `downloads`
--
ALTER TABLE `downloads`
  ADD PRIMARY KEY (`id`),
  ADD KEY `downloads_department_id_foreign` (`department_id`),
  ADD KEY `downloads_subject_id_foreign` (`subject_id`),
  ADD KEY `downloads_program_id_foreign` (`program_id`),
  ADD KEY `downloads_uploaded_by_foreign` (`uploaded_by`);

--
-- Indexes for table `exams`
--
ALTER TABLE `exams`
  ADD PRIMARY KEY (`id`),
  ADD KEY `exams_academic_session_id_foreign` (`academic_session_id`),
  ADD KEY `exams_department_id_foreign` (`department_id`);

--
-- Indexes for table `exam_program`
--
ALTER TABLE `exam_program`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `exam_program_exam_id_program_id_semester_unique` (`exam_id`,`program_id`,`semester`),
  ADD KEY `exam_program_program_id_foreign` (`program_id`);

--
-- Indexes for table `exam_subject_marking_schemes`
--
ALTER TABLE `exam_subject_marking_schemes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `exam_subject_marking_unique` (`exam_id`,`subject_id`),
  ADD KEY `exam_subject_marking_schemes_subject_id_foreign` (`subject_id`),
  ADD KEY `idx_exam_subject_marking_lookup` (`exam_id`,`subject_id`);

--
-- Indexes for table `executives`
--
ALTER TABLE `executives`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `facilities`
--
ALTER TABLE `facilities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `facilities_department_id_foreign` (`department_id`),
  ADD KEY `facilities_program_id_foreign` (`program_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

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
-- Indexes for table `marks`
--
ALTER TABLE `marks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `marks_exam_id_student_id_subject_id_unique` (`exam_id`,`student_id`,`subject_id`),
  ADD KEY `marks_subject_id_foreign` (`subject_id`),
  ADD KEY `marks_teacher_id_foreign` (`teacher_id`),
  ADD KEY `idx_marks_exam_status` (`exam_id`,`status`),
  ADD KEY `idx_marks_student_status` (`student_id`,`status`),
  ADD KEY `idx_marks_program_semester` (`program_id`,`semester`);

--
-- Indexes for table `media`
--
ALTER TABLE `media`
  ADD PRIMARY KEY (`id`),
  ADD KEY `media_department_id_foreign` (`department_id`),
  ADD KEY `media_uploaded_by_foreign` (`uploaded_by`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `notices`
--
ALTER TABLE `notices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `notices_slug_unique` (`slug`),
  ADD KEY `notices_program_id_foreign` (`program_id`),
  ADD KEY `notices_created_by_foreign` (`created_by`),
  ADD KEY `idx_notices_published_type` (`is_published`,`type`),
  ADD KEY `idx_notices_dept_published` (`department_id`,`is_published`);

--
-- Indexes for table `notice_attachments`
--
ALTER TABLE `notice_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notice_attachments_notice_id_foreign` (`notice_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`);

--
-- Indexes for table `otps`
--
ALTER TABLE `otps`
  ADD PRIMARY KEY (`id`),
  ADD KEY `otps_phone_expires_at_index` (`phone`,`expires_at`),
  ADD KEY `otps_phone_index` (`phone`);

--
-- Indexes for table `pages`
--
ALTER TABLE `pages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pages_slug_unique` (`slug`),
  ADD KEY `pages_created_by_foreign` (`created_by`);

--
-- Indexes for table `parents`
--
ALTER TABLE `parents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parents_user_id_foreign` (`user_id`);

--
-- Indexes for table `parent_student`
--
ALTER TABLE `parent_student`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `parent_student_parent_id_student_id_unique` (`parent_id`,`student_id`),
  ADD KEY `parent_student_student_id_foreign` (`student_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  ADD KEY `personal_access_tokens_expires_at_index` (`expires_at`);

--
-- Indexes for table `programs`
--
ALTER TABLE `programs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `programs_code_unique` (`code`),
  ADD UNIQUE KEY `programs_slug_unique` (`slug`),
  ADD KEY `programs_department_id_foreign` (`department_id`),
  ADD KEY `programs_coordinator_id_foreign` (`coordinator_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `site_settings`
--
ALTER TABLE `site_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `site_settings_key_unique` (`key`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `staff_staff_code_unique` (`staff_code`),
  ADD KEY `staff_user_id_foreign` (`user_id`);

--
-- Indexes for table `staff_attendances`
--
ALTER TABLE `staff_attendances`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `staff_attendances_staff_id_attendance_date_unique` (`staff_id`,`attendance_date`);

--
-- Indexes for table `staff_documents`
--
ALTER TABLE `staff_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `staff_documents_staff_id_document_type_index` (`staff_id`,`document_type`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `students_student_no_unique` (`student_no`),
  ADD KEY `students_user_id_foreign` (`user_id`),
  ADD KEY `students_academic_session_id_foreign` (`academic_session_id`),
  ADD KEY `idx_students_dept_session` (`department_id`,`academic_session_id`),
  ADD KEY `idx_students_program_sem` (`program_id`,`current_semester`),
  ADD KEY `idx_students_status` (`status`,`is_archived`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `subjects_code_unique` (`code`),
  ADD KEY `subjects_program_id_foreign` (`program_id`);

--
-- Indexes for table `subject_teacher`
--
ALTER TABLE `subject_teacher`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `subject_teacher_unique` (`teacher_id`,`subject_id`,`academic_session_id`,`section`),
  ADD KEY `subject_teacher_subject_id_foreign` (`subject_id`),
  ADD KEY `idx_subteach_session_teacher` (`academic_session_id`,`teacher_id`);

--
-- Indexes for table `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `teachers_employee_id_unique` (`employee_id`),
  ADD KEY `teachers_user_id_foreign` (`user_id`),
  ADD KEY `teachers_department_id_foreign` (`department_id`);

--
-- Indexes for table `timetables`
--
ALTER TABLE `timetables`
  ADD PRIMARY KEY (`id`),
  ADD KEY `timetables_academic_session_id_foreign` (`academic_session_id`),
  ADD KEY `timetables_program_id_foreign` (`program_id`);

--
-- Indexes for table `timetable_slots`
--
ALTER TABLE `timetable_slots`
  ADD PRIMARY KEY (`id`),
  ADD KEY `timetable_slots_timetable_id_foreign` (`timetable_id`),
  ADD KEY `timetable_slots_subject_id_foreign` (`subject_id`),
  ADD KEY `timetable_slots_teacher_id_foreign` (`teacher_id`),
  ADD KEY `idx_slots_day_tt` (`day_of_week`,`timetable_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `academic_sessions`
--
ALTER TABLE `academic_sessions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `academic_session_semesters`
--
ALTER TABLE `academic_session_semesters`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `alumni`
--
ALTER TABLE `alumni`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `alumni_achievements`
--
ALTER TABLE `alumni_achievements`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `alumni_employments`
--
ALTER TABLE `alumni_employments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `alumni_projects`
--
ALTER TABLE `alumni_projects`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `assignments`
--
ALTER TABLE `assignments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `assignment_submissions`
--
ALTER TABLE `assignment_submissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `attendances`
--
ALTER TABLE `attendances`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `attendance_sessions`
--
ALTER TABLE `attendance_sessions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=259;

--
-- AUTO_INCREMENT for table `banners`
--
ALTER TABLE `banners`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `communications`
--
ALTER TABLE `communications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `downloads`
--
ALTER TABLE `downloads`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `exams`
--
ALTER TABLE `exams`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `exam_program`
--
ALTER TABLE `exam_program`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `exam_subject_marking_schemes`
--
ALTER TABLE `exam_subject_marking_schemes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `executives`
--
ALTER TABLE `executives`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `facilities`
--
ALTER TABLE `facilities`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `marks`
--
ALTER TABLE `marks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `media`
--
ALTER TABLE `media`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `notices`
--
ALTER TABLE `notices`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `notice_attachments`
--
ALTER TABLE `notice_attachments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `otps`
--
ALTER TABLE `otps`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pages`
--
ALTER TABLE `pages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `parents`
--
ALTER TABLE `parents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `parent_student`
--
ALTER TABLE `parent_student`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `programs`
--
ALTER TABLE `programs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `site_settings`
--
ALTER TABLE `site_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `staff_attendances`
--
ALTER TABLE `staff_attendances`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `staff_documents`
--
ALTER TABLE `staff_documents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `subject_teacher`
--
ALTER TABLE `subject_teacher`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `teachers`
--
ALTER TABLE `teachers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `timetables`
--
ALTER TABLE `timetables`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `timetable_slots`
--
ALTER TABLE `timetable_slots`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `academic_session_semesters`
--
ALTER TABLE `academic_session_semesters`
  ADD CONSTRAINT `academic_session_semesters_academic_session_id_foreign` FOREIGN KEY (`academic_session_id`) REFERENCES `academic_sessions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `alumni`
--
ALTER TABLE `alumni`
  ADD CONSTRAINT `alumni_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `alumni_program_id_foreign` FOREIGN KEY (`program_id`) REFERENCES `programs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `alumni_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `alumni_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `alumni_achievements`
--
ALTER TABLE `alumni_achievements`
  ADD CONSTRAINT `alumni_achievements_alumni_id_foreign` FOREIGN KEY (`alumni_id`) REFERENCES `alumni` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `alumni_employments`
--
ALTER TABLE `alumni_employments`
  ADD CONSTRAINT `alumni_employments_alumni_id_foreign` FOREIGN KEY (`alumni_id`) REFERENCES `alumni` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `alumni_projects`
--
ALTER TABLE `alumni_projects`
  ADD CONSTRAINT `alumni_projects_alumni_id_foreign` FOREIGN KEY (`alumni_id`) REFERENCES `alumni` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `assignments`
--
ALTER TABLE `assignments`
  ADD CONSTRAINT `assignments_program_id_foreign` FOREIGN KEY (`program_id`) REFERENCES `programs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `assignments_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `assignments_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `assignment_submissions`
--
ALTER TABLE `assignment_submissions`
  ADD CONSTRAINT `assignment_submissions_assignment_id_foreign` FOREIGN KEY (`assignment_id`) REFERENCES `assignments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `assignment_submissions_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `attendances`
--
ALTER TABLE `attendances`
  ADD CONSTRAINT `attendances_attendance_session_id_foreign` FOREIGN KEY (`attendance_session_id`) REFERENCES `attendance_sessions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendances_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `attendance_sessions`
--
ALTER TABLE `attendance_sessions`
  ADD CONSTRAINT `attendance_sessions_academic_session_id_foreign` FOREIGN KEY (`academic_session_id`) REFERENCES `academic_sessions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendance_sessions_program_id_foreign` FOREIGN KEY (`program_id`) REFERENCES `programs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendance_sessions_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendance_sessions_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `communications`
--
ALTER TABLE `communications`
  ADD CONSTRAINT `communications_receiver_id_foreign` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `communications_sender_id_foreign` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `downloads`
--
ALTER TABLE `downloads`
  ADD CONSTRAINT `downloads_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `downloads_program_id_foreign` FOREIGN KEY (`program_id`) REFERENCES `programs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `downloads_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `downloads_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `exams`
--
ALTER TABLE `exams`
  ADD CONSTRAINT `exams_academic_session_id_foreign` FOREIGN KEY (`academic_session_id`) REFERENCES `academic_sessions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exams_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `exam_program`
--
ALTER TABLE `exam_program`
  ADD CONSTRAINT `exam_program_exam_id_foreign` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exam_program_program_id_foreign` FOREIGN KEY (`program_id`) REFERENCES `programs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `exam_subject_marking_schemes`
--
ALTER TABLE `exam_subject_marking_schemes`
  ADD CONSTRAINT `exam_subject_marking_schemes_exam_id_foreign` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exam_subject_marking_schemes_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `facilities`
--
ALTER TABLE `facilities`
  ADD CONSTRAINT `facilities_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `facilities_program_id_foreign` FOREIGN KEY (`program_id`) REFERENCES `programs` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `marks`
--
ALTER TABLE `marks`
  ADD CONSTRAINT `marks_exam_id_foreign` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `marks_program_id_foreign` FOREIGN KEY (`program_id`) REFERENCES `programs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `marks_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `marks_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `marks_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `media`
--
ALTER TABLE `media`
  ADD CONSTRAINT `media_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `media_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notices`
--
ALTER TABLE `notices`
  ADD CONSTRAINT `notices_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notices_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notices_program_id_foreign` FOREIGN KEY (`program_id`) REFERENCES `programs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notice_attachments`
--
ALTER TABLE `notice_attachments`
  ADD CONSTRAINT `notice_attachments_notice_id_foreign` FOREIGN KEY (`notice_id`) REFERENCES `notices` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pages`
--
ALTER TABLE `pages`
  ADD CONSTRAINT `pages_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `parents`
--
ALTER TABLE `parents`
  ADD CONSTRAINT `parents_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `parent_student`
--
ALTER TABLE `parent_student`
  ADD CONSTRAINT `parent_student_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `parents` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `parent_student_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `programs`
--
ALTER TABLE `programs`
  ADD CONSTRAINT `programs_coordinator_id_foreign` FOREIGN KEY (`coordinator_id`) REFERENCES `teachers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `programs_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `staff`
--
ALTER TABLE `staff`
  ADD CONSTRAINT `staff_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `staff_attendances`
--
ALTER TABLE `staff_attendances`
  ADD CONSTRAINT `staff_attendances_staff_id_foreign` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `staff_documents`
--
ALTER TABLE `staff_documents`
  ADD CONSTRAINT `staff_documents_staff_id_foreign` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_academic_session_id_foreign` FOREIGN KEY (`academic_session_id`) REFERENCES `academic_sessions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `students_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `students_program_id_foreign` FOREIGN KEY (`program_id`) REFERENCES `programs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `students_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `subjects`
--
ALTER TABLE `subjects`
  ADD CONSTRAINT `subjects_program_id_foreign` FOREIGN KEY (`program_id`) REFERENCES `programs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `subject_teacher`
--
ALTER TABLE `subject_teacher`
  ADD CONSTRAINT `subject_teacher_academic_session_id_foreign` FOREIGN KEY (`academic_session_id`) REFERENCES `academic_sessions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `subject_teacher_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `subject_teacher_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `teachers`
--
ALTER TABLE `teachers`
  ADD CONSTRAINT `teachers_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `teachers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `timetables`
--
ALTER TABLE `timetables`
  ADD CONSTRAINT `timetables_academic_session_id_foreign` FOREIGN KEY (`academic_session_id`) REFERENCES `academic_sessions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `timetables_program_id_foreign` FOREIGN KEY (`program_id`) REFERENCES `programs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `timetable_slots`
--
ALTER TABLE `timetable_slots`
  ADD CONSTRAINT `timetable_slots_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `timetable_slots_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `timetable_slots_timetable_id_foreign` FOREIGN KEY (`timetable_id`) REFERENCES `timetables` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
