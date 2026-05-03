<?php
// manage_placements.php (Modernized)
session_name("staff");
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: admin_login.php");
    exit;
}
require_once "../includes/config.php";

$feedback_msg = "";
$feedback_class = "";

// Record Placement Logic
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_placement'])) {
    $student_regdno = $_POST['student_regdno'];
    $job_id = $_POST['job_id'];

    if (empty($student_regdno) || empty($job_id)) {
        $feedback_msg = "Please select both a student and a job.";
        $feedback_class = "alert-error";
    } else {
        $conn->begin_transaction();
        try {
            $sql_insert = "INSERT INTO placements (student_regdno, job_id, package_lpa) SELECT ?, ?, package_lpa FROM jobs WHERE job_id = ?";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("sii", $student_regdno, $job_id, $job_id);
            $stmt_insert->execute();
            $stmt_insert->close();

            $sql_update = "UPDATE student SET placement_status = 'Placed' WHERE regdno = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("s", $student_regdno);
            $stmt_update->execute();
            $stmt_update->close();

            $conn->commit();
            $_SESSION['flash_message'] = "Placement recorded successfully! Student marked as 'Placed'.";
            header("location: admin_manage_placements.php");
            exit;

        } catch (Exception $e) {
            $conn->rollback();
            if ($e->getCode() == 1062 || $conn->errno == 1062) {
                $feedback_msg = "This student has already been recorded as placed.";
            } else {
                $feedback_msg = "An error occurred. " . $e->getMessage();
            }
            $feedback_class = "alert-error";
        }
    }
}



// Fetch Data
$placements = [];
$sql_fetch_placements = "SELECT p.placement_id, s.name, j.company_name, j.job_title, p.package_lpa, p.placement_date
                        FROM placements p
                        JOIN student s ON p.student_regdno = s.regdno
                        LEFT JOIN jobs j ON p.job_id = j.job_id
                        ORDER BY p.placement_date DESC";
if($result = $conn->query($sql_fetch_placements)){
    while($row = $result->fetch_assoc()){ $placements[] = $row; }
    $result->free();
}

$students = [];
$sql_fetch_students = "SELECT regdno, name FROM student WHERE placement_status = 'Not Placed' ORDER BY name ASC";
if($result = $conn->query($sql_fetch_students)){
    while($row = $result->fetch_assoc()){ $students[] = $row; }
    $result->free();
}

$jobs = [];
$sql_fetch_jobs = "SELECT job_id, job_title, company_name FROM jobs ORDER BY company_name, job_title ASC";
if($result = $conn->query($sql_fetch_jobs)){
    while($row = $result->fetch_assoc()){ $jobs[] = $row; }
    $result->free();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Placements - Admin Dashboard</title>
    <?php include '../includes/header_includes.php'; ?>
</head>
    <style>
        /* Table Styles */
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
        
        /* Column Widths */
        th:nth-child(1) { width: 25%; } /* Student */
        th:nth-child(2) { width: 30%; } /* Company & Role */
        th:nth-child(3) { width: 15%; } /* Package */
        th:nth-child(4) { width: 15%; } /* Date */
        th:nth-child(5) { width: 15%; } /* Action */

        /* Searchable Select */
        .searchable-select {
            position: relative;
            width: 100%;
        }
        .searchable-select-trigger {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.75rem 1rem;
            font-size: 0.95rem;
            color: var(--text-main);
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 9999px;
            cursor: pointer;
            transition: all 0.2s;
            line-height: 1.5;
        }
        .searchable-select-trigger:hover {
            border-color: var(--primary);
        }
        .searchable-select.open .searchable-select-trigger {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-light);
        }
        .searchable-select-text {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .searchable-select-text.placeholder {
            color: var(--text-muted);
        }
        .searchable-select-dropdown {
            position: absolute;
            top: calc(100% + 6px);
            left: 0;
            right: 0;
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 1rem;
            box-shadow: var(--shadow-lg);
            z-index: 200;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-8px);
            transition: all 0.2s ease;
        }
        .searchable-select.open .searchable-select-dropdown {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        .searchable-select-search {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1rem;
            border-bottom: 1px solid var(--border);
        }
        .searchable-select-search input {
            border: none;
            outline: none;
            flex: 1;
            font-size: 0.9rem;
            color: var(--text-main);
            background: transparent;
        }
        .searchable-select-search input::placeholder {
            color: var(--text-muted);
        }
        .searchable-select-options {
            list-style: none;
            padding: 0.25rem 0;
            margin: 0;
            max-height: 200px;
            overflow-y: auto;
        }
        .searchable-select-option {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.65rem 1rem;
            cursor: pointer;
            font-size: 0.9rem;
            color: var(--text-main);
            transition: all 0.15s;
        }
        .searchable-select-option:hover,
        .searchable-select-option.highlighted {
            background: var(--primary-light);
            color: var(--primary);
        }
        .searchable-select-option.selected {
            background: var(--primary);
            color: #fff;
            font-weight: 500;
        }
        .searchable-select-option.selected:hover {
            background: var(--primary);
            color: #fff;
        }
        .option-regdno {
            font-size: 0.75rem;
            color: var(--text-muted);
            font-weight: 400;
        }
        .searchable-select-option:hover .option-regdno,
        .searchable-select-option.highlighted .option-regdno {
            color: var(--primary);
            opacity: 0.7;
        }
        .searchable-select-option.selected .option-regdno {
            color: rgba(255,255,255,0.7);
        }
        .searchable-select-empty {
            padding: 1.5rem 1rem;
            text-align: center;
            color: var(--text-muted);
            font-size: 0.875rem;
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
                    <h1 style="font-size: 1.5rem; margin-bottom: 0.5rem;">Manage Placements</h1>
                    <p class="text-muted">Record successful student placements and view history.</p>
                </div>

                <div class="grid grid-cols-3 gap-6" style="grid-template-columns: 1fr 2fr;">
                    
                    <!-- Record Placement Form -->
                    <div class="card" style="height: fit-content;">
                        <div class="card-header" style="margin-bottom: 1.5rem;">
                            <h3><i data-lucide="award" style="width: 20px; vertical-align: middle;"></i> Record Placement</h3>
                        </div>

                        <?php if(!empty($feedback_msg) && $feedback_class !== 'alert-success'): ?>
                            <div class="alert <?php echo $feedback_class; ?>" style="background: #fef2f2; color: #ef4444; border: 1px solid #fee2e2; padding:0.75rem; border-radius:0.5rem; margin-bottom:1rem;">
                                <?php echo $feedback_msg; ?>
                            </div>
                        <?php endif; ?>

                        <form action="../admin/admin_manage_placements.php" method="post">
                            <div class="form-group">
                                <label class="form-label">Student (Not Placed)</label>
                                <input type="hidden" id="student_regdno" name="student_regdno" required>
                                <div class="searchable-select" id="studentSearchSelect">
                                    <div class="searchable-select-trigger" id="studentTrigger">
                                        <span class="searchable-select-text">-- Select Student --</span>
                                        <i data-lucide="chevron-down" style="width:16px; height:16px; flex-shrink:0; color:var(--text-muted);"></i>
                                    </div>
                                    <div class="searchable-select-dropdown" id="studentDropdown">
                                        <div class="searchable-select-search">
                                            <i data-lucide="search" style="width:16px; height:16px; color:var(--text-muted); flex-shrink:0;"></i>
                                            <input type="text" id="studentSearchInput" placeholder="Search students..." autocomplete="off">
                                        </div>
                                        <ul class="searchable-select-options" id="studentOptionsList">
                                            <?php foreach($students as $student): ?>
                                                <li class="searchable-select-option" data-value="<?php echo htmlspecialchars($student['regdno']); ?>">
                                                    <?php echo htmlspecialchars($student['name']); ?>
                                                    <span class="option-regdno"><?php echo htmlspecialchars($student['regdno']); ?></span>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                        <div class="searchable-select-empty" style="display:none;">No students found</div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="job_id">Job Offer</label>
                                <div class="select-wrapper">
                                    <select id="job_id" name="job_id" class="form-select" required>
                                        <option value="">-- Select Job --</option>
                                        <?php foreach($jobs as $job): ?>
                                            <option value="<?php echo $job['job_id']; ?>">
                                                <?php echo htmlspecialchars($job['company_name'] . ' - ' . $job['job_title']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <button type="submit" name="add_placement" class="btn btn-primary w-full" style="width: 100%; justify-content: center;">
                                <i data-lucide="check"></i> Confirm Placement
                            </button>
                        </form>
                    </div>

                    <!-- Placements List -->
                    <div class="card">
                        <div class="card-header" style="margin-bottom: 1.5rem;">
                            <h3>Placed Students (<?php echo count($placements); ?>)</h3>
                        </div>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>Company & Role</th>
                                        <th>Package</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($placements)): ?>
                                        <tr><td colspan="5" class="text-center text-muted">No placements recorded yet.</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($placements as $placement): ?>
                                            <tr>
                                                <td><strong><?php echo htmlspecialchars($placement['name']); ?></strong></td>
                                                <td>
                                                    <div class="font-bold"><?php echo htmlspecialchars($placement['company_name'] ?? 'N/A'); ?></div>
                                                    <div class="text-xs text-muted"><?php echo htmlspecialchars($placement['job_title'] ?? 'N/A'); ?></div>
                                                </td>
                                                <td><?php echo htmlspecialchars($placement['package_lpa'] ?? '-'); ?> LPA</td>
                                                <td class="text-sm text-muted"><?php echo date("M d, Y", strtotime($placement['placement_date'])); ?></td>
                                                <td>
                                                    <a href="admin_delete_placement.php?id=<?php echo $placement['placement_id']; ?>" class="btn btn-sm btn-ghost-danger" title="Remove" onclick="return showDeleteModal(event, this.href, 'Remove Placement', 'Remove this placement record? Student status will revert to Not Placed.');">
                                                        <i data-lucide="trash-2" style="width: 14px;"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
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

        // Searchable Select Logic
        (function() {
            const wrapper = document.getElementById('studentSearchSelect');
            const trigger = document.getElementById('studentTrigger');
            const dropdown = document.getElementById('studentDropdown');
            const searchInput = document.getElementById('studentSearchInput');
            const optionsList = document.getElementById('studentOptionsList');
            const hiddenInput = document.getElementById('student_regdno');
            const textSpan = trigger.querySelector('.searchable-select-text');
            const emptyMsg = dropdown.querySelector('.searchable-select-empty');
            const options = optionsList.querySelectorAll('.searchable-select-option');

            function openDropdown() {
                wrapper.classList.add('open');
                searchInput.value = '';
                filterOptions('');
                setTimeout(() => searchInput.focus(), 50);
            }

            function closeDropdown() {
                wrapper.classList.remove('open');
            }

            function selectOption(li) {
                const val = li.dataset.value;
                const name = li.childNodes[0].textContent.trim();
                hiddenInput.value = val;
                textSpan.textContent = name;
                textSpan.classList.remove('placeholder');
                options.forEach(o => o.classList.remove('selected'));
                li.classList.add('selected');
                closeDropdown();
            }

            function filterOptions(query) {
                const q = query.toLowerCase();
                let visible = 0;
                options.forEach(li => {
                    const name = li.childNodes[0].textContent.trim().toLowerCase();
                    const regdno = li.dataset.value.toLowerCase();
                    if (name.includes(q) || regdno.includes(q)) {
                        li.style.display = '';
                        visible++;
                    } else {
                        li.style.display = 'none';
                    }
                    li.classList.remove('highlighted');
                });
                emptyMsg.style.display = visible === 0 ? '' : 'none';
            }

            trigger.addEventListener('click', function(e) {
                e.stopPropagation();
                if (wrapper.classList.contains('open')) {
                    closeDropdown();
                } else {
                    openDropdown();
                }
            });

            searchInput.addEventListener('input', function() {
                filterOptions(this.value);
            });

            searchInput.addEventListener('keydown', function(e) {
                const visibleOpts = [...options].filter(o => o.style.display !== 'none');
                const current = visibleOpts.findIndex(o => o.classList.contains('highlighted'));
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    visibleOpts.forEach(o => o.classList.remove('highlighted'));
                    const next = current < visibleOpts.length - 1 ? current + 1 : 0;
                    visibleOpts[next].classList.add('highlighted');
                    visibleOpts[next].scrollIntoView({ block: 'nearest' });
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    visibleOpts.forEach(o => o.classList.remove('highlighted'));
                    const prev = current > 0 ? current - 1 : visibleOpts.length - 1;
                    visibleOpts[prev].classList.add('highlighted');
                    visibleOpts[prev].scrollIntoView({ block: 'nearest' });
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    const hl = visibleOpts.find(o => o.classList.contains('highlighted'));
                    if (hl) selectOption(hl);
                } else if (e.key === 'Escape') {
                    closeDropdown();
                }
            });

            options.forEach(li => {
                li.addEventListener('click', function(e) {
                    e.stopPropagation();
                    selectOption(this);
                });
            });

            document.addEventListener('click', function(e) {
                if (!wrapper.contains(e.target)) closeDropdown();
            });
        })();
    </script>
</body>
</html>