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

$id = $_GET['id'];

$r = mysqli_query($conn, "SELECT * FROM rab WHERE id='$id'");
$data = mysqli_fetch_assoc($r);

$detail = mysqli_query($conn, "SELECT * FROM rab_detail WHERE rab_id='$id'");
$as = mysqli_query($conn, "SELECT * FROM rab_assistant WHERE rab_id='$id'");
?>

<!DOCTYPE html>
<html>
<head>
<title>Edit RAB</title>

<style>

/* ===== GLOBAL ===== */
body {
    font-family: Arial;
    background:#f4f4f4;
    padding:20px;
}

/* ===== CONTAINER ===== */
.container {
    max-width:1000px;
    margin:auto;
    background:#fff;
    padding:30px;
    border-radius:12px;
}

/* ===== TITLE ===== */
h2 {
    text-align:center;
    margin-bottom:5px;
}

.nomor {
    text-align:center;
    margin-bottom:30px;
    color:#555;
}

/* ===== SECTION ===== */
.section {
    margin-bottom:30px;
}

.section h3 {
    margin-bottom:15px;
}

/* ===== FORM STYLE ===== */
.form-view {
    display:flex;
    flex-direction:column;
    gap:12px;
}

.row {
    display:flex;
    align-items:center;
}

.label {
    width:220px;
}

.colon {
    width:10px;
}

.value {
    flex:1;
}

input, select, textarea {
    padding:6px;
    border:1px solid #ccc;
    border-radius:5px;
}

/* ===== TABLE ===== */
.tabel {
    border-collapse:collapse;
    width:100%;
    margin-top:10px;
}

.tabel th {
    background:#ffc107;
}

.tabel th, .tabel td {
    border:1px solid #ccc;
    padding:8px;
    text-align:center;
}

/* kecilkan qty & hari */
.tabel th:nth-child(3),
.tabel td:nth-child(3),
.tabel th:nth-child(4),
.tabel td:nth-child(4){
    width:80px;
}

/* ===== BUTTON ===== */
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

/* ===== TEXTAREA ===== */
textarea {
    width:100%;
    min-height:80px;
}

.req { color:red; }

</style>

</head>

<body>

<div class="container">

<h2>Rencana Anggaran Biaya</h2>
<div class="nomor">Nomor : <?= $data['no_rab'] ?></div>

<form method="POST" action="rab_update.php">
<input type="hidden" name="id" value="<?= $data['id'] ?>">
<input type="hidden" name="no_rab" value="<?= $data['no_rab'] ?>">

<!-- ================= INFORMASI ================= -->
<div class="section">
<h3>Informasi Penugasan</h3>

<div class="form-view">

<div class="row">
    <div class="label">Nama Klien <span class="req">*</span></div>
    <div class="colon">:</div>
    <div class="value">
        <select name="nama_klien" required>

<option value="">-- Pilih Klien --</option>

<?php while($k = mysqli_fetch_assoc($klien)): ?>

<option
value="<?= $k['nama_klien'] ?>"

<?= ($data['nama_klien']==$k['nama_klien']) ? 'selected' : '' ?>>

<?= $k['nama_klien'] ?>

</option>

<?php endwhile; ?>

</select>
    </div>
</div>

<div class="row">
    <div class="label">Jenis Penugasan <span class="req">*</span></div>
    <div class="colon">:</div>
    <div class="value">
        <select name="jenis_penugasan" required>

<option value="">-- Pilih Penugasan --</option>

<?php while($p = mysqli_fetch_assoc($penugasan)): ?>

<option
value="<?= $p['jenis_penugasan'] ?>"

<?= ($data['jenis_penugasan']==$p['jenis_penugasan']) ? 'selected' : '' ?>>

<?= $p['jenis_penugasan'] ?>

</option>

<?php endwhile; ?>

</select>
        <br>
        <input type="text" name="jenis_lainnya" placeholder="Isi sendiri..." style="display:none;">
    </div>
</div>

<div class="row">
    <div class="label">Tahun Buku</div>
    <div class="colon">:</div>
    <div class="value">
        <input type="date" name="tahun_buku" value="<?= $data['tahun_buku'] ?>">
    </div>
</div>

<div class="row">
    <div class="label">Periode Penugasan</div>
    <div class="colon">:</div>
    <div class="value">
        <input type="date" name="periode_awal" value="<?= $data['periode_awal'] ?>">
        s.d
        <input type="date" name="periode_akhir" value="<?= $data['periode_akhir'] ?>">
    </div>
</div>

</div>
</div>

<!-- ================= TIM ================= -->
<div class="section">
<h3>Susunan Tim Audit</h3>

<div class="form-view">

<?php
$tim = [
    "Signing Partner" => "signing_partner",
    "Partner Review" => "partner_review",
    "Manager In-Charge" => "manager_ic",
    "Auditor In-Charge" => "auditor_ic"
];

foreach($tim as $label => $name){
?>

<div class="row">

    <div class="label"><?= $label ?></div>
    <div class="colon">:</div>

    <div class="value">

        <select name="<?= $name ?>">

            <option value="">-- Pilih --</option>

            <?php
            mysqli_data_seek($karyawan, 0);

            while($kar = mysqli_fetch_assoc($karyawan)):
            ?>

            <option
            value="<?= $kar['nama_karyawan'] ?>"

            <?= ($data[$name] == $kar['nama_karyawan']) ? 'selected' : '' ?>>

            <?= $kar['nama_karyawan'] ?>

            </option>

            <?php endwhile; ?>

        </select>

    </div>

</div>

<?php } ?>

<div class="row">
    <div class="label">Assistant</div>
    <div class="colon">:</div>

    <div class="value" id="assistant_container">

        <?php while($a = mysqli_fetch_assoc($as)){ ?>

        <div class="assistant-row">

            <select name="assistant[]">

                <option value="">-- Pilih Assistant --</option>

                <?php
                mysqli_data_seek($karyawan, 0);

                while($kar = mysqli_fetch_assoc($karyawan)):
                ?>

                <option
                value="<?= $kar['nama_karyawan'] ?>"

                <?= ($a['nama'] == $kar['nama_karyawan']) ? 'selected' : '' ?>>

                <?= $kar['nama_karyawan'] ?>

                </option>

                <?php endwhile; ?>

            </select>

            <button type="button"
            onclick="hapusAssistant(this)">-</button>

        </div>

        <?php } ?>

        <!-- BARIS KOSONG -->
        <div class="assistant-row">

            <select name="assistant[]">

                <option value="">-- Pilih Assistant --</option>

                <?php
                mysqli_data_seek($karyawan, 0);

                while($kar = mysqli_fetch_assoc($karyawan)):
                ?>

                <option
                value="<?= $kar['nama_karyawan'] ?>">

                <?= $kar['nama_karyawan'] ?>

                </option>

                <?php endwhile; ?>

            </select>

            <button type="button"
            onclick="hapusAssistant(this)">-</button>

        </div>

        <br>

        <button type="button"
        onclick="tambahAssistant()">

        + Baris Assistant

        </button>

    </div>
</div>

</div>
</div>

<!-- ================= INPUT RAB ================= -->
<div class="section">
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

<?php
$no = 1;
mysqli_data_seek($detail, 0);

while($row = mysqli_fetch_assoc($detail)){
    if($row['kategori'] != $k) continue;
?>

<tr>
<td><?= $no++ ?></td>
<td><input name="<?= $k ?>_deskripsi[]" value="<?= $row['deskripsi'] ?>"></td>
<td><input type="number" name="<?= $k ?>_qty[]" value="<?= $row['qty'] ?>" oninput="hitung(this)"></td>
<td><input type="number" name="<?= $k ?>_hari[]" value="<?= $row['hari'] ?>" oninput="hitung(this)"></td>
<td><input name="<?= $k ?>_nominal[]" value="Rp <?= number_format($row['nominal'],0,',','.') ?>" oninput="formatNominal(this);hitung(this)"></td>
<td class="total" data-value="<?= $row['total'] ?>">Rp <?= number_format($row['total'],0,',','.') ?></td>
<td><button type="button" onclick="hapusBaris(this,'<?= $k ?>')">-</button></td>
</tr>

<?php } ?>

</table>

<button type="button" onclick="tambahBaris('<?= $k ?>')">+ Tambah Baris</button>
<p>Subtotal: Rp <span id="subtotal_<?= $k ?>">0</span></p>

<?php } ?>

</div>

<!-- ================= TOTAL ================= -->
<div class="section">
<h3>Total Anggaran</h3>
<p style="font-size:18px; font-weight:bold;">
Rp <span id="total_anggaran">0</span>
</p>
</div>

<input type="submit" value="Update & Submit Ulang">

</form>

</div>

<!-- ================= JS (TIDAK DIUBAH) ================= -->
<script>
// JS ANDA TETAP (TIDAK DIUBAH SAMA SEKALI)
function tambahAssistant(){

    let container =
    document.getElementById('assistant_container');

    let div = document.createElement('div');

    div.classList.add('assistant-row');

    div.innerHTML = `

        <select name="assistant[]">

            <option value="">-- Pilih Assistant --</option>

            <?php
            mysqli_data_seek($karyawan, 0);

            while($kar = mysqli_fetch_assoc($karyawan)):
            ?>

            <option
            value="<?= $kar['nama_karyawan'] ?>">

            <?= $kar['nama_karyawan'] ?>

            </option>

            <?php endwhile; ?>

        </select>

        <button type="button"
        onclick="hapusAssistant(this)">-</button>

    `;

    container.appendChild(div);
}
function hapusAssistant(btn){ btn.parentNode.remove(); }

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
function hapusBaris(btn,k){ btn.closest("tr").remove(); hitungSubtotal(k); }

function formatNominal(el){
    let val = el.value.replace(/[^0-9]/g,'');
    el.value = "Rp " + val.replace(/\B(?=(\d{3})+(?!\d))/g,".");
}
function hitung(el){
    let row = el.closest("tr");
    let qty = parseInt(row.cells[2].querySelector("input").value)||0;
    let hari = parseInt(row.cells[3].querySelector("input").value)||0;
    let nominal = parseInt(row.cells[4].querySelector("input").value.replace(/[^0-9]/g,''))||0;
    let total = qty*hari*nominal;
    let totalCell = row.querySelector(".total");
    totalCell.setAttribute("data-value", total);
    totalCell.innerText = "Rp "+formatAngka(total);
    let k = row.closest("table").id.replace("_table","");
    hitungSubtotal(k);
}
function hitungSubtotal(k){
    let table = document.getElementById(k+"_table");
    let subtotal=0;
    table.querySelectorAll(".total").forEach(cell=>{
        subtotal += parseInt(cell.getAttribute("data-value"))||0;
    });
    document.getElementById("subtotal_"+k).innerText = formatAngka(subtotal);
    hitungTotalAnggaran();
}
function hitungTotalAnggaran(){
    let kategoriList=["Akomodasi","Transportasi","Konsumsi","Lain-lain"];
    let total=0;
    kategoriList.forEach(k=>{
        let el=document.getElementById("subtotal_"+k);
        if(el){
            total+=parseInt(el.innerText.replace(/[^0-9]/g,''))||0;
        }
    });
    document.getElementById("total_anggaran").innerText = formatAngka(total);
}
function formatAngka(angka){
    return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g,".");
}
window.onload=function(){
    ["Akomodasi","Transportasi","Konsumsi","Lain-lain"].forEach(k=>hitungSubtotal(k));
    hitungTotalAnggaran();
};
</script>

</body>
</html>