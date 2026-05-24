<?php
// login.php
require_once 'config/db.php';
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true); // Prevent session fixation
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];
        if (isset($user['managed_department'])) {
            $_SESSION['managed_department'] = $user['managed_department'];
        }

        if (in_array($user['role'], ['super_admin', 'college_po', 'dept_po'])) {
            header("Location: admin/dashboard.php");
        } elseif ($user['role'] == 'company') {
            header("Location: company/dashboard.php");
        } else {
            header("Location: student/dashboard.php");
        }
        exit;
    } else {
        $error = "Invalid email or password.";
    }
}
?>

<?php include 'includes/header.php'; ?>

<div style="max-width: 450px; margin: 4rem auto;">
    <div class="glass-card">
        <h2 style="margin-bottom: 2rem; text-align: center;">Welcome Back</h2>
        
        <?php if ($error): ?>
            <div style="background: rgba(239, 68, 68, 0.2); border: 1px solid var(--danger-color); padding: 1rem; border-radius: 10px; margin-bottom: 1.5rem; color: #fca5a5; font-size: 0.9rem;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="name@college.edu" required>
            </div>
            <div class="form-group">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <label>Password</label>
                    <a href="forgot_password.php" style="font-size: 0.8rem; color: var(--secondary-color); text-decoration: none;">Forgot?</a>
                </div>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem; padding: 1rem;">Login to Account</button>

        </form>
        
        <p style="text-align: center; margin-top: 2rem; color: #94a3b8; font-size: 0.9rem;">
            Don't have an account? <a href="register.php" style="color: var(--secondary-color); text-decoration: none;">Create one</a>
        </p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
