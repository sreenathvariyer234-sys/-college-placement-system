<?php
// student/dashboard.php
require_once '../config/db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Get user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Get applications
$stmt = $pdo->prepare("
    SELECT a.*, j.title, j.company, j.location 
    FROM applications a 
    JOIN jobs j ON a.job_id = j.id 
    WHERE a.student_id = ? 
    ORDER BY a.applied_at DESC
");
$stmt->execute([$user_id]);
$my_applications = $stmt->fetchAll();

// Get available jobs not yet applied to, matching department
$stmt = $pdo->prepare("
    SELECT * FROM jobs 
    WHERE id NOT IN (SELECT job_id FROM applications WHERE student_id = ?) 
    AND (department = ? OR department = 'Common')
    ORDER BY created_at DESC
");
$stmt->execute([$user_id, $user['department']]);
$available_jobs = $stmt->fetchAll();
?>

<?php include '../includes/header.php'; ?>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 3rem;">
        <div style="display: flex; align-items: center; gap: 1.5rem;">
            <?php
            $profile_img = $user['profile_pic'];
            $img_path = '../uploads/' . $profile_img;
            
            // If they haven't uploaded a picture or the file doesn't exist, use an initial-based avatar
            if (empty($profile_img) || $profile_img === 'default_profile.png' || !file_exists($img_path)) {
                $name_url = urlencode($user['full_name']);
                $img_path = "https://ui-avatars.com/api/?name=$name_url&background=6366f1&color=fff&size=128";
            }
            ?>
            <img src="<?php echo htmlspecialchars($img_path); ?>" alt="Profile Picture" class="avatar-lg">
            <div>
                <h1 class="welcome-shake" style="margin: 0;">Welcome, <?php echo htmlspecialchars($user['full_name']); ?> 👋</h1>
                <div style="font-size: 0.9rem; color: #94a3b8; margin-top: 0.2rem;">Student Portal</div>
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
        <!-- Available Jobs -->
        <div>
            <h2 style="margin-bottom: 1.5rem;">Explore Opportunities</h2>
            <?php if ($available_jobs): ?>
                <?php foreach ($available_jobs as $job): ?>
                    <div class="glass-card job-card" style="margin-bottom: 1.5rem; padding: 2rem;">
                        <div style="display: flex; gap: 1.5rem; align-items: flex-start;">
                            <!-- Company Logo -->
                            <?php 
                                $company_name = $job['company'];
                                $words = explode(' ', $company_name);
                                $initials = '';
                                foreach($words as $w){
                                    if(!empty($w)) $initials .= strtoupper(substr($w, 0, 1));
                                }
                                if(strlen($initials) > 2) $initials = substr($initials, 0, 2);
                            ?>
                            <div class="company-logo" style="flex-shrink: 0; width: 60px; height: 60px; font-size: 1.8rem; background: var(--gradient-primary); box-shadow: 0 4px 15px rgba(99, 102, 241, 0.2);">
                                <?php echo htmlspecialchars($initials); ?>
                            </div>
                            
                            <div style="flex-grow: 1;">
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem;">
                                    <div>
                                        <h3 style="margin: 0; font-size: 1.25rem; color: white;"><?php echo htmlspecialchars($job['title']); ?></h3>
                                        <div style="color: var(--secondary-color); font-weight: 600; font-size: 0.95rem; margin-top: 0.2rem;"><?php echo htmlspecialchars($job['company']); ?></div>
                                    </div>
                                    <span class="badge badge-primary" style="padding: 0.5rem 1rem;">
                                        <i class="fas fa-graduation-cap" style="margin-right: 0.4rem;"></i> <?php echo htmlspecialchars($job['department']); ?>
                                    </span>
                                </div>

                                <p style="color: #94a3b8; font-size: 0.9rem; line-height: 1.6; margin-bottom: 1.5rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                    <?php echo htmlspecialchars($job['description']); ?>
                                </p>

                                <div style="display: flex; justify-content: space-between; align-items: flex-end;">
                                    <div class="job-info-row">
                                        <div class="job-info-item">
                                            <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($job['location']); ?>
                                        </div>
                                        <div class="job-info-item">
                                            <i class="fas fa-money-bill-wave"></i> <?php echo htmlspecialchars($job['salary']); ?>
                                        </div>
                                        <?php if(!empty($job['vacancies'])): ?>
                                            <div class="job-info-item">
                                                <i class="fas fa-users"></i> <?php echo htmlspecialchars($job['vacancies']); ?> Positions
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="apply-btn-container">
                                        <form action="apply.php" method="POST">
                                            <input type="hidden" name="job_id" value="<?php echo $job['id']; ?>">
                                            <button type="submit" class="btn btn-primary" style="padding: 0.7rem 1.5rem;">Apply Now <i class="fas fa-paper-plane" style="margin-left: 0.3rem;"></i></button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

            <?php else: ?>
                <div class="glass-card" style="text-align: center; padding: 3rem;">
                    <p style="color: #94a3b8;">No new job openings at the moment.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- My Applications -->
        <div>
            <h2 style="margin-bottom: 1.5rem;">My Applications</h2>
            <?php if ($my_applications): ?>
                <?php foreach ($my_applications as $app): ?>
                    <div class="glass-card" style="margin-bottom: 1rem; padding: 1.2rem; display: flex; gap: 1rem; align-items: flex-start; border-color: rgba(255,255,255,0.03);">
                        <!-- Company Logo -->
                        <?php 
                            $company_name = $app['company'];
                            $words = explode(' ', $company_name);
                            $initials = '';
                            foreach($words as $w){
                                if(!empty($w)) $initials .= strtoupper(substr($w, 0, 1));
                            }
                            if(strlen($initials) > 2) $initials = substr($initials, 0, 2);
                        ?>
                        <div class="company-logo" style="width: 45px; height: 45px; font-size: 1.1rem; flex-shrink: 0; background: rgba(255,255,255,0.03); border-radius: 50%;">
                            <?php echo htmlspecialchars($initials); ?>
                        </div>
                        
                        <div style="flex-grow: 1;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem;">
                                <div>
                                    <h4 style="margin: 0; font-size: 1rem; color: white;"><?php echo htmlspecialchars($app['title']); ?></h4>
                                    <div style="font-size: 0.8rem; color: #94a3b8; margin-top: 0.1rem;"><?php echo htmlspecialchars($app['company']); ?></div>
                                </div>
                                <span class="badge status-<?php echo $app['status']; ?>" style="text-transform: capitalize; font-size: 0.65rem; padding: 0.3rem 0.6rem;">
                                    <?php echo str_replace('_', ' ', $app['status']); ?>
                                </span>
                            </div>
                            
                            <?php if ($app['status'] == 'interview_scheduled'): ?>
                                <div style="background: rgba(99, 102, 241, 0.08); border: 1px solid rgba(99, 102, 241, 0.15); padding: 0.6rem; border-radius: 8px; margin: 0.8rem 0; font-size: 0.75rem;">
                                    <div style="color: var(--secondary-color); font-weight: 600; margin-bottom: 0.2rem;"><i class="fas fa-calendar-check"></i> Interview</div>
                                    <div style="color: #cbd5e1;">
                                        <?php echo date('M d', strtotime($app['interview_date'])); ?> @ <?php echo date('h:i A', strtotime($app['interview_time'])); ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if ($app['status'] == 'shortlisted'): ?>
                                <div style="display: flex; gap: 0.4rem; margin: 0.8rem 0;">
                                    <a href="manage_response.php?id=<?php echo $app['id']; ?>&action=accepted" class="btn" style="background: var(--accent-color); color: white; padding: 0.4rem; font-size: 0.75rem; flex: 1; text-align: center; border-radius: 6px;">Accept</a>
                                    <a href="manage_response.php?id=<?php echo $app['id']; ?>&action=declined" class="btn" style="background: rgba(255,255,255,0.03); color: #f87171; padding: 0.4rem; font-size: 0.75rem; flex: 1; text-align: center; border: 1px solid rgba(248, 113, 113, 0.2); border-radius: 6px;">Decline</a>
                                </div>
                            <?php endif; ?>

                            <div style="display: flex; justify-content: flex-end;">
                                <span style="font-size: 0.65rem; color: #475569;">Applied <?php echo date('M d', strtotime($app['applied_at'])); ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

            <?php else: ?>
                <div class="glass-card" style="text-align: center; color: #64748b; padding: 2rem;">
                    <p>You haven't applied to any jobs yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php include '../includes/footer.php'; ?>
