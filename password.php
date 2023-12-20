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
// if (isset($_SESSION['LAST_ACTIVITY']) && time() - $_SESSION['LAST_ACTIVITY'] > 60) {
//     // Jika lebih dari 1 menit sejak aktivitas terakhir
//     session_unset();     // Unset $_SESSION variable
//     session_destroy();   // Hapus sesi
//     header("Location: login.php");
//     exit();
// }

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

$query = "SELECT * FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);
mysqli_close($conn);

// if (!empty($_GET['message'])) {
//     $message = $_GET['message'];
//     echo '<div class="mb-4 text-red-500">' . $message . '</div>';
// }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password</title>
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
        <!-- Form Ubah Password -->
        <h1 class="text-3xl font-bold mb-4 text-center text-gray-800">Change Password</h1>

        <div class="border border-gray-300 bg-white p-8 rounded shadow-md">
            <form action="update_password.php" method="post" class="space-y-4">
                <?php
                if (!empty($_GET['message'])) {
                    $message = $_GET['message'];
                    echo '<div class="mb-4 text-red-500">' . $message . '</div>';
                }
                ?>
                <div class="mb-4">
                    <label for="new_password" class="block text-gray-700 text-sm font-bold mb-2">Old Password:</label>
                    <input type="password" id="old_password" name="old_password" class="border rounded w-full py-2 px-3 focus:outline-none focus:border-blue-500 transition duration-300" required>
                </div>

                <div class="mb-4">
                    <label for="new_password" class="block text-gray-700 text-sm font-bold mb-2">New Password:</label>
                    <input type="password" id="new_password" name="new_password" class="border rounded w-full py-2 px-3 focus:outline-none focus:border-blue-500 transition duration-300" required>
                </div>

                <div class="mb-4">
                    <label for="confirm_password" class="block text-gray-700 text-sm font-bold mb-2">Confirm Password:</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="border rounded w-full py-2 px-3 focus:outline-none focus:border-blue-500 transition duration-300" required>
                </div>

                <button type="submit" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600 focus:outline-none transition duration-300">Change
                    Password</button>
            </form>
        </div>
    </div>

    <!-- Optional: Anda dapat menyertakan script atau stylesheet tambahan di sini -->

</body>

</html>