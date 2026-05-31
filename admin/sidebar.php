<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
/* SIDEBAR */
.sidebar{
    width:250px;
    background:black;
    color:white;
    min-height:100vh;
    position:fixed;
    overflow-y:auto;
}

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

/* ICON BULAT */
.submenu a i{
    color:#facc15;
    font-size:10px;
}

/* HOVER */
.submenu a:hover{
    background:#111;
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

    <!-- MASTER DATA -->
    <div class="menu">
        <span class="menu-title">
            <i class="fa-solid fa-users"></i>
            Master Data
        </span>
    </div>

    <div class="submenu">
        <a href="data_karyawan.php">
            <i class="fa-solid fa-circle"></i>
            Data Karyawan
        </a>

        <a href="data_klien.php">
            <i class="fa-solid fa-circle"></i>
            Data Klien
        </a>

        <a href="data_penugasan.php">
            <i class="fa-solid fa-circle"></i>
            Data Jenis Penugasan
        </a>
    </div>

    <!-- PERMOHONAN UANG MUKA -->
    <div class="menu">
        <span class="menu-title">
            <i class="fa-solid fa-money-bill-transfer"></i>
            Permohonan Uang Muka
        </span>
    </div>

    <div class="submenu">
        <a href="rab_tambah.php">
            <i class="fa-solid fa-circle"></i>
            Rencana Anggaran Biaya
        </a>

        <a href="rab_riwayat.php">
            <i class="fa-solid fa-circle"></i>
            Riwayat Pengajuan
        </a>
    </div>

    <!-- LPJ -->
    <div class="menu">
        <span class="menu-title">
            <i class="fa-solid fa-book"></i>
            Laporan Pertanggungjawaban
        </span>
    </div>

    <div class="submenu">
        <a href="lpj_input.php">
            <i class="fa-solid fa-circle"></i>
            Input Realisasi
        </a>

        <a href="lpj_riwayat.php">
            <i class="fa-solid fa-circle"></i>
            Riwayat LPJ
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