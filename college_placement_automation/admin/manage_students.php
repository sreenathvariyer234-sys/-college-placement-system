<?php
// admin/manage_students.php
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

// Handle student deletion (only for higher admins maybe, but let's allow Dept PO too for their own students)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_student_id'])) {
    $student_id_to_delete = $_POST['delete_student_id'];

    try {
        // Double check department if they are a Dept PO
        if ($role === 'dept_po') {
            $check_stmt = $pdo->prepare("SELECT department FROM users WHERE id = ? AND role = 'student'");
            $check_stmt->execute([$student_id_to_delete]);
            $student_dept = $check_stmt->fetchColumn();

            if ($student_dept !== $managed_dept) {
                throw new Exception("Unauthorized deletion attempt.");
            }
        }

        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'student'");
        $stmt->execute([$student_id_to_delete]);
        $success_message = "Student account has been successfully removed.";
    } catch (Exception $e) {
        $error_message = $e->getMessage();
        error_log("Student deletion error: " . $e->getMessage());
    }
}

// Fetch students with filtering
$query = "SELECT id, full_name, email, department, cgpa, semester, passing_year, created_at FROM users WHERE role = 'student'";
$params = [];

if ($role === 'dept_po' && $managed_dept) {
    $query .= " AND department = ?";
    $params = [$managed_dept];
}

$query .= " ORDER BY created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$students = $stmt->fetchAll();
?>

<?php include '../includes/header.php'; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 3rem;">
    <div>
        <h1>Manage Students</h1>
        <div style="font-size: 0.9rem; color: #94a3b8; margin-top: 0.5rem;">
            <?php echo ($role === 'dept_po') ? "List of students in " . htmlspecialchars($managed_dept) : "View and manage all registered students"; ?>
        </div>
    </div>
    <div style="display: flex; gap: 1rem;">
        <a href="export_selected.php" class="btn"
            style="background: rgba(16, 185, 129, 0.2); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3);"><i
                class="fas fa-file-export"></i> Export Selected</a>
        <a href="dashboard.php" class="btn" style="background: rgba(255,255,255,0.05); color: white;">&larr; Back to
            Dashboard</a>
    </div>
</div>

<?php if (isset($success_message)): ?>
    <div
        style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); color: #34d399; padding: 1rem; border-radius: 8px; margin-bottom: 2rem;">
        <?php echo $success_message; ?>
    </div>
<?php endif; ?>

<?php if (isset($error_message)): ?>
    <div
        style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); color: #f87171; padding: 1rem; border-radius: 8px; margin-bottom: 2rem;">
        <?php echo $error_message; ?>
    </div>
<?php endif; ?>

<div class="glass-card" style="padding: 0; overflow: hidden;">
    <table class="custom-table">
        <thead>
            <tr>
                <th>Student Name</th>
                <th>Email</th>
                <th>Department</th>
                <th>CGPA</th>
                <th>Semester</th>
                <th style="text-align: right;">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($students)): ?>
                <tr>
                    <td colspan="6" style="text-align: center; color: #94a3b8; padding: 3rem 1rem;">
                        No students found.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($students as $student): ?>
                    <tr>
                        <td>
                            <div style="color: white; font-weight: 500;">
                                <?php echo htmlspecialchars($student['full_name']); ?>
                            </div>
                            <div style="font-size: 0.75rem; color: #64748b;">Joined
                                <?php echo date('M Y', strtotime($student['created_at'])); ?></div>
                        </td>
                        <td><?php echo htmlspecialchars($student['email']); ?></td>
                        <td>
                            <span class="badge badge-primary">
                                <?php echo htmlspecialchars($student['department']); ?>
                            </span>
                        </td>
                        <td style="color: var(--accent-color); font-weight: bold;">
                            <?php echo htmlspecialchars($student['cgpa'] ?? 'N/A'); ?>
                        </td>
                        <td><?php echo htmlspecialchars($student['semester'] ?? 'N/A'); ?></td>
                        <td style="text-align: right;">
                            <form method="POST" onsubmit="return confirm('Are you sure you want to remove this student?');">
                                <input type="hidden" name="delete_student_id" value="<?php echo $student['id']; ?>">
                                <button type="submit" class="btn"
                                    style="background: rgba(239, 68, 68, 0.15); color: #f87171; padding: 0.5rem 1rem; border: 1px solid rgba(239, 68, 68, 0.3);">
                                    <i class="fas fa-trash-alt"></i> Remove
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>