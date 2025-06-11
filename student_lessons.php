<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$categoryFilter = isset($_GET['category']) ? $_GET['category'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lesson_id = $_POST['lesson_id'];

    if (isset($_POST['complete_lesson'])) {
        $stmt = $conn->prepare("INSERT INTO progress (user_id, lesson_id, status, completed_at) VALUES (?, ?, 'completed', NOW()) ON DUPLICATE KEY UPDATE status = 'completed', completed_at = NOW()");
        $stmt->bind_param("ii", $user_id, $lesson_id);
        $stmt->execute();
    }

    if (isset($_POST['mark_incomplete'])) {
        $stmt = $conn->prepare("DELETE FROM progress WHERE user_id = ? AND lesson_id = ?");
        $stmt->bind_param("ii", $user_id, $lesson_id);
        $stmt->execute();
    }
}

// Get lessons
if ($categoryFilter) {
    $stmt = $conn->prepare("SELECT id, title, content, audio_path FROM lessons WHERE category = ? ORDER BY id ASC");
    $stmt->bind_param("s", $categoryFilter);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = mysqli_query($conn, "SELECT id, title, content, audio_path FROM lessons ORDER BY id ASC");
}

$lessons = [];
while ($row = mysqli_fetch_assoc($result)) {
    $lessons[] = $row;
}

$currentIndex = isset($_GET['index']) ? (int)$_GET['index'] : 0;
$totalLessons = count($lessons);
$currentLesson = $lessons[$currentIndex] ?? null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Lessons - iLearn Khasi</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f8;
            margin: 0;
            padding: 20px;
        }
        .lesson-container {
            max-width: 900px;
            margin: auto;
        }
        h1 {
            text-align: center;
            color: #222;
        }
        .dropdown, .jump-form {
            margin: 10px 0;
            text-align: center;
        }
        select, .nav-button {
            padding: 10px;
            font-size: 16px;
            border-radius: 6px;
            border: 1px solid #ccc;
            background-color: #fff;
            cursor: pointer;
            margin: 5px;
        }
        .lesson {
            background-color: #ffffff;
            border: 1px solid #ddd;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        .complete-btn {
            margin-top: 15px;
            padding: 8px 15px;
            background-color: #2ecc71;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
        .complete-btn:hover {
            background-color: #27ae60;
        }
        .incomplete-btn {
            background-color: #e74c3c;
        }
        .incomplete-btn:hover {
            background-color: #c0392b;
        }
        .back-dashboard {
            background-color: #555;
            color: white;
            padding: 10px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 20px;
        }
        .audio-controls button {
            padding: 8px 14px;
            margin: 5px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
        .audio-controls button:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>

<div class="lesson-container">
    <a class="back-dashboard" href="dashboard.php">← Back to Dashboard</a>

    <h1>📚 Your Lessons</h1>

    <div class="dropdown">
        <form method="get" action="">
            <select name="category" onchange="this.form.submit()">
                <option value="">All Categories</option>
                <option value="Basic Lessons" <?= $categoryFilter === 'Basic Lessons' ? 'selected' : '' ?>>Basic Lessons</option>
                <option value="Beginner Level" <?= $categoryFilter === 'Beginner Level' ? 'selected' : '' ?>>Beginner Level</option>
                <option value="Intermediate Level" <?= $categoryFilter === 'Intermediate Level' ? 'selected' : '' ?>>Intermediate Level</option>
            </select>
        </form>
    </div>

    <?php if ($currentLesson): ?>
        <div class="lesson">
            <h2><?= htmlspecialchars($currentLesson['title']) ?></h2>
            <p><?= nl2br(htmlspecialchars($currentLesson['content'])) ?></p>

            <?php if (!empty($currentLesson['audio_path'])): ?>
                <div class="audio-controls">
                    <audio id="audioPlayer" src="<?= 'audio/' . htmlspecialchars($currentLesson['audio_path']) ?>"></audio>
                    <button onclick="playAudio()">🔊 Play</button>
                    <button onclick="pauseAudio()">⏸️ Pause</button>
                    <button onclick="stopAudio()">⏹️ Stop</button>
                </div>
            <?php endif; ?>

            <form method="post">
                <input type="hidden" name="lesson_id" value="<?= $currentLesson['id'] ?>">
                <?php
                $check = $conn->prepare("SELECT id FROM progress WHERE user_id = ? AND lesson_id = ?");
                $check->bind_param("ii", $user_id, $currentLesson['id']);
                $check->execute();
                $checkResult = $check->get_result();
                $isCompleted = $checkResult->num_rows > 0;
                ?>
                <?php if ($isCompleted): ?>
                    <button type="submit" name="mark_incomplete" class="complete-btn incomplete-btn">❌ Mark as Incomplete</button>
                <?php else: ?>
                    <button type="submit" name="complete_lesson" class="complete-btn">✅ Mark as Completed</button>
                <?php endif; ?>
            </form>
        </div>

        <div style="text-align: center;">
            <?php if ($currentIndex > 0): ?>
                <a href="?index=<?= $currentIndex - 1 ?><?= $categoryFilter ? '&category=' . urlencode($categoryFilter) : '' ?>" class="nav-button">⬅️ Previous</a>
            <?php endif; ?>
            <?php if ($currentIndex < $totalLessons - 1): ?>
                <a href="?index=<?= $currentIndex + 1 ?><?= $categoryFilter ? '&category=' . urlencode($categoryFilter) : '' ?>" class="nav-button">Next ➡️</a>
            <?php endif; ?>
        </div>

        <div class="jump-form">
            <form method="get" action="">
                <input type="hidden" name="category" value="<?= htmlspecialchars($categoryFilter) ?>">
                <select name="index" onchange="this.form.submit()">
                    <option disabled selected>Jump to a lesson</option>
                    <?php foreach ($lessons as $i => $lesson): ?>
                        <option value="<?= $i ?>">Lesson <?= $i + 1 ?>: <?= htmlspecialchars($lesson['title']) ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>

    <?php else: ?>
        <p style="text-align:center;">No lessons found for this category.</p>
    <?php endif; ?>
</div>

<script>
    const audioPlayer = document.getElementById('audioPlayer');

    function playAudio() {
        audioPlayer.play();
    }

    function pauseAudio() {
        audioPlayer.pause();
    }

    function stopAudio() {
        audioPlayer.pause();
        audioPlayer.currentTime = 0;
    }
</script>

</body>
</html>
