<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// ambil data rab
$query = mysqli_query($conn, "SELECT * FROM rab ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
<title>Riwayat RAB</title>

<style>

body{
    font-family:Arial;
    padding:20px;
    background:#f4f4f4;
}

/* TITLE */
h2{
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
    border-radius:8px;
    outline:none;
}

.search-box input:focus{
    border-color:#b8860b;
}

/* TABLE */
table{
    border-collapse:collapse;
    width:100%;
    background:white;
    box-shadow:0 3px 10px rgba(0,0,0,0.1);
}

th{
    background:#ffc107;
    color:black;
    padding:10px;
    position:sticky;
    top:0;
}

th, td{
    border:1px solid #ddd;
    padding:10px;
    text-align:center;
    font-size:14px;
}

tr:hover{
    background:#fafafa;
}

/* STATUS */
.status-approved{
    background:#28a745;
    color:white;
    padding:5px 10px;
    border-radius:20px;
    font-size:12px;
    font-weight:bold;
}

.status-rejected{
    background:#dc3545;
    color:white;
    padding:5px 10px;
    border-radius:20px;
    font-size:12px;
    font-weight:bold;
}

.status-pending{
    background:#dc3545;
    color:white;
    padding:5px 10px;
    border-radius:20px;
    font-size:12px;
    font-weight:bold;
}

.status-submitted{
    background:#6c757d;
    color:white;
    padding:5px 10px;
    border-radius:20px;
    font-size:12px;
    font-weight:bold;
}

/* PENCAIRAN */
.paid{
    background:#28a745;
    color:white;
    padding:5px 10px;
    border-radius:20px;
    font-size:12px;
    font-weight:bold;
}

.pending{
    background:#dc3545;
    color:white;
    padding:5px 10px;
    border-radius:20px;
    font-size:12px;
    font-weight:bold;
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

/* NO DATA */
.no-data{
    color:#888;
    font-style:italic;
}

</style>
</head>

<body>

<h2>Riwayat Pengajuan RAB</h2>

<!-- SEARCH -->
<div class="search-box">
    <input type="text" id="searchInput" placeholder="Cari data RAB...">
</div>

<table id="rabTable">

<tr>

<th>No</th>
<th>Tanggal Pengajuan</th>
<th>Nomor RAB</th>
<th>Nama Klien</th>
<th>Jenis Penugasan</th>
<th>Total Anggaran</th>
<th>Status</th>
<th>Pencairan Dana</th>
<th>Aksi</th>
<th>Form Pencairan Dana</th>

</tr>

<?php
$no = 1;

while($d = mysqli_fetch_assoc($query)){

    $tanggal = date('d-m-Y', strtotime($d['created_at']));
    $total = "Rp " . number_format($d['total_anggaran'],0,',','.');

    $status = $d['status'];
    $pencairan = $d['pencairan'];

    // ========================
    // STATUS STYLE
    // ========================
    if($status == 'Approved'){

    $status_badge = "<span class='status-approved'>Approved</span>";

} elseif($status == 'Rejected'){

    $status_badge = "<span class='status-rejected'>Rejected</span>";

} elseif($status == 'Submitted'){

    $status_badge = "<span class='status-submitted'>Submitted</span>";

} else {

    $status_badge = "<span class='status-pending'>Pending</span>";

}

    // ========================
    // PENCAIRAN STYLE
    // ========================
    if($pencairan == 'Paid'){
        $pencairan_badge = "<span class='paid'>Paid</span>";
    } else {
        $pencairan_badge = "<span class='pending'>Pending</span>";
    }

    // ========================
    // AKSI
    // ========================
    if($status == 'Rejected'){
        $aksi = "<a class='btn-edit' href='rab_edit.php?id=".$d['id']."'>Edit</a>";
    } else {
        $aksi = "<a class='btn-detail' href='rab_detail.php?id=".$d['id']."'>Detail</a>";
    }

    // ========================
    // FORM PENCAIRAN
    // ========================
    if($status == 'Approved' && $pencairan == 'Paid'){
        $form = "<a class='btn-detail' href='../finance/pencairan_detail.php?id=".$d['id']."'>Detail</a>";
    } else {
        $form = "<span class='no-data'>-</span>";
    }
?>

<tr>

    <td><?= $no++ ?></td>

    <td><?= $tanggal ?></td>

    <td><?= htmlspecialchars($d['no_rab']) ?></td>

    <td><?= htmlspecialchars($d['nama_klien']) ?></td>

    <td><?= htmlspecialchars($d['jenis_penugasan']) ?></td>

    <td><?= $total ?></td>

    <td>

        <?= $status_badge ?>

        <?php if($status == 'Rejected' && !empty($d['rejected_note'])){ ?>

            <span onclick="showNote('<?= htmlspecialchars($d['rejected_note'], ENT_QUOTES) ?>')" 
                  style="cursor:pointer; margin-left:5px;">
                👁
            </span>

        <?php } ?>

    </td>

    <td><?= $pencairan_badge ?></td>

    <td><?= $aksi ?></td>

    <td><?= $form ?></td>

</tr>

<?php } ?>

</table>

<!-- SEARCH SCRIPT -->
<script>

document.getElementById("searchInput").addEventListener("keyup", function() {

    let input = this.value.toLowerCase();
    let rows = document.querySelectorAll("#rabTable tr");

    rows.forEach((row, index) => {

        if(index === 0) return;

        let text = row.innerText.toLowerCase();

        row.style.display = text.includes(input) ? "" : "none";
    });

});

/* POPUP NOTE */
function showNote(note){
    alert("Alasan Reject:\n\n" + note);
}

</script>

</body>
</html>