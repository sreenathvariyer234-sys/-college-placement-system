<?php
// admin/export_selected.php
require_once '../config/db.php';
session_start();

// Ensure only accepted admin roles can access
$allowed_roles = ['super_admin', 'college_po', 'dept_po'];
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], $allowed_roles)) {
    header("Location: ../login.php");
    exit;
}

$role = $_SESSION['role'];
$managed_dept = $_SESSION['managed_department'] ?? null;

// Prepare query for selected students (those who accepted an offer)
$query = "
    SELECT 
        u.full_name, 
        u.email, 
        u.department, 
        u.cgpa, 
        j.title as job_title, 
        j.company,
        a.applied_at,
        a.status
    FROM applications a
    JOIN users u ON a.student_id = u.id
    JOIN jobs j ON a.job_id = j.id
    WHERE a.status = 'accepted'
";

$params = [];

// Apply department filter for Dept POs
if ($role === 'dept_po' && $managed_dept) {
    $query .= " AND u.department = ?";
    $params = [$managed_dept];
}

$query .= " ORDER BY u.department ASC, u.full_name ASC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$selected_students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// If no results, we could redirect back with an error or just give an empty CSV
// Let's just generate the CSV.

$filename = "Selected_Students_" . ($managed_dept ? str_replace(' ', '_', $managed_dept) . "_" : "") . date('Y-m-d') . ".csv";

// Set headers for download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');

// Set CSV headers
fputcsv($output, ['Student Name', 'Email', 'Department', 'CGPA', 'Job Title', 'Company', 'Applied Date']);

// Populate CSV data
foreach ($selected_students as $student) {
    fputcsv($output, [
        $student['full_name'],
        $student['email'],
        $student['department'],
        $student['cgpa'],
        $student['job_title'],
        $student['company'],
        date('M d, Y', strtotime($student['applied_at']))
    ]);
}

fclose($output);
exit;
?>
