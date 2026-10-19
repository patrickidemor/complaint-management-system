-- Web Based Complaint Management System
-- Database Schema (matches the ERD in Chapter Three)

CREATE DATABASE IF NOT EXISTS complaint_management;
USE complaint_management;

-- Departments
CREATE TABLE departments (
    department_id INT AUTO_INCREMENT PRIMARY KEY,
    department_name VARCHAR(100) NOT NULL
);

-- Administrators
CREATE TABLE administrators (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    department_id INT,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(department_id)
);

-- Users / Complainants
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Complaint Categories
CREATE TABLE categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL
);

-- Complaint Status lookup
CREATE TABLE complaint_status (
    status_id INT AUTO_INCREMENT PRIMARY KEY,
    status_name VARCHAR(50) NOT NULL
);

-- Complaints
CREATE TABLE complaints (
    complaint_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    category_id INT NOT NULL,
    status_id INT NOT NULL DEFAULT 1,
    admin_id INT NULL,
    title VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    date_submitted TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (category_id) REFERENCES categories(category_id),
    FOREIGN KEY (status_id) REFERENCES complaint_status(status_id),
    FOREIGN KEY (admin_id) REFERENCES administrators(admin_id)
);

-- Responses
CREATE TABLE responses (
    response_id INT AUTO_INCREMENT PRIMARY KEY,
    complaint_id INT NOT NULL,
    admin_id INT NOT NULL,
    response_text TEXT NOT NULL,
    response_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (complaint_id) REFERENCES complaints(complaint_id),
    FOREIGN KEY (admin_id) REFERENCES administrators(admin_id)
);

-- Activity Logs
CREATE TABLE activity_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(255) NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- Seed data: default complaint statuses
INSERT INTO complaint_status (status_name) VALUES
('Submitted'), ('Under Review'), ('Assigned'), ('In Progress'), ('Resolved'), ('Closed');

-- Seed data: sample departments
INSERT INTO departments (department_name) VALUES
('Student Affairs'), ('ICT Unit'), ('Academic Affairs'), ('Works and Maintenance');

-- Seed data: sample categories
INSERT INTO categories (category_name) VALUES
('Academic'), ('Hostel/Accommodation'), ('ICT/Portal'), ('Facilities'), ('Welfare'), ('Other');

-- No default administrator is seeded here, because a PHP-generated
-- password hash cannot safely be hardcoded into this file.
-- After importing this file, open setup_admin.php in your browser ONCE
-- to create the first administrator account, then delete that file.
