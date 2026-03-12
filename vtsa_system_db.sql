SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Create Database
CREATE DATABASE IF NOT EXISTS `vtsa_system`;
USE `vtsa_system`;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `full_name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `user_type` ENUM('applicant', 'employee', 'admin') NOT NULL DEFAULT 'applicant',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

--
-- Table structure for table `jobs`
--
CREATE TABLE `jobs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `status` ENUM('Open', 'Closed') NOT NULL DEFAULT 'Open',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

--
-- Table structure for table `applicants`
--
CREATE TABLE `applicants` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `job_id` INT NOT NULL,
  `status` ENUM('Pending', 'Hired', 'Rejected') NOT NULL DEFAULT 'Pending',
  `applied_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`job_id`) REFERENCES `jobs`(`id`) ON DELETE CASCADE
);

--
-- Table structure for table `employees`
--
CREATE TABLE `employees` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL UNIQUE,
  `employee_id_string` VARCHAR(50) NULL,
  `position` VARCHAR(100) NULL,
  `surname` VARCHAR(100) NOT NULL,
  `first_name` VARCHAR(100) NOT NULL,
  `middle_name` VARCHAR(100) NULL,
  `civil_status` VARCHAR(50) NOT NULL,
  `birth_date` DATE NOT NULL,
  `permanent_address` TEXT NOT NULL,
  `current_address` TEXT NULL,
  `contact_number` VARCHAR(20) NOT NULL,
  `other_contact_number` VARCHAR(20) NULL,
  `personal_email` VARCHAR(255) NOT NULL,
  `work_email` VARCHAR(255) NULL,
  `emergency_contact_person` VARCHAR(255) NOT NULL,
  `emergency_contact_relationship` VARCHAR(100) NOT NULL,
  `emergency_contact_number` VARCHAR(20) NOT NULL,
  `record_updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
);

-- --------------------------------------------------------

--
-- Dumping data for testing (Based on dashboard HTML)
--

-- 1. Insert Jobs
INSERT INTO `jobs` (`title`, `status`) VALUES
('Test and Commissioning Technician', 'Open'),
('PMS Technician', 'Open'),
('Sales & Marketing Officer', 'Closed'),
('Admin Staff', 'Open');

-- 2. Insert Users (Password is 'password123' hashed for demo purposes)
INSERT INTO `users` (`full_name`, `email`, `password_hash`, `user_type`) VALUES
('Admin User', 'admin@vtsa.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('John Doe', 'john.doe@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'applicant'),
('Jane Smith', 'jane.smith@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'applicant'),
('Marcia Brady', 'marcia@vtsa.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employee');

-- 3. Insert Applicants
-- John Doe (id:2) applied for Technician (id:1)
-- Jane Smith (id:3) applied for Admin Staff (id:4) - Hired
INSERT INTO `applicants` (`user_id`, `job_id`, `status`) VALUES
(2, 1, 'Pending'),
(3, 4, 'Hired');

-- 4. Insert Employee Records
-- Marcia Brady (id:4)
INSERT INTO `employees` (`user_id`, `surname`, `first_name`, `civil_status`, `birth_date`, `permanent_address`, `contact_number`, `personal_email`, `work_email`, `emergency_contact_person`, `emergency_contact_relationship`, `emergency_contact_number`) VALUES
(4, 'Brady', 'Marcia', 'Single', '1990-05-15', '4222 Clinton Way', '09171234567', 'marcia@example.com', 'marcia@vtsa.com', 'Mike Brady', 'Father', '09181234567');

COMMIT;