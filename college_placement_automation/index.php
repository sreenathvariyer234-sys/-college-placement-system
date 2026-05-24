<?php
// index.php
require_once 'config/db.php';
include 'includes/header.php';

// Fetch some featured jobs
$stmt = $pdo->query("SELECT * FROM jobs ORDER BY created_at DESC LIMIT 3");
$featured_jobs = $stmt->fetchAll();
?>

<div style="text-align: center; padding: 4rem 0;">
    <h1 style="font-size: 3.5rem; margin-bottom: 1.5rem; background: linear-gradient(to right, #818cf8, #c084fc); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
        Elevate Your Career with PlacementHub
    </h1>
    <p style="font-size: 1.25rem; color: #94a3b8; max-width: 800px; margin: 0 auto 3rem;">
        The all-in-one platform for students to find their dream jobs and for companies to discover top-tier campus talent.
    </p>
    
    <?php if (!isset($_SESSION['user_id'])): ?>
        <div style="display: flex; gap: 1rem; justify-content: center;">
            <a href="register.php" class="btn btn-primary" style="padding: 1rem 2rem; font-size: 1.1rem;">Get Started as Student</a>
            <a href="login.php" class="btn" style="background: rgba(255,255,255,0.1); color: white; padding: 1rem 2rem; font-size: 1.1rem; border: 1px solid rgba(255,255,255,0.1);">Administrator Login</a>
        </div>
    <?php else: ?>
        <div style="display: flex; gap: 1rem; justify-content: center;">
            <?php if (in_array($_SESSION['role'], ['super_admin', 'college_po', 'dept_po'])): ?>
                <a href="admin/dashboard.php" class="btn btn-primary" style="padding: 1rem 2rem; font-size: 1.1rem;">Go to Admin Dashboard</a>
            <?php else: ?>
                <a href="student/dashboard.php" class="btn btn-primary" style="padding: 1rem 2rem; font-size: 1.1rem;">View My Applications</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<div style="margin-top: 4rem;">
    <h2 style="margin-bottom: 2rem;">Recent Job Openings</h2>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
        <?php if ($featured_jobs): ?>
            <?php foreach ($featured_jobs as $job): ?>
                <div class="glass-card" style="display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <div style="color: var(--secondary-color); font-weight: bold; margin-bottom: 0.5rem;"><?php echo htmlspecialchars($job['company']); ?></div>
                        <h3 style="margin-bottom: 1rem;"><?php echo htmlspecialchars($job['title']); ?></h3>
                        <p style="color: #94a3b8; font-size: 0.95rem; margin-bottom: 1.5rem; line-height: 1.6;">
                            <?php echo substr(htmlspecialchars($job['description']), 0, 150) . '...'; ?>
                        </p>
                    </div>
                    <div style="display: flex; gap: 1rem; color: #64748b; font-size: 0.85rem; margin-bottom: 1rem;">
                        <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($job['location']); ?></span>
                        <?php if(!empty($job['vacancies'])): ?>
                            <span style="color: var(--accent-color);"><i class="fas fa-users"></i> <?php echo htmlspecialchars($job['vacancies']); ?> Vacancies</span>
                        <?php endif; ?>
                    </div>
                    <div style="display: flex; justify-content: flex-end; align-items: center;">
                        <a href="login.php" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.85rem;">Apply Now</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="glass-card" style="grid-column: 1 / -1; text-align: center; padding: 3rem;">
                <p style="color: #94a3b8;">No jobs posted yet. Check back soon!</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<div id="about" style="margin-top: 8rem; display: grid; grid-template-columns: 1fr 1fr; gap: 4rem; align-items: center;">
    <div>
        <h2 style="font-size: 2.5rem; margin-bottom: 1.5rem;" class="text-gradient">Bridging the Gap Between Ambition and Opportunity</h2>
        <p style="color: #94a3b8; line-height: 1.8; margin-bottom: 2rem; font-size: 1.1rem;">
            PlacementHub is a comprehensive college placement automation system designed to streamline the recruitment process. We empower students to showcase their skills, assist department officers in tracking progress, and provide companies with a seamless interface to find the best talent.
        </p>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="glass-card" style="padding: 1.5rem; text-align: left;">
                <i class="fas fa-rocket" style="font-size: 1.5rem; color: var(--secondary-color); margin-bottom: 1rem;"></i>
                <h4 style="margin-bottom: 0.5rem;">Fast Selection</h4>
                <p style="color: #64748b; font-size: 0.85rem;">Automated shortlisting and instant notifications.</p>
            </div>
            <div class="glass-card" style="padding: 1.5rem; text-align: left;">
                <i class="fas fa-shield-alt" style="font-size: 1.5rem; color: var(--accent-color); margin-bottom: 1rem;"></i>
                <h4 style="margin-bottom: 0.5rem;">Secure Profile</h4>
                <p style="color: #64748b; font-size: 0.85rem;">Verified academic data and professional resumes.</p>
            </div>
        </div>
    </div>
    <div class="glass-card" style="padding: 3rem; background: linear-gradient(135deg, rgba(99, 102, 241, 0.1), rgba(192, 132, 252, 0.1)); border: 1px solid rgba(255,255,255,0.1);">
        <h3 style="margin-bottom: 2rem;">How It Works</h3>
        <div class="animate-list">
            <div style="display: flex; gap: 1.5rem; margin-bottom: 2rem;">
                <div style="width: 40px; height: 40px; background: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; flex-shrink: 0;">1</div>
                <div>
                    <h4 style="margin-bottom: 0.3rem;">Create Your Profile</h4>
                    <p style="color: #64748b; font-size: 0.9rem;">Register with your academic details, CGPA, and professional resume.</p>
                </div>
            </div>
            <div style="display: flex; gap: 1.5rem; margin-bottom: 2rem;">
                <div style="width: 40px; height: 40px; background: var(--secondary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; flex-shrink: 0;">2</div>
                <div>
                    <h4 style="margin-bottom: 0.3rem;">Apply for Jobs</h4>
                    <p style="color: #64748b; font-size: 0.9rem;">Browse department-specific or common openings and apply with one click.</p>
                </div>
            </div>
            <div style="display: flex; gap: 1.5rem;">
                <div style="width: 40px; height: 40px; background: var(--accent-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; flex-shrink: 0;">3</div>
                <div>
                    <h4 style="margin-bottom: 0.3rem;">Get Recruited</h4>
                    <p style="color: #64748b; font-size: 0.9rem;">Track applications, attend interviews, and accept official offers.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div style="margin: 8rem 0; text-align: center;">
    <h2 style="margin-bottom: 3rem;">Trusted by Top Departments</h2>
    <div style="display: flex; gap: 2rem; justify-content: center; flex-wrap: wrap; opacity: 0.6;">
        <span class="badge" style="padding: 1rem 2rem; font-size: 1rem;">Computer Engineering</span>
        <span class="badge" style="padding: 1rem 2rem; font-size: 1rem;">Computer Hardware Engineering</span>
        <span class="badge" style="padding: 1rem 2rem; font-size: 1rem;">Electronics</span>
        <span class="badge" style="padding: 1rem 2rem; font-size: 1rem;">Mechanical</span>
        <span class="badge" style="padding: 1rem 2rem; font-size: 1rem;">Civil</span>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
