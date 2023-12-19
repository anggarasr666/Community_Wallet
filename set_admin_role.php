<?php
// Ambil data formulir
$username = $_POST['username'];

// Update basis data untuk menetapkan peran admin
$query = "UPDATE users SET role = 'admin' WHERE username = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "s", $username);

if (mysqli_stmt_execute($stmt)) {
    echo "Peran admin berhasil ditetapkan untuk pengguna $username.";
} else {
    echo "Gagal menetapkan peran admin.";
}

mysqli_stmt_close($stmt);
?>
