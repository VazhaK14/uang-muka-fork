<?php
session_start();
include "../config/db.php";

$id = $_GET['id'];

$q = mysqli_query($conn, "
SELECT r.*, p.*
FROM rab r
JOIN pencairan p ON r.id = p.rab_id
WHERE r.id='$id'
");

$d = mysqli_fetch_assoc($q);
?>

<!DOCTYPE html>
<html>
<head>
<title>Detail Pencairan</title>

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
    padding:30px;
    border-radius:12px;
}

.center { text-align:center; }

.section { margin-top:25px; }

/* FORM */
.form-view .row {
    display:flex;
    margin-bottom:8px;
}

.label { width:200px; }
.colon { width:10px; }
.value { flex:1; }

/* TABLE */
table {
    border-collapse: collapse;
    width:100%;
    margin-top:10px;
    margin-bottom:15px;
}

th {
    background:#ffc107;
}

th, td {
    border:1px solid #ddd;
    padding:8px;
    text-align:center;
}

tr:nth-child(even){
    background:#fafafa;
}

.subtotal td {
    font-weight:bold;
    background:#f9f9f9;
}

.subtotal td:last-child {
    text-align:right;
}

/* TOTAL */
.total-box {
    text-align:right;
    font-size:18px;
    font-weight:bold;
    margin-top:10px;
}

h4 {
    margin-bottom:5px;
}

hr{
    border:0;
    border-top:2px dashed #ccc;
    margin:30px 0;
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

</style>

</head>

<body>

<div class="container">

<div style="text-align:right; margin-bottom:20px;">

    <a 
        href="javascript:void(0)"
        onclick="window.open(
            'pencairan_print.php?id=<?= $id ?>',
            '_blank'
        )"
        class="print-btn"
    >
        🖨 Export Form
    </a>

</div>

<h3 class="center">FORMULIR PENCAIRAN DANA</h3>
<p class="center">Nomor : <?= $d['no_pencairan'] ?></p>

<!-- ================= INFO ================= -->
<div class="section form-view">

<div class="row">
    <div class="label">Tanggal Cetak</div>
    <div class="colon">:</div>
    <div class="value"><?= date('d/m/Y',strtotime($d['tanggal'])) ?></div>
</div>

<div class="row">
    <div class="label">Nomor RAB</div>
    <div class="colon">:</div>
    <div class="value"><?= $d['no_rab'] ?></div>
</div>

<div class="row">
    <div class="label">Nama Klien</div>
    <div class="colon">:</div>
    <div class="value"><?= $d['nama_klien'] ?></div>
</div>

<div class="row">
    <div class="label">Jenis Penugasan</div>
    <div class="colon">:</div>
    <div class="value"><?= $d['jenis_penugasan'] ?></div>
</div>

<div class="row">
    <div class="label">Periode Penugasan</div>
    <div class="colon">:</div>
    <div class="value">
        <?= date('d/m/Y',strtotime($d['periode_awal'])) ?> 
        s.d 
        <?= date('d/m/Y',strtotime($d['periode_akhir'])) ?>
    </div>
</div>

</div>

<!-- ================= DETAIL RAB ================= -->
<?php
$detail = mysqli_query($conn, "
SELECT * FROM rab_detail 
WHERE rab_id='$id'
ORDER BY FIELD(kategori,
    'Akomodasi',
    'Transportasi',
    'Konsumsi',
    'Lain-lain'
)
");

$current_kategori = "";
$subtotal = 0;
$no = 1;

while($row = mysqli_fetch_assoc($detail)){

    if($current_kategori != $row['kategori']){

        if($current_kategori != ""){
            echo "<tr class='subtotal'>
                    <td colspan='5'>Subtotal</td>
                    <td>Rp ".number_format($subtotal,0,',','.')."</td>
                  </tr>";
            echo "</table></div>";
        }

        $current_kategori = $row['kategori'];
        $subtotal = 0;

        echo "<div class='section'>";
        echo "<h4>$current_kategori</h4>";

        echo "<table>
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
    echo "<tr class='subtotal'>
            <td colspan='5'>Subtotal</td>
            <td>Rp ".number_format($subtotal,0,',','.')."</td>
          </tr>";
    echo "</table></div>";
}
?>

<!-- ================= TOTAL ================= -->
<div class="total-box">
Total Dibayar : Rp <?= number_format($d['total_dibayar'],0,',','.') ?>
</div>

<hr>

<h4>Informasi Penerimaan Cash Advance</h4>

<div class="section form-view">

<div class="row">
    <div class="label">Nama Penerima</div>
    <div class="colon">:</div>
    <div class="value">
        <?= $d['penerima'] ?>
    </div>
</div>

<div class="row">
    <div class="label">Bank</div>
    <div class="colon">:</div>
    <div class="value">
        <?= $d['bank'] ?>
    </div>
</div>

<div class="row">
    <div class="label">No Rekening</div>
    <div class="colon">:</div>
    <div class="value">
        <?= $d['rekening'] ?>
    </div>
</div>

</div>

<hr>

<br>

<p>*Formulir ini dihasilkan otomatis oleh sistem sebagai bukti penerimaan dana yang sah</p>

<br><br>

<div style="
    width:300px;
    margin-left:auto;
    margin-top:60px;
    text-align:center;
">

    <div>Dibuat oleh,</div>

    <br>

    <div><b>Finance</b></div>

    <br><br>

    <div style="
        color:green;
        font-weight:bold;
    ">
        [ APPROVED DIGITAL ]
    </div>

    <br>

    <div>
        <b>
            <?= $d['approved_by'] ?? '-' ?>
        </b>
    </div>

    <div>
        <?= !empty($d['approved_at']) 
            ? date('d/m/Y H:i', strtotime($d['approved_at']))
            : '-'
        ?>
    </div>

</div>

</div>

</body>
</html>