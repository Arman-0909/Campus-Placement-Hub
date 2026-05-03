<?php
// upload.php (Modernized Offer Letter Upload Processor)
include '../includes/config.php';
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: student_login.php");
    exit;
}

$page_title = "Processing...";
$feedback_icon = "loader-2";
$feedback_color = "text-primary";
$feedback_heading = "Processing...";
$feedback_message = "Please wait...";
$feedback_class = "border-primary";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_FILES["file"]) && $_FILES["file"]["error"] == 0) {
        $companyName = $_POST["title"] ?? null; 
        $packageAmount = $_POST["pac"] ?? null;
        $regdno = $_SESSION["num"] ?? null;
        
        if (empty($companyName) || empty($packageAmount) || empty($regdno)) {
             $page_title = "Error";
             $feedback_icon = "alert-circle";
             $feedback_color = "text-danger";
             $feedback_heading = "Missing Information";
             $feedback_message = "Please fill in all required fields.";
        } else {
            $originalFileName = basename($_FILES["file"]["name"]);
            $pname = rand(1000, 10000) . "-" . $originalFileName;
            $uploadDirectory = "../uploaded_files/";
            if (!file_exists($uploadDirectory)) mkdir($uploadDirectory, 0777, true);
            $targetFilePath = $uploadDirectory . $pname;

            if (move_uploaded_file($_FILES["file"]["tmp_name"], $targetFilePath)) {
                $sql = "INSERT INTO package (regdno, companyname, package, file) VALUES (?, ?, ?, ?)";
                if ($stmt = $conn->prepare($sql)) {
                    $stmt->bind_param("ssds", $regdno, $companyName, $packageAmount, $pname);
                    if ($stmt->execute()) {
                        $page_title = "Success";
                        $feedback_icon = "check-circle-2";
                        $feedback_color = "text-success";
                        $feedback_heading = "Upload Successful!";
                        $feedback_message = "Your offer letter has been uploaded successfully.";
                    } else {
                        $page_title = "Error";
                        $feedback_icon = "alert-octagon";
                        $feedback_color = "text-danger";
                        $feedback_heading = "Database Error";
                        $feedback_message = "Could not save record.";
                    }
                    $stmt->close();
                }
            } else {
                $page_title = "Error";
                $feedback_icon = "upload-cloud"; // fallback
                $feedback_color = "text-danger";
                $feedback_heading = "Upload Failed";
                $feedback_message = "Could not move uploaded file.";
            }
        }
    } else {
        $page_title = "Error";
        $feedback_icon = "file-warning";
        $feedback_color = "text-danger";
        $feedback_heading = "No File";
        $feedback_message = "Please select a file to upload.";
    }
} else {
    header("location: update_placement.php");
    exit;
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Campus Placement Hub</title>
    <?php include '../includes/header_includes.php'; ?>
</head>
<body>
    <div class="dashboard-layout">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="dashboard-main">
            <?php include '../includes/header.php'; ?>
            
            <div class="container" style="padding-top: 4rem; display: flex; justify-content: center;">
                
                <div class="card" style="max-width: 500px; text-align: center; padding: 3rem;">
                    <div style="margin-bottom: 2rem; display: flex; justify-content: center;">
                        <i data-lucide="<?php echo $feedback_icon; ?>" class="<?php echo $feedback_color; ?>" style="width: 64px; height: 64px;"></i>
                    </div>
                    
                    <h2 class="text-2xl font-bold mb-2"><?php echo $feedback_heading; ?></h2>
                    <p class="text-muted mb-8"><?php echo $feedback_message; ?></p>
                    
                    <a href="../student/student_dashboard.php" class="btn btn-primary w-full justify-center">
                        Return to Profile
                    </a>
                </div>

            </div>
        </main>
    </div>
    <script>
        lucide.createIcons();
    </script>
</body>
</html>