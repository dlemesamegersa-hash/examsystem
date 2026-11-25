-- Student Registration System Database Schema
-- Target Database: stud_reg_system

CREATE DATABASE IF NOT EXISTS `stud_reg_system` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `stud_reg_system`;

-- --------------------------------------------------------
-- Table structure for table `users`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `full_name` VARCHAR(100) NOT NULL,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `phone` VARCHAR(20),
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('admin', 'staff') DEFAULT 'staff',
    `status` ENUM('active', 'inactive') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `students`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `students`;
CREATE TABLE `students` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `full_name` VARCHAR(100) NOT NULL,
    `gender` ENUM('male', 'female', 'other') NOT NULL,
    `date_of_birth` DATE,
    `email` VARCHAR(100) UNIQUE,
    `phone` VARCHAR(20),
    `address` TEXT,
    `department` VARCHAR(100),
    `registration_number` VARCHAR(50) NOT NULL UNIQUE,
    `admission_date` DATE,
    `status` ENUM('active', 'inactive') DEFAULT 'active',
    `photo` VARCHAR(255),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `contact_messages`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `contact_messages`;
CREATE TABLE `contact_messages` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `full_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(20),
    `subject` VARCHAR(150),
    `message` TEXT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Seed Data for `users`
-- Default admin password: admin123
-- Default staff password: staff123
-- --------------------------------------------------------
INSERT INTO `users` (`id`, `full_name`, `username`, `email`, `phone`, `password`, `role`, `status`) VALUES
(1, 'System Administrator', 'admin', 'admin@example.com', '+1 (555) 019-2834', '$2y$12$NGIk6vm9WH./nTKUHZyJauYw4E/oQQCUtddYfuCsSe/p7L983RDS6', 'admin', 'active'),
(2, 'Staff Member', 'staff', 'staff@example.com', '+1 (555) 014-9988', '$2y$12$t3tMMT.N37RsGhawZoUnt.JGetffeKYwjoMxVpAQJtMyb9R7xgDK2', 'staff', 'active');

-- --------------------------------------------------------
-- Seed Data for `students`
-- --------------------------------------------------------
INSERT INTO `students` (`id`, `full_name`, `gender`, `date_of_birth`, `email`, `phone`, `address`, `department`, `registration_number`, `admission_date`, `status`, `photo`) VALUES
(1, 'Sarah Jenkins', 'female', '2003-04-12', 'sarah.j@example.com', '+1 (555) 234-5678', '742 Evergreen Terrace, Springfield', 'Computer Science', 'REG-2024-001', '2024-09-01', 'active', NULL),
(2, 'Alex Morgan', 'male', '2002-11-20', 'alex.m@example.com', '+1 (555) 345-6789', '123 Baker Street, London', 'Information Technology', 'REG-2024-002', '2024-09-01', 'active', NULL),
(3, 'Emily Davis', 'female', '2004-01-15', 'emily.d@example.com', '+1 (555) 456-7890', '456 Elm Street, Dallas', 'Business Administration', 'REG-2024-003', '2024-09-05', 'active', NULL),
(4, 'Michael Chen', 'male', '2003-07-08', 'michael.c@example.com', '+1 (555) 567-8901', '89 Ocean Avenue, Miami', 'Electrical Engineering', 'REG-2024-004', '2024-09-07', 'inactive', NULL),
(5, 'Jessica Taylor', 'female', '2004-03-29', 'jessica.t@example.com', '+1 (555) 678-9012', '12 Maple Road, Seattle', 'Nursing', 'REG-2024-005', '2024-09-10', 'active', NULL);

-- --------------------------------------------------------
-- Seed Data for `contact_messages`
-- --------------------------------------------------------
INSERT INTO `contact_messages` (`id`, `full_name`, `email`, `phone`, `subject`, `message`) VALUES
(1, 'David Miller', 'david.m@example.com', '+1 (555) 789-0123', 'Inquiry about Spring Admissions', 'Hello, I would like to inquire regarding the transfer admission process for Computer Science for the upcoming spring term.'),
(2, 'Grace Hopper', 'grace.h@example.com', '+1 (555) 890-1234', 'Campus Visit Schedule', 'Could you please share the visiting hours for the admissions office and campus tour schedules?'),
(3, 'Robert Brown', 'robert.b@example.com', '+1 (555) 901-2345', 'Fee Structure Question', 'Good day, please provide details regarding semester payment schedules and scholarship criteria for engineering students.');
