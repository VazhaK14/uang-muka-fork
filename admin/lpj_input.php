<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// ambil data RAB yang sudah dicairkan & BELUM ADA LPJ
$query = mysqli_query($conn, "
    SELECT * FROM rab 
    WHERE pencairan = 'Paid'
    AND id NOT IN (SELECT rab_id FROM lpj)
    ORDER BY id DESC
");
?>

<!DOCTYPE html>
<html>
<head>
<title>Input Realisasi</title>

<style>

/* ===== GLOBAL ===== */
body {
    font-family: Arial;
    background:#f4f4f4;
    padding:20px;
}

/* ===== CONTAINER ===== */
.container {
    max-width:1000px;
    margin:auto;
    background:#fff;
    padding:30px;
    border-radius:12px;
}

/* ===== TITLE ===== */
h2 {
    text-align:center;
    margin-bottom:25px;
}

/* ===== TABLE ===== */
table {
    border-collapse: collapse;
    width:100%;
}

th {
    background:#ffc107;
}

th, td {
    border:1px solid #ddd;
    padding:10px;
    text-align:center;
}

/* zebra */
tr:nth-child(even){
    background:#fafafa;
}

/* hover */
tr:hover {
    background:#f1f1f1;
}

/* ===== BUTTON ===== */
.btn {
    padding:6px 12px;
    background:#3498db;
    border:none;
    border-radius:6px;
    text-decoration:none;
    color:white;
    font-weight:bold;
}

.btn:hover {
    background:#2980b9;
}

.deadline-badge{
    padding:6px 12px;
    border-radius:20px;
    color:white;
    font-size:12px;
    font-weight:bold;
    display:inline-block;
}

.aman{
    background:#28a745;
}

.warning{
    background:#f39c12;
}

.terlambat{
    background:#dc3545;
}

</style>

</head>

<body>

<div class="container">

<h2>Input Realisasi (LPJ)</h2>

<table>
<tr>
    <th rowspan="2">No</th>
    <th rowspan="2">Nomor RAB</th>
    <th rowspan="2">Nama Klien</th>
    <th colspan="2">Periode Penugasan</th>
    <th rowspan="2">Batas LPJ</th>
    <th rowspan="2">Status Deadline</th>
    <th rowspan="2">Aksi</th>
</tr>
<tr>
    <th>Awal</th>
    <th>Akhir</th>
</tr>

<?php
$no = 1;
while($d = mysqli_fetch_assoc($query)){

    $awal = date('d/m/Y', strtotime($d['periode_awal']));
    $akhir = date('d/m/Y', strtotime($d['periode_akhir']));
    $batas = date('d/m/Y', strtotime($d['periode_akhir'] . ' +7 days'));

    $today = date('Y-m-d');
$batas_asli = date('Y-m-d', strtotime($d['periode_akhir'].' +7 days'));

$selisih = (strtotime($batas_asli) - strtotime($today)) / 86400;

if($selisih < 0){
    $status_deadline = "<span class='deadline-badge terlambat'>Past Due</span>";
}
elseif($selisih <= 2){
    $status_deadline = "<span class='deadline-badge warning'>Due Soon</span>";
}
else{
    $status_deadline = "<span class='deadline-badge aman'>Not Yet Due</span>";
}

?>

<tr>
    <td><?= $no++ ?></td>
    <td><?= $d['no_rab'] ?></td>
    <td><?= $d['nama_klien'] ?></td>
    <td><?= $awal ?></td>
    <td><?= $akhir ?></td>
    <td><?= $batas ?></td>
    <td><?= $status_deadline ?></td>
    <td>
        <a href="lpj_form.php?id=<?= $d['id'] ?>" class="btn">Input</a>
    </td>
</tr>

<?php } ?>

</table>

</div>

</body>
</html>