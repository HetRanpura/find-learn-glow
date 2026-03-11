-- ============================================================
--  FindLearnGlow – COMPLETE DATABASE SCHEMA
--  Run this ONCE in phpMyAdmin → tutor_db → SQL tab
--  This drops everything first and rebuilds clean.
-- ============================================================

CREATE DATABASE IF NOT EXISTS tutor_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE tutor_db;

-- Drop all tables in correct order (children first)
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS batch_enrollments;
DROP TABLE IF EXISTS batches;
DROP TABLE IF EXISTS applications;
DROP TABLE IF EXISTS tutor_courses;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS tutor_availability;
DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS tutors;
DROP TABLE IF EXISTS users;

DROP TRIGGER IF EXISTS update_tutor_rating;

SET FOREIGN_KEY_CHECKS = 1;

-- ─────────────────────────────────────────────────────────────
-- TABLE: users
-- ─────────────────────────────────────────────────────────────
CREATE TABLE users (
    user_id       INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    full_name     VARCHAR(150)    NOT NULL,
    email         VARCHAR(180)    NOT NULL UNIQUE,
    phone         VARCHAR(20)     DEFAULT NULL,
    city          VARCHAR(100)    DEFAULT NULL,
    password_hash VARCHAR(255)    NOT NULL,
    role          ENUM('student','tutor','admin') NOT NULL DEFAULT 'student',
    profile_photo VARCHAR(255)    DEFAULT NULL,
    is_active     TINYINT(1)      NOT NULL DEFAULT 1,
    created_at    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id),
    INDEX idx_email (email),
    INDEX idx_role  (role)
) ENGINE=InnoDB;


-- ─────────────────────────────────────────────────────────────
-- TABLE: tutors
-- ─────────────────────────────────────────────────────────────
CREATE TABLE tutors (
    tutor_id            INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    user_id             INT UNSIGNED     NOT NULL UNIQUE,
    subjects            VARCHAR(500)     NOT NULL,
    hourly_rate         DECIMAL(10,2)    NOT NULL,
    experience_years    TINYINT UNSIGNED NOT NULL DEFAULT 0,
    qualification       VARCHAR(300)     NOT NULL,
    bio                 TEXT             DEFAULT NULL,
    certificate_path    VARCHAR(500)     DEFAULT NULL,
    verification_status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    rating              DECIMAL(3,2)     NOT NULL DEFAULT 0.00,
    total_reviews       INT UNSIGNED     NOT NULL DEFAULT 0,
    is_available        TINYINT(1)       NOT NULL DEFAULT 1,
    created_at          DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (tutor_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_status (verification_status),
    INDEX idx_rating (rating)
) ENGINE=InnoDB;


-- ─────────────────────────────────────────────────────────────
-- TABLE: bookings
-- ─────────────────────────────────────────────────────────────
CREATE TABLE bookings (
    booking_id      INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    student_id      INT UNSIGNED  NOT NULL,
    tutor_id        INT UNSIGNED  NOT NULL,
    subject_booked  VARCHAR(200)  NOT NULL,
    session_date    DATE          NOT NULL,
    session_time    TIME          NOT NULL,
    duration_hours  TINYINT UNSIGNED NOT NULL DEFAULT 1,
    session_mode    ENUM('online','home') NOT NULL DEFAULT 'online',
    address         TEXT          DEFAULT NULL,
    special_notes   TEXT          DEFAULT NULL,
    amount          DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    platform_fee    DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    total_amount    DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    payment_method  ENUM('upi','card','cod') NOT NULL DEFAULT 'upi',
    upi_txn_id      VARCHAR(100)  DEFAULT NULL,
    status          ENUM('pending','pending_verification','confirmed','completed','cancelled') NOT NULL DEFAULT 'pending',
    cancelled_by    ENUM('student','tutor','admin') DEFAULT NULL,
    cancel_reason   VARCHAR(300)  DEFAULT NULL,
    created_at      DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (booking_id),
    FOREIGN KEY (student_id) REFERENCES users(user_id)   ON DELETE CASCADE,
    FOREIGN KEY (tutor_id)   REFERENCES tutors(tutor_id) ON DELETE CASCADE,
    INDEX idx_student (student_id),
    INDEX idx_tutor   (tutor_id),
    INDEX idx_status  (status),
    INDEX idx_date    (session_date)
) ENGINE=InnoDB;


-- ─────────────────────────────────────────────────────────────
-- TABLE: reviews
-- ─────────────────────────────────────────────────────────────
CREATE TABLE reviews (
    review_id   INT UNSIGNED NOT NULL AUTO_INCREMENT,
    booking_id  INT UNSIGNED NOT NULL UNIQUE,
    tutor_id    INT UNSIGNED NOT NULL,
    student_id  INT UNSIGNED NOT NULL,
    rating      TINYINT      NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment     TEXT         DEFAULT NULL,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (review_id),
    FOREIGN KEY (booking_id)  REFERENCES bookings(booking_id) ON DELETE CASCADE,
    FOREIGN KEY (tutor_id)    REFERENCES tutors(tutor_id)     ON DELETE CASCADE,
    FOREIGN KEY (student_id)  REFERENCES users(user_id)       ON DELETE CASCADE,
    INDEX idx_tutor (tutor_id)
) ENGINE=InnoDB;


-- ─────────────────────────────────────────────────────────────
-- TABLE: tutor_availability
-- ─────────────────────────────────────────────────────────────
CREATE TABLE tutor_availability (
    slot_id     INT UNSIGNED NOT NULL AUTO_INCREMENT,
    tutor_id    INT UNSIGNED NOT NULL,
    day_of_week TINYINT      NOT NULL CHECK (day_of_week BETWEEN 0 AND 6),
    start_time  TIME         NOT NULL,
    end_time    TIME         NOT NULL,
    PRIMARY KEY (slot_id),
    FOREIGN KEY (tutor_id) REFERENCES tutors(tutor_id) ON DELETE CASCADE,
    INDEX idx_tutor_day (tutor_id, day_of_week)
) ENGINE=InnoDB;


-- ─────────────────────────────────────────────────────────────
-- TABLE: notifications
-- ─────────────────────────────────────────────────────────────
CREATE TABLE notifications (
    notif_id    INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id     INT UNSIGNED NOT NULL,
    type        VARCHAR(60)  NOT NULL,
    title       VARCHAR(200) NOT NULL,
    message     TEXT         NOT NULL,
    is_read     TINYINT(1)   NOT NULL DEFAULT 0,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (notif_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user_read (user_id, is_read)
) ENGINE=InnoDB;


-- ─────────────────────────────────────────────────────────────
-- TABLE: tutor_courses  (NEW)
-- Tutors define subjects with syllabus and duration
-- ─────────────────────────────────────────────────────────────
CREATE TABLE tutor_courses (
    course_id    INT UNSIGNED NOT NULL AUTO_INCREMENT,
    tutor_id     INT UNSIGNED NOT NULL,
    subject_name VARCHAR(200) NOT NULL,
    syllabus     TEXT         NOT NULL,
    duration     VARCHAR(100) NOT NULL,
    created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (course_id),
    FOREIGN KEY (tutor_id) REFERENCES tutors(tutor_id) ON DELETE CASCADE,
    INDEX idx_tutor (tutor_id)
) ENGINE=InnoDB;


-- ─────────────────────────────────────────────────────────────
-- TABLE: applications  (NEW)
-- Students apply to tutors
-- ─────────────────────────────────────────────────────────────
CREATE TABLE applications (
    application_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    student_id     INT UNSIGNED NOT NULL,
    tutor_id       INT UNSIGNED NOT NULL,
    subject        VARCHAR(200) NOT NULL,
    message        TEXT         DEFAULT NULL,
    status         ENUM('pending','accepted','rejected') NOT NULL DEFAULT 'pending',
    created_at     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (application_id),
    FOREIGN KEY (student_id) REFERENCES users(user_id)   ON DELETE CASCADE,
    FOREIGN KEY (tutor_id)   REFERENCES tutors(tutor_id) ON DELETE CASCADE,
    INDEX idx_student (student_id),
    INDEX idx_tutor   (tutor_id),
    INDEX idx_status  (status)
) ENGINE=InnoDB;


-- ─────────────────────────────────────────────────────────────
-- TABLE: batches  (NEW)
-- Tutor creates group batch schedules
-- ─────────────────────────────────────────────────────────────
CREATE TABLE batches (
    batch_id      INT UNSIGNED NOT NULL AUTO_INCREMENT,
    tutor_id      INT UNSIGNED NOT NULL,
    course_id     INT UNSIGNED NOT NULL DEFAULT 0,
    batch_name    VARCHAR(200) NOT NULL,
    schedule_date DATE         NOT NULL,
    schedule_time TIME         NOT NULL,
    max_students  INT UNSIGNED NOT NULL DEFAULT 20,
    notes         TEXT         DEFAULT NULL,
    status        ENUM('upcoming','ongoing','completed') NOT NULL DEFAULT 'upcoming',
    created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (batch_id),
    FOREIGN KEY (tutor_id) REFERENCES tutors(tutor_id) ON DELETE CASCADE,
    INDEX idx_tutor  (tutor_id),
    INDEX idx_status (status)
) ENGINE=InnoDB;


-- ─────────────────────────────────────────────────────────────
-- TABLE: batch_enrollments  (NEW)
-- Links students to specific batches
-- ─────────────────────────────────────────────────────────────
CREATE TABLE batch_enrollments (
    enrollment_id  INT UNSIGNED NOT NULL AUTO_INCREMENT,
    batch_id       INT UNSIGNED NOT NULL,
    student_id     INT UNSIGNED NOT NULL,
    application_id INT UNSIGNED DEFAULT NULL,
    status         ENUM('scheduled','completed','cancelled') NOT NULL DEFAULT 'scheduled',
    enrolled_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (enrollment_id),
    UNIQUE KEY uq_enrollment (batch_id, student_id),
    FOREIGN KEY (batch_id)   REFERENCES batches(batch_id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(user_id)    ON DELETE CASCADE,
    INDEX idx_student (student_id),
    INDEX idx_batch   (batch_id)
) ENGINE=InnoDB;


-- ─────────────────────────────────────────────────────────────
-- TRIGGER: auto-update tutor rating after review inserted
-- ─────────────────────────────────────────────────────────────
DELIMITER $$

CREATE TRIGGER update_tutor_rating
AFTER INSERT ON reviews
FOR EACH ROW
BEGIN
    UPDATE tutors
    SET
        rating        = (SELECT AVG(rating) FROM reviews WHERE tutor_id = NEW.tutor_id),
        total_reviews = (SELECT COUNT(*)    FROM reviews WHERE tutor_id = NEW.tutor_id)
    WHERE tutor_id = NEW.tutor_id;
END$$

DELIMITER ;


-- ─────────────────────────────────────────────────────────────
-- SEED DATA
-- Admin    password: Admin@1234
-- Tutor    password: Tutor@1234
-- Student  password: Student@123
-- ─────────────────────────────────────────────────────────────
INSERT INTO users (full_name, email, phone, city, password_hash, role) VALUES
(
  'Admin User',
  'admin@findlearnglow.com',
  '9000000000',
  'Mumbai',
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
  'admin'
),
(
  'Priya Sharma',
  'priya@example.com',
  '9876543210',
  'Mumbai',
  '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p/zkBPf3S4FBZ4i0P8D1Ik',
  'tutor'
),
(
  'Rahul Verma',
  'rahul@example.com',
  '9988776655',
  'Delhi',
  '$2y$10$TKh8H1.PkR5dOaOUrqW2FuW5y1RRj1vJYLFy9VaBe6R.4gK.qW.hG',
  'student'
);

INSERT INTO tutors (user_id, subjects, hourly_rate, experience_years, qualification, bio, verification_status, rating, total_reviews) VALUES
(
  2,
  'Mathematics, Physics, Chemistry',
  600.00,
  5,
  'B.Sc Mathematics (Hons), IIT Bombay',
  'Passionate educator with 5 years of experience helping students crack board exams and competitive entrance tests.',
  'approved',
  4.90,
  28
);

INSERT INTO tutor_courses (tutor_id, subject_name, syllabus, duration) VALUES
(
  1,
  'Mathematics — Class 11 & 12',
  'Unit 1: Sets and Functions\nUnit 2: Algebra\nUnit 3: Coordinate Geometry\nUnit 4: Calculus\nUnit 5: Statistics & Probability',
  '3 months'
);
