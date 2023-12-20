<?php

require_once 'db_functions.php';
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['id'];

$conn = dbconn();

if (!$conn) {
    die("Koneksi database gagal");
}

$id = $_GET["id"];
$query = "SELECT w.id, w.creator_id, w.wallet_name, w.balance, w.goal_balance, u.username FROM wallets w JOIN users u ON u.id = w.creator_id WHERE w.id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$wallet = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

$query = "SELECT w.wallet_id, u.id, u.username FROM wallet_members w JOIN users u ON u.id = w.user_id WHERE w.wallet_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$members = array(); // Array to store the results

while ($row = mysqli_fetch_assoc($result)) {
    $members[] = $row;
}

mysqli_stmt_close($stmt);

$wallet_data = array(
    "wallet" => $wallet,
    "members" => $members
);

echo json_encode($wallet_data);
