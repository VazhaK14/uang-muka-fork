<?php
$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';
$name = getenv('DB_NAME') ?: 'uang_muka';
$port = (int)(getenv('DB_PORT') ?: 3306);

$conn = mysqli_connect($host, $user, $pass, $name, $port);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}
?>