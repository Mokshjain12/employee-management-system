-- Employee Management System Database
-- Run this SQL to create the database and tables

-- Create database
CREATE DATABASE IF NOT EXISTS ems_db;
USE ems_db;

-- Users table for authentication
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'employee') DEFAULT 'employee',
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    profile_image VARCHAR(255) DEFAULT 'default.png',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active TINYINT(1) DEFAULT 1
);

-- Departments table
CREATE TABLE IF NOT EXISTS departments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Employees table (extends users with employee-specific data)
CREATE TABLE IF NOT EXISTS employees (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT UNIQUE NOT NULL,
    employee_id VARCHAR(20) UNIQUE NOT NULL,
    department_id INT,
    designation VARCHAR(100),
    salary DECIMAL(10, 2) DEFAULT 0,
    join_date DATE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
);

-- Attendance table
CREATE TABLE IF NOT EXISTS attendance (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    date DATE NOT NULL,
    check_in TIME,
    check_out TIME,
    status ENUM('present', 'absent', 'late', 'half_day') DEFAULT 'present',
    notes TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_attendance (user_id, date)
);

-- Leave requests table
CREATE TABLE IF NOT EXISTS leave_requests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    leave_type ENUM('sick', 'casual', 'annual', 'unpaid') NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    days INT NOT NULL,
    reason TEXT,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    reviewed_by INT,
    reviewed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Salary records table
CREATE TABLE IF NOT EXISTS salaries (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    month VARCHAR(7) NOT NULL,
    basic_salary DECIMAL(10, 2) NOT NULL,
    allowances DECIMAL(10, 2) DEFAULT 0,
    deductions DECIMAL(10, 2) DEFAULT 0,
    net_salary DECIMAL(10, 2) NOT NULL,
    payment_status ENUM('pending', 'paid') DEFAULT 'pending',
    payment_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_salary (user_id, month)
);

-- Activity logs table
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert default admin user (password: admin123)
INSERT INTO users (username, email, password, role, first_name, last_name) 
VALUES ('admin', 'admin@ems.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'System', 'Administrator');

-- Insert default departments
INSERT INTO departments (name, description) VALUES
('Human Resources', 'Manages employee relations and recruitment'),
('IT Department', 'Information Technology and technical support'),
('Finance', 'Financial management and accounting'),
('Marketing', 'Marketing and communications'),
('Operations', 'Day-to-day operations management'),
('Sales', 'Sales and business development');

-- Sample employees (linked to users)
-- Employee 1: John Doe (IT Department)
INSERT INTO users (username, email, password, role, first_name, last_name, phone) 
VALUES ('john.doe', 'john.doe@ems.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employee', 'John', 'Doe', '+1 234 567 8901');

INSERT INTO employees (user_id, employee_id, department_id, designation, salary, join_date) 
VALUES (2, 'EMP001', 2, 'Senior Developer', 75000.00, '2023-01-15');

-- Employee 2: Jane Smith (HR Department)
INSERT INTO users (username, email, password, role, first_name, last_name, phone) 
VALUES ('jane.smith', 'jane.smith@ems.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employee', 'Jane', 'Smith', '+1 234 567 8902');

INSERT INTO employees (user_id, employee_id, department_id, designation, salary, join_date) 
VALUES (3, 'EMP002', 1, 'HR Manager', 65000.00, '2022-06-01');

-- Employee 3: Mike Johnson (Finance)
INSERT INTO users (username, email, password, role, first_name, last_name, phone) 
VALUES ('mike.johnson', 'mike.johnson@ems.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employee', 'Mike', 'Johnson', '+1 234 567 8903');

INSERT INTO employees (user_id, employee_id, department_id, designation, salary, join_date) 
VALUES (4, 'EMP003', 3, 'Financial Analyst', 60000.00, '2023-03-20');

-- Employee 4: Sarah Williams (Marketing)
INSERT INTO users (username, email, password, role, first_name, last_name, phone) 
VALUES ('sarah.williams', 'sarah.williams@ems.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employee', 'Sarah', 'Williams', '+1 234 567 8904');

INSERT INTO employees (user_id, employee_id, department_id, designation, salary, join_date) 
VALUES (5, 'EMP004', 4, 'Marketing Specialist', 55000.00, '2023-07-10');

-- Sample attendance records
INSERT INTO attendance (user_id, date, check_in, check_out, status) VALUES
(2, CURDATE(), '09:00:00', '18:00:00', 'present'),
(3, CURDATE(), '08:55:00', '17:55:00', 'present'),
(4, CURDATE(), '09:15:00', '18:10:00', 'late'),
(5, CURDATE(), '09:00:00', '18:00:00', 'present'),
(2, DATE_SUB(CURDATE(), INTERVAL 1 DAY), '09:00:00', '18:00:00', 'present'),
(3, DATE_SUB(CURDATE(), INTERVAL 1 DAY), '09:05:00', '17:50:00', 'present'),
(4, DATE_SUB(CURDATE(), INTERVAL 1 DAY), '00:00:00', '00:00:00', 'absent'),
(5, DATE_SUB(CURDATE(), INTERVAL 1 DAY), '09:00:00', '18:00:00', 'present');

-- Sample leave request
INSERT INTO leave_requests (user_id, leave_type, start_date, end_date, days, reason, status) 
VALUES (2, 'sick', DATE_SUB(CURDATE(), INTERVAL 5 DAY), DATE_SUB(CURDATE(), INTERVAL 3 DAY), 3, 'Flu and fever', 'approved');

-- Sample salary records
INSERT INTO salaries (user_id, month, basic_salary, allowances, deductions, net_salary, payment_status, payment_date) VALUES
(2, '2024-01', 75000.00, 5000.00, 2500.00, 77500.00, 'paid', '2024-01-31'),
(3, '2024-01', 65000.00, 3000.00, 2000.00, 66000.00, 'paid', '2024-01-31'),
(4, '2024-01', 60000.00, 2500.00, 1800.00, 60700.00, 'paid', '2024-01-31'),
(5, '2024-01', 55000.00, 2000.00, 1500.00, 55500.00, 'paid', '2024-01-31'),
(2, '2024-02', 75000.00, 5000.00, 2500.00, 77500.00, 'paid', '2024-02-29'),
(3, '2024-02', 65000.00, 3000.00, 2000.00, 66000.00, 'paid', '2024-02-29'),
(4, '2024-02', 60000.00, 2500.00, 1800.00, 60700.00, 'pending', NULL);
