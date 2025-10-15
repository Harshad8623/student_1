<?php
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header('Location: index.html');
    exit();
}

// Get report data
$total_students = $pdo->query("SELECT COUNT(*) FROM students WHERE status = 'approved'")->fetchColumn();
$total_teachers = $pdo->query("SELECT COUNT(*) FROM teachers WHERE status = 'active'")->fetchColumn();
$total_subjects = $pdo->query("SELECT COUNT(*) FROM subjects")->fetchColumn();

// Attendance statistics
$attendance_stats = $pdo->query("
    SELECT 
        AVG(CASE WHEN status = 'present' THEN 1 ELSE 0 END) * 100 as avg_attendance,
        COUNT(*) as total_records
    FROM attendance
")->fetch();

// Class-wise student count
$class_stats = $pdo->query("
    SELECT class, COUNT(*) as student_count 
    FROM students 
    WHERE status = 'approved' 
    GROUP BY class
")->fetchAll();

// Generate report if requested
if (isset($_GET['generate_report'])) {
    $report_type = $_GET['report_type'];
    $class = $_GET['class'];
    
    // This would generate detailed reports (simplified for example)
    if ($report_type == 'attendance') {
        // Generate attendance report
    } elseif ($report_type == 'performance') {
        // Generate performance report
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .report-filters {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .filter-group {
            display: flex;
            gap: 15px;
            align-items: end;
        }

        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }

        .chart-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .chart-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo">
            <h2><i class="fas fa-user-shield"></i> Admin Panel</h2>
        </div>
        <ul class="nav-links">
            <li><a href="admin_dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="admin_students.php"><i class="fas fa-user-graduate"></i> Students</a></li>
            <li><a href="admin_teachers.php"><i class="fas fa-chalkboard-teacher"></i> Teachers</a></li>
            <li><a href="admin_subjects.php"><i class="fas fa-book"></i> Subjects</a></li>
            <li><a href="#" class="active"><i class="fas fa-chart-pie"></i> Reports</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="header">
            <h1>Reports & Analytics</h1>
            <p>Generate comprehensive reports and view analytics</p>
        </div>

        <div class="stats-cards">
            <div class="stat-card">
                <i class="fas fa-user-graduate" style="color: #667eea; font-size: 2rem;"></i>
                <h3><?php echo $total_students; ?></h3>
                <p>Total Students</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-chalkboard-teacher" style="color: #28a745; font-size: 2rem;"></i>
                <h3><?php echo $total_teachers; ?></h3>
                <p>Total Teachers</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-book" style="color: #ffc107; font-size: 2rem;"></i>
                <h3><?php echo $total_subjects; ?></h3>
                <p>Total Subjects</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-calendar-check" style="color: #dc3545; font-size: 2rem;"></i>
                <h3><?php echo round($attendance_stats['avg_attendance'], 1); ?>%</h3>
                <p>Avg Attendance</p>
            </div>
        </div>

        <div class="report-filters">
            <h3>Generate Report</h3>
            <form method="GET">
                <div class="filter-group">
                    <div class="form-group">
                        <label>Report Type</label>
                        <select name="report_type" required>
                            <option value="attendance">Attendance Report</option>
                            <option value="performance">Performance Report</option>
                            <option value="students">Student Report</option>
                            <option value="teachers">Teacher Report</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Class</label>
                        <select name="class">
                            <option value="">All Classes</option>
                            <option value="FY">First Year</option>
                            <option value="SY">Second Year</option>
                            <option value="TY">Third Year</option>
                            <option value="BE">Final Year</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Date From</label>
                        <input type="date" name="date_from">
                    </div>
                    <div class="form-group">
                        <label>Date To</label>
                        <input type="date" name="date_to">
                    </div>
                    <div class="form-group">
                        <button type="submit" name="generate_report" class="btn btn-primary">
                            <i class="fas fa-download"></i> Generate Report
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="chart-grid">
            <div class="chart-container">
                <h3>Students by Class</h3>
                <canvas id="classChart" height="200"></canvas>
            </div>
            <div class="chart-container">
                <h3>Attendance Overview</h3>
                <canvas id="attendanceChart" height="200"></canvas>
            </div>
        </div>

        <div class="table-container">
            <h3>Class-wise Statistics</h3>
            <table>
                <thead>
                    <tr>
                        <th>Class</th>
                        <th>Number of Students</th>
                        <th>Percentage</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($class_stats as $class): ?>
                    <tr>
                        <td><?php echo $class['class']; ?></td>
                        <td><?php echo $class['student_count']; ?></td>
                        <td><?php echo round(($class['student_count'] / $total_students) * 100, 1); ?>%</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Class Distribution Chart
        const classCtx = document.getElementById('classChart').getContext('2d');
        const classChart = new Chart(classCtx, {
            type: 'doughnut',
            data: {
                labels: [<?php 
                    $labels = [];
                    foreach ($class_stats as $stat) {
                        $labels[] = "'" . $stat['class'] . "'";
                    }
                    echo implode(', ', $labels);
                ?>],
                datasets: [{
                    data: [<?php 
                        $data = [];
                        foreach ($class_stats as $stat) {
                            $data[] = $stat['student_count'];
                        }
                        echo implode(', ', $data);
                    ?>],
                    backgroundColor: [
                        '#667eea', '#764ba2', '#f093fb', '#f5576c', '#4facfe'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Attendance Chart
        const attendanceCtx = document.getElementById('attendanceChart').getContext('2d');
        const attendanceChart = new Chart(attendanceCtx, {
            type: 'pie',
            data: {
                labels: ['Present', 'Absent/Late'],
                datasets: [{
                    data: [
                        <?php echo round($attendance_stats['avg_attendance'], 2); ?>,
                        <?php echo 100 - round($attendance_stats['avg_attendance'], 2); ?>
                    ],
                    backgroundColor: ['#28a745', '#dc3545']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>
</body>
</html>