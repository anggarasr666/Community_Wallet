<?php
require_once 'db_functions.php';

$message = ''; // Variable untuk menyimpan pesan sukses atau kesalahan

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $password_confirmation = $_POST['password_confirmation'];

    // Perform validation (e.g., password matching) if needed

    if ($password == $password_confirmation) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $conn = dbconn();

        // Use prepared statement to prevent SQL injection
        $query = "INSERT INTO users (username, password) VALUES (?, ?)";
        $stmt = mysqli_prepare($conn, $query);

        if ($stmt) {
            // Bind parameters and execute the statement
            mysqli_stmt_bind_param($stmt, "ss", $username, $hashed_password);
            mysqli_stmt_execute($stmt);

            // Registration successful
            $message = "Registration successful!"; // Set the success message

            // Close the statement
            mysqli_stmt_close($stmt);

            header("location: login.php");
        } else {
            // Error in preparing the statement
            $message = "Error: " . mysqli_error($conn);
        }

        // Close the database connection
        mysqli_close($conn);
    } else {
        // Passwords do not match
        $message = "Passwords do not match.";
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
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
        <h1 class="text-3xl font-bold mb-4 text-center text-gray-800">Register</h1>

        <?php
        // Tampilkan pesan sukses atau kesalahan
        if (!empty($message)) {
            echo '<div class="mb-4 text-green-600">' . $message . '</div>';
        }
        ?>
        <form action="register.php" method="post" class="space-y-4">
            <div class="mb-4">
                <label for="username" class="block text-gray-700 text-sm font-bold mb-2">Username:</label>
                <input type="text" id="username" name="username" class="border rounded w-full py-2 px-3 focus:outline-none focus:border-blue-500 transition duration-300" required>
            </div>

            <div class="mb-4">
                <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password:</label>
                <input type="password" id="password" name="password" class="border rounded w-full py-2 px-3 focus:outline-none focus:border-blue-500 transition duration-300" required>
            </div>

            <div class="mb-4">
                <label for="password_confirmation" class="block text-gray-700 text-sm font-bold mb-2">Confirm Password:</label>
                <input type="password" id="password_confirmation" name="password_confirmation" class="border rounded w-full py-2 px-3 focus:outline-none focus:border-blue-500 transition duration-300" required>
            </div>

            <button type="submit" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600 focus:outline-none transition duration-300 w-full">Register</button>

            <div class="text-center mt-4">
                <p class="text-sm">Already have an account? <a href="login.php" class="text-blue-500">Login here</a></p>
            </div>
        </form>
    </div>

    <!-- Optional: You may include your own scripts here -->
    <script>
        // Your custom JavaScript code here
        $(document).ready(function() {
            // Example: jQuery document ready function
        });
    </script>

</body>

</html>