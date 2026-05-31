<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$edit_mode = false;
$lpj_id = null;
$detail_group = [];

if(isset($_GET['edit'])){
    $edit_mode = true;
    $lpj_id = $_GET['edit'];

    $q_lpj = mysqli_query($conn, "SELECT * FROM lpj WHERE id='$lpj_id'");
    $lpj = mysqli_fetch_assoc($q_lpj);

    if(!$lpj){
        die("LPJ tidak ditemukan");
    }

    $id = $lpj['rab_id'];

    $q_detail = mysqli_query($conn, "SELECT * FROM lpj_detail WHERE lpj_id='$lpj_id'");
    while($d = mysqli_fetch_assoc($q_detail)){
        $detail_group[$d['kategori']][] = $d;
    }
} else {

    if (!isset($_GET['id'])) {
        die("ID RAB tidak ditemukan!");
    }

    $id = $_GET['id'];
}

$r = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM rab WHERE id='$id'"));
$as = @mysqli_query($conn, "SELECT * FROM rab_assistant WHERE rab_id='$id'");

$q_rab_detail = mysqli_query($conn, "SELECT * FROM rab_detail WHERE rab_id='$id'");
$rincian = [];
while($d = mysqli_fetch_assoc($q_rab_detail)){
    $rincian[$d['kategori']][] = $d;
}

function tgl($d){
    return $d ? date('d/m/Y', strtotime($d)) : '-';
}

if(!$edit_mode){

    function romawi($bln){
        return ['I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'][$bln-1];
    }

    $bulan_romawi = romawi(date('n'));
    $tahun = date('Y');

    $q = mysqli_query($conn, "
    SELECT no_lpj
    FROM lpj
    WHERE no_lpj LIKE '%/$bulan_romawi/$tahun'
    ORDER BY id DESC
    LIMIT 1
    ");

    $d = mysqli_fetch_assoc($q);

    if($d){

        $pecah = explode('/', $d['no_lpj']);

        $urut = (int)$pecah[2] + 1;

    }else{

        $urut = 1;
    }

    $urut = str_pad($urut,3,'0',STR_PAD_LEFT);

    $no_lpj = "LPJ/KAP-PQR/$urut/$bulan_romawi/$tahun";

}else{

    $no_lpj = $lpj['no_lpj'];
}
?>

<!DOCTYPE html>
<html>
<head>
<title>LPJ</title>

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

.form-view { margin-top:10px; }

.row {
    display:flex;
    margin-bottom:10px;
}

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
</style>

</head>
<body>

<div class="container">

<!-- 🔥 TAMBAHAN enctype -->
<form method="POST" action="<?= $edit_mode ? 'lpj_update.php' : 'lpj_simpan.php' ?>" enctype="multipart/form-data">

<input type="hidden" name="rab_id" value="<?= $id ?>">
<input type="hidden" name="no_lpj" value="<?= $no_lpj ?>">

<h2>LAPORAN PERTANGGUNGJAWABAN</h2>
<div class="nomor">Nomor : <?= $no_lpj ?></div>

<h3>Informasi Penugasan</h3>

<div class="form-view">
<div class="row"><div class="label">Nama Klien</div><div class="colon">:</div><div class="value"><?= $r['nama_klien'] ?></div></div>
<div class="row"><div class="label">Jenis Penugasan</div><div class="colon">:</div><div class="value"><?= $r['jenis_penugasan'] ?></div></div>
<div class="row"><div class="label">Tahun Buku</div><div class="colon">:</div><div class="value"><?= tgl($r['tahun_buku']) ?></div></div>
<div class="row"><div class="label">Periode Penugasan</div><div class="colon">:</div><div class="value"><?= tgl($r['periode_awal']) ?> s.d <?= tgl($r['periode_akhir']) ?></div></div>
</div>

<h3>Susunan Tim Audit</h3>

<div class="form-view">

<?php
$tim = [
"Signing Partner"=>"signing_partner",
"Partner Review"=>"partner_review",
"Manager In-Charge"=>"manager_ic",
"Auditor In-Charge"=>"auditor_ic"
];

foreach($tim as $label=>$field){
?>
<div class="row">
<div class="label"><?= $label ?></div>
<div class="colon">:</div>
<div class="value"><?= $r[$field] ?: '-' ?></div>
</div>
<?php } ?>

<div class="row">
<div class="label">Assistant</div>
<div class="colon">:</div>
<div class="value">
<?php
$i=1;$out="";
while($a=mysqli_fetch_assoc($as)){
    if(trim($a['nama'])){
        $out.=$i.". ".$a['nama']."<br>";
        $i++;
    }
}
echo $out ?: '-';
?>
</div>
</div>

</div>

<div class="section">I. PEMASUKAN</div>

<table>
<tr>
<th>No</th><th>Deskripsi</th><th>Referensi</th><th>Total</th>
</tr>
<tr>
<td>1</td>
<td>Cash Advance</td>
<td><?= $r['no_rab'] ?></td>
<td>Rp <?= number_format($r['total_anggaran']) ?></td>

</tr>
</table>

<h4>Rincian Pemasukan</h4>
<table>
<tr><th>Kategori Biaya</th><th>Subtotal</th></tr>

<?php
$total_rab=0;
foreach($rincian as $k=>$rows){
$sub=0;
foreach($rows as $row){$sub+=$row['total'];}
$total_rab+=$sub;
?>
<tr>
<td><?= $k ?></td>
<td>Rp <?= number_format($sub) ?></td>
</tr>
<?php } ?>
</table>

<div class="section">II. PENGELUARAN</div>

<?php
$kategori=['Akomodasi','Transportasi','Konsumsi','Lain-lain'];
foreach($kategori as $k){
?>

<h4><?= $k ?></h4>

<table id="<?= str_replace([' ','-'],'_', $k) ?>">
<tr>
<th>No</th><th>Tanggal</th><th>Deskripsi</th><th>Nominal</th><th>Bukti</th><th></th>
</tr>
</table>

<button type="button" onclick="tambahBaris('<?= $k ?>')">+ Tambah</button>
<p>Subtotal: Rp <span id="subtotal_<?= $k ?>">0</span></p>

<?php } ?>

<table>
<tr><td>Total Anggaran</td><td>Rp <?= number_format($total_rab) ?></td></tr>
<tr><td>Total Realisasi</td><td>Rp <span id="total_realisasi">0</span></td></tr>
<tr><td>Surplus (Defisit)</td><td>Rp <span id="selisih">0</span></td></tr>
</table>

<button type="submit">Submit LPJ</button>

</form>

</div>

<script>
function formatRupiah(el){
let val=el.value.replace(/[^0-9]/g,'');
el.value="Rp "+val.replace(/\B(?=(\d{3})+(?!\d))/g,".");
}

function tambahBaris(k){
let id = k.replace(/[\s-]/g,"_");
let t=document.getElementById(id);

let r=t.insertRow();

r.innerHTML=`
<td>${t.rows.length-1}</td>
<td><input type="date" name="${k}_tanggal[]"></td>
<td><input name="${k}_deskripsi[]"></td>
<td><input name="${k}_nominal[]" oninput="formatRupiah(this);hitung()"></td>

<!-- 🔥 UBAH JADI FILE -->
<td><input type="file" name="bukti[${k}][]" accept="image/*"></td>

<td><button type="button" onclick="this.closest('tr').remove();hitung()">-</button></td>
`;
}

function hitung(){
let kategori=["Akomodasi","Transportasi","Konsumsi","Lain-lain"];
let total=0;

kategori.forEach(k=>{
let id = k.replace(/[\s-]/g,"_");
let t=document.getElementById(id);

let sub=0;

for(let i=1;i<t.rows.length;i++){
let val=t.rows[i].cells[3].querySelector("input").value;
val=parseInt(val.replace(/[^0-9]/g,''))||0;
sub+=val;
}

document.getElementById("subtotal_"+k).innerText=sub.toLocaleString();
total+=sub;
});

document.getElementById("total_realisasi").innerText=total.toLocaleString();

let anggaran=<?= $total_rab ?>;
let selisih=anggaran-total;

document.getElementById("selisih").innerText=selisih.toLocaleString();
}
</script>

</body>
</html>