<?php

session_name("staff");
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["role"]) || $_SESSION["role"] !== 'Admin'){
    header("location: admin_login.php");
    exit;
}
require_once "../includes/config.php";

$feedback_msg = "";
$feedback_class = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_company'])) {
    if (empty(trim($_POST['company_name']))) {
        $feedback_msg = "Company name is required.";
        $feedback_class = "alert-error";
    } else {
        $sql = "INSERT INTO company (companyname, website, description) VALUES (?, ?, ?)";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("sss", $_POST['company_name'], $_POST['website'], $_POST['description']);
            try {
                $stmt->execute();
                $_SESSION['flash_success'] = "Company '" . htmlspecialchars($_POST['company_name']) . "' added successfully!";
                header("location: admin_manage_companies.php");
                exit;
            } catch(Exception $e) {
                if ($e->getCode() == 1062 || $conn->errno == 1062) { $feedback_msg = "A company with this name already exists."; } 
                else { $feedback_msg = "Oops! Something went wrong."; }
                $feedback_class = "alert-error";
            }
            $stmt->close();
        }
    }
}

$companies = [];
$sql_fetch = "SELECT companyname, companyname AS id, website, created_at FROM company ORDER BY companyname ASC";
if ($result = $conn->query($sql_fetch)) {
    while ($row = $result->fetch_assoc()) { $companies[] = $row; }
    $result->free();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Companies - Admin Dashboard</title>
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
        .table-container table th:nth-child(1),
        .table-container table td:nth-child(1) { width: 20%; text-align: left; }
        
        .table-container table th:nth-child(2),
        .table-container table td:nth-child(2) { width: 40%; text-align: left; }
        
        .table-container table th:nth-child(3),
        .table-container table td:nth-child(3) { width: 20%; text-align: left; }
        
        .table-container table th:nth-child(4),
        .table-container table td:nth-child(4) { width: 20%; text-align: center; }
        .table-container table td:nth-child(2) a {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .table-container table td:nth-child(4) .flex {
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
                
                <div style="margin-bottom: 2rem;">
                    <h1 style="font-size: 1.5rem; margin-bottom: 0.5rem;">Manage Companies</h1>
                    <p class="text-muted">Register and manage recruiting companies.</p>
                </div>

                <div class="grid grid-cols-3 gap-6" style="grid-template-columns: 1fr 2fr;">
                    <div class="card" style="height: fit-content;">
                        <div class="card-header" style="margin-bottom: 1.5rem;">
                            <h3><i data-lucide="building-2" style="width: 20px; vertical-align: middle;"></i> Add Company</h3>
                        </div>

                        <?php if(!empty($feedback_msg) && $feedback_class !== 'alert-success'): ?>
                            <div class="alert <?php echo $feedback_class; ?>" style="background: #fef2f2; color: #ef4444; border: 1px solid #fee2e2; padding:0.75rem; border-radius:0.5rem; margin-bottom:1rem;">
                                <?php echo $feedback_msg; ?>
                            </div>
                        <?php endif; ?>

                        <form action="../admin/admin_manage_companies.php" method="post">
                            <div class="form-group">
                                <label class="form-label" for="company_name">Company Name</label>
                                <input type="text" id="company_name" name="company_name" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="website">Website</label>
                                <input type="url" id="website" name="website" class="form-control" placeholder="https://example.com">
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="description">Description (Optional)</label>
                                <textarea id="description" name="description" class="form-control" rows="4"></textarea>
                            </div>
                            <button type="submit" name="add_company" class="btn btn-primary w-full" style="width: 100%; justify-content: center;">
                                <i data-lucide="plus"></i> Add Company
                            </button>
                        </form>
                    </div>
                    <div class="card">
                        <div class="card-header" style="margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center;">
                            <h3>Registered Companies (<?php echo count($companies); ?>)</h3>
                            <div style="width: 250px;">
                                <input type="text" id="company-search" placeholder="Search companies..." class="form-control" style="padding: 0.5rem; font-size: 0.9rem;">
                            </div>
                        </div>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Website</th>
                                        <th>Added On</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($companies as $company): ?>
                                        <tr class="company-row" data-search="<?php echo htmlspecialchars(strtolower($company['companyname'])); ?>">
                                            <td><strong><?php echo htmlspecialchars($company['companyname']); ?></strong></td>
                                            <td>
                                                <?php 
                                                $url = $company['website'];
                                                $display_url = strlen($url) > 40 ? substr($url, 0, 40) . '...' : $url;
                                                ?>
                                                <a href="<?php echo htmlspecialchars($url); ?>" target="_blank" rel="noopener noreferrer" class="text-primary hover:underline" style="display: inline-flex; align-items: center; gap: 4px;" title="<?php echo htmlspecialchars($url); ?>">
                                                    <?php echo htmlspecialchars($display_url); ?>
                                                    <i data-lucide="external-link" style="width: 12px; flex-shrink: 0;"></i>
                                                </a>
                                            </td>
                                            <td><?php echo date("d M, Y", strtotime($company['created_at'])); ?></td>
                                            <td>
                                                <div class="flex gap-2">
                                                    <a href="admin_edit_company.php?id=<?php echo urlencode($company['id']); ?>" class="btn btn-sm btn-ghost" title="Edit">
                                                        <i data-lucide="edit-2" style="width: 14px;"></i>
                                                    </a>
                                                    <a href="admin_delete_company.php?id=<?php echo urlencode($company['id']); ?>" class="btn btn-sm btn-ghost-danger" title="Delete" 
                                                       onclick="return showDeleteModal(event, this.href, 'Delete Company', 'Are you sure you want to remove <?php echo htmlspecialchars(addslashes($company['companyname'])); ?>? This action cannot be undone.');">
                                                        <i data-lucide="trash-2" style="width: 14px;"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
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

        const searchInput = document.getElementById('company-search');
        
        if (searchInput) {
            searchInput.addEventListener('keyup', function() {
                const term = this.value.toLowerCase().trim();
                const rows = document.querySelectorAll('.company-row');
                
                rows.forEach(row => {
                    const text = row.getAttribute('data-search');
                    if (text.includes(term)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            if (typeof TablePagination !== 'undefined') {
                new TablePagination('table', 10);
            }
        });
    </script>

</body>
</html>