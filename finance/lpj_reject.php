<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'finance') {
    header("Location: ../auth/login.php");
    exit;
}

/* ================= HANDLE POST ================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id   = $_POST['id'] ?? null;
    $note = $_POST['rejected_note'] ?? '';
    $user = $_SESSION['id'] ?? 0;

    if(!$id){
        die("ID tidak ditemukan");
    }

    mysqli_query($conn, "
    UPDATE lpj 
    SET 
        status='Rejected',

        approved_by=NULL,
        approved_at=NULL,

        rejected_note='...',
        rejected_by='...',
        rejected_at=NOW()
    WHERE id='$id'
    ");

} 
/* ================= BACKWARD COMPATIBLE (GET) ================= */
else if(isset($_GET['id'])){

    $id = $_GET['id'];

    mysqli_query($conn, "
    UPDATE lpj 
    SET status='Rejected'
    WHERE id='$id'
    ");
}
else {
    die("ID tidak ditemukan");
}

/* ================= REDIRECT ================= */
header("Location: lpj_review.php");
exit;