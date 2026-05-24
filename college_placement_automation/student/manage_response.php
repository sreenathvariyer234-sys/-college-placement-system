<?php
// student/manage_response.php
require_once '../config/db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit;
}

$id = $_GET['id'] ?? null;
$action = $_GET['action'] ?? null; // 'accepted' or 'declined'

if (!$id || !in_array($action, ['accepted', 'declined'])) {
    header("Location: dashboard.php");
    exit;
}

// Verify ownership and status (only shortlisted students can accept/decline)
$stmt = $pdo->prepare("SELECT id FROM applications WHERE id = ? AND student_id = ? AND status = 'shortlisted'");
$stmt->execute([$id, $_SESSION['user_id']]);
if ($stmt->fetch()) {
    $stmt = $pdo->prepare("UPDATE applications SET status = ? WHERE id = ?");
    $stmt->execute([$action, $id]);
}

header("Location: dashboard.php");
exit;
