CREATE DATABASE IF NOT EXISTS udms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE udms;
CREATE TABLE IF NOT EXISTS departments(
 id INT AUTO_INCREMENT PRIMARY KEY, department_name VARCHAR(120) NOT NULL UNIQUE, description VARCHAR(255) NULL) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS categories(
 id INT AUTO_INCREMENT PRIMARY KEY, category_name VARCHAR(120) NOT NULL UNIQUE, description VARCHAR(255) NULL) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS users(
 id INT AUTO_INCREMENT PRIMARY KEY, full_name VARCHAR(120) NOT NULL, username VARCHAR(40) NOT NULL UNIQUE,
 email VARCHAR(150) NOT NULL UNIQUE, password VARCHAR(255) NOT NULL,
 role ENUM('Admin','Faculty','Student','Guest') NOT NULL DEFAULT 'Student',
 department_id INT NULL, id_number VARCHAR(50) NULL,
 status ENUM('Active','Inactive','Pending') NOT NULL DEFAULT 'Pending',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 FOREIGN KEY(department_id) REFERENCES departments(id) ON DELETE SET NULL) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS documents(
 id INT AUTO_INCREMENT PRIMARY KEY, document_id VARCHAR(30) NOT NULL UNIQUE, title VARCHAR(200) NOT NULL, description TEXT NULL,
 document_type VARCHAR(50) NOT NULL, category_id INT NULL, department_id INT NULL,
 file_name VARCHAR(255) NOT NULL, file_path VARCHAR(255) NOT NULL, file_type VARCHAR(10) NULL, file_size INT NULL,
 tags VARCHAR(255) NULL, date_received DATE NULL, uploaded_by INT NOT NULL,
 status ENUM('Draft','Pending Review','Approved','Rejected','Returned for Revision','Archived') NOT NULL DEFAULT 'Draft',
 prev_status VARCHAR(30) NULL,
 access_level ENUM('Public','Faculty Only','Department','Private') NOT NULL DEFAULT 'Private',
 current_version VARCHAR(10) NOT NULL DEFAULT '1.0', approved_at DATETIME NULL, archived_by INT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, archived_at DATETIME NULL,
 FOREIGN KEY(category_id) REFERENCES categories(id) ON DELETE SET NULL,
 FOREIGN KEY(department_id) REFERENCES departments(id) ON DELETE SET NULL,
 FOREIGN KEY(uploaded_by) REFERENCES users(id), INDEX(status), INDEX(uploaded_by)) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS document_versions(
 id INT AUTO_INCREMENT PRIMARY KEY, document_id INT NOT NULL, version_number VARCHAR(10) NOT NULL,
 file_name VARCHAR(255) NOT NULL, file_path VARCHAR(255) NOT NULL, uploaded_by INT NOT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(document_id) REFERENCES documents(id) ON DELETE CASCADE, FOREIGN KEY(uploaded_by) REFERENCES users(id)) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS document_reviews(
 id INT AUTO_INCREMENT PRIMARY KEY, document_id INT NOT NULL, reviewer_id INT NOT NULL, action VARCHAR(40) NOT NULL,
 comments TEXT NULL, reviewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(document_id) REFERENCES documents(id) ON DELETE CASCADE, FOREIGN KEY(reviewer_id) REFERENCES users(id)) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS document_shares(
 id INT AUTO_INCREMENT PRIMARY KEY, document_id INT NOT NULL, shared_by INT NOT NULL, shared_with INT NOT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, UNIQUE(document_id,shared_with),
 FOREIGN KEY(document_id) REFERENCES documents(id) ON DELETE CASCADE,
 FOREIGN KEY(shared_by) REFERENCES users(id), FOREIGN KEY(shared_with) REFERENCES users(id)) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS activity_logs(
 id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NULL, action VARCHAR(60) NOT NULL, document_id INT NULL,
 description VARCHAR(500) NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, INDEX(user_id), INDEX(document_id)) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS notifications(
 id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NOT NULL, message VARCHAR(500) NOT NULL, link VARCHAR(200) NULL,
 is_read TINYINT(1) NOT NULL DEFAULT 0, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, INDEX(user_id,is_read),
 FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE) ENGINE=InnoDB;

-- ========================================================
-- Initial Seed Data
-- ========================================================

-- Departments
INSERT IGNORE INTO departments (id, department_name, description) VALUES
(1, 'College of Computer Studies', 'IT and Computer Science'),
(2, 'College of Engineering', 'Engineering Programs'),
(3, 'Registrar Office', 'Academic Records and Registrar'),
(4, 'Administration', 'University Executive Management');

-- Categories
INSERT IGNORE INTO categories (id, category_name, description) VALUES
(1, 'Academic Records', 'Student transcripts, grades, and clearances'),
(2, 'Administrative Policies', 'Memorandums and campus policies'),
(3, 'Curriculum & Syllabus', 'Course syllabi and instructional guides'),
(4, 'Faculty Clearances', 'Teaching loads and end-of-term requirements');

-- Demo Accounts (Passwords: Admin@123, Faculty@123, Student@123)
INSERT IGNORE INTO users (id, full_name, username, email, password, role, department_id, id_number, status) VALUES
(1, 'System Administrator', 'admin', 'admin@university.edu', '$2y$10$3HrdSmTWjG7YipRWfBiMC.t1wTw2XI27TX3XxfCHKhhjRSz.CJCCG', 'Admin', 4, 'ADM-001', 'Active'),
(2, 'Prof. John Doe', 'faculty1', 'faculty1@university.edu', '$2y$10$h1OUxF.97SjzY/dTOvPJ0OwBWrraSxMpNPy6k4sBZ7epjrIy5xApK', 'Faculty', 1, 'FAC-001', 'Active'),
(3, 'Jane Smith', 'student1', 'student1@university.edu', '$2y$10$SNip94tUNOk4SU3..5Up2uU1W6O9zY57VhyIeHULkiOPr1JjRf8HS', 'Student', 1, 'STU-2026-001', 'Active');

