<?php
// Mulai sesi (pastikan ini berada di bagian atas file)
session_start();

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
        <!-- Konten Dashboard Anda akan ditempatkan di sini -->
        <h1 class="text-3xl font-bold mb-4 text-center text-gray-800">Welcome to Dashboard</h1>
        <!-- ... (Tambahkan konten dashboard lainnya sesuai kebutuhan) ... -->
    </div>

    <!-- Optional: Anda dapat menyertakan script atau stylesheet tambahan di sini -->

</body>
</html>
