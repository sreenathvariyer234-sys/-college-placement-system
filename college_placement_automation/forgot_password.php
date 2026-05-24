<?php
// forgot_password.php
require_once 'config/db.php';
session_start();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];

    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // In a real app, send email with token here.
        // For this automation project, we'll redirect to a reset page for demo purposes.
        $_SESSION['reset_email'] = $email;
        header("Location: reset_password.php");
        exit;
    } else {
        $error = "No account found with that email address.";
    }
}
?>

<?php include 'includes/header.php'; ?>

<div style="max-width: 450px; margin: 4rem auto;">
    <div class="glass-card">
        <h2 style="margin-bottom: 1rem; text-align: center;">Reset Password</h2>
        <p style="text-align: center; color: #94a3b8; margin-bottom: 2rem; font-size: 0.9rem;">
            Enter your email address and we'll help you reset your password.
        </p>
        
        <?php if ($error): ?>
            <div style="background: rgba(239, 68, 68, 0.2); border: 1px solid var(--danger-color); padding: 1rem; border-radius: 10px; margin-bottom: 1.5rem; color: #fca5a5; font-size: 0.9rem;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form action="forgot_password.php" method="POST">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="name@college.edu" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1.5rem; padding: 1rem;">Verify Email</button>
        </form>
        
        <p style="text-align: center; margin-top: 2rem; color: #94a3b8; font-size: 0.9rem;">
            Remembered your password? <a href="login.php" style="color: var(--secondary-color); text-decoration: none;">Back to login</a>
        </p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
