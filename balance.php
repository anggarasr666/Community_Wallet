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

// Proses formulir penambahan balance
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_balance = $_POST['new_balance'];

    // Validasi input untuk memastikan bahwa nilai adalah desimal 2 digit
    if (!preg_match('/^\d+(\.\d{1,2})?$/', $new_balance)) {
        $message = "Error: Invalid input format. Please enter a valid decimal number with up to 2 digits after the decimal point.";
    } else {
        // Simpan data top-up balance ke database dengan status "menunggu konfirmasi"
        $status = "menunggu konfirmasi";
        $query = "INSERT INTO topup (user_id, amount, status) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ids", $user_id, $new_balance, $status);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            $message = "Top-up request sent. Waiting for admin confirmation.";
        } else {
            $message = "Error: " . mysqli_error($conn);
        }
    }
}

// Ambil informasi pengguna dari database atau sesi sesuai kebutuhan
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
    <title>Dashboard</title>
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
        <!-- Konten Form Top-up Balance -->
        <h1 class="text-3xl font-bold mb-4 text-center text-gray-800">Top-up Balance</h1>
        <div class="border border-gray-300 bg-white p-8 rounded shadow-md">
            <?php
            // Tampilkan pesan sukses atau kesalahan
            if (!empty($message)) {
                echo '<div class="mb-4 text-green-600">' . $message . '</div>';
            }
            ?>
            <form action="balance.php" method="post" class="space-y-4">
                <div class="mb-4">
                    <label for="new_balance" class="block text-gray-700 text-sm font-bold mb-2">Amount to Top-up:</label>
                    <input type="number" id="new_balance" name="new_balance" min="0" class="border rounded w-full py-2 px-3 focus:outline-none focus:border-blue-500 transition duration-300" required step="0.01">
                </div>

                <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">

                <button type="submit" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600 focus:outline-none transition duration-300 w-full">Submit</button>
            </form>
        </div>
    </div>
</body>

</html>