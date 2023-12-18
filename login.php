<?php
// login.php

require_once 'db_functions.php';

// Mulai sesi
session_start();

$message = array(); // Menggunakan array untuk menyimpan pesan sukses atau kesalahan

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $conn = dbconn();

    // Gunakan prepared statement untuk mencegah SQL injection
    $query = "SELECT id, username, password FROM users WHERE username=?";
    $stmt = mysqli_prepare($conn, $query);

    if ($stmt) {
        // Bind parameters dan execute statement
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);

        // Store the result
        mysqli_stmt_store_result($stmt);

        // Check if a user with the provided username exists
        if (mysqli_stmt_num_rows($stmt) > 0) {
            // Bind the result
            mysqli_stmt_bind_result($stmt, $id, $db_username, $db_password);
            mysqli_stmt_fetch($stmt);

            // Verifikasi password
            if (password_verify($password, $db_password)) {
                // Authentication successful
                $message[] = "Login successful!"; // Tambahkan pesan keberhasilan ke dalam array

                // Set session user_id
                $_SESSION['id'] = $id;

                // Redirect ke dashboard.php
                header('Location: dashboard.php');
                exit();
            } else {
                // Authentication failed
                $message[] = "Username or password is incorrect.";
            }
        } else {
            // User dengan username yang diberikan tidak ada
            $message[] = "Username or password is incorrect.";
        }

        // Tutup statement
        mysqli_stmt_close($stmt);
    } else {
        // Error dalam menyiapkan statement
        $message[] = "Error: " . mysqli_error($conn);
    }

    // Tutup koneksi database
    mysqli_close($conn);
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="./output.css">
    <link rel="stylesheet" href="./assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.7.2/animate.min.css">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="./assets/js/script.js"></script>
    <script src="./node_modules/jquery/dist/jquery.min.js"></script>
</head>
<body class="bg-gradient-to-br from-purple-800 to-blue-600 flex items-center justify-center h-screen">

    <div class="border border-gray-300 bg-white p-8 rounded shadow-md w-96 animate__animated animate__fadeInDown">
        <h1 class="text-3xl font-bold mb-4 text-center text-gray-800">Login</h1>

        <?php
        // Tampilkan pesan sukses atau kesalahan
        foreach ($message as $msg) {
            echo '<div class="mb-4 text-green-600">' . $msg . '</div>';
        }
        ?>
        <form action="login.php" method="post" class="space-y-4">
            <div class="mb-4">
                <label for="username" class="block text-gray-700 text-sm font-bold mb-2">Username:</label>
                <input type="text" id="username" name="username"
                    class="border rounded w-full py-2 px-3 focus:outline-none focus:border-blue-500 transition duration-300"
                    required>
            </div>

            <div class="mb-4">
                <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password:</label>
                <input type="password" id="password" name="password"
                    class="border rounded w-full py-2 px-3 focus:outline-none focus:border-blue-500 transition duration-300"
                    required>
            </div>

            <button type="submit"
                class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600 focus:outline-none transition duration-300 w-full">Login</button>

                <div class="text-center mt-4">
                    <p class="text-sm">Don't have an account? <a href="register.php" class="text-blue-500">Register here</a></p>
                </div>
        </form>
    </div>
    <script>
        $(document).ready(function() {

        });
    </script>

</body>
</html>



