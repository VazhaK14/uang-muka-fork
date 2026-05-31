<?php
session_start();
include "../config/db.php";

if ($_SESSION['role'] != 'finance') {
    header("Location: ../auth/login.php");
    exit;
}

// hanya yang sudah di approve
$q = mysqli_query($conn, "SELECT * FROM rab WHERE status='Approved' ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
<title>Pencairan Dana</title>

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
.table-container{
    max-height:600px;
    overflow-y:auto;
    border:1px solid #ccc;
    background:white;
}

table{
    border-collapse:collapse;
    width:100%;
}

th{
    background:#ffc107;
    position:sticky;
    top:0;
    z-index:10;
}

th, td{
    border:1px solid #ccc;
    padding:10px;
    text-align:center;
    font-size:14px;
}

tr:hover{
    background:#fafafa;
}

/* BUTTON */
.btn{
    padding:6px 12px;
    text-decoration:none;
    border-radius:6px;
    color:white;
    font-weight:bold;
    font-size:13px;
}

.process{
    background:#ffc107;
    color:black;
}

.process:hover{
    background:#e0a800;
}

.detail{
    background:#0d6efd;
}

.detail:hover{
    background:#0b5ed7;
}

/* STATUS */
.pending{
    background:#dc3545;
    color:white;
    padding:5px 10px;
    border-radius:20px;
    font-size:12px;
    font-weight:bold;
}

.paid{
    background:#28a745;
    color:white;
    padding:5px 10px;
    border-radius:20px;
    font-size:12px;
    font-weight:bold;
}

</style>
</head>

<body>

<h2>Pencairan Dana</h2>

<!-- SEARCH -->
<div class="search-box">
    <input type="text" id="searchInput" placeholder="Cari data pencairan...">
</div>

<div class="table-container">

<table id="pencairanTable">

<tr>
<th>No</th>
<th>Tanggal Pengajuan</th>
<th>Nomor RAB</th>
<th>Nama Klien</th>
<th>Jenis Penugasan</th>
<th>Total Anggaran</th>
<th>Detail RAB</th>
<th>Status</th>
<th>Aksi</th>
</tr>

<?php $no=1; while($d = mysqli_fetch_assoc($q)){ ?>

<tr>

<td><?= $no++ ?></td>

<td><?= date('d-m-Y', strtotime($d['created_at'])) ?></td>

<td><?= $d['no_rab'] ?></td>

<td><?= $d['nama_klien'] ?></td>

<td><?= $d['jenis_penugasan'] ?></td>

<td>
Rp <?= number_format($d['total_anggaran'],0,',','.') ?>
</td>

<td>

<a href="../admin/rab_detail.php?id=<?= $d['id'] ?>" 
   class="btn detail">
    Detail
</a>

</td>

<td>

<?php if($d['pencairan']=='Pending'){ ?>

    <span class="pending">Pending</span>

<?php } else { ?>

    <span class="paid">Paid</span>

<?php } ?>

</td>

<td>

<?php if($d['pencairan']=='Pending'){ ?>

    <a href="pencairan_form.php?id=<?= $d['id'] ?>" class="btn process">
        Process
    </a>

<?php } else { ?>

    <a href="pencairan_detail.php?id=<?= $d['id'] ?>" class="btn detail">
        Detail
    </a>

<?php } ?>

</td>

</tr>

<?php } ?>

</table>

</div>

<!-- SEARCH SCRIPT -->
<script>

document.getElementById("searchInput").addEventListener("keyup", function() {

    let input = this.value.toLowerCase();
    let rows = document.querySelectorAll("#pencairanTable tr");

    rows.forEach((row, index) => {

        if(index === 0) return;

        let text = row.innerText.toLowerCase();

        row.style.display = text.includes(input) ? "" : "none";
    });

});

</script>

</body>
</html>