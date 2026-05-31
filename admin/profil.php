<?php
session_start();
include "../config/db.php";

$user_id = $_SESSION['user_id'];

// =======================
// AMBIL DATA USER
// =======================
$user = mysqli_fetch_assoc(mysqli_query($conn, "
SELECT * FROM users WHERE id='$user_id'
"));

// =======================
// UPDATE PROFIL
// =======================
if(isset($_POST['update'])){

    $nama     = $_POST['nama'];
    $username = $_POST['username'];
    $password = $_POST['password'];

    // kalau password diisi → update
    if($password != ""){
        $password = md5($password);
        mysqli_query($conn, "
        UPDATE users SET
        nama_lengkap='$nama',
        username='$username',
        password='$password'
        WHERE id='$user_id'
        ");
    } else {
        mysqli_query($conn, "
        UPDATE users SET
        nama_lengkap='$nama',
        username='$username'
        WHERE id='$user_id'
        ");
    }

    // update session biar langsung ke refresh di topbar
    $_SESSION['nama_lengkap'] = $nama;

    header("Location: profil.php?success=1");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Profil</title>

<style>
body {
    font-family: Arial;
    background: #f4f4f4;
    margin: 0;
}

/* CONTAINER */
.container {
    max-width: 900px;
    margin: 40px auto;
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

/* TITLE */
h2 {
    margin-bottom: 20px;
}

/* FORM */
.form-group {
    margin-bottom: 15px;
}

label {
    font-weight: bold;
}

input {
    width: 100%;
    padding: 10px;
    margin-top: 5px;
    border: 1px solid #ccc;
    border-radius: 6px;
}

/* READONLY */
input[readonly] {
    background: #eee;
}

/* BUTTON */
.btn {
    background: #b8860b;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
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
    margin-bottom: 15px;
    border-radius: 6px;
}
</style>

</head>

<body>

<div class="container">

<h2>Profil User</h2>

<?php if(isset($_GET['success'])): ?>
<div class="alert">Profil berhasil diupdate</div>
<?php endif; ?>

<form method="POST">

<div class="form-group">
<label>Role</label>
<input type="text" value="<?= ucfirst($user['role']); ?>" readonly>
</div>

<div class="form-group">
<label>Nama</label>
<input type="text" name="nama" value="<?= $user['nama_lengkap']; ?>" required>
</div>

<div class="form-group">
<label>Username</label>
<input type="text" name="username" value="<?= $user['username']; ?>" required>
</div>

<div class="form-group">
<label>Password (kosongkan jika tidak diubah)</label>
<input type="password" name="password">
</div>

<button type="submit" name="update" class="btn">Simpan</button>

</form>

</div>

</body>
</html>