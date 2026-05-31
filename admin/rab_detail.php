<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION['role'])) {
    header("Location: ../auth/login.php");
    exit;
}

$id = $_GET['id'] ?? 0;

$r = mysqli_query($conn, "SELECT * FROM rab WHERE id='$id'");
$data = mysqli_fetch_assoc($r);

$d = mysqli_query($conn, "
    SELECT * FROM rab_detail 
    WHERE rab_id='$id'
    ORDER BY FIELD(kategori,
        'Akomodasi',
        'Transportasi',
        'Konsumsi',
        'Lain-lain'
    )
");

// assistant (aman walau tabel belum ada)
$as = @mysqli_query($conn, "SELECT * FROM rab_assistant WHERE rab_id='$id'");

// format tanggal
function tgl($date){
    return $date ? date('d/m/Y', strtotime($date)) : '-';
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Detail RAB</title>
<style>

body {
    font-family: Arial;
    background:#f4f4f4;
    padding:20px;
}

.container {
    max-width:1000px;
    margin:auto;
    background:#fff;
    padding:35px;
    border-radius:12px;
}

/* ===== TITLE ===== */
h2 {
    text-align:center;
    margin-bottom:5px;
}

.nomor {
    text-align:center;
    margin-bottom:30px;
    color:#555;
}

/* ===== SECTION ===== */
.section {
    margin-bottom:30px;
}

.section h3 {
    margin-bottom:15px;
}

/* ===== FORM STYLE ===== */
.form-view {
    display:flex;
    flex-direction:column;
    gap:12px;
}

.row {
    display:flex;
    align-items:flex-start;
}

.label {
    width:220px;
    color:#333;
}

.colon {
    width:15px;
}

.value {
    flex:1;
    color:#000;
}

/* assistant list */
.value ol {
    margin:0;
    padding-left:18px;
}

/* ===== TABLE ===== */
table {
    border-collapse: collapse;
    width:100%;
    margin-top:10px;
    margin-bottom:20px;
}

th {
    background:#ffc107;
}

th, td {
    border:1px solid #ddd;
    padding:10px;
    text-align:center;
}

/* zebra biar enak dibaca */
tr:nth-child(even){
    background:#fafafa;
}

/* kecilin qty & hari */
.tabel th:nth-child(3),
.tabel td:nth-child(3),
.tabel th:nth-child(4),
.tabel td:nth-child(4){
    width:80px;
}

/* subtotal row */
.subtotal-row td {
    font-weight:bold;
    background:#f9f9f9;
}

/* ===== TOTAL ===== */
.total-box {
    font-size:20px;
    font-weight:bold;
    margin-top:10px;
}

/* ===== BUTTON ===== */
.back {
    display:inline-block;
    margin-top:20px;
    text-decoration:none;
    color:#333;
}

/* ===== TOP BUTTON ===== */
.top-action{
    max-width:1000px;
    margin:auto;
    margin-bottom:15px;
    display:flex;
    justify-content:flex-end;
}

.print-btn{
    background:#b8860b;
    color:white;
    padding:10px 18px;
    border-radius:8px;
    text-decoration:none;
    font-weight:bold;
    display:inline-block;
}

.print-btn:hover{
    opacity:0.9;
}

.ttd{
    margin-top:70px;
    display:flex;
    justify-content:space-between;
    gap:80px;
    page-break-inside: avoid;
}

.ttd-box{
    flex:1;
    text-align:center;
}

.ttd-title{
    margin-bottom:5px;
}

.ttd-role{
    margin-bottom:35px;
}

.approved-box{
    font-weight:bold;
    color:green;
    margin-bottom:12px;
}

.ttd-name{
    font-weight:bold;
}

.ttd-date{
    margin-top:5px;
    color:#555;
    font-size:14px;
}

</style>
</head>

<body>

<div class="top-action">

<a 
    href="javascript:void(0)"
    onclick="window.open(
        'rab_print.php?id=<?= $id ?>',
        '_blank'
    )"
    class="print-btn"
>
    🖨 Export RAB
</a>

</div>

<div class="container">

<h2>Rencana Anggaran Biaya</h2>
<div class="nomor">Nomor : <?= $data['no_rab'] ?></div>

<!-- ================= INFORMASI ================= -->
<div class="section">
<h3>Informasi Penugasan</h3>

<div class="form-view">

<div class="row">
    <div class="label">Nama Klien</div>
    <div class="colon">:</div>
    <div class="value"><?= $data['nama_klien'] ?: '-' ?></div>
</div>

<div class="row">
    <div class="label">Jenis Penugasan</div>
    <div class="colon">:</div>
    <div class="value"><?= $data['jenis_penugasan'] ?: '-' ?></div>
</div>

<div class="row">
    <div class="label">Tahun Buku</div>
    <div class="colon">:</div>
    <div class="value"><?= tgl($data['tahun_buku'] ?? null) ?></div>
</div>

<div class="row">
    <div class="label">Periode Penugasan</div>
    <div class="colon">:</div>
    <div class="value">
        <?= tgl($data['periode_awal'] ?? null) ?> s.d <?= tgl($data['periode_akhir'] ?? null) ?>
    </div>
</div>

</div>
</div>

<!-- ================= TIM ================= -->
<div class="section">
<h3>Susunan Tim Audit</h3>

<div class="form-view">

<div class="row">
    <div class="label">Signing Partner</div>
    <div class="colon">:</div>
    <div class="value"><?= $data['signing_partner'] ?? '-' ?></div>
</div>

<div class="row">
    <div class="label">Partner Review</div>
    <div class="colon">:</div>
    <div class="value"><?= $data['partner_review'] ?? '-' ?></div>
</div>

<div class="row">
    <div class="label">Manager In-Charge</div>
    <div class="colon">:</div>
    <div class="value"><?= $data['manager_ic'] ?? '-' ?></div>
</div>

<div class="row">
    <div class="label">Auditor In-Charge</div>
    <div class="colon">:</div>
    <div class="value"><?= $data['auditor_ic'] ?? '-' ?></div>
</div>

<div class="row">
    <div class="label">Assistant</div>
    <div class="colon">:</div>
    <div class="value">
        <?php 
        if($as){
            $list = "";
            while($a = mysqli_fetch_assoc($as)){
                if(trim($a['nama']) != ''){
                    $list .= "<li>".$a['nama']."</li>";
                }
            }

            if($list != ""){
                echo "<ol>".$list."</ol>";
            } else {
                echo "-";
            }
        } else {
            echo "-";
        }
        ?>
    </div>
</div>

</div>
</div>

<!-- ================= DETAIL ================= -->
<?php
$current_kategori = "";
$subtotal = 0;

while($row = mysqli_fetch_assoc($d)){

    if($current_kategori != $row['kategori']){

        if($current_kategori != ""){
            echo "<tr class='subtotal-row'><td colspan='5'>Subtotal</td><td>Rp ".number_format($subtotal,0,',','.')."</td></tr>";
            echo "</table>";
        }

        $current_kategori = $row['kategori'];
        $subtotal = 0;

        echo "<div class='section'>";
        echo "<h3>$current_kategori</h3>";
        echo "<table class='tabel'>
        <tr>
        <th>No</th>
        <th>Deskripsi</th>
        <th>Qty</th>
        <th>Hari</th>
        <th>Nominal</th>
        <th>Total</th>
        </tr>";
        
        $no = 1;
    }

    $subtotal += $row['total'];

    echo "<tr>
        <td>$no</td>
        <td>{$row['deskripsi']}</td>
        <td>{$row['qty']}</td>
        <td>{$row['hari']}</td>
        <td>Rp ".number_format($row['nominal'],0,',','.')."</td>
        <td>Rp ".number_format($row['total'],0,',','.')."</td>
    </tr>";

    $no++;
}

if($current_kategori != ""){
    echo "<tr class='subtotal-row'><td colspan='5'>Subtotal</td><td>Rp ".number_format($subtotal,0,',','.')."</td></tr>";
    echo "</table></div>";
}
?>

<!-- ================= TOTAL ================= -->
<div class="section">
<h3>Total Anggaran</h3>
<div class="total-box">
Rp <?= number_format($data['total_anggaran'],0,',','.') ?>
</div>

<!-- TTD -->
<div class="ttd">

    <!-- ADMIN -->
    <div class="ttd-box">

        <div class="ttd-title">
            Dibuat oleh,
        </div>

        <div class="ttd-role">
            Admin
        </div>

        <div class="approved-box">
            [ APPROVED DIGITAL ]
        </div>

        <div class="ttd-name">
            <?= $data['submitted_by'] ?? '-' ?>
        </div>

        <div class="ttd-date">

            <?= !empty($data['submitted_at'])
                ? date(
                    'd/m/Y H:i',
                    strtotime($data['submitted_at'])
                )
                : '-'
            ?>

        </div>

    </div>

    <!-- DIRECTOR -->
    <div class="ttd-box">

        <div class="ttd-title">
            Disetujui oleh,
        </div>

        <div class="ttd-role">
            Audit Director
        </div>

        <div class="approved-box">

            <?= !empty($data['approved_at']) 
                ? '[ APPROVED DIGITAL ]'
                : '[ BELUM DISETUJUI ]'
            ?>

        </div>

        <div class="ttd-name">

            <?= !empty($data['approved_by'])
                ? $data['approved_by']
                : '—'
            ?>

        </div>

        <div class="ttd-date">

            <?= !empty($data['approved_at'])
                ? date(
                    'd/m/Y H:i',
                    strtotime($data['approved_at'])
                )
                : '-'
            ?>

        </div>

    </div>

</div>

</div>

<a href="rab_riwayat.php" class="back">← Kembali</a>

</div>

</body>
</html>