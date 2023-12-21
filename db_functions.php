<?php
function dbconn()
{
    $db_server = "127.0.0.1";
    $db_username = "root";
    $db_password = "";
    $db_database = "dbpweb#";
    $conn = mysqli_connect($db_server, $db_username, $db_password, $db_database);

    if (!$conn) {
        error_log("Database connection error: " . mysqli_connect_error());
        return null;
    }

    return $conn;
}
