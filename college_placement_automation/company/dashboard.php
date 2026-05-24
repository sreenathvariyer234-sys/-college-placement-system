<?php
require_once '../config/db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'company') {
    header("Location: ../login.php");
    exit;
}

$company_id = $_SESSION['user_id'];

// Get jobs posted by this company
$stmt = $pdo->prepare("SELECT * FROM jobs WHERE company_id = ? ORDER BY created_at DESC");
$stmt->execute([$company_id]);
$jobs = $stmt->fetchAll();

include '../includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <h1>Company Dashboard</h1>
    <a href="post_job.php" class="btn btn-primary">Post New Job</a>
</div>

<div class="glass-card" style="padding: 0; overflow: hidden;">
    <h3 style="padding: 1.5rem; margin: 0; border-bottom: 1px solid rgba(255,255,255,0.05);">My Job Postings</h3>
    <table class="custom-table">
        <thead>
            <tr>
                <th>Job Title</th>
                <th>Location</th>
                <th>Vacancies</th>
                <th>Applications</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($jobs): ?>
                <?php foreach ($jobs as $job): 
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM applications WHERE job_id = ?");
                    $stmt->execute([$job['id']]);
                    $app_count = $stmt->fetchColumn();
                ?>
                    <tr>
                        <td style="color: white; font-weight: 500;"><?php echo htmlspecialchars($job['title']); ?></td>
                        <td><?php echo htmlspecialchars($job['location']); ?></td>
                        <td style="color: #94a3b8;"><?php echo !empty($job['vacancies']) ? htmlspecialchars($job['vacancies']) : 'N/A'; ?></td>
                        <td><span class="badge badge-primary"><?php echo $app_count; ?></span></td>
                        <td>
                            <a href="view_applications.php?job_id=<?php echo $job['id']; ?>" style="color: var(--secondary-color); text-decoration: none; font-weight: 500;">View Applications</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="padding: 3rem; text-align: center; color: #94a3b8;">You haven't posted any jobs yet.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>
