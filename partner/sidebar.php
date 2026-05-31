<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>

.sidebar{
    width:250px;
    background:black;
    color:white;
    min-height:100vh;
    position:fixed;

    display:flex;
    flex-direction:column;
}

.sidebar h2{
    padding:20px;
    margin:0;
    font-size:17px;
}

/* MENU UTAMA */
.menu{
    background:#b8860b;
    margin-bottom:5px;
}

.menu a,
.menu-title{
    display:flex;
    align-items:center;
    gap:12px;

    padding:15px 20px;
    color:white;
    text-decoration:none;
    font-weight:bold;
    font-size:14px;
}

.menu:hover{
    background:#a37500;
}

/* SUBMENU */
.submenu{
    background:black;
    width:100%;
}

.submenu a{
    display:flex;
    align-items:center;
    gap:12px;

    padding:15px 20px;
    color:white;
    text-decoration:none;

    font-size:14px;
    font-weight:normal;

    border-top:1px solid rgba(255,255,255,0.05);

    background:black;
    border-radius:0;
    margin:0;
}

/* HOVER */
.submenu a:hover{
    background:#111;
}

/* ICON BULAT KUNING */
.submenu a i{
    color:#ffd43b;
    font-size:10px;
}

/* LOGOUT */
.logout-menu{
    margin-top:auto;
    background:#b8860b;
}

.logout-menu a{
    display:flex;
    align-items:center;
    gap:12px;

    padding:15px 20px;
    color:white;
    text-decoration:none;
    font-weight:bold;
    font-size:14px;
}

.logout-menu:hover{
    background:#a37500;
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

    <!-- LAPORAN -->
    <div class="menu">

        <span class="menu-title">
            <i class="fa-solid fa-folder-open"></i>
            Laporan Penugasan
        </span>

        <div class="submenu">

            <a href="master_dokumen.php">
                <i class="fa-solid fa-circle"></i>
                Master Dokumen
            </a>

            <a href="laporan.php">
                <i class="fa-solid fa-circle"></i>
                Laporan Cash Advance
            </a>

        </div>

    </div>

    <!-- LOGOUT -->
    <div class="logout-menu">
        <a href="../auth/logout.php">
            <i class="fa-solid fa-right-from-bracket"></i>
            Logout
        </a>
    </div>

</div>