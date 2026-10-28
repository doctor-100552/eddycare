CREATE DATABASE IF NOT EXISTS eddycare CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE eddycare;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(100) NOT NULL,
  email VARCHAR(120) NOT NULL UNIQUE,
  phone VARCHAR(30) NOT NULL,
  password VARCHAR(255) NOT NULL,
  role ENUM('user','admin') NOT NULL DEFAULT 'user',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE doctors (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  specialty VARCHAR(60) NOT NULL,
  bio TEXT,
  rating DECIMAL(2,1) DEFAULT 0,
  reviews INT DEFAULT 0,
  image VARCHAR(100) DEFAULT NULL
);

CREATE TABLE appointments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  doctor_id INT NOT NULL,
  appointment_date DATE NOT NULL,
  appointment_time VARCHAR(10) NOT NULL,
  notes TEXT,
  status ENUM('Pending','Confirmed','Cancelled','Completed') NOT NULL DEFAULT 'Pending',
  admin_response VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE,
  UNIQUE KEY no_double_booking (doctor_id, appointment_date, appointment_time)
);

CREATE TABLE feedback (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT DEFAULT NULL,
  doctor_id INT DEFAULT NULL,
  name VARCHAR(100) NOT NULL,
  rating TINYINT NOT NULL,
  message TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE SET NULL
);

INSERT INTO doctors (name, specialty, bio, rating, reviews, image) VALUES
('Dr. Eddy Kreins','Cardiologist','Board-certified cardiologist with over 15 years of experience, specialising in interventional cardiology with over 2,000 successful procedures.',4.9,128,'Capture2.PNG'),
('Dr. Owomugisha Darius','Neurologist','Renowned neurologist with expertise in treating complex neurological disorders and numerous published research papers.',4.9,95,'Capture3.PNG'),
('Dr. Olibo Edward','Pediatrician','Dedicated to pediatric care, known for a compassionate approach and expertise in childhood development and vaccinations.',4.2,202,'Capture4.PNG'),
('Dr. Tazenya Emmanuel','Dentist','Renowned dentist with expertise in treating complex tooth conditions and published research.',4.6,95,'Capture5.PNG');

-- The default admin (admin@eddycare.com / Admin@123) is created automatically
-- the first time you open admin/login.php. Change the password afterwards.
