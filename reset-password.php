<?php
session_start();
require_once("db.php");

if (empty($_SESSION['reset_user_id'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $password = trim($_POST['password']);
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("UPDATE users SET pwd = ? WHERE id = ?");
    $stmt->bind_param("si", $hashedPassword, $_SESSION['reset_user_id']);

    if ($stmt->execute()) {
        unset($_SESSION['reset_user_id']);
        header("Location: index.php?reset=success");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
    <style>
        body {
            background: #f0f4f8;
            font-family: Arial, sans-serif;
            display: flex;
            height: 100vh;
            justify-content: center;
            align-items: center;
        }
        .container {
            background: #fff;
            text-align: center;
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        h2 {
            margin-bottom: 20px;
            text-align: center;
            color: #333;
        }
        label {
            display: block;
            margin-bottom: 6px;
            font-weight: bold;
            color: #444;
        }
        input[type="password"], button {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 16px;
        }
        button {
            background-color: #27ae60;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #1e8449;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Reset Your Password</h2>
    <form method="POST">
        <label for="password">New Password</label>
        <input type="password" name="password" required>
        <button type="submit">Reset Password</button>
        <a href="forgot-password.php">⬅️ Previous</a>
    </form>
</div>
</body>
</html>
