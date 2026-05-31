<?php
session_start();
include "../config/db.php";

if (
    !isset($_SESSION['role']) ||
    (
        $_SESSION['role'] != 'finance' &&
        $_SESSION['role'] != 'partner'
    )
) {
    header("Location: ../auth/login.php");
    exit;
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID LPJ tidak ditemukan!");
}

$id = $_GET['id'];

$query = mysqli_query($conn, "
SELECT 
    lpj.*,

    lpj.submitted_by AS lpj_submitted_by,
    lpj.submitted_at AS lpj_submitted_at,

    lpj.approved_by AS lpj_approved_by,
    lpj.approved_at AS lpj_approved_at,

    rab.no_rab,
    rab.nama_klien,
    rab.jenis_penugasan,
    rab.tahun_buku,
    rab.periode_awal,
    rab.periode_akhir,
    rab.total_anggaran,

    rab.signing_partner,
    rab.partner_review,
    rab.manager_ic,
    rab.auditor_ic

FROM lpj
JOIN rab ON lpj.rab_id = rab.id

WHERE lpj.id='$id'
");

$data = mysqli_fetch_assoc($query);

if(!$data){
    die("Data tidak ditemukan!");
}

/* ================= ASSISTANT ================= */
$as = @mysqli_query($conn, "SELECT * FROM rab_assistant WHERE rab_id='".$data['rab_id']."'");

/* ================= DETAIL LPJ ================= */
$detail = mysqli_query($conn, "
    SELECT * FROM lpj_detail 
    WHERE lpj_id='$id'
    ORDER BY kategori ASC
");

$group = [];
while($d = mysqli_fetch_assoc($detail)){
    $group[$d['kategori']][] = $d;
}

/* ================= RINCIAN RAB ================= */
$q_rab = mysqli_query($conn, "
    SELECT * FROM rab_detail 
    WHERE rab_id='".$data['rab_id']."'
");

$rincian = [];
while($r = mysqli_fetch_assoc($q_rab)){
    $rincian[$r['kategori']][] = $r;
}

function tgl($d){
    return $d ? date('d/m/Y', strtotime($d)) : '-';
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Detail LPJ (Finance)</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
body { font-family: Arial; background:#f4f4f4; padding:20px; }
.container { max-width:1000px; margin:auto; background:#fff; padding:30px; border-radius:10px; }

h2 { text-align:center; }
.nomor { text-align:center; margin-bottom:30px; }

.row { display:flex; margin-bottom:10px; }
.label { width:220px; }
.colon { width:10px; }
.value { flex:1; }

table { width:100%; border-collapse:collapse; margin-top:10px; margin-bottom:20px; }
th { background:#ffc107; }
th, td { border:1px solid #ccc; padding:8px; text-align:center; }

.section {
    margin-top:25px;
    font-weight:bold;
    background:#ffc107;
    padding:6px;
}

/* ===== MODAL (SAMA ADMIN) ===== */
.modal {
    display:none;
    position:fixed;
    z-index:999;
    left:0; top:0;
    width:100%; height:100%;
    background:rgba(0,0,0,0.7);
    justify-content:center;
    align-items:center;
}

.modal img {
    max-width:80%;
    max-height:80%;
    border-radius:10px;
}

.modal span {
    position:absolute;
    top:20px;
    right:30px;
    color:#fff;
    font-size:30px;
    cursor:pointer;
}

/* =========================
   MODAL PREVIEW
========================= */

.modal-preview{
    display:none;
    position:fixed;
    z-index:9999;
    left:0;
    top:0;
    width:100%;
    height:100%;
    background:rgba(0,0,0,0.75);

    justify-content:center;
    align-items:center;
}

/* ========================= */

.modal-preview img{
    max-width:85%;
    max-height:90%;
    border-radius:10px;
    box-shadow:0 4px 20px rgba(0,0,0,0.3);
    background:white;
}

/* ========================= */

.close-preview{
    position:absolute;
    top:20px;
    right:35px;
    color:white;
    font-size:40px;
    font-weight:bold;
    cursor:pointer;
    transition:0.2s;
}

.close-preview:hover{
    color:#ffc107;
}

/* =========================
   BUTTON AREA
========================= */

.btn-area{
    position:fixed;
    top:20px;
    right:25px;
    z-index:9999;

    display:flex;
    flex-direction:column;
    gap:10px;
}

/* ========================= */

.btn-export{
    background:#198754;
    padding:12px 18px;
    text-decoration:none;
    color:white;
    border-radius:12px;
    font-weight:bold;
    border:none;
    cursor:pointer;
    box-shadow:0 4px 12px rgba(0,0,0,0.2);
    transition:0.2s;
}

.btn-export:hover{
    transform:scale(1.05);
}

/* ========================= */

.btn-lampiran{
    background:#ffc107;
    padding:12px 18px;
    text-decoration:none;
    color:black;
    border-radius:12px;
    font-weight:bold;
    box-shadow:0 4px 12px rgba(0,0,0,0.2);
    display:inline-block;
    transition:0.2s;
}

.btn-lampiran:hover{
    transform:scale(1.05);
}

/* =========================
   PRINT AREA
========================= */

.kop-header{
    display:none;
}

.kop-footer{
    display:none;
}


.ttd-area{
    display:flex;
    justify-content:space-between;
    margin-top:80px;
}

.ttd-box{
    width:45%;
    text-align:center;
}

.ttd-status{
    color:green;
    font-weight:bold;
    margin:30px 0 10px;
}

/* =========================
   PRINT STYLE
========================= */

@page{
    size:A4;
    margin:0;
}

@media print{

    body{
        background:white;
        padding:0;
    }

    .btn-area{
        display:none !important;
    }

    .modal-preview{
        display:none !important;
    }

    .container{
    box-shadow:none;
    border-radius:0;

    padding:
        110px   /* atas */
        40px    /* kanan */
        150px   /* bawah */
        40px;   /* kiri */

    max-width:100%;
}

.kop-header{
    display:block;

    position:fixed;
    top:15px;
    left:0;
    width:100%;

    text-align:center;
}

.kop-header img{
    width:25%;
    height:auto;
}

    .kop-footer{
        display:block;
        position:fixed;
        bottom:0;
        left:0;
        width:100%;
    }

    .kop-footer img{
        width:100%;
    }

    table{
        page-break-inside:auto;
    }

    tr{
        page-break-inside:avoid;
    }

    .section,
    th{
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    .page-break{
    page-break-before:always;
    height:140px;
}
}

</style>

</head>
<body>

<div class="btn-area">

    <!-- EXPORT LPJ -->
    <button onclick="window.print()" class="btn-export">
        🖨 Export LPJ
    </button>

    <!-- LAMPIRAN -->
    <a href="lampiran_bukti.php?id=<?= $id ?>"
       target="_blank"
       class="btn-lampiran">
       📎 Lampiran Bukti
    </a>

</div>

<div class="container">

<!-- HEADER PRINT -->
<div class="kop-header">
    <img src="../assets/img/header-kop.jpg">
</div>

<h2>LAPORAN PERTANGGUNGJAWABAN</h2>
<div class="nomor">Nomor : <?= $data['no_lpj'] ?></div>

<!-- INFORMASI -->
<h3>Informasi Penugasan</h3>
<div class="row"><div class="label">Nama Klien</div><div class="colon">:</div><div class="value"><?= $data['nama_klien'] ?></div></div>
<div class="row"><div class="label">Jenis Penugasan</div><div class="colon">:</div><div class="value"><?= $data['jenis_penugasan'] ?></div></div>
<div class="row"><div class="label">Tahun Buku</div><div class="colon">:</div><div class="value"><?= tgl($data['tahun_buku']) ?></div></div>
<div class="row"><div class="label">Periode</div><div class="colon">:</div><div class="value"><?= tgl($data['periode_awal']) ?> s.d <?= tgl($data['periode_akhir']) ?></div></div>

<!-- TIM -->
<h3>Susunan Tim Audit</h3>
<div class="row"><div class="label">Signing Partner</div><div class="colon">:</div><div class="value"><?= $data['signing_partner'] ?: '-' ?></div></div>
<div class="row"><div class="label">Partner Review</div><div class="colon">:</div><div class="value"><?= $data['partner_review'] ?: '-' ?></div></div>
<div class="row"><div class="label">Manager In-Charge</div><div class="colon">:</div><div class="value"><?= $data['manager_ic'] ?: '-' ?></div></div>
<div class="row"><div class="label">Auditor In-Charge</div><div class="colon">:</div><div class="value"><?= $data['auditor_ic'] ?: '-' ?></div></div>

<div class="row">
<div class="label">Assistant</div>
<div class="colon">:</div>
<div class="value">
<?php
$i=1;$out="";
if($as){
    while($a=mysqli_fetch_assoc($as)){
        if(trim($a['nama'])){
            $out .= $i.". ".$a['nama']."<br>";
            $i++;
        }
    }
}
echo $out ?: '-';
?>
</div>
</div>

<!-- PEMASUKAN -->
<div class="section">I. PEMASUKAN</div>

<table>
<tr>
<th>No</th><th>Deskripsi</th><th>Referensi</th><th>Total</th>
</tr>

<tr>
<td>1</td>
<td>Cash Advance</td>
<td><?= $data['no_rab'] ?></td>
<td>Rp <?= number_format($data['total_anggaran'],0,',','.') ?></td>

</tr>
</table>

<!-- RINCIAN -->
<h4>Rincian Pemasukan</h4>
<table>
<tr><th>Kategori</th><th>Subtotal</th></tr>

<?php
$total_rab = 0;
foreach($rincian as $k => $rows){
    $sub = 0;
    foreach($rows as $row){ $sub += $row['total']; }
    $total_rab += $sub;
?>
<tr>
<td style="text-align:left"><?= $k ?></td>
<td>Rp <?= number_format($sub,0,',','.') ?></td>
</tr>
<?php } ?>
</table>

<div class="page-break"></div>

<!-- PENGELUARAN -->
<div class="section">II. PENGELUARAN</div>

<?php
$urutan = ['Akomodasi','Transportasi','Konsumsi','Lain-lain'];

foreach($urutan as $kategori){
if(empty($group[$kategori])) continue;

$rows = $group[$kategori];
$subtotal = 0;
?>

<h4><?= $kategori ?></h4>

<table>
<tr>
<th>No</th><th>Tanggal</th><th>Deskripsi</th><th>Nominal</th><th>Bukti</th>
</tr>

<?php
$no=1;
foreach($rows as $r){
$subtotal += $r['nominal'];
?>
<tr>
<td><?= $no++ ?></td>
<td><?= tgl($r['tanggal']) ?></td>
<td><?= $r['deskripsi'] ?></td>
<td>Rp <?= number_format($r['nominal'],0,',','.') ?></td>

<td>
<?php 
$bukti = $r['bukti'] ?? null;
$path = "../uploads/".$bukti;

if(!empty($bukti) && file_exists($path)){
?>
<?php if($r['bukti']):

    $path = "../uploads/".$r['bukti'];

?>

<a href="javascript:void(0)"
onclick="showPreview('<?= $path ?>')"
style="
    text-decoration:none;
    color:#007bff;
    font-weight:bold;
">
    <?= $r['bukti_ref'] ?: '-' ?>
</a>

<?php else: ?>

-

<?php endif; ?>
<?php } else { echo "-"; } ?>
</td>

</tr>
<?php } ?>

<tr>
<td colspan="3"><b>Subtotal</b></td>
<td colspan="2"><b>Rp <?= number_format($subtotal,0,',','.') ?></b></td>
</tr>

</table>

<?php } ?>

<!-- TOTAL -->
<table>
<tr><td><b>Total Anggaran</b></td><td>Rp <?= number_format($data['total_anggaran'],0,',','.') ?></td></tr>
<tr><td><b>Total Realisasi</b></td><td>Rp <?= number_format($data['total_realisasi'],0,',','.') ?></td></tr>

<tr>
<td><b>Surplus (Defisit)</b></td>
<td>
<?php
$selisih = $data['total_anggaran'] - $data['total_realisasi'];
echo $selisih >= 0 
? "<span style='color:green'>Rp ".number_format($selisih,0,',','.')."</span>"
: "<span style='color:red'>(Rp ".number_format(abs($selisih),0,',','.').")</span>";
?>
</td>
</tr>
</table>

<div class="ttd-area">

    <!-- ADMIN -->
    <div class="ttd-box">

        Diajukan oleh,<br><br>

        Admin<br><br><br>

        <div class="ttd-status">
            [ APPROVED DIGITAL ]
        </div>

        <b>
            <?= $data['submitted_by'] ?: '-' ?>
        </b>
        <br>

        <?= $data['submitted_at']
            ? date('d/m/Y H:i', strtotime($data['submitted_at']))
            : '-' ?>

    </div>

    <!-- FINANCE -->
    <div class="ttd-box">

        Disetujui oleh,<br><br>

        Finance<br><br><br>

        <?php if($data['approved_by']){ ?>

            <div class="ttd-status">
                [ APPROVED DIGITAL ]
            </div>

            <b><?= $data['approved_by'] ?></b>
            <br>

            <?= date('d/m/Y H:i', strtotime($data['approved_at'])) ?>

        <?php } else { ?>

            <div class="ttd-status">
                [ BELUM DISETUJUI ]
            </div>

            <b>—</b><br>-

        <?php } ?>

    </div>

</div>

</div>

<!-- MODAL PREVIEW -->
<div id="previewModal" class="modal-preview">

    <span class="close-preview" onclick="closePreview()">
        &times;
    </span>

    <img id="previewImg">

</div>

<script>

function showPreview(src){

    document.getElementById("previewModal").style.display = "flex";
    document.getElementById("previewImg").src = src;

}

function closePreview(){

    document.getElementById("previewModal").style.display = "none";

}

/* klik luar gambar = close */
document.getElementById("previewModal").onclick = function(e){

    if(e.target.id == "previewModal"){
        closePreview();
    }

}

</script>

<!-- FOOTER PRINT -->
<div class="kop-footer">
    <img src="../assets/img/footer-kop.jpeg">
</div>

</body>
</html>