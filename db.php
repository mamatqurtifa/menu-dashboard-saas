<?php
$host = 'localhost';
$username = 'root';
$password = 'your_password';
$database = 'sekolah_db';

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

function createTables($conn) {
        $sql_pengguna = "CREATE TABLE IF NOT EXISTS pengguna (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        nama_lengkap VARCHAR(100) NOT NULL,
        role ENUM('admin', 'users') NOT NULL
    )";
    
        $sql_siswa = "CREATE TABLE IF NOT EXISTS siswa (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        nis VARCHAR(20) NOT NULL UNIQUE,
        nama VARCHAR(100) NOT NULL,
        kelas VARCHAR(10) NOT NULL,
        jenis_kelamin ENUM('Laki-laki', 'Perempuan') NOT NULL,
        alamat TEXT
    )";
    
        $sql_guru = "CREATE TABLE IF NOT EXISTS guru (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        nip VARCHAR(20) NOT NULL UNIQUE,
        nama VARCHAR(100) NOT NULL,
        mata_pelajaran VARCHAR(50) NOT NULL,
        jenis_kelamin ENUM('Laki-laki', 'Perempuan') NOT NULL,
        alamat TEXT
    )";
    
    $conn->query($sql_pengguna);
    $conn->query($sql_siswa);
    $conn->query($sql_guru);
    
        $check = $conn->query("SELECT * FROM pengguna WHERE username='admin'");
    if ($check->num_rows == 0) {
        $password = password_hash('admin123', PASSWORD_DEFAULT);
        $conn->query("INSERT INTO pengguna (username, password, nama_lengkap, role) VALUES ('admin', '$password', 'Administrator', 'admin')");
    }
    
        $check = $conn->query("SELECT * FROM pengguna WHERE username='user'");
    if ($check->num_rows == 0) {
        $password = password_hash('user123', PASSWORD_DEFAULT);
        $conn->query("INSERT INTO pengguna (username, password, nama_lengkap, role) VALUES ('user', '$password', 'User Biasa', 'users')");
    }
}

createTables($conn);
?>