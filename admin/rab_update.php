<?php
session_start();
include "../config/db.php";

// =====================
// AMBIL DATA
// =====================
$id = $_POST['id'];

$nama_klien = $_POST['nama_klien'];
$jenis_penugasan = $_POST['jenis_penugasan'];
$tahun_buku = $_POST['tahun_buku'];
$periode_awal = $_POST['periode_awal'];
$periode_akhir = $_POST['periode_akhir'];
$catatan = $_POST['catatan'] ?? '';

$signing_partner = $_POST['signing_partner'] ?? '';
$partner_review = $_POST['partner_review'] ?? '';
$manager_ic = $_POST['manager_ic'] ?? '';
$auditor_ic = $_POST['auditor_ic'] ?? '';

$assistant = $_POST['assistant'] ?? [];

// handle "lain"
if($jenis_penugasan == "lain"){
    $jenis_penugasan = $_POST['jenis_lainnya'] ?? '';
}

// =====================
// KATEGORI (SUDAH FIX)
// =====================
$kategori_list = ['Akomodasi','Transportasi','Konsumsi','Lain-lain'];

$total_anggaran = 0;

// =====================
// HAPUS DETAIL LAMA
// =====================
mysqli_query($conn, "DELETE FROM rab_detail WHERE rab_id='$id'");

// =====================
// HAPUS ASSISTANT LAMA
// =====================
mysqli_query($conn, "DELETE FROM rab_assistant WHERE rab_id='$id'");

// =====================
// INSERT ASSISTANT BARU
// =====================
foreach($assistant as $a){
    if(trim($a) != ''){
        mysqli_query($conn, "
            INSERT INTO rab_assistant (rab_id, nama)
            VALUES ('$id', '$a')
        ");
    }
}

// =====================
// LOOP DETAIL BARU
// =====================
foreach($kategori_list as $kategori){

    $deskripsi = $_POST[$kategori.'_deskripsi'] ?? [];
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

        mysqli_query($conn, "
            INSERT INTO rab_detail 
            (rab_id, kategori, deskripsi, qty, hari, nominal, total)
            VALUES
            ('$id','$kategori','{$deskripsi[$i]}','$q','$h','$n','$total')
        ");
    }
}

// =====================
// VALIDASI MINIMAL BIAYA
// =====================
if($total_anggaran == 0){
    die("❌ Minimal isi 1 biaya!");
}

// =====================
// UPDATE HEADER (FULL FIX)
// =====================
mysqli_query($conn, "
UPDATE rab SET 
nama_klien='$nama_klien',
jenis_penugasan='$jenis_penugasan',
tahun_buku='$tahun_buku',
periode_awal='$periode_awal',
periode_akhir='$periode_akhir',

submitted_by='".$_SESSION['nama_lengkap']."',
submitted_at=NOW(),
approved_by=NULL,
approved_at=NULL,

signing_partner='$signing_partner',
partner_review='$partner_review',
manager_ic='$manager_ic',
auditor_ic='$auditor_ic',
total_anggaran='$total_anggaran',
status='Submitted',

rejected_note=NULL,
rejected_by=NULL,
rejected_at=NULL

WHERE id='$id'
");

// =====================
// REDIRECT
// =====================
header("Location: rab_riwayat.php?update=1");
exit;