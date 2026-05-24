<?php
require_once '../config/db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'company') {
    header("Location: ../login.php");
    exit;
}

$job_id = $_GET['job_id'];

// Verify company owns this job
$stmt = $pdo->prepare("SELECT * FROM jobs WHERE id = ? AND company_id = ?");
$stmt->execute([$job_id, $_SESSION['user_id']]);
$job = $stmt->fetch();

if (!$job) {
    die("Access Denied.");
}

// Get applications
$stmt = $pdo->prepare("SELECT a.*, u.full_name, u.email FROM applications a JOIN users u ON a.student_id = u.id WHERE a.job_id = ?");
$stmt->execute([$job_id]);
$applications = $stmt->fetchAll();

include '../includes/header.php';
?>

<div style="margin-bottom: 2rem;">
    <a href="dashboard.php" style="color: var(--secondary-color); text-decoration: none;"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
    <h2 style="margin-top: 1rem;">Applications for: <?php echo htmlspecialchars($job['title']); ?></h2>
</div>

<div class="glass-card">
    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="border-bottom: 1px solid rgba(255,255,255,0.1); text-align: left;">
                <th style="padding: 1rem;">Applicant Name</th>
                <th style="padding: 1rem;">Email</th>
                <th style="padding: 1rem;">Interview Details</th>
                <th style="padding: 1rem;">Status</th>
                <th style="padding: 1rem;">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($applications): ?>
                <?php foreach ($applications as $app): ?>
                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                        <td style="padding: 1rem;"><?php echo htmlspecialchars($app['full_name']); ?></td>
                        <td style="padding: 1rem;"><?php echo htmlspecialchars($app['email']); ?></td>
                        <td style="padding: 1rem; font-size: 0.85rem; color: #94a3b8;">
                            <?php if ($app['interview_date']): ?>
                                <i class="fas fa-calendar-alt"></i> <?php echo date('M d, Y', strtotime($app['interview_date'])); ?><br>
                                <i class="fas fa-clock"></i> <?php echo date('h:i A', strtotime($app['interview_time'])); ?>
                            <?php else: ?>
                                <span style="opacity: 0.5;">Not scheduled</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 1rem;">
                            <span class="badge status-<?php echo $app['status']; ?>" style="text-transform: capitalize;">
                                <?php echo str_replace('_', ' ', $app['status']); ?>
                            </span>
                        </td>
                        <td style="padding: 1rem;">
                            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                <?php if ($app['status'] == 'pending'): ?>
                                    <!-- Simple inline form for scheduling -->
                                    <form action="manage_application.php" method="POST" style="display: inline-flex; gap: 0.3rem; align-items: center; background: rgba(255,255,255,0.05); padding: 0.4rem; border-radius: 6px;">
                                        <input type="hidden" name="id" value="<?php echo $app['id']; ?>">
                                        <input type="hidden" name="job_id" value="<?php echo $job_id; ?>">
                                        <input type="hidden" name="action" value="schedule_interview">
                                        <input type="date" name="interview_date" required style="background: transparent; color: white; border: 1px solid rgba(255,255,255,0.2); font-size: 0.75rem; border-radius: 4px; padding: 0.2rem;">
                                        <input type="time" name="interview_time" required style="background: transparent; color: white; border: 1px solid rgba(255,255,255,0.2); font-size: 0.75rem; border-radius: 4px; padding: 0.2rem;">
                                        <button type="submit" class="btn" style="background: var(--secondary-color); color: white; padding: 0.3rem 0.6rem; font-size: 0.75rem;">Set Interview</button>
                                    </form>
                                    
                                    <a href="manage_application.php?id=<?php echo $app['id']; ?>&action=rejected&job_id=<?php echo $job_id; ?>" class="btn" style="background: var(--danger-color); color: white; padding: 0.45rem 1rem; font-size: 0.8rem; height: fit-content;" onclick="return confirm('Reject this applicant?')">Reject</a>
                                
                                <?php elseif ($app['status'] == 'interview_scheduled'): 
                                    $interview_timestamp = strtotime($app['interview_date'] . ' ' . $app['interview_time']);
                                    $current_timestamp = time();
                                    $unlock_time = $interview_timestamp + 600; // 10 minutes later
                                    $is_passed = ($current_timestamp > $unlock_time);
                                ?>
                                    <?php if ($is_passed): ?>
                                        <a href="manage_application.php?id=<?php echo $app['id']; ?>&action=accepted&job_id=<?php echo $job_id; ?>" class="btn" style="background: var(--accent-color); color: white; padding: 0.45rem 1rem; font-size: 0.8rem;">Accept</a>
                                        <a href="manage_application.php?id=<?php echo $app['id']; ?>&action=shortlisted&job_id=<?php echo $job_id; ?>" class="btn" style="background: var(--secondary-color); color: white; padding: 0.45rem 1rem; font-size: 0.8rem;">Shortlist</a>
                                        <a href="manage_application.php?id=<?php echo $app['id']; ?>&action=rejected&job_id=<?php echo $job_id; ?>" class="btn" style="background: var(--danger-color); color: white; padding: 0.45rem 1rem; font-size: 0.8rem;" onclick="return confirm('Reject this applicant?')">Reject</a>
                                    <?php else: ?>
                                        <?php 
                                            $remaining = $unlock_time - $current_timestamp;
                                            $mins = ceil($remaining / 60);
                                        ?>
                                        <span style="color: var(--secondary-color); font-size: 0.75rem; font-weight: 500;">
                                            <i class="fas fa-lock"></i> Decision unlocks in <?php echo $mins; ?>m
                                        </span>
                                    <?php endif; ?>


                                
                                <?php elseif ($app['status'] == 'shortlisted'): ?>

                                    <span style="color: #94a3b8; font-size: 0.8rem;">Waiting for student response</span>
                                
                                <?php else: ?>
                                    <span style="color: #64748b; font-size: 0.8rem;">Completed</span>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="padding: 3rem; text-align: center; color: #94a3b8;">No applications received yet.</td>
                </tr>
            <?php endif; ?>

        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>
