<?php
session_start();

if (isset($_SESSION['role'])) {

    if ($_SESSION['role'] == 'admin') {
        header("Location: ../admin/dashboard.php");
    } elseif ($_SESSION['role'] == 'manager') {
        header("Location: ../manager/dashboard.php");
    } elseif ($_SESSION['role'] == 'director') {
        header("Location: ../manager/dashboard.php");
    } elseif ($_SESSION['role'] == 'finance') {
        header("Location: ../finance/dashboard.php");
    } elseif ($_SESSION['role'] == 'partner') {
        header("Location: ../partner/dashboard.php");
    }

    exit;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">

<title>Login - Cash Advance System</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    font-family:'Poppins', sans-serif;
    min-height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    overflow:hidden;
    position:relative;

    background-image:
        linear-gradient(
            rgba(184,147,47,0.90),
            rgba(202,168,74,0.90)
        ),
        url("../assets/img/company-bg.jpeg");

    background-size:cover;
    background-position:center;
    background-repeat:no-repeat;
}

/* ================= BACKGROUND CIRCLE ================= */

body::before{
    content:"";
    position:absolute;
    width:350px;
    height:350px;
    background:rgba(255,255,255,0.08);
    border-radius:50%;
    top:-100px;
    left:-100px;
}

body::after{
    content:"";
    position:absolute;
    width:280px;
    height:280px;
    background:rgba(255,255,255,0.08);
    border-radius:50%;
    bottom:-100px;
    right:-80px;
}

/* ================= CONTAINER ================= */

.login-wrapper{
    width:100%;
    max-width:500px;
    padding:20px;
    position:relative;
    z-index:2;
}

/* ================= WELCOME ================= */

.welcome-text{
    text-align:center;
    color:white;
    font-weight:bold;
    margin-bottom:35px;
}

.welcome-text h1{
    font-size:48px;
    font-weight:700;
    letter-spacing:1px;
    margin-bottom:15px;
}

.welcome-text h2{
    font-size:24px;
    font-weight:500;
    line-height:1.5;
    margin-bottom:10px;
}

.welcome-text p{
    font-size:14px;
    opacity:0.9;
}

/* ================= CARD ================= */

.login-card{
    background:rgba(255,255,255,0.96);
    padding:32px;
    border-radius:22px;
    box-shadow:0 15px 40px rgba(0,0,0,0.18);
    backdrop-filter:blur(4px);
}

/* ================= TITLE ================= */

.login-card h3{
    text-align:center;
    margin-bottom:30px;
    font-size:18px;
    font-weight:600;
    color:#333;
}

/* ================= ERROR ================= */

.error{
    background:#ffe5e5;
    color:#d63031;
    padding:12px;
    border-radius:8px;
    text-align:center;
    margin-bottom:20px;
    font-size:14px;
}

/* ================= FORM ================= */

.form-group{
    margin-bottom:20px;
}

.form-group label{
    display:block;
    margin-bottom:8px;
    font-size:14px;
    font-weight:600;
    color:#333;
}

.input-box{
    position:relative;
}

.input-box i{
    position:absolute;
    left:15px;
    top:50%;
    transform:translateY(-50%);
    font-style:normal;
    color:#b8932f;
    font-size:15px;
}

.input-box input{
    width:100%;
    height:50px;
    padding:0 15px 0 45px;
    border:1px solid #ddd;
    border-radius:12px;
    outline:none;
    font-size:14px;
    transition:0.3s;
}

.input-box input:focus{
    border-color:#b8932f;
    box-shadow:0 0 0 3px rgba(184,147,47,0.15);
}

/* ================= BUTTON ================= */

button{
    width:100%;
    height:50px;
    border:none;
    border-radius:12px;
    background:#b8932f;
    color:white;
    font-size:15px;
    font-weight:600;
    cursor:pointer;
    transition:0.3s;
    margin-top:10px;
}

button:hover{
    background:#9f7d25;
    transform:translateY(-2px);
}

/* ================= FOOTER ================= */

.footer{
    text-align:center;
    margin-top:20px;
    font-size:13px;
    color:#999;
}

/* ================= RESPONSIVE ================= */

@media(max-width:768px){

    .welcome-text h1{
        font-size:36px;
    }

    .welcome-text h2{
        font-size:18px;
    }

    .login-card{
        padding:30px;
    }

}

</style>

</head>

<body>

<div class="login-wrapper">

    <!-- WELCOME -->

    <div class="welcome-text">

        <h2>
            Sistem Informasi Akuntansi <br>
            Pengelolaan Cash Advance KAP PQR
        </h2>

        <p>
            Accounting Information System - Cash Advance KAP PQR
        </p>

    </div>

    <!-- LOGIN CARD -->

    <div class="login-card">

        <h3>Silakan login sesuai role</h3>

        <?php if (isset($_GET['error'])) { ?>

            <div class="error">
                Username atau password salah!
            </div>

        <?php } ?>

        <form action="proses_login.php" method="POST">

            <!-- USERNAME -->

            <div class="form-group">

                <label>Username</label>

                <div class="input-box">

                    <i>👤</i>

                    <input
                        type="text"
                        name="username"
                        placeholder="Enter your username"
                        required
                    >

                </div>

            </div>

            <!-- PASSWORD -->

            <div class="form-group">

                <label>Password</label>

                <div class="input-box">

                    <i>🔒</i>

                    <input
                        type="password"
                        name="password"
                        placeholder="Enter your password"
                        required
                    >

                </div>

            </div>

            <!-- BUTTON -->

            <button type="submit" name="login">
                Login
            </button>

        </form>

        <!-- FOOTER -->

        <div class="footer">
            Copyright © 2026 - KAP PQR
        </div>

    </div>

</div>

</body>
</html>