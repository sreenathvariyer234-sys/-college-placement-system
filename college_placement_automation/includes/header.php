<!-- includes/header.php -->
<?php if (session_status() === PHP_SESSION_NONE) { session_start(); } ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>College Placement Automation</title>
    <link rel="stylesheet" href="/college_placement_automation/assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <nav>
        <a href="/college_placement_automation/index.php" class="logo">PlacementHub</a>
        <div class="nav-links">
            <a href="/college_placement_automation/index.php">Home</a>
            <a href="/college_placement_automation/index.php#about">About</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if (in_array($_SESSION['role'], ['super_admin', 'college_po', 'dept_po'])): ?>
                    <a href="/college_placement_automation/admin/dashboard.php">
                        <?php 
                            if ($_SESSION['role'] === 'super_admin') echo 'Super Admin';
                            else if ($_SESSION['role'] === 'college_po') echo 'College PO';
                            else echo 'Dept. PO';
                        ?> Dashboard
                    </a>
                    <a href="/college_placement_automation/admin/export_selected.php">Export Selected</a>
                <?php elseif ($_SESSION['role'] == 'company'): ?>
                    <a href="/college_placement_automation/company/dashboard.php">Company Dashboard</a>
                <?php else: ?>
                    <a href="/college_placement_automation/student/dashboard.php">My Dashboard</a>
                    <a href="/college_placement_automation/student/notifications.php">Notifications</a>
                <?php endif; ?>
                <a href="/college_placement_automation/logout.php" class="btn btn-primary">Logout</a>
            <?php else: ?>
                <a href="/college_placement_automation/login.php">Login</a>
                <a href="/college_placement_automation/register.php" class="btn btn-primary">Register</a>
            <?php endif; ?>
        </div>
    </nav>
    <div class="container">
