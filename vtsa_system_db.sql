SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Create Database
CREATE DATABASE IF NOT EXISTS `vtsa_system`;
USE `vtsa_system`;

-- --------------------------------------------------------

--
-- Table structure for table `applicants`
--
CREATE TABLE `applicants` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `desired_position` VARCHAR(255) NULL DEFAULT NULL,
  `date_time_applied` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `phone` VARCHAR(25) NULL DEFAULT NULL,
  `address` TEXT NULL DEFAULT NULL,
  `password` VARCHAR(255) NOT NULL,
  `application` VARCHAR(255) DEFAULT NULL,
  `resume` VARCHAR(255) DEFAULT NULL,
  `profile_pic` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('Pending', 'Interview', 'Hired', 'Rejected') NOT NULL DEFAULT 'Pending'
);

--
-- Table structure for table `applicant_skills`
--
CREATE TABLE `applicant_skills` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `applicant_id` INT NOT NULL,
  `skill_name` VARCHAR(255) NOT NULL,
  FOREIGN KEY (`applicant_id`) REFERENCES `applicants`(`id`) ON DELETE CASCADE
);

--
-- Table structure for table `applicant_education`
--
CREATE TABLE `applicant_education` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `applicant_id` INT NOT NULL,
  `institution` VARCHAR(255) NOT NULL,
  `degree` VARCHAR(255) NOT NULL,
  `years` VARCHAR(100) NOT NULL,
  `description` TEXT,
  FOREIGN KEY (`applicant_id`) REFERENCES `applicants`(`id`) ON DELETE CASCADE
);

--
-- Table structure for table `applicant_work_exp`
--
CREATE TABLE `applicant_work_exp` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `applicant_id` INT NOT NULL,
  `job_title` VARCHAR(255) NOT NULL,
  `company` VARCHAR(255) NOT NULL,
  `years` VARCHAR(100) NOT NULL,
  `description` TEXT,
  FOREIGN KEY (`applicant_id`) REFERENCES `applicants`(`id`) ON DELETE CASCADE
);

--
-- Table structure for table `applicant_status`
--
CREATE TABLE `applicant_status` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `applicant_id` INT NOT NULL,
  `status` VARCHAR(100) NOT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`applicant_id`) REFERENCES `applicants`(`id`) ON DELETE CASCADE
);

--
-- Table structure for table `hr_database`
--
CREATE TABLE `hr_database` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL
);

--
-- Table structure for table `admin_database`
--
CREATE TABLE `admin_database` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL
);

--
-- Table structure for table `job_listing_database`
--
CREATE TABLE `job_listing_database` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `job_title` VARCHAR(255) NOT NULL,
  `description` TEXT
);

--
-- Table structure for table `employees`
--
CREATE TABLE `employees` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `employee_id_number` VARCHAR(50) NULL,
  `password` VARCHAR(255) NOT NULL,
  `position` VARCHAR(100) NULL,
  `civil_status` VARCHAR(50) NULL,
  `gender` VARCHAR(50) NULL,
  `date_of_birth` DATE NULL,
  `personal_no` VARCHAR(50) NULL,
  `work_email` VARCHAR(255) NULL,
  `personal_email` VARCHAR(255) NOT NULL UNIQUE,
  `permanent_address` TEXT NULL,
  `current_address` TEXT NULL,
  `contact_person` VARCHAR(255) NULL,
  `relationship` VARCHAR(100) NULL,
  `contact_number` VARCHAR(20) NULL,
  `e_signature` VARCHAR(255) NULL
);

--
-- Table structure for table `request_bpaper`
--
CREATE TABLE `request_bpaper` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `employee_id` INT NOT NULL,
  `paper_size` VARCHAR(50) NOT NULL,
  `quantity` INT NOT NULL,
  `department` VARCHAR(100) NOT NULL,
  `status` VARCHAR(50) NOT NULL,
  `date_time_requested` timestamp NOT NULL DEFAULT current_timestamp(),
  FOREIGN KEY (`employee_id`) REFERENCES `employees`(`id`) ON DELETE CASCADE
);

--
-- Table structure for table `request_supplies`
--
CREATE TABLE `request_supplies` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `employee_id` INT NOT NULL,
  `item_name` VARCHAR(255) NOT NULL,
  `quantity` INT NOT NULL,
  `department` VARCHAR(100) NOT NULL,
  `status` VARCHAR(50) NOT NULL,
  `date_time_requested` timestamp NOT NULL DEFAULT current_timestamp(),
  FOREIGN KEY (`employee_id`) REFERENCES `employees`(`id`) ON DELETE CASCADE
);

-- --------------------------------------------------------

--
-- Dumping data for testing
--

-- Insert a default HR user (Password: @VTSA_HR2026 - Run set_hr_password.php to update hash)
INSERT INTO `hr_database` (`name`, `email`, `password`) VALUES
('HR Manager', 'hr@vtsa.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Insert a default Admin user (Password: @ADMINvtsa_2026 - Run set_admin_password.php to update hash)
INSERT INTO `admin_database` (`name`, `email`, `password`) VALUES
('Admin User', 'admin@vtsa.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
COMMIT;