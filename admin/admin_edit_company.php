<?php

session_name("staff");
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: admin_login.php");
    exit;
}

require_once "../includes/config.php";

$company_name = "";
$company_data = null;
$feedback_msg = "";
$feedback_class = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['original_company_name'])) {
    $original_company_name = $_POST['original_company_name'];
    $new_company_name = $_POST['company_name'];
    $website = $_POST['website'];
    $description = $_POST['description'];

    $sql = "UPDATE company SET companyname = ?, website = ?, description = ? WHERE companyname = ?";
    if($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ssss", $new_company_name, $website, $description, $original_company_name);
        if($stmt->execute()) {
            $_SESSION['flash_message'] = "Company details updated successfully!";
            header("Location: admin_edit_company.php?id=" . urlencode($new_company_name));
            exit;
        } else {
            $feedback_msg = "Error updating record. Please check for duplicate names.";
            $feedback_class = "alert-error";
        }
        $stmt->close();
    }

    $company_name = $new_company_name;
} 

else if (isset($_GET['id'])) {
    $company_name = $_GET['id'];

}

if (!empty($company_name)) {
    $sql_fetch = "SELECT * FROM company WHERE companyname = ?";
    if($stmt_fetch = $conn->prepare($sql_fetch)) {
        $stmt_fetch->bind_param("s", $company_name);
        $stmt_fetch->execute();
        $result = $stmt_fetch->get_result();
        if ($result->num_rows == 1) {
            $company_data = $result->fetch_assoc();
        } else {
            $feedback_msg = "Error: Company not found.";
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
    <title>Edit Company - Admin Dashboard</title>
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
                        <h1 style="font-size: 1.5rem; margin-bottom: 0.5rem;">Edit Company</h1>
                        <p class="text-muted">Update company information.</p>
                    </div>
                    <a href="../admin/admin_manage_companies.php" class="btn btn-secondary">
                        <i data-lucide="arrow-left" style="width: 16px;"></i> Back to List
                    </a>
                </div>

                <div class="card" style="max-width: 800px; margin: 0 auto;">
                    
                    <?php if(!empty($feedback_msg)): ?>
                        <div class="alert <?php echo $feedback_class; ?>" style="<?php echo ($feedback_class === 'alert-success') ? 'background:#d1fae5; color:#065f46;' : 'background:#fee2e2; color:#991b1b;'; ?> padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
                            <?php echo $feedback_msg; ?>
                        </div>
                    <?php endif; ?>

                    <?php if($company_data): ?>
                    <form action="../admin/admin_edit_company.php" method="post">
                        <input type="hidden" name="original_company_name" value="<?php echo htmlspecialchars($company_data['companyname']); ?>">

                        <div class="form-group">
                            <label class="form-label" for="company_name">Company Name</label>
                            <input type="text" id="company_name" name="company_name" class="form-control" value="<?php echo htmlspecialchars($company_data['companyname']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="website">Company Website</label>
                            <input type="url" id="website" name="website" class="form-control" value="<?php echo htmlspecialchars($company_data['website']); ?>" placeholder="https://example.com">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="description">Description</label>
                            <textarea id="description" name="description" class="form-control" rows="5"><?php echo htmlspecialchars($company_data['description']); ?></textarea>
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