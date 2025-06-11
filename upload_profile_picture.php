<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Handle remove picture
if (isset($_POST['remove_picture'])) {
    $stmt = $conn->prepare("UPDATE users SET profile_picture = 'default-avatar.png' WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    header("Location: profile.php");
    exit;
}

// Handle upload
if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === 0) {
    $target_dir = "uploads/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true); // Create uploads folder if not exist
    }

    $file_name = basename($_FILES["profile_picture"]["name"]);
    $target_file = $target_dir . time() . "_" . $file_name;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Validate image
    $check = getimagesize($_FILES["profile_picture"]["tmp_name"]);
    if ($check !== false && in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
        // Optional: delete old picture (not default)
        $result = $conn->query("SELECT profile_picture FROM users WHERE id = $user_id");
        $row = $result->fetch_assoc();
        if ($row['profile_picture'] !== 'default-avatar.png' && file_exists($row['profile_picture'])) {
            unlink($row['profile_picture']);
        }

        // Upload and update DB
        if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
            $stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
            $stmt->bind_param("si", $target_file, $user_id);
            $stmt->execute();
        }
    }
}

header("Location: profile.php");
exit;
