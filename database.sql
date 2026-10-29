CREATE DATABASE IF NOT EXISTS hostel_complaints CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE hostel_complaints;

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  room_number VARCHAR(30) DEFAULT NULL,
  role ENUM('student','admin') NOT NULL DEFAULT 'student',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS complaints (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  title VARCHAR(160) NOT NULL,
  category ENUM('Maintenance','Electrical','Plumbing','Cleanliness','Internet','Security','Other') NOT NULL DEFAULT 'Other',
  description TEXT NOT NULL,
  location VARCHAR(160) NOT NULL,
  priority ENUM('Low','Medium','High','Urgent') NOT NULL DEFAULT 'Medium',
  status ENUM('Submitted','In Progress','Resolved','Rejected') NOT NULL DEFAULT 'Submitted',
  admin_note TEXT DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_complaints_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_complaints_status (status),
  INDEX idx_complaints_user (user_id),
  INDEX idx_complaints_created (created_at)
) ENGINE=InnoDB;
