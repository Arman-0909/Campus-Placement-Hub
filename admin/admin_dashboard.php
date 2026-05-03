<?php
// staff_access.php (Modernized Admin Dashboard)
require_once "../includes/config.php";
session_name("staff");
session_start();
 
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: admin_login.php");
    exit;
}

// Stats
$total_students = $conn->query("SELECT COUNT(regdno) as count FROM student")->fetch_assoc()['count'] ?? 0;
$total_placed = $conn->query("SELECT COUNT(regdno) as count FROM student WHERE placement_status = 'Placed'")->fetch_assoc()['count'] ?? 0;
$companies_registered = $conn->query("SELECT COUNT(*) as count FROM company")->fetch_assoc()['count'] ?? 0;
$active_jobs = $conn->query("SELECT COUNT(*) as count FROM jobs")->fetch_assoc()['count'] ?? 0;

// Chart Data: Placement Growth by Year
$placement_years = [];
$result_years = $conn->query("SELECT YEAR(placement_date) as yr, COUNT(*) as cnt FROM placements GROUP BY YEAR(placement_date) ORDER BY yr ASC");
if ($result_years) {
    while ($row = $result_years->fetch_assoc()) {
        $placement_years[] = $row;
    }
}

// Chart Data: Company Hiring (students per company)
$company_hiring = [];
$result_hiring = $conn->query("SELECT j.company_name, COUNT(*) as cnt FROM placements p JOIN jobs j ON p.job_id = j.job_id GROUP BY j.company_name ORDER BY cnt DESC LIMIT 10");
if ($result_hiring) {
    while ($row = $result_hiring->fetch_assoc()) {
        $company_hiring[] = $row;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Campus Placement Hub</title>
    <?php include '../includes/header_includes.php'; ?>
    <style>
        .chart-card {
            background: white;
            border-radius: var(--radius-lg);
            border: 1px solid var(--border);
            padding: 1.5rem;
            box-shadow: var(--shadow-sm);
            transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1),
                        box-shadow 0.35s ease;
        }
        .chart-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 32px -8px rgba(0,0,0,0.1);
        }
        .chart-card h3 {
            font-size: 1rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .chart-card canvas {
            max-height: 300px;
        }

        /* Animated number styling */
        .stat-value[data-target] {
            font-variant-numeric: tabular-nums;
        }

        /* Stat card icon pulse animation on load */
        @keyframes iconPulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.15); }
            100% { transform: scale(1); }
        }
        .stat-card .stat-icon {
            animation: iconPulse 0.6s ease-out;
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
                    <h1 style="font-size: 1.75rem; color: var(--secondary);">Admin Overview</h1>
                    <p class="text-muted">Manage students, companies, and placement activities.</p>
                </div>

                <!-- Stats Grid with Animated Numbers -->
                <div class="grid grid-cols-4 gap-6 mobile-stack" style="margin-bottom: 2rem;">
                    <div class="stat-card">
                        <div>
                            <div class="stat-label">Total Students</div>
                            <div class="stat-value" data-target="<?php echo $total_students; ?>">0</div>
                        </div>
                        <div class="stat-icon purple"><i data-lucide="users"></i></div>
                    </div>
                    
                    <div class="stat-card">
                        <div>
                            <div class="stat-label">Placed Students</div>
                            <div class="stat-value" data-target="<?php echo $total_placed; ?>">0</div>
                        </div>
                        <div class="stat-icon green"><i data-lucide="award"></i></div>
                    </div>
                    
                    <div class="stat-card">
                        <div>
                            <div class="stat-label">Companies</div>
                            <div class="stat-value" data-target="<?php echo $companies_registered; ?>">0</div>
                        </div>
                        <div class="stat-icon orange"><i data-lucide="building-2"></i></div>
                    </div>

                    <div class="stat-card">
                        <div>
                            <div class="stat-label">Active Jobs</div>
                            <div class="stat-value" data-target="<?php echo $active_jobs; ?>">0</div>
                        </div>
                        <div class="stat-icon blue"><i data-lucide="briefcase"></i></div>
                    </div>
                </div>

                <!-- Charts Row 1: Line Chart + Pie Chart -->
                <div class="grid grid-cols-2 gap-6 mobile-stack" style="margin-bottom: 2rem; grid-template-columns: 2fr 1fr;">
                    <div class="chart-card">
                        <h3>
                            <div style="background: #dbeafe; padding: 0.4rem; border-radius: 8px; color: #2563eb; display: flex;">
                                <i data-lucide="trending-up" style="width: 18px;"></i>
                            </div>
                            Placement Growth Over the Years
                        </h3>
                        <canvas id="lineChart"></canvas>
                    </div>

                    <div class="chart-card">
                        <h3>
                            <div style="background: #f3e8ff; padding: 0.4rem; border-radius: 8px; color: #9333ea; display: flex;">
                                <i data-lucide="pie-chart" style="width: 18px;"></i>
                            </div>
                            Placement Ratio
                        </h3>
                        <canvas id="pieChart"></canvas>
                        <div style="text-align: center; margin-top: 0.75rem;">
                            <span class="text-sm text-muted">
                                <strong style="color: var(--success);"><?php echo $total_placed; ?></strong> Placed · 
                                <strong style="color: var(--danger);"><?php echo $total_students - $total_placed; ?></strong> Unplaced
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Charts Row 2: Bar Chart -->
                <div class="chart-card" style="margin-bottom: 2rem;">
                    <h3>
                        <div style="background: #d1fae5; padding: 0.4rem; border-radius: 8px; color: #059669; display: flex;">
                            <i data-lucide="bar-chart-3" style="width: 18px;"></i>
                        </div>
                        Students Hired Per Company
                    </h3>
                    <canvas id="barChart" style="max-height: 350px;"></canvas>
                </div>

                <!-- Row: Quick Find & Data Management -->
                <div class="grid grid-cols-2 gap-6 mobile-stack" style="margin-bottom: 2rem; grid-template-columns: 2fr 1fr;">
                    
                    <!-- Quick Find Student -->
                    <div class="card" style="height: 100%; display: flex; flex-direction: column; justify-content: center;">
                        <div class="flex items-center gap-3 mb-4">
                            <div style="background: var(--primary-light); padding: 0.5rem; border-radius: 8px; color: var(--primary);">
                                <i data-lucide="search"></i>
                            </div>
                            <h3 style="margin: 0; font-size: 1.25rem;">Quick Find Student</h3>
                        </div>
                        <p class="text-muted text-sm mb-4">Look up a student profile instantly by registration number.</p>
                        <form action="../admin/admin_edit_student.php" method="get" class="flex gap-2">
                            <input type="text" name="regdno" placeholder="Enter Registration No (e.g. 1903011234)" class="form-control" required style="flex: 1;">
                            <button type="submit" class="btn btn-primary">Go</button>
                        </form>
                    </div>

                    <!-- Data Management -->
                    <div class="card" style="background: linear-gradient(135deg, var(--secondary), #334155); color: white; height: 100%; display: flex; flex-direction: column; justify-content: center;">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 style="color: white; margin-bottom: 0.5rem; font-size: 1.25rem;">Data Management</h3>
                                <p style="opacity: 0.8; font-size: 0.9rem;">Bulk import students & marks via CSV.</p>
                            </div>
                            <i data-lucide="database" style="opacity: 0.2; width: 32px; height: 32px;"></i>
                        </div>
                        <div>
                             <a href="../admin/admin_import_data.php" class="btn btn-sm" style="background: white; color: var(--secondary); border: none; width: 100%; justify-content: center;">Go to Import Tools</a>
                        </div>
                    </div>
                </div>

                <!-- Common Actions -->
                <div class="card">
                     <h3 class="mb-4" style="font-size: 1.25rem;">Common Actions</h3>
                     <div class="grid grid-cols-4 gap-4" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                        <a href="../admin/admin_manage_students.php" class="btn btn-secondary justify-center">
                            <i data-lucide="users"></i> Manage Students
                        </a>
                        <a href="../admin/admin_manage_jobs.php" class="btn btn-secondary justify-center">
                            <i data-lucide="briefcase"></i> Manage Jobs
                        </a>
                        <a href="../admin/admin_view_applications.php" class="btn btn-secondary justify-center">
                            <i data-lucide="file-text"></i> View Applications
                        </a>
                        <a href="../admin/admin_manage_companies.php" class="btn btn-secondary justify-center">
                            <i data-lucide="building"></i> Companies
                        </a>
                    </div>
                </div>

            </div>
        </main>
    </div>
    
    <script>
        lucide.createIcons();

        // ====== Animated Number Counter ======
        function animateCountUp(el) {
            const target = parseInt(el.getAttribute('data-target'));
            const duration = 1500; // ms
            const startTime = performance.now();
            
            function update(currentTime) {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);
                
                // Ease out cubic
                const eased = 1 - Math.pow(1 - progress, 3);
                const current = Math.round(eased * target);
                
                el.textContent = current.toLocaleString();
                
                if (progress < 1) {
                    requestAnimationFrame(update);
                }
            }
            requestAnimationFrame(update);
        }

        // Trigger count-up on page load
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.stat-value[data-target]').forEach(el => {
                animateCountUp(el);
            });
        });

        // ====== Chart.js Charts ======
        const chartFont = { family: "'Inter', sans-serif", size: 12 };
        const chartColors = {
            primary: '#4f46e5',
            primaryLight: 'rgba(79, 70, 229, 0.1)',
            success: '#10b981',
            successLight: 'rgba(16, 185, 129, 0.1)',
            danger: '#ef4444',
            dangerLight: 'rgba(239, 68, 68, 0.1)',
            blue: '#3b82f6',
            purple: '#8b5cf6',
            orange: '#f59e0b',
            teal: '#14b8a6',
            rose: '#f43f5e',
            sky: '#0ea5e9',
            amber: '#f59e0b',
            emerald: '#10b981',
            indigo: '#6366f1',
            pink: '#ec4899'
        };
        const paletteColors = ['#4f46e5', '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#14b8a6', '#f43f5e', '#0ea5e9', '#ec4899'];

        Chart.defaults.font = chartFont;
        Chart.defaults.plugins.legend.labels.usePointStyle = true;
        Chart.defaults.plugins.legend.labels.padding = 16;

        // --- Line Chart: Placement Growth ---
        const lineData = <?php echo json_encode($placement_years); ?>;
        new Chart(document.getElementById('lineChart'), {
            type: 'line',
            data: {
                labels: lineData.map(d => d.yr),
                datasets: [{
                    label: 'Students Placed',
                    data: lineData.map(d => d.cnt),
                    borderColor: chartColors.primary,
                    backgroundColor: chartColors.primaryLight,
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: chartColors.primary,
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        titleColor: '#fff',
                        bodyColor: '#cbd5e1',
                        cornerRadius: 8,
                        padding: 12
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, color: '#94a3b8' },
                        grid: { color: 'rgba(0,0,0,0.04)' }
                    },
                    x: {
                        ticks: { color: '#94a3b8' },
                        grid: { display: false }
                    }
                }
            }
        });

        // --- Pie Chart: Placed vs Unplaced ---
        const placed = <?php echo $total_placed; ?>;
        const unplaced = <?php echo $total_students - $total_placed; ?>;
        new Chart(document.getElementById('pieChart'), {
            type: 'doughnut',
            data: {
                labels: ['Placed', 'Unplaced'],
                datasets: [{
                    data: [placed, unplaced],
                    backgroundColor: [chartColors.success, '#e2e8f0'],
                    borderColor: ['#fff', '#fff'],
                    borderWidth: 3,
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                cutout: '65%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { padding: 16 }
                    },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        cornerRadius: 8,
                        padding: 12
                    }
                }
            }
        });

        // --- Bar Chart: Company Hiring ---
        const barData = <?php echo json_encode($company_hiring); ?>;
        new Chart(document.getElementById('barChart'), {
            type: 'bar',
            data: {
                labels: barData.map(d => d.company_name),
                datasets: [{
                    label: 'Students Hired',
                    data: barData.map(d => d.cnt),
                    backgroundColor: barData.map((_, i) => paletteColors[i % paletteColors.length]),
                    borderRadius: 8,
                    borderSkipped: false,
                    barThickness: 40,
                    hoverBackgroundColor: barData.map((_, i) => paletteColors[i % paletteColors.length] + 'cc')
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        cornerRadius: 8,
                        padding: 12
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, color: '#94a3b8' },
                        grid: { color: 'rgba(0,0,0,0.04)' }
                    },
                    x: {
                        ticks: { color: '#64748b', maxRotation: 45 },
                        grid: { display: false }
                    }
                }
            }
        });
    </script>
</body>
</html>