<?php
include "../config/db.php";
?>

<!DOCTYPE html>
<html>
<head>
<title>Data Akun</title>

<style>
body {
    margin:0;
    font-family:Arial;
    background:#f4f4f4;
}

/* CONTENT WRAPPER (🔥 biar ga fullscreen) */
.content {
    margin-left:250px;
    padding:30px;
}

/* CONTAINER BIAR TENGAH */
.card {
    max-width:900px;
    margin:auto;
}

/* TABLE */
table {
    width:100%;
    border-collapse:collapse;
    background:white;
    box-shadow:0 3px 10px rgba(0,0,0,0.1);
    border-radius:8px;
    overflow:hidden;
}

th, td {
    padding:12px;
    border:1px solid #eee;
    text-align:left;
}

th {
    background:#b8860b;
    color:white;
}

tr:nth-child(even){
    background:#fafafa;
}

/* BUTTON */
.btn {
    padding:7px 12px;
    border:none;
    cursor:pointer;
    border-radius:5px;
    font-size:13px;
}

.btn-tambah {
    background:#b8860b;
    color:white;
}

.btn-edit {
    background:#007bff;
    color:white;
}

.btn-hapus {
    background:#dc3545;
    color:white;
}

/* MODAL */
.modal {
    display:none;
    position:fixed;
    z-index:999;
    left:0; top:0;
    width:100%; height:100%;
    background:rgba(0,0,0,0.6);
    justify-content:center;
    align-items:center;
}

/* MODAL CONTENT */
.modal-content {
    background:#fff;
    padding:25px 30px;
    border-radius:12px;
    width:420px;
    max-width:90%;
    box-shadow:0 10px 25px rgba(0,0,0,0.2);
    position:relative;
}

/* TITLE */
.modal-content h3 {
    margin-bottom:20px;
}

/* FORM GRID (🔥 biar rapi) */
.form-group {
    margin-bottom:15px;
}

.form-group label {
    display:block;
    margin-bottom:5px;
    font-weight:500;
}

/* INPUT */
.form-group input,
.form-group textarea {
    width:100%;
    padding:10px;
    border:1px solid #ccc;
    border-radius:6px;
    font-size:14px;
}

/* TEXTAREA */
.form-group textarea {
    resize:none;
    height:70px;
}

/* BUTTON */
.modal-content button {
    background:#b8860b;
    color:white;
    border:none;
    padding:10px;
    border-radius:6px;
    cursor:pointer;
    width:100%;
}

.modal-content button:hover {
    background:#d4a017;
}

/* CLOSE */
.close {
    position:absolute;
    top:10px;
    right:15px;
    font-size:20px;
    cursor:pointer;
}

td:first-child,
th:first-child{
    text-align:center;
}

td:nth-child(2),
th:nth-child(2){
    text-align:center;
}

</style>
</head>

<body>

<?php include "sidebar.php"; ?>

<div class="content">

<div class="card">
<h2>Data Akun</h2>

<button class="btn btn-tambah" onclick="openModal()">+ Tambah</button>

<br><br>

<table>
<tr>
    <th>No</th>
    <th>Kode Akun</th>
    <th>Nama Akun</th>
    <th>Keterangan</th>
    <th>Action</th>
</tr>

<?php
$no = 1;
$q = mysqli_query($conn, "SELECT * FROM akun");

while($d = mysqli_fetch_assoc($q)){
?>

<tr>
<td><?= $no++ ?></td>
<td><?= $d['kode_akun'] ?></td>
<td><?= $d['nama_akun'] ?></td>
<td><?= $d['keterangan'] ?></td>
<td>
    <button class="btn btn-edit" 
        onclick="editData('<?= $d['id'] ?>','<?= $d['kode_akun'] ?>','<?= $d['nama_akun'] ?>','<?= $d['keterangan'] ?>')">
        Edit
    </button>

    <a href="data_akun_hapus.php?id=<?= $d['id'] ?>" onclick="return confirm('Hapus data?')">
        <button class="btn btn-hapus">Hapus</button>
    </a>
</td>
</tr>

<?php } ?>
</table>
</div>

</div>

<!-- MODAL -->
<div class="modal" id="modalAkun">
<div class="modal-content">

<span class="close" onclick="closeModal()">&times;</span>

<h3 id="judulModal">Tambah Akun</h3>

<form method="POST" action="data_akun_simpan.php">
<input type="hidden" name="id" id="id">

<div class="form-group">
<label>Kode Akun</label>
<input type="text" name="kode_akun" id="kode_akun" required>
</div>

<div class="form-group">
<label>Nama Akun</label>
<input type="text" name="nama_akun" id="nama_akun" required>
</div>

<div class="form-group">
<label>Keterangan</label>
<textarea name="keterangan" id="keterangan"></textarea>
</div>

<button type="submit">Simpan</button>
</form>

</div>
</div>

<script>
function openModal(){
    document.getElementById("modalAkun").style.display="flex";
    document.getElementById("judulModal").innerText="Tambah Akun";

    document.getElementById("id").value="";
    document.getElementById("kode_akun").value="";
    document.getElementById("nama_akun").value="";
    document.getElementById("keterangan").value="";
}

function closeModal(){
    document.getElementById("modalAkun").style.display="none";
}

function editData(id,kode,nama,ket){
    openModal();
    document.getElementById("judulModal").innerText="Edit Akun";

    document.getElementById("id").value=id;
    document.getElementById("kode_akun").value=kode;
    document.getElementById("nama_akun").value=nama;
    document.getElementById("keterangan").value=ket;
}
</script>

</body>
</html>