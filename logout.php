<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_unset();           // Clear session variables
    session_destroy();         // End session

    // Redirect to login page
    header("Location: index.php");
    exit();
} else {
    // If someone tries to access logout.php directly via GET, redirect them back
    header("Location: dashboard.php");
    exit();
}
?>