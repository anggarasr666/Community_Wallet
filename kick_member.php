<?php
header('Content-Type: application/json');
require_once 'db_functions.php';
session_start();

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['type' => 'error', 'msg' => 'Invalid request method']);
    exit();
}

// Validate and sanitize input
$wallet_id = isset($_POST['wallet_id']) ? intval($_POST['wallet_id']) : null;
$user_id_to_kick = isset($_POST['user_id']) ? intval($_POST['user_id']) : null;

// Check if wallet_id and user_id are provided
if ($wallet_id === null || $user_id_to_kick === null) {
    echo json_encode(['type' => 'error', 'msg' => 'Missing wallet_id or user_id']);
    exit();
}

// Your database connection logic (you may need to adjust this based on your dbconn function)
$conn = dbconn();
if (!$conn) {
    echo json_encode(['type' => 'error', 'msg' => 'Database connection error']);
    exit();
}

function userHasAuthority($user_id, $wallet_id)
{
    // Your authorization logic goes here
    // For example, you might check if the user is the creator of the wallet or has a specific role

    // Fetch wallet information from the database
    $conn = dbconn(); // Replace with your database connection logic
    if (!$conn) {
        return false;
    }

    $query = "SELECT creator_id FROM wallets WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $wallet_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $wallet = mysqli_fetch_assoc($result);

    // Check if the user is the creator of the wallet (modify as needed based on your requirements)
    $isCreator = ($user_id === $wallet['creator_id']);

    // Close the database connection
    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    return $isCreator; // Adjust this based on your authorization logic
}

// Check if the user performing the action has the authority to kick members (add your authorization logic)
$user_id = $_SESSION['id'];
if (!userHasAuthority($user_id, $wallet_id)) {
    echo json_encode(['type' => 'error', 'msg' => 'You do not have the authority to kick members from this wallet']);
    exit();
}

// Perform the "kick" action (assuming you have a table named wallet_members)
$query = "DELETE FROM wallet_members WHERE wallet_id = ? AND user_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ii", $wallet_id, $user_id_to_kick);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['type' => 'success', 'msg' => 'Member kicked successfully']);
} else {
    echo json_encode(['type' => 'error', 'msg' => 'Error kicking member: ' . mysqli_error($conn)]);
}

// Close the database connection
mysqli_close($conn);
exit();
