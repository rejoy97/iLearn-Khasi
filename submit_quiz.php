<?php
session_start();
require 'db.php'; // DB connection

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['answers']) && isset($_POST['lesson_id'])) {
    $user_answers = $_POST['answers'];
    $quiz_ids = array_keys($user_answers);
    $lesson_id = intval($_POST['lesson_id']);
    $user_id = $_SESSION['user_id'];

    if (!empty($quiz_ids)) {
        // Get correct answers from database
        $placeholders = implode(',', array_fill(0, count($quiz_ids), '?'));
        $types = str_repeat('i', count($quiz_ids));
        $stmt = $conn->prepare("SELECT id, correct_option FROM quizzes WHERE id IN ($placeholders)");
        $stmt->bind_param($types, ...$quiz_ids);
        $stmt->execute();
        $result = $stmt->get_result();

        $correct_answers = [];
        while ($row = $result->fetch_assoc()) {
            $correct_answers[$row['id']] = $row['correct_option'];
        }

        // Prepare progress insert
        $insertProgress = $conn->prepare("INSERT INTO progress (user_id, lesson_id, quiz_id, user_answer, score, status) VALUES (?, ?, ?, ?, ?, 'completed')");

        $score = 0;
        foreach ($user_answers as $quiz_id => $user_answer) {
            $isCorrect = isset($correct_answers[$quiz_id]) && $correct_answers[$quiz_id] === $user_answer;
            $question_score = $isCorrect ? 1 : 0;
            $score += $question_score;

            $insertProgress->bind_param("iiisi", $user_id, $lesson_id, $quiz_id, $user_answer, $question_score);
            $insertProgress->execute();
        }

        // Check if quiz_results already exists
        $checkStmt = $conn->prepare("SELECT id FROM quiz_results WHERE user_id = ? AND lesson_id = ?");
        $checkStmt->bind_param("ii", $user_id, $lesson_id);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows === 0) {
            // Insert total score into quiz_results table
            $total_questions = count($quiz_ids);
            $insertSummary = $conn->prepare("INSERT INTO quiz_results (user_id, lesson_id, score, total_questions) VALUES (?, ?, ?, ?)");
            $insertSummary->bind_param("iiii", $user_id, $lesson_id, $score, $total_questions);
            $insertSummary->execute();
        }

        $_SESSION['score'] = $score;
        $message = "Your score is $score out of " . count($quiz_ids) . ".";
    }
}
?>
