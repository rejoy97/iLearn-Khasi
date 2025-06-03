<?php
session_start();
if (!isset($_SESSION["admin"])) {
    header("Location: admin_login.php");
    exit();
}

// Enable MySQLi error reporting (makes debugging easier)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli('localhost', 'root', '', 'ilearn_khasi');
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Handle deletion
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM lessons WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: manage_lessons.php?msg=deleted");
    exit();
}

// Handle insert or update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $category = $_POST['category'];

    if (!empty($_POST['id'])) {
        $id = intval($_POST['id']);
        $stmt = $conn->prepare("UPDATE lessons SET title = ?, content = ?, category = ? WHERE id = ?");
        $stmt->bind_param("sssi", $title, $content, $category, $id);
        $stmt->execute();
        header("Location: manage_lessons.php?msg=updated");
    } else {
        $stmt = $conn->prepare("INSERT INTO lessons (title, content, category) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $title, $content, $category);
        $stmt->execute();
        header("Location: manage_lessons.php?msg=added");
    }
    exit();
}

// For editing
$editLesson = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $res = $conn->query("SELECT * FROM lessons WHERE id=$id ");
    $editLesson = $res->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Lessons - iLearn Khasi</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7f9;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 900px;
            margin: auto;
            background: #fff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        h1, h2 {
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }
        a {
            color: #007BFF;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            font-weight: bold;
        }
        input[type="text"], textarea {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #218838;
        }
        .back-link {
            margin-top: 20px;
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📚 Manage Lessons</h1>

        <form method="POST">
            <input type="hidden" name="id" value="<?= $editLesson['id'] ?? '' ?>">

            <div class="form-group">
                <label for="title">Lesson Title:</label>
                <input type="text" name="title" id="title" required value="<?= $editLesson['title'] ?? '' ?>">
            </div>

            <div class="form-group">
                <label for="content">Lesson Content:</label>
                <textarea name="content" id="content" rows="5" required><?= $editLesson['content'] ?? '' ?></textarea>
            </div>

            <div class="form-group">
                <label for="category">Category:</label>
                <input type="text" name="category" id="category" required value="<?= $editLesson['category'] ?? '' ?>">
            </div>


            <button type="submit"><?= $editLesson ? 'Update Lesson' : 'Add Lesson' ?></button>
        </form>
        <?php if (isset($_GET['msg'])): ?>
            <p style="color: green;">
        <?php
            if ($_GET['msg'] == 'added') echo "✅ Lesson added successfully.";
            elseif ($_GET['msg'] == 'updated') echo "✅ Lesson updated successfully.";
            elseif ($_GET['msg'] == 'deleted') echo "🗑️ Lesson deleted.";
        ?>
         </p>
        <?php endif; ?>

        <h2>📄 Existing Lessons</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result = $conn->query("SELECT * FROM lessons ORDER BY id ASC");
                while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['title']) ?></td>
                        <td><?= htmlspecialchars($row['category']) ?></td>
                        <td>
                            <a href="?edit=<?= $row['id'] ?>">Edit</a> |
                            <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Delete this lesson?')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <a class="back-link" href="admin_dashboard.php">⬅️ Back to Dashboard</a>
    </div>
</body>
</html>
