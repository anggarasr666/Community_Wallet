<?php
// Pastikan session sudah dimulai di bagian atas file
require_once 'db_functions.php';
session_start();

// Periksa apakah pengguna sudah login
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

// Perbarui waktu timeout
if (isset($_SESSION['LAST_ACTIVITY']) && time() - $_SESSION['LAST_ACTIVITY'] > 60) {
    // Jika lebih dari 1 menit sejak aktivitas terakhir
    session_unset();     // Unset $_SESSION variable
    session_destroy();   // Hapus sesi
    header("Location: login.php");
    exit();
}

// Perbarui waktu aktivitas terakhir
$_SESSION['LAST_ACTIVITY'] = time();

// Ambil data pengguna dari sesi atau database sesuai kebutuhan
// Misalnya, jika data pengguna disimpan dalam sesi:
$user_id = $_SESSION['id'];

// Panggil fungsi dbconn() untuk mendapatkan koneksi database
$conn = dbconn();

if (!$conn) {
    die("Koneksi database gagal");
}

// Ambil password lama (sebelum di-hash) dari formulir
$old_password_plain = isset($_POST['old_password']) ? $_POST['old_password'] : '';

// Ambil password lama (sebelum di-hash) dari database
$query = "SELECT password FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
$old_password_hashed = $user['password'];
mysqli_stmt_close($stmt);

$new_password = $_POST['new_password'];
$confirm_password = $_POST['confirm_password'];

if (!password_verify($old_password_plain, $old_password_hashed)) {
    $message = "Password Lama tidak sesuai!";
} else if ($new_password != $confirm_password) {
    $message = "Password Baru tidak sesuai dengan Password Konfirmasi";
} else {
    $hash_password = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $hash_password, $user_id);

    if ($stmt->execute()) {
        $message = "Password updated successfully";
    } else {
        $message = "Error updating Password: " . $stmt->error;
    }
}

header("Location: password.php?message=$message");
exit();
