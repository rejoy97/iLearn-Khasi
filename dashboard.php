<?php
session_start();
include 'db.php';

// Enforce strict session validation
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Optional: Regenerate session ID for added security
session_regenerate_id(true);

$user_id = $_SESSION['user_id'];
$conn->query("UPDATE users SET last_activity = NOW() WHERE id = $user_id");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>iLearn Khasi - Dashboard</title>
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-image: url('Weisawdong.jpg');
      background-repeat: no-repeat;
      background-position: center;
      background-size: 1000px 650px;
      margin: 0;
      padding: 0;
      background-color: #f0f0f0;
      color: lightblue;
      display: flex;
      transition: background-color 0.3s, color 0.3s;
    }

    .menu-button {
      font-size: 24px;
      background: none;
      border: 2px solid #31859e;
      color: #31859e;
      padding: 8px 12px;
      border-radius: 8px;
      cursor: pointer;
      margin: 10px;
      transition: background-color 0.3s, color 0.3s;
      z-index: 1001;
    }

    .menu-button:hover {
      background-color: #31859e;
      color: white;
    }

    .sidebar {
      width: 200px;
      height: 100%;
      background: #000;
      color: white;
      padding: 20px 10px;
      position: fixed;
      top: 0;
      left: 0;
      transform: translateX(-100%);
      transition: transform 0.3s ease;
      display: flex;
      flex-direction: column;
      gap: 20px;
      z-index: 1000;
    }

    .sidebar.active {
      transform: translateX(0);
    }

    .sidebar a {
      color: white;
      text-decoration: none;
      font-size: 18px;
      padding: 12px;
      border-radius: 8px;
      transition: background 0.3s;
    }

    .sidebar a:hover {
      background: #555;
    }

    .dark-mode {
      background-color: #000;
      color: lightgreen;
    }

    .dark-mode .sidebar {
      background: #222;
    }

    .dark-mode .sidebar a {
      color: #9effa9; /* Light green text in light mode */
    }

    .container {
      flex-grow: 1;
      display: flex;
      flex-direction: column;
      margin-left: 0;
      width: 100%;
      min-height: 100vh;
    }

    header {
      background-color: rgb(13, 178, 219);
      color: #000;
      padding: 10px 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 2px 5px rgb(255, 253, 253);
    }

    .header-left h1 {
      font-size: 24px;
      margin: 0;
    }

    .header-right {
      display: flex;
      gap: 12px;
      align-items: center;
    }

    .dark-mode-btn,
    #audio-control {
      background-color: transparent;
      border: 2px solid #000;
      color: #000;
      padding: 6px 10px;
      font-size: 18px;
      border-radius: 50%;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    #audio-control {
      border-radius: 6px;
      font-size: 16px;
    }

    .dark-mode .dark-mode-btn,
    .dark-mode #audio-control {
      color: #fff;
      border-color: #fff;
    }

    .main-content {
      text-align: right;
      padding: 50px 220px;
      margin-top: 60px;
      max-width: 700px;
    }

    #logout-button {
      margin-top: 20px;
      padding: 10px 20px;
      border: none;
      background-color: red;
      color: white;
      font-weight: bold;
      border-radius: 8px;
      cursor: pointer;
    }

    .hidden { display: none; }

    @media (max-width: 768px) {
      .sidebar { width: 70%; }
      .menu-button { margin: 10px 15px; }
      header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
      }
      .header-right {
        align-self: flex-end;
      }
    }

    @media (max-width: 480px) {
      .sidebar { width: 80%; }
      .main-content p { font-size: 16px; }
    }
  </style>
</head>
<body>

<!-- Menu Button -->
<button class="menu-button" id="menu-btn" onclick="toggleSidebar()">☰ Menu</button>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
  <h2>Welcome to iLearn Khasi</h2>
  <a href="student_lessons.php">📚 Lessons</a>
  <a href="student_quizzes.php">📝 Quizzes</a>
  <a href="profile.php">👤 Profile</a>
  <a href="progress.php">📊 View My Progress</a>
  <form action="logout.php" method="post" onsubmit="return confirm('Are you sure you want to log out?');">
    <button type="submit" id="logout-button">🚪 Logout</button>
  </form>

</div>

<!-- Main Container -->
<div class="container">
  <header>
    <div class="header-left">
      <h1>iLearn Khasi</h1>
    </div>
    <div class="header-right">
      <button id="toggle-dark-mode" class="dark-mode-btn" title="Toggle Dark Mode"><i class="fas fa-moon"></i></button>
      <button id="audio-control">🔊 Stop Music</button>
    </div>
  </header>

  <div class="main-content">
    <h2>Welcome back! Ready to learn? 😎</h2>
  </div>
</div>

<!-- Audio Element -->
<audio id="theme-audio" autoplay loop muted>
  <source src="Khasi Theme.mp3" type="audio/mpeg">
  Your browser does not support the audio element.
</audio>

<!-- JS Scripts -->
<script>
  const toggleBtn = document.getElementById('toggle-dark-mode');
  const icon = toggleBtn.querySelector('i');
  const sidebar = document.getElementById('sidebar');
  const menuBtn = document.getElementById('menu-btn');

  toggleBtn.addEventListener('click', () => {
    document.body.classList.toggle('dark-mode');
    icon.classList.toggle('fa-moon');
    icon.classList.toggle('fa-sun');
  });

  function toggleSidebar() {
    sidebar.classList.toggle('active');
    menuBtn.classList.toggle('hidden');
  }

  document.addEventListener("click", function(event) {
    if (!sidebar.contains(event.target) && !menuBtn.contains(event.target)) {
      if (sidebar.classList.contains("active")) {
        sidebar.classList.remove("active");
        menuBtn.classList.remove("hidden");
      }
    }
  });

  document.getElementById("logout-button").addEventListener("click", function () {
    if (confirm("Are you sure you want to log out?")) {
      window.location.href = "logout.php";
    }
  });

  const audio = document.getElementById('theme-audio');
  const audioBtn = document.getElementById('audio-control');

  window.addEventListener('load', () => {
    setTimeout(() => {
      audio.muted = false;
      audio.play().catch(() => {});
    }, 500);
  });

  let isPlaying = true;
  audioBtn.addEventListener('click', () => {
    if (isPlaying) {
      audio.pause();
      audioBtn.textContent = '▶️ Play Music';
    } else {
      audio.play();
      audioBtn.textContent = '🔊 Stop Music';
    }
    isPlaying = !isPlaying;
  });
</script>
</body>
</html>
