<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID LPJ tidak ditemukan!");
}

$lpj_id = $_GET['id'];

$q = mysqli_query($conn, "
    SELECT lpj.*, rab.*
    FROM lpj
    JOIN rab ON lpj.rab_id = rab.id
    WHERE lpj.id='$lpj_id'
");

$data = mysqli_fetch_assoc($q);

/* ================= DETAIL LPJ ================= */
$detail = mysqli_query($conn, "
    SELECT * FROM lpj_detail WHERE lpj_id='$lpj_id'
");

$group = [];
while($d = mysqli_fetch_assoc($detail)){
    $group[$d['kategori']][] = $d;
}

/* ================= RINCIAN RAB ================= */
$q_rab_detail = mysqli_query($conn, "
    SELECT * FROM rab_detail WHERE rab_id='".$data['rab_id']."'
");

$rincian = [];
while($d = mysqli_fetch_assoc($q_rab_detail)){
    $rincian[$d['kategori']][] = $d;
}

/* ================= ASSISTANT ================= */
$as = @mysqli_query($conn, "SELECT * FROM rab_assistant WHERE rab_id='".$data['rab_id']."'");

function tgl($d){
    return $d ? date('d/m/Y', strtotime($d)) : '-';
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Edit LPJ</title>

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

table { width:100%; border-collapse:collapse; margin-top:10px; }
th, td { border:1px solid #ccc; padding:8px; text-align:center; }
th { background:#ffc107; }

.section {
    margin-top:25px;
    font-weight:bold;
    background:#ffc107;
    padding:6px;
}

input { width:100%; padding:5px; }

.preview {
    width:60px;
    display:block;
    margin-bottom:5px;
}
</style>

</head>
<body>

<div class="container">

<form method="POST" action="lpj_update.php" enctype="multipart/form-data">

<input type="hidden" name="lpj_id" value="<?= $lpj_id ?>">

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
    mysqli_data_seek($as,0);
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
$total_rab=0;
foreach($rincian as $k=>$rows){
$sub=0;
foreach($rows as $row){$sub+=$row['total'];}
$total_rab+=$sub;
?>
<tr>
<td><?= $k ?></td>
<td>Rp <?= number_format($sub,0,',','.') ?></td>
</tr>
<?php } ?>
</table>

<!-- PENGELUARAN -->
<div class="section">II. PENGELUARAN</div>

<?php
$kategori=['Akomodasi','Transportasi','Konsumsi','Lain-lain'];

foreach($kategori as $k){
?>

<h4><?= $k ?></h4>

<table id="<?= $k ?>">
<tr>
<th>No</th><th>Tanggal</th><th>Deskripsi</th><th>Nominal</th><th>Bukti</th><th>Aksi</th>
</tr>

<?php
$rows = $group[$k] ?? [];

foreach($rows as $i => $row){
?>
<tr>
<td><?= $i+1 ?></td>
<td><input type="date" name="tanggal[<?= $k ?>][]" value="<?= $row['tanggal'] ?>"></td>
<td><input name="deskripsi[<?= $k ?>][]" value="<?= $row['deskripsi'] ?>"></td>
<td><input name="nominal[<?= $k ?>][]" value="Rp <?= number_format($row['nominal'],0,',','.') ?>" oninput="formatRupiah(this);hitung()"></td>

<td>

<?php if(!empty($row['bukti']) && file_exists("../uploads/".$row['bukti'])){ ?>
    <img src="../uploads/<?= $row['bukti'] ?>" class="preview">
<?php } ?>

<input type="hidden" name="bukti_lama[<?= $k ?>][]" value="<?= $row['bukti'] ?>">

<input type="file" name="bukti[<?= $k ?>][]" accept="image/*">

</td>

<td><button type="button" onclick="hapusBaris(this)">-</button></td>
</tr>
<?php } ?>

</table>

<button type="button" onclick="tambahBaris('<?= $k ?>')">+ Tambah</button>
<p>Subtotal: Rp <span id="subtotal_<?= $k ?>">0</span></p>

<?php } ?>

<!-- TOTAL -->
<table>
<tr><td><b>Total Anggaran</b></td><td>Rp <?= number_format($data['total_anggaran'],0,',','.') ?></td></tr>
<tr><td><b>Total Realisasi</b></td><td>Rp <span id="total_realisasi">0</span></td></tr>
<tr><td><b>Surplus (Defisit)</b></td><td><span id="selisih">Rp 0</span></td></tr>
</table>

<br>
<button type="submit">Update LPJ</button>

</form>

</div>

<script>
function tambahBaris(k){
    let table = document.getElementById(k);
    let row = table.insertRow();
    let no = table.rows.length - 1;

    row.innerHTML = `
    <td>${no}</td>
    <td><input type="date" name="tanggal[${k}][]"></td>
    <td><input name="deskripsi[${k}][]"></td>
    <td><input name="nominal[${k}][]" oninput="formatRupiah(this);hitung()"></td>
    <td><input type="file" name="bukti[${k}][]" accept="image/*"></td>
    <td><button type="button" onclick="hapusBaris(this)">-</button></td>
    `;
}

function hapusBaris(btn){
    btn.closest("tr").remove();
    hitung();
}

function formatRupiah(el){
    let angka = el.value.replace(/[^0-9]/g,'');
    el.value = "Rp " + angka.replace(/\B(?=(\d{3})+(?!\d))/g,".");
}

function hitung(){
    let kategori = ['Akomodasi','Transportasi','Konsumsi','Lain-lain'];
    let total = 0;

    kategori.forEach(k => {
        let table = document.getElementById(k);
        let subtotal = 0;

        for(let i=1;i<table.rows.length;i++){
            let val = table.rows[i].cells[3].querySelector("input").value.replace(/[^0-9]/g,'') || 0;
            subtotal += parseInt(val);
        }

        document.getElementById("subtotal_"+k).innerText = subtotal.toLocaleString();
        total += subtotal;
    });

    document.getElementById("total_realisasi").innerText = total.toLocaleString();

    let anggaran = <?= (int)$data['total_anggaran'] ?>;
    let selisih = anggaran - total;

    document.getElementById("selisih").innerText = selisih.toLocaleString();
}

window.onload = hitung;
</script>

</body>
</html>