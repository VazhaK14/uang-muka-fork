<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'finance') {
    header("Location: ../auth/login.php");
    exit;
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID tidak ditemukan");
}

$lpj_id = $_GET['id'];

/* ================= DATA ================= */
$q = mysqli_query($conn, "
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
WHERE lpj.id='$lpj_id'
");

$data = mysqli_fetch_assoc($q);

if(!$data){
    die("Data tidak ditemukan!");
}

/* ================= ASSISTANT ================= */
$as = @mysqli_query($conn, "
    SELECT * FROM rab_assistant 
    WHERE rab_id='".$data['rab_id']."'
");

/* ================= DETAIL LPJ ================= */
$detail = mysqli_query($conn, "
    SELECT * FROM lpj_detail 
    WHERE lpj_id='$lpj_id'
");

$group = [];
$missing_bukti = false;

while($d = mysqli_fetch_assoc($detail)){
    if(empty($d['bukti'])){
        $missing_bukti = true;
    }
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
<title>Review LPJ</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
body { font-family: Arial; background:#f4f4f4; padding:20px; }

.container {
    max-width:1000px;
    margin:auto;
    background:#fff;
    padding:30px;
    border-radius:10px;
}

h2 { text-align:center; }
.nomor { text-align:center; margin-bottom:30px; }

.row { display:flex; margin-bottom:10px; }
.label { width:220px; }
.colon { width:10px; }
.value { flex:1; }

table { width:100%; border-collapse:collapse; margin-top:10px; margin-bottom:20px; }
th, td { border:1px solid #ccc; padding:8px; text-align:center; }
th { background:#ffc107; }

.section {
    margin-top:25px;
    font-weight:bold;
    background:#ffc107;
    padding:6px;
}

.btn {
    padding:10px 20px;
    border:none;
    cursor:pointer;
    margin:10px;
    border-radius:6px;
}

.approve { background:green; color:white; }
.reject { background:red; color:white; }

.modal {
    display:none;
    position:fixed;
    top:0; left:0;
    width:100%; height:100%;
    background:rgba(0,0,0,0.5);
    justify-content:center;
    align-items:center;
    z-index:999;
}

.modal-content {
    background:#fff;
    padding:20px;
    border-radius:12px;
    width:500px;
    max-width:90%;
    text-align:center;
    position:relative;
    box-shadow:0 5px 20px rgba(0,0,0,0.3);
}

.modal-content img {
    max-width:100%;
    max-height:600px;
    border-radius:8px;
}

.close-btn {
    position:absolute;
    top:10px;
    right:15px;
    font-size:20px;
    cursor:pointer;
    color:#333;
}

.ttd-area{
    display:flex;
    justify-content:space-between;
    margin-top:70px;
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

</style>

</head>
<body>

<div class="container">

<h2>LAPORAN PERTANGGUNGJAWABAN</h2>
<div class="nomor">Nomor : <?= $data['no_lpj'] ?></div>

<!-- ================= INFORMASI ================= -->
<h3>Informasi Penugasan</h3>

<div class="row"><div class="label">Nama Klien</div><div class="colon">:</div><div class="value"><?= $data['nama_klien'] ?></div></div>
<div class="row"><div class="label">Jenis Penugasan</div><div class="colon">:</div><div class="value"><?= $data['jenis_penugasan'] ?></div></div>
<div class="row"><div class="label">Tahun Buku</div><div class="colon">:</div><div class="value"><?= tgl($data['tahun_buku']) ?></div></div>
<div class="row"><div class="label">Periode</div><div class="colon">:</div><div class="value"><?= tgl($data['periode_awal']) ?> s.d <?= tgl($data['periode_akhir']) ?></div></div>

<!-- ================= TIM ================= -->
<h3>Susunan Tim Audit</h3>

<div class="row"><div class="label">Signing Partner</div><div class="colon">:</div><div class="value"><?= $data['signing_partner'] ?: '-' ?></div></div>
<div class="row"><div class="label">Partner Review</div><div class="colon">:</div><div class="value"><?= $data['partner_review'] ?: '-' ?></div></div>
<div class="row"><div class="label">Manager IC</div><div class="colon">:</div><div class="value"><?= $data['manager_ic'] ?: '-' ?></div></div>
<div class="row"><div class="label">Auditor IC</div><div class="colon">:</div><div class="value"><?= $data['auditor_ic'] ?: '-' ?></div></div>

<div class="row">
<div class="label">Assistant</div>
<div class="colon">:</div>
<div class="value">
<?php
$i=1;$out="";
if($as){
    while($a=mysqli_fetch_assoc($as)){
        if(trim($a['nama'])){
            $out.=$i.". ".$a['nama']."<br>";
            $i++;
        }
    }
}
echo $out ?: '-';
?>
</div>
</div>

<!-- ================= PEMASUKAN ================= -->
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

<h4>Rincian Pemasukan</h4>
<table>
<tr><th>Kategori</th><th>Subtotal</th></tr>

<?php foreach($rincian as $k => $rows){
    $sub = 0;
    foreach($rows as $rr){ $sub += $rr['total']; }
?>
<tr>
<td style="text-align:left;"><?= $k ?></td>
<td>Rp <?= number_format($sub,0,',','.') ?></td>
</tr>
<?php } ?>
</table>

<!-- ================= PENGELUARAN ================= -->
<div class="section">II. PENGELUARAN</div>

<?php foreach($group as $kategori => $rows){ 
$subtotal = 0;
?>

<h4><?= $kategori ?></h4>

<table>
<tr>
<th>No</th><th>Tanggal</th><th>Deskripsi</th><th>Nominal</th><th>Bukti</th>
</tr>

<?php $no=1; foreach($rows as $r){ $subtotal += $r['nominal']; ?>

<tr>
<td><?= $no++ ?></td>
<td><?= tgl($r['tanggal']) ?></td>
<td><?= $r['deskripsi'] ?></td>
<td>Rp <?= number_format($r['nominal'],0,',','.') ?></td>

<td>
<?php if(!empty($r['bukti']) && file_exists("../uploads/".$r['bukti'])){ ?>
<i class="fa-solid fa-file-lines" onclick="showPreview('../uploads/<?= $r['bukti'] ?>')"></i>
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

<!-- ================= TOTAL ================= -->
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

        <?php if(!empty($data['lpj_submitted_by'])){ ?>

            <div class="ttd-status">
                [ APPROVED DIGITAL ]
            </div>

            <b>
                <?= $data['lpj_submitted_by'] ?>
            </b>
            <br>

            <?= date('d/m/Y H:i', strtotime($data['lpj_submitted_at'])) ?>

        <?php } else { ?>

            <div class="ttd-status">
                [ BELUM DISUBMIT ]
            </div>

            <b>—</b><br>-

        <?php } ?>

    </div>

    <!-- FINANCE -->
    <div class="ttd-box">

        Disetujui oleh,<br><br>

        Finance<br><br><br>

        <?php if(!empty($data['lpj_approved_by'])){ ?>

            <div class="ttd-status">
                [ APPROVED DIGITAL ]
            </div>

            <b><?= $data['lpj_approved_by'] ?></b>
            <br>

            <?= date('d/m/Y H:i', strtotime($data['lpj_approved_at'])) ?>

        <?php } else { ?>

            <div class="ttd-status">
                [ BELUM DISETUJUI ]
            </div>

            <b>—</b><br>-

        <?php } ?>

    </div>

</div>

<!-- ================= BUTTON ================= -->
<div style="text-align:center;">
<a href="lpj_approve.php?id=<?= $lpj_id ?>">
<button class="btn approve">Approve</button>
</a>

<button onclick="showReject()" class="btn reject">Reject</button>

<div id="rejectBox" style="display:none;">
<form method="POST" action="lpj_reject.php">
<input type="hidden" name="id" value="<?= $lpj_id ?>">

<textarea name="rejected_note" required 
style="width:100%;height:80px;"></textarea>

<br><br>

<button type="submit" class="btn reject">Submit Reject</button>
</form>
</div>
</div>

</div>

<!-- MODAL -->
<div class="modal" id="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal()">✖</span>
        <img id="imgPreview">
    </div>
</div>

<script>
function showPreview(src){
    document.getElementById("modal").style.display="flex";
    document.getElementById("imgPreview").src=src;
}

function closeModal(){
    document.getElementById("modal").style.display="none";
}

function showReject(){
    document.getElementById("rejectBox").style.display="block";
}
</script>

</body>
</html>