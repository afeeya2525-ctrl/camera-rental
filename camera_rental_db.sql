-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 09, 2026 at 04:09 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: camera_rental_db
--

-- --------------------------------------------------------

--
-- Table structure for table bookings
--

CREATE TABLE bookings (
  id int(11) NOT NULL,
  user_id int(11) NOT NULL,
  camera_id int(11) NOT NULL,
  start_date date NOT NULL,
  end_date date NOT NULL,
  add_tripod tinyint(1) DEFAULT 0,
  add_battery tinyint(1) DEFAULT 0,
  total_price decimal(10,2) NOT NULL,
  refund_account varchar(100) DEFAULT NULL,
  note text DEFAULT NULL,
  slip_image varchar(255) DEFAULT NULL,
  id_card_image varchar(255) DEFAULT NULL,
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  status varchar(20) NOT NULL DEFAULT 'pending',
  tracking_number varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table bookings
--

INSERT INTO bookings (id, user_id, camera_id, start_date, end_date, add_tripod, add_battery, total_price, refund_account, note, slip_image, id_card_image, created_at, status, tracking_number) VALUES
(1, 2, 1, '2026-10-09', '2026-10-10', 1, 1, 1300.00, 'กสิกร 0980980980', '', '1791469248_slip_Blue and White Simple Modern Resume A4.jpeg', '1791469248_idcard_Blue and White Simple Modern Resume A4.jpeg', '2026-10-08 14:20:48', 'pending', NULL);

-- --------------------------------------------------------

--
-- Table structure for table cameras
--

CREATE TABLE cameras (
  id int(11) NOT NULL,
  camera_name varchar(100) NOT NULL,
  daily_rate decimal(10,2) NOT NULL DEFAULT 300.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table cameras
--

INSERT INTO cameras (id, camera_name, daily_rate) VALUES
(1, 'Canon IXY', 300.00);

-- --------------------------------------------------------

--
-- Table structure for table users
--

CREATE TABLE users (
  id int(11) NOT NULL,
  username varchar(50) NOT NULL,
  password varchar(255) NOT NULL,
  fullname varchar(100) NOT NULL,
  phone varchar(20) NOT NULL,
  line_id varchar(50) DEFAULT NULL,
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  role varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table users
--

INSERT INTO users (id, username, password, fullname, phone, line_id, created_at, role) VALUES
(1, 'feeya', '$2y$10$706P0w7F84aApuuW3buXieMBDJcX.Ou5Hh9zD.nOTYdvqESr8x2QC', 'feeya ya', '0937136749', 'LOL', '2026-10-08 05:00:42', 'Admin'),
(2, 'Dia', '$2y$10$Iw4Z3jiL1NElA3O3QPvWKOwc9x5B7AUPfLeUsfrXsuB7L9k9UQy3.', 'Dia', '0909090909', '0909090909', '2026-10-08 14:18:25', '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table bookings
--
ALTER TABLE bookings
  ADD PRIMARY KEY (id),
  ADD KEY user_id (user_id),
  ADD KEY camera_id (camera_id);

--
-- Indexes for table cameras
--
ALTER TABLE cameras
  ADD PRIMARY KEY (id);

--
-- Indexes for table users
--
ALTER TABLE users
  ADD PRIMARY KEY (id),
  ADD UNIQUE KEY username (username);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table bookings
--
ALTER TABLE bookings
  MODIFY id int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table cameras
--
ALTER TABLE cameras
  MODIFY id int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table users
--
ALTER TABLE users
  MODIFY id int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table bookings
--
ALTER TABLE bookings
  ADD CONSTRAINT bookings_ibfk_1 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
  ADD CONSTRAINT bookings_ibfk_2 FOREIGN KEY (camera_id) REFERENCES cameras (id) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
www.phpmyadmin.net