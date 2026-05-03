<?php
// update_placement.php (Modernized Form)
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: student_login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Offer Letter - Campus Placement Hub</title>
    <?php include '../includes/header_includes.php'; ?>
</head>
<body>
    <div class="dashboard-layout">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="dashboard-main">
            <?php include '../includes/header.php'; ?>
            
            <div class="container" style="padding-top: 2rem;">
                
                <div class="card" style="max-width: 600px; margin: 0 auto;">
                    <div class="card-header" style="margin-bottom: 1.5rem;">
                         <h3><i data-lucide="upload" style="width: 20px; vertical-align: middle;"></i> Upload Offer Letter</h3>
                    </div>
                    
                    <div class="alert alert-info" style="margin-bottom: 1.5rem; background: var(--bg-hover);">
                        <i data-lucide="info" style="width: 16px;"></i>
                        <span>Got a new offer? Upload the details and proof here to update your placement status.</span>
                    </div>

                    <form action="upload.php" method="post" enctype="multipart/form-data">    
                        <div class="form-group">
                            <label class="form-label" for="title">Company Name</label>
                            <input type="text" id="title" name="title" placeholder="e.g. Infosys" required class="form-control">
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="pac">Package (LPA)</label>
                            <input type="number" id="pac" name="pac" step="0.01" min="0" max="99" placeholder="e.g. 10.5" required class="form-control">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Offer Letter (PDF/Image)</label>
                            
                            <div style="position: relative;">
                                <input type="file" name="file" id="file" class="hidden-input" style="opacity: 0; position: absolute; z-index: -1; width: 0.1px; height: 0.1px;" required onchange="updateFileName(this)">
                                <label for="file" class="btn btn-secondary dashed w-full" style="justify-content: center; cursor: pointer; border-style: dashed;">
                                    <i data-lucide="paperclip"></i> 
                                    <span id="file-label">Choose File...</span>
                                </label>
                            </div>
                        </div>

                        <div class="flex gap-4 mt-6">
                            <button type="reset" class="btn btn-secondary flex-1 justify-center">Reset</button>
                            <button type="submit" name="submit" class="btn btn-primary flex-1 justify-center">
                                <i data-lucide="upload-cloud"></i> Upload Details
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </main>
    </div>
    
    <script>
        lucide.createIcons();

        function updateFileName(input) {
            const label = document.getElementById('file-label');
            if (input.files && input.files.length > 0) {
                label.textContent = input.files[0].name;
                label.parentElement.style.borderColor = 'var(--primary)';
                label.parentElement.style.color = 'var(--primary)';
            } else {
                label.textContent = "Choose File...";
                label.parentElement.style.borderColor = 'var(--border)';
                label.parentElement.style.color = 'var(--text)';
            }
        }
    </script>
</body>
</html>