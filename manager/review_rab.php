<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'director') {
    header("Location: ../auth/login.php");
    exit;
}

$q = mysqli_query($conn, "SELECT * FROM rab ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
<title>Review RAB</title>
<style>
body{
    font-family:Arial;
    padding:20px;
    background:#f4f4f4;
}

/* SEARCH */
.search-box{
    margin-bottom:15px;
}

.search-box input{
    width:280px;
    padding:10px;
    border:1px solid #ccc;
    border-radius:6px;
    outline:none;
}

/* TABLE */
.table-wrapper{
    max-height:650px;
    overflow-y:auto;
    background:white;
}

table{
    border-collapse:collapse;
    width:100%;
}

thead th{
    position:sticky;
    top:0;
    z-index:10;
    background:#ffc107;
}

th, td{
    border:1px solid #ddd;
    padding:8px;
    text-align:center;
}

/* STATUS */
.badge{
    padding:5px 10px;
    border-radius:20px;
    color:white;
    font-size:12px;
    font-weight:bold;
}

.approved{
    background:#28a745;
}

.rejected{
    background:#dc3545;
}

.submitted{
    background:#6c757d;
}

.pending{
    background:#dc3545;
}

.paid{
    background:#28a745;
}

/* BUTTON */
.btn{
    padding:6px 12px;
    border-radius:5px;
    color:white;
    text-decoration:none;
    font-weight:bold;
    font-size:13px;
}

.detail{
    background:#0d6efd;
}

.review{
    background:#ffc107;
    color:black;
}

/* ICON MATA */
.eye-icon{
    color:black;
    font-size:14px;
    cursor:pointer;
    margin-left:5px;
}

.table-wrapper{
    max-height:650px;
    overflow-y:auto;
}

thead th{
    position:sticky;
    top:0;
    background:#ffc107;
    z-index:100;
}

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

</style>
</head>
<body>

<h2>Review RAB</h2>

<div class="search-box">
    <input 
        type="text" 
        id="searchInput" 
        placeholder="Cari data RAB..."
    >
</div>

<div class="table-wrapper">

<table>

<thead>
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
</thead>

<tbody>

<?php
$no = 1;
while($d = mysqli_fetch_assoc($q)){

    $tanggal = date('d-m-Y', strtotime($d['created_at']));

    $statusClass = "";
    if($d['status'] == 'Submitted') $statusClass = 'status-submitted';
    if($d['status'] == 'Approved') $statusClass = 'status-approved';
    if($d['status'] == 'Rejected') $statusClass = 'status-rejected';
?>

<tr>
<td><?= $no++ ?></td>
<td><?= $tanggal ?></td>
<td><?= $d['no_rab'] ?></td>
<td><?= $d['nama_klien'] ?></td>
<td><?= $d['jenis_penugasan'] ?></td>
<td>Rp <?= number_format($d['total_anggaran'],0,',','.') ?></td>

<!-- ✅ REVISI 2 + 4 -->
<td>

<?php if($d['status'] == 'Approved'){ ?>
    <span class="badge approved">Approved</span>

<?php } elseif($d['status'] == 'Rejected'){ ?>
    <span class="badge rejected">Rejected</span>

<?php } else { ?>
    <span class="badge submitted">Submitted</span>
<?php } ?>

    <?php if($d['status'] == 'Rejected' && !empty($d['rejected_note'])){ ?>
        <span onclick="showNote('<?= htmlspecialchars($d['rejected_note'], ENT_QUOTES) ?>')" 
              style="cursor:pointer; margin-left:5px;">
            👁
        </span>
    <?php } ?>
</td>

<td>
<?php if($d['pencairan'] == 'Pending'){ ?>
    <span class="badge pending">Pending</span>
<?php } else { ?>
    <span class="badge paid">Paid</span>
<?php } ?>
</td>

<td>

<!-- 🔥 REVISI 1 -->
<?php if($d['status'] == 'Submitted'){ ?>
    <a href="rab_review_detail.php?id=<?= $d['id'] ?>" class="btn review">Review</a>
<?php } else { ?>
    <a href="../admin/rab_detail.php?id=<?= $d['id'] ?>" class="btn detail">Detail</a>
<?php } ?>

</td>

<td>
<?php if($d['status'] == 'Approved' && $d['pencairan'] == 'Pending'){ ?>
    -
<?php } elseif($d['pencairan'] == 'Paid'){ ?>
    <a href="../finance/pencairan_detail.php?id=<?= $d['id'] ?>" class="btn detail">Detail</a>
<?php } else { ?>
    -
<?php } ?>
</td>

</tr>

<?php } ?>

</tbody>
</table>

<!-- 🔥 SCRIPT REVISI 4 -->
<script>
function showNote(note){
    alert("Alasan Reject:\n\n" + note);
}
</script>

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