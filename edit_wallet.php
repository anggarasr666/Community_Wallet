<?php
require_once 'db_functions.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

// Get the user ID from the session
$user_id = $_SESSION['id'];

// Get the wallet ID from the query string or other source
$wallet_id = isset($_GET['wallet_id']) ? $_GET['wallet_id'] : null;

// Check if the wallet ID is provided
if (!$wallet_id) {
    // Redirect to an error page or handle the situation accordingly
    echo "Error: Wallet ID not provided.";
    exit();
}

// Get the wallet data from the database
$conn = dbconn();
if (!$conn) {
    die("Database connection error");
}

$query = "SELECT * FROM wallets WHERE id = ? AND creator_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ii", $wallet_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$walletData = mysqli_fetch_assoc($result);

// Check if the wallet exists and belongs to the logged-in user
if (!$walletData) {
    // Redirect to an error page or handle the situation accordingly
    echo "Error: Wallet not found or you don't have permission.";
    exit();
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and validate form data (you should add proper validation)
    $newWalletName = isset($_POST['wallet_name']) ? $_POST['wallet_name'] : '';
    $newBalance = isset($_POST['balance']) ? $_POST['balance'] : '';

    // Update wallet data in the database
    $updateQuery = "UPDATE wallets SET wallet_name = ?, goal_balance = ? WHERE id = ?";
    $updateStmt = mysqli_prepare($conn, $updateQuery);
    mysqli_stmt_bind_param($updateStmt, "sdi", $newWalletName, $newBalance, $wallet_id);

    if (mysqli_stmt_execute($updateStmt)) {
        echo "<script> let msg = {msg: 'Wallet data updated successfully!', type: 'success'}; </script>";
        // You can redirect the user to the wallet details page or another page after successful update
    } else {
        echo "<script> let msg = {msg: 'Error updating wallet data: " . mysqli_error($conn) . "', type: 'error'}; </script>";
    }

    // Close the statement
    mysqli_stmt_close($updateStmt);
}

// Close the database connection
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
    <script>
        $(document).ready(function() {
            if (typeof msg !== 'undefined') {
                toasts.push({
                    title: 'Berhasil Edit Wallet',
                    content: msg.msg,
                    style: msg.type
                });
            }
        });
    </script>
</head>

<body class="bg-gray-100 min-h-screen flex items-center justify-center">

    <?php include('navbar.php'); ?>
    <div class="bg-white p-8 rounded shadow-md w-full sm:w-96">
        <h2 class="text-2xl font-bold mb-6">Edit Wallet</h2>

        <form method="post">
            <div class="mb-4">
                <label for="wallet_name" class="block text-sm font-medium text-gray-600">Wallet Name:</label>
                <input type="text" id="wallet_name" name="wallet_name" value="<?php echo $walletData['wallet_name']; ?>" class="mt-1 p-2 border rounded w-full focus:outline-none focus:border-blue-500" required>
            </div>

            <div class="mb-4">
                <label for="balance" class="block text-sm font-medium text-gray-600">Goal Balance:</label>
                <input type="number" id="balance" name="balance" min="0" class="border rounded w-full py-2 px-3 focus:outline-none focus:border-blue-500 transition duration-300" required step="0.01" value="<?= $newBalance ?? $walletData['goal_balance']; ?>" class="mt-1 p-2 border rounded w-full focus:outline-none focus:border-blue-500" required>
            </div>

            <button type="submit" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-700 focus:outline-none">
                Update Wallet
            </button>
        </form>
    </div>

</body>

</html>