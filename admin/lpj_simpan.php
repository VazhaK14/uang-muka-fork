<?php
session_start();
include "../config/db.php";

$rab_id = $_POST['rab_id'];
$no_lpj = $_POST['no_lpj'];
$created_by = $_SESSION['id'] ?? 1;

// =======================
// SETUP FOLDER
// =======================
$upload_dir = "../uploads/";

if(!is_dir($upload_dir)){
    mkdir($upload_dir, 0777, true);
}

// =======================
// INSERT LPJ
// =======================
mysqli_query($conn, "
INSERT INTO lpj
(
    rab_id,
    no_lpj,
    tanggal,
    total_realisasi,
    status,
    created_by,
    submitted_by,
    submitted_at
)
VALUES
(
    '$rab_id',
    '$no_lpj',
    NOW(),
    '0',
    'Submitted',
    '$created_by',
    '".$_SESSION['nama_lengkap']."',
    NOW()
)
");

$lpj_id = mysqli_insert_id($conn);

// =======================
$kategori_list = ['Akomodasi','Transportasi','Konsumsi','Lain-lain'];

$total = 0;

// nomor cross reference
$ref_no = 1;

// =======================
foreach($kategori_list as $kategori){

    $tanggal_arr   = $_POST[$kategori.'_tanggal'] ?? [];
    $deskripsi_arr = $_POST[$kategori.'_deskripsi'] ?? [];
    $nominal_arr   = $_POST[$kategori.'_nominal'] ?? [];

    for($i=0; $i<count($nominal_arr); $i++){

        $tanggal   = $tanggal_arr[$i] ?? null;
        $deskripsi = $deskripsi_arr[$i] ?? '';

        // =====================
        // FILE PENGELUARAN
        // =====================
        $bukti = null;
        $bukti_ref = null;

        if(isset($_FILES['bukti']['name'][$kategori][$i])
            && $_FILES['bukti']['name'][$kategori][$i] != ''){

            $file_name = $_FILES['bukti']['name'][$kategori][$i];
            $tmp       = $_FILES['bukti']['tmp_name'][$kategori][$i];
            $size      = $_FILES['bukti']['size'][$kategori][$i];

            $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            if(!in_array($ext, ['jpg','jpeg','png'])){
                die("Format file harus JPG/JPEG/PNG");
            }

            if($size > 2000000){
                die("Maksimal ukuran file 2MB");
            }

            $new_name = uniqid()."_".$kategori."_".$i.".".$ext;

            if(move_uploaded_file($tmp, $upload_dir.$new_name)){

                $bukti = $new_name;

                // generate B1 B2 B3
                $bukti_ref = "B".$ref_no;

                $ref_no++;
            }
        }

        // =====================
        $nominal = preg_replace('/[^0-9]/','',$nominal_arr[$i] ?? 0);

        $nominal = intval($nominal);

        if($nominal <= 0) continue;

        $total += $nominal;

        mysqli_query($conn, "
        INSERT INTO lpj_detail
        (lpj_id, kategori, tanggal, deskripsi, nominal, bukti, bukti_ref)
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
// AMBIL TOTAL ANGGARAN RAB
// =======================

$anggaran = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT SUM(total) as total_anggaran
    FROM rab_detail
    WHERE rab_id='$rab_id'
"));

$total_anggaran = $anggaran['total_anggaran'] ?? 0;

$surplus_defisit = $total_anggaran - $total;

// =======================
// UPDATE LPJ
// =======================

mysqli_query($conn, "
UPDATE lpj
SET
    total_realisasi='$total',
    surplus_defisit='$surplus_defisit'
WHERE id='$lpj_id'
");

header("Location: lpj_riwayat.php");
exit;
?>