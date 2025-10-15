<?php
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'student') {
    header('Location: index.html');
    exit();
}

$student_prn = $_SESSION['user_id'];

// Get marks summary by subject
$marks_summary = $pdo->prepare("
    SELECT 
        m.subject_code,
        s.subject_name,
        COUNT(*) as exam_count,
        AVG(m.marks_obtained / m.total_marks * 100) as avg_percentage,
        MAX(m.marks_obtained / m.total_marks * 100) as max_percentage,
        MIN(m.marks_obtained / m.total_marks * 100) as min_percentage
    FROM marks m 
    JOIN subjects s ON m.subject_code = s.subject_code 
    WHERE m.student_prn = ? 
    GROUP BY m.subject_code, s.subject_name
    ORDER BY s.subject_name
")->execute([$student_prn])->fetchAll();

// Get detailed marks
$detailed_marks = $pdo->prepare("
    SELECT m.*, s.subject_name 
    FROM marks m 
    JOIN subjects s ON m.subject_code = s.subject_code 
    WHERE m.student_prn = ? 
    ORDER BY m.uploaded_at DESC
")->execute([$student_prn])->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Marks - SMS</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            color: #333;
        }

        .sidebar {
            width: 250px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            height: 100vh;
            position: fixed;
            padding: 20px 0;
        }

        .main-content {
            margin-left: 250px;
            padding: 20px;
        }

        .header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }

        .chart-container {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .table-container {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        th {
            background: #f8f9fa;
            font-weight: 600;
        }

        .percentage-high { color: #28a745; font-weight: 600; }
        .percentage-medium { color: #ffc107; font-weight: 600; }
        .percentage-low { color: #dc3545; font-weight: 600; }

        .grade {
            font-weight: 600;
            padding: 4px 8px;
            border-radius: 4px;
        }

        .grade-A { background: #d4edda; color: #155724; }
        .grade-B { background: #fff3cd; color: #856404; }
        .grade-C { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo">
            <h2><i class="fas fa-graduation-cap"></i> SMS</h2>
        </div>
        <ul class="nav-links">
            <li><a href="student_dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="student_attendance.php"><i class="fas fa-calendar-check"></i> Attendance</a></li>
            <li><a href="#" class="active"><i class="fas fa-chart-bar"></i> Marks & Grades</a></li>
            <li><a href="student_profile.php"><i class="fas fa-user"></i> Profile</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="header">
            <h1>My Marks & Grades</h1>
            <p>Track your academic performance across all subjects</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <i class="fas fa-book"></i>
                <h3><?php echo count($marks_summary); ?></h3>
                <p>Subjects</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-chart-line"></i>
                <h3>
                    <?php
                    $total_percentage = 0;
                    $subject_count = count($marks_summary);
                    foreach ($marks_summary as $subject) {
                        $total_percentage += $subject['avg_percentage'];
                    }
                    echo $subject_count > 0 ? round($total_percentage / $subject_count, 1) : 0;
                    ?>%
                </h3>
                <p>Average Percentage</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-clipboard-list"></i>
                <h3><?php echo count($detailed_marks); ?></h3>
                <p>Total Exams</p>
            </div>
        </div>

        <div class="chart-container">
            <h3>Subject-wise Performance</h3>
            <canvas id="performanceChart" height="100"></canvas>
        </div>

        <div class="table-container">
            <h3>Performance Summary by Subject</h3>
            <table>
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Exams Taken</th>
                        <th>Average %</th>
                        <th>Highest %</th>
                        <th>Lowest %</th>
                        <th>Grade</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($marks_summary as $subject): ?>
                    <?php 
                    $avg_percentage = round($subject['avg_percentage'], 1);
                    $grade = '';
                    $grade_class = '';
                    
                    if ($avg_percentage >= 80) {
                        $grade = 'A';
                        $grade_class = 'grade-A';
                    } elseif ($avg_percentage >= 60) {
                        $grade = 'B';
                        $grade_class = 'grade-B';
                    } else {
                        $grade = 'C';
                        $grade_class = 'grade-C';
                    }
                    ?>
                    <tr>
                        <td><?php echo $subject['subject_name']; ?></td>
                        <td><?php echo $subject['exam_count']; ?></td>
                        <td class="percentage-<?php 
                            if ($avg_percentage >= 75) echo 'high';
                            elseif ($avg_percentage >= 50) echo 'medium';
                            else echo 'low';
                        ?>">
                            <?php echo $avg_percentage; ?>%
                        </td>
                        <td><?php echo round($subject['max_percentage'], 1); ?>%</td>
                        <td><?php echo round($subject['min_percentage'], 1); ?>%</td>
                        <td><span class="grade <?php echo $grade_class; ?>"><?php echo $grade; ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="table-container">
            <h3>Detailed Marks</h3>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Subject</th>
                        <th>Exam Type</th>
                        <th>Marks Obtained</th>
                        <th>Total Marks</th>
                        <th>Percentage</th>
                        <th>Grade</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($detailed_marks as $mark): ?>
                    <?php 
                    $percentage = round(($mark['marks_obtained'] / $mark['total_marks']) * 100, 1);
                    $grade = '';
                    $grade_class = '';
                    
                    if ($percentage >= 80) {
                        $grade = 'A';
                        $grade_class = 'grade-A';
                    } elseif ($percentage >= 60) {
                        $grade = 'B';
                        $grade_class = 'grade-B';
                    } else {
                        $grade = 'C';
                        $grade_class = 'grade-C';
                    }
                    ?>
                    <tr>
                        <td><?php echo date('M d, Y', strtotime($mark['uploaded_at'])); ?></td>
                        <td><?php echo $mark['subject_name']; ?></td>
                        <td><?php echo $mark['exam_type']; ?></td>
                        <td><?php echo $mark['marks_obtained']; ?></td>
                        <td><?php echo $mark['total_marks']; ?></td>
                        <td class="percentage-<?php 
                            if ($percentage >= 75) echo 'high';
                            elseif ($percentage >= 50) echo 'medium';
                            else echo 'low';
                        ?>">
                            <?php echo $percentage; ?>%
                        </td>
                        <td><span class="grade <?php echo $grade_class; ?>"><?php echo $grade; ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Performance Chart
        const ctx = document.getElementById('performanceChart').getContext('2d');
        const performanceChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: [<?php 
                    $labels = [];
                    foreach ($marks_summary as $subject) {
                        $labels[] = "'" . $subject['subject_name'] . "'";
                    }
                    echo implode(', ', $labels);
                ?>],
                datasets: [{
                    label: 'Average Percentage',
                    data: [<?php 
                        $data = [];
                        foreach ($marks_summary as $subject) {
                            $data[] = round($subject['avg_percentage'], 1);
                        }
                        echo implode(', ', $data);
                    ?>],
                    backgroundColor: '#667eea',
                    borderColor: '#764ba2',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        title: {
                            display: true,
                            text: 'Percentage (%)'
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>