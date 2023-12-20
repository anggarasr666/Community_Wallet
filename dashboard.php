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
    // if (isset($_SESSION['LAST_ACTIVITY']) && time() - $_SESSION['LAST_ACTIVITY'] > 60) {
    //     // Jika lebih dari 1 menit sejak aktivitas terakhir
    //     session_unset();     // Unset $_SESSION variable
    //     session_destroy();   // Hapus sesi
    //     header("Location: login.php");
    //     exit();
    // }

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
function joinWallet($conn, $user_id, $wallet_id)
{
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
    <script>
        let userId = <?= $user_id ?>;

        function openWallet(id) {
            $.get("wallet.php", {
                id: id
            }, (data) => {
                let main = $('div#main-content')[0];
                let wallet = JSON.parse(data);
                let walletData = wallet["wallet"];
                let walletMember = wallet["members"];
                htmlMember = walletMember.map((d) => `<div class="mb-4">
                            <label class="block text-gray-600 text-sm font-semibold mb-2">Member:</label>
                            <p class="text-gray-800 text-lg">${ d['username'] }</p> 
                            ${ (userId == walletData["creator_id"] && d['id'] != userId) ? `<button class="px-2 button bg-red-400 rounded-md " onclick="kick('${d['wallet_id']}','${d['id']}')">kick!</button>` : '' }
                            </div>`).join("\n");

                console.log(walletData);
                main.innerHTML = `
                    <div class="bg-gray-100 h-full flex flex-col gap-5 items-center justify-center">
        
                    <div class='flex flex-row gap-3'>
                        <div class="bg-white p-8 rounded shadow-md w-96">
                            <h1 class="text-2xl font-semibold mb-4">Wallet Details</h1>

                            <!-- Wallet Data -->
                            <div class="mb-4">
                                <label class="block text-gray-600 text-sm font-semibold mb-2">ID:</label>
                                <p class="text-gray-800 text-lg">${ walletData['id'] }</p>
                            </div>

                            <div class="mb-4">
                                <label class="block text-gray-600 text-sm font-semibold mb-2">Creator:</label>
                                <p class="text-gray-800 text-lg">${ walletData['username'] }</p>
                            </div>

                            <div class="mb-4">
                                <label class="block text-gray-600 text-sm font-semibold mb-2">Wallet Name:</label>
                                <p class="text-gray-800 text-lg">${ walletData['wallet_name'] }</p>
                            </div>

                            <div class="mb-4">
                                <label class="block text-gray-600 text-sm font-semibold mb-2">Balance:</label>
                                <p class="text-gray-800 text-lg">${ walletData['balance'] }</p>
                            </div>

                            <div class="mb-4">
                                <label class="block text-gray-600 text-sm font-semibold mb-2">Goal Balance:</label>
                                <p class="text-gray-800 text-lg">${ walletData['goal_balance'] }</p>
                            </div>

                        </div>
                        <div class="bg-white p-8 rounded shadow-md w-96 overflow-x-auto">
                            <h1 class="text-2xl font-semibold mb-4">Members</h1>
                            ${htmlMember}

                        </div>
                    </div>
                    <div class="flex flex-row gap-3">
                            ${ (userId == walletData["creator_id"]) ? `<a href="edit_wallet.php?wallet_id=${walletData["id"]}" class="button bg-blue-500 px-10 py-3 rounded-lg text-lg font-bold text-white"> Edit </a>` : '' }
                        <button class="button bg-green-500 px-10 py-3 rounded-lg text-lg font-bold text-white" onclick="document.querySelector('div#donate-container').style.display = 'flex'"> Donate </button>
                    </div>
                    <div class="hidden" id="donate-container">
                        <form id="donate-form">
                            <div class="mb-4">
                                <label for="balance" class="block text-gray-700 text-sm font-bold mb-2">Amount to Top-up:</label>
                                <input type="number" id="balance" name="balance" min="0"
                                    class="border rounded w-full py-2 px-3 focus:outline-none focus:border-blue-500 transition duration-300"
                                    required step="0.01">
                            </div>
                            <input type="hidden" name="wallet_id" value="${walletData['id']}">
                            <button type="submit" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600 focus:outline-none transition duration-300 w-full">Submit</button>
                        </form>
                    </div>
                </div>  `;
                $("#donate-form").submit(function(event) {
                    event.preventDefault();

                    var formData = $(this).serialize();

                    // Make an AJAX request to submit the form data
                    $.ajax({
                        type: "POST",
                        url: "donate_wallet.php",
                        data: formData,
                        success: function(response) {
                            openWallet(id);
                            toasts.push({
                                title: 'Berhasil Donasi',
                                content: response,
                                style: 'success'
                            });
                        },
                        error: function(error) {
                            console.error("Error submitting form:", error);
                            toasts.push({
                                title: "Error submitting form: " + error,
                                content: 'My notification description.',
                                style: 'Error'
                            });
                        }
                    });
                });

            })
        }
    </script>
</head>

<body class="bg-gradient-to-br from-purple-800 to-blue-600 flex flex-col h-screen">

    <!-- Navbar -->
    <?php include('navbar.php'); ?>

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
                <label for="angka" class="block text-center text-white text-sm font-bold mb-2">Daftar Wallet</label>
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

                    echo '<div class="mb-4">';
                    echo '<button class="bg-white text-blue-500 py-2 px-4 rounded hover:bg-blue-100 focus:outline-none transition duration-300 w-full font-bold text-xl" onclick="openWallet(' . $wallet_id . ')">';
                    echo "$wallet_name";

                    echo '</button>';
                    echo '</div>';
                }
                ?>
            </div>
        </div>


        <!-- Content Layout -->
        <div class="bg-white w-3/4 p-4 shadow" id="main-content">
        </div>
    </div>
</body>

</html>