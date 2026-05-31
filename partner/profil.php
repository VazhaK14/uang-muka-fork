<?php
session_start();
include "../config/db.php";

// CEK LOGIN
if(!isset($_SESSION['id_user'])){
    header("Location: ../auth/login.php");
    exit;
}

$id = $_SESSION['id_user'];

// AMBIL DATA USER
$query = mysqli_query($conn, "SELECT * FROM users WHERE id='$id'");
$data  = mysqli_fetch_assoc($query);

// =======================
// UPDATE PROFIL
// =======================
if(isset($_POST['simpan'])){

    $nama     = $_POST['nama'];
    $username = $_POST['username'];
    $password = $_POST['password'];

    if($password != ""){
        $password = md5($password);
        mysqli_query($conn, "
            UPDATE users SET 
            nama_lengkap='$nama',
            username='$username',
            password='$password'
            WHERE id='$id'
        ");
    } else {
        mysqli_query($conn, "
            UPDATE users SET 
            nama_lengkap='$nama',
            username='$username'
            WHERE id='$id'
        ");
    }

    // update session juga
    $_SESSION['nama_lengkap'] = $nama;
    $_SESSION['username']     = $username;

    header("Location: profil.php?success=1");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Profil Partner</title>

<style>
body {
    font-family: Arial;
    background: #f4f4f4;
    margin: 0;
}

/* CONTAINER */
.container {
    display: flex;
    justify-content: center;
    padding: 40px;
}

/* CARD */
.card {
    width: 100%;
    max-width: 600px;
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

/* FORM */
label {
    font-weight: bold;
}

input {
    width: 100%;
    padding: 10px;
    margin: 5px 0 15px;
    border-radius: 8px;
    border: 1px solid #ccc;
}

/* BUTTON */
.btn {
    background: #b8860b;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
}

.btn:hover {
    background: #a0760a;
}

/* ALERT */
.alert {
    background: #4CAF50;
    color: white;
    padding: 10px;
    border-radius: 6px;
    margin-bottom: 15px;
}
</style>

</head>

<body>

<div class="container">
<div class="card">

<h2>Profil Partner</h2>

<?php if(isset($_GET['success'])): ?>
<div class="alert">Data berhasil diperbarui!</div>
<?php endif; ?>

<form method="POST">

<label>Role</label>
<input type="text" value="Partner" readonly>

<label>Nama</label>
<input type="text" name="nama" value="<?= $data['nama_lengkap'] ?>" required>

<label>Username</label>
<input type="text" name="username" value="<?= $data['username'] ?>" required>

<label>Password (kosongkan jika tidak diubah)</label>
<input type="password" name="password">

<button type="submit" name="simpan" class="btn">Simpan</button>

</form>

</div>
</div>

</body>
</html>