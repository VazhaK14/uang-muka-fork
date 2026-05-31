<?php
session_start();
include "../config/db.php";

$rab_id        = $_POST['rab_id'];
$no_pencairan  = $_POST['no_pencairan'];
$tanggal       = date('Y-m-d');
$total         = $_POST['total'];

// =======================
// TAMBAHAN PENERIMA
// =======================
$penerima = $_POST['penerima'];
$bank     = $_POST['bank'];
$rekening = $_POST['rekening'];

// =======================
// SIMPAN PENCAIRAN (FLOW ASLI)
// =======================
mysqli_query($conn, "
INSERT INTO pencairan
(
    rab_id,
    no_pencairan,
    tanggal,
    total_dibayar,

    -- 🔥 tambahan baru
    penerima,
    bank,
    rekening,

    approved_by,
    approved_at
)
VALUES
(
    '$rab_id',
    '$no_pencairan',
    '$tanggal',
    '$total',

    -- 🔥 tambahan baru
    '$penerima',
    '$bank',
    '$rekening',

    '".$_SESSION['nama_lengkap']."',
    NOW()
)
") or die(mysqli_error($conn));

// ambil ID pencairan terakhir
$pencairan_id = mysqli_insert_id($conn);

// =======================
// UPDATE STATUS RAB (FLOW ASLI)
// =======================
mysqli_query($conn, "
UPDATE rab 
SET pencairan='Paid'
WHERE id='$rab_id'
") or die(mysqli_error($conn));

// =======================
// AUTO JURNAL PENCAIRAN (FIX)
// =======================

// mapping akun (sesuaikan dengan DB lo)
$akun_cash_bank     = 2; // Cash/Bank
$akun_cash_advance  = 3; // Cash Advance

$ref = $no_pencairan;

// =======================
// 1️⃣ DEBIT → Cash Advance
// =======================
mysqli_query($conn, "
INSERT INTO jurnal_umum 
(
    tanggal,
    ref_no,
    akun_id,
    debit,
    kredit,
    keterangan,
    pencairan_id
)
VALUES
(
    '$tanggal',
    '$ref',
    '$akun_cash_advance',
    '$total',
    0,
    'Pencairan Dana',
    '$pencairan_id'
)
") or die(mysqli_error($conn));

// =======================
// 2️⃣ KREDIT → Cash / Bank
// =======================
mysqli_query($conn, "
INSERT INTO jurnal_umum 
(
    tanggal,
    ref_no,
    akun_id,
    debit,
    kredit,
    keterangan,
    pencairan_id
)
VALUES
(
    '$tanggal',
    '$ref',
    '$akun_cash_bank',
    0,
    '$total',
    'Pencairan Dana',
    '$pencairan_id'
)
") or die(mysqli_error($conn));

// =======================
header("Location: pencairan.php");
exit;
?>