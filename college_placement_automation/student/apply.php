<?php
// student/apply.php
require_once '../config/db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['job_id'])) {
    $student_id = $_SESSION['user_id'];
    $job_id = $_POST['job_id'];

    // Double check if already applied
    $stmt = $pdo->prepare("SELECT id FROM applications WHERE student_id = ? AND job_id = ?");
    $stmt->execute([$student_id, $job_id]);
    
    if (!$stmt->fetch()) {
        $stmt = $pdo->prepare("INSERT INTO applications (student_id, job_id) VALUES (?, ?)");
        $stmt->execute([$student_id, $job_id]);
    }
}

header("Location: dashboard.php");
exit;
?>
