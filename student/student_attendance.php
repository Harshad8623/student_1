<?php
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'student') {
    header('Location: index.html');
    exit();
}

$student_prn = $_SESSION['user_id'];

// Get attendance summary by subject
$attendance_summary = $pdo->prepare("
    SELECT 
        subject_code,
        COUNT(*) as total_classes,
        SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present,
        SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late,
        SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent,
        (SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) + 
         SUM(CASE WHEN status = 'late' THEN 0.5 ELSE 0 END)) / COUNT(*) * 100 as percentage
    FROM attendance 
    WHERE student_prn = ? 
    GROUP BY subject_code
    ORDER BY subject_code
")->execute([$student_prn])->fetchAll();

// Get detailed attendance records
$detailed_attendance = $pdo->prepare("
    SELECT a.*, s.subject_name 
    FROM attendance a 
    JOIN subjects s ON a.subject_code = s.subject_code 
    WHERE a.student_prn = ? 
    ORDER BY a.date DESC, a.subject_code 
    LIMIT 50
")->execute([$student_prn])->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Attendance - SMS</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Reuse styles from student dashboard */
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

        .logo {
            text-align: center;
            padding: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
        }

        .nav-links {
            list-style: none;
            padding: 0 20px;
        }

        .nav-links li {
            margin-bottom: 10px;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            padding: 12px 15px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            transition: background 0.3s ease;
        }

        .nav-links a:hover, .nav-links a.active {
            background: rgba(255,255,255,0.1);
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

        .attendance-percentage {
            font-weight: bold;
        }

        .percentage-high { color: #28a745; }
        .percentage-medium { color: #ffc107; }
        .percentage-low { color: #dc3545; }

        .status-present { color: #28a745; font-weight: 600; }
        .status-absent { color: #dc3545; font-weight: 600; }
        .status-late { color: #ffc107; font-weight: 600; }

        .progress-bar {
            width: 100%;
            height: 10px;
            background: #e0e0e0;
            border-radius: 5px;
            overflow: hidden;
            margin-top: 5px;
        }

        .progress-fill {
            height: 100%;
            background: #667eea;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo">
            <h2><i class="fas fa-graduation-cap"></i> SMS</h2>
        </div>
        <ul class="nav-links">
            <li><a href="student_dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="#" class="active"><i class="fas fa-calendar-check"></i> Attendance</a></li>
            <li><a href="student_marks.php"><i class="fas fa-chart-bar"></i> Marks & Grades</a></li>
            <li><a href="student_profile.php"><i class="fas fa-user"></i> Profile</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="header">
            <h1>My Attendance</h1>
            <p>Track your attendance across all subjects</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <i class="fas fa-book"></i>
                <h3><?php echo count($attendance_summary); ?></h3>
                <p>Subjects</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-calendar-check"></i>
                <h3>
                    <?php
                    $total_classes = 0;
                    $present_classes = 0;
                    foreach ($attendance_summary as $subject) {
                        $total_classes += $subject['total_classes'];
                        $present_classes += $subject['present'] + ($subject['late'] * 0.5);
                    }
                    echo $total_classes > 0 ? round(($present_classes / $total_classes) * 100, 1) : 0;
                    ?>%
                </h3>
                <p>Overall Attendance</p>
            </div>
        </div>

        <div class="table-container">
            <h3>Attendance Summary by Subject</h3>
            <table>
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Total Classes</th>
                        <th>Present</th>
                        <th>Late</th>
                        <th>Absent</th>
                        <th>Attendance %</th>
                        <th>Progress</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($attendance_summary as $subject): ?>
                    <tr>
                        <td><?php echo $subject['subject_code']; ?></td>
                        <td><?php echo $subject['total_classes']; ?></td>
                        <td class="status-present"><?php echo $subject['present']; ?></td>
                        <td class="status-late"><?php echo $subject['late']; ?></td>
                        <td class="status-absent"><?php echo $subject['absent']; ?></td>
                        <td class="attendance-percentage <?php
                            if ($subject['percentage'] >= 75) echo 'percentage-high';
                            elseif ($subject['percentage'] >= 50) echo 'percentage-medium';
                            else echo 'percentage-low';
                        ?>">
                            <?php echo round($subject['percentage'], 1); ?>%
                        </td>
                        <td>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?php echo round($subject['percentage'], 1); ?>%"></div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="table-container">
            <h3>Detailed Attendance Records</h3>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Subject</th>
                        <th>Status</th>
                        <th>Recorded By</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($detailed_attendance as $record): ?>
                    <tr>
                        <td><?php echo date('M d, Y', strtotime($record['date'])); ?></td>
                        <td><?php echo $record['subject_name']; ?></td>
                        <td class="status-<?php echo $record['status']; ?>">
                            <?php echo ucfirst($record['status']); ?>
                        </td>
                        <td><?php echo $record['recorded_by']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>