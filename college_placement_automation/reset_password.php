<?php
// reset_password.php
require_once 'config/db.php';
session_start();

if (!isset($_SESSION['reset_email'])) {
    header("Location: forgot_password.php");
    exit;
}

$error = '';
$success = '';
$email = $_SESSION['reset_email'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
        
        if ($stmt->execute([$hashed_password, $email])) {
            unset($_SESSION['reset_email']);
            $success = "Password updated successfully! You can now <a href='login.php' style='color:inherit; font-weight:bold;'>login</a>.";
        } else {
            $error = "Failed to update password. Please try again.";
        }
    }
}
?>

<?php include 'includes/header.php'; ?>

<div style="max-width: 450px; margin: 4rem auto;">
    <div class="glass-card">
        <h2 style="margin-bottom: 1rem; text-align: center;">Set New Password</h2>
        <p style="text-align: center; color: #94a3b8; margin-bottom: 2rem; font-size: 0.9rem;">
            Create a strong password for your account: <strong><?php echo htmlspecialchars($email); ?></strong>
        </p>
        
        <?php if ($error): ?>
            <div style="background: rgba(239, 68, 68, 0.2); border: 1px solid var(--danger-color); padding: 1rem; border-radius: 10px; margin-bottom: 1.5rem; color: #fca5a5; font-size: 0.9rem;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div style="background: rgba(16, 185, 129, 0.2); border: 1px solid var(--accent-color); padding: 1rem; border-radius: 10px; margin-bottom: 1.5rem; color: #6ee7b7; font-size: 0.9rem;">
                <?php echo $success; ?>
            </div>
        <?php else: ?>
            <form action="reset_password.php" method="POST">
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>
                <div class="form-group">
                    <label>Confirm New Password</label>
                    <input type="password" name="confirm_password" class="form-control" placeholder="••••••••" required>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1.5rem; padding: 1rem;">Update Password</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
