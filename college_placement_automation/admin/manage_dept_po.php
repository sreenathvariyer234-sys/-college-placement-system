<?php
// admin/manage_dept_po.php
require_once '../config/db.php';
session_start();

// Ensure only super_admin or college_po can access
$allowed_roles = ['super_admin', 'college_po'];
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], $allowed_roles)) {
    if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'dept_po'])) {
        header("Location: dashboard.php");
        exit;
    }
    header("Location: ../login.php");
    exit;
}

// Handle Dept PO deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_po_id'])) {
    $po_id_to_delete = $_POST['delete_po_id'];
    
    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'dept_po'");
        $stmt->execute([$po_id_to_delete]);
        $success_message = "Department Placement Officer has been successfully removed.";
    } catch (Exception $e) {
        $error_message = "Failed to remove officer. Please try again.";
        error_log("Dept PO deletion error: " . $e->getMessage());
    }
}

// Fetch all Dept POs
$stmt = $pdo->query("SELECT id, full_name, email, managed_department, created_at FROM users WHERE role = 'dept_po' ORDER BY created_at DESC");
$dept_pos = $stmt->fetchAll();
?>

<?php include '../includes/header.php'; ?>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 3rem;">
        <div>
            <h1>Manage Dept Officers</h1>
            <div style="font-size: 0.9rem; color: #94a3b8; margin-top: 0.5rem;">View and remove Department Placement Officers</div>
        </div>
        <a href="dashboard.php" class="btn" style="background: rgba(255,255,255,0.05); color: white;">&larr; Back to Dashboard</a>
    </div>

    <?php if (isset($success_message)): ?>
        <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); color: #34d399; padding: 1rem; border-radius: 8px; margin-bottom: 2rem;">
            <?php echo $success_message; ?>
        </div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
        <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); color: #f87171; padding: 1rem; border-radius: 8px; margin-bottom: 2rem;">
            <?php echo $error_message; ?>
        </div>
    <?php endif; ?>

    <div class="glass-card" style="padding: 0; overflow: hidden;">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>Officer Name</th>
                    <th>Email</th>
                    <th>Department</th>
                    <th>Joined</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($dept_pos)): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; color: #94a3b8; padding: 3rem 1rem;">
                            No Department Placement Officers have registered yet.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($dept_pos as $po): ?>
                        <tr>
                            <td>
                                <div style="color: white; font-weight: 500;">
                                    <?php echo htmlspecialchars($po['full_name']); ?>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($po['email']); ?></td>
                            <td>
                                <span class="badge badge-primary">
                                    <?php echo htmlspecialchars($po['managed_department']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($po['created_at'])); ?></td>
                            <td style="text-align: right;">
                                <form method="POST" onsubmit="return confirm('Are you sure you want to remove this officer?');">
                                    <input type="hidden" name="delete_po_id" value="<?php echo $po['id']; ?>">
                                    <button type="submit" class="btn" style="background: rgba(239, 68, 68, 0.15); color: #f87171; padding: 0.5rem 1rem; border: 1px solid rgba(239, 68, 68, 0.3);">
                                        <i class="fas fa-user-minus"></i> Remove
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
