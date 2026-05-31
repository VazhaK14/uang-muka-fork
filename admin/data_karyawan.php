<?php
session_start();
include "../config/db.php";

// =======================
// FUNCTION AUTO ID
// =======================
function generateID($conn){
    $query = mysqli_query($conn, "
        SELECT id_karyawan 
        FROM karyawan 
        ORDER BY id_karyawan DESC 
        LIMIT 1
    ");

    $data = mysqli_fetch_assoc($query);

    if($data){
        $num = (int) substr($data['id_karyawan'], 1);
        $num++;
    } else {
        $num = 1;
    }

    return "K" . str_pad($num, 3, "0", STR_PAD_LEFT);
}

$auto_id = generateID($conn);

// =======================
// SIMPAN / UPDATE DATA
// =======================
if(isset($_POST['simpan'])){

    $id             = $_POST['id'];
    $nama_karyawan  = $_POST['nama_karyawan'];
    $rekening       = $_POST['rekening'];

    $bank = $_POST['bank'];

if($bank == 'Lainnya'){
    $bank = $_POST['bank_lainnya'];
}

    if($id == "") {

        $id_karyawan = generateID($conn);

        mysqli_query($conn, "
        INSERT INTO karyawan
        (id_karyawan,nama_karyawan,bank,rekening)
        VALUES
        ('$id_karyawan','$nama_karyawan','$bank','$rekening')
        ");

    } else {

        mysqli_query($conn, "
        UPDATE karyawan SET
        nama_karyawan='$nama_karyawan',
        bank='$bank',
        rekening='$rekening'
        WHERE id='$id'
        ");
    }

    header("Location: data_karyawan.php?success=1");
    exit;
}

// =======================
// HAPUS
// =======================
if(isset($_GET['hapus'])){
    mysqli_query($conn, "DELETE FROM karyawan WHERE id='".$_GET['hapus']."'");
    header("Location: data_karyawan.php?success=2");
    exit;
}

// =======================
$q = mysqli_query($conn, "SELECT * FROM karyawan ORDER BY id ASC");
?>

<!DOCTYPE html>
<html>
<head>
<title>Data Karyawan</title>

<style>
body {
    font-family: Arial;
    background: #f4f4f4;
    margin: 0;
}

/* CONTENT */
.content {
    padding: 40px;
    display: flex;
    justify-content: center;
}

/* CARD */
.card {
    width: 100%;
    max-width: 900px;
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
}

/* HEADER */
.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

/* TABLE */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

th, td {
    padding: 12px;
    border: 1px solid #eee;
}

th {
    background: #b8860b;
    color: white;
    font-weight: bold;
}

td:first-child {
    text-align: center;
}

/* BUTTON */
.btn {
    background: #b8860b;
    color: white;
    padding: 8px 15px;
    border: none;
    cursor: pointer;
    border-radius: 6px;
    transition: 0.2s;
}

.btn:hover {
    background: #a0760a;
}

/* AKSI BUTTON */
.aksi {
    display: flex;
    gap: 8px;
}

.btn-edit {
    background: #3498db;
    color: white;
    padding: 6px 12px;
    border-radius: 5px;
    cursor: pointer;
    font-size: 13px;
}

.btn-delete {
    background: #e74c3c;
    color: white;
    padding: 6px 12px;
    border-radius: 5px;
    text-decoration: none;
    font-size: 13px;
}

/* MODAL BACKDROP */
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

/* MODAL BOX */
.modal-content {
    background: white;
    width: 420px;
    margin: 80px auto;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.2);
    animation: fadeIn 0.2s ease-in-out;
}

/* ANIMATION */
@keyframes fadeIn {
    from { transform: translateY(-10px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

/* FORM GROUP */
.form-group {
    margin-bottom: 15px;
}

.form-group label {
    font-weight: bold;
    font-size: 14px;
    display: block;
    margin-bottom: 5px;
}

.form-group input,
.form-group select {

    width: 100%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 6px;
    outline: none;
    box-sizing: border-box;
}

.form-group input:focus,
.form-group select:focus {

    border-color: #b8860b;
}

/* BUTTON AREA */
.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 15px;
}

/* ALERT */
.alert {
    padding: 12px;
    background: #4CAF50;
    color: white;
    margin-bottom: 15px;
    border-radius: 6px;
    font-size: 14px;
}
</style>

</head>

<body>

<div class="content">
<div class="card">

<div class="header">
    <h2>Data Karyawan</h2>
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

<h3 id="modalTitle">Tambah Data</h3>

<form method="POST">

<input type="hidden" name="id" id="id">

<div class="form-group">
    <label>ID Karyawan</label>
    <input 
    type="text"
    name="id_karyawan"
    id="id_karyawan"
    value="<?= $auto_id ?>"
    readonly
>
</div>

<div class="form-group">
    <label>Nama Karyawan</label>
    <input type="text" name="nama_karyawan" id="nama_karyawan" required>
</div>

<div class="form-group">
    <label>Bank</label>

<select 
    name="bank" 
    id="bankSelect"
    class="form-control"
    onchange="toggleBankLainnya()"
    required
>

    <option value="">-- Pilih Bank --</option>

    <option value="MANDIRI">MANDIRI</option>
    <option value="BRI">BRI</option>
    <option value="BTN">BTN</option>
    <option value="BNI">BNI</option>
    <option value="BCA">BCA</option>
    <option value="BJB">BJB</option>
    <option value="Lainnya">Lainnya</option>

</select>

<input 
    type="text"
    name="bank_lainnya"
    id="bankLainnya"
    class="form-control"
    placeholder="Masukkan nama bank"
    style="display:none; margin-top:10px;"
    autocomplete="off"
>
</div>

<div class="form-group">

    <label>No Rekening</label>

    <input 
        type="text"
        name="rekening"
        id="rekening"
        class="form-control"
        required
        inputmode="numeric"
        pattern="[0-9]+"
        maxlength="30"
        placeholder="Masukkan nomor rekening"
    >

</div>

<div class="modal-footer">
    <button type="submit" name="simpan" class="btn">Simpan</button>
    <button type="button" onclick="closeModal()" class="btn">Batal</button>
</div>

</form>

</div>
</div>

<!-- TABEL -->
<table>
<tr>
    <th>No</th>
    <th>ID Karyawan</th>
    <th>Nama Karyawan</th>
    <th>Bank</th>
    <th>No Rekening</th>
    <th>Aksi</th>
</tr>

<?php $no=1; while($d = mysqli_fetch_assoc($q)): ?>

<tr>
    <td><?= $no++ ?></td>
    <td><?= $d['id_karyawan'] ?></td>
    <td><?= $d['nama_karyawan'] ?></td>
    <td><?= $d['bank'] ?></td>
    <td><?= $d['rekening'] ?></td>
    <td>
        <div class="aksi">
            <span class="btn-edit" onclick="editData(
            '<?= $d['id'] ?>',
            '<?= $d['id_karyawan'] ?>',
            '<?= $d['nama_karyawan'] ?>',
            '<?= $d['bank'] ?>',
            '<?= $d['rekening'] ?>'
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
var autoID = "<?= $auto_id ?>";

function openModal(){
    document.getElementById("modalForm").style.display = "block";
    document.getElementById("modalTitle").innerText = "Tambah Data";

    document.getElementById("id").value = "";
    document.getElementById("nama_karyawan").value = "";
    document.getElementById("rekening").value = "";

    document.getElementById("id_karyawan").value = autoID;
}

function closeModal(){
    document.getElementById("modalForm").style.display = "none";
}

function editData(id, id_karyawan, nama, bank, rekening)
{
    document.getElementById("modalForm").style.display = "block";

    document.getElementById("modalTitle").innerText = "Edit Data";

    document.getElementById("id").value = id;

    document.getElementById("id_karyawan").value = id_karyawan;

    document.getElementById("nama_karyawan").value = nama;

    document.getElementById("bankSelect").value = bank;

    document.getElementById("rekening").value = rekening;
}

function toggleBankLainnya(){

    const select = document.getElementById('bankSelect');
    const input = document.getElementById('bankLainnya');

    if(select.value === 'Lainnya'){
        input.style.display = 'block';
        input.required = true;
    }else{
        input.style.display = 'none';
        input.required = false;
    }
}

</script>

</body>
</html>