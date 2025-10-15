-- Create database
CREATE DATABASE student_management_system;
USE student_management_system;

-- Students table
CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    prn_number VARCHAR(20) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(15),
    class VARCHAR(50) NOT NULL,
    division VARCHAR(10) NOT NULL,
    date_of_birth DATE,
    address TEXT,
    parent_name VARCHAR(100),
    parent_phone VARCHAR(15),
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Teachers table
CREATE TABLE teachers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id VARCHAR(20) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(15),
    subject VARCHAR(100),
    type ENUM('subject', 'class') NOT NULL,
    class_assigned VARCHAR(50),
    division_assigned VARCHAR(10),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Subjects table
CREATE TABLE subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject_code VARCHAR(20) UNIQUE NOT NULL,
    subject_name VARCHAR(100) NOT NULL,
    teacher_id VARCHAR(20),
    class VARCHAR(50),
    FOREIGN KEY (teacher_id) REFERENCES teachers(teacher_id)
);

-- Attendance table
CREATE TABLE attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_prn VARCHAR(20) NOT NULL,
    subject_code VARCHAR(20) NOT NULL,
    date DATE NOT NULL,
    status ENUM('present', 'absent', 'late') NOT NULL,
    recorded_by VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_prn) REFERENCES students(prn_number),
    FOREIGN KEY (subject_code) REFERENCES subjects(subject_code)
);

-- Marks table
CREATE TABLE marks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_prn VARCHAR(20) NOT NULL,
    subject_code VARCHAR(20) NOT NULL,
    exam_type VARCHAR(50) NOT NULL,
    marks_obtained DECIMAL(5,2) NOT NULL,
    total_marks DECIMAL(5,2) NOT NULL,
    uploaded_by VARCHAR(20) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_prn) REFERENCES students(prn_number),
    FOREIGN KEY (subject_code) REFERENCES subjects(subject_code)
);

-- Notices table
CREATE TABLE notices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    posted_by VARCHAR(20) NOT NULL,
    target_audience ENUM('all', 'students', 'teachers') DEFAULT 'all',
    class VARCHAR(50),
    division VARCHAR(10),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admin (class teacher)
INSERT INTO teachers (teacher_id, password, full_name, email, type, class_assigned, division_assigned) 
VALUES ('ADMIN001', 'admin123', 'Admin Teacher', 'admin@sms.com', 'class', 'All', 'All');

-- Insert some sample subjects
INSERT INTO subjects (subject_code, subject_name) VALUES
('MATH101', 'Mathematics'),
('PHY101', 'Physics'),
('CHE101', 'Chemistry'),
('ENG101', 'English'),
('CS101', 'Computer Science');