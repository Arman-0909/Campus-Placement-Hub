<?php  

require_once "../includes/config.php";
session_name("staff");
session_start();
 
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: admin_login.php");
    exit;
}

$feedback_message = "";
$feedback_class = "";
$error_details = [];
$active_tab = isset($_POST['import_type']) ? $_POST['import_type'] : (isset($_GET['tab']) ? $_GET['tab'] : 'students');

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if(isset($_POST["submit"]))
{
    if(isset($_FILES['file']) && $_FILES['file']['error'] == 0)
    {
        $filename = explode(".", $_FILES['file']['name']);
        if(end($filename) == 'csv')
        {
            $handle = fopen($_FILES['file']['tmp_name'], "r");
            $success_count = 0;
            $error_count = 0;
            $skipped_count = 0;

            $header_keywords = ['regdno', 'registration', 'reg', 'regd', 'registration no', 'registration number', 'reg no', 'company', 'companyname', 'company name', 'company_name', 'job', 'job title', 'job_title', 'name', 'sr', 'sno', 's.no', 'sl'];
            $first_row = fgetcsv($handle);
            $is_header = false;
            if ($first_row && isset($first_row[0])) {
                $first_cell = strtolower(trim($first_row[0]));
                if (in_array($first_cell, $header_keywords)) {
                    $is_header = true;
                }
            }
            if (!$is_header) {
                rewind($handle);
            }
            $row_num = $is_header ? 1 : 0;

            if ($_POST['import_type'] === 'students') {
                while($data = fgetcsv($handle)) {
                    $row_num++;
                    if (count($data) >= 7) {
                        $regdno = trim($data[0]); $name = trim($data[1]); $email = trim($data[2]);
                        $contact = trim($data[3]); $dob = trim($data[4]); $department = trim($data[5]);
                        $password = trim($data[6]);
                        if (empty($regdno) || empty($name) || empty($email) || empty($password)) {
                            $error_count++; $error_details[] = "Row {$row_num}: Missing required fields"; continue;
                        }
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        try {
                            $stmt = $conn->prepare("INSERT INTO student (regdno, name, email, contact, dob, department, password) VALUES (?, ?, ?, ?, ?, ?, ?)");
                            $stmt->bind_param("sssssss", $regdno, $name, $email, $contact, $dob, $department, $hashed_password);
                            $stmt->execute(); $stmt->close(); $success_count++;
                        } catch (mysqli_sql_exception $e) {
                            $error_count++;
                            $error_details[] = ($e->getCode() == 1062) ? "Row {$row_num}: Duplicate '{$regdno}'" : "Row {$row_num}: Database error";
                        }
                    } else { $error_count++; $error_details[] = "Row {$row_num}: Expected 7 columns, got " . count($data); }
                }
            }

            elseif ($_POST['import_type'] === 'marks') {
                while($data = fgetcsv($handle)) {
                    $row_num++;
                    if (isset($data[0]) && isset($data[1]) && isset($data[2])) {
                        $regdno = trim($data[0]); $cgpa = trim($data[1]); $backlogs = trim($data[2]);
                        try {
                            $stmt = $conn->prepare("INSERT INTO marks (regdno, cgpa, backlogs) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE cgpa = VALUES(cgpa), backlogs = VALUES(backlogs)");
                            $stmt->bind_param("sdi", $regdno, $cgpa, $backlogs);
                            $stmt->execute();
                            if ($stmt->affected_rows > 0) { $success_count++; } else { $skipped_count++; }
                            $stmt->close();
                        } catch (mysqli_sql_exception $e) {
                            $error_count++;
                            $error_details[] = (strpos($e->getMessage(), 'oreign') !== false) ? "Row {$row_num}: Student '{$regdno}' not found" : "Row {$row_num}: Database error";
                        }
                    } else { $error_count++; $error_details[] = "Row {$row_num}: Expected 3 columns"; }
                }
            }

            elseif ($_POST['import_type'] === 'update_students') {
                while($data = fgetcsv($handle)) {
                    $row_num++;
                    if (count($data) >= 2) {
                        $regdno = trim($data[0]);
                        if (empty($regdno)) { $error_count++; $error_details[] = "Row {$row_num}: Missing Reg. No"; continue; }
                        $fields = []; $params = []; $types = "";
                        $col_map = [1 => 'name', 2 => 'department', 3 => 'contact', 4 => 'email', 5 => 'placement_status'];
                        foreach ($col_map as $idx => $db_field) {
                            if (isset($data[$idx]) && trim($data[$idx]) !== '') {
                                $fields[] = "$db_field = ?"; $params[] = trim($data[$idx]); $types .= "s";
                            }
                        }
                        if (empty($fields)) { $skipped_count++; continue; }
                        $fields[] = "last_updated_by = ?"; $params[] = $_SESSION['username']; $types .= "s";
                        $fields[] = "last_updated_on = NOW()";
                        $params[] = $regdno; $types .= "s";
                        try {
                            $stmt = $conn->prepare("UPDATE student SET " . implode(", ", $fields) . " WHERE regdno = ?");
                            $stmt->bind_param($types, ...$params); $stmt->execute();
                            if ($stmt->affected_rows > 0) { $success_count++; }
                            else {
                                $check = $conn->prepare("SELECT regdno FROM student WHERE regdno = ?");
                                $check->bind_param("s", $regdno); $check->execute(); $check->store_result();
                                if ($check->num_rows == 0) { $error_count++; $error_details[] = "Row {$row_num}: Student '{$regdno}' not found"; }
                                else { $skipped_count++; }
                                $check->close();
                            }
                            $stmt->close();
                        } catch (mysqli_sql_exception $e) { $error_count++; $error_details[] = "Row {$row_num}: Database error"; }
                    } else { $error_count++; $error_details[] = "Row {$row_num}: Expected at least 2 columns"; }
                }
            }

            elseif ($_POST['import_type'] === 'companies') {
                while($data = fgetcsv($handle)) {
                    $row_num++;
                    if (count($data) >= 1 && !empty(trim($data[0]))) {
                        $companyname = trim($data[0]);
                        $website = isset($data[1]) ? trim($data[1]) : null;
                        $description = isset($data[2]) ? trim($data[2]) : null;
                        try {
                            $stmt = $conn->prepare("INSERT INTO company (companyname, website, description) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE website = VALUES(website), description = VALUES(description)");
                            $stmt->bind_param("sss", $companyname, $website, $description);
                            $stmt->execute();
                            if ($stmt->affected_rows > 0) { $success_count++; } else { $skipped_count++; }
                            $stmt->close();
                        } catch (mysqli_sql_exception $e) {
                            $error_count++;
                            $error_details[] = ($e->getCode() == 1062) ? "Row {$row_num}: Duplicate '{$companyname}'" : "Row {$row_num}: Database error";
                        }
                    } else { $error_count++; $error_details[] = "Row {$row_num}: Company name is required"; }
                }
            }

            elseif ($_POST['import_type'] === 'jobs') {
                while($data = fgetcsv($handle)) {
                    $row_num++;

                    if (count($data) >= 2) {
                        $company_name = trim($data[0]);
                        $job_title = trim($data[1]);
                        $description = isset($data[2]) ? trim($data[2]) : null;
                        $package_lpa = isset($data[3]) && is_numeric(trim($data[3])) ? trim($data[3]) : null;
                        $required_cgpa = isset($data[4]) && is_numeric(trim($data[4])) ? trim($data[4]) : 6.00;
                        $max_backlogs = isset($data[5]) && is_numeric(trim($data[5])) ? intval(trim($data[5])) : 0;

                        if (empty($company_name) || empty($job_title)) {
                            $error_count++; $error_details[] = "Row {$row_num}: Company name and job title required"; continue;
                        }
                        try {
                            $stmt = $conn->prepare("INSERT INTO jobs (company_name, job_title, description, package_lpa, required_cgpa, max_backlogs) VALUES (?, ?, ?, ?, ?, ?)");
                            $stmt->bind_param("sssddi", $company_name, $job_title, $description, $package_lpa, $required_cgpa, $max_backlogs);
                            $stmt->execute(); $stmt->close(); $success_count++;
                        } catch (mysqli_sql_exception $e) {
                            $error_count++;
                            if (strpos($e->getMessage(), 'oreign') !== false || strpos($e->getMessage(), 'FOREIGN') !== false) {
                                $error_details[] = "Row {$row_num}: Company '{$company_name}' does not exist";
                            } else {
                                $error_details[] = "Row {$row_num}: Database error";
                            }
                        }
                    } else { $error_count++; $error_details[] = "Row {$row_num}: Expected at least 2 columns"; }
                }
            }

            fclose($handle);

            $parts = [];
            if ($success_count > 0) $parts[] = "<strong>{$success_count} succeeded</strong>";
            if ($skipped_count > 0) $parts[] = "{$skipped_count} unchanged";
            if ($error_count > 0) $parts[] = "<strong>{$error_count} failed</strong>";

            if ($success_count > 0 && $error_count == 0 && $skipped_count == 0) {
                $_SESSION['flash_message'] = "Success! All {$success_count} records processed.";
                header("Location: admin_import_data.php?tab=" . $active_tab);
                exit;
            } elseif (empty($parts)) {
                $feedback_message = "File was empty or contained no valid data.";
                $feedback_class = "warning";
            } else {
                $feedback_message = implode(" &middot; ", $parts);
                $feedback_class = ($error_count > 0 && $success_count == 0) ? "error" : (($error_count > 0) ? "warning" : "success");
            }

        } else {
            $feedback_message = "Please upload a valid CSV file.";
            $feedback_class = "error";
        }
    } else {
        $feedback_message = "No file was selected.";
        $feedback_class = "error";
    }
}
?>  
<!DOCTYPE html>  
<html lang="en">  
<head>  
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Data - Admin Dashboard</title>
    <?php include '../includes/header_includes.php'; ?>
    <style>
        .import-selector {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.5rem;
            margin-bottom: 2rem;
        }
        .import-tab {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.4rem;
            padding: 0.85rem 0.5rem;
            border-radius: var(--radius-md);
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--text-muted);
            cursor: pointer;
            transition: all 0.25s var(--ease);
            border: 1px solid var(--border);
            background: var(--bg-body);
            user-select: none;
        }
        .import-tab:hover {
            color: var(--text-main);
            border-color: #cbd5e1;
            background: white;
            transform: translateY(-1px);
        }
        .import-tab.active {
            background: var(--primary-light);
            color: var(--primary);
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.08);
        }
        .import-tab .tab-icon {
            width: 20px;
            height: 20px;
        }
        .import-selector .import-tab:nth-child(5) {
            grid-column: 1 / 2;
        }
        .import-selector .import-tab:nth-child(6) {
            grid-column: 2 / 3;
        }
        .selector-divider {
            grid-column: 1 / -1;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin: 0.25rem 0;
        }
        .selector-divider::before,
        .selector-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }
        .selector-divider span {
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--text-light);
            white-space: nowrap;
        }

        .tab-panel { display: none; animation: fadeIn 0.3s ease; }
        .tab-panel.active { display: block; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }

        .panel-header { text-align: center; margin-bottom: 2rem; }
        .panel-icon { width: 56px; height: 56px; border-radius: 16px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; }
        .panel-header h2 { font-size: 1.35rem; margin-bottom: 0.5rem; }
        .panel-header p { color: var(--text-muted); font-size: 0.9rem; max-width: 480px; margin: 0 auto; line-height: 1.5; }

        .csv-format { background: var(--bg-input); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 1rem 1.25rem; margin-bottom: 1.5rem; }
        .csv-format-label { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted); margin-bottom: 0.5rem; }
        .csv-format code { display: block; font-family: 'JetBrains Mono', 'SF Mono', 'Fira Code', monospace; font-size: 0.825rem; color: var(--primary); line-height: 1.6; word-break: break-all; }
        .csv-format .csv-example { color: var(--text-light); margin-top: 0.25rem; font-size: 0.775rem; }

        .upload-zone { position: relative; margin-bottom: 1.5rem; }
        .upload-zone input[type="file"] { position: absolute; width: 100%; height: 100%; opacity: 0; cursor: pointer; z-index: 2; }
        .upload-box { border: 2px dashed var(--border); border-radius: var(--radius-md); padding: 2rem; text-align: center; background: var(--bg-body); transition: all 0.25s var(--ease); }
        .upload-box.active { border-color: var(--primary); background: var(--primary-light); }
        .upload-box .upload-icon { color: var(--text-light); margin-bottom: 0.5rem; }
        .upload-box.active .upload-icon { color: var(--primary); }
        .upload-box .file-label { font-weight: 500; color: var(--text-muted); font-size: 0.9rem; }
        .upload-box.active .file-label { color: var(--text-main); }

        .import-feedback { border-radius: var(--radius-md); padding: 1rem 1.25rem; margin-bottom: 1.5rem; font-size: 0.9rem; line-height: 1.5; }
        .import-feedback.success { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
        .import-feedback.warning { background: #fffbeb; color: #92400e; border: 1px solid #fde68a; }
        .import-feedback.error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
        .error-details { margin-top: 0.75rem; max-height: 140px; overflow-y: auto; font-size: 0.8rem; opacity: 0.85; }
        .error-details div { padding: 0.2rem 0; border-bottom: 1px solid rgba(0,0,0,0.06); }
        .error-details div:last-child { border-bottom: none; }

        .info-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 0.75rem; margin-bottom: 1.5rem; }
        .info-chip { display: flex; align-items: center; gap: 0.5rem; padding: 0.625rem 0.875rem; background: var(--bg-body); border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 0.8rem; color: var(--text-muted); }
        .info-chip i { width: 14px; height: 14px; flex-shrink: 0; }

        .hint-box { background: #eff6ff; border: 1px solid #bfdbfe; border-radius: var(--radius-sm); padding: 0.75rem 1rem; margin-bottom: 1.5rem; font-size: 0.825rem; color: #1e40af; line-height: 1.5; }

        @media (max-width: 480px) {
            .import-selector { grid-template-columns: repeat(2, 1fr); }
            .import-selector .import-tab:nth-child(5),
            .import-selector .import-tab:nth-child(6) { grid-column: auto; }
        }
    </style>
</head>  
<body>  
    <div class="dashboard-layout">
        <?php include '../includes/sidebar.php'; ?>
        <main class="dashboard-main">
            <?php include '../includes/header.php'; ?>
            <div class="container" style="padding-top: 2rem;">
                <div class="card" style="max-width: 700px; margin: 0 auto;">
                    <div class="import-selector" id="tab-bar">
                        <button class="import-tab <?php echo ($active_tab === 'students') ? 'active' : ''; ?>" data-tab="students" type="button">
                            <i data-lucide="users-round" class="tab-icon"></i><span>Students</span>
                        </button>
                        <button class="import-tab <?php echo ($active_tab === 'marks') ? 'active' : ''; ?>" data-tab="marks" type="button">
                            <i data-lucide="file-bar-chart" class="tab-icon"></i><span>Marks</span>
                        </button>
                        <button class="import-tab <?php echo ($active_tab === 'update_students') ? 'active' : ''; ?>" data-tab="update_students" type="button">
                            <i data-lucide="user-cog" class="tab-icon"></i><span>Update Students</span>
                        </button>
                        <div class="selector-divider"><span>Company &amp; Jobs</span></div>
                        <button class="import-tab <?php echo ($active_tab === 'companies') ? 'active' : ''; ?>" data-tab="companies" type="button">
                            <i data-lucide="building-2" class="tab-icon"></i><span>Companies</span>
                        </button>
                        <button class="import-tab <?php echo ($active_tab === 'jobs') ? 'active' : ''; ?>" data-tab="jobs" type="button">
                            <i data-lucide="briefcase" class="tab-icon"></i><span>Jobs</span>
                        </button>
                    </div>

                    <?php

                    function render_feedback($msg, $cls, $errs, $active, $expected) {
                        if(!empty($msg) && $active === $expected): ?>
                        <div class="import-feedback <?php echo $cls; ?>">
                            <?php echo $msg; ?>
                            <?php if(!empty($errs)): ?>
                                <div class="error-details">
                                    <?php foreach(array_slice($errs, 0, 10) as $e): ?><div><?php echo htmlspecialchars($e); ?></div><?php endforeach; ?>
                                    <?php if(count($errs) > 10): ?><div style="font-style:italic;opacity:.7">...and <?php echo count($errs)-10; ?> more</div><?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; } ?>
                    <div class="tab-panel <?php echo ($active_tab === 'students') ? 'active' : ''; ?>" id="panel-students">
                        <div class="panel-header">
                            <div class="panel-icon" style="background: var(--primary-light); color: var(--primary);"><i data-lucide="users-round" style="width:28px;height:28px;"></i></div>
                            <h2>Bulk Import Students</h2>
                            <p>Add multiple new students at once. Each student gets a login account with the password you provide.</p>
                        </div>
                        <div class="csv-format">
                            <div class="csv-format-label">Expected CSV Format (7 columns)</div>
                            <code>RegdNo, Name, Email, Contact, DOB, Department, Password</code>
                            <code class="csv-example">240140, Rahul Kumar, rahul@email.com, 9876543210, 2003-05-15, CSE, pass123</code>
                        </div>
                        <div class="info-row">
                            <div class="info-chip"><i data-lucide="info"></i><span>Header auto-skipped</span></div>
                            <div class="info-chip"><i data-lucide="lock"></i><span>Passwords hashed</span></div>
                            <div class="info-chip"><i data-lucide="shield-check"></i><span>Duplicates rejected</span></div>
                        </div>
                        <?php render_feedback($feedback_message, $feedback_class, $error_details, $active_tab, 'students'); ?>
                        <form method="post" enctype="multipart/form-data">
                            <input type="hidden" name="import_type" value="students">
                            <div class="upload-zone">
                                <input type="file" name="file" accept=".csv" required>
                                <div class="upload-box"><div class="upload-icon"><i data-lucide="file-spreadsheet" style="width:32px;height:32px;margin:0 auto;"></i></div><span class="file-label">Click or drag to select CSV file</span></div>
                            </div>
                            <button type="submit" name="submit" class="btn btn-primary" style="width:100%;justify-content:center;"><i data-lucide="upload"></i> Import Students</button>
                        </form>
                    </div>
                    <div class="tab-panel <?php echo ($active_tab === 'marks') ? 'active' : ''; ?>" id="panel-marks">
                        <div class="panel-header">
                            <div class="panel-icon" style="background:#dbeafe;color:#2563eb;"><i data-lucide="file-bar-chart" style="width:28px;height:28px;"></i></div>
                            <h2>Import / Update Marks</h2>
                            <p>Set CGPA and backlog data. Automatically inserts new records or updates existing ones.</p>
                        </div>
                        <div class="csv-format">
                            <div class="csv-format-label">Expected CSV Format (3 columns)</div>
                            <code>RegdNo, CGPA, Backlogs</code>
                            <code class="csv-example">240101, 8.50, 0</code>
                        </div>
                        <div class="info-row">
                            <div class="info-chip"><i data-lucide="zap" style="color:var(--primary);"></i><span>Smart: Insert or Update</span></div>
                            <div class="info-chip"><i data-lucide="alert-triangle" style="color:var(--warning);"></i><span>Students must exist</span></div>
                        </div>
                        <?php render_feedback($feedback_message, $feedback_class, $error_details, $active_tab, 'marks'); ?>
                        <form method="post" enctype="multipart/form-data">
                            <input type="hidden" name="import_type" value="marks">
                            <div class="upload-zone">
                                <input type="file" name="file" accept=".csv" required>
                                <div class="upload-box"><div class="upload-icon"><i data-lucide="file-spreadsheet" style="width:32px;height:32px;margin:0 auto;"></i></div><span class="file-label">Click or drag to select CSV file</span></div>
                            </div>
                            <button type="submit" name="submit" class="btn btn-primary" style="width:100%;justify-content:center;"><i data-lucide="file-up"></i> Import / Update Marks</button>
                        </form>
                    </div>
                    <div class="tab-panel <?php echo ($active_tab === 'update_students') ? 'active' : ''; ?>" id="panel-update_students">
                        <div class="panel-header">
                            <div class="panel-icon" style="background:#fef3c7;color:#d97706;"><i data-lucide="user-cog" style="width:28px;height:28px;"></i></div>
                            <h2>Bulk Update Students</h2>
                            <p>Update placement status, department, contact info in bulk. Leave any column blank to keep its current value.</p>
                        </div>
                        <div class="csv-format">
                            <div class="csv-format-label">Expected CSV Format (up to 6 columns)</div>
                            <code>RegdNo, Name, Department, Contact, Email, PlacementStatus</code>
                            <code class="csv-example">240101, , , , , Placed</code>
                        </div>
                        <div class="hint-box"><strong>Status values:</strong> Not Placed, Placed, Seeking Better Offer, Not Interested</div>
                        <div class="info-row">
                            <div class="info-chip"><i data-lucide="minus"></i><span>Blank = keep current</span></div>
                            <div class="info-chip"><i data-lucide="user-check"></i><span>Tracks who updated</span></div>
                        </div>
                        <?php render_feedback($feedback_message, $feedback_class, $error_details, $active_tab, 'update_students'); ?>
                        <form method="post" enctype="multipart/form-data">
                            <input type="hidden" name="import_type" value="update_students">
                            <div class="upload-zone">
                                <input type="file" name="file" accept=".csv" required>
                                <div class="upload-box"><div class="upload-icon"><i data-lucide="file-spreadsheet" style="width:32px;height:32px;margin:0 auto;"></i></div><span class="file-label">Click or drag to select CSV file</span></div>
                            </div>
                            <button type="submit" name="submit" class="btn btn-primary" style="width:100%;justify-content:center;"><i data-lucide="refresh-cw"></i> Update Students</button>
                        </form>
                    </div>
                    <div class="tab-panel <?php echo ($active_tab === 'companies') ? 'active' : ''; ?>" id="panel-companies">
                        <div class="panel-header">
                            <div class="panel-icon" style="background:#fef3c7;color:#ea580c;"><i data-lucide="building-2" style="width:28px;height:28px;"></i></div>
                            <h2>Import Companies</h2>
                            <p>Add new companies or update existing ones. If a company name already exists, its website and description will be updated.</p>
                        </div>
                        <div class="csv-format">
                            <div class="csv-format-label">Expected CSV Format (up to 3 columns)</div>
                            <code>CompanyName, Website, Description</code>
                            <code class="csv-example">Flipkart, https://flipkart.com/careers, India's leading e-commerce marketplace</code>
                        </div>
                        <div class="info-row">
                            <div class="info-chip"><i data-lucide="zap" style="color:var(--primary);"></i><span>Smart: Insert or Update</span></div>
                            <div class="info-chip"><i data-lucide="globe"></i><span>Website is optional</span></div>
                        </div>
                        <?php render_feedback($feedback_message, $feedback_class, $error_details, $active_tab, 'companies'); ?>
                        <form method="post" enctype="multipart/form-data">
                            <input type="hidden" name="import_type" value="companies">
                            <div class="upload-zone">
                                <input type="file" name="file" accept=".csv" required>
                                <div class="upload-box"><div class="upload-icon"><i data-lucide="file-spreadsheet" style="width:32px;height:32px;margin:0 auto;"></i></div><span class="file-label">Click or drag to select CSV file</span></div>
                            </div>
                            <button type="submit" name="submit" class="btn btn-primary" style="width:100%;justify-content:center;"><i data-lucide="building-2"></i> Import Companies</button>
                        </form>
                    </div>
                    <div class="tab-panel <?php echo ($active_tab === 'jobs') ? 'active' : ''; ?>" id="panel-jobs">
                        <div class="panel-header">
                            <div class="panel-icon" style="background:#dbeafe;color:#2563eb;"><i data-lucide="briefcase" style="width:28px;height:28px;"></i></div>
                            <h2>Import Jobs</h2>
                            <p>Add multiple job postings at once. The company must already exist in the system before importing jobs for it.</p>
                        </div>
                        <div class="csv-format">
                            <div class="csv-format-label">Expected CSV Format (up to 6 columns)</div>
                            <code>CompanyName, JobTitle, Description, PackageLPA, RequiredCGPA, MaxBacklogs</code>
                            <code class="csv-example">Flipkart, Software Engineer, Build scalable systems, 18.00, 7.50, 0</code>
                        </div>
                        <div class="hint-box"><strong>Note:</strong> Company name must exactly match an existing company. CGPA defaults to 6.0 and backlogs to 0 if omitted.</div>
                        <div class="info-row">
                            <div class="info-chip"><i data-lucide="alert-triangle" style="color:var(--warning);"></i><span>Company must exist</span></div>
                            <div class="info-chip"><i data-lucide="settings-2"></i><span>Defaults for CGPA/Backlogs</span></div>
                        </div>
                        <?php render_feedback($feedback_message, $feedback_class, $error_details, $active_tab, 'jobs'); ?>
                        <form method="post" enctype="multipart/form-data">
                            <input type="hidden" name="import_type" value="jobs">
                            <div class="upload-zone">
                                <input type="file" name="file" accept=".csv" required>
                                <div class="upload-box"><div class="upload-icon"><i data-lucide="file-spreadsheet" style="width:32px;height:32px;margin:0 auto;"></i></div><span class="file-label">Click or drag to select CSV file</span></div>
                            </div>
                            <button type="submit" name="submit" class="btn btn-primary" style="width:100%;justify-content:center;"><i data-lucide="briefcase"></i> Import Jobs</button>
                        </form>
                    </div>

                </div>
            </div>
        </main>
    </div>
    
    <script>
        lucide.createIcons();
        document.querySelectorAll('.import-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                const target = this.dataset.tab;
                document.querySelectorAll('.import-tab').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
                this.classList.add('active');
                const panel = document.getElementById('panel-' + target);
                if (panel) panel.classList.add('active');
                const url = new URL(window.location);
                url.searchParams.set('tab', target);
                window.history.replaceState({}, '', url);
            });
        });
        document.querySelectorAll('.upload-zone').forEach(zone => {
            const input = zone.querySelector('input[type="file"]');
            const box = zone.querySelector('.upload-box');
            const label = zone.querySelector('.file-label');
            input.addEventListener('change', function() {
                if (this.files && this.files[0]) { label.textContent = this.files[0].name; box.classList.add('active'); }
                else { label.textContent = 'Click or drag to select CSV file'; box.classList.remove('active'); }
            });
            input.addEventListener('dragenter', () => box.classList.add('active'));
            input.addEventListener('dragleave', () => { if (!input.files || !input.files.length) box.classList.remove('active'); });
        });
    </script>
</body>  
</html>
