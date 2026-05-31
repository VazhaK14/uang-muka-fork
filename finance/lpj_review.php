<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'finance') {
    header("Location: ../auth/login.php");
    exit;
}

$query = mysqli_query($conn, "
    SELECT lpj.*, rab.no_rab, rab.total_anggaran, rab.periode_akhir
    FROM lpj
    JOIN rab ON lpj.rab_id = rab.id
    ORDER BY lpj.id DESC
");
?>

<!DOCTYPE html>
<html>
<head>
<title>Review LPJ</title>

<style>
body {
    font-family: Arial;
    background: #f4f4f4;
    padding: 20px;
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
h2 {
    text-align: center;
    margin-bottom: 25px;
}

/* TABLE */
table {
    border-collapse: collapse;
    width: 100%;
    table-layout: auto;
}

/* SEARCH */
.search-box{
 margin-bottom:20px;
}

.search-box input{
 width:300px;
 padding:10px 15px;
 border:1px solid #ccc;
 border-radius:8px;
 font-size:14px;
 outline:none;
}

.search-box input:focus{
 border-color:#ffc107;
}

/* TABLE WRAPPER */
.table-wrapper{
 max-height:600px;
 overflow-y:auto;
 border:1px solid #ddd;
}

/* HEADER STICKY */
thead th{
 position:sticky;
 top:0;
 z-index:2;
 background:#ffc107;
}

th {
    background: #ffc107;
    font-weight: bold;
}

th, td {
    border: 1px solid #ccc;
    padding: 10px;
    text-align: center;
}

tr:hover {
    background: #f9f9f9;
}

/* NUMBER ALIGN */
td:nth-child(6),
td:nth-child(7),
td:nth-child(8){
    text-align: right;
    padding-right: 15px;
}

/* STATUS BADGE */
.badge {
    display:inline-block;
    padding:4px 8px;
    border-radius:20px;
    font-size:10px;
    font-weight:bold;
    white-space:nowrap;
    width:auto;
}

.submitted { background:#6c757d; color:white; }
.approved { background:#28a745; color:white; }
.rejected { background:#dc3545; color:white; }

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
.btn{
 padding:6px 12px;
 border-radius:6px;
 color:white;
 text-decoration:none;
 font-weight:bold;
 display:inline-block;
 font-size:13px;
}

.review{
 background:#ffc107;
 color:white;
}

.detail{
 background:#0d6efd;
 color:white;
}

.btn:hover{
 opacity:0.9;
}

a:hover {
    text-decoration: underline;
}

.table-wrapper{
 max-height:600px;
 overflow-y:auto;
 border:1px solid #ddd;
}

thead th{
 position: sticky;
 top: 0;
 z-index: 10;
 background: #ffc107;
}

</style>

</head>
<body>

<div class="container">

<h2>Review LPJ (Finance)</h2>

<div class="search-box">
    <input type="text" id="searchInput" placeholder="Cari data LPJ...">
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
 <th>Status</th>
 <th>Aksi</th>
</tr>
</thead>

<tbody>

<?php
$no = 1;
while($d = mysqli_fetch_assoc($query)){

    // FORMAT TANGGAL
    $tanggal = date('d/m/Y', strtotime($d['created_at']));

    // BATAS LPJ
    $batas = date('d/m/Y', strtotime($d['periode_akhir'] . ' +7 days'));

    // RUPIAH
    $anggaran_val = $d['total_anggaran'];
    $realisasi_val = $d['total_realisasi'];

    $anggaran = "Rp " . number_format($anggaran_val,0,',','.');
    $realisasi = "Rp " . number_format($realisasi_val,0,',','.');

    // SELISIH
    $selisih_val = $anggaran_val - $realisasi_val;

    if($selisih_val >= 0){
        $selisih = "<span style='color:green;font-weight:bold;'>Rp ".number_format($selisih_val,0,',','.')."</span>";
    } else {
        $selisih = "<span style='color:red;font-weight:bold;'>(Rp ".number_format(abs($selisih_val),0,',','.').")</span>";
    }

    // STATUS DEADLINE
$tanggal_submit = strtotime($d['created_at']);
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

    // STATUS BADGE
    $status = $d['status'];

    if($status == 'Approved'){
        $status_badge = "<span class='badge approved'>Approved</span>";
    } elseif($status == 'Rejected'){
        $status_badge = "<span class='badge rejected'>Rejected</span>";
    } else {
        $status_badge = "<span class='badge submitted'>Submitted</span>";
    }

    // AKSI
    if($status == 'Submitted'){
        $aksi = "<a class='btn review' href='lpj_review_detail.php?id=".$d['id']."'>Review</a>";
    } else {
        $aksi = "<a class='btn detail' href='lpj_detail.php?id=".$d['id']."'>Detail</a>";
    }
?>

<tr>
    <td><?= $no++ ?></td>
    <td><?= $tanggal ?></td>
    <td><?= $d['no_lpj'] ?></td>
    <td><?= $d['no_rab'] ?></td>
    <td><?= $batas ?></td>
    <td><?= $anggaran ?></td>
    <td><?= $realisasi ?></td>
    <td><?= $selisih ?></td>

    <td><?= $deadline_badge ?></td>

    <!-- ✅ TAMBAHAN ICON 👁 -->
    <td>
        <?= $status_badge ?>

        <?php if($status == 'Rejected' && !empty($d['rejected_note'])){ ?>
            <span onclick="showNote('<?= htmlspecialchars($d['rejected_note'], ENT_QUOTES) ?>')" 
                  style="cursor:pointer; margin-left:5px;">
                👁
            </span>
        <?php } ?>
    </td>

    <td><?= $aksi ?></td>
</tr>

<?php } ?>

</tbody>
</table>
</div>
</div>

<!-- 🔥 SCRIPT POPUP -->
<script>
function showNote(note){
    alert("Alasan Reject:\n\n" + note);
}
</script>

<script>
document.getElementById("searchInput").addEventListener("keyup", function() {

    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll("table tbody tr");

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