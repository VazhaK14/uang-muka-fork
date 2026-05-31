<?php
session_start();
include "../config/db.php";

if ($_SESSION['role'] != 'director') {
    header("Location: ../auth/login.php");
    exit;
}

$id = $_GET['id'] ?? 0;

mysqli_query($conn, "
    UPDATE rab 
    SET status='Rejected'
    WHERE id='$id'
");

header("Location: review_rab.php");
exit;