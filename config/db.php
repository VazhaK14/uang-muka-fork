<?php
$conn = mysqli_connect("localhost", "root", "", "uang_muka");

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}
?>