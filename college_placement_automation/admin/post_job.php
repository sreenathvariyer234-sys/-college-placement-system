<?php
// admin/post_job.php
require_once '../config/db.php';
session_start();

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['super_admin', 'college_po'])) {
    header("Location: ../login.php");
    exit;
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

    $stmt = $pdo->prepare("INSERT INTO jobs (title, company, location, salary, description, requirements, department, vacancies) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$title, $company, $location, $salary, $description, $requirements, $department, $vacancies])) {
        $job_id = $pdo->lastInsertId();
        
        // Notify students in the matching department, or all students if Common
        if ($department === 'Common') {
            $student_stmt = $pdo->prepare("SELECT id FROM users WHERE role = 'student'");
            $student_stmt->execute();
        } else {
            $student_stmt = $pdo->prepare("SELECT id FROM users WHERE role = 'student' AND department = ?");
            $student_stmt->execute([$department]);
        }
        
        $students = $student_stmt->fetchAll();
        $notification_msg = "A new job opportunity ($title at $company) matching your branch has been posted!";
        
        foreach ($students as $student) {
            $notif_stmt = $pdo->prepare("INSERT INTO notifications (user_id, message, type) VALUES (?, ?, 'new_job')");
            $notif_stmt->execute([$student['id'], $notification_msg]);
        }

        $success = "Job posting created successfully and students notified!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post New Job - PlacementHub</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav>
        <a href="../index.php" class="logo">PlacementHub</a>
        <div class="nav-links">
            <a href="dashboard.php">Back to Dashboard</a>
        </div>
    </nav>
    <div class="container">

    <div style="max-width: 800px; margin: 2rem auto;">
        <div class="glass-card">
            <h2 style="margin-bottom: 2rem;">Post a New Job Opportunity</h2>

            <?php if ($success): ?>
                <div style="background: rgba(16, 185, 129, 0.2); border: 1px solid var(--accent-color); padding: 1rem; border-radius: 10px; margin-bottom: 1.5rem; color: #6ee7b7;">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <form action="post_job.php" method="POST">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div class="form-group">
                        <label>Job Title</label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Software Engineer" required>
                    </div>
                    <div class="form-group">
                        <label>Company Name</label>
                        <input type="text" name="company" class="form-control" placeholder="e.g. Google" required>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label>Department Target</label>
                    <select name="department" class="form-control" required>
                        <option value="Common">Common (Open to All)</option>
                        <option value="Computer Engineering">Computer Engineering</option>
                        <option value="Civil">Civil Engineering</option>
                        <option value="Mechanical">Mechanical Engineering</option>
                        <option value="Electronics">Electronics Engineering</option>
                        <option value="Computer Hardware Engineering">Computer Hardware Engineering</option>
                    </select>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div class="form-group">
                        <label>Location</label>
                        <input type="text" name="location" class="form-control" placeholder="e.g. Remote / New York" required>
                    </div>
                    <div class="form-group">
                        <label>Package / Salary</label>
                        <input type="text" name="salary" class="form-control" placeholder="e.g. $120k / year" required>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label>Number of Vacancies (Optional)</label>
                    <input type="number" name="vacancies" class="form-control" placeholder="e.g. 5" min="1">
                </div>

                <div class="form-group">
                    <label>Job Description</label>
                    <textarea name="description" class="form-control" rows="5" placeholder="Describe the role..." required></textarea>
                </div>

                <div class="form-group">
                    <label>Requirements</label>
                    <textarea name="requirements" class="form-control" rows="3" placeholder="Key skills needed..."></textarea>
                </div>

                <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                    <button type="submit" class="btn btn-primary" style="flex: 1; padding: 1rem;">Publish Job Opening</button>
                    <a href="dashboard.php" class="btn" style="background: rgba(255,255,255,0.05); color: white; padding: 1rem 2rem; border: 1px solid rgba(255,255,255,0.1);">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    </div>
</body>
</html>
