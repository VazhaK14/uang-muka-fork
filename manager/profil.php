<?php
session_start();
include "../config/db.php";

// =======================
// CEK LOGIN
// =======================
if (!isset($_SESSION['id_user'])) {
    header("Location: ../auth/login.php");
    exit;
}

$id_user = $_SESSION['id_user'];

// =======================
// UPDATE DATA
// =======================
if(isset($_POST['simpan'])){

    $nama     = $_POST['nama'];
    $username = $_POST['username'];
    $password = $_POST['password'];

    // jika password diisi → update
    if($password != ""){
        $password = md5($password);

        mysqli_query($conn, "
        UPDATE users SET
        nama_lengkap='$nama',
        username='$username',
        password='$password'
        WHERE id='$id_user'
        ");
    } else {
        mysqli_query($conn, "
        UPDATE users SET
        nama_lengkap='$nama',
        username='$username'
        WHERE id='$id_user'
        ");
    }

    // update session biar langsung ke refresh di topbar
    $_SESSION['nama_lengkap'] = $nama;
    $_SESSION['username']     = $username;

    header("Location: profil.php?success=1");
    exit;
}

// =======================
// AMBIL DATA USER
// =======================
$user = mysqli_fetch_assoc(mysqli_query($conn, "
SELECT * FROM users WHERE id='$id_user'
"));
?>

<!DOCTYPE html>
<html>
<head>
<title>Profil Audit Director</title>

<style>
body {
    font-family: Arial;
    background: #f4f4f4;
    margin: 0;
}

/* CONTENT */
.content {
    padding: 40px;
    display: flex;
    justify-content: center;
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

/* TITLE */
h2 {
    margin-bottom: 20px;
}

/* FORM */
label {
    font-weight: bold;
}

input {
    width: 100%;
    padding: 10px;
    margin: 5px 0 15px;
    border: 1px solid #ccc;
    border-radius: 6px;
}

input[readonly] {
    background: #eee;
}

/* BUTTON */
.btn {
    background: #b8860b;
    color: white;
    padding: 10px 20px;
    border: none;
    cursor: pointer;
    border-radius: 6px;
}

.btn:hover {
    background: #a0760a;
}

/* ALERT */
.alert {
    background: #4CAF50;
    color: white;
    padding: 10px;
    margin-bottom: 15px;
    border-radius: 6px;
}
</style>

</head>

<body>

<div class="content">

<div class="card">

<h2>Profil Audit Director</h2>

<?php if(isset($_GET['success'])): ?>
<div class="alert">Data berhasil diupdate!</div>
<?php endif; ?>

<form method="POST">

<!-- ROLE -->
<label>Role</label>
<?php
$role_tampil = ($user['role'] == 'director') ? 'Audit Director' : ucfirst($user['role']);
?>

<input type="text" value="<?= $role_tampil; ?>" readonly>

<!-- NAMA -->
<label>Nama</label>
<input type="text" name="nama" value="<?= $user['nama_lengkap']; ?>" required>

<!-- USERNAME -->
<label>Username</label>
<input type="text" name="username" value="<?= $user['username']; ?>" required>

<!-- PASSWORD -->
<label>Password (kosongkan jika tidak diubah)</label>
<input type="password" name="password">

<!-- BUTTON -->
<button type="submit" name="simpan" class="btn">Simpan</button>

</form>

</div>

</div>

</body>
</html>