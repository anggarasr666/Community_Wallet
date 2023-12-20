<?php
// Pastikan session sudah dimulai di bagian atas file
require_once 'db_functions.php';
session_start();

// Periksa apakah pengguna sudah login
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

// Perbarui waktu aktivitas terakhir
$_SESSION['LAST_ACTIVITY'] = time();

// Ambil data pengguna dari sesi atau database sesuai kebutuhan
// Misalnya, jika data pengguna disimpan dalam sesi:
$user_id = $_SESSION['id'];
// Ambil informasi pengguna dari database atau sesi sesuai kebutuhan

// Panggil fungsi dbconn() untuk mendapatkan koneksi database
$conn = dbconn();

if (!$conn) {
    die("Koneksi database gagal");
}

$query = "SELECT * FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);
mysqli_close($conn);
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Detail</title>
    <link rel="stylesheet" href="./output.css">
    <link rel="stylesheet" href="./assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.7.2/animate.min.css">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="./assets/js/script.js"></script>
    <script src="./node_modules/jquery/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@2.8.2/dist/alpine.min.js" defer></script>
</head>

<body class="bg-gradient-to-br from-purple-800 to-blue-600 flex flex-col h-screen">

    <?php include('navbar.php'); ?>

    <div class="flex-grow p-8">
        <!-- Konten Detail User -->
        <h1 class="text-3xl font-bold mb-4 text-center text-gray-800">User Detail</h1>
        <div class="border border-gray-300 bg-white p-8 rounded shadow-md">
            <p><strong>Username:</strong> <?php echo $user['username']; ?></p>
            <p><strong>Password:</strong> <?php echo $user['password']; ?></p>
            <!-- Tampilkan informasi balance -->
            <p><strong>Balance:</strong> $<?php echo number_format($user['balance'], 2); ?></p>

            <!-- Tambahkan tombol Ubah Password -->
            <a href="password.php" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600 focus:outline-none transition duration-300 mt-4 inline-block">Ubah Password</a>
            <a href="balance.php" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600 focus:outline-none transition duration-300 mt-4 inline-block">Isi Balance</a>
            <!-- Tambahkan informasi pengguna lainnya sesuai kebutuhan -->
        </div>
    </div>

    <!-- Optional: Anda dapat menyertakan script atau stylesheet tambahan di sini -->

</body>

</html>