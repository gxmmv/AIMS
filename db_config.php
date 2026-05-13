<?php
$host = "localhost";
$dbname = "university_db";
$username = "root";
$password = "";

try {
    // 1. Initial Connection
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 2. Create Database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$dbname` ");

    // 3. STUDENTS TABLE
    $pdo->exec("CREATE TABLE IF NOT EXISTS students (
        id INT AUTO_INCREMENT PRIMARY KEY,
        first_name VARCHAR(50) NOT NULL,
        last_name VARCHAR(50) NOT NULL,
        student_id VARCHAR(15) NOT NULL UNIQUE,
        course VARCHAR(20) NOT NULL,
        section VARCHAR(10) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        email VARCHAR(100) NOT NULL,
        status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
        reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;");

    // 4. USERS TABLE (Faculty)
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL,
        password VARCHAR(255) NOT NULL,
        department VARCHAR(50),
        user_type ENUM('admin', 'faculty') NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;");

    // 5. ADMINS TABLE
    $pdo->exec("CREATE TABLE IF NOT EXISTS admins (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;");

    // 6. FACULTY SCHEDULES
    $pdo->exec("CREATE TABLE IF NOT EXISTS faculty_schedules (
        id INT AUTO_INCREMENT PRIMARY KEY,
        faculty_username VARCHAR(50) NOT NULL,
        day_of_week ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday') NOT NULL,
        scheduled_time VARCHAR(100),
        room VARCHAR(50),
        section VARCHAR(50),
        FOREIGN KEY (faculty_username) REFERENCES users(username) ON DELETE CASCADE
    ) ENGINE=InnoDB;");

    // 7. SCHEDULE REQUESTS
    $pdo->exec("CREATE TABLE IF NOT EXISTS schedule_requests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        faculty_username VARCHAR(50) NOT NULL,
        day VARCHAR(20) NOT NULL,
        proposed_time VARCHAR(100),
        proposed_room VARCHAR(50),
        proposed_section VARCHAR(50),
        status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (faculty_username) REFERENCES users(username) ON DELETE CASCADE
    ) ENGINE=InnoDB;");

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>