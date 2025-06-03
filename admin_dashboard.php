<?php
  session_start();
  if (!isset($_SESSION["admin"])) {
      header("Location: admin_login.php");
      exit();
  }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard - iLearn Khasi</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    body {
      background: #f0f4f8;
      color: #333;
      transition: background-color 0.3s, color 0.3s;
    }

    header {
      background-color: #ffffff;
      color: #000;
      padding: 15px 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.5);
    }

    header h1 {
      font-size: 24px;
      margin: 0;
      display: flex;
      align-items: center;
    }

    .toggle-container {
      display: flex;
      align-items: center;
    }

    #toggle-dark-mode {
      cursor: pointer;
      padding: 8px 15px;
      border: none;
      background-color: #000;
      color: #fff;
      border-radius: 5px;
      font-weight: bold;
      font-size: 14px;
    }

    main.dashboard {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 20px;
      padding: 40px 20px;
      max-width: 1000px;
      margin: 40px auto;
    }

    .tile {
      background: white;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.08);
      text-align: center;
      font-size: 18px;
      font-weight: 600;
      cursor: pointer;
      transition: 0.3s ease;
    }

    .tile:hover {
      background: #3498db;
      color: white;
      transform: translateY(-5px);
    }

    /* Dark mode styles */
    .dark-mode {
      background-color: #121212;
      color: #ffffff;
    }

    .dark-mode header {
      background-color: #1e1e1e;
      color: #ffffff;
    }

    .dark-mode .tile {
      background: #1e1e1e;
      color: #fff;
      box-shadow: 0 4px 8px rgba(255, 255, 255, 0.1);
    }

    .dark-mode .tile:hover {
      background: #3498db;
      color: white;
    }

    .dark-mode #toggle-dark-mode {
      background-color: #ffffff;
      color: #000;
    }

    @media (max-width: 600px) {
      .tile {
        font-size: 16px;
        padding: 20px;
      }
    }
  </style>
</head>
<body>

  <header>
    <h1>👨‍💻 Admin - iLearn Khasi</h1>
    <div class="toggle-container">
      <button id="toggle-dark-mode">Dark Mode</button>
    </div>
  </header>

  <main class="dashboard">
    <div class="tile" onclick="location.href='manage_lessons.php'">📚 Manage Lessons</div>
    <div class="tile" onclick="location.href='manage_quizzes.php'">📝 Manage Quizzes</div>
    <div class="tile" onclick="location.href='users.php'">👥 Manage Users</div>
    <div class="tile" onclick="location.href='admin_logout.php'">🚪 Logout</div>
  </main>

  <script>
    const toggleButton = document.getElementById('toggle-dark-mode');
    toggleButton.addEventListener('click', () => {
      document.body.classList.toggle('dark-mode');
    });
  </script>

</body>
</html>
