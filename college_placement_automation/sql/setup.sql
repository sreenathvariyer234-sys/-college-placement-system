-- Database: college_placement
CREATE DATABASE IF NOT EXISTS college_placement;
USE college_placement;

-- Users table (Students and Admins)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('student', 'super_admin', 'college_po', 'dept_po', 'company') DEFAULT 'student',
    department ENUM('Computer Engineering', 'Civil', 'Mechanical', 'Electronics', 'Computer Hardware Engineering', 'Common') NULL,
    cgpa DECIMAL(4,2) NULL,
    semester VARCHAR(20) NULL,
    passing_year INT NULL,
    skills TEXT NULL,
    institute VARCHAR(150) NULL,
    website VARCHAR(255) NULL,
    address VARCHAR(255) NULL,
    city VARCHAR(100) NULL,
    state VARCHAR(100) NULL,
    managed_department ENUM('Computer Engineering', 'Civil', 'Mechanical', 'Electronics', 'Computer Hardware Engineering', 'Common') NULL,
    profile_pic VARCHAR(255) DEFAULT 'default_profile.png',
    resume_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Job postings table
CREATE TABLE IF NOT EXISTS jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    company VARCHAR(100) NOT NULL,
    company_id INT NULL,
    department ENUM('Computer Engineering', 'Civil', 'Mechanical', 'Electronics', 'Computer Hardware Engineering', 'Common') NOT NULL DEFAULT 'Common',
    description TEXT NOT NULL,
    requirements TEXT,
    vacancies INT NULL,
    salary VARCHAR(50),
    location VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Applications table
CREATE TABLE IF NOT EXISTS applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    job_id INT NOT NULL,
    status ENUM('pending', 'interview_scheduled', 'shortlisted', 'rejected', 'offered', 'accepted', 'declined') DEFAULT 'pending',
    interview_date DATE NULL,
    interview_time TIME NULL,
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE
);


-- Notifications table
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    type VARCHAR(50) DEFAULT 'general',
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Sample Data (Admin)
-- Default password is 'admin123' (hashed using bcrypt: $2y$12$Vz3ae2PfIjVFq8uX6RAh5ey2koVLWU.qfQ1nc94tImrKRTQjY/Ep2)
INSERT INTO users (full_name, email, password, role) VALUES 
('Placement Coordinator', 'admin@college.edu', '$2y$12$Vz3ae2PfIjVFq8uX6RAh5ey2koVLWU.qfQ1nc94tImrKRTQjY/Ep2', 'admin');
