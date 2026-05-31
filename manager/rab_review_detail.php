<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'director') {
    header("Location: ../auth/login.php");
    exit;
}

$id = $_GET['id'] ?? 0;

// ambil status (buat kontrol tombol)
$q = mysqli_query($conn, "SELECT * FROM rab WHERE id='$id'");
$d = mysqli_fetch_assoc($q);

// ================= APPROVE =================
if(isset($_POST['approve'])){

    $nama_director = $_SESSION['nama_lengkap'];

    mysqli_query($conn, "
        UPDATE rab 
        SET 
            status='Approved',
            approved_by='$nama_director',
            approved_at=NOW()
        WHERE id='$id'
    ");

    header("Location: review_rab.php");
    exit;
}

// ================= REJECT =================
if(isset($_POST['reject'])){
    $note = $_POST['rejected_note'];

    mysqli_query($conn, "
        UPDATE rab 
        SET status='Rejected',
            rejected_note='".mysqli_real_escape_string($conn,$note)."',
            rejected_by='".$_SESSION['nama_lengkap']."',
            rejected_at=NOW()
        WHERE id='$id'
    ");

    header("Location: review_rab.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Review Detail RAB</title>

<style>
body { font-family: Arial; padding:20px; background:#f4f4f4; }

.container {
    background:white;
    padding:20px;
    border-radius:10px;
}

iframe {
    width:100%;
    height:600px;
    border:none;
    margin-bottom:20px;
}

.btn {
    padding:10px 20px;
    border:none;
    border-radius:5px;
    color:white;
    cursor:pointer;
}

.approve { background:green; }
.reject { background:red; }

textarea {
    width:100%;
    height:80px;
    margin-top:10px;
}

.box-reject {
    background:#ffe5e5;
    padding:10px;
    border-left:5px solid red;
    margin-bottom:15px;
}
</style>

<script>
function showReject(){
    document.getElementById("rejectBox").style.display = "block";
}
</script>

</head>

<body>

<div class="container">

<h2>Review RAB</h2>

<!-- 🔥 FULL DETAIL DARI ADMIN -->
<iframe src="../admin/rab_detail.php?id=<?= $id ?>"></iframe>

<!-- 🔥 AKSI -->
<?php if($d['status'] == 'Submitted'){ ?>

<form method="POST" style="display:inline;">
    <button name="approve" class="btn approve">Approve</button>
</form>

<button onclick="showReject()" class="btn reject">Reject</button>

<div id="rejectBox" style="display:none;">
    <form method="POST">
        <textarea name="rejected_note" placeholder="Masukkan alasan reject..." required></textarea>
        <br><br>
        <button name="reject" class="btn reject">Submit Reject</button>
    </form>
</div>

<?php } ?>

</div>

</body>
</html>