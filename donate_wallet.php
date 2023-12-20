<?php
// Pastikan session sudah dimulai di bagian atas file
require_once 'db_functions.php';
session_start();

// Periksa apakah pengguna sudah login
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

// Ambil data pengguna dari sesi atau database sesuai kebutuhan
$user_id = $_SESSION['id'];

// Panggil fungsi dbconn() untuk mendapatkan koneksi database
$conn = dbconn();

if (!$conn) {
    die("Koneksi database gagal");
}

// Ambil informasi pengguna
$query = "SELECT balance FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
$user_balance = $user['balance'];
mysqli_stmt_close($stmt);

$query = "SELECT w.id, w.creator_id, w.wallet_name, w.balance, w.goal_balance, u.username FROM wallets w JOIN users u ON u.id = w.creator_id WHERE w.id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $_POST["wallet_id"]);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$wallet = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// Simulasikan proses donasi
$donation_amount = (float)$_POST["balance"]; // Replace with the actual donation amount
if ($user_balance >= $donation_amount) {
    // Update user's balance
    $new_user_balance = $user_balance - $donation_amount;
    $stmt = $conn->prepare("UPDATE users SET balance = ? WHERE id = ?");
    $stmt->bind_param("di", $new_user_balance, $user_id);

    if ($stmt->execute()) {
        // Update wallet's balance (assuming the wallet_id is known, replace it with the actual wallet_id)
        $wallet_id = $_POST["wallet_id"];
        $add_donation = $donation_amount + (float)$wallet['balance'];
        $query = "UPDATE wallets SET balance = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "di", $add_donation, $wallet_id);

        if (mysqli_stmt_execute($stmt)) {
            $message = "Berhasil Mendonasikan $" . $donation_amount . " kepada wallet " . $wallet_id;
        } else {
            $message = "Error updating wallet balance: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    } else {
        $message = "Error updating user balance: " . $stmt->error;
    }
} else {
    $message = "Insufficient balance for donation";
}

echo $message;

exit();
