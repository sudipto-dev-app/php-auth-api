-- =====================================================
-- Auth API — Database Schema
-- MySQL এ এই SQL run করো
-- =====================================================

-- প্রথমে database তৈরি করো
CREATE DATABASE IF NOT EXISTS auth_api_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE auth_api_db;

-- =====================================================
-- users table — সব user এর credentials এখানে থাকবে
-- =====================================================
CREATE TABLE IF NOT EXISTS users (
    id          INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100)    NOT NULL,
    email       VARCHAR(255)    NOT NULL UNIQUE,
    password    VARCHAR(255)    NOT NULL,       -- bcrypt hashed
    created_at  DATETIME        DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME        DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    last_login  DATETIME        DEFAULT NULL,
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- password_resets — forgot password এর OTP রাখবে
-- =====================================================
CREATE TABLE IF NOT EXISTS password_resets (
    id          INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    email       VARCHAR(255)    NOT NULL,
    otp         VARCHAR(6)      NOT NULL,
    expires_at  DATETIME        NOT NULL,
    created_at  DATETIME        DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email_otp (email, otp)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- Test user (password: test1234)
-- =====================================================
INSERT INTO users (name, email, password) VALUES
('Test User', 'test@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
