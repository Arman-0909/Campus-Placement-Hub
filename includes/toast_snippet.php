<?php
// Check for flash messages
if (isset($_SESSION['flash_message'])) {
    $msg = $_SESSION['flash_message'];
    $type = 'success'; // Default to success
    unset($_SESSION['flash_message']);
} elseif (isset($_SESSION['flash_success'])) { // Handle legacy variable name
    $msg = $_SESSION['flash_success'];
    $type = 'success';
    unset($_SESSION['flash_success']);
} elseif (isset($_SESSION['flash_error'])) {
    $msg = $_SESSION['flash_error'];
    $type = 'error';
    unset($_SESSION['flash_error']);
}

// Render Toast if message exists
if (isset($msg) && !empty($msg)): 
    // Success: Green, Error: Red
    $bgColor = $type === 'success' ? '#10b981' : '#ef4444';
    $icon = $type === 'success' ? 'check-circle' : 'alert-circle';
?>
<!-- Global Toast Notification -->
<div id="global-toast" style="position: fixed; bottom: 2rem; right: 2rem; background: <?php echo $bgColor; ?>; color: white; padding: 1rem 2rem; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); z-index: 9999; display: flex; align-items: center; gap: 12px; transform: translateY(100px); opacity: 0; transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);">
    <i data-lucide="<?php echo $icon; ?>" style="width: 24px; height: 24px;"></i>
    <span style="font-weight: 500; font-size: 0.95rem;"><?php echo htmlspecialchars($msg); ?></span>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toast = document.getElementById('global-toast');
        if(toast) {
            // Initialize Lucide icons for the toast specifically
            lucide.createIcons({
                root: toast
            });

            // Animate In
            setTimeout(() => {
                toast.style.transform = 'translateY(0)';
                toast.style.opacity = '1';
            }, 100);

            // Animate Out after 4 seconds
            setTimeout(() => {
                toast.style.transform = 'translateY(20px)';
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 300);
            }, 4000);
        }
    });
</script>
<?php endif; ?>
