<?php

session_name("staff");
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: admin_login.php");
    exit;
}

require_once "../includes/config.php";

$job_id = "";
$job_data = null;
$feedback_msg = "";
$feedback_class = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['job_id'])) {
    $job_id = $_POST['job_id'];

    $pdf_filename = null;
    $update_pdf = false;

    if (isset($_POST['delete_pdf']) && $_POST['delete_pdf'] == '1') {

        $sql_get = "SELECT job_description_pdf FROM jobs WHERE job_id = ?";
        if($stmt_get = $conn->prepare($sql_get)) {
            $stmt_get->bind_param("i", $job_id);
            $stmt_get->execute();
            $result = $stmt_get->get_result();
            if($row = $result->fetch_assoc()) {
                $old_pdf = $row['job_description_pdf'];
                if($old_pdf && file_exists('../uploads/job_pdfs/' . $old_pdf)) {
                    unlink('../uploads/job_pdfs/' . $old_pdf);
                }
            }
            $stmt_get->close();
        }
        $pdf_filename = null;
        $update_pdf = true;
    }

    elseif (isset($_FILES['job_pdf']) && $_FILES['job_pdf']['error'] == UPLOAD_ERR_OK) {
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

            $sql_get = "SELECT job_description_pdf FROM jobs WHERE job_id = ?";
            if($stmt_get = $conn->prepare($sql_get)) {
                $stmt_get->bind_param("i", $job_id);
                $stmt_get->execute();
                $result = $stmt_get->get_result();
                if($row = $result->fetch_assoc()) {
                    $old_pdf = $row['job_description_pdf'];
                    if($old_pdf && file_exists('../uploads/job_pdfs/' . $old_pdf)) {
                        unlink('../uploads/job_pdfs/' . $old_pdf);
                    }
                }
                $stmt_get->close();
            }

            $pdf_filename = uniqid('job_', true) . '.pdf';
            $upload_path = '../uploads/job_pdfs/' . $pdf_filename;
            
            if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
                $feedback_msg = "Failed to upload PDF file.";
                $feedback_class = "alert-error";
                $pdf_filename = null;
            } else {
                $update_pdf = true;
            }
        }
    }

    if (empty($feedback_msg)) {
        if ($update_pdf) {
            $sql = "UPDATE jobs SET job_title = ?, description = ?, package_lpa = ?, required_cgpa = ?, max_backlogs = ?, job_description_pdf = ? WHERE job_id = ?";
            if($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("ssddisi",  
                    $_POST['job_title'],
                    $_POST['description'],
                    $_POST['package_lpa'],
                    $_POST['required_cgpa'],
                    $_POST['max_backlogs'],
                    $pdf_filename,
                    $job_id
                );
                if($stmt->execute()) {
                    $_SESSION['flash_success'] = "Job posting updated successfully!";
                    header("Location: admin_edit_job.php?id=" . urlencode($job_id));
                    exit;
                } else {
                    $feedback_msg = "Error updating record. Please try again.";
                    $feedback_class = "alert-error";
                }
                $stmt->close();
            }
        } else {
            $sql = "UPDATE jobs SET job_title = ?, description = ?, package_lpa = ?, required_cgpa = ?, max_backlogs = ? WHERE job_id = ?";
            if($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("ssddii", 
                    $_POST['job_title'],
                    $_POST['description'],
                    $_POST['package_lpa'],
                    $_POST['required_cgpa'],
                    $_POST['max_backlogs'],
                    $job_id
                );
                if($stmt->execute()) {
                    $_SESSION['flash_success'] = "Job posting updated successfully!";
                    header("Location: admin_edit_job.php?id=" . urlencode($job_id));
                    exit;
                } else {
                    $feedback_msg = "Error updating record. Please try again.";
                    $feedback_class = "alert-error";
                }
                $stmt->close();
            }
        }
    }
} 

else if (isset($_GET['id'])) {
    $job_id = $_GET['id'];

}

if (!empty($job_id)) {
    $sql_fetch = "SELECT * FROM jobs WHERE job_id = ?";
    if($stmt_fetch = $conn->prepare($sql_fetch)) {
        $stmt_fetch->bind_param("i", $job_id);
        $stmt_fetch->execute();
        $result = $stmt_fetch->get_result();
        if ($result->num_rows == 1) {
            $job_data = $result->fetch_assoc();
        } else {
            $feedback_msg = "Error: Job posting not found.";
            $feedback_class = "alert-error";
        }
        $stmt_fetch->close();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Job Posting - Admin Dashboard</title>
    <?php include '../includes/header_includes.php'; ?>
</head>
<body>
    <div class="dashboard-layout">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="dashboard-main">
            <?php include '../includes/header.php'; ?>
            
            <div class="container" style="padding-top: 2rem;">
                
                <div style="margin-bottom: 2rem; display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <h1 style="font-size: 1.5rem; margin-bottom: 0.5rem;">Edit Job Posting</h1>
                        <p class="text-muted">Update job details for <?php echo htmlspecialchars($job_data['company_name'] ?? ''); ?>.</p>
                    </div>
                    <a href="../admin/admin_manage_jobs.php" class="btn btn-secondary">
                        <i data-lucide="arrow-left" style="width: 16px;"></i> Back to List
                    </a>
                </div>

                <div class="card" style="max-width: 800px; margin: 0 auto;">
                    
                    <?php if(!empty($feedback_msg)): ?>
                        <div class="alert <?php echo $feedback_class; ?>" style="<?php echo ($feedback_class === 'alert-success') ? 'background:#d1fae5; color:#065f46;' : 'background:#fee2e2; color:#991b1b;'; ?> padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
                            <?php echo $feedback_msg; ?>
                        </div>
                    <?php endif; ?>

                    <?php if($job_data): ?>
                    <form action="../admin/admin_edit_job.php" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="job_id" value="<?php echo htmlspecialchars($job_data['job_id']); ?>">

                        <div class="form-group">
                            <label class="form-label" for="job_title">Job Title / Role</label>
                            <input type="text" id="job_title" name="job_title" class="form-control" value="<?php echo htmlspecialchars($job_data['job_title']); ?>" required>
                        </div>
                        
                        <div class="grid grid-cols-3 gap-4">
                            <div class="form-group">
                                <label class="form-label" for="package_lpa">Package (LPA)</label>
                                <input type="number" step="0.01" id="package_lpa" name="package_lpa" class="form-control" value="<?php echo htmlspecialchars($job_data['package_lpa']); ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="required_cgpa">Min CGPA</label>
                                <input type="number" step="0.01" id="required_cgpa" name="required_cgpa" class="form-control" value="<?php echo htmlspecialchars($job_data['required_cgpa']); ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="max_backlogs">Max Backlogs</label>
                                <input type="number" id="max_backlogs" name="max_backlogs" class="form-control" value="<?php echo htmlspecialchars($job_data['max_backlogs']); ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="description">Job Description</label>
                            <textarea id="description" name="description" class="form-control" rows="6"><?php echo htmlspecialchars($job_data['description']); ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Job Description PDF</label>
                            <?php if (!empty($job_data['job_description_pdf'])): ?>
                                <div style="background: var(--bg-body); padding: 1rem; border-radius: var(--radius-md); margin-bottom: 1rem;">
                                    <div style="display: flex; align-items: center; justify-content: space-between;">
                                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                                            <i data-lucide="file-text" style="width: 20px; color: var(--primary);"></i>
                                            <span style="font-weight: 500;"><?php echo htmlspecialchars($job_data['job_description_pdf']); ?></span>
                                        </div>
                                        <div style="display: flex; gap: 0.5rem;">
                                            <a href="../uploads/job_pdfs/<?php echo htmlspecialchars($job_data['job_description_pdf']); ?>" target="_blank" class="btn btn-sm btn-ghost" title="View PDF">
                                                <i data-lucide="external-link" style="width: 14px;"></i> View
                                            </a>
                                            <button type="button" onclick="document.getElementById('delete_pdf').value='1'; document.getElementById('pdf_delete_notice').style.display='block'; this.parentElement.parentElement.parentElement.style.display='none';" class="btn btn-sm btn-ghost-danger" title="Remove PDF">
                                                <i data-lucide="trash-2" style="width: 14px;"></i> Remove
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div id="pdf_delete_notice" style="display: none; background: #fef3c7; color: #92400e; padding: 0.75rem; border-radius: var(--radius-md); margin-bottom: 1rem; font-size: 0.875rem;">
                                    <i data-lucide="alert-triangle" style="width: 14px; vertical-align: middle;"></i> PDF will be deleted when you save changes.
                                </div>
                                <input type="hidden" id="delete_pdf" name="delete_pdf" value="0">
                            <?php endif; ?>
                            <input type="file" id="job_pdf" name="job_pdf" class="form-control" accept=".pdf" style="padding: 0.5rem;">
                            <small class="text-muted" style="font-size: 0.75rem; display: block; margin-top: 0.25rem;">
                                <?php if (!empty($job_data['job_description_pdf'])): ?>
                                    Upload a new PDF to replace the current one. Max file size: 5MB
                                <?php else: ?>
                                    Upload a PDF file (optional). Max file size: 5MB
                                <?php endif; ?>
                            </small>
                        </div>
                        
                        <div style="margin-top: 2rem;">
                            <button type="submit" class="btn btn-primary">
                                <i data-lucide="save"></i> Save Changes
                            </button>
                        </div>
                    </form>
                    <?php endif; ?>
                </div>

            </div>
        </main>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>