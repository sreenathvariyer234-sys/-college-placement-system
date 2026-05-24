<?php
// admin/manage_companies.php
require_once '../config/db.php';
session_start();

// Ensure only super_admin or college_po can access
$allowed_roles = ['super_admin', 'college_po'];
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], $allowed_roles)) {
    // If they are an admin but not the right type, send them to dashboard
    if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'dept_po'])) {
        header("Location: dashboard.php");
        exit;
    }
    // Otherwise send to login
    header("Location: ../login.php");
    exit;
}

// Handle company deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_company_id'])) {
    $company_id_to_delete = $_POST['delete_company_id'];
    
    // Begin transaction to ensure safe deletion or rollback
    try {
        $pdo->beginTransaction();
        
        // Explicitly delete all jobs posted by this company first
        // This will cascade down to the 'applications' table and delete student applications for these jobs
        $stmt_jobs = $pdo->prepare("DELETE FROM jobs WHERE company_id = ?");
        $stmt_jobs->execute([$company_id_to_delete]);
        
        // Now delete the actual company user account
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'company'");
        $stmt->execute([$company_id_to_delete]);
        
        $pdo->commit();
        $success_message = "Company and all associated jobs have been successfully removed.";
    } catch (Exception $e) {
        $pdo->rollBack();
        $error_message = "Failed to remove company. Please try again.";
        error_log("Company deletion error: " . $e->getMessage());
    }
}

// Fetch all companies
$stmt = $pdo->query("SELECT id, full_name, email, website, city, state, created_at FROM users WHERE role = 'company' ORDER BY created_at DESC");
$companies = $stmt->fetchAll();
?>

<?php include '../includes/header.php'; ?>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 3rem;">
        <div>
            <h1>Manage Companies</h1>
            <div style="font-size: 0.9rem; color: #94a3b8; margin-top: 0.5rem;">View and remove registered employers</div>
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
                    <th>Company Name</th>
                    <th>Email</th>
                    <th>Location</th>
                    <th>Joined</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($companies)): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; color: #94a3b8; padding: 3rem 1rem;">
                            No companies have registered yet.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($companies as $company): ?>
                        <tr>
                            <td>
                                <div style="color: white; font-weight: 500;">
                                    <?php echo htmlspecialchars($company['full_name']); ?>
                                </div>
                                <?php if (!empty($company['website'])): ?>
                                    <a href="<?php echo htmlspecialchars($company['website']); ?>" target="_blank" style="font-size: 0.75rem; color: var(--primary-color); text-decoration: none; margin-top: 0.2rem; display: inline-block;">
                                        Visit Website <i class="fas fa-external-link-alt" style="font-size: 0.6rem;"></i>
                                    </a>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($company['email']); ?></td>
                            <td>
                                <?php 
                                    $location = array_filter([$company['city'], $company['state']]);
                                    echo !empty($location) ? htmlspecialchars(implode(', ', $location)) : '<span style="color: #64748b;">Not specified</span>';
                                ?>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($company['created_at'])); ?></td>
                            <td style="text-align: right;">
                                <form method="POST">
                                    <input type="hidden" name="delete_company_id" value="<?php echo $company['id']; ?>">
                                    <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 0.5rem;">
                                        <label style="font-size: 0.75rem; color: #f87171; display: flex; align-items: center; gap: 0.25rem; cursor: pointer;">
                                            <input type="checkbox" required> Confirm bypass
                                        </label>
                                        <button type="submit" class="btn" style="background: rgba(239, 68, 68, 0.15); color: #f87171; padding: 0.5rem 1rem; border: 1px solid rgba(239, 68, 68, 0.3);">
                                            <i class="fas fa-trash-alt"></i> Remove
                                        </button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

<?php include '../includes/footer.php'; ?>
