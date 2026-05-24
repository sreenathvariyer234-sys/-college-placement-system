<?php
require_once '../config/db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Get notifications
$stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll();

// Mark all as read
$stmt = $pdo->prepare("UPDATE notifications SET is_read = TRUE WHERE user_id = ?");
$stmt->execute([$user_id]);

include '../includes/header.php';
?>

<h1>My Notifications</h1>

<div style="margin-top: 2rem;">
    <?php if ($notifications): ?>
        <?php foreach ($notifications as $note): ?>
            <div class="glass-card" style="margin-bottom: 1rem; border-left: 4px solid <?php echo $note['is_read'] ? 'rgba(255,255,255,0.1)' : 'var(--secondary-color)'; ?>;">
                <p style="margin-bottom: 0.5rem;"><?php echo htmlspecialchars($note['message']); ?></p>
                <small style="color: #64748b;"><?php echo date('M d, Y h:i A', strtotime($note['created_at'])); ?></small>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="glass-card" style="text-align: center; padding: 3rem;">
            <p style="color: #94a3b8;">No notifications yet.</p>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
