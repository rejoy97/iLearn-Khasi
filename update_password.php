<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $token = $_POST['token'];
    $newPassword = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
    $currentTime = date("U");

    $stmt = $conn->prepare("SELECT * FROM users WHERE email=? AND reset_token=? AND token_expire >= ?");
    $stmt->bind_param("ssi", $email, $token, $currentTime);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $stmt = $conn->prepare("UPDATE users SET password=?, reset_token=NULL, token_expire=NULL WHERE email=?");
        $stmt->bind_param("ss", $newPassword, $email);
        $stmt->execute();

        echo "Password reset successfully. <a href='login.php'>Login</a>";
    } else {
        echo "Invalid request.";
    }
}
?>
