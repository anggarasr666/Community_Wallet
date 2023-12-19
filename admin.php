<?php
require_once 'db_functions.php';
session_start();

// Periksa apakah pengguna sudah login sebagai admin
if (!isset($_SESSION['admin'])) {
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

// Panggil fungsi dbconn() untuk mendapatkan koneksi database
$conn = dbconn();

if (!$conn) {
    die("Koneksi database gagal");
}

// Ambil data top-up yang masih menunggu konfirmasi
$query = "SELECT * FROM topup WHERE status = 'menunggu konfirmasi'";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Error: " . mysqli_error($conn));
}

// Proses konfirmasi top-up oleh admin
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm'])) {
    $topup_id = $_POST['topup_id'];

    // Update status top-up menjadi "dikonfirmasi"
    $query = "UPDATE topup SET status = 'dikonfirmasi' WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $topup_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        // Ambil data top-up yang dikonfirmasi
        $query = "SELECT * FROM topup WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $topup_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $topup_data = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        // Update saldo user
        $query = "UPDATE users SET balance = balance + ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "di", $topup_data['amount'], $topup_data['user_id']);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            $message = "Top-up confirmed successfully. User balance updated.";
        } else {
            $message = "Error updating user balance: " . mysqli_error($conn);
        }
    } else {
        $message = "Error: " . mysqli_error($conn);
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
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
    <nav class="bg-gray-800 p-4 text-white flex justify-between items-center">
        <div class="flex items-center">
            <div class="mr-4">
                <span class="text-lg font-bold">Admin Panel</span>
            </div>
        </div>

        <div class="flex items-center">
            <a href="login.php" class="text-white hover:text-gray-300">Log-out</a>
        </div>
    </nav>

    <div class="flex-grow p-8">
        <!-- Konten Admin Panel -->
        <h1 class="text-3xl font-bold mb-4 text-center text-gray-800">Admin Panel</h1>
        <div class="border border-gray-300 bg-white p-8 rounded shadow-md">
            <?php
            // Tampilkan pesan sukses atau kesalahan
            if (!empty($message)) {
                echo '<div class="mb-4 text-green-600">' . $message . '</div>';
            }
            ?>
            <h2 class="text-2xl font-bold mb-4">Top-up Requests</h2>
            <?php
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    echo '<div class="mb-4 border-b pb-4">';
                    echo '<p><strong>User ID:</strong> ' . $row['user_id'] . '</p>';
                    echo '<p><strong>Amount:</strong> ' . $row['amount'] . '</p>';
                    echo '<form action="admin.php" method="post">';
                    echo '<input type="hidden" name="topup_id" value="' . $row['id'] . '">';
                    echo '<input type="hidden" name="user_id" value="' . $row['user_id'] . '">';
                    echo '<button type="submit" name="confirm" class="bg-green-500 text-white py-2 px-4 rounded hover:bg-green-600 focus:outline-none transition duration-300">Confirm</button>';
                    echo '</form>';
                    echo '</div>';
                }
            } else {
                echo '<p>No top-up requests.</p>';
            }
            ?>
        </div>
    </div>
</body>

</html>

