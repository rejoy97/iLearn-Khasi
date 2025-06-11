<?php
require 'db.php';
session_start();

$user_id = $_SESSION['user_id'] ?? 0;
$category = $_POST['category'] ?? '';

if ($user_id && $category) {
    // Delete from completed_quizzes
    $stmt = $conn->prepare("DELETE FROM completed_quizzes WHERE user_id = ? AND category = ?");
    $stmt->bind_param("is", $user_id, $category);
    $stmt->execute();
    $stmt->close();

    // Get all quiz IDs in this category
    $quizIds = [];
    $qstmt = $conn->prepare("SELECT id FROM quizzes WHERE category = ?");
    $qstmt->bind_param("s", $category);
    $qstmt->execute();
    $result = $qstmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $quizIds[] = $row['id'];
    }
    $qstmt->close();

    // Delete attempts related to this quiz
    if (!empty($quizIds)) {
        $placeholders = implode(',', array_fill(0, count($quizIds), '?'));
        $types = str_repeat('i', count($quizIds) + 1); // +1 for user_id
        $sql = "DELETE FROM quiz_attempts WHERE user_id = ? AND quiz_id IN ($placeholders)";
        $stmt = $conn->prepare($sql);

        $params = array_merge([$user_id], $quizIds);
        $bindNames[] = $types;
        foreach ($params as $key => $value) {
            $bindNames[] = &$params[$key];
        }

        call_user_func_array([$stmt, 'bind_param'], $bindNames);
        $stmt->execute();
        $stmt->close();
    }

    // Optionally delete quiz_results too
    $stmt = $conn->prepare("DELETE FROM quiz_results WHERE user_id = ? AND lesson_id IN (SELECT id FROM lessons WHERE category = ?)");
    $stmt->bind_param("is", $user_id, $category);
    $stmt->execute();
    $stmt->close();
}

header("Location: quiz.php?category=" . urlencode($category));
exit;
?>
