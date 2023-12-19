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

    <nav class="bg-gray-800 p-4 text-white flex justify-between items-center">
        <div class="flex items-center">
            <div class="mr-4">
                <span class="text-lg font-bold">PUPUAN</span>
            </div>
        </div>

        <div class="flex items-center flex-grow">
            <a href="contact.php" class="text-white hover:text-gray-300">Contact</a>
        </div>

        <div class="flex items-center">
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="flex flex-row items-center px-4 py-2 mt-2 text-sm font-semibold text-left bg-transparent rounded-lg dark-mode:bg-transparent dark-mode:focus:text-white dark-mode:hover:text-white dark-mode:focus:bg-gray-600 dark-mode:hover:bg-gray-600 md:w-auto md:inline md:mt-0 md:ml-4 hover:text-gray-900 focus:text-gray-900 hover:bg-gray-200 focus:bg-gray-200 focus:outline-none focus:shadow-outline cursor-pointer">
                    <span>Account</span>
                    <svg fill="currentColor" viewBox="0 0 20 20" :class="{'rotate-180': open, 'rotate-0': !open}" class="inline w-4 h-4 mt-1 ml-1 transition-transform duration-200 transform md:-mt-1"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                </button>
                <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="absolute right-0 w-full mt-2 origin-top-right rounded-md shadow-lg md:w-48">
                    <div class="px-2 py-2 bg-white rounded-md shadow dark-mode:bg-gray-800">
                        <a class="block px-4 py-2 mt-2 text-sm font-semibold text-gray-900 bg-transparent rounded-lg dark-mode:bg-transparent dark-mode:hover:bg-gray-600 dark-mode:focus:bg-gray-600 dark-mode:focus:text-white dark-mode:hover:text-white dark-mode:text-gray-200 md:mt-0 hover:text-gray-900 focus:text-gray-900 hover:bg-gray-200 focus:bg-gray-200 focus:outline-none focus:shadow-outline" href="detail.php">Detail</a>
                        <?php
                        // Tampilkan tombol log-out jika pengguna telah login
                        if (isset($_SESSION['id'])) {
                            echo '<a class="block px-4 py-2 mt-2 text-sm font-semibold text-gray-900 bg-transparent rounded-lg dark-mode:bg-transparent dark-mode:hover:bg-gray-600 dark-mode:focus:bg-gray-600 dark-mode:focus:text-white dark-mode:hover:text-white dark-mode:text-gray-200 md:mt-0 hover:text-gray-900 focus:text-gray-900 hover:bg-gray-200 focus:bg-gray-200 focus:outline-none focus:shadow-outline" href="dashboard.php?logout=true">Log-out</a>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </nav>

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
                    <input type="number" id="new_balance" name="new_balance" min="0"
                        class="border rounded w-full py-2 px-3 focus:outline-none focus:border-blue-500 transition duration-300"
                        required step="0.01">
                </div>

                <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">

                <button type="submit"
                    class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600 focus:outline-none transition duration-300 w-full">Submit</button>
            </form>
        </div>
    </div>
</body>

</html>
