<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'director') {
    header("Location: ../auth/login.php");
    exit;
}

$query = mysqli_query($conn, "
    SELECT lpj.id as lpj_id, lpj.*, rab.*
    FROM lpj
    JOIN rab ON lpj.rab_id = rab.id
    WHERE lpj.status = 'Approved'
    ORDER BY lpj.id DESC
");
?>

<!DOCTYPE html>
<html>
<head>
<title>LPJ Audit Director</title>

<style>
body{
    font-family:Arial;
    background:#f4f4f4;
    padding:20px;
}

/* CONTAINER */
.container {
    width:95%;
    max-width:1200px;
    margin:30px auto;
    background:#fff;
    padding:25px;
    border-radius:15px;
    box-shadow:0 5px 15px rgba(0,0,0,0.08);
}

/* TITLE */
h2{
    text-align:center;
    margin-bottom:20px;
}

/* SEARCH */
.search-box{
    margin-bottom:15px;
}

.search-box input{
    width:300px;
    padding:10px;
    border:1px solid #ccc;
    border-radius:6px;
    outline:none;
    font-size:14px;
}

.search-box input:focus{
    border-color:#ffc107;
}

/* TABLE WRAPPER */
.table-wrapper{
    max-height:650px;
    overflow-y:auto;
    border:1px solid #ddd;
}

/* TABLE */
table {
    border-collapse: collapse;
    width: 100%;
    table-layout: auto;
}

thead th{
    position:sticky;
    top:0;
    z-index:100;
    background:#ffc107;
    color:black;
}

th, td{
    border:1px solid #ccc;
    padding:10px;
    text-align:center;
}

tr:hover{
    background:#f9f9f9;
}

/* BADGE */
.badge{
    display:inline-block;
    padding:4px 10px;
    border-radius:20px;
    font-size:10px;
    font-weight:bold;
    white-space:nowrap;
}

/* STATUS DEADLINE */
.ontime{
    background:#d4edda;
    color:#155724;
}

.late{
    background:#f8d7da;
    color:#721c24;
}

/* BUTTON */
.btn-detail{
    background:#0d6efd;
    color:white;
    padding:6px 12px;
    border-radius:5px;
    text-decoration:none;
    font-size:13px;
    font-weight:bold;
    display:inline-block;
}

.btn-detail:hover{
    background:#0b5ed7;
}

/* NUMBER */
td:nth-child(6),
td:nth-child(7),
td:nth-child(8){
    text-align:right;
    padding-right:15px;
}

.table-wrapper{
    max-height:650px;
    overflow-y:auto;
    border:1px solid #ddd;
}

thead th{
    position:sticky;
    top:0;
    background:#ffc107;
    z-index:999;
}

th, td{
 border:1px solid #ccc;
 padding:10px;
 text-align:center;
}

</style>

</head>
<body>

<div class="container">

<h2>Riwayat LPJ (Audit Director)</h2>

<div class="search-box">
    <input 
        type="text" 
        id="searchInput" 
        placeholder="Cari data LPJ..."
    >
</div>

<div class="table-wrapper">

<table>

<thead>
<tr>
    <th>No</th>
    <th>Tanggal</th>
    <th>Nomor LPJ</th>
    <th>Nomor RAB</th>
    <th>Batas LPJ</th>
    <th>Total Anggaran</th>
    <th>Total Realisasi</th>
    <th>Surplus (Defisit)</th>
    <th>Status Submit</th>
    <th>Aksi</th>
</tr>
</thead>

<tbody>

<?php
$no = 1;
while($d = mysqli_fetch_assoc($query)){

    $tanggal = date('d/m/Y', strtotime($d['tanggal']));
    $batas = date('d/m/Y', strtotime($d['periode_akhir'].' +7 days'));

    $anggaran = $d['total_anggaran'];
    $realisasi = $d['total_realisasi'];
    $selisih = $anggaran - $realisasi;

    $anggaran_rp = "Rp " . number_format($anggaran,0,',','.');
    $realisasi_rp = "Rp " . number_format($realisasi,0,',','.');

    if($selisih >= 0){
        $selisih_rp = "<span style='color:green;font-weight:bold;'>Rp ".number_format($selisih,0,',','.')."</span>";
    } else {
        $selisih_rp = "<span style='color:red;font-weight:bold;'>(Rp ".number_format(abs($selisih),0,',','.').")</span>";
    }

    // STATUS DEADLINE
$tanggal_submit = strtotime($d['tanggal']);
$tanggal_batas  = strtotime($d['periode_akhir'] . ' +7 days');

if($tanggal_submit <= $tanggal_batas){

    $deadline_badge = "
    <span class='badge ontime'>
        On Time Submission
    </span>";

} else {

    $deadline_badge = "
    <span class='badge late'>
        Late Submission
    </span>";

}

?>

<tr>
    <td><?= $no++ ?></td>
    <td><?= $tanggal ?></td>
    <td><?= $d['no_lpj'] ?></td>
    <td><?= $d['no_rab'] ?></td>
    <td><?= $batas ?></td>
    <td><?= $anggaran_rp ?></td>
    <td><?= $realisasi_rp ?></td>
    <td><?= $selisih_rp ?></td>
    <td><?= $deadline_badge ?></td>
    
    <td>
        <a href="lpj_detail.php?id=<?= $d['lpj_id'] ?>" class="btn-detail">
    Detail
</a>
    </td>
</tr>

<?php } ?>

</tbody>

</table>
</div>

</div>

<script>
document.getElementById("searchInput")
.addEventListener("keyup", function(){

    let filter = this.value.toLowerCase();

    let rows = document.querySelectorAll("tbody tr");

    rows.forEach(function(row){

        let text = row.innerText.toLowerCase();

        if(text.includes(filter)){
            row.style.display = "";
        } else {
            row.style.display = "none";
        }

    });

});
</script>

</body>
</html>