<?php
require_once '../config/db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'company') {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $requirements = $_POST['requirements'];
    $salary = $_POST['salary'];
    $location = $_POST['location'];
    $vacancies = !empty($_POST['vacancies']) ? (int)$_POST['vacancies'] : null;
    $company_id = $_SESSION['user_id'];
    $company_name = $_SESSION['full_name'];

    $department = $_POST['department'];

    $stmt = $pdo->prepare("INSERT INTO jobs (title, company, description, requirements, salary, location, company_id, department, vacancies) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$title, $company_name, $description, $requirements, $salary, $location, $company_id, $department, $vacancies])) {
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
        $notification_msg = "A new job opportunity ($title at $company_name) matching your branch has been posted!";
        
        foreach ($students as $student) {
            $notif_stmt = $pdo->prepare("INSERT INTO notifications (user_id, message, type) VALUES (?, ?, 'new_job')");
            $notif_stmt->execute([$student['id'], $notification_msg]);
        }

        header("Location: dashboard.php");
        exit;
    }
}

include '../includes/header.php';
?>

<div style="max-width: 800px; margin: 2rem auto;">
    <div class="glass-card">
        <h2 style="margin-bottom: 2rem;">Post a New Job</h2>
        <form method="POST">
            <div class="form-group">
                <label>Job Title</label>
                <input type="text" name="title" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Location</label>
                <input type="text" name="location" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Salary Range</label>
                <input type="text" name="salary" class="form-control">
            </div>
            <div class="form-group">
                <label>Number of Vacancies (Optional)</label>
                <input type="number" name="vacancies" class="form-control" placeholder="e.g. 5" min="1">
            </div>
            <div class="form-group">
                <label>Job Description</label>
                <textarea name="description" class="form-control" rows="5" required></textarea>
            </div>
            <div class="form-group">
                <label>Requirements</label>
                <textarea name="requirements" class="form-control" rows="5"></textarea>
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
            <button type="submit" class="btn btn-primary" style="padding: 1rem 2.5rem;">Post Job Opening</button>
            <a href="dashboard.php" style="margin-left: 1rem; color: #94a3b8; text-decoration: none;">Cancel</a>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
