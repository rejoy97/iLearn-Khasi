<?php

$host = "localhost";
$user = "root"; // Default XAMPP user
$password = ""; // Default XAMPP password is empty
$database = "ilearn_khasi"; // Change this if you have a different DB name

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>
