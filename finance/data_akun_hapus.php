<?php
include "../config/db.php";

$id = $_GET['id'];

mysqli_query($conn, "DELETE FROM akun WHERE id='$id'");

header("Location: data_akun.php");