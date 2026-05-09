<?php

require_once '../includes/config.php';
session_start();
 
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["num"])){
    header("location: student_login.php");
    exit;
}
$regdno = $_SESSION["num"];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['apply_for_job'])) {
    $job_id_to_apply = $_POST['job_id'];
    
    $sql_apply = "INSERT INTO applications (job_id, student_regdno) VALUES (?, ?)";
    if($stmt_apply = $conn->prepare($sql_apply)) {
        $stmt_apply->bind_param("is", $job_id_to_apply, $regdno);
        try {
            if ($stmt_apply->execute()) {

                $conn->query("CREATE TABLE IF NOT EXISTS `admin_notifications` (
                  `notification_id` int(11) NOT NULL AUTO_INCREMENT,
                  `type` varchar(50) NOT NULL,
                  `title` varchar(255) NOT NULL,
                  `message` text NOT NULL,
                  `is_read` tinyint(1) NOT NULL DEFAULT 0,
                  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                  PRIMARY KEY (`notification_id`),
                  KEY `is_read` (`is_read`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

                $job_info_sql = "SELECT job_title, company_name FROM jobs WHERE job_id = ?";
                $stmt_job = $conn->prepare($job_info_sql);
                $stmt_job->bind_param("i", $job_id_to_apply);
                $stmt_job->execute();
                $job_result = $stmt_job->get_result()->fetch_assoc();
                $stmt_job->close();

                $stu_info_sql = "SELECT name FROM student WHERE regdno = ?";
                $stmt_stu = $conn->prepare($stu_info_sql);
                $stmt_stu->bind_param("s", $regdno);
                $stmt_stu->execute();
                $stu_result = $stmt_stu->get_result()->fetch_assoc();
                $stmt_stu->close();

                if ($job_result && $stu_result) {
                    $notif_title = "New Application Received";
                    $notif_message = $stu_result['name'] . " applied for " . $job_result['job_title'] . " at " . $job_result['company_name'] . ".";
                    
                    $notif_insert = "INSERT INTO admin_notifications (type, title, message) VALUES ('new_application', ?, ?)";
                    $stmt_an = $conn->prepare($notif_insert);
                    $stmt_an->bind_param("ss", $notif_title, $notif_message);
                    $stmt_an->execute();
                    $stmt_an->close();
                }

                $_SESSION['flash_success'] = "Application submitted successfully!";
            }
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1062) {
                $_SESSION['flash_error'] = "You have already applied for this job.";
            } else {
                $_SESSION['flash_error'] = "An error occurred. Please try again.";
            }
        }
        $stmt_apply->close();
        header("location: student_jobs.php");
        exit;
    }
}

$student_cgpa = 0;
$student_backlogs = 0;
$student_name = "Student";
$student_placed = false;
$student_package = 0;

$sql_student = "SELECT s.name, m.cgpa, m.backlogs, p.package_lpa 
                FROM student s 
                LEFT JOIN marks m ON s.regdno = m.regdno 
                LEFT JOIN placements p ON s.regdno = p.student_regdno 
                WHERE s.regdno = ?";
if ($stmt_student = $conn->prepare($sql_student)) {
    $stmt_student->bind_param("s", $regdno);
    $stmt_student->execute();
    $result = $stmt_student->get_result();
    if($student_data = $result->fetch_assoc()) {
        $student_name = $student_data['name'];
        $student_cgpa = $student_data['cgpa'] ?? 0;
        $student_backlogs = $student_data['backlogs'] ?? 0;
        $student_package = $student_data['package_lpa'] ?? 0;
        $student_placed = !empty($student_data['package_lpa']);
    }
    $stmt_student->close();
}

$role_filter = $_GET['role'] ?? '';

$eligible_jobs = [];
$sql_jobs = "SELECT * FROM jobs WHERE required_cgpa <= ? AND max_backlogs >= ?";
$types = "di";
$params = [$student_cgpa, $student_backlogs];

if (!empty($role_filter)) {
    $sql_jobs .= " AND job_title LIKE ?";
    $types .= "s";
    $params[] = "%" . $role_filter . "%";
}

$sql_jobs .= " ORDER BY package_lpa DESC";

if ($stmt_jobs = $conn->prepare($sql_jobs)) {
    $stmt_jobs->bind_param($types, ...$params);
    $stmt_jobs->execute();
    $result_jobs = $stmt_jobs->get_result();
    while($row = $result_jobs->fetch_assoc()){
        $eligible_jobs[] = $row;
    }
    $stmt_jobs->close();
}

$applied_job_ids = [];
$sql_applied = "SELECT job_id FROM applications WHERE student_regdno = ?";
if($stmt_applied = $conn->prepare($sql_applied)){
    $stmt_applied->bind_param("s", $regdno);
    $stmt_applied->execute();
    $result_applied = $stmt_applied->get_result();
    while($row = $result_applied->fetch_assoc()){
        $applied_job_ids[] = $row['job_id'];
    }
    $stmt_applied->close();
}

$status_filter = $_GET['status'] ?? 'all';

$available_count = 0;
$applied_count = 0;
foreach ($eligible_jobs as $job) {
    if (in_array($job['job_id'], $applied_job_ids)) {
        $applied_count++;
    } else {
        $available_count++;
    }
}

if ($status_filter === 'applied') {
    $eligible_jobs = array_filter($eligible_jobs, function($job) use ($applied_job_ids) {
        return in_array($job['job_id'], $applied_job_ids);
    });
} elseif ($status_filter === 'available') {
    $eligible_jobs = array_filter($eligible_jobs, function($job) use ($applied_job_ids) {
        return !in_array($job['job_id'], $applied_job_ids);
    });
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eligible Jobs - Student Portal</title>
    <?php include '../includes/header_includes.php'; ?>
    <style>
        .job-card-modern {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            border: 1px solid var(--border);
            transition: all 0.2s;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        .job-card-modern:hover {
            box-shadow: var(--shadow-md);
            border-color: var(--primary-light);
            transform: translateY(-2px);
        }
        .package-badge {
            background: #ecfdf5;
            color: #059669; /* Green text */
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            font-size: 0.9rem;
            font-weight: 700;
            text-align: center;
            border: 1px solid #d1fae5;
            min-width: 100px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body>
    <div class="dashboard-layout">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="dashboard-main">
            <?php include '../includes/header.php'; ?>
            
            <div class="container" style="padding-top: 2rem;">
            
                <div class="flex justify-between items-center mb-4" style="margin-bottom: 2rem;">
                    <div>
                        <h1 style="font-size: 1.5rem; margin-bottom: 0.5rem;">Eligible Jobs</h1>
                        <p class="text-muted">
                            Based on your profile (CGPA: <strong><?php echo number_format($student_cgpa, 2); ?></strong>, Backlogs: <strong><?php echo $student_backlogs; ?></strong>)
                        </p>
                        <?php if ($student_placed && $student_package > 2): ?>
                            <div style="background: #fef3c7; color: #92400e; padding: 0.75rem 1rem; border-radius: var(--radius-md); margin-top: 1rem; font-size: 0.875rem; display: flex; align-items: center; gap: 0.5rem;">
                                <i data-lucide="alert-triangle" style="width: 16px; flex-shrink: 0;"></i>
                                <span>You are already placed with a package of <strong><?php echo number_format($student_package, 2); ?> LPA</strong>. You can only apply to jobs if your current package is ≤ 2 LPA.</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card" style="margin-bottom: 1.5rem;">
                    <form action="" method="get" class="grid grid-cols-2 gap-4 mobile-stack" style="align-items: end;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label"><i data-lucide="briefcase" style="width: 14px; vertical-align: middle;"></i> Job Role</label>
                            <input type="text" name="role" class="form-control" placeholder="Search by job role (e.g. Developer)" value="<?php echo htmlspecialchars($role_filter); ?>">
                            <?php if ($status_filter !== 'all'): ?>
                                <input type="hidden" name="status" value="<?php echo htmlspecialchars($status_filter); ?>">
                            <?php endif; ?>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label" style="visibility: hidden;">Action</label>
                            <div class="flex gap-2">
                                <button type="submit" class="btn btn-primary" style="height: 42px; flex: 1;">
                                    <i data-lucide="search"></i> Search
                                </button>
                                <?php if ($role_filter): ?>
                                <a href="student_jobs.php<?php echo ($status_filter !== 'all') ? '?status=' . htmlspecialchars($status_filter) : ''; ?>" class="btn btn-secondary" style="height: 42px;" title="Clear Search">
                                    <i data-lucide="x"></i>
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </form>
                </div>
                <div style="display: flex; gap: 0.5rem; margin-bottom: 1.5rem;">
                    <a href="student_jobs.php<?php echo $role_filter ? '?role=' . urlencode($role_filter) : ''; ?>" 
                       class="btn btn-sm <?php echo ($status_filter === 'all') ? 'btn-primary' : 'btn-secondary'; ?>" 
                       style="border-radius: 2rem; padding: 0.4rem 1rem; font-size: 0.8rem;">
                        All <span style="background: <?php echo ($status_filter === 'all') ? 'rgba(255,255,255,0.2)' : 'var(--bg-input)'; ?>; padding: 0.1rem 0.5rem; border-radius: 1rem; margin-left: 0.25rem; font-size: 0.75rem;"><?php echo count($eligible_jobs); ?></span>
                    </a>
                    <a href="student_jobs.php?status=available<?php echo $role_filter ? '&role=' . urlencode($role_filter) : ''; ?>" 
                       class="btn btn-sm <?php echo ($status_filter === 'available') ? 'btn-primary' : 'btn-secondary'; ?>" 
                       style="border-radius: 2rem; padding: 0.4rem 1rem; font-size: 0.8rem;">
                        Available <span style="background: <?php echo ($status_filter === 'available') ? 'rgba(255,255,255,0.2)' : 'var(--bg-input)'; ?>; padding: 0.1rem 0.5rem; border-radius: 1rem; margin-left: 0.25rem; font-size: 0.75rem;"><?php echo $available_count; ?></span>
                    </a>
                    <a href="student_jobs.php?status=applied<?php echo $role_filter ? '&role=' . urlencode($role_filter) : ''; ?>" 
                       class="btn btn-sm <?php echo ($status_filter === 'applied') ? 'btn-primary' : 'btn-secondary'; ?>" 
                       style="border-radius: 2rem; padding: 0.4rem 1rem; font-size: 0.8rem;">
                        Applied <span style="background: <?php echo ($status_filter === 'applied') ? 'rgba(255,255,255,0.2)' : 'var(--bg-input)'; ?>; padding: 0.1rem 0.5rem; border-radius: 1rem; margin-left: 0.25rem; font-size: 0.75rem;"><?php echo $applied_count; ?></span>
                    </a>
                </div>

                <?php if (empty($eligible_jobs)): ?>
                    <div style="text-align: center; padding: 4rem 2rem; background: white; border-radius: 1rem; border: 1px solid var(--border);">
                        <div style="background: var(--bg-input); width: 64px; height: 64px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; color: var(--text-muted);">
                            <i data-lucide="frown"></i>
                        </div>
                        <h3>No matching jobs found</h3>
                        <p class="text-muted">Keep improving your score to unlock more opportunities.</p>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 gap-6">
                        <?php foreach($eligible_jobs as $job): ?>
                            <div class="job-card-modern">
                                <div class="flex justify-between items-start">
                                    <div class="flex gap-4">
                                        <div style="width: 48px; height: 48px; background: var(--bg-input); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; font-weight: 700; color: var(--text-muted);">
                                            <?php echo strtoupper(substr($job['company_name'], 0, 1)); ?>
                                        </div>
                                        <div>
                                            <h3 style="font-size: 1.1rem; margin-bottom: 0.25rem;"><?php echo htmlspecialchars($job['job_title']); ?></h3>
                                            <div class="text-muted text-sm font-medium"><?php echo htmlspecialchars($job['company_name']); ?></div>
                                        </div>
                                    </div>
                                    <div class="package-badge">
                                        <?php echo htmlspecialchars($job['package_lpa']); ?> LPA
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-4 text-sm text-muted" style="background: var(--bg-body); padding: 1rem; border-radius: 0.5rem;">
                                    <div class="flex items-center gap-2">
                                        <i data-lucide="star" style="width: 16px;"></i> Min CGPA: <strong><?php echo htmlspecialchars($job['required_cgpa']); ?></strong>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <i data-lucide="alert-circle" style="width: 16px;"></i> Max Backlogs: <strong><?php echo htmlspecialchars($job['max_backlogs']); ?></strong>
                                    </div>
                                </div>
                                
                                <p class="text-sm text-muted" style="flex: 1;">
                                    <?php echo nl2br(htmlspecialchars($job['description'])); ?>
                                </p>
                                
                                <?php if (!empty($job['job_description_pdf'])): ?>
                                    <div style="background: var(--bg-body); padding: 0.75rem 1rem; border-radius: var(--radius-md); display: flex; align-items: center; gap: 1rem;">
                                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                                            <i data-lucide="file-text" style="width: 18px; color: var(--primary);"></i>
                                            <span style="font-size: 0.875rem; font-weight: 500;">Job Description PDF</span>
                                        </div>
                                        <div style="display: flex; gap: 0.5rem;">
                                            <a href="../uploads/job_pdfs/<?php echo htmlspecialchars($job['job_description_pdf']); ?>" target="_blank" class="btn btn-sm btn-ghost" title="View PDF">
                                                <i data-lucide="eye" style="width: 14px;"></i> View
                                            </a>
                                            <a href="../uploads/job_pdfs/<?php echo htmlspecialchars($job['job_description_pdf']); ?>" class="btn btn-sm btn-ghost" download title="Download PDF">
                                                <i data-lucide="download" style="width: 14px;"></i> Download
                                            </a>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <div style="border-top: 1px solid var(--border); padding-top: 1rem;">
                                    <?php 
                                    $can_apply = !($student_placed && $student_package > 2);
                                    $already_applied = in_array($job['job_id'], $applied_job_ids);
                                    ?>
                                    
                                    <?php if ($already_applied): ?>
                                        <button class="btn btn-secondary w-full" disabled style="width: 100%; opacity: 0.7; cursor: not-allowed; justify-content: center;">
                                            <i data-lucide="check-circle" style="width: 18px;"></i> Applied
                                        </button>
                                    <?php elseif (!$can_apply): ?>
                                        <button class="btn btn-secondary w-full" disabled style="width: 100%; opacity: 0.6; cursor: not-allowed; justify-content: center;" title="You are placed with a package > 2 LPA">
                                            <i data-lucide="lock" style="width: 18px;"></i> Application Restricted
                                        </button>
                                    <?php else: ?>
                                        <form action="../student/student_jobs.php" method="post" style="margin:0;">
                                            <input type="hidden" name="job_id" value="<?php echo $job['job_id']; ?>">
                                            <input type="hidden" name="apply_for_job" value="1">
                                            <button type="submit" class="btn btn-primary w-full" style="width: 100%; justify-content: center;" onclick="confirmApply(event, this.form)">
                                                Apply Now
                                                <i data-lucide="arrow-right" style="width: 18px;"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
            </div>
        </main>
    </div>
    
    <style>
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.2s ease-in-out;
            backdrop-filter: blur(4px);
        }
        
        .modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }
        
        .modal-container {
            background: white;
            border-radius: var(--radius-lg);
            width: 90%;
            max-width: 400px;
            padding: 2rem;
            text-align: center;
            transform: scale(0.95);
            transition: all 0.2s ease-in-out;
            box-shadow: var(--shadow-xl);
        }
        
        .modal-overlay.active .modal-container {
            transform: scale(1);
        }
        
        .modal-icon {
            width: 64px;
            height: 64px;
            background: var(--primary-light);
            color: var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
        }
        
        .modal-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: var(--secondary);
        }
        
        .modal-text {
            color: var(--text-muted);
            margin-bottom: 2rem;
            line-height: 1.5;
        }
        
        .modal-actions {
            display: flex;
            gap: 1rem;
        }
        
        .modal-actions .btn {
            flex: 1;
            justify-content: center;
        }
    </style>
    <div class="modal-overlay" id="confirmModal">
        <div class="modal-container">
            <div class="modal-icon">
                <i data-lucide="briefcase" style="width: 32px; height: 32px;"></i>
            </div>
            <h3 class="modal-title">Confirm Application</h3>
            <p class="modal-text">Are you sure you want to apply for this position? This action cannot be undone.</p>
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmBtn">Confirm Apply</button>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();
        
        let pendingForm = null;

        function confirmApply(event, form) {
            event.preventDefault();
            pendingForm = form;
            document.getElementById('confirmModal').classList.add('active');
        }

        function closeModal() {
            document.getElementById('confirmModal').classList.remove('active');
            pendingForm = null;
        }

        document.getElementById('confirmBtn').addEventListener('click', function() {
            if (pendingForm) {
                pendingForm.submit();
            }
            closeModal();
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeModal();
        });

        document.getElementById('confirmModal').addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });
    </script>
</body>
</html>