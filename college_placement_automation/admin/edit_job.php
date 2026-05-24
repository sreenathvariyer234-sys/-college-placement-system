<?php
// admin/edit_job.php
require_once '../config/db.php';
session_start();

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['super_admin', 'college_po'])) {
    header("Location: ../login.php");
    exit;
}

$job_id = $_GET['id'] ?? null;
if (!$job_id) {
    header("Location: manage_jobs.php");
    exit;
}

// Fetch job details
$stmt = $pdo->prepare("SELECT * FROM jobs WHERE id = ?");
$stmt->execute([$job_id]);
$job = $stmt->fetch();

if (!$job) {
    die("Job not found.");
}

$success = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $company = $_POST['company'];
    $location = $_POST['location'];
    $salary = $_POST['salary'];
    $description = $_POST['description'];
    $requirements = $_POST['requirements'];
    $vacancies = !empty($_POST['vacancies']) ? (int)$_POST['vacancies'] : null;
    $department = $_POST['department'];

    $stmt = $pdo->prepare("UPDATE jobs SET title = ?, company = ?, location = ?, salary = ?, description = ?, requirements = ?, department = ?, vacancies = ? WHERE id = ?");
    if ($stmt->execute([$title, $company, $location, $salary, $description, $requirements, $department, $vacancies, $job_id])) {
        $success = "Job posting updated successfully!";
        // Refresh job data
        $stmt = $pdo->prepare("SELECT * FROM jobs WHERE id = ?");
        $stmt->execute([$job_id]);
        $job = $stmt->fetch();
    }
}
?>

<?php include '../includes/header.php'; ?>

    <div style="max-width: 800px; margin: 2rem auto;">
        <div style="margin-bottom: 2rem;">
            <a href="manage_jobs.php" style="color: var(--secondary-color); text-decoration: none;"><i class="fas fa-arrow-left"></i> Back to Job List</a>
            <h2 style="margin-top: 1rem;">Edit Job: <?php echo htmlspecialchars($job['title']); ?></h2>
        </div>

        <div class="glass-card">
            <?php if ($success): ?>
                <div style="background: rgba(16, 185, 129, 0.2); border: 1px solid var(--accent-color); padding: 1rem; border-radius: 10px; margin-bottom: 1.5rem; color: #6ee7b7;">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <form action="edit_job.php?id=<?php echo $job_id; ?>" method="POST">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div class="form-group">
                        <label>Job Title</label>
                        <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($job['title']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Company Name</label>
                        <input type="text" name="company" class="form-control" value="<?php echo htmlspecialchars($job['company']); ?>" required>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label>Department Target</label>
                    <select name="department" class="form-control" required>
                        <option value="Common" <?php echo $job['department'] == 'Common' ? 'selected' : ''; ?>>Common (Open to All)</option>
                        <option value="Computer Engineering" <?php echo $job['department'] == 'Computer Engineering' ? 'selected' : ''; ?>>Computer Engineering</option>
                        <option value="Civil" <?php echo $job['department'] == 'Civil' ? 'selected' : ''; ?>>Civil Engineering</option>
                        <option value="Mechanical" <?php echo $job['department'] == 'Mechanical' ? 'selected' : ''; ?>>Mechanical Engineering</option>
                        <option value="Electronics" <?php echo $job['department'] == 'Electronics' ? 'selected' : ''; ?>>Electronics Engineering</option>
                        <option value="Computer Hardware Engineering" <?php echo $job['department'] == 'Computer Hardware Engineering' ? 'selected' : ''; ?>>Computer Hardware Engineering</option>
                    </select>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div class="form-group">
                        <label>Location</label>
                        <input type="text" name="location" class="form-control" value="<?php echo htmlspecialchars($job['location']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Package / Salary</label>
                        <input type="text" name="salary" class="form-control" value="<?php echo htmlspecialchars($job['salary']); ?>" required>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label>Number of Vacancies (Optional)</label>
                    <input type="number" name="vacancies" class="form-control" value="<?php echo htmlspecialchars($job['vacancies']); ?>" min="1">
                </div>

                <div class="form-group">
                    <label>Job Description</label>
                    <textarea name="description" class="form-control" rows="5" required><?php echo htmlspecialchars($job['description']); ?></textarea>
                </div>

                <div class="form-group">
                    <label>Requirements</label>
                    <textarea name="requirements" class="form-control" rows="3"><?php echo htmlspecialchars($job['requirements']); ?></textarea>
                </div>

                <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                    <button type="submit" class="btn btn-primary" style="flex: 1; padding: 1rem;">Update Job Opening</button>
                    <a href="manage_jobs.php" class="btn" style="background: rgba(255,255,255,0.05); color: white; padding: 1rem 2rem; border: 1px solid rgba(255,255,255,0.1);">Cancel</a>
                </div>
            </form>
        </div>
    </div>

<?php include '../includes/footer.php'; ?>
