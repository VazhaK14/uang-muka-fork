<?php
session_start();
include "../config/db.php";

if(!isset($_GET['id'])){
    die("ID tidak ditemukan");
}

$id = $_GET['id'];

$q = mysqli_query($conn,"
SELECT *
FROM lpj_detail
WHERE lpj_id='$id'
AND bukti IS NOT NULL
");

?>

<!DOCTYPE html>
<html>
<head>
<title>Lampiran Bukti</title>

<style>

/* =========================
   A4 PRINT STYLE
========================= */

@page{
    size:A4 landscape;
    margin:25mm 10mm 15mm 10mm;
}

/* ========================= */

body{
    font-family:Arial;
    background:#f4f4f4;
    margin:0;
    padding:30px;
    box-sizing:border-box;
}

/* =========================
   WRAPPER VIEW MODE
========================= */

.wrapper{
    max-width:1450px;
    margin:0 auto;
    background:white;
    padding:30px;
    border-radius:18px;
    box-shadow:0 2px 15px rgba(0,0,0,0.08);
}

/* ========================= */

.topbar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:20px;
}

h2{
    margin:0;
    font-size:28px;
}

/* ========================= */

.btn-print{
    background:#ffc107;
    border:none;
    padding:10px 18px;
    border-radius:8px;
    font-weight:bold;
    cursor:pointer;
    box-shadow:0 2px 8px rgba(0,0,0,0.15);
    transition:0.2s;
}

.btn-print:hover{
    background:#e0aa00;
}

/* ========================= */

.container{
    display:grid;
    grid-template-columns:repeat(3, 1fr);
    gap:15px;
    align-items:start;
    margin-top:50px;
}

/* ========================= */

.item{
    border:1px solid #dcdcdc;
    border-radius:10px;
    padding:10px;
    height:500px;
    box-sizing:border-box;
    page-break-inside:avoid;
    background:#fafafa;

    display:flex;
    flex-direction:column;
}

/* ========================= */

.item h4{
    margin:0 0 10px;
    text-align:center;
    font-size:20px;
    font-weight:bold;
}

/* ========================= */

.image-wrapper{
    flex:1;
    display:flex;
    justify-content:center;
    align-items:flex-start;
    overflow:hidden;
}

/* ========================= */

.item img{
    width:100%;
    height:440px;
    object-fit:contain;
    border:1px solid #ddd;
    border-radius:6px;
    background:white;
}

/* =========================
   PRINT MODE
========================= */

@media print{

    body{
        background:white;
        padding:0;
        zoom:95%;
    }

    .wrapper{
        max-width:100%;
        margin:0;
        padding:0;
        box-shadow:none;
        border-radius:0;
    }

    .btn-print{
        display:none;
    }

    .topbar{
        margin-bottom:10px;
    }

    .item{
        break-inside:avoid;
    }
}

</style>

</head>
<body>

<div class="wrapper">

    <div class="topbar">

        <h2>Lampiran Bukti Transaksi</h2>

        <button class="btn-print" onclick="window.print()">
            🖨 Export PDF
        </button>

    </div>

    <div class="container">

    <?php while($d = mysqli_fetch_assoc($q)): ?>

        <div class="item">

            <h4><?= $d['bukti_ref'] ?></h4>

            <div class="image-wrapper">
                <img src="../uploads/<?= $d['bukti'] ?>">
            </div>

        </div>

    <?php endwhile; ?>

    </div>

</div>

</body>
</html>