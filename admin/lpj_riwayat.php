<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
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
<title>Riwayat LPJ</title>

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

.table-container{
    overflow-x:auto;
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
.btn-detail{
    background:#0d6efd;
    color:white;
    padding:6px 12px;
    border-radius:6px;
    text-decoration:none;
    font-size:13px;
    font-weight:bold;
}

.btn-detail:hover{
    background:#0b5ed7;
}

.btn-edit{
    background:#ffc107;
    color:black;
    padding:6px 12px;
    border-radius:6px;
    text-decoration:none;
    font-size:13px;
    font-weight:bold;
}

.btn-edit:hover{
    background:#e0a800;
}

a:hover {
    text-decoration: underline;
}

.search-box{
    margin-bottom:15px;
}

.search-box input{
    width:300px;
    padding:10px;
    border:1px solid #ccc;
    border-radius:8px;
    outline:none;
}

.search-box input:focus{
    border-color:#b8860b;
}

.table-container{
    max-height:600px;
    overflow-y:auto;
    border:1px solid #ccc;
}

th{
    background:#ffc107;
    font-weight:bold;
    position:sticky;
    top:0;
    z-index:10;
}

</style>

</head>
<body>

<div class="container">

<h2>Riwayat LPJ</h2>

<div class="search-box">
    <input type="text" id="searchInput" placeholder="Cari data LPJ...">
</div>

<div class="table-container">
<table>
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
        $selisih = "<span style='color:green;font-weight:bold;'>Rp " . number_format($selisih_val,0,',','.') . "</span>";
    } else {
        $selisih = "<span style='color:red;font-weight:bold;'>(Rp " . number_format(abs($selisih_val),0,',','.') . ")</span>";
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

    // STATUS
    $status = $d['status'] ?? 'Submitted';

    if($status == 'Approved'){
        $status_badge = "<span class='badge approved'>Approved</span>";
    } elseif($status == 'Rejected'){
        $status_badge = "<span class='badge rejected'>Rejected</span>";
    } else {
        $status_badge = "<span class='badge submitted'>Submitted</span>";
    }

    // AKSI
    if($status == 'Rejected'){

    $aksi = "<a class='btn-edit' href='lpj_edit.php?id=".$d['id']."'>Edit</a>";

} else {

    $aksi = "<a class='btn-detail' href='lpj_detail.php?id=".$d['id']."'>Detail</a>";

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

    <!-- ✅ REVISI STEP 3 (ICON 👁) -->
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

</table>

</div>

<!-- 🔥 SCRIPT POPUP -->
<script>
function showNote(note){
    alert("Alasan Reject:\n\n" + note);
}
</script>

<script>

document.getElementById("searchInput").addEventListener("keyup", function() {

    let input = this.value.toLowerCase();
    let rows = document.querySelectorAll("table tr");

    rows.forEach((row, index) => {

        if(index === 0) return;

        let text = row.innerText.toLowerCase();

        row.style.display = text.includes(input) ? "" : "none";
    });

});

</script>

</body>
</html>