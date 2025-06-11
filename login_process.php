<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'db.php'; // Include your database connection

// Handle login form submission
if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Prepare and execute query
    $stmt = $conn->prepare("SELECT id, pwd FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $hashed_password);
        $stmt->fetch();
        echo "Entered: " . $password . "<br>";
        echo "Stored Hash: " . $hashed_password . "<br>";

        if (password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $id;
            $_SESSION['email'] = $email;
            header("Location: dashboard.php"); // ✅ Redirect to the dashboard
            exit();
        }
        

        // Verify password
        if (password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $id;
            $_SESSION['email'] = $email; // Optionally store email
            header("Location: dashboard.php"); // Redirect to dashboard
            exit();
        } else {
            header("Location: index.php?error=invalid_password"); // Incorrect password
            exit();
        }
    } else {
        // No user found with that email
        header("Location: index.php?error=no_user");
        exit();
    }

    $stmt->close();
}
$conn->close();
?>