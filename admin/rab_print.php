<?php
session_start();
include "../config/db.php";

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

$as = @mysqli_query($conn, "
    SELECT * FROM rab_assistant 
    WHERE rab_id='$id'
");

function tgl($date){
    return $date ? date('d/m/Y', strtotime($date)) : '-';
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Print RAB</title>

<style>

body{
    font-family:Arial;
    background:white;
    padding:30px;
    color:#000;
}

.container{
    max-width:1000px;
    margin:auto;
}

h2{
    text-align:center;
    margin-bottom:5px;
}

.nomor{
    text-align:center;
    margin-bottom:40px;
}

.section{
    margin-bottom:30px;
}

.section h3{
    margin-bottom:15px;
}

.form-view{
    display:flex;
    flex-direction:column;
    gap:10px;
}

.row{
    display:flex;
}

.label{
    width:220px;
}

.colon{
    width:20px;
}

.value{
    flex:1;
}

table{
    width:100%;
    border-collapse:collapse;
    margin-top:10px;
    margin-bottom:20px;
}

th{
    background:#ffc107;
}

th, td{
    border:1px solid #ccc;
    padding:10px;
    text-align:center;
}

.subtotal{
    font-weight:bold;
    background:#f5f5f5;
}

.total-box{
    font-size:22px;
    font-weight:bold;
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

.print-btn{
    position:fixed;
    top:20px;
    right:20px;
    background:#b8860b;
    color:white;
    padding:10px 18px;
    border:none;
    border-radius:8px;
    cursor:pointer;
    font-weight:bold;
}

@media print{
    .print-btn{
        display:none;
    }

    body{
        padding:0;
    }

    .ttd{
        margin-top:50px;
    }
}

</style>
</head>

<body>

<script>
window.onload = function(){

    window.print();

    window.onafterprint = function(){
        window.close();
    }

}
</script>

<div class="container">

<h2>RENCANA ANGGARAN BIAYA</h2>

<div class="nomor">
    Nomor : <?= $data['no_rab'] ?>
</div>

<!-- INFORMASI -->
<div class="section">

<h3>Informasi Penugasan</h3>

<div class="form-view">

<div class="row">
    <div class="label">Nama Klien</div>
    <div class="colon">:</div>
    <div class="value"><?= $data['nama_klien'] ?></div>
</div>

<div class="row">
    <div class="label">Jenis Penugasan</div>
    <div class="colon">:</div>
    <div class="value"><?= $data['jenis_penugasan'] ?></div>
</div>

<div class="row">
    <div class="label">Tahun Buku</div>
    <div class="colon">:</div>
    <div class="value"><?= tgl($data['tahun_buku']) ?></div>
</div>

<div class="row">
    <div class="label">Periode Penugasan</div>
    <div class="colon">:</div>
    <div class="value">
        <?= tgl($data['periode_awal']) ?>
        s.d
        <?= tgl($data['periode_akhir']) ?>
    </div>
</div>

</div>
</div>

<!-- TIM -->
<div class="section">

<h3>Susunan Tim Audit</h3>

<div class="form-view">

<div class="row">
    <div class="label">Signing Partner</div>
    <div class="colon">:</div>
    <div class="value"><?= $data['signing_partner'] ?: '-' ?></div>
</div>

<div class="row">
    <div class="label">Partner Review</div>
    <div class="colon">:</div>
    <div class="value"><?= $data['partner_review'] ?: '-' ?></div>
</div>

<div class="row">
    <div class="label">Manager In-Charge</div>
    <div class="colon">:</div>
    <div class="value"><?= $data['manager_ic'] ?: '-' ?></div>
</div>

<div class="row">
    <div class="label">Auditor In-Charge</div>
    <div class="colon">:</div>
    <div class="value"><?= $data['auditor_ic'] ?: '-' ?></div>
</div>

<div class="row">
    <div class="label">Assistant</div>
    <div class="colon">:</div>
    <div class="value">

        <?php
        $assistant = [];

        if($as){
            while($a = mysqli_fetch_assoc($as)){
                if(trim($a['nama']) != ''){
                    $assistant[] = $a['nama'];
                }
            }
        }

        echo count($assistant)
            ? implode(', ', $assistant)
            : '-';
        ?>

    </div>
</div>

</div>
</div>

<!-- DETAIL -->
<?php

$current_kategori = "";
$subtotal = 0;

while($row = mysqli_fetch_assoc($d)){

    if($current_kategori != $row['kategori']){

        if($current_kategori != ""){
            echo "
            <tr class='subtotal'>
                <td colspan='5'>Subtotal</td>
                <td>Rp ".number_format($subtotal,0,',','.')."</td>
            </tr>
            </table>
            ";
        }

        $current_kategori = $row['kategori'];
        $subtotal = 0;

        echo "
        <div class='section'>
        <h3>$current_kategori</h3>

        <table>
        <tr>
            <th>No</th>
            <th>Deskripsi</th>
            <th>Qty</th>
            <th>Hari</th>
            <th>Nominal</th>
            <th>Total</th>
        </tr>
        ";

        $no = 1;
    }

    $subtotal += $row['total'];

    echo "
    <tr>
        <td>$no</td>
        <td>{$row['deskripsi']}</td>
        <td>{$row['qty']}</td>
        <td>{$row['hari']}</td>
        <td>Rp ".number_format($row['nominal'],0,',','.')."</td>
        <td>Rp ".number_format($row['total'],0,',','.')."</td>
    </tr>
    ";

    $no++;
}

if($current_kategori != ""){
    echo "
    <tr class='subtotal'>
        <td colspan='5'>Subtotal</td>
        <td>Rp ".number_format($subtotal,0,',','.')."</td>
    </tr>
    </table>
    ";
}

?>

<!-- TOTAL -->
<div class="section">

<h3>Total Anggaran</h3>

<div class="total-box">
    Rp <?= number_format($data['total_anggaran'],0,',','.') ?>
</div>

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

</body>
</html>