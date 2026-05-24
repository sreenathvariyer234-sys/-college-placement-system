<?php
// admin/dashboard.php
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

// Build query constraints based on role
$user_filter = "role = 'student'";
$job_filter = "1=1";
$app_filter = "1=1";
$params = [];

if ($role === 'dept_po' && $managed_dept) {
    $user_filter .= " AND department = ?";
    $job_filter = "department = ? OR department = 'Common'";
    $app_filter = "u.department = ?";
    $params = [$managed_dept];
}

// Stats
$job_count = $pdo->prepare("SELECT COUNT(*) FROM jobs WHERE $job_filter");
$job_count->execute($params);
$job_count = $job_count->fetchColumn();

$student_count = $pdo->prepare("SELECT COUNT(*) FROM users WHERE $user_filter");
$student_count->execute($params);
$student_count = $student_count->fetchColumn();

$app_count_query = "
    SELECT COUNT(*) FROM applications a 
    JOIN users u ON a.student_id = u.id 
    WHERE $app_filter
";
$app_count = $pdo->prepare($app_count_query);
$app_count->execute($params);
$app_count = $app_count->fetchColumn();

// Get recent job postings
$jobs_query = $pdo->prepare("SELECT * FROM jobs WHERE $job_filter ORDER BY created_at DESC");
$jobs_query->execute($params);
$jobs = $jobs_query->fetchAll();

// Get recent applications with student details
$recent_apps_query = $pdo->prepare("
    SELECT a.*, u.full_name, u.email, j.title as job_title, j.company 
    FROM applications a 
    JOIN users u ON a.student_id = u.id 
    JOIN jobs j ON a.job_id = j.id 
    WHERE $app_filter
    ORDER BY a.applied_at DESC 
    LIMIT 10
");
$recent_apps_query->execute($params);
$recent_apps = $recent_apps_query->fetchAll();
?>

<?php include '../includes/header.php'; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 3rem;">
    <div>
        <h1>
            <?php
            if ($role === 'super_admin')
                echo 'Super Admin Dashboard';
            else if ($role === 'college_po')
                echo 'College Placement Officer';
            else if ($role === 'dept_po')
                echo htmlspecialchars($managed_dept) . ' Department Officer';
            ?>
        </h1>
        <div style="font-size: 0.9rem; color: #94a3b8; margin-top: 0.5rem;">Control Panel</div>
    </div>
    <div style="display: flex; gap: 1rem;">
        <a href="manage_students.php" class="btn" style="background: rgba(255,255,255,0.05); color: white;"><i
                class="fas fa-user-graduate"></i> Manage Students</a>
        <a href="export_selected.php" class="btn"
            style="background: rgba(16, 185, 129, 0.2); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3);"><i
                class="fas fa-file-export"></i> Export Selected</a>
        <?php if (in_array($role, ['super_admin', 'college_po'])): ?>
            <a href="manage_jobs.php" class="btn" style="background: rgba(255,255,255,0.05); color: white;"><i
                    class="fas fa-briefcase"></i> Manage Jobs</a>
            <a href="manage_dept_po.php" class="btn" style="background: rgba(255,255,255,0.05); color: white;"><i
                    class="fas fa-user-shield"></i> Manage Dept POs</a>
            <a href="manage_companies.php" class="btn" style="background: rgba(255,255,255,0.05); color: white;"><i
                    class="fas fa-building"></i> Manage Companies</a>
            <a href="post_job.php" class="btn btn-primary"><i class="fas fa-plus"></i> Post New Job</a>
        <?php endif; ?>
    </div>


</div>

<!-- Stats Cards -->
<div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem; margin-bottom: 3rem;">
    <div class="glass-card" style="text-align: center;">
        <div style="font-size: 2.5rem; font-weight: 800; color: var(--secondary-color);"><?php echo $job_count; ?></div>
        <div style="color: #94a3b8; margin-top: 0.5rem;">Active Job Postings</div>
    </div>
    <div class="glass-card" style="text-align: center;">
        <div style="font-size: 2.5rem; font-weight: 800; color: var(--accent-color);"><?php echo $student_count; ?>
        </div>
        <div style="color: #94a3b8; margin-top: 0.5rem;">
            <?php echo ($role === 'dept_po') ? 'My Dept. Students' : 'Registered Students'; ?>
        </div>
    </div>
    <div class="glass-card" style="text-align: center;">
        <div style="font-size: 2.5rem; font-weight: 800; color: #c084fc;"><?php echo $app_count; ?></div>
        <div style="color: #94a3b8; margin-top: 0.5rem;">
            <?php echo ($role === 'dept_po') ? 'My Dept. Applications' : 'Total Applications'; ?>
        </div>
    </div>

</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
    <!-- Jobs List -->
    <div>
        <h2 style="margin-bottom: 1.5rem;">Active Openings</h2>
        <div class="glass-card" style="padding: 0; overflow: hidden;">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Job Title</th>
                        <th>Department</th>
                        <th>Company</th>
                        <th>Vacancies</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($jobs as $job): ?>
                        <tr>
                            <td style="color: white; font-weight: 500;"><?php echo htmlspecialchars($job['title']); ?></td>
                            <td>
                                <span class="badge badge-primary">
                                    <?php echo htmlspecialchars($job['department']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($job['company']); ?></td>
                            <td style="color: #94a3b8;">
                                <?php echo !empty($job['vacancies']) ? htmlspecialchars($job['vacancies']) : 'N/A'; ?>
                            </td>
                            <td><?php echo date('M d', strtotime($job['created_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Applications -->
    <div>
        <h2 style="margin-bottom: 1.5rem;">Recent Applications</h2>
        <div class="glass-card" style="padding: 0; overflow: hidden;">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Job</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_apps as $app): ?>
                        <tr>
                            <td>
                                <div style="color: white; font-weight: 500;">
                                    <?php echo htmlspecialchars($app['full_name']); ?>
                                </div>
                                <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.2rem;">
                                    <?php echo htmlspecialchars($app['email']); ?>
                                </div>
                            </td>
                            <td>
                                <div style="color: white;"><?php echo htmlspecialchars($app['job_title']); ?></div>
                                <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.2rem;">
                                    <?php echo htmlspecialchars($app['company']); ?>
                                </div>
                            </td>
                            <td>
                                <span class="badge status-<?php echo $app['status']; ?>"
                                    style="text-transform: capitalize;">
                                    <?php echo $app['status']; ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>
<?php include '../includes/footer.php'; ?>