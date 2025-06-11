<?php 
session_start();
require 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get total lessons
$totalLessonsQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM lessons");
$totalLessons = mysqli_fetch_assoc($totalLessonsQuery)['total'];

// Get completed lessons
$completedLessonsQuery = mysqli_query($conn, "SELECT COUNT(*) AS completed FROM progress WHERE user_id = $user_id AND status = 'completed'");
$completedLessons = mysqli_fetch_assoc($completedLessonsQuery)['completed'];

// Get total quizzes
$totalQuizzesQuery = mysqli_query($conn, "SELECT COUNT(DISTINCT lesson_id) AS total FROM quizzes");
$totalQuizzes = mysqli_fetch_assoc($totalQuizzesQuery)['total'];

// Get attempted quizzes
$attemptedQuizzesQuery = mysqli_query($conn, "SELECT COUNT(DISTINCT lesson_id) AS attempted FROM quiz_results WHERE user_id = $user_id");
$attemptedQuizzes = mysqli_fetch_assoc($attemptedQuizzesQuery)['attempted'];

// Get quiz scores
$quizScoresQuery = mysqli_query($conn, "SELECT lesson_id, score, total_questions FROM quiz_results WHERE user_id = $user_id");

// Handle error if query fails
if (!$quizScoresQuery) {
    die("Error fetching quiz scores: " . mysqli_error($conn));
}

// Progress percentage
$totalItems = $totalLessons + $totalQuizzes;
$completedItems = $completedLessons + $attemptedQuizzes;
$progressPercent = $totalItems > 0 ? round(($completedItems / $totalItems) * 100) : 0;
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Progress</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 30px;
            background-color: #f9f9f9;
        }
        h2 {
            color: #333;
        }
        .progress-container {
            background: #ddd;
            border-radius: 20px;
            overflow: hidden;
            height: 25px;
            margin: 15px 0;
        }
        .progress-bar {
            background-color: #4caf50;
            height: 100%;
            width: <?= $progressPercent ?>%;
            color: #fff;
            text-align: center;
            line-height: 25px;
            transition: width 0.5s;
        }
        .section {
            background: #fff;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }
        ul {
            padding-left: 20px;
        }
        .back-btn {
            display: inline-block;
            padding: 10px 20px;
            margin-bottom: 20px;
            background-color: #000;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .back-btn:hover {
            background-color: #444;
        }
    </style>
</head>
<body>

<div class="section">
    <a class="back-btn" href="dashboard.php">← Back to Dashboard</a>
    <h2>📊 Overall Progress</h2>
    <p>✅ Lessons Completed: <strong><?= $completedLessons ?> / <?= $totalLessons ?></strong></p>
    <p>🧠 Quizzes Attempted: <strong><?= $attemptedQuizzes ?> / <?= $totalQuizzes ?></strong></p>

    <div class="progress-container">
        <div class="progress-bar"><?= $progressPercent ?>%</div>
    </div>
</div>

<div class="section">
    <h2>📋 Quiz Scores</h2>
    <?php if (mysqli_num_rows($quizScoresQuery) > 0): ?>
        <ul>
            <?php while($row = mysqli_fetch_assoc($quizScoresQuery)): ?>
                <li>Lesson <?= $row['lesson_id'] ?>: <strong><?= $row['score'] ?>/<?= $row['total_questions'] ?></strong></li>
            <?php endwhile; ?>
        </ul>
    <?php else: ?>
        <p>You haven't attempted any quizzes yet.</p>
    <?php endif; ?>
</div>

<!-- ✅ New Category-wise Quiz Progress Section -->
<div class="section">
    <h2>📚 Quiz Progress by Category</h2>
    <?php
    $categoryProgressQuery = mysqli_query($conn, "
        SELECT 
            l.category,
            COUNT(DISTINCT q.lesson_id) AS total_quizzes,
            COUNT(DISTINCT qr.lesson_id) AS attempted_quizzes
        FROM lessons l
        LEFT JOIN quizzes q ON l.id = q.lesson_id
        LEFT JOIN quiz_results qr ON qr.lesson_id = l.id AND qr.user_id = $user_id
        GROUP BY l.category
        ORDER BY l.category
    ");

    if (mysqli_num_rows($categoryProgressQuery) > 0):
    ?>
        <ul>
            <?php while ($catRow = mysqli_fetch_assoc($categoryProgressQuery)): ?>
                <li>
                    <strong><?= htmlspecialchars($catRow['category']) ?></strong>: 
                    <?= $catRow['attempted_quizzes'] ?> / <?= $catRow['total_quizzes'] ?> quizzes attempted
                </li>
            <?php endwhile; ?>
        </ul>
    <?php else: ?>
        <p>No quiz data found by category.</p>
    <?php endif; ?>
</div>

</body>
</html>
