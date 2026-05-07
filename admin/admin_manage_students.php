<?php

session_name("staff");
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["role"]) || $_SESSION["role"] !== 'Admin'){
    header("location: admin_login.php");
    exit;
}
require_once "../includes/config.php";

$add_error = "";
$add_success = "";
$is_hod = (isset($_SESSION['role']) && $_SESSION['role'] === 'Hod');
$department = $_SESSION['department'] ?? '';

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_student'])) {
        $regdno = trim($_POST['regdno']);
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $contact = trim($_POST['contact']);
        $dob = trim($_POST['dob']);
    $student_department = $is_hod ? $department : trim($_POST['department']);
    $password = trim($_POST['password']);
    
    if (empty($regdno) || empty($name) || empty($email) || empty($contact) || empty($dob) || empty($student_department) || empty($password)) {
        $add_error = "Please fill in all fields.";
    } else {

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO student (regdno, name, email, contact, dob, department, password) VALUES (?, ?, ?, ?, ?, ?, ?)";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("sssssss", $regdno, $name, $email, $contact, $dob, $student_department, $hashed_password);
            try {
                $stmt->execute();
                $_SESSION['flash_message'] = "Student added successfully!";
                header("Location: admin_manage_students.php");
                exit;
            } catch(Exception $e) {
                if ($e->getCode() == 1062 || $conn->errno == 1062) { 
                    $add_error = "A student with this registration number already exists."; 
                } else { 
                    $add_error = "Oops! Something went wrong."; 
                }
            }
            $stmt->close();
        }
    }
}

$students = [];
$sql_fetch = "SELECT regdno, name, department, resume_path FROM student";
$params = [];
$types = "";

if ($is_hod) {
    $sql_fetch .= " WHERE department = ?";
    $params[] = $department;
    $types .= "s";
}
$sql_fetch .= " ORDER BY name ASC";

if ($conn->connect_error) { require "../includes/config.php"; }

$stmt_fetch = $conn->prepare($sql_fetch);
if (!empty($types)) {
    $stmt_fetch->bind_param($types, ...$params);
}
$stmt_fetch->execute();
$result = $stmt_fetch->get_result();
while ($row = $result->fetch_assoc()) {
    $students[] = $row;
}
$stmt_fetch->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students - Admin Dashboard</title>
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
        th:nth-child(1) { width: 20%; } /* Reg No */
        th:nth-child(2) { width: 30%; } /* Name */
        th:nth-child(3) { width: 20%; } /* Department */
        th:nth-child(4) { width: 30%; } /* Actions */
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
                        <h1 style="font-size: 1.5rem; margin-bottom: 0.5rem;">Manage Students</h1>
                        <p class="text-muted">Add new students or manage existing records.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6">
                    <div class="card">
                        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                            <h3><i data-lucide="user-plus" style="width: 20px; vertical-align: middle;"></i> Add New Student</h3>
                        </div>
                        
                        <?php if(!empty($add_error)): ?>
                            <div class="alert alert-error" style="background: #fef2f2; color: #ef4444; border: 1px solid #fee2e2; padding:0.75rem; border-radius:0.5rem; margin-bottom:1rem;">
                                <?php echo $add_error; ?>
                            </div>
                        <?php endif; ?>
                        

                        <form action="../admin/admin_manage_students.php" method="post">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="form-group">
                                    <label class="form-label" for="regdno">Registration No</label>
                                    <input type="text" id="regdno" name="regdno" class="form-control" required pattern="[0-9]+" title="Numbers only">
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="name">Full Name</label>
                                    <input type="text" id="name" name="name" class="form-control" required>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div class="form-group">
                                    <label class="form-label" for="email">Email Address</label>
                                    <input type="email" id="email" name="email" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="contact">Contact Number</label>
                                    <input type="text" id="contact" name="contact" class="form-control" required pattern="[0-9]{10}" title="10 digit mobile number">
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div class="form-group">
                                    <label class="form-label" for="dob">Date of Birth</label>
                                    <input type="date" id="dob" name="dob" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="department">Department</label>
                                    <?php if($is_hod): ?>
                                        <input type="hidden" name="department" value="<?php echo htmlspecialchars($department); ?>">
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($department); ?>" disabled>
                                    <?php else: ?>
                                        <div class="select-wrapper">
                                            <select id="department" name="department" class="form-select" required>
                                                <option value="">-- Select Department --</option>
                                                <option value="CSE">Computer Science (CSE)</option>
                                                <option value="ECE">Electronics & Comm. (ECE)</option>
                                                <option value="ME">Mechanical (ME)</option>
                                                <option value="CE">Civil (CE)</option>
                                                <option value="EEE">Electrical & Electronics (EEE)</option>
                                                <option value="BCA">Bachelor of Computer Applications (BCA)</option>
                                            </select>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="password">Password</label>
                                <div style="position: relative;">
                                    <input type="password" id="password" name="password" class="form-control" required style="padding-right: 40px;">
                                    <span class="password-toggle-icon" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; color: var(--text-muted);">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                    </span>
                                </div>
                            </div>
                            <div style="margin-top: 1.5rem;">
                                <button type="submit" name="add_student" class="btn btn-primary">
                                    <i data-lucide="plus"></i> Add Student
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="card">
                        <div class="card-header" style="margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center;">
                            <h3>Existing Students (<?php echo count($students); ?>)</h3>
                            <div style="width: 250px;">
                                <input type="text" id="student-search" placeholder="Search students..." class="form-control" style="padding: 0.5rem; font-size: 0.9rem;">
                            </div>
                        </div>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Reg. No</th>
                                        <th>Name</th>
                                        <th>Department</th>
                                        <th>Actions</th>
                                        <th>Resume</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($students as $student): ?>
                                        <tr class="student-row" data-search="<?php echo htmlspecialchars(strtolower($student['name'] . ' ' . $student['regdno'])); ?>">
                                            <td><?php echo htmlspecialchars($student['regdno']); ?></td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($student['name']); ?></strong>
                                            </td>
                                            <td>
                                                <span class="badge" style="background: var(--bg-input); padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.85rem;">
                                                    <?php echo htmlspecialchars($student['department'] ?? 'N/A'); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="flex gap-2" style="align-items: center;">
                                                    <a href="admin_edit_student.php?regdno=<?php echo urlencode($student['regdno']); ?>" class="btn btn-sm btn-ghost" title="Edit" style="padding: 0.25rem;">
                                                        <i data-lucide="edit-2" style="width: 14px;"></i>
                                                    </a>
                                                    <button class="btn btn-sm btn-ghost-danger delete-btn" 
                                                            onclick="return showDeleteModal(event, 'admin_delete_student.php?regdno=<?php echo urlencode($student['regdno']); ?>', 'Delete Student', 'Are you sure you want to remove <?php echo htmlspecialchars(addslashes($student['name'])); ?>? This action cannot be undone.');" 
                                                            title="Delete">
                                                        <i data-lucide="trash-2" style="width: 14px;"></i>
                                                    </button>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if(!empty($student['resume_path'])): ?>
                                                    <div class="flex gap-2" style="align-items: center;">
                                                        <a href="<?php echo htmlspecialchars($student['resume_path']); ?>" target="_blank" class="btn btn-sm btn-ghost" title="View Resume" style="padding: 0.25rem;">
                                                            <i data-lucide="eye" style="width: 14px;"></i>
                                                        </a>
                                                        <a href="<?php echo htmlspecialchars($student['resume_path']); ?>" download class="btn btn-sm btn-ghost-success" title="Download" style="padding: 0.25rem;">
                                                            <i data-lucide="download" style="width: 14px;"></i>
                                                        </a>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-muted" style="font-size: 0.85rem;">—</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <div id="no-results" style="display: none; text-align: center; padding: 2rem; color: var(--text-muted);">
                                No students found matching your search.
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </main>
    </div>

    <script>
        lucide.createIcons();

        document.getElementById('student-search')?.addEventListener('keyup', function() {
            const term = this.value.toLowerCase();
            let visibleCount = 0;
            
            document.querySelectorAll('.student-row').forEach(row => {
                const text = row.getAttribute('data-search');
                if (text.includes(term)) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            const noResults = document.getElementById('no-results');
            if (noResults) {
                noResults.style.display = visibleCount === 0 ? 'block' : 'none';
            }
        });

        const passToggle = document.querySelector('.password-toggle-icon');
        if (passToggle) {
            passToggle.addEventListener('click', function() {
                const input = document.getElementById('password');
                if (input.type === 'password') {
                    input.type = 'text';
                    this.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>';
                } else {
                    input.type = 'password';
                    this.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>';
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {

            if (typeof TablePagination !== 'undefined') {
                new TablePagination('table', 10); // 10 rows per page
            }
        });
    </script>
</body>
</html>