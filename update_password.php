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

// Periksa apakah konfirmasi password sesuai dengan password lama (sebelum di-hash)
if (empty($old_password_plain) || !password_verify($old_password_plain, $old_password_hashed)) {
    $message = "Konfirmasi password tidak sesuai dengan password lama.";
    // Redirect ke halaman password.php dengan parameter pesan
    header("Location: password.php?message=$message");
    exit();
} else {
    // Ambil data password baru dan konfirmasi password dari formulir
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Periksa apakah konfirmasi password sesuai dengan password baru
    if ($new_password !== $confirm_password) {
        $message = "Konfirmasi password tidak sesuai dengan password baru.";
        // Redirect ke halaman password.php dengan parameter pesan
        header("Location: password.php?message=$message");
        exit();
    } else {
        // ... (kode lainnya tetap sama)

        // Pesan berhasil
        $message = "Password berhasil diubah.";
        // Redirect ke halaman password.php dengan parameter pesan
        header("Location: password.php?message=$message");
        exit();
    }
}

// Redirect ke halaman detail setelah mengubah password
header("Location: password.php");
exit();
?>
