<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include "../config/db.php";

// mapping role biar konsisten tampilan
$role = $_SESSION['role'] == 'director' ? 'Audit Director' : ucfirst($_SESSION['role']);
?>

<div class="topbar">

    <div class="topbar-right">

        <!-- PROFIL -->
        <a href="profil.php" class="profile-link">

            <div class="profile-box">

                <!-- AVATAR -->
                <img src="https://ui-avatars.com/api/?name=<?= $_SESSION['nama_lengkap'] ?>&background=b8860b&color=fff" class="avatar">

                <!-- TEXT -->
                <div class="profile-text">
                    <div class="nama">
                        <?= $_SESSION['nama_lengkap']; ?>
                    </div>
                    <div class="role">
                        <?= $role; ?>
                    </div>
                </div>

            </div>

        </a>

    </div>

</div>

<style>

/* TOPBAR */
.topbar {
    display: flex;
    justify-content: flex-end;
    padding: 15px 30px;
}

/* RIGHT */
.topbar-right {
    display: flex;
    align-items: center;
    gap: 25px;
}

/* NOTIF */
.notif {
    position: relative;
    cursor: pointer;
    font-size: 20px;
}

.badge {
    position: absolute;
    top: -5px;
    right: -8px;
    background: red;
    color: white;
    font-size: 11px;
    padding: 2px 6px;
    border-radius: 50%;
}

/* PROFILE */
.profile-link {
    text-decoration: none;
    color: inherit;
}

.profile-box {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
}

.avatar {
    width: 35px;
    height: 35px;
    border-radius: 50%;
}

.profile-text {
    line-height: 1.2;
}

.profile-text .nama {
    font-weight: 600;
    font-size: 14px;
}

.profile-text .role {
    font-size: 12px;
    color: #ffffff;
}

</style>