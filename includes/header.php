<?php 

    $page = basename($_SERVER['PHP_SELF'], '.php');
    $title = ucwords(str_replace('_', ' ', $page));

    $title_overrides = [
        'admin_dashboard' => 'Dashboard',
        'student_dashboard' => 'Dashboard',
        'hod_dashboard' => 'Dashboard',
        'admin_manage_students' => 'Manage Students',
        'admin_manage_companies' => 'Manage Companies',
        'admin_manage_jobs' => 'Manage Jobs',
        'admin_view_applications' => 'View Applications',
        'admin_manage_placements' => 'Manage Placements',
        'admin_import_data' => 'Import Data',
        'student_jobs' => 'Eligible Jobs',
        'student_applications' => 'My Applications',
    ];
    if (isset($title_overrides[$page])) {
        $title = $title_overrides[$page];
    }

    $sub_pages = [
        'admin_edit_student' => '../admin/admin_manage_students.php',
        'admin_edit_job' => '../admin/admin_manage_jobs.php',
        'admin_edit_company' => '../admin/admin_manage_companies.php',
        'admin_edit_placement' => '../admin/admin_manage_placements.php',
        'student_upload_resume' => '../student/student_dashboard.php',
    ];
    
    $show_back = isset($sub_pages[$page]);
    $back_url = $sub_pages[$page] ?? null;

    $is_student = isset($_SESSION['num']) && !isset($_SESSION['role']);
    $is_admin = isset($_SESSION['role']);
?>
<header class="dashboard-header" style="background: white; border-bottom: 1px solid var(--border-light, #f1f5f9); padding: 1.25rem 2.5rem; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 1px 3px rgba(0,0,0,0.02); position: sticky; top: 0; z-index: 50; backdrop-filter: blur(8px); background: rgba(255,255,255,0.92);">
    <div class="flex items-center gap-4">
        <button id="sidebar-toggle" class="btn btn-ghost btn-icon mobile-toggle" style="margin-right: 0.5rem;">
            <i data-lucide="menu"></i>
        </button>

        <?php if ($show_back && $back_url): ?>
            <a href="<?php echo $back_url; ?>" class="btn btn-ghost btn-icon btn-sm" title="Go Back" style="border-radius: 10px;">
                <i data-lucide="arrow-left"></i>
            </a>
        <?php endif; ?>

        <div>
            <h2 style="margin: 0; font-size: 1.15rem; letter-spacing: -0.02em;">
                <?php echo $title; ?>
            </h2>
        </div>
    </div>
    
    <div class="flex items-center gap-3">
        <?php if ($is_student || $is_admin): ?>
        <div id="notification-wrapper" style="position: relative;">
            <button id="notification-bell" class="btn btn-ghost btn-icon btn-sm" style="position: relative; padding: 0.5rem; border-radius: 10px;" title="Notifications">
                <i data-lucide="bell" style="width: 20px; height: 20px;"></i>
                <span id="notification-badge" style="display: none; position: absolute; top: 2px; right: 2px; background: #ef4444; color: white; font-size: 0.65rem; font-weight: 700; min-width: 16px; height: 16px; border-radius: 50%; display: none; align-items: center; justify-content: center; line-height: 1; padding: 0 4px;">0</span>
            </button>
            <div id="notification-dropdown" style="display: none; position: absolute; top: 100%; right: 0; margin-top: 0.5rem; width: 360px; max-height: 420px; background: white; border-radius: var(--radius-lg, 1rem); box-shadow: 0 20px 40px -8px rgba(0,0,0,0.15), 0 8px 16px -4px rgba(0,0,0,0.08); border: 1px solid var(--border, #e2e8f0); z-index: 9999; overflow: hidden;">
                <div style="padding: 1rem 1.25rem; border-bottom: 1px solid var(--border, #e2e8f0); display: flex; justify-content: space-between; align-items: center;">
                    <h4 style="margin: 0; font-size: 0.95rem;">Notifications</h4>
                    <button id="mark-all-read" class="text-sm" style="background: none; border: none; color: var(--primary, #4f46e5); cursor: pointer; font-weight: 500; font-size: 0.8rem;">Mark all read</button>
                </div>
                <div id="notification-list" style="overflow-y: auto; max-height: 340px;">
                    <div style="padding: 2rem; text-align: center; color: var(--text-muted, #64748b);">
                        <i data-lucide="bell-off" style="width: 32px; height: 32px; margin: 0 auto 0.5rem; display: block; opacity: 0.4;"></i>
                        <p style="font-size: 0.875rem;">No notifications yet</p>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="hidden-mobile" style="display: flex; align-items: center; gap: 0.5rem; padding: 0.4rem 0.85rem; background: var(--bg-input, #f1f5f9); border-radius: 8px; font-size: 0.8rem; color: var(--text-muted, #64748b); font-weight: 500;">
            <i data-lucide="calendar" style="width: 14px; height: 14px; opacity: 0.6;"></i>
            <?php echo date('D, M j'); ?>
        </div>
    </div>
</header>
<?php include '../includes/toast_snippet.php'; ?>

<?php if ($is_student || $is_admin): ?>
<script>
(function() {
    const bell = document.getElementById('notification-bell');
    const dropdown = document.getElementById('notification-dropdown');
    const badge = document.getElementById('notification-badge');
    const list = document.getElementById('notification-list');
    const markAllBtn = document.getElementById('mark-all-read');
    let isOpen = false;

    const isAdmin = <?php echo isset($_SESSION['role']) ? 'true' : 'false'; ?>;
    const fetchUrl = isAdmin ? '../api/fetch_notifications.php?role=admin' : '../api/fetch_notifications.php';
    const markUrl = isAdmin ? '../api/mark_notifications_read.php?role=admin' : '../api/mark_notifications_read.php';

    function getIcon(type) {
        if (type === 'new_job') return 'briefcase';
        if (type === 'status_update') return 'refresh-cw';
        return 'bell';
    }

    function getIconColor(type) {
        if (type === 'new_job') return { bg: '#dbeafe', fg: '#2563eb' };
        if (type === 'status_update') return { bg: '#fef3c7', fg: '#d97706' };
        return { bg: '#f1f5f9', fg: '#64748b' };
    }

    function getLink(type) {
        if (type === 'new_job') return '../student/student_jobs.php';
        if (type === 'status_update') return '../student/student_applications.php';
        if (type === 'new_application') return '../admin/admin_view_applications.php';
        return '#';
    }

    function renderNotifications(data) {
        const { notifications, unread_count } = data;

        if (unread_count > 0) {
            badge.textContent = unread_count > 9 ? '9+' : unread_count;
            badge.style.display = 'flex';
        } else {
            badge.style.display = 'none';
        }
        
        if (notifications.length === 0) {
            list.innerHTML = `
                <div style="padding: 2rem; text-align: center; color: var(--text-muted, #64748b);">
                    <p style="font-size: 0.875rem;">No notifications yet</p>
                </div>`;
            return;
        }

        list.innerHTML = notifications.map(n => {
            const color = getIconColor(n.type);
            const link = getLink(n.type);
            const unreadDot = !parseInt(n.is_read) ? '<div style="width: 8px; height: 8px; background: #4f46e5; border-radius: 50%; flex-shrink: 0;"></div>' : '';
            return `
                <a href="${link}" style="text-decoration: none; color: inherit; display: block;">
                <div style="padding: 0.85rem 1.25rem; border-bottom: 1px solid #f1f5f9; display: flex; gap: 0.75rem; align-items: flex-start; transition: background 0.2s; cursor: pointer; ${!parseInt(n.is_read) ? 'background: #f8fafc;' : ''}" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='${!parseInt(n.is_read) ? '#f8fafc' : 'white'}'">
                    <div style="width: 36px; height: 36px; background: ${color.bg}; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i data-lucide="${getIcon(n.type)}" style="width: 16px; height: 16px; color: ${color.fg};"></i>
                    </div>
                    <div style="flex: 1; min-width: 0;">
                        <div style="font-weight: 600; font-size: 0.85rem; color: #1e293b; margin-bottom: 2px;">${n.title}</div>
                        <div style="font-size: 0.8rem; color: #64748b; line-height: 1.4; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${n.message}</div>
                        <div style="font-size: 0.7rem; color: #94a3b8; margin-top: 4px;">${n.time_ago}</div>
                    </div>
                    ${unreadDot}
                </div>
                </a>`;
        }).join('');

        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    function fetchNotifications() {
        fetch(fetchUrl)
            .then(r => r.json())
            .then(data => renderNotifications(data))
            .catch(() => {});
    }

    bell.addEventListener('click', function(e) {
        e.stopPropagation();
        isOpen = !isOpen;
        dropdown.style.display = isOpen ? 'block' : 'none';
        if (isOpen) fetchNotifications();
    });

    document.addEventListener('click', function(e) {
        if (!dropdown.contains(e.target) && e.target !== bell) {
            isOpen = false;
            dropdown.style.display = 'none';
        }
    });

    markAllBtn.addEventListener('click', function() {
        fetch(markUrl, { method: 'POST' })
            .then(() => fetchNotifications())
            .catch(() => {});
    });

    fetchNotifications();
    setInterval(fetchNotifications, 30000);
})();
</script>
<?php endif; ?>
