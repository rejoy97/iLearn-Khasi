<?php
include('db.php');

$title = $_POST['title'];
$lesson_id = $_POST['lesson_id'];
$category = $_POST['category'];
$question = $_POST['question'];
$option_a = $_POST['option_a'];
$option_b = $_POST['option_b'];
$option_c = $_POST['option_c'];
$option_d = $_POST['option_d'];
$correct_option = $_POST['correct_option'];

$stmt = $conn->prepare("INSERT INTO quizzes (title, lesson_id, category, question, option_a, option_b, option_c, option_d, correct_option) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sisssssss", $title, $lesson_id, $category, $question, $option_a, $option_b, $option_c, $option_d, $correct_option);

if ($stmt->execute()) {
    header("Location: manage_quizzes.php");
} else {
    echo "Error: " . $stmt->error;
}
?>
