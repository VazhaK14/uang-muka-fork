<?php
session_start();
include "../config/db.php";

// =======================
// AUTO GENERATE ID PENUGASAN
// =======================
if(isset($_GET['get_id'])){

    $q = mysqli_query($conn, "
        SELECT id_penugasan 
        FROM penugasan 
        ORDER BY id_penugasan DESC 
        LIMIT 1
    ");

    $data = mysqli_fetch_assoc($q);

    if($data){
        $last = $data['id_penugasan'];
        $num = (int) substr($last, 2);
        $num++;
    } else {
        $num = 1;
    }

    $newID = "PN" . str_pad($num, 3, '0', STR_PAD_LEFT);

    echo $newID;
    exit;
}

// =======================
// SIMPAN / UPDATE
// =======================
if(isset($_POST['simpan'])){

    $id                = $_POST['id'];
    $id_penugasan      = $_POST['id_penugasan'];
    $jenis_penugasan   = $_POST['jenis_penugasan'];

    if($id == ""){
        mysqli_query($conn, "
        INSERT INTO penugasan (id_penugasan, jenis_penugasan)
        VALUES ('$id_penugasan','$jenis_penugasan')
        ");
    } else {
        mysqli_query($conn, "
        UPDATE penugasan SET
        id_penugasan='$id_penugasan',
        jenis_penugasan='$jenis_penugasan'
        WHERE id='$id'
        ");
    }

    header("Location: data_penugasan.php?success=1");
    exit;
}

// =======================
// HAPUS
// =======================
if(isset($_GET['hapus'])){
    mysqli_query($conn, "DELETE FROM penugasan WHERE id='".$_GET['hapus']."'");
    header("Location: data_penugasan.php?success=2");
    exit;
}

// =======================
$q = mysqli_query($conn, "SELECT * FROM penugasan ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
<title>Data Jenis Penugasan</title>

<style>
body {
    font-family: Arial;
    background: #f4f4f4;
    margin: 0;
}

.content {
    padding: 40px;
    display: flex;
    justify-content: center;
}

.card {
    width: 100%;
    max-width: 900px;
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

th, td {
    padding: 10px;
    border: 1px solid #ddd;
}

th {
    background: #b8860b;
    color: white;
}

td:first-child {
    text-align: center;
}

.btn {
    background: #b8860b;
    color: white;
    padding: 7px 14px;
    border: none;
    cursor: pointer;
    border-radius: 6px;
}

.btn:hover {
    background: #a0760a;
}

.aksi {
    display: flex;
    gap: 8px;
}

.btn-edit {
    background: #3498db;
    color: white;
    padding: 5px 10px;
    border-radius: 5px;
    cursor: pointer;
}

.btn-delete {
    background: #e74c3c;
    color: white;
    padding: 5px 10px;
    border-radius: 5px;
    text-decoration: none;
}

/* MODAL */
.modal {
    display: none;
    position: fixed;
    z-index: 999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
}

.modal-content {
    background: white;
    width: 400px;
    margin: 100px auto;
    padding: 20px;
    border-radius: 10px;
}

/* FORM */
.form-group {
    margin-bottom: 12px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
}

.form-group input {
    width: 100%;
    padding: 8px;
}

/* NOTIF */
.alert {
    padding: 10px;
    background: #4CAF50;
    color: white;
    margin-bottom: 15px;
    border-radius: 6px;
}
</style>

</head>

<body>

<div class="content">
<div class="card">

<div class="header">
    <h2>Data Jenis Penugasan</h2>
    <button class="btn" onclick="openModal()">+ Tambah</button>
</div>

<?php if(isset($_GET['success'])): ?>
<div class="alert">
    <?= $_GET['success']==1 ? "Data berhasil disimpan!" : "Data berhasil dihapus!" ?>
</div>
<?php endif; ?>

<!-- MODAL -->
<div id="modalForm" class="modal">
<div class="modal-content">

<h3 id="modalTitle">Tambah Data Penugasan</h3>

<form method="POST">

<input type="hidden" name="id" id="id">

<div class="form-group">
<label>ID Penugasan</label>
<input type="text" name="id_penugasan" id="id_penugasan" readonly>
</div>

<div class="form-group">
<label>Jenis Penugasan</label>
<input type="text" name="jenis_penugasan" id="jenis_penugasan" required>
</div>

<button type="submit" name="simpan" class="btn">Simpan</button>
<button type="button" onclick="closeModal()" class="btn">Batal</button>

</form>

</div>
</div>

<!-- TABEL -->
<table>
<tr>
    <th>No</th>
    <th>ID Penugasan</th>
    <th>Jenis Penugasan</th>
    <th>Aksi</th>
</tr>

<?php $no=1; while($d = mysqli_fetch_assoc($q)): ?>

<tr>
    <td><?= $no++ ?></td>
    <td><?= $d['id_penugasan'] ?></td>
    <td><?= $d['jenis_penugasan'] ?></td>
    <td>
        <div class="aksi">
            <span class="btn-edit" onclick="editData(
                '<?= $d['id'] ?>',
                '<?= $d['id_penugasan'] ?>',
                '<?= $d['jenis_penugasan'] ?>'
            )">Edit</span>

            <a class="btn-delete" href="?hapus=<?= $d['id'] ?>" onclick="return confirm('Yakin?')">Hapus</a>
        </div>
    </td>
</tr>

<?php endwhile; ?>

</table>

</div>
</div>

<script>

// =======================
// AUTO ID
// =======================
function generateID(){
    fetch("data_penugasan.php?get_id=1")
    .then(res => res.text())
    .then(data => {
        document.getElementById("id_penugasan").value = data;
    });
}

function openModal(){
    document.getElementById("modalForm").style.display = "block";
    document.getElementById("modalTitle").innerText = "Tambah Data Penugasan";

    document.getElementById("id").value = "";
    document.getElementById("id_penugasan").value = "";
    document.getElementById("jenis_penugasan").value = "";

    generateID(); // 🔥 AUTO
}

function closeModal(){
    document.getElementById("modalForm").style.display = "none";
}

function editData(id, id_penugasan, jenis){
    document.getElementById("modalForm").style.display = "block";
    document.getElementById("modalTitle").innerText = "Edit Data Penugasan";

    document.getElementById("id").value = id;
    document.getElementById("id_penugasan").value = id_penugasan;
    document.getElementById("jenis_penugasan").value = jenis;
}

</script>

</body>
</html>