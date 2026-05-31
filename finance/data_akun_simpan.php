<?php
include "../config/db.php";

$id = $_POST['id'];
$kode = $_POST['kode_akun'];
$nama = $_POST['nama_akun'];
$ket  = $_POST['keterangan'];

if($id == ""){
    mysqli_query($conn, "INSERT INTO akun(kode_akun,nama_akun,keterangan)
    VALUES('$kode','$nama','$ket')");
}else{
    mysqli_query($conn, "UPDATE akun SET
    kode_akun='$kode',
    nama_akun='$nama',
    keterangan='$ket'
    WHERE id='$id'");
}

header("Location: data_akun.php");