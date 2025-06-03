<?php
session_start();
if (!isset($_SESSION["admin"])) {
    header("Location: admin_login.php");
    exit();
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $conn = new mysqli('localhost', 'root', '', 'ilearn_khasi');
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM quizzes WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: manage_quizzes.php?msg=deleted");
    exit();
}

// Handle add/update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lesson_id = $_POST['lesson_id'];
    $question = $_POST['question'];
    $option_a = $_POST['option_a'];
    $option_b = $_POST['option_b'];
    $option_c = $_POST['option_c'];
    $option_d = $_POST['option_d'];
    $correct_option = $_POST['correct_option'];
    $category = $_POST['category'];

    if (!empty($_POST['id'])) {
        $id = intval($_POST['id']);
        $stmt = $conn->prepare("UPDATE quizzes SET lesson_id=?, question=?, option_a=?, option_b=?, option_c=?, option_d=?, correct_option=?, category=? WHERE id=?");
        $stmt->bind_param("isssssssi", $lesson_id, $question, $option_a, $option_b, $option_c, $option_d, $correct_option, $category, $id);
        $stmt->execute();
        header("Location: manage_quizzes.php?msg=updated");
    } else {
        $stmt = $conn->prepare("INSERT INTO quizzes (lesson_id, question, option_a, option_b, option_c, option_d, correct_option, category) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssssss", $lesson_id, $question, $option_a, $option_b, $option_c, $option_d, $correct_option, $category);
        $stmt->execute();
        header("Location: manage_quizzes.php?msg=added");
    }
    exit();
}

// For editing
$editQuiz = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $res = $conn->query("SELECT * FROM quizzes WHERE id=$id");
    $editQuiz = $res->fetch_assoc();
}

// Get all lessons for dropdown
$lessons = $conn->query("SELECT * FROM lessons ORDER BY title ASC");
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Manage Quizzes - iLearn Khasi</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { font-family: 'Segoe UI', Tahoma; background-color: #f4f7f9; padding: 20px; }
        .container { max-width: 900px; margin: auto; background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 0 15px rgba(0,0,0,0.1); }
        h1, h2 { color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; border-bottom: 1px solid #ddd; text-align: left; }
        a { color: #007BFF; text-decoration: none; }
        a:hover { text-decoration: underline; }
        .form-group { margin-bottom: 15px; }
        label { font-weight: bold; }
        input[type="text"], select, textarea { width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ccc; border-radius: 5px; }
        button { background-color: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background-color: #218838; }
        .back-link { margin-top: 20px; display: inline-block; }
    </style>
</head>
<body>
<div class="container">
    <h1>📝 Manage Quizzes</h1>

    <form method="POST">
        <input type="hidden" name="id" value="<?= $editQuiz['id'] ?? '' ?>">

        <div class="form-group">
            <label for="lesson_id">Select Lesson:</label>
            <select name="lesson_id" id="lesson_id" required>
                <option value="">-- Choose Lesson --</option>
                <?php while ($lesson = $lessons->fetch_assoc()): ?>
                    <option value="<?= $lesson['id'] ?>" <?= (isset($editQuiz) && $editQuiz['lesson_id'] == $lesson['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($lesson['title']) ?> (<?= $lesson['category'] ?>)
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="question">Question:</label>
            <textarea name="question" id="question" rows="3" required><?= $editQuiz['question'] ?? '' ?></textarea>
        </div>

        <div class="form-group">
            <label>Options:</label>
            <input type="text" name="option_a" placeholder="Option A" required value="<?= $editQuiz['option_a'] ?? '' ?>">
            <input type="text" name="option_b" placeholder="Option B" required value="<?= $editQuiz['option_b'] ?? '' ?>">
            <input type="text" name="option_c" placeholder="Option C" required value="<?= $editQuiz['option_c'] ?? '' ?>">
            <input type="text" name="option_d" placeholder="Option D" required value="<?= $editQuiz['option_d'] ?? '' ?>">
        </div>

        <div class="form-group">
            <label for="correct_option">Correct Option (A/B/C/D):</label>
            <input type="text" name="correct_option" id="correct_option" maxlength="1" required value="<?= $editQuiz['correct_option'] ?? '' ?>">
        </div>

        <div class="form-group">
            <label for="category">Category:</label>
            <input type="text" name="category" id="category" required value="<?= $editQuiz['category'] ?? '' ?>">
        </div>

        <button type="submit"><?= $editQuiz ? 'Update Quiz' : 'Add Quiz' ?></button>
    </form>

    <?php if (isset($_GET['msg'])): ?>
        <p style="color: green;">
            <?= $_GET['msg'] == 'added' ? '✅ Quiz added successfully.' : ($_GET['msg'] == 'updated' ? '✅ Quiz updated.' : '🗑️ Quiz deleted.') ?>
        </p>
    <?php endif; ?>

    <h2>📋 Existing Quizzes</h2>
    <table>
        <thead>
        <tr>
            <th>ID</th>
            <th>Lesson</th>
            <th>Category</th>
            <th>Question</th>
            <th>Correct</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $result = $conn->query("SELECT quizzes.*, lessons.title AS lesson_title FROM quizzes JOIN lessons ON quizzes.lesson_id = lessons.id ORDER BY quizzes.id ASC");
        while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['lesson_title']) ?></td>
                <td><?= htmlspecialchars($row['category']) ?></td>
                <td><?= htmlspecialchars($row['question']) ?></td>
                <td><?= $row['correct_option'] ?></td>
                <td>
                    <a href="?edit=<?= $row['id'] ?>">Edit</a> |
                    <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Delete this quiz?')">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>

    <a class="back-link" href="admin_dashboard.php">⬅️ Back to Dashboard</a>
</div>
</body>
</html>
