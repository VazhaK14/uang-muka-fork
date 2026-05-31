<?php
session_start();
include "../config/db.php";

// =======================
// AUTO GENERATE ID (AJAX DI FILE INI)
// =======================
if(isset($_GET['get_id'])){

    $kode = $_GET['kode'];

    $q = mysqli_query($conn, "
        SELECT id_klien 
        FROM klien 
        WHERE id_klien LIKE '$kode-%'
        ORDER BY id_klien DESC
        LIMIT 1
    ");

    $data = mysqli_fetch_assoc($q);

    if($data){
        $last = $data['id_klien'];
        $num = (int) substr($last, strpos($last, '-') + 1);
        $num++;
    } else {
        $num = 1;
    }

    $newID = $kode . '-' . str_pad($num, 3, '0', STR_PAD_LEFT);

    echo $newID;
    exit;
}

// =======================
// SIMPAN / UPDATE
// =======================
if(isset($_POST['simpan'])){

    $id            = $_POST['id'];
    $id_klien      = $_POST['id_klien'];
    $nama_klien    = $_POST['nama_klien'];
    $bidang        = $_POST['bidang'];
    $alamat        = $_POST['alamat'];
    $kontak        = $_POST['kontak'];

    if($id == ""){
        mysqli_query($conn, "
        INSERT INTO klien (id_klien, nama_klien, bidang, alamat, kontak)
        VALUES ('$id_klien','$nama_klien','$bidang','$alamat','$kontak')
        ");
    } else {
        mysqli_query($conn, "
        UPDATE klien SET
        id_klien='$id_klien',
        nama_klien='$nama_klien',
        bidang='$bidang',
        alamat='$alamat',
        kontak='$kontak'
        WHERE id='$id'
        ");
    }

    header("Location: data_klien.php?success=1");
    exit;
}

// =======================
// HAPUS
// =======================
if(isset($_GET['hapus'])){
    mysqli_query($conn, "DELETE FROM klien WHERE id='".$_GET['hapus']."'");
    header("Location: data_klien.php?success=2");
    exit;
}

// =======================
$q = mysqli_query($conn, "SELECT * FROM klien ORDER BY id ASC");

function getNamaBidang($kode){
    $map = [
        "C1" => "Jasa",
        "C2" => "Dagang",
        "C3" => "Manufaktur",
        "C4" => "Kesehatan",
        "C5" => "Konstruksi & Properti",
        "C6" => "Keuangan",
        "C7" => "Pendidikan",
        "C8" => "Transportasi & Logistik",
        "C9" => "Teknologi",
        "C10" => "Energi & Pertambangan",
        "C11" => "Yayasan",
        "C12" => "Lainnya"
    ];

    return $map[$kode] ?? $kode;
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Data Klien</title>

<style>
/* CSS TIDAK DIUBAH */
body { font-family: Arial; background: #f4f4f4; margin: 0; }
.content { padding: 40px; display: flex; justify-content: center; }
.card { width: 100%; max-width: 1000px; background: white; padding: 25px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
.header { display: flex; justify-content: space-between; align-items: center; }
table { width: 100%; border-collapse: collapse; margin-top: 20px; }
th, td { padding: 10px; border: 1px solid #ddd; }
th { background: #b8860b; color: white; }
td:first-child { text-align: center; }
.btn { background: #b8860b; color: white; padding: 7px 14px; border: none; cursor: pointer; border-radius: 6px; }
.btn:hover { background: #a0760a; }
.aksi { display: flex; gap: 8px; }
.btn-edit { background: #3498db; color: white; padding: 5px 10px; border-radius: 5px; cursor: pointer; }
.btn-delete { background: #e74c3c; color: white; padding: 5px 10px; border-radius: 5px; text-decoration: none; }
.modal { display: none; position: fixed; z-index: 999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
.modal-content { background: white; width: 450px; margin: 80px auto; padding: 20px; border-radius: 10px; }
.form-group { margin-bottom: 12px; }
.form-group label { display: block; margin-bottom: 5px; font-weight: 500; }
.form-group input, .form-group select, .form-group textarea { width: 100%; padding: 8px; box-sizing: border-box; }
.alert { padding: 10px; background: #4CAF50; color: white; margin-bottom: 15px; border-radius: 6px; }
</style>

</head>

<body>

<div class="content">
<div class="card">

<div class="header">
    <h2>Data Klien</h2>
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

<h3 id="modalTitle">Tambah Data Klien</h3>

<form method="POST">

<input type="hidden" name="id" id="id">

<!-- 🔥 DIPINDAH KE ATAS -->
<div class="form-group">
<label>Bidang Bisnis</label>
<select name="bidang" id="bidang" onchange="generateID()" required>
    <option value="">-- Pilih Bidang --</option>
    <option value="C1">Jasa</option>
    <option value="C2">Dagang</option>
    <option value="C3">Manufaktur</option>
    <option value="C4">Kesehatan</option>
    <option value="C5">Konstruksi & Properti</option>
    <option value="C6">Keuangan</option>
    <option value="C7">Pendidikan</option>
    <option value="C8">Transportasi & Logistik</option>
    <option value="C9">Teknologi</option>
    <option value="C10">Energi & Pertambangan</option>
    <option value="C11">Yayasan</option>
    <option value="C12">Lainnya</option>
</select>
</div>

<div class="form-group">
<label>ID Klien</label>
<input type="text" name="id_klien" id="id_klien" readonly>
</div>

<div class="form-group">
<label>Nama Klien</label>
<input type="text" name="nama_klien" id="nama_klien" required>
</div>

<div class="form-group">
<label>Alamat</label>
<textarea name="alamat" id="alamat" required></textarea>
</div>

<div class="form-group">
<label>Kontak</label>
<input type="text" name="kontak" id="kontak" required>
</div>

<button type="submit" name="simpan" class="btn">Simpan</button>
<button type="button" onclick="closeModal()" class="btn">Batal</button>

</form>

</div>
</div>

<table>
<tr>
    <th>No</th>
    <th>Bidang Bisnis</th>
    <th>ID Klien</th>
    <th>Nama Klien</th>
    <th>Alamat</th>
    <th>Kontak</th>
    <th>Aksi</th>
</tr>

<?php $no=1; while($d = mysqli_fetch_assoc($q)): ?>

<tr>
    <td><?= $no++ ?></td>
    <td><?= getNamaBidang($d['bidang']) ?></td>
    <td><?= $d['id_klien'] ?></td>
    <td><?= $d['nama_klien'] ?></td>
    <td><?= $d['alamat'] ?></td>
    <td><?= $d['kontak'] ?></td>
    <td>
        <div class="aksi">
            <span class="btn-edit" onclick="editData(
                '<?= $d['id'] ?>',
                '<?= $d['id_klien'] ?>',
                '<?= $d['nama_klien'] ?>',
                '<?= $d['bidang'] ?>',
                '<?= htmlspecialchars($d['alamat']) ?>',
                '<?= $d['kontak'] ?>'
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
function generateID(){
    let kode = document.getElementById("bidang").value;

    if(kode === "") return;

    fetch("data_klien.php?get_id=1&kode=" + kode)
    .then(res => res.text())
    .then(data => {
        document.getElementById("id_klien").value = data;
    });
}

function openModal(){
    document.getElementById("modalForm").style.display = "block";
    document.getElementById("modalTitle").innerText = "Tambah Data Klien";

    document.getElementById("id").value = "";
    document.getElementById("id_klien").value = "";
    document.getElementById("nama_klien").value = "";
    document.getElementById("bidang").value = "";
    document.getElementById("alamat").value = "";
    document.getElementById("kontak").value = "";
}

function closeModal(){
    document.getElementById("modalForm").style.display = "none";
}

function editData(id, id_klien, nama, bidang, alamat, kontak){
    document.getElementById("modalForm").style.display = "block";
    document.getElementById("modalTitle").innerText = "Edit Data Klien";

    document.getElementById("id").value = id;
    document.getElementById("id_klien").value = id_klien;
    document.getElementById("nama_klien").value = nama;
    document.getElementById("bidang").value = bidang;
    document.getElementById("alamat").value = alamat;
    document.getElementById("kontak").value = kontak;
}
</script>

</body>
</html>