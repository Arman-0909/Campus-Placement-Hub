<?php

require_once '../includes/config.php';
session_start();
 
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["num"])){
    header("location: student_login.php");
    exit;
}
$regdno = $_SESSION["num"];

$student_data = null;
$placement_data = null;

$sql_student = "SELECT s.*, m.cgpa, m.backlogs FROM student s LEFT JOIN marks m ON s.regdno = m.regdno WHERE s.regdno = ?";
if ($stmt_student = $conn->prepare($sql_student)) {
    $stmt_student->bind_param("s", $regdno);
    $stmt_student->execute();
    $result = $stmt_student->get_result();
    $student_data = $result->fetch_assoc();
    $stmt_student->close();
}

if (!$student_data) { echo "Error: Student data not found."; exit; }

if ($student_data['placement_status'] == 'Placed') {
    $sql_placement = "SELECT p.package_lpa, p.placement_date, j.company_name, j.job_title FROM placements p LEFT JOIN jobs j ON p.job_id = j.job_id WHERE p.student_regdno = ?";
    if($stmt_placement = $conn->prepare($sql_placement)){
        $stmt_placement->bind_param("s", $regdno);
        $stmt_placement->execute();
        $result = $stmt_placement->get_result();
        $placement_data = $result->fetch_assoc();
        $stmt_placement->close();
    }
}

$resume_msg = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["resume"])) {
    $target_dir = "../uploads/resumes/";
    if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }
    
    $file_ext = strtolower(pathinfo($_FILES["resume"]["name"], PATHINFO_EXTENSION));
    $new_filename = "resume_" . $regdno . "." . $file_ext;
    $target_file = $target_dir . $new_filename;
    
    if ($file_ext != "pdf") {
        $resume_msg = "<div class='alert alert-danger'>Only PDF files are allowed.</div>";
    } elseif ($_FILES["resume"]["size"] > 2097152) { // 2MB
        $resume_msg = "<div class='alert alert-danger'>File size must be less than 2MB.</div>";
    } else {
        if (move_uploaded_file($_FILES["resume"]["tmp_name"], $target_file)) {
            $sql_update = "UPDATE student SET resume_path = ? WHERE regdno = ?";
            if ($stmt = $conn->prepare($sql_update)) {
                $stmt->bind_param("ss", $target_file, $regdno);
                $stmt->execute();
                $stmt->close();

                $student_data['resume_path'] = $target_file;
                $_SESSION['flash_message'] = "Resume uploaded successfully!";
                header("Location: student_dashboard.php");
                exit;
            }
        } else {
            $_SESSION['flash_error'] = "Error uploading file.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Student Portal</title>
    <?php include '../includes/header_includes.php'; ?>
</head>
<body>
    <div class="dashboard-layout">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="dashboard-main">
            <?php include '../includes/header.php'; ?>
            
            <div class="container" style="padding-top: 2rem;">
                <div style="margin-bottom: 2rem;">
                    <h1 style="font-size: 1.75rem; color: var(--secondary);">Hello, <?php echo htmlspecialchars($student_data['name']); ?> 👋</h1>
                    <p class="text-muted">Here's what's happening with your placement journey.</p>
                </div>
                <div class="grid grid-cols-3 gap-6" style="margin-bottom: 1.5rem;">
                    <div class="stat-card">
                        <div>
                            <div class="stat-label">Current CGPA</div>
                            <div class="stat-value"><?php echo htmlspecialchars(number_format($student_data['cgpa'] ?? 0, 2)); ?></div>
                        </div>
                        <div class="stat-icon purple">
                            <i data-lucide="graduation-cap"></i>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div>
                            <div class="stat-label">Active Backlogs</div>
                            <div class="stat-value"><?php echo htmlspecialchars($student_data['backlogs'] ?? 0); ?></div>
                        </div>
                        <div class="stat-icon orange">
                            <i data-lucide="alert-circle"></i>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div>
                            <div class="stat-label">Status</div>
                            <div class="stat-value" style="font-size: 1.5rem; color: <?php echo ($student_data['placement_status'] == 'Placed') ? 'var(--success)' : 'var(--primary)'; ?>">
                                <?php echo htmlspecialchars($student_data['placement_status']); ?>
                            </div>
                        </div>
                        <div class="stat-icon green">
                            <i data-lucide="flag"></i>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-6">
                    <div class="flex flex-col gap-6 col-span-2">
                        <div class="card" style="padding: 1rem;">
                            
                            <div class="flex justify-between items-center mb-6">
                                <h3 class="flex items-center gap-2" style="font-size: 1rem; color: var(--secondary); margin: 0;">
                                    <i data-lucide="file-text" style="width: 18px;"></i> My Resume
                                </h3>
                                <?php if (!empty($student_data['resume_path'])): ?>
                                    <a href="<?php echo htmlspecialchars($student_data['resume_path']); ?>" target="_blank" class="btn btn-sm btn-ghost" style="color: var(--primary); padding: 0.25rem 0.5rem; height: auto;">
                                        <i data-lucide="eye" style="width: 14px;"></i> View
                                    </a>
                                <?php endif; ?>
                            </div>

                            <?php if(!empty($resume_msg)): ?>
                                <div style="margin-bottom: 1rem;">
                                    <?php echo $resume_msg; ?>
                                </div>
                            <?php endif; ?>

                            <form action="" method="post" enctype="multipart/form-data" style="margin: 0;">
                                <div class="upload-area" id="drop-area" style="border: 2px dashed var(--border); border-radius: 0.5rem; padding: 1rem; text-align: center; background: var(--bg-body); transition: all 0.2s; cursor: pointer; position: relative;">
                                    <input type="file" name="resume" id="file-input" class="file-input" accept=".pdf" required style="position: absolute; top:0; left:0; width:100%; height:100%; opacity:0; cursor: pointer;">
                                    
                                    <div class="upload-content flex items-center justify-center gap-3">
                                        <div style="background: white; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: var(--shadow-sm); flex-shrink: 0;">
                                            <i data-lucide="upload-cloud" style="width: 18px; color: var(--primary);"></i>
                                        </div>
                                        <div class="text-left">
                                            <p style="margin: 0; font-size: 0.9rem; color: var(--secondary); font-weight: 500;">
                                                <span style="color: var(--primary);">Click to upload</span> or drag PDF
                                            </p>
                                            <p class="text-muted" style="font-size: 0.75rem; margin: 0;">Max 2MB</p>
                                        </div>
                                    </div>
                                    <div id="file-name" style="margin-top: 0.5rem; font-size: 0.85rem; color: var(--success); font-weight: 500; display: none;"></div>
                                </div>
                                <div style="margin-top: 0.75rem; text-align: right;">
                                    <button type="submit" class="btn btn-primary btn-sm" style="width: 100%; justify-content: center;">
                                        <i data-lucide="save" style="width: 16px;"></i> Upload Resume
                                    </button>
                                </div>
                            </form>
                        </div>

                        <script>
                            const fileInput = document.getElementById('file-input');
                            const dropArea = document.getElementById('drop-area');
                            const fileNameDisplay = document.getElementById('file-name');

                            ['dragenter', 'dragover'].forEach(eventName => {
                                dropArea.addEventListener(eventName, () => {
                                    dropArea.style.borderColor = 'var(--primary)';
                                    dropArea.style.background = 'var(--bg-input)';
                                }, false);
                            });

                            ['dragleave', 'drop'].forEach(eventName => {
                                dropArea.addEventListener(eventName, () => {
                                    dropArea.style.borderColor = 'var(--border)';
                                    dropArea.style.background = 'var(--bg-body)';
                                }, false);
                            });

                            fileInput.addEventListener('change', function() {
                                if(this.files && this.files[0]) {
                                    fileNameDisplay.style.display = 'block';
                                    fileNameDisplay.textContent = 'Selected: ' + this.files[0].name;
                                    fileNameDisplay.innerHTML = '<i data-lucide="check" style="width:14px; vertical-align:middle;"></i> ' + this.files[0].name;
                                    lucide.createIcons();
                                }
                            });
                        </script>
                        <div class="card" style="background: linear-gradient(135deg, var(--primary), var(--primary-hover)); color: white; border: none;">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h2 style="color: white; margin-bottom: 0.5rem;">Explore Opportunities</h2>
                                    <p style="opacity: 0.9; margin-bottom: 1.5rem; max-width: 400px;">
                                        Check out the latest job openings tailored for your department and eligibility criteria.
                                    </p>
                                    <div class="flex gap-4">
                                        <a href="../student/student_jobs.php" class="btn" style="background: white; color: var(--primary);">
                                            <i data-lucide="search"></i> Find Jobs
                                        </a>
                                        <a href="../student/student_applications.php" class="btn" style="background: rgba(255,255,255,0.2); color: white;">
                                            <i data-lucide="file-text"></i> My Applications
                                        </a>
                                    </div>
                                </div>
                                <i data-lucide="briefcase" style="width: 80px; height: 80px; opacity: 0.2;"></i>
                            </div>
                        </div>

                        <?php if ($placement_data): ?>
                        <div class="card" style="border-left: 5px solid var(--success);">
                            <div class="flex gap-4 items-start">
                                <div style="background: #ecfdf5; color: var(--success); padding: 1rem; border-radius: 1rem;">
                                    <i data-lucide="party-popper" style="width: 32px; height: 32px;"></i>
                                </div>
                                <div>
                                    <h3 style="margin-bottom: 0.25rem;">Congratulations! 🎉</h3>
                                    <p class="text-muted" style="margin-bottom: 0.5rem;">You have been placed at <strong><?php echo htmlspecialchars($placement_data['company_name']); ?></strong></p>
                                    <div class="flex gap-4 text-sm">
                                        <div class="flex items-center gap-2">
                                            <i data-lucide="briefcase" style="width: 14px;"></i>
                                            <?php echo htmlspecialchars($placement_data['job_title']); ?>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <i data-lucide="banknote" style="width: 14px;"></i>
                                            <?php echo htmlspecialchars($placement_data['package_lpa']); ?> LPA
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                    </div>
                    <div class="card" style="height: 100%; display: flex; flex-direction: column; overflow: hidden; padding: 0;">
                        <div style="background: linear-gradient(135deg, var(--bg-input), white); padding: 2rem 1.5rem 1.5rem; text-align: center; border-bottom: 1px solid var(--border);">
                            <div style="width: 80px; height: 80px; background: white; border: 4px solid white; box-shadow: var(--shadow-md); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; font-size: 2rem; color: var(--primary);">
                                <i data-lucide="user"></i>
                            </div>
                            <h3 style="margin-bottom: 0.25rem;"><?php echo htmlspecialchars($student_data['name']); ?></h3>
                            <div class="text-sm font-medium" style="color: var(--primary); background: var(--primary-light); display: inline-block; padding: 0.25rem 0.75rem; border-radius: 1rem; margin-top: 0.5rem;">
                                <?php echo htmlspecialchars($student_data['regdno']); ?>
                            </div>
                        </div>
                        
                        <div class="flex flex-col gap-4" style="padding: 1.5rem;">
                            <div class="flex items-center gap-3 p-2" style="transition: background 0.2s; border-radius: 0.5rem;">
                                <div style="width: 36px; height: 36px; background: var(--bg-input); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: var(--text-muted);"><i data-lucide="mail" style="width: 18px;"></i></div>
                                <div>
                                    <div class="text-xs text-muted font-medium uppercase">Email</div>
                                    <div class="text-sm truncate" style="font-weight: 500;"><?php echo htmlspecialchars($student_data['email']); ?></div>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 p-2" style="transition: background 0.2s; border-radius: 0.5rem;">
                                <div style="width: 36px; height: 36px; background: var(--bg-input); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: var(--text-muted);"><i data-lucide="phone" style="width: 18px;"></i></div>
                                <div>
                                    <div class="text-xs text-muted font-medium uppercase">Contact</div>
                                    <div class="text-sm" style="font-weight: 500;"><?php echo htmlspecialchars($student_data['contact']); ?></div>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 p-2" style="transition: background 0.2s; border-radius: 0.5rem;">
                                <div style="width: 36px; height: 36px; background: var(--bg-input); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: var(--text-muted);"><i data-lucide="calendar" style="width: 18px;"></i></div>
                                <div>
                                    <div class="text-xs text-muted font-medium uppercase">Date of Birth</div>
                                    <div class="text-sm" style="font-weight: 500;"><?php echo date("d M, Y", strtotime($student_data['dob'])); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </main>
    </div>
    
    <script>
        lucide.createIcons();
    </script>
</body>
</html>