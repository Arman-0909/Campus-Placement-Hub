<!-- Unified Sidebar -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <i data-lucide="graduation-cap" style="width: 24px;"></i>
        <span>Campus Placement Hub</span>
    </div>

    <nav class="sidebar-nav">
        <?php
        // Determine current page for active state
        $current_page = basename($_SERVER['PHP_SELF']);
        
        // Helper function for links
        function nav_link($href, $icon, $label, $current) {
            $active = ($href === $current) ? 'active' : '';
            echo "<a href='$href' class='sidebar-link $active'>
                    <i data-lucide='$icon'></i>
                    <span>$label</span>
                  </a>";
        }

        // --- ADMIN ROLE ---
        if (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin') {
            echo '<div style="padding: 0 1rem; margin-bottom: 0.5rem; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Administration</div>';
            nav_link('../admin/admin_dashboard.php', 'layout-dashboard', 'Dashboard', $current_page);
            echo '<div style="padding: 0 1rem; margin-top: 1rem; margin-bottom: 0.5rem; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Management</div>';
            nav_link('../admin/admin_manage_students.php', 'users', 'Students', $current_page);
            nav_link('../admin/admin_manage_companies.php', 'building', 'Companies', $current_page);
            nav_link('../admin/admin_manage_jobs.php', 'briefcase', 'Jobs', $current_page);
             nav_link('../admin/admin_view_applications.php', 'file-text', 'Applications', $current_page);
             nav_link('../admin/admin_manage_placements.php', 'award', 'Placements', $current_page);
             nav_link('../admin/admin_placement_records.php', 'bar-chart-2', 'Placement Records', $current_page);
             
             echo '<div style="padding: 0 1rem; margin-top: 1.5rem; margin-bottom: 0.5rem; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Data</div>';
             nav_link('../admin/admin_import_data.php', 'database', 'Import Data', $current_page);
            
            echo '<div style="padding: 0 1rem; margin-top: 1.5rem; margin-bottom: 0.5rem; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Settings</div>';
            nav_link('../admin/admin_forgot_password.php', 'key', 'Change Password', $current_page);
            nav_link('../admin/admin_logout.php', 'log-out', 'Logout', $current_page);
        }
        
        // --- HOD ROLE ---
        elseif (isset($_SESSION['role']) && ($_SESSION['role'] === 'Hod' || $_SESSION['role'] === 'hod')) {
            echo '<div style="padding: 0 1rem; margin-bottom: 0.5rem; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Department</div>';
            
            // Handle both dashboard variants if they exist
            nav_link('../admin/hod_access2.php', 'layout-dashboard', 'Dashboard', $current_page);
            nav_link('../admin/hod_studentDetails.php', 'users', 'My Students', $current_page);
            nav_link('../admin/eligible_students.php', 'check-circle', 'Eligible Students', $current_page);
            nav_link('../admin/admin_view_applications.php', 'file-text', 'Applications', $current_page); // Shared view
            
            echo '<div style="padding: 0 1rem; margin-top: 1.5rem; margin-bottom: 0.5rem; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Settings</div>';
            nav_link('../admin/hod_reset_password.php', 'key', 'Change Password', $current_page);
            nav_link('../admin/hod_logout.php', 'log-out', 'Logout', $current_page);
        }
        
        // --- STUDENT ROLE ---
        else {
            echo '<div style="padding: 0 1rem; margin-bottom: 0.5rem; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Student Portal</div>';
            nav_link('../student/student_dashboard.php', 'layout-dashboard', 'Dashboard', $current_page);
            nav_link('../student/student_jobs.php', 'briefcase', 'Find Jobs', $current_page);
            nav_link('../student/student_applications.php', 'file-check', 'My Applications', $current_page);
            
            echo '<div style="padding: 0 1rem; margin-top: 1.5rem; margin-bottom: 0.5rem; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Settings</div>';
            nav_link('../student/student_forgot_password.php', 'key', 'Change Password', $current_page);
            nav_link('../student/student_logout.php', 'log-out', 'Logout', $current_page);
        }
        ?>
    </nav>
    
    <!-- Spacer to push user profile to bottom -->
    <div style="margin-top: auto;"></div>
    
    <div style="padding: 1.5rem; border-top: 1px solid var(--border);">
        <div class="flex items-center gap-3" style="padding: 0 0.5rem;">
            <div style="width: 36px; height: 36px; border-radius: 50%; background: var(--primary-light); color: var(--primary); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.8rem;">
                <?php echo strtoupper(substr($_SESSION['username'] ?? 'U', 0, 1)); ?>
            </div>
            <div style="overflow: hidden;">
                <div class="font-medium text-sm text-truncate" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 140px;">
                    <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?>
                </div>
                <div class="text-xs text-muted">
                    <?php echo isset($_SESSION['role']) ? $_SESSION['role'] : 'Student'; ?>
                </div>
            </div>
        </div>
    </div>
</aside>
<script>
// Apply role-based theme class to body
(function() {
    <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'Admin' || $_SESSION['role'] === 'Hod' || $_SESSION['role'] === 'hod')): ?>
        document.body.classList.add('theme-admin');
    <?php else: ?>
        document.body.classList.add('theme-student');
    <?php endif; ?>
})();
</script>
