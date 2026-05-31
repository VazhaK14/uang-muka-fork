<?php
session_start();
include "../config/db.php";

$lpj_id = $_POST['lpj_id'];

// =======================
$upload_dir = "../uploads/";

if(!is_dir($upload_dir)){
    mkdir($upload_dir, 0777, true);
}

// =======================
// HANDLE PEMASUKAN
// =======================

$old_bukti = $_POST['old_bukti_pemasukan'] ?? null;
$bukti_pemasukan = $old_bukti;

if(isset($_FILES['bukti_pemasukan']) && $_FILES['bukti_pemasukan']['name']){

    $file = $_FILES['bukti_pemasukan'];

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if(in_array($ext, ['jpg','jpeg','png']) && $file['size'] <= 2000000){

        $nama = uniqid()."_pemasukan.".$ext;

        if(move_uploaded_file($file['tmp_name'], $upload_dir.$nama)){

            $bukti_pemasukan = $nama;

            // hapus file lama
            if($old_bukti && file_exists($upload_dir.$old_bukti)){
                unlink($upload_dir.$old_bukti);
            }

        }
    }
}

// =======================
// HAPUS DETAIL LAMA
// =======================

mysqli_query($conn, "DELETE FROM lpj_detail WHERE lpj_id='$lpj_id'");

$total = 0;

// =======================
// AUTO CROSS REF
// =======================

$no_bukti = 1;

// =======================

foreach($_POST['nominal'] as $kategori => $data){

    for($i=0; $i<count($data); $i++){

        $tanggal = $_POST['tanggal'][$kategori][$i] ?? null;
        $deskripsi = $_POST['deskripsi'][$kategori][$i] ?? '';

        // =======================
        // BUKTI LAMA
        // =======================

        $bukti_lama = $_POST['bukti_lama'][$kategori][$i] ?? null;
        $bukti = $bukti_lama;

        // =======================
        // HANDLE FILE BARU
        // =======================

        if(
            isset($_FILES['bukti']['name'][$kategori][$i]) &&
            $_FILES['bukti']['name'][$kategori][$i] != ''
        ){

            $file_name = $_FILES['bukti']['name'][$kategori][$i];
            $tmp = $_FILES['bukti']['tmp_name'][$kategori][$i];
            $size = $_FILES['bukti']['size'][$kategori][$i];

            $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            if(in_array($ext, ['jpg','jpeg','png']) && $size <= 2000000){

                $new_name = uniqid()."_".$kategori."_".$i.".".$ext;

                if(move_uploaded_file($tmp, $upload_dir.$new_name)){

                    // hapus file lama
                    if($bukti_lama && file_exists($upload_dir.$bukti_lama)){
                        unlink($upload_dir.$bukti_lama);
                    }

                    $bukti = $new_name;
                }
            }
        }

        // =======================
        // NOMINAL
        // =======================

        $nominal = preg_replace('/[^0-9]/','',$data[$i] ?? 0);
        $nominal = intval($nominal);

        if($nominal <= 0) continue;

        $total += $nominal;

        // =======================
        // AUTO CROSS REF
        // =======================

        $bukti_ref = null;

        if(!empty($bukti)){
            $bukti_ref = "B".$no_bukti;
            $no_bukti++;
        }

        // =======================
        // INSERT DETAIL
        // =======================

        mysqli_query($conn, "
            INSERT INTO lpj_detail
            (
                lpj_id,
                kategori,
                tanggal,
                deskripsi,
                nominal,
                bukti,
                bukti_ref
            )
            VALUES
            (
                '$lpj_id',
                '$kategori',
                '$tanggal',
                '$deskripsi',
                '$nominal',
                ".($bukti ? "'$bukti'" : "NULL").",
                ".($bukti_ref ? "'$bukti_ref'" : "NULL")."
            )
        ");

    }
}

// =======================
// UPDATE HEADER
// =======================

// =======================
// AMBIL TOTAL ANGGARAN RAB
// =======================

$get_lpj = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT rab_id
    FROM lpj
    WHERE id='$lpj_id'
"));

$rab_id = $get_lpj['rab_id'];

$anggaran = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT SUM(total) as total_anggaran
    FROM rab_detail
    WHERE rab_id='$rab_id'
"));

$total_anggaran = $anggaran['total_anggaran'] ?? 0;

// =======================
// HITUNG SURPLUS / DEFISIT
// =======================

$surplus_defisit = $total_anggaran - $total;

$update_pemasukan = $bukti_pemasukan
? ", bukti_pemasukan='$bukti_pemasukan'"
: "";

mysqli_query($conn, "
    UPDATE lpj
    SET
        total_realisasi='$total',
        surplus_defisit='$surplus_defisit',
        status='Submitted',

        submitted_by='".$_SESSION['nama_lengkap']."',
        submitted_at=NOW(),

        approved_by=NULL,
        approved_at=NULL,

        rejected_note=NULL,
        rejected_by=NULL,
        rejected_at=NULL
        $update_pemasukan
    WHERE id='$lpj_id'
");

// =======================

header("Location: lpj_riwayat.php");
exit;
?>