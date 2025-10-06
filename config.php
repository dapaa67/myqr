<?php
// SET TIMEZONE DEFAULT UNTUK SELURUH APLIKASI
date_default_timezone_set('Asia/Jakarta');

session_start();

define('DB_HOST', 'localhost');
define('DB_USER', 'u822465743_user');
define('DB_PASS', 'Absen1..');
define('DB_NAME', 'u822465743_absen');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// !!! TAMBAHKAN BARIS INI UNTUK MENYAMAKAN TIMEZONE DATABASE !!!
// Mengatur timezone untuk koneksi ini ke UTC+7 (WIB)
$conn->query("SET time_zone = '+07:00'");

?>