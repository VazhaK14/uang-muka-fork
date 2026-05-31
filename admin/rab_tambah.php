<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

include "../config/db.php";

// =====================
// DATA KLIEN
// =====================
$klien = mysqli_query($conn, "
    SELECT *
    FROM klien
    ORDER BY nama_klien ASC
");

// =====================
// DATA PENUGASAN
// =====================
$penugasan = mysqli_query($conn, "
    SELECT *
    FROM penugasan
    ORDER BY jenis_penugasan ASC
");

// =====================
// DATA KARYAWAN
// =====================
$karyawan = mysqli_query($conn, "
    SELECT *
    FROM karyawan
    ORDER BY nama_karyawan ASC
");

// AUTO NOMOR
$bulan = date('m');
$tahun = date('Y');

function romawi($bln) {
    $r = [1=>'I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'];
    return $r[(int)$bln];
}

$q = mysqli_query($conn, "
    SELECT MAX(no_rab) as max_no 
    FROM rab 
    WHERE MONTH(created_at) = '$bulan' 
    AND YEAR(created_at) = '$tahun'
");

$d = mysqli_fetch_assoc($q);
$urut = 1;

if ($d['max_no']) {
    $pecah = explode('/', $d['max_no']);
    $urut = (int)$pecah[2] + 1;
}

$urut = str_pad($urut, 3, '0', STR_PAD_LEFT);
$no_rab = "RAB/KAP-PQR/$urut/" . romawi($bulan) . "/$tahun";
?>

<!DOCTYPE html>
<html>
<head>
<title>Tambah RAB</title>
<style>

/* ================= GLOBAL ================= */
body {
    font-family: Arial;
    background: #f4f4f4;
    padding: 20px;
}

/* ================= CONTAINER ================= */
.container {
    max-width: 1000px;
    margin: auto;
    background: #fff;
    padding: 30px;
    border-radius: 10px;
}

/* ================= TABLE ================= */
th { background:#ffc107; }

.tabel {
    border-collapse:collapse;
    width:100%;
    margin-top:10px;
}

.tabel th, .tabel td {
    border:1px solid #ccc;
    padding:8px;
    text-align:center;
}

/* ====== FIX WIDTH KOLOM RAB (TAMBAHAN AMAN) ====== */

/* No */
.tabel th:nth-child(1),
.tabel td:nth-child(1){
    width: 50px;
}

/* Deskripsi (dibesarkan) */
.tabel th:nth-child(2),
.tabel td:nth-child(2){
    width: 35%;
}

/* Qty */
.tabel th:nth-child(3),
.tabel td:nth-child(3){
    width: 80px;
}

/* Hari */
.tabel th:nth-child(4),
.tabel td:nth-child(4){
    width: 80px;
}

/* Nominal */
.tabel th:nth-child(5),
.tabel td:nth-child(5){
    width: 150px;
}

/* Total */
.tabel th:nth-child(6),
.tabel td:nth-child(6){
    width: 150px;
}

/* Tombol */
.tabel th:nth-child(7),
.tabel td:nth-child(7){
    width: 60px;
}

/* Input qty & hari biar kecil */
.tabel td:nth-child(3) input,
.tabel td:nth-child(4) input {
    width: 60px;
    text-align: center;
}

/* ================= FORM ================= */
.req { color:red; }

input, select, textarea {
    padding:6px;
    border:1px solid #ccc;
    border-radius:5px;
}

/* ================= BUTTON ================= */
button, input[type="submit"] {
    padding:6px 10px;
    border:none;
    background:#ffc107;
    cursor:pointer;
    border-radius:5px;
}

button:hover, input[type="submit"]:hover {
    background:#e0a800;
}

/* ================= SECTION ================= */
h3 {
    margin-top:30px;
}

</style>
</head>

<body>

<div class="container">

<h2 style="text-align:center;">Rencana Anggaran Biaya</h2>
<p style="text-align:center;">Nomor : <?= $no_rab ?></p>

<form method="POST" action="rab_simpan.php">
<input type="hidden" name="no_rab" value="<?= $no_rab ?>">

<h3>Informasi Penugasan</h3>
<table>
<tr>

<td>
    Nama Klien <span class="req">*</span>
</td>

<td>:</td>

<td>

<select name="nama_klien" required>

    <option value="">-- Pilih Klien --</option>

    <?php while($k = mysqli_fetch_assoc($klien)): ?>
        <option value="<?= $k['nama_klien'] ?>">
            <?= $k['nama_klien'] ?>
        </option>
    <?php endwhile; ?>

</select>

</td>

</tr>

<tr>
<td>Jenis Penugasan <span class="req">*</span></td><td>:</td>
<td>
<select name="jenis_penugasan" required>
    <option value="">-- Pilih Penugasan --</option>

    <?php while($p = mysqli_fetch_assoc($penugasan)): ?>
        <option value="<?= $p['jenis_penugasan'] ?>">
            <?= $p['jenis_penugasan'] ?>
        </option>
    <?php endwhile; ?>

</select>
<br>
<input type="text" name="jenis_lainnya" id="jenis_lainnya" placeholder="Isi jenis penugasan" style="display:none;">
</td>
</tr>

<tr>
<td>Tahun Buku <span class="req">*</span></td><td>:</td>
<td><input type="date" name="tahun_buku" required></td>
</tr>

<tr>
<td>Periode Penugasan <span class="req">*</span></td><td>:</td>
<td>
<input type="date" name="periode_awal" required> s.d 
<input type="date" name="periode_akhir" required>
</td>
</tr>
</table>

<h3>Susunan Tim Audit</h3>
<table>
<tr><td>Signing Partner</td><td>:</td><td><select name="signing_partner">
<option value="">-- Pilih Karyawan --</option>

<?php
mysqli_data_seek($karyawan, 0);
while($kar = mysqli_fetch_assoc($karyawan)):
?>

<option value="<?= $kar['nama_karyawan'] ?>">
    <?= $kar['nama_karyawan'] ?>
</option>

<?php endwhile; ?>
</select>
<tr><td>Partner Review</td><td>:</td><td><select name="partner_review">
<option value="">-- Pilih Karyawan --</option>

<?php
mysqli_data_seek($karyawan, 0);
while($kar = mysqli_fetch_assoc($karyawan)):
?>

<option value="<?= $kar['nama_karyawan'] ?>">
    <?= $kar['nama_karyawan'] ?>
</option>

<?php endwhile; ?>
</select>
<tr><td>Manager In-Charge</td><td>:</td><td><select name="manager_ic">
<option value="">-- Pilih Karyawan --</option>

<?php
mysqli_data_seek($karyawan, 0);
while($kar = mysqli_fetch_assoc($karyawan)):
?>

<option value="<?= $kar['nama_karyawan'] ?>">
    <?= $kar['nama_karyawan'] ?>
</option>

<?php endwhile; ?>
</select>
<tr><td>Auditor In-Charge</td><td>:</td><td><select name="auditor_ic">
<option value="">-- Pilih Karyawan --</option>

<?php
mysqli_data_seek($karyawan, 0);
while($kar = mysqli_fetch_assoc($karyawan)):
?>

<option value="<?= $kar['nama_karyawan'] ?>">
    <?= $kar['nama_karyawan'] ?>
</option>

<?php endwhile; ?>
</select>

<tr>
<td>Assistant</td><td>:</td>
<td id="assistant_container">
<div>
<select name="assistant[]">

<option value="">-- Pilih Karyawan --</option>

<?php
mysqli_data_seek($karyawan, 0);
while($kar = mysqli_fetch_assoc($karyawan)):
?>

<option value="<?= $kar['nama_karyawan'] ?>">
    <?= $kar['nama_karyawan'] ?>
</option>

<?php endwhile; ?>

</select>
<button type="button" onclick="hapusAssistant(this)">-</button>
</div>
</td>
</tr>

<tr>
<td></td><td></td>
<td><button type="button" onclick="tambahAssistant()">+ Baris Assistant</button></td>
</tr>
</table>

<h3>Input RAB</h3>

<?php
$kategori = ['Akomodasi','Transportasi','Konsumsi','Lain-lain'];
foreach($kategori as $k){
?>
<h4><?= $k ?></h4>
<table class="tabel" id="<?= $k ?>_table">
<tr>
<th>No</th><th>Deskripsi</th><th>Qty</th><th>Hari</th><th>Nominal</th><th>Total</th><th></th>
</tr>
</table>

<button type="button" onclick="tambahBaris('<?= $k ?>')">+ Tambah Baris</button>
<p>Subtotal: Rp <span id="subtotal_<?= $k ?>">0</span></p>
<?php } ?>

<h3>TOTAL ANGGARAN</h3>
<p style="font-size:18px; font-weight:bold;">
Rp <span id="total_anggaran">0</span>
</p>

<br><br>
<input type="submit" value="Submit">

</form>

</div>

<!-- JS TIDAK DIUBAH -->
<script>
// (SEMUA JS KAMU TETAP SAMA)
function cekLainnya(){
    let v = document.getElementById("jenis_penugasan").value;
    document.getElementById("jenis_lainnya").style.display = (v=="lain")?"block":"none";
}

function tambahAssistant(){
    let c = document.getElementById("assistant_container");
    let div = document.createElement("div");
    div.innerHTML = `
    <select name="assistant[]">

        <option value="">-- Pilih Karyawan --</option>

        <?php
        mysqli_data_seek($karyawan, 0);
        while($kar = mysqli_fetch_assoc($karyawan)):
        ?>

        <option value="<?= $kar['nama_karyawan'] ?>">
            <?= $kar['nama_karyawan'] ?>
        </option>

        <?php endwhile; ?>

    </select>

    <button type="button" onclick="hapusAssistant(this)">-</button>
`;
    c.appendChild(div);
}

function hapusAssistant(btn){
    btn.parentNode.remove();
}

function tambahBaris(k){
    let table = document.getElementById(k+"_table");
    let row = table.insertRow();
    let no = table.rows.length - 1;

    row.innerHTML = `
    <td>${no}</td>
    <td><input name="${k}_deskripsi[]"></td>
    <td><input type="number" name="${k}_qty[]" oninput="hitung(this)"></td>
    <td><input type="number" name="${k}_hari[]" oninput="hitung(this)"></td>
    <td><input name="${k}_nominal[]" oninput="formatNominal(this);hitung(this)"></td>
    <td class="total" data-value="0">Rp 0</td>
    <td><button type="button" onclick="hapusBaris(this,'${k}')">-</button></td>
    `;
}

function hapusBaris(btn,k){
    btn.closest("tr").remove();
    hitungSubtotal(k);
}

function formatNominal(el){
    let val = el.value.replace(/[^0-9]/g,'');
    el.value = "Rp " + val.replace(/\B(?=(\d{3})+(?!\d))/g,".");
}

function hitung(el){
    let row = el.closest("tr");

    let qty = parseInt(row.cells[2].querySelector("input").value) || 0;
    let hari = parseInt(row.cells[3].querySelector("input").value) || 0;

    let nominalText = row.cells[4].querySelector("input").value;
    let nominal = parseInt(nominalText.replace(/[^0-9]/g,'')) || 0;

    let total = qty * hari * nominal;

    let totalCell = row.querySelector(".total");
    totalCell.setAttribute("data-value", total);
    totalCell.innerText = "Rp " + formatAngka(total);

    let k = row.closest("table").id.replace("_table","");
    hitungSubtotal(k);
}

function hitungSubtotal(k){
    let table = document.getElementById(k+"_table");
    let subtotal = 0;

    let totalCells = table.querySelectorAll(".total");

    totalCells.forEach(cell => {
        let val = parseInt(cell.getAttribute("data-value")) || 0;
        subtotal += val;
    });

    document.getElementById("subtotal_"+k).innerText = formatAngka(subtotal);
    hitungTotalAnggaran();
}

function hitungTotalAnggaran(){
    let kategoriList = [
    "Akomodasi",
    "Transportasi",
    "Konsumsi",
    "Lain-lain"
];

    let total = 0;

    kategoriList.forEach(k => {
        let el = document.getElementById("subtotal_"+k);
        if(el){
            let angka = el.innerText.replace(/[^0-9]/g,'');
            total += parseInt(angka || 0);
        }
    });

    document.getElementById("total_anggaran").innerText = formatAngka(total);
}

function formatAngka(angka){
    return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g,".");
}
</script>

</body>
</html>