<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$category = isset($_GET['category']) ? $_GET['category'] : 'Basic Lessons';
$quizzes = [];
$hasCompleted = false;

// Check if user already completed this category
if (!empty($category)) {
    $stmt = $conn->prepare("SELECT 1 FROM completed_quizzes WHERE user_id = ? AND category = ?");
    $stmt->bind_param("is", $user_id, $category);
    $stmt->execute();
    $stmt->store_result();
    $hasCompleted = $stmt->num_rows > 0;
    $stmt->close();
}

// Fetch quizzes
if (!empty($category)) {
    $stmt = $conn->prepare("SELECT q.id, q.lesson_id, l.title, q.question, q.option_a, q.option_b, q.option_c, q.option_d, q.correct_option, q.category 
                            FROM quizzes q 
                            JOIN lessons l ON q.lesson_id = l.id 
                            WHERE q.category = ? 
                            ORDER BY q.id ASC");
    $stmt->bind_param("s", $category);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT q.id, q.lesson_id, l.title, q.question, q.option_a, q.option_b, q.option_c, q.option_d, q.correct_option, q.category 
                            FROM quizzes q 
                            JOIN lessons l ON q.lesson_id = l.id 
                            ORDER BY q.id ASC");
}

while ($row = $result->fetch_assoc()) {
    $quizzes[] = $row;
}

// Handle submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['answers']) && !$hasCompleted) {
    $user_answers = $_POST['answers'];
    $quiz_ids = array_keys($user_answers);

    if (!empty($quiz_ids)) {
        $placeholders = implode(',', array_fill(0, count($quiz_ids), '?'));
        $types = str_repeat('i', count($quiz_ids));
        $stmt = $conn->prepare("SELECT id, correct_option, lesson_id FROM quizzes WHERE id IN ($placeholders)");
        $stmt->bind_param($types, ...$quiz_ids);
        $stmt->execute();
        $result = $stmt->get_result();

        $correct_answers = [];
        $lesson_scores = [];

        while ($row = $result->fetch_assoc()) {
            $quiz_id = $row['id'];
            $correct_answers[$quiz_id] = $row['correct_option'];
            $lesson_id = $row['lesson_id'];

            if (!isset($lesson_scores[$lesson_id])) {
                $lesson_scores[$lesson_id] = ['score' => 0, 'total' => 0];
            }

            $lesson_scores[$lesson_id]['total']++;
            if ($correct_answers[$quiz_id] === $user_answers[$quiz_id]) {
                $lesson_scores[$lesson_id]['score']++;
            }
        }

        foreach ($lesson_scores as $lesson_id => $data) {
            $stmt = $conn->prepare("INSERT INTO quiz_results (user_id, lesson_id, score, total_questions, submitted_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->bind_param("iiii", $user_id, $lesson_id, $data['score'], $data['total']);
            $stmt->execute();
        }

        if (!empty($category)) {
            $stmt = $conn->prepare("INSERT INTO completed_quizzes (user_id, category, completed_at) VALUES (?, ?, NOW())");
            $stmt->bind_param("is", $user_id, $category);
            $stmt->execute();
        }

        $_SESSION['score'] = array_sum(array_column($lesson_scores, 'score'));
        $_SESSION['total'] = array_sum(array_column($lesson_scores, 'total'));
        header("Location: student_quizzes.php?category=" . urlencode($category));
        exit();
    }
}

$category_result = $conn->query("SELECT DISTINCT category FROM quizzes");
$categories = [];
while ($row = $category_result->fetch_assoc()) {
    $categories[] = $row['category'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Quizzes - iLearn Khasi</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f8;
            margin: 0;
            padding: 20px;
        }
        .container { max-width: 900px; margin: auto; }
        h1 { text-align: center; color: #222; }
        .back-btn {
            display: inline-block; padding: 10px 20px; margin-bottom: 20px;
            background-color: #000; color: #fff; text-decoration: none; border-radius: 5px;
        }
        .dropdown { margin: 20px 0; text-align: center; }
        select {
            padding: 10px; font-size: 16px; border-radius: 6px;
            border: 1px solid #ccc; background-color: #fff; cursor: pointer;
        }
        .quiz {
            background-color: #fff; border: 1px solid #ddd;
            padding: 20px; margin-bottom: 20px; border-radius: 10px;
        }
        .question { font-weight: bold; margin-bottom: 10px; }
        .option { margin: 5px 0 10px 20px; }
        button {
            display: block; margin: 20px auto; padding: 10px 25px;
            background-color: #000; color: #fff; border: none;
            border-radius: 6px; font-size: 16px; cursor: pointer;
        }
        .message {
            text-align: center; background: #e0ffe0; padding: 15px;
            border-radius: 10px; margin-bottom: 20px; color: #2c662d;
        }
        .alert {
            text-align: center; background: #ffe0e0; padding: 15px;
            border-radius: 10px; margin-bottom: 20px; color: #992d2d;
        }
    </style>
</head>
<body>
<div class="container">
    <a class="back-btn" href="dashboard.php">← Back to Dashboard</a>
    <h1>📝 Quizzes <?= !empty($category) ? "for '$category'" : '' ?></h1>

    <div class="dropdown">
        <form method="GET">
            <select name="category" onchange="this.form.submit()">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= htmlspecialchars($cat) ?>" <?= $category === $cat ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <?php if ($hasCompleted): ?>
        <div class="alert">You have already completed this category. Well done! ✅</div>
        <form action="unmark.php" method="POST" onsubmit="return confirm('Are you sure you want to restart this quiz?')">
            <input type="hidden" name="category" value="<?= htmlspecialchars($category) ?>">
            <button type="submit">Restart Quiz</button>
        </form>
    <?php elseif (isset($_SESSION['score'])): ?>
        <div class="message">🎉 Your score: <?= $_SESSION['score'] ?> out of <?= $_SESSION['total'] ?>.</div>
        <?php unset($_SESSION['score'], $_SESSION['total']); ?>
        <button type="button" onclick="window.location.reload();">Restart Quiz</button>
    <?php endif; ?>

    <form method="POST" action="student_quizzes.php">
        <?php if (count($quizzes) === 0): ?>
            <p style="text-align:center;">No quizzes found<?= !empty($category) ? " for '$category'" : ''; ?>.</p>
        <?php elseif (!$hasCompleted): ?>
            <?php foreach ($quizzes as $quiz): ?>
                <div class="quiz">
                    <div class="question">Lesson: <?= htmlspecialchars($quiz['title']) ?><br><?= htmlspecialchars($quiz['question']) ?></div>
                    <div class="option">
                        <input type="radio" name="answers[<?= $quiz['id'] ?>]" value="A" id="A_<?= $quiz['id'] ?>" required>
                        <label for="A_<?= $quiz['id'] ?>">A. <?= htmlspecialchars($quiz['option_a']) ?></label>
                    </div>
                    <div class="option">
                        <input type="radio" name="answers[<?= $quiz['id'] ?>]" value="B" id="B_<?= $quiz['id'] ?>">
                        <label for="B_<?= $quiz['id'] ?>">B. <?= htmlspecialchars($quiz['option_b']) ?></label>
                    </div>
                    <div class="option">
                        <input type="radio" name="answers[<?= $quiz['id'] ?>]" value="C" id="C_<?= $quiz['id'] ?>">
                        <label for="C_<?= $quiz['id'] ?>">C. <?= htmlspecialchars($quiz['option_c']) ?></label>
                    </div>
                    <div class="option">
                        <input type="radio" name="answers[<?= $quiz['id'] ?>]" value="D" id="D_<?= $quiz['id'] ?>">
                        <label for="D_<?= $quiz['id'] ?>">D. <?= htmlspecialchars($quiz['option_d']) ?></label>
                    </div>
                </div>
            <?php endforeach; ?>
            <button type="submit">Submit Answers</button>
        <?php endif; ?>
    </form>
</div>
</body>
</html>