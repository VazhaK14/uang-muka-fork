<?php
session_start();
include "../config/db.php";

if ($_SESSION['role'] != 'director') {
    header("Location: ../auth/login.php");
    exit;
}

$id = $_GET['id'] ?? 0;

$nama_director = $_SESSION['nama_lengkap'];

mysqli_query($conn, "
    UPDATE rab 
    SET 
        status='Approved',
        approved_by='$nama_director',
        approved_at=NOW()
    WHERE id='$id'
");

header("Location: review_rab.php");
exit;