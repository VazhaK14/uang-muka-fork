<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

include "../config/db.php";

// ================= KPI =================

// Total Penugasan
$q1 = mysqli_query($conn, "
    SELECT COUNT(*) as total 
    FROM rab 
    WHERE pencairan='Paid'
");
$total_penugasan = mysqli_fetch_assoc($q1)['total'] ?? 0;

// Revisi RAB
$q2 = mysqli_query($conn, "
    SELECT COUNT(*) as total 
    FROM rab 
    WHERE status='Rejected'
");
$revisi_rab = mysqli_fetch_assoc($q2)['total'] ?? 0;

// Revisi LPJ
$q3 = mysqli_query($conn, "
    SELECT COUNT(*) as total 
    FROM lpj 
    WHERE status='Rejected'
");
$revisi_lpj = mysqli_fetch_assoc($q3)['total'] ?? 0;

// ✅ KPI BARU: LPJ OUTSTANDING
$q4 = mysqli_query($conn, "
    SELECT COUNT(*) as total
    FROM rab r
    LEFT JOIN lpj l ON r.id = l.rab_id
    WHERE r.pencairan = 'Paid'
    AND l.id IS NULL
");
$lpj_outstanding = mysqli_fetch_assoc($q4)['total'] ?? 0;


// ================= LPJ OUTSTANDING =================
$q_outstanding = mysqli_query($conn, "
    SELECT r.*
    FROM rab r
    LEFT JOIN lpj l ON r.id = l.rab_id
    WHERE r.pencairan = 'Paid'
    AND l.id IS NULL
    ORDER BY r.id DESC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Dashboard Admin</title>

<style>
body {
    margin: 0;
    font-family: Arial;
    display: flex;
}

/* CONTENT */
.content{
    margin-left:250px;
    width:calc(100% - 250px);

    min-height:100vh;
    padding:25px;
    color:white;

    background:
        linear-gradient(
            rgba(102,102,102,0.88),
            rgba(102,102,102,0.88)
        ),
        url('../assets/img/company-bg.jpeg');

    background-size:cover;
    background-position:center;
    background-repeat:no-repeat;

    background-attachment:fixed;
}

.topbar {
    display: flex;
    justify-content: flex-end;
    gap: 30px;
    margin-bottom: 30px;
}

/* KPI */
.card-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
    margin: 30px 0;
}

.card {
    background: white;
    color: black;
    padding: 20px;
    border-radius: 15px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    border-left: 10px solid rgb(201,155,40);
}

.card h3 {
    margin: 0;
    font-size: 16px;
    color: rgb(201,155,40);
    font-weight: bold;
}

.card p {
    font-size: 32px;
    font-weight: bold;
    margin: 5px;
}

/* SECTION */
.section {
    background: white;
    color: black;
    padding: 20px;
    border-radius: 10px;
    margin-top: 20px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}

.section b {
    font-size: 16px;
}

/* TABLE */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}

th {
    background: #ffc107;
    padding: 10px;
}

td {
    padding: 10px;
    border: 1px solid #ddd;
    text-align: center;
}

tr:hover {
    background: #f9f9f9;
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

table{
    border-collapse: collapse;
    width:100%;
    background:white;
}

/* BUTTON INPUT */
.btn-input {
    color: white;
    background: #3498db;
    padding: 5px 10px;
    border-radius: 5px;
    text-decoration: none;
    font-size: 13px;
}

.btn-input:hover {
    background: #2980b9;
}

</style>

</head>
<body>

<?php include "sidebar.php"; ?>

<div class="content">

<?php include "topbar.php"; ?>

<h1>Dashboard Admin</h1>
<p>Selamat datang, <b><?= $_SESSION['nama_lengkap']; ?></b></p>

<!-- KPI -->
<div class="card-container">

    <div class="card">
        <h3>Total Penugasan</h3>
        <p><?= $total_penugasan ?></p>
    </div>

    <div class="card">
        <h3>Cash Advance Outstanding</h3>
        <p><?= $lpj_outstanding ?></p>
    </div>

    <div class="card">
        <h3>Revisi RAB</h3>
        <p><?= $revisi_rab ?></p>
    </div>

    <div class="card">
        <h3>Revisi LPJ</h3>
        <p><?= $revisi_lpj ?></p>
    </div>

</div>

<!-- LPJ OUTSTANDING -->
<div class="section">

<b>Cash Advance Outstanding</b>

<table>
<thead>

<tr>
<th>No</th>
<th>Nomor RAB</th>
<th>Nama Klien</th>
<th>Periode Penugasan</th>
<th>Batas LPJ</th>
<th>Status Deadline</th>
<th>Aksi</th>
</tr>

</thead>
<tbody>

<?php 
$no = 1;

while($o = mysqli_fetch_assoc($q_outstanding)):

$awal = date('d/m/Y', strtotime($o['periode_awal']));
$akhir = date('d/m/Y', strtotime($o['periode_akhir']));
$batas = date('d/m/Y', strtotime($o['periode_akhir'].' +7 days'));

$today = date('Y-m-d');
$batas_asli = date('Y-m-d', strtotime($o['periode_akhir'].' +7 days'));

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
<td><?= $o['no_rab'] ?></td>
<td><?= $o['nama_klien'] ?></td>
<td><?= $awal ?> - <?= $akhir ?></td>
<td><?= $batas ?></td>

<td><?= $status_deadline ?></td>

<td>
<a href="lpj_form.php?id=<?= $o['id'] ?>" class="btn-input">
    Input
</a>
</td>

</tr>

<?php endwhile; ?>

<?php if($no == 1): ?>
<tr>
<td colspan="7">Tidak ada data</td>
</tr>
<?php endif; ?>

</tbody>
</table>

</div>

</div>

</body>
</html>