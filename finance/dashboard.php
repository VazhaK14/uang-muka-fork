<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'finance') {
    header("Location: ../auth/login.php");
    exit;
}

include "../config/db.php";

// ================= KPI =================
$q1 = mysqli_query($conn, "SELECT SUM(total_realisasi) as total FROM lpj WHERE status='Approved'");
$total_pengeluaran = mysqli_fetch_assoc($q1)['total'] ?? 0;

$q2 = mysqli_query($conn, "SELECT COUNT(*) as total FROM rab WHERE pencairan='Paid'");
$total_penugasan = mysqli_fetch_assoc($q2)['total'] ?? 0;

$q3 = mysqli_query($conn, "
SELECT COUNT(*) as total
FROM rab r
LEFT JOIN lpj l ON r.id = l.rab_id
WHERE r.pencairan='Paid' AND l.id IS NULL
");
$lpj_outstanding = mysqli_fetch_assoc($q3)['total'] ?? 0;

$q4 = mysqli_query($conn, "
SELECT COUNT(*) as total 
FROM rab 
WHERE status='Approved' AND (pencairan IS NULL OR pencairan!='Paid')
");
$menunggu_pencairan = mysqli_fetch_assoc($q4)['total'] ?? 0;

$q5 = mysqli_query($conn, "SELECT COUNT(*) as total FROM lpj WHERE status='Submitted'");
$review_lpj = mysqli_fetch_assoc($q5)['total'] ?? 0;

// ================= DATA =================
$q_realisasi = mysqli_query($conn, "
SELECT ld.kategori, SUM(ld.nominal) total
FROM lpj_detail ld
JOIN lpj l ON ld.lpj_id=l.id
WHERE l.status='Approved' AND YEAR(ld.tanggal)=2026
GROUP BY ld.kategori
");

$data_realisasi=[];
while($r=mysqli_fetch_assoc($q_realisasi)){
$data_realisasi[$r['kategori']]=$r['total'];
}

$q_anggaran = mysqli_query($conn, "
SELECT rd.kategori, SUM(rd.total) total
FROM rab_detail rd
JOIN rab r ON rd.rab_id=r.id
WHERE r.pencairan='Paid'
GROUP BY rd.kategori
");

$data_anggaran=[];
while($a=mysqli_fetch_assoc($q_anggaran)){
$data_anggaran[$a['kategori']]=$a['total'];
}

// ================= LINE =================
$q_chart = mysqli_query($conn,"
SELECT MONTH(ld.tanggal) bulan, SUM(ld.nominal) total
FROM lpj_detail ld
JOIN lpj l ON ld.lpj_id=l.id
WHERE l.status='Approved' AND YEAR(ld.tanggal)=2026
GROUP BY MONTH(ld.tanggal)
");

$data_bulanan=array_fill(1,12,0);
while($c=mysqli_fetch_assoc($q_chart)){
$data_bulanan[(int)$c['bulan']]=$c['total'];
}

// ================= PIE =================
$q_pie=mysqli_query($conn,"
SELECT rab.nama_klien, SUM(lpj.total_realisasi) total
FROM lpj
JOIN rab ON lpj.rab_id=rab.id
WHERE lpj.status='Approved'
GROUP BY rab.nama_klien
ORDER BY total DESC
");

$labels_pie=[];$data_pie=[];$no=0;
while($p=mysqli_fetch_assoc($q_pie)){
$no++;
if($no<=3){
$labels_pie[]=$p['nama_klien'];
$data_pie[]=$p['total'];
}else break;
}

// ================= CASH ADVANCE OUTSTANDING =================
$q_outstanding=mysqli_query($conn,"
SELECT rab.*
FROM rab
LEFT JOIN lpj ON rab.id=lpj.rab_id
WHERE rab.pencairan='Paid' AND lpj.id IS NULL
ORDER BY rab.id DESC
");
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Dashboard Finance</title>

<style>
body { margin:0; font-family:Arial; display:flex; }

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
    display:flex;
    justify-content:flex-end;
    gap:30px;
    margin-bottom:30px;
}

/* KPI */
.card-container {
    display:grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap:20px;
    margin-bottom:30px;
}

.card {
    background:white;
    color:black;
    padding:20px;
    border-radius:15px;
    box-shadow:0 4px 12px rgba(0,0,0,0.08);
    border-left:10px solid rgb(201,155,40);
}

.card h3 {
    font-size:18px;
    color:rgb(201,155,40);
    font-weight:bold;
}

.card p {
    font-size:26px;
    font-weight:bold;
    margin:0;
}

/* SECTION */
.section {
    background:white;
    color:black;
    padding:15px;
    border-radius:5px;
    margin-bottom:20px;
}

table {
    width:100%;
    border-collapse:collapse;
    margin-top:10px;
}

th, td {
    border:1px solid #ccc;
    padding:8px;
    text-align:center;
}

th { background:#ffc107; }

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

.overdue{
    background:#dc3545;
}

</style>
</head>

<body>

<?php include "sidebar.php"; ?>

<div class="content">

<?php include "topbar.php"; ?>

<h1>Dashboard Finance</h1>
<p>Selamat datang, <b><?= $_SESSION['nama_lengkap']; ?></b></p>

<!-- KPI -->
<div class="card-container">
<div class="card"><h3>Total Pengeluaran</h3><p>Rp <?=number_format($total_pengeluaran,0,',','.')?></p></div>
<div class="card"><h3>Total Penugasan</h3><p><?=$total_penugasan?></p></div>
<div class="card"><h3>Cash Advance Outstanding</h3><p><?=$lpj_outstanding?></p></div>
<div class="card"><h3>Pencairan Dana</h3><p><?=$menunggu_pencairan?></p></div>
<div class="card"><h3>Review LPJ</h3><p><?=$review_lpj?></p></div>
</div>

<!-- TABEL -->
<div class="section">
<b>Total Pengeluaran berdasarkan Kategori</b>

<table>
<tr>
<th>No</th>
<th>Kategori</th>
<th>Total Anggaran</th>
<th>Total Realisasi</th>
<th>Surplus (Defisit)</th>
<th>%</th>
</tr>

<?php
$kategori=['Akomodasi','Transportasi','Konsumsi','Lain-lain'];
$no=1;

foreach($kategori as $k){
$a=$data_anggaran[$k]??0;
$r=$data_realisasi[$k]??0;
$s=$a-$r;
$p=$a>0?($s/$a)*100:0;
?>

<tr>
<td><?=$no++?></td>
<td><?=$k?></td>
<td>Rp <?=number_format($a,0,',','.')?></td>
<td>Rp <?=number_format($r,0,',','.')?></td>

<td>
<?= $s >= 0 
? "<span style='color:green'>Rp ".number_format($s,0,',','.')."</span>"
: "<span style='color:red'>(Rp ".number_format(abs($s),0,',','.').")</span>"
?>
</td>

<td style="color:<?= $p >= 0 ? 'green':'red' ?>">
<?= $p >= 0 
? number_format($p,2)."%" 
: "(".number_format(abs($p),2)."%)"
?>
</td>

</tr>
<?php } ?>
</table>
</div>

<!-- LINE -->
<div class="section">
<b>Grafik Total Pengeluaran per Bulan</b>
<canvas id="lineChart" height="100"></canvas>
</div>

<div style="display:flex; gap:20px; align-items:stretch;">

<div class="section" style="flex:1; display:flex; flex-direction:column;">
<b>Pengeluaran berdasarkan Penugasan</b>
<div style="width:280px; margin:auto;">
<canvas id="pieChart"></canvas>
</div>
</div>

<div class="section" style="flex:2; display:flex; flex-direction:column;">
<b>Cash Advance Outstanding</b>

<div style="flex:1; overflow-y:auto; max-height:300px;">
<table>
<tr>
<th>No</th>
<th>Nomor RAB</th>
<th>Nama Klien</th>
<th>Periode Penugasan</th>
<th>Batas LPJ</th>
<th>Status Deadline</th>
</tr>

<?php 
$no=1; 
$total=mysqli_num_rows($q_outstanding);

while($o=mysqli_fetch_assoc($q_outstanding)):
$awal = date('d/m/Y', strtotime($o['periode_awal']));
$akhir = date('d/m/Y', strtotime($o['periode_akhir']));
$batas = date('d/m/Y', strtotime($o['periode_akhir'].' +7 days'));

$today = date('Y-m-d');
$batas_asli = date('Y-m-d', strtotime($o['periode_akhir'].' +7 days'));

$selisih = (strtotime($batas_asli) - strtotime($today)) / 86400;

if($selisih < 0){
    $status_deadline = "<span class='deadline-badge overdue'>Past Due</span>";
}
elseif($selisih <= 2){
    $status_deadline = "<span class='deadline-badge warning'>Due Soon</span>";
}
else{
    $status_deadline = "<span class='deadline-badge aman'>Not Yet Due</span>";
}
?>

<tr>
<td><?=$no++?></td>
<td><?=$o['no_rab']?></td>
<td><?=$o['nama_klien']?></td>
<td><?=$awal?> - <?=$akhir?></td>
<td><?=$batas?></td>
<td><?=$status_deadline?></td>
</tr>

<?php endwhile;?>
</table>
</div>

<div style="margin-top:10px;font-size:12px;">
Showing 1-<?=$no-1?> of <?=$total?> data
</div>

</div>
</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>

<!-- LINE CHART (FULL MANAGER VERSION) -->
<script>
new Chart(document.getElementById('lineChart'), {
type:'line',
data:{
labels:['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'],
datasets:[{
label:'Total Pengeluaran',
data:<?= json_encode(array_values($data_bulanan)) ?>,
borderWidth:2,
fill:false,
tension:0.3
}]
},
options:{
layout:{padding:{top:30}},
plugins:{
legend:{display:false},
tooltip:{
callbacks:{
label:function(context){
let v=context.raw||0;
return 'Rp '+v.toLocaleString('id-ID');
}
}
},
datalabels:{
    display:true,
    anchor:'end',
    align:'top',
    color:'blue',
    font:{weight:'bold'},
    formatter:function(v){
        let angka = parseInt(v) || 0;
        return 'Rp ' + angka.toLocaleString('id-ID');
    }
}
},
scales:{
y:{
beginAtZero:true,
ticks:{
callback:function(v){
return 'Rp '+v.toLocaleString('id-ID');
}
}
}
}
},
plugins:[ChartDataLabels]
});
</script>

<!-- PIE CHART -->
<script>
new Chart(document.getElementById('pieChart'), {
    type: 'pie',
    data: {
        labels: <?= json_encode($labels_pie ?? []) ?>,
        datasets: [{
    data: <?= json_encode($data_pie ?? []) ?>,

    backgroundColor: [
        '#2ecc71',
        '#3498db',
        '#ff69b4'
    ],

    hoverOffset: 10
}]
    },
    options: {
        plugins: {
    legend: {
    position: 'bottom',
    align: 'center',
    labels: {
        boxWidth: 10,
        padding: 15,
        usePointStyle: true,
        font: {
            size: 11
        }
    }
},
    tooltip: {
        callbacks: {
            label: function(context) {
                let value = context.raw || 0;
                let total = context.dataset.data.reduce((a,b)=>a+b,0);
                let persen = total > 0 ? (value/total*100).toFixed(1) : 0;

                return context.label + " : Rp " 
                    + value.toLocaleString('id-ID') 
                    + " (" + persen + "%)";
            }
        }
    },
    datalabels: {
        formatter: function(value, context) {
            let total = context.chart._metasets[0].total || 0;
            let persen = total > 0 ? (value / total * 100).toFixed(1) : 0;
            return persen + '%';
        },
        color: '#fff',
        font: {
            weight: 'bold'
        }
    }
}
    },
    plugins: [ChartDataLabels]
});
</script>

</body>
</html>