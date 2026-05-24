<?php
// register.php
require_once 'config/db.php';
session_start();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'];

    // Basic validation for role
    if (!in_array($role, ['student', 'company', 'dept_po'])) {
        $error = "Invalid role selected.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Handle student-specific fields
        $department = null;
        $resume_url = null;
        
        if ($role === 'student') {
            $department = $_POST['department'] ?? null;
            $cgpa = $_POST['cgpa'] ?? null;
            $semester = $_POST['semester'] ?? null;
            $passing_year = $_POST['passing_year'] ?? null;
            $skills = $_POST['skills'] ?? null;
            $institute = $_POST['institute'] ?? null;
        } else if ($role === 'company') {
            $website = $_POST['website'] ?? null;
            $address = $_POST['address'] ?? null;
            $city = $_POST['city'] ?? null;
            $state = $_POST['state'] ?? null;
        } else if ($role === 'dept_po') {
            $managed_department = $_POST['managed_department'] ?? null;
        }
            
        // Handle profile picture/logo upload for both roles
        $profile_pic = 'default_profile.png';
        if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/profiles/';
            $file_extension = strtolower(pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp'];
            
            // MIME type validation
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $_FILES['profile_pic']['tmp_name']);
            finfo_close($finfo);
            $allowed_mimes = ['image/jpeg', 'image/png', 'image/webp'];
            
            if (in_array($file_extension, $allowed_extensions) && in_array($mime_type, $allowed_mimes)) {
                $new_filename = uniqid('profile_') . '.' . $file_extension;
                $target_path = $upload_dir . $new_filename;
                if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target_path)) {
                    $profile_pic = $target_path;
                } else {
                    $error = "Failed to upload image.";
                }
            } else {
                $error = "Invalid image format or content. Only valid JPG, PNG, and WebP images are allowed.";
            }
        }

        // Handle resume upload (Student Only)
        if ($role === 'student' && empty($error) && isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/resumes/';
            $file_extension = strtolower(pathinfo($_FILES['resume']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['pdf', 'doc', 'docx'];
            
            // MIME type validation
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $_FILES['resume']['tmp_name']);
            finfo_close($finfo);
            $allowed_mimes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
            
            if (in_array($file_extension, $allowed_extensions) && in_array($mime_type, $allowed_mimes)) {
                $new_filename = uniqid('resume_') . '.' . $file_extension;
                $target_path = $upload_dir . $new_filename;
                
                if (move_uploaded_file($_FILES['resume']['tmp_name'], $target_path)) {
                    $resume_url = $target_path;
                } else {
                    $error = "Failed to upload resume file.";
                }
            } else {
                $error = "Invalid file type or content. Only valid PDF and DOC/DOCX files are allowed.";
            }
        }

        if (empty($error)) {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = "Email already registered.";
            } else if ($role === 'dept_po') {
                // Check if a Dept PO already exists for this department
                $stmt = $pdo->prepare("SELECT id FROM users WHERE role = 'dept_po' AND managed_department = ?");
                $stmt->execute([$managed_department]);
                if ($stmt->fetch()) {
                    $error = "A Department Placement Officer is already registered for this department.";
                }
            }
            
            if (empty($error)) {
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);

                if ($role === 'student') {
                    $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, role, department, cgpa, semester, passing_year, skills, institute, profile_pic, resume_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $exec_result = $stmt->execute([$full_name, $email, $hashed_password, $role, $department, $cgpa, $semester, $passing_year, $skills, $institute, $profile_pic, $resume_url]);
                } else if ($role === 'dept_po') {
                    $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, role, managed_department, profile_pic) VALUES (?, ?, ?, ?, ?, ?)");
                    $exec_result = $stmt->execute([$full_name, $email, $hashed_password, $role, $managed_department, $profile_pic]);
                } else {
                    $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, role, website, address, city, state, profile_pic) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $exec_result = $stmt->execute([$full_name, $email, $hashed_password, $role, $website, $address, $city, $state, $profile_pic]);
                }
                
                if ($exec_result) {
                    $success = "Registration successful! You can now <a href='login.php' style='color:inherit; font-weight:bold;'>login</a>.";
                } else {
                    $error = "Registration failed. Please try again.";
                }
            }
        }
    }
}
?>

<?php include 'includes/header.php'; ?>

<div style="max-width: 500px; margin: 4rem auto;">
    <div class="glass-card">
        <h2 style="margin-bottom: 2rem; text-align: center;">Create Account</h2>
        
        <?php if ($error): ?>
            <div style="background: rgba(239, 68, 68, 0.2); border: 1px solid var(--danger-color); padding: 1rem; border-radius: 10px; margin-bottom: 1.5rem; color: #fca5a5; font-size: 0.9rem;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div style="background: rgba(16, 185, 129, 0.2); border: 1px solid var(--accent-color); padding: 1rem; border-radius: 10px; margin-bottom: 1.5rem; color: #6ee7b7; font-size: 0.9rem;">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <form action="register.php" method="POST" enctype="multipart/form-data">
            <div class="form-group" style="margin-bottom: 2.5rem;">
                <div class="role-selection-grid">
                    <div>
                        <input type="radio" name="role" id="role-student" value="student" checked required onclick="toggleFields('student')">
                        <label for="role-student" class="role-card">
                            <i class="fas fa-user-graduate"></i>
                            <span>Student</span>
                        </label>
                    </div>
                    <div>
                        <input type="radio" name="role" id="role-company" value="company" required onclick="toggleFields('company')">
                        <label for="role-company" class="role-card">
                            <i class="fas fa-building"></i>
                            <span>Company</span>
                        </label>
                    </div>
                    <div>
                        <input type="radio" name="role" id="role-dept_po" value="dept_po" required onclick="toggleFields('dept_po')">
                        <label for="role-dept_po" class="role-card">
                            <i class="fas fa-user-shield"></i>
                            <span>Dept. PO</span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="full_name" class="form-control" placeholder="John Doe" required>
            </div>
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="john@college.edu" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>
            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" placeholder="••••••••" required>
            </div>
            <div id="student-only-fields">
                <div class="form-group">
                    <label>Profile Picture</label>
                    <input type="file" name="profile_pic" class="form-control" accept="image/*">
                </div>

                <div class="form-group">
                    <label>Department</label>
                    <select name="department" class="form-control" id="department-select">
                        <option value="">Select Department</option>
                        <option value="Computer Engineering">Computer Engineering</option>
                        <option value="Civil">Civil Engineering</option>
                        <option value="Mechanical">Mechanical Engineering</option>
                        <option value="Electronics">Electronics Engineering</option>
                        <option value="Computer Hardware Engineering">Computer Hardware Engineering</option>
                    </select>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div class="form-group">
                        <label>College / Institute</label>
                        <input type="text" name="institute" class="form-control" id="institute-input" placeholder="e.g. ABC College">
                    </div>
                    <div class="form-group">
                        <label>Year of Passing</label>
                        <input type="number" name="passing_year" class="form-control" id="passing-year-input" min="2000" max="2100" placeholder="e.g. 2026">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div class="form-group">
                        <label>CGPA (Out of 10.0)</label>
                        <input type="number" name="cgpa" class="form-control" id="cgpa-input" step="0.01" min="0" max="10" placeholder="e.g. 8.5">
                    </div>
                    <div class="form-group">
                        <label>Current Semester</label>
                        <input type="text" name="semester" class="form-control" id="semester-input" placeholder="e.g. Semester 6">
                    </div>
                </div>

                <div class="form-group">
                    <label>Top Skills (Comma Separated)</label>
                    <input type="text" name="skills" class="form-control" id="skills-input" placeholder="e.g. Java, Python, AutoCad">
                </div>
                
                <div class="form-group">
                    <label>Upload Resume (PDF/DOCX format only)</label>
                    <input type="file" name="resume" class="form-control" accept=".pdf,.doc,.docx" id="resume-upload">
                </div>
            </div>

            <div id="dept-po-only-fields" style="display: none;">
                <div class="form-group">
                    <label>Managed Department</label>
                    <select name="managed_department" class="form-control" id="managed-department-select">
                        <option value="">Select Department to Manage</option>
                        <option value="Computer Engineering">Computer Engineering</option>
                        <option value="Civil">Civil Engineering</option>
                        <option value="Mechanical">Mechanical Engineering</option>
                        <option value="Electronics">Electronics Engineering</option>
                        <option value="Computer Hardware Engineering">Computer Hardware Engineering</option>
                    </select>
                </div>
            </div>

            <div id="company-only-fields" style="display: none;">
                <div class="form-group">
                    <label>Company/Institution Logo</label>
                    <input type="file" name="profile_pic" class="form-control" accept="image/*" id="company-logo">
                </div>
                
                <div class="form-group">
                    <label>Company Website</label>
                    <input type="url" name="website" class="form-control" placeholder="https://www.example.com" id="company-website">
                </div>

                <div class="form-group">
                    <label>Company Headquarters Address</label>
                    <input type="text" name="address" class="form-control" placeholder="123 Tech Park Ave" id="company-address">
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div class="form-group">
                        <label>City</label>
                        <input type="text" name="city" class="form-control" id="company-city" placeholder="e.g. San Francisco">
                    </div>
                    <div class="form-group">
                        <label>State / Region</label>
                        <input type="text" name="state" class="form-control" id="company-state" placeholder="e.g. CA">
                    </div>
                </div>
            </div>


            <script>
                function toggleFields(role) {
                    const studentFields = document.getElementById('student-only-fields');
                    const companyFields = document.getElementById('company-only-fields');
                    const deptPoFields = document.getElementById('dept-po-only-fields');

                    const deptSelect = document.getElementById('department-select');
                    const managedDeptSelect = document.getElementById('managed-department-select');

                    const studentInputs = [
                        document.getElementById('institute-input'),
                        document.getElementById('passing-year-input'),
                        document.getElementById('cgpa-input'),
                        document.getElementById('semester-input'),
                        document.getElementById('skills-input')
                    ];

                    const companyInputs = [
                        document.getElementById('company-website'),
                        document.getElementById('company-address'),
                        document.getElementById('company-city'),
                        document.getElementById('company-state')
                    ];

                    if (role === 'student') {
                        studentFields.style.display = 'block';
                        companyFields.style.display = 'none';
                        deptPoFields.style.display = 'none';

                        deptSelect.setAttribute('required', 'required');
                        managedDeptSelect.removeAttribute('required');
                        studentInputs.forEach(input => input.setAttribute('required', 'required'));
                        companyInputs.forEach(input => input.removeAttribute('required'));
                        
                        document.getElementById('company-logo').disabled = true;
                        
                    } else if (role === 'company') {
                        studentFields.style.display = 'none';
                        companyFields.style.display = 'block';
                        deptPoFields.style.display = 'none';

                        deptSelect.removeAttribute('required');
                        managedDeptSelect.removeAttribute('required');
                        studentInputs.forEach(input => input.removeAttribute('required'));
                        companyInputs.forEach(input => input.setAttribute('required', 'required'));

                        document.getElementById('company-logo').disabled = false;
                    } else if (role === 'dept_po') {
                        studentFields.style.display = 'none';
                        companyFields.style.display = 'none';
                        deptPoFields.style.display = 'block';

                        deptSelect.removeAttribute('required');
                        managedDeptSelect.setAttribute('required', 'required');
                        studentInputs.forEach(input => input.removeAttribute('required'));
                        companyInputs.forEach(input => input.removeAttribute('required'));

                        document.getElementById('company-logo').disabled = true;
                    }
                }
                // Initialize state on page load
                toggleFields(document.querySelector('input[name="role"]:checked').value);
            </script>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem; padding: 1rem;">Create Account</button>
        </form>
        
        <p style="text-align: center; margin-top: 2rem; color: #94a3b8; font-size: 0.9rem;">
            Already have an account? <a href="login.php" style="color: var(--secondary-color); text-decoration: none;">Login here</a>
        </p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
