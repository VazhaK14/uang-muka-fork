<?php
session_start();
include "../config/db.php";

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = md5($_POST['password']);

    $query = mysqli_query($conn, "SELECT * FROM users WHERE username='$username' AND password='$password'");
    $data = mysqli_fetch_assoc($query);

    if ($data) {
        $_SESSION['id_user'] = $data['id'];
        $_SESSION['user_id'] = $data['id'];
        $_SESSION['nama_lengkap'] = $data['nama_lengkap'];
        $_SESSION['username'] = $data['username'];
        $_SESSION['role'] = $data['role'];

        if ($data['role'] == 'admin') {
            header("Location: ../admin/dashboard.php");
        } elseif ($data['role'] == 'manager') {
            header("Location: ../manager/dashboard.php");
        } elseif ($data['role'] == 'director') {
            header("Location: ../manager/dashboard.php");
        } elseif ($data['role'] == 'finance') {
            header("Location: ../finance/dashboard.php");
        } elseif ($data['role'] == 'partner') {
            header("Location: ../partner/dashboard.php");
        }
        exit;
    } else {
        header("Location: login.php?error=1");
        exit;
    }
}
?>