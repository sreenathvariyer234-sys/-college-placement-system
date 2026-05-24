<?php
require_once '../config/db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'company') {
    header("Location: ../login.php");
    exit;
}

$id = $_REQUEST['id'] ?? null;
$action = $_REQUEST['action'] ?? null;
$job_id = $_REQUEST['job_id'] ?? null;

if (!$id || !$action || !$job_id) {
    header("Location: dashboard.php");
    exit;
}

// Verify identity through job ownership
$stmt = $pdo->prepare("SELECT a.student_id, j.title FROM applications a JOIN jobs j ON a.job_id = j.id WHERE a.id = ? AND j.company_id = ?");
$stmt->execute([$id, $_SESSION['user_id']]);
$record = $stmt->fetch();

if ($record) {
    if ($action === 'schedule_interview') {
        $date = $_POST['interview_date'];
        $time = $_POST['interview_time'];
        
        $stmt = $pdo->prepare("UPDATE applications SET status = 'interview_scheduled', interview_date = ?, interview_time = ? WHERE id = ?");
        if ($stmt->execute([$date, $time, $id])) {
            $message = "Interview scheduled for '" . $record['title'] . "' on " . date('M d', strtotime($date)) . " at " . date('h:i A', strtotime($time)) . ".";
            $stmt = $pdo->prepare("INSERT INTO notifications (user_id, message, type) VALUES (?, ?, 'interview')");
            $stmt->execute([$record['student_id'], $message]);
        }
    } else {
        // Handle generic status updates (rejected, shortlisted, etc.)
        $stmt = $pdo->prepare("UPDATE applications SET status = ? WHERE id = ?");
        if ($stmt->execute([$action, $id])) {
            $display_status = str_replace('_', ' ', $action);
            $message = "Your application for '" . $record['title'] . "' has been " . $display_status . ".";
            $stmt = $pdo->prepare("INSERT INTO notifications (user_id, message, type) VALUES (?, ?, 'status_update')");
            $stmt->execute([$record['student_id'], $message]);
        }
    }
}

header("Location: view_applications.php?job_id=" . $job_id);
exit;
