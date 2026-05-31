<?php
session_start();
include "../config/db.php";

// =====================
// ERROR REPORTING
// =====================
error_reporting(E_ALL);
ini_set('display_errors', 1);

// =====================
// SESSION
// =====================
$created_by = $_SESSION['id'] ?? 1;

// =====================
// AMBIL DATA FORM
// =====================
$no_rab = $_POST['no_rab'] ?? '';
$nama_klien = $_POST['nama_klien'] ?? '';
$jenis_penugasan = $_POST['jenis_penugasan'] ?? '';
$tahun_buku = $_POST['tahun_buku'] ?? null;
$periode_awal = $_POST['periode_awal'] ?? '';
$periode_akhir = $_POST['periode_akhir'] ?? '';

$signing_partner = $_POST['signing_partner'] ?? '';
$partner_review = $_POST['partner_review'] ?? '';
$manager_ic = $_POST['manager_ic'] ?? '';
$auditor_ic = $_POST['auditor_ic'] ?? '';

$assistant = $_POST['assistant'] ?? [];

if($jenis_penugasan == "lain"){
    $jenis_penugasan = $_POST['jenis_lainnya'] ?? '';
}

// =====================
// VALIDASI MINIMAL
// =====================
if(empty($no_rab) || empty($nama_klien)){
    die("❌ Data tidak lengkap!");
}

// =====================
// KATEGORI
// =====================
$kategori_list = ['Akomodasi','Transportasi','Konsumsi','Lain-lain'];

$total_anggaran = 0;

// =====================
// INSERT RAB (FIXED)
// =====================
$query = "INSERT INTO rab 
(no_rab, nama_klien, jenis_penugasan, tahun_buku, periode_awal, periode_akhir,
signing_partner, partner_review, manager_ic, auditor_ic,
total_anggaran, status, pencairan, created_by,
submitted_by, submitted_at) 
VALUES 
('$no_rab','$nama_klien','$jenis_penugasan','$tahun_buku','$periode_awal','$periode_akhir',
'$signing_partner','$partner_review','$manager_ic','$auditor_ic',
'0','Submitted','Pending','$created_by',
'".$_SESSION['nama_lengkap']."', NOW())";

$result = mysqli_query($conn, $query);

if(!$result){
    die("❌ ERROR INSERT RAB: " . mysqli_error($conn));
}

// ambil id rab
$rab_id = mysqli_insert_id($conn);

// =====================
// INSERT ASSISTANT
// =====================
foreach($assistant as $a){
    if(trim($a) != ''){
        mysqli_query($conn, "
            INSERT INTO rab_assistant (rab_id, nama)
            VALUES ('$rab_id', '$a')
        ");
    }
}

// =====================
// LOOP DETAIL RAB
// =====================
foreach($kategori_list as $kategori){

    $deskripsi = $_POST[$kategori.'_deskripsi'] ?? [];

    if(empty($deskripsi)) continue;

    $qty = $_POST[$kategori.'_qty'] ?? [];
    $hari = $_POST[$kategori.'_hari'] ?? [];
    $nominal = $_POST[$kategori.'_nominal'] ?? [];

    for($i=0; $i<count($deskripsi); $i++){

        if(trim($deskripsi[$i]) == '') continue;

        $q = intval($qty[$i] ?? 0);
        $h = intval($hari[$i] ?? 0);

        $n = preg_replace('/[^0-9]/', '', $nominal[$i] ?? 0);
        $n = intval($n);

        $total = $q * $h * $n;

        $total_anggaran += $total;

        $insert_detail = mysqli_query($conn, "
            INSERT INTO rab_detail 
            (rab_id, kategori, deskripsi, qty, hari, nominal, total)
            VALUES
            ('$rab_id', '$kategori', '{$deskripsi[$i]}', '$q', '$h', '$n', '$total')
        ");

        if(!$insert_detail){
            die("❌ ERROR INSERT DETAIL: " . mysqli_error($conn));
        }
    }
}

// =====================
// VALIDASI MINIMAL BIAYA
// =====================
if($total_anggaran == 0){
    die("❌ Minimal isi 1 biaya!");
}

// =====================
// UPDATE TOTAL
// =====================
$update = mysqli_query($conn, "
    UPDATE rab 
    SET total_anggaran = '$total_anggaran'
    WHERE id = '$rab_id'
");

if(!$update){
    die("❌ ERROR UPDATE TOTAL: " . mysqli_error($conn));
}

// =====================
// REDIRECT
// =====================
header("Location: rab_riwayat.php?success=1");
exit;