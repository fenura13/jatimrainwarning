<?php
$host = 'localhost';
$dbname = 'jatg4813_login'; // Nama database Anda
$username = 'jatg4813_fenura1303'; // Username database Anda
$password = 'Fenura1302&'; // Password database Anda
$port = 3306; // Tambahkan baris ini

try {
    // Menggunakan variabel yang benar dan menyertakan port dalam DSN
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;port=$port", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
