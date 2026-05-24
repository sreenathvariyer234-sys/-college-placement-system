<?php
// admin/manage_jobs.php
require_once '../config/db.php';
session_start();

// Ensure only accepted admin roles can access
$allowed_roles = ['super_admin', 'college_po'];
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], $allowed_roles)) {
    header("Location: ../login.php");
    exit;
}

$role = $_SESSION['role'];

// Handle job deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_job_id'])) {
    $job_id_to_delete = $_POST['delete_job_id'];
    
    try {
        $stmt = $pdo->prepare("DELETE FROM jobs WHERE id = ?");
        $stmt->execute([$job_id_to_delete]);
        $success_message = "Job posting has been successfully removed.";
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

// Fetch jobs with filtering
$query = "SELECT * FROM jobs ORDER BY created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute();
$jobs = $stmt->fetchAll();
?>

<?php include '../includes/header.php'; ?>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 3rem;">
        <div>
            <h1>Manage Jobs</h1>
            <div style="font-size: 0.9rem; color: #94a3b8; margin-top: 0.5rem;">
                View and manage all job openings
            </div>
        </div>
        <div style="display: flex; gap: 1rem;">
            <a href="post_job.php" class="btn btn-primary"><i class="fas fa-plus"></i> Post New Job</a>
            <a href="dashboard.php" class="btn" style="background: rgba(255,255,255,0.05); color: white;">&larr; Back to Dashboard</a>
        </div>
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
                    <th>Job Title</th>
                    <th>Company</th>
                    <th>Department</th>
                    <th>Vacancies</th>
                    <th>Salary</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($jobs)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; color: #94a3b8; padding: 3rem 1rem;">
                            No job postings found.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($jobs as $job): ?>
                        <tr>
                            <td>
                                <div style="color: white; font-weight: 500;">
                                    <?php echo htmlspecialchars($job['title']); ?>
                                </div>
                                <div style="font-size: 0.75rem; color: #64748b;">Posted <?php echo date('M d, Y', strtotime($job['created_at'])); ?></div>
                            </td>
                            <td><?php echo htmlspecialchars($job['company']); ?></td>
                            <td>
                                <span class="badge badge-primary">
                                    <?php echo htmlspecialchars($job['department']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($job['vacancies'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($job['salary'] ?? 'N/A'); ?></td>
                            <td style="text-align: right;">
                                <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                                    <a href="edit_job.php?id=<?php echo $job['id']; ?>" class="btn" style="background: rgba(99, 102, 241, 0.15); color: #818cf8; padding: 0.5rem 1rem; border: 1px solid rgba(99, 102, 241, 0.3);">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <form method="POST" onsubmit="return confirm('Are you sure you want to remove this job opening?');" style="display:inline;">
                                        <input type="hidden" name="delete_job_id" value="<?php echo $job['id']; ?>">
                                        <button type="submit" class="btn" style="background: rgba(239, 68, 68, 0.15); color: #f87171; padding: 0.5rem 1rem; border: 1px solid rgba(239, 68, 68, 0.3);">
                                            <i class="fas fa-trash-alt"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

<?php include '../includes/footer.php'; ?>
