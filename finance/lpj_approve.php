<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'finance') {
    header("Location: ../auth/login.php");
    exit;
}

if (!isset($_GET['id'])) {
    die("ID tidak ditemukan");
}

$lpj_id = $_GET['id'];

// =======================
// UPDATE STATUS
// =======================
mysqli_query($conn, "
UPDATE lpj 
SET 
    status='Approved',
    approved_by='".$_SESSION['nama_lengkap']."',
    approved_at=NOW()
WHERE id='$lpj_id'
");

// =======================
// AMBIL DATA LPJ
// =======================
$lpj = mysqli_fetch_assoc(mysqli_query($conn, "
SELECT * FROM lpj WHERE id='$lpj_id'
"));

$tanggal_lpj = $lpj['tanggal'];
$ref         = $lpj['no_lpj'];

// =======================
// CEK SUDAH PERNAH JURNAL
// =======================
$cek = mysqli_fetch_assoc(mysqli_query($conn, "
SELECT COUNT(*) as total 
FROM jurnal_umum 
WHERE ref_no = '$ref'
"));

if($cek['total'] > 0){
    header("Location: lpj_review.php");
    exit;
}

// =======================
// AKUN
// =======================
$akun_cash_advance = 3;
$akun_cash_bank    = 2;
$akun_reimburse    = 4;

// =======================
$total_realisasi = 0;
$tanggal_terakhir = $tanggal_lpj;

// =======================
// TANGGAL APPROVE
// =======================
$tanggal_jurnal = $tanggal_lpj;

// =======================
// SUBTOTAL PER KATEGORI
// =======================
$kategori_total = [];

$q = mysqli_query($conn, "
SELECT *
FROM lpj_detail
WHERE lpj_id='$lpj_id'
");

while($d = mysqli_fetch_assoc($q)){

    $kategori = $d['kategori'];
    $nominal  = $d['nominal'];

    if(!isset($kategori_total[$kategori])){
        $kategori_total[$kategori] = 0;
    }

    $kategori_total[$kategori] += $nominal;
}

// =======================
// INSERT JURNAL BEBAN
// =======================
$total_realisasi = 0;

foreach($kategori_total as $kategori => $subtotal){

    // mapping akun
    if($kategori == 'Akomodasi'){
        $akun_id = 5;

    } elseif($kategori == 'Transportasi'){
        $akun_id = 6;

    } elseif($kategori == 'Konsumsi'){
        $akun_id = 7;

    } elseif($kategori == 'Lain-lain'){
        $akun_id = 8;

    } else {
        $akun_id = 0;
    }

    // insert debit expense
    if($akun_id){

        mysqli_query($conn, "
        INSERT INTO jurnal_umum
        (
            tanggal,
            ref_no,
            akun_id,
            debit,
            kredit,
            keterangan
        )

        VALUES
        (
            '$tanggal_jurnal',
            '$ref',
            '$akun_id',
            '$subtotal',
            0,
            'Realisasi LPJ'
        )
        ");
    }

    $total_realisasi += $subtotal;
}

// =======================
// AMBIL TOTAL PENCAIRAN
// =======================
$p = mysqli_fetch_assoc(mysqli_query($conn, "
SELECT total_dibayar 
FROM pencairan 
WHERE rab_id = '".$lpj['rab_id']."'
ORDER BY id DESC LIMIT 1
"));

$total_pencairan = $p['total_dibayar'] ?? 0;

// fallback
if($total_pencairan == 0){
    $total_pencairan = $total_realisasi;
}

// =======================
// KREDIT CASH ADVANCE
// =======================
mysqli_query($conn, "
INSERT INTO jurnal_umum 
(tanggal, ref_no, akun_id, debit, kredit, keterangan)
VALUES
('$tanggal_terakhir','$ref','$akun_cash_advance',0,'$total_pencairan','Realisasi LPJ')
");

// =======================
// HITUNG SELISIH (FIX)
// =======================
$selisih = $total_realisasi - $total_pencairan;

// =======================
// DEFISIT
// =======================
if($selisih > 0){

    mysqli_query($conn, "
    INSERT INTO jurnal_umum 
    (tanggal, ref_no, akun_id, debit, kredit, keterangan)
    VALUES
    ('$tanggal_terakhir','$ref','$akun_reimburse',0,'$selisih','Defisit LPJ')
    ");
}

// =======================
// SURPLUS
// =======================
elseif($selisih < 0){

    $selisih = abs($selisih);

    mysqli_query($conn, "
    INSERT INTO jurnal_umum 
    (tanggal, ref_no, akun_id, debit, kredit, keterangan)
    VALUES
    ('$tanggal_terakhir','$ref',9,'$selisih',0,'Surplus LPJ')
    ");
}

// =======================
header("Location: lpj_review.php");
exit;