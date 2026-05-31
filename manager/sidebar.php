<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>

.sidebar{
    width:250px;
    background:black;
    color:white;
    min-height:100vh;
    position:fixed;
    overflow-y:auto;
}

/* JUDUL */
.sidebar h2{
    padding:20px;
    margin:0;
    font-size:17px;
    font-weight:bold;
}

/* MENU */
.menu{
    background:#b8860b;
    margin-bottom:5px;
}

/* LINK MENU */
.menu a{
    color:white;
    text-decoration:none;

    display:flex;
    align-items:center;
    gap:10px;

    padding:15px 20px;

    font-size:14px;
    font-weight:bold;
}

/* HOVER */
.menu:hover{
    background:#a37500;
}

/* ICON */
.menu i{
    width:16px;
    font-size:14px;
}

/* LOGOUT */
.logout{
    position:absolute;
    bottom:0;
    width:100%;
}

</style>

<div class="sidebar">

    <h2>Sistem Informasi Akuntansi Pengelolaan Cash Advance KAP PQR</h2>

    <!-- DASHBOARD -->
    <div class="menu">
        <a href="dashboard.php">
            <i class="fa-solid fa-house"></i>
            Dashboard
        </a>
    </div>

    <!-- REVIEW RAB -->
    <div class="menu">
        <a href="review_rab.php">
            <i class="fa-solid fa-money-bill-transfer"></i>
            Review RAB
        </a>
    </div>

    <!-- LPJ -->
    <div class="menu">
        <a href="lpj_manager.php">
            <i class="fa-solid fa-book"></i>
            Laporan Pertanggungjawaban
        </a>
    </div>

    <!-- LAPORAN CA -->
    <div class="menu">
        <a href="laporan.php">
            <i class="fa-solid fa-folder-open"></i>
            Laporan Cash Advance
        </a>
    </div>

    <!-- LOGOUT -->
    <div class="menu logout">
        <a href="../auth/logout.php">
            <i class="fa-solid fa-right-from-bracket"></i>
            Logout
        </a>
    </div>

</div>