<?php
// Mulai sesi (pastikan ini berada di bagian atas file)
session_start();
include "db_functions.php";

// Fungsi untuk logout
if (isset($_GET['logout'])) {
    // Hapus semua data sesi
    session_unset();
    session_destroy();
    // Redirect ke halaman login
    header("Location: login.php");
    exit();
}

// Periksa apakah 'id' sudah terdefinisi di sesi
if (isset($_SESSION['id'])) {
    $user_id = $_SESSION['id'];

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

    // Ambil koneksi database
    $conn = dbconn();

    // Fungsi untuk logout
    if (isset($_GET['logout'])) {
        // Hapus semua data sesi
        session_unset();
        session_destroy();
        // Redirect ke halaman login
        header("Location: login.php");
        exit();
    }
} else {
    // Jika 'id' belum terdefinisi, arahkan ke halaman login
    header("Location: login.php");
    exit();
}

// Fungsi untuk menambahkan user ke dalam sebuah wallet
function joinWallet($conn, $user_id, $wallet_id) {
    // Periksa apakah user sudah menjadi member dalam wallet tersebut
    $query_check_member = "SELECT * FROM wallet_members WHERE wallet_id = $wallet_id AND user_id = $user_id";
    $result_check_member = mysqli_query($conn, $query_check_member);

    if ($result_check_member && mysqli_num_rows($result_check_member) > 0) {
        // Jika user sudah menjadi member, tidak perlu melakukan apa-apa
        return;
    }

    // Periksa apakah wallet dengan ID tersebut sudah ada
    $query_check_wallet = "SELECT id, creator_id FROM wallets WHERE id = $wallet_id";
    $result_check_wallet = mysqli_query($conn, $query_check_wallet);

    if ($result_check_wallet && mysqli_num_rows($result_check_wallet) > 0) {
        // Wallet sudah ada, user menjadi member
        $row_wallet = mysqli_fetch_assoc($result_check_wallet);
        $creator_id = $row_wallet["creator_id"];

        // Perbaikan pada baris berikut
        $query_add_member = "INSERT INTO wallet_members (wallet_id, user_id) VALUES ($wallet_id, $user_id)";
        mysqli_query($conn, $query_add_member);
    } else {
        // Wallet belum ada, user menjadi creator
        $query_add_wallet = "INSERT INTO wallets (id, creator_id, wallet_name) VALUES ($wallet_id, $user_id, 'Wallet $wallet_id')";
        mysqli_query($conn, $query_add_wallet);

        $query_add_member = "INSERT INTO wallet_members (wallet_id, user_id) VALUES ($wallet_id, $user_id)";
        mysqli_query($conn, $query_add_member);
    }
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["tambah_wallet"])) {
    $angka = $_POST["angka"];
    // Validasi 6 digit
    if (strlen($angka) == 6 && is_numeric($angka)) {
        joinWallet($conn, $user_id, $angka);
    }
}

// Query untuk mendapatkan wallet yang dimiliki user
$query_user_wallets = "SELECT w.id, w.wallet_name FROM wallets w
                      JOIN wallet_members wm ON w.id = wm.wallet_id
                      WHERE wm.user_id = $user_id";

$result_user_wallets = mysqli_query($conn, $query_user_wallets);
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

    <!-- Navbar -->
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
                <button @click="open = !open"
                    class="flex flex-row items-center px-4 py-2 mt-2 text-sm font-semibold text-left bg-transparent rounded-lg dark-mode:bg-transparent dark-mode:focus:text-white dark-mode:hover:text-white dark-mode:focus:bg-gray-600 dark-mode:hover:bg-gray-600 md:w-auto md:inline md:mt-0 md:ml-4 hover:text-gray-900 focus:text-gray-900 hover:bg-gray-200 focus:bg-gray-200 focus:outline-none focus:shadow-outline cursor-pointer">
                    <span>Account</span>
                    <svg fill="currentColor" viewBox="0 0 20 20"
                        :class="{'rotate-180': open, 'rotate-0': !open}"
                        class="inline w-4 h-4 mt-1 ml-1 transition-transform duration-200 transform md:-mt-1">
                        <path fill-rule="evenodd"
                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                            clip-rule="evenodd"></path>
                    </svg>
                </button>
                <div x-show="open" @click.away="open = false"
                    x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="transform opacity-0 scale-95"
                    x-transition:enter-end="transform opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="transform opacity-100 scale-100"
                    x-transition:leave-end="transform opacity-0 scale-95"
                    class="absolute right-0 w-full mt-2 origin-top-right rounded-md shadow-lg md:w-48">
                    <div class="px-2 py-2 bg-white rounded-md shadow dark-mode:bg-gray-800">
                        <a class="block px-4 py-2 mt-2 text-sm font-semibold text-gray-900 bg-transparent rounded-lg dark-mode:bg-transparent dark-mode:hover:bg-gray-600 dark-mode:focus:bg-gray-600 dark-mode:focus:text-white dark-mode:hover:text-white dark-mode:text-gray-200 md:mt-0 hover:text-gray-900 focus:text-gray-900 hover:bg-gray-200 focus:bg-gray-200 focus:outline-none focus:shadow-outline"
                            href="detail.php">Detail</a>
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
  <!-- Grid Layout -->
  <div class="flex flex-grow">
        <!-- Sidebar -->
        <div class="bg-white w-1/4 p-4 shadow flex flex-col">
            <div class="bg-blue-300 w-full p-4 shadow mb-4" style="flex: 3;">
                <form method="POST">
                    <!-- Input untuk Angka (Maksimal 6 Digit) -->
                    <div class="mb-4">
                        <label for="angka" class="block text-center text-white text-sm font-bold mb-2">Join Wallet</label>
                        <input type="number" id="angka" name="angka" class="border rounded w-full py-2 px-3 focus:outline-none focus:border-blue-500 transition duration-300" max="999999" required>
                    </div>

                    <!-- Tombol Tambah -->
                    <button type="submit" name="tambah_wallet" class="bg-white text-blue-500 py-2 px-4 rounded hover:bg-blue-100 focus:outline-none transition duration-300 w-full">
                        Tambah
                    </button>
                </form>
            </div>

            <!-- Sidebar - Bottom Part (9:12) -->
            <div class="bg-blue-600 w-full p-4 shadow" style="flex: 9;">
                <p>Sidebar - Bottom</p>
                <?php
                // Loop untuk menampilkan wallet yang dimiliki user
                while ($row_wallet = mysqli_fetch_assoc($result_user_wallets)) {
                    $wallet_id = $row_wallet["id"];
                    $wallet_name = $row_wallet["wallet_name"];

                    // Query untuk mendapatkan creator wallet
                    $query_creator = "SELECT u.username FROM users u
                                      JOIN wallets w ON u.id = w.creator_id
                                      WHERE w.id = $wallet_id";
                    $result_creator = mysqli_query($conn, $query_creator);
                    $row_creator = mysqli_fetch_assoc($result_creator);
                    $creator_name = $row_creator["username"];

                    // Query untuk mendapatkan member wallet
                    $query_members = "SELECT u.username FROM users u
                                      JOIN wallet_members wm ON u.id = wm.user_id
                                      WHERE wm.wallet_id = $wallet_id";
                    $result_members = mysqli_query($conn, $query_members);

                    echo '<div class="mb-4">';
                    echo '<button class="bg-white text-blue-500 py-2 px-4 rounded hover:bg-blue-100 focus:outline-none transition duration-300 w-full">';
                    echo "Wallet: $wallet_name (Creator: $creator_name)";
                    
                    // Menampilkan daftar member wallet
                    while ($row_member = mysqli_fetch_assoc($result_members)) {
                        $member_name = $row_member["username"];
                        echo "<br>- Member: $member_name";
                    }

                    echo '</button>';
                    echo '</div>';
                }
                ?>
            </div>
        </div>

        <!-- Content Layout -->
        <div class="bg-white w-3/4 p-4 shadow">
            <!-- Isi dengan konten yang sesuai -->
            <p>Main Content</p>
        </div>
    </div>
</body>

</html>