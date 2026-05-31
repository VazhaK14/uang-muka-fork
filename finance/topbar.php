<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include "../config/db.php";

$role = $_SESSION['role'];

// =========================
// AMBIL DATA NOTIF
// =========================
$q_notif = mysqli_query($conn, "
    SELECT *
    FROM notifikasi
    WHERE role_tujuan='$role'
    ORDER BY id DESC
    LIMIT 5
");

$total_notif = mysqli_num_rows(mysqli_query($conn, "
    SELECT *
    FROM notifikasi
    WHERE role_tujuan='$role'
    AND status_baca='belum'
"));
?>

<div class="topbar">

    <div class="topbar-right">

        <!-- ========================= -->
        <!-- PROFILE -->
        <!-- ========================= -->

        <a href="profil.php" class="profile-link">

            <div class="profile-box">

                <img
                    src="https://ui-avatars.com/api/?name=<?= $_SESSION['nama_lengkap'] ?>&background=b8860b&color=fff"
                    class="avatar"
                >

                <div class="profile-text">

                    <div class="nama">
                        <?= $_SESSION['nama_lengkap']; ?>
                    </div>

                    <div class="role">
                        <?= ucfirst($_SESSION['role']); ?>
                    </div>

                </div>

            </div>

        </a>

    </div>

</div>

<style>

/* =========================
   TOPBAR
========================= */

.topbar{
    width: 100%;
    padding: 15px 30px;
    display: flex;
    justify-content: flex-end;
    align-items: center;
    box-sizing: border-box;
}

/* =========================
   RIGHT
========================= */

.topbar-right{
    display: flex;
    align-items: center;
    gap: 25px;
}

/* =========================
   NOTIF
========================= */

.notif{
    position: relative;
    cursor: pointer;
    font-size: 22px;
}

/* ICON */
.icon{
    display: inline-block;
}

/* BADGE */
.badge{
    position: absolute;
    top: -5px;
    right: -8px;

    background: red;
    color: white;

    font-size: 11px;
    font-weight: bold;

    padding: 2px 6px;

    border-radius: 50%;
}

/* =========================
   DROPDOWN
========================= */

.notif-dropdown{

    position: absolute;

    top: 38px;
    right: 0;

    width: 320px;
    max-height: 400px;

    overflow-y: auto;

    background: #fff;

    border-radius: 12px;

    box-shadow: 0 4px 20px rgba(0,0,0,0.15);

    display: none;

    z-index: 99999;
}

/* SHOW */
.notif:hover .notif-dropdown{
    display: block;
}

/* TITLE */
.notif-title{
    padding: 15px;
    font-weight: bold;
    border-bottom: 1px solid #eee;
    color: #111;
    font-size: 15px;
}

/* ITEM */
.notif-item{
    padding: 12px 15px;
    border-bottom: 1px solid #f1f1f1;
}

/* HOVER */
.notif-item:hover{
    background: #f8f8f8;
}

/* MESSAGE */
.notif-message{
    color: #222;
    font-size: 13px;
    line-height: 1.5;
}

/* TIME */
.notif-time{
    margin-top: 6px;
    font-size: 11px;
    color: gray;
}

/* EMPTY */
.notif-empty{
    padding: 20px;
    text-align: center;
    color: gray;
    font-size: 13px;
}

/* =========================
   PROFILE
========================= */

.profile-link{
    text-decoration: none;
    color: inherit;
}

.profile-box{
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
}

/* AVATAR */
.avatar{
    width: 38px;
    height: 38px;
    border-radius: 50%;
}

/* TEXT */
.profile-text{
    line-height: 1.2;
}

/* NAME */
.profile-text .nama{
    font-weight: 600;
    font-size: 14px;
    color: white;
}

/* ROLE */
.profile-text .role{
    font-size: 12px;
    color: #ddd;
}

</style>