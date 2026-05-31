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

/* MENU UTAMA */
.menu{
    background:#b8860b;
    margin-bottom:5px;
}

/* LINK MENU */
.menu a,
.menu-title{
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

/* ICON MENU */
.menu i{
    width:16px;
    font-size:14px;
}

/* SUBMENU */
.submenu{
    background:black;
}

/* LINK SUBMENU */
.submenu a{
    display:flex;
    align-items:center;
    gap:10px;

    padding:14px 25px;

    color:white;
    text-decoration:none;

    font-size:14px;

    border-top:1px solid rgba(255,255,255,0.05);
}

/* ICON SUBMENU */
.submenu a i{
    font-size:10px;
    color:#facc15;
}

/* HOVER SUBMENU */
.submenu a:hover{
    background:#111;
}

/* LOGOUT */
.logout{
    position:absolute;
    bottom:0;
    width:100%;
}

/* CONTENT */
.content{
    margin-left:250px;
    padding:20px;
}

</style>

<div class="sidebar">
    <h2>Sistem Informasi Akuntansi Pengelolaan Cash Advance KAP PQR</h2>

    <!-- MENU UTAMA -->
    <div class="menu">
    <a href="dashboard.php">
        <i class="fa-solid fa-house"></i>
        Dashboard
    </a>
</div>

<div class="menu">
    <a href="pencairan.php">
        <i class="fa-solid fa-money-bill-transfer"></i>
        Pencairan Dana
    </a>
</div>

<div class="menu">
    <a href="lpj_review.php">
        <i class="fa-solid fa-book"></i>
        Review Laporan Pertanggungjawaban
    </a>
</div>

<div class="menu">
    <span class="menu-title">
        <i class="fa-solid fa-folder-open"></i>
        Laporan Penugasan
    </span>
</div>

    <!-- 🔥 SUBMENU (FITUR TURUNAN) -->
    <div class="submenu">

    <a href="data_akun.php">
        <i class="fa-solid fa-circle"></i>
        Data Akun
    </a>

    <a href="jurnal_umum.php">
        <i class="fa-solid fa-circle"></i>
        Jurnal Umum
    </a>

    <a href="data_laporan.php">
        <i class="fa-solid fa-circle"></i>
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