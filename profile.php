<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT first_name, last_name, email, profile_picture FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
  <title>My Profile - iLearn Khasi</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f4f6f8;
      padding: 40px;
    }

    .container {
      max-width: 500px;
      margin: auto;
      background: white;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    h2 {
      text-align: center;
      margin-bottom: 20px;
    }

    .profile-pic {
      text-align: center;
      margin-bottom: 20px;
    }

    .profile-pic img {
      width: 120px;
      height: 120px;
      object-fit: cover;
      border-radius: 50%;
      border: 3px solid #007BFF;
    }

    .info {
      margin-bottom: 20px;
      text-align: center;
    }

    .info p {
      margin: 8px 0;
      font-size: 16px;
    }

    .actions {
      text-align: center;
    }

    .actions a, .actions button {
      display: inline-block;
      margin: 8px;
      padding: 10px 20px;
      border: none;
      border-radius: 6px;
      background: #007BFF;
      color: white;
      text-decoration: none;
      font-size: 14px;
      cursor: pointer;
    }

    .actions a:hover, .actions button:hover {
      background: #0056b3;
    }

    input[type="file"] {
      margin-top: 10px;
    }

  </style>
</head>
<body>

<div class="profile-pic">
  <form action="upload_profile_picture.php" method="post" enctype="multipart/form-data">
    <label for="fileInput" style="cursor: pointer; display: inline-block; position: relative;">
      <img src="/iLearnKhasi/<?= htmlspecialchars($user['profile_picture'] ?: 'default-avatar.png') ?>" 
           alt="Profile Picture" width="120" height="120" style="border-radius: 50%; border: 3px solid #007BFF;">
      <span style="position: absolute; bottom: 0; right: 0; background: #007BFF; color: white; border-radius: 50%; padding: 2px 6px; font-size: 20px;">+</span>
    </label>
    <input type="file" id="fileInput" name="profile_picture" accept="image/*" style="display: none;" onchange="document.getElementById('saveBtn').style.display = 'inline-block';">
    
    <div style="margin-top: 10px;">
      <button id="saveBtn" type="submit" style="display: none;">💾 Save</button>
      <?php if ($user['profile_picture'] !== 'default-avatar.png'): ?>
        <button type="submit" name="remove_picture" value="1" style="background: #dc3545;">🗑️ Remove</button>
      <?php endif; ?>
    </div>
  </form>
</div>


  <div class="info">
    <p><strong>Full Name:</strong> <?= htmlspecialchars($user['first_name']) . ' ' . htmlspecialchars($user['last_name']) ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
  </div>

  <div class="actions">
    <a href="change_password.php">🔑 Change Password</a>
    <a href="dashboard.php">← Back to Dashboard</a>
  </div>
</div>

</body>
</html>
