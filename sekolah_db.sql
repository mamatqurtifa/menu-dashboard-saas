-- phpMyAdmin SQL Dump
-- version 5.2.0
-- Host: localhost
-- Generation Time: Apr 11, 2025 at 02:45 AM
-- Server version: 10.4.27-MariaDB
-- PHP Version: 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sekolah_db`
--
CREATE DATABASE IF NOT EXISTS `sekolah_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `sekolah_db`;

-- --------------------------------------------------------

--
-- Table structure for table `guru`
--

CREATE TABLE `guru` (
  `id` int(11) NOT NULL,
  `nip` varchar(20) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `mata_pelajaran` varchar(50) NOT NULL,
  `jenis_kelamin` enum('Laki-laki','Perempuan') NOT NULL,
  `alamat` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `guru`
--

INSERT INTO `guru` (`id`, `nip`, `nama`, `mata_pelajaran`, `jenis_kelamin`, `alamat`) VALUES
(1, '19850512200901001', 'Ahmad Fauzi', 'Matematika', 'Laki-laki', 'Jl. Mawar No. 23, Jakarta Selatan'),
(2, '19880710201001002', 'Siti Rahayu', 'Bahasa Indonesia', 'Perempuan', 'Jl. Melati No. 45, Jakarta Timur'),
(3, '19900815201201003', 'Budi Santoso', 'Fisika', 'Laki-laki', 'Jl. Anggrek No. 12, Jakarta Barat'),
(4, '19871125200901004', 'Dewi Lestari', 'Biologi', 'Perempuan', 'Jl. Kenanga No. 78, Jakarta Utara'),
(5, '19820630199901005', 'Eko Prasetyo', 'Kimia', 'Laki-laki', 'Jl. Dahlia No. 56, Jakarta Pusat');

-- --------------------------------------------------------

--
-- Table structure for table `pengguna`
--

CREATE TABLE `pengguna` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `role` enum('admin','users') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pengguna`
--

INSERT INTO `pengguna` (`id`, `username`, `password`, `nama_lengkap`, `role`) VALUES
(1, 'admin', '$2y$10$YsZJJqxfXfd2XkPbCnKEw.iuTjIiYr.Vjr1oGkB/Z22jIUX7lyCHO', 'Administrator', 'admin'),
(2, 'user', '$2y$10$23ODg8LmMJWZE1bLyBjqEu3M9dn45yjLDpvbqRHT9CdQYzMwAMQcO', 'User Biasa', 'users');

-- --------------------------------------------------------

--
-- Table structure for table `siswa`
--

CREATE TABLE `siswa` (
  `id` int(11) NOT NULL,
  `nis` varchar(20) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `kelas` varchar(10) NOT NULL,
  `jenis_kelamin` enum('Laki-laki','Perempuan') NOT NULL,
  `alamat` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `siswa`
--

INSERT INTO `siswa` (`id`, `nis`, `nama`, `kelas`, `jenis_kelamin`, `alamat`) VALUES
(1, '2023001', 'Anisa Putri', 'XI IPA 1', 'Perempuan', 'Jl. Teratai No. 15, Jakarta Selatan'),
(2, '2023002', 'Dimas Pratama', 'XI IPA 1', 'Laki-laki', 'Jl. Cempaka No. 27, Jakarta Timur'),
(3, '2023003', 'Ratna Sari', 'XI IPA 2', 'Perempuan', 'Jl. Flamboyan No. 8, Jakarta Barat'),
(4, '2023004', 'Fajar Ramadhan', 'XI IPA 2', 'Laki-laki', 'Jl. Bougenville No. 33, Jakarta Pusat'),
(5, '2023005', 'Nadia Safira', 'XI IPS 1', 'Perempuan', 'Jl. Seroja No. 42, Jakarta Utara'),
(6, '2023006', 'Rizky Pratama', 'XI IPS 1', 'Laki-laki', 'Jl. Kamboja No. 19, Jakarta Selatan'),
(7, '2023007', 'Maya Indah', 'XI IPS 2', 'Perempuan', 'Jl. Tulip No. 24, Jakarta Timur'),
(8, '2023008', 'Aditya Nugraha', 'XI IPS 2', 'Laki-laki', 'Jl. Asoka No. 37, Jakarta Barat');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `guru`
--
ALTER TABLE `guru`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nip` (`nip`);

--
-- Indexes for table `pengguna`
--
ALTER TABLE `pengguna`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `siswa`
--
ALTER TABLE `siswa`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nis` (`nis`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `guru`
--
ALTER TABLE `guru`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `pengguna`
--
ALTER TABLE `pengguna`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `siswa`
--
ALTER TABLE `siswa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;