<?php

session_name("staff");
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: admin_login.php");
    exit;
}

require_once "../includes/config.php";

$feedback_msg = "";
$feedback_class = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_job'])) {
    if (empty(trim($_POST['company_name'])) || empty(trim($_POST['job_title']))) {
        $feedback_msg = "Company Name and Job Title are required.";
        $feedback_class = "alert-error";
    } else {

        $pdf_filename = null;
        if (isset($_FILES['job_pdf']) && $_FILES['job_pdf']['error'] == UPLOAD_ERR_OK) {
            $file = $_FILES['job_pdf'];
            $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if ($file_ext !== 'pdf') {
                $feedback_msg = "Only PDF files are allowed.";
                $feedback_class = "alert-error";
            }

            elseif ($file['size'] > 5 * 1024 * 1024) {
                $feedback_msg = "File size must be less than 5MB.";
                $feedback_class = "alert-error";
            } else {

                $pdf_filename = uniqid('job_', true) . '.pdf';
                $upload_path = '../uploads/job_pdfs/' . $pdf_filename;

                if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
                    $feedback_msg = "Failed to upload PDF file.";
                    $feedback_class = "alert-error";
                    $pdf_filename = null;
                }
            }
        }

        if (empty($feedback_msg)) {
            $sql = "INSERT INTO jobs (company_name, job_title, description, package_lpa, required_cgpa, max_backlogs, job_description_pdf) VALUES (?, ?, ?, ?, ?, ?, ?)";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("sssddis", 
                    $_POST['company_name'],
                    $_POST['job_title'],
                    $_POST['description'],
                    $_POST['package_lpa'],
                    $_POST['required_cgpa'],
                    $_POST['max_backlogs'],
                    $pdf_filename
                );
                
                if ($stmt->execute()) {

                    $conn->query("CREATE TABLE IF NOT EXISTS `notifications` (
                      `notification_id` int(11) NOT NULL AUTO_INCREMENT,
                      `student_regdno` varchar(11) NOT NULL,
                      `type` varchar(50) NOT NULL,
                      `title` varchar(255) NOT NULL,
                      `message` text NOT NULL,
                      `is_read` tinyint(1) NOT NULL DEFAULT 0,
                      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                      PRIMARY KEY (`notification_id`),
                      KEY `student_regdno` (`student_regdno`),
                      KEY `is_read` (`is_read`),
                      CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`student_regdno`) REFERENCES `student` (`regdno`) ON DELETE CASCADE
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

                    $job_title_val = $_POST['job_title'];
                    $company_val = $_POST['company_name'];
                    $notif_title = "New Job Posted";
                    $notif_msg = "$company_val is hiring for $job_title_val. Check eligibility and apply now!";
                    
                    $sql_notif = "INSERT INTO notifications (student_regdno, type, title, message) SELECT regdno, 'new_job', ?, ? FROM student";
                    if ($stmt_notif = $conn->prepare($sql_notif)) {
                        $stmt_notif->bind_param("ss", $notif_title, $notif_msg);
                        $stmt_notif->execute();
                        $stmt_notif->close();
                    }
                    
                    $_SESSION['flash_message'] = "New job posting for '" . htmlspecialchars($_POST['company_name']) . "' added successfully!";
                    header("location: admin_manage_jobs.php");
                    exit;
                } else {

                    if ($pdf_filename && file_exists('../uploads/job_pdfs/' . $pdf_filename)) {
                        unlink('../uploads/job_pdfs/' . $pdf_filename);
                    }
                    $feedback_msg = "Error: Could not add job posting. Please ensure the company exists.";
                    $feedback_class = "alert-error";
                }
                $stmt->close();
            }
        }
    }
}

$jobs = [];
$sql_fetch_jobs = "SELECT * FROM jobs ORDER BY created_at DESC";
if ($result = $conn->query($sql_fetch_jobs)) {
    while ($row = $result->fetch_assoc()) {
        $jobs[] = $row;
    }
    $result->free();
}

$companies = [];
$sql_fetch_companies = "SELECT companyname FROM company ORDER BY companyname ASC";
if ($result = $conn->query($sql_fetch_companies)) {
    while ($row = $result->fetch_assoc()) {
        $companies[] = $row;
    }
    $result->free();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Jobs - Admin Dashboard</title>
    <?php include '../includes/header_includes.php'; ?>
</head>
    <style>
        .table-container {
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            font-size: 0.9rem;
        }
        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }
        th {
            background-color: var(--bg-input);
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
        }
        tr:last-child td {
            border-bottom: none;
        }
        tr:hover {
            background-color: var(--bg-body);
        }
        th:nth-child(1) { width: 35%; } /* Role & Company */
        th:nth-child(2) { width: 30%; } /* Eligibility */
        th:nth-child(3) { width: 20%; } /* Package */
        th:nth-child(4) { width: 15%; } /* Actions */
    </style>
</head>
<body>
    <div class="dashboard-layout">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="dashboard-main">
            <?php include '../includes/header.php'; ?>
            
            <div class="container" style="padding-top: 2rem;">
                
                <div style="margin-bottom: 2rem;">
                    <h1 style="font-size: 1.5rem; margin-bottom: 0.5rem;">Manage Job Postings</h1>
                    <p class="text-muted">Create and oversee job opportunities for students.</p>
                </div>

                <div class="grid grid-cols-3 gap-6" style="grid-template-columns: 1fr 2fr;">
                    <div class="card" style="height: fit-content;">
                        <div class="card-header" style="margin-bottom: 1.5rem;">
                            <h3><i data-lucide="briefcase" style="width: 20px; vertical-align: middle;"></i> New Job Posting</h3>
                        </div>

                        <?php if(!empty($feedback_msg) && $feedback_class !== 'alert-success'): ?>
                            <div class="alert <?php echo $feedback_class; ?>" style="background:var(--bg-error); color:var(--text-error); padding:0.75rem; border-radius:0.5rem; margin-bottom:1rem;">
                                <?php echo $feedback_msg; ?>
                            </div>
                        <?php endif; ?>

                        <form action="../admin/admin_manage_jobs.php" method="post" enctype="multipart/form-data">
                            <div class="form-group">
                                <label class="form-label" for="company_name">Company</label>
                                <div class="select-wrapper">
                                    <select id="company_name" name="company_name" class="form-select" required>
                                        <option value="">-- Select Company --</option>
                                        <?php foreach ($companies as $company): ?>
                                            <option value="<?php echo htmlspecialchars($company['companyname']); ?>"><?php echo htmlspecialchars($company['companyname']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="job_title">Job Title</label>
                                <input type="text" id="job_title" name="job_title" class="form-control" placeholder="e.g. Graduate Trainee" required>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="form-group">
                                    <label class="form-label" for="package_lpa">Package (LPA)</label>
                                    <input type="number" step="0.01" id="package_lpa" name="package_lpa" class="form-control" placeholder="4.5">
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="required_cgpa">Min CGPA</label>
                                    <input type="number" step="0.01" id="required_cgpa" name="required_cgpa" class="form-control" value="6.0">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="max_backlogs">Max Backlogs</label>
                                <input type="number" id="max_backlogs" name="max_backlogs" class="form-control" value="0">
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="description">Description (Optional)</label>
                                <textarea id="description" name="description" class="form-control" rows="4"></textarea>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="job_pdf">Job Description PDF (Optional)</label>
                                <input type="file" id="job_pdf" name="job_pdf" class="form-control" accept=".pdf" style="padding: 0.5rem;">
                                <small class="text-muted" style="font-size: 0.75rem; display: block; margin-top: 0.25rem;">Max file size: 5MB</small>
                            </div>
                            <button type="submit" name="add_job" class="btn btn-primary w-full" style="width: 100%; justify-content: center;">
                                <i data-lucide="plus"></i> Post Job
                            </button>
                        </form>
                    </div>
                    <div class="card">
                        <div class="card-header" style="margin-bottom: 1.5rem; display: flex; flex-direction: column; gap: 1rem;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <h3>Current Job Postings (<?php echo count($jobs); ?>)</h3>
                            </div>
                            <div class="grid grid-cols-3 gap-4 mobile-stack">
                                <input type="text" id="job-search" placeholder="Search role or company..." class="form-control" style="padding: 0.5rem; font-size: 0.9rem;">
                                <select id="filter-company" class="form-select" style="padding: 0.5rem; font-size: 0.9rem; border: 1px solid var(--border); border-radius: var(--radius-md);">
                                    <option value="">All Companies</option>
                                    <?php 
                                    $unique_companies = array_unique(array_column($jobs, 'company_name'));
                                    sort($unique_companies);
                                    foreach ($unique_companies as $company): ?>
                                        <option value="<?php echo htmlspecialchars($company); ?>"><?php echo htmlspecialchars($company); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <select id="filter-role" class="form-select" style="padding: 0.5rem; font-size: 0.9rem; border: 1px solid var(--border); border-radius: var(--radius-md);">
                                    <option value="">All Roles</option>
                                    <?php 
                                    $unique_roles = array_unique(array_column($jobs, 'job_title'));
                                    sort($unique_roles);
                                    foreach ($unique_roles as $role): ?>
                                        <option value="<?php echo htmlspecialchars($role); ?>"><?php echo htmlspecialchars($role); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Role & Company</th>
                                        <th>Eligibility</th>
                                        <th>Package</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($jobs)): ?>
                                        <tr><td colspan="4" class="text-center text-muted">No jobs found.</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($jobs as $job): 
                                            $search_text = strtolower(($job['job_title'] ?? '') . ' ' . ($job['company_name'] ?? ''));
                                        ?>
                                            <tr class="job-row" 
                                                data-search="<?php echo htmlspecialchars($search_text); ?>"
                                                data-company="<?php echo htmlspecialchars($job['company_name'] ?? ''); ?>"
                                                data-role="<?php echo htmlspecialchars($job['job_title'] ?? ''); ?>">
                                                <td>
                                                    <div class="font-bold"><?php echo htmlspecialchars($job['job_title'] ?? 'N/A'); ?></div>
                                                    <div class="text-xs text-muted"><?php echo htmlspecialchars($job['company_name'] ?? 'N/A'); ?></div>
                                                </td>
                                                <td class="text-sm">
                                                    <div>CGPA: <?php echo htmlspecialchars($job['required_cgpa']); ?>+</div>
                                                    <div>Backlogs: <?php echo htmlspecialchars($job['max_backlogs']); ?></div>
                                                </td>
                                                <td><?php echo htmlspecialchars($job['package_lpa'] ?? 'N/A'); ?> LPA</td>
                                                <td>
                                                    <div class="flex gap-2">
                                                        <a href="admin_edit_job.php?id=<?php echo $job['job_id']; ?>" class="btn btn-sm btn-ghost" title="Edit">
                                                            <i data-lucide="edit-2" style="width: 14px;"></i>
                                                        </a>
                                                        <a href="admin_delete_job.php?id=<?php echo $job['job_id']; ?>" class="btn btn-sm btn-ghost-danger" title="Delete" 
                                                           onclick="return showDeleteModal(event, this.href, 'Delete Job', 'Are you sure you want to delete this job posting?');">
                                                            <i data-lucide="trash-2" style="width: 14px;"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </main>
    </div>

    <script>
        lucide.createIcons();

        const searchInput = document.getElementById('job-search');
        const companyFilter = document.getElementById('filter-company');
        const roleFilter = document.getElementById('filter-role');
        const rows = document.querySelectorAll('.job-row');

        function filterJobs() {
            const searchTerm = searchInput.value.toLowerCase().trim();
            const selectedCompany = companyFilter.value;
            const selectedRole = roleFilter.value;
            let visibleCount = 0;

            rows.forEach(row => {
                const text = row.getAttribute('data-search');
                const company = row.getAttribute('data-company');
                const role = row.getAttribute('data-role');

                const matchesSearch = text.includes(searchTerm);
                const matchesCompany = selectedCompany === '' || company === selectedCompany;
                const matchesRole = selectedRole === '' || role === selectedRole;

                if (matchesSearch && matchesCompany && matchesRole) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

        }

        if(searchInput) searchInput.addEventListener('keyup', filterJobs);
        if(companyFilter) companyFilter.addEventListener('change', filterJobs);
        if(roleFilter) roleFilter.addEventListener('change', filterJobs);

        document.addEventListener('DOMContentLoaded', function() {
            if (typeof TablePagination !== 'undefined') {
                new TablePagination('table', 10);
            }
        });
    </script>
    

    <script>
        lucide.createIcons();
    </script>
</body>
</html>