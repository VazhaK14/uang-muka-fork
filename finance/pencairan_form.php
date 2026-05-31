<?php
session_start();
include "../config/db.php";

$id = $_GET['id'];

// ambil data RAB
$q = mysqli_query($conn, "SELECT * FROM rab WHERE id='$id'");
$r = mysqli_fetch_assoc($q);

// AUTO NOMOR
$bulan = date('m');
$tahun = date('Y');

function romawi($bln){
    $r=[1=>'I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'];
    return $r[(int)$bln];
}

$q_no = mysqli_query($conn, "
    SELECT MAX(no_pencairan) as max_no 
    FROM pencairan 
    WHERE MONTH(created_at)='$bulan'
    AND YEAR(created_at)='$tahun'
");

$d_no = mysqli_fetch_assoc($q_no);

$urut = 1;
if($d_no['max_no']){
    $pecah = explode('/', $d_no['max_no']);
    $urut = (int)$pecah[2] + 1;
}

$urut = str_pad($urut, 3, '0', STR_PAD_LEFT);
$no = "PAY/KAP-PQR/$urut/".romawi($bulan)."/$tahun";

// =======================
// DATA KARYAWAN
// =======================
$q_karyawan = mysqli_query($conn, "
    SELECT * FROM karyawan
    ORDER BY nama_karyawan ASC
");
?>

<!DOCTYPE html>
<html>
<head>
<title>Form Pencairan</title>

<style>
body {
    font-family: Arial;
    background:#f4f4f4;
    padding:20px;
}

.container {
    max-width:1000px;
    margin:auto;
    background:#fff;
    padding:30px;
    border-radius:12px;
}

.center { text-align:center; }

.section { margin-top:25px; }

/* FORM */
.form-view .row {
    display:flex;
    margin-bottom:8px;
    align-items:center;
}

.label { width:200px; }
.colon { width:10px; }
.value { flex:1; }

/* INPUT */
select,
input[type=text]{
    width:100%;
    padding:8px;
    border:1px solid #ccc;
    border-radius:5px;
    box-sizing:border-box;
}

/* TABLE */
table {
    border-collapse: collapse;
    width:100%;
    margin-top:10px;
    margin-bottom:15px;
}

th {
    background:#ffc107;
}

th, td {
    border:1px solid #ddd;
    padding:8px;
    text-align:center;
}

tr:nth-child(even){
    background:#fafafa;
}

.subtotal td {
    font-weight:bold;
    background:#f9f9f9;
}

.subtotal td:last-child {
    text-align:right;
}

/* TOTAL */
.total-box {
    text-align:right;
    font-size:18px;
    font-weight:bold;
    margin-top:10px;
}

button {
    padding:10px 18px;
    border:none;
    background:#ffc107;
    border-radius:5px;
    cursor:pointer;
    font-weight:bold;
}

button:hover {
    background:#e0a800;
}

h4 {
    margin-bottom:10px;
}

hr{
    border:0;
    border-top:2px dashed #ccc;
    margin:30px 0;
}
</style>

</head>

<body>

<div class="container">

<h3 class="center">FORMULIR PENCAIRAN DANA</h3>
<p class="center">Nomor : <?= $no ?></p>

<form method="POST" action="pencairan_proses.php">

<input type="hidden" name="rab_id" value="<?= $id ?>">
<input type="hidden" name="no_pencairan" value="<?= $no ?>">

<!-- ================= INFO ================= -->
<div class="section form-view">

<div class="row">
    <div class="label">Tanggal Cetak</div>
    <div class="colon">:</div>
    <div class="value"><?= date('d/m/Y') ?></div>
</div>

<div class="row">
    <div class="label">Nomor RAB</div>
    <div class="colon">:</div>
    <div class="value"><?= $r['no_rab'] ?></div>
</div>

<div class="row">
    <div class="label">Nama Klien</div>
    <div class="colon">:</div>
    <div class="value"><?= $r['nama_klien'] ?></div>
</div>

<div class="row">
    <div class="label">Jenis Penugasan</div>
    <div class="colon">:</div>
    <div class="value"><?= $r['jenis_penugasan'] ?></div>
</div>

<div class="row">
    <div class="label">Periode Penugasan</div>
    <div class="colon">:</div>
    <div class="value">
        <?= date('d/m/Y',strtotime($r['periode_awal'])) ?> 
        s.d 
        <?= date('d/m/Y',strtotime($r['periode_akhir'])) ?>
    </div>
</div>

</div>

<!-- ================= TABEL RAB ================= -->
<?php
$detail = mysqli_query($conn, "
    SELECT * FROM rab_detail 
    WHERE rab_id='$id'
    ORDER BY FIELD(kategori,
        'Akomodasi',
        'Transportasi',
        'Konsumsi',
        'Lain-lain'
    )
");

$current_kategori = "";
$subtotal = 0;
$no = 1;

while($row = mysqli_fetch_assoc($detail)){

    if($current_kategori != $row['kategori']){

        if($current_kategori != ""){
            echo "<tr class='subtotal'>
                    <td colspan='5'>Subtotal</td>
                    <td>Rp ".number_format($subtotal,0,',','.')."</td>
                  </tr>";
            echo "</table></div>";
        }

        $current_kategori = $row['kategori'];
        $subtotal = 0;

        echo "<div class='section'>";
        echo "<h4>$current_kategori</h4>";

        echo "<table>
        <tr>
        <th>No</th>
        <th>Deskripsi</th>
        <th>Qty</th>
        <th>Hari</th>
        <th>Nominal</th>
        <th>Total</th>
        </tr>";

        $no = 1;
    }

    $subtotal += $row['total'];

    echo "<tr>
        <td>$no</td>
        <td>{$row['deskripsi']}</td>
        <td>{$row['qty']}</td>
        <td>{$row['hari']}</td>
        <td>Rp ".number_format($row['nominal'],0,',','.')."</td>
        <td>Rp ".number_format($row['total'],0,',','.')."</td>
    </tr>";

    $no++;
}

if($current_kategori != ""){
    echo "<tr class='subtotal'>
            <td colspan='5'>Subtotal</td>
            <td>Rp ".number_format($subtotal,0,',','.')."</td>
          </tr>";
    echo "</table></div>";
}
?>

<!-- ================= TOTAL ================= -->
<div class="total-box">
Total Dibayar : Rp <?= number_format($r['total_anggaran'],0,',','.') ?>
</div>

<input type="hidden" name="total" value="<?= $r['total_anggaran'] ?>">

<!-- ================= INFO PENERIMA ================= -->
<hr>

<h4>Informasi Penerimaan Cash Advance</h4>

<div class="section form-view">

<div class="row">
    <div class="label">Nama Penerima</div>
    <div class="colon">:</div>
    <div class="value">

        <select 
            name="penerima_id"
            id="penerimaSelect"
            required
            onchange="setPenerima(this)"
        >

        <option value="">-- Pilih Karyawan --</option>

        <?php while($k = mysqli_fetch_assoc($q_karyawan)){ ?>

        <option
            value="<?= $k['id'] ?>"
            data-bank="<?= $k['bank'] ?>"
            data-rekening="<?= $k['rekening'] ?>"
            data-nama="<?= $k['nama_karyawan'] ?>"
        >
            <?= $k['nama_karyawan'] ?>
        </option>

        <?php } ?>

        </select>

    </div>
</div>

<div class="row">
    <div class="label">Bank</div>
    <div class="colon">:</div>
    <div class="value">
        <input type="text" name="bank" id="bank" readonly>
    </div>
</div>

<div class="row">
    <div class="label">No Rekening</div>
    <div class="colon">:</div>
    <div class="value">
        <input type="text" name="rekening" id="rekening" readonly>
    </div>
</div>

<input type="hidden" name="penerima" id="penerimaNama">

</div>

<hr>

<p>*Formulir ini dihasilkan otomatis oleh sistem sebagai bukti penerimaan dana yang sah</p>

<br><br>

<button type="submit">Lakukan Pencairan Dana</button>

</form>

</div>

<script>
function setPenerima(select){

    let selected = select.options[select.selectedIndex];

    document.getElementById("bank").value =
        selected.getAttribute("data-bank");

    document.getElementById("rekening").value =
        selected.getAttribute("data-rekening");

    document.getElementById("penerimaNama").value =
        selected.getAttribute("data-nama");
}
</script>

</body>
</html>