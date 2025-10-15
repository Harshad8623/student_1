<?php
include 'config.php';

// Check if student is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'student') {
    header('Location: index.html');
    exit();
}

// Get student data
$stmt = $pdo->prepare("SELECT * FROM students WHERE prn_number = ?");
$stmt->execute([$_SESSION['user_id']]);
$student = $stmt->fetch();

// Get attendance summary
$attendance_stmt = $pdo->prepare("
    SELECT subject_code, 
           COUNT(*) as total_classes,
           SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present,
           SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late
    FROM attendance 
    WHERE student_prn = ? 
    GROUP BY subject_code
");
$attendance_stmt->execute([$_SESSION['user_id']]);
$attendance_summary = $attendance_stmt->fetchAll();

// Get recent marks
$marks_stmt = $pdo->prepare("
    SELECT m.*, s.subject_name 
    FROM marks m 
    JOIN subjects s ON m.subject_code = s.subject_code 
    WHERE m.student_prn = ? 
    ORDER BY m.uploaded_at DESC 
    LIMIT 5
");
$marks_stmt->execute([$_SESSION['user_id']]);
$recent_marks = $marks_stmt->fetchAll();

// Get notices
$notices_stmt = $pdo->prepare("
    SELECT * FROM notices 
    WHERE (target_audience = 'all' OR target_audience = 'students')
    AND (class IS NULL OR class = ?)
    ORDER BY created_at DESC 
    LIMIT 5
");
$notices_stmt->execute([$student['class']]);
$notices = $notices_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - SMS</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

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

        .logo h2 {
            font-size: 1.5rem;
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

        .nav-links i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
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
            display: flex;
            justify-content: between;
            align-items: center;
        }

        .welcome h1 {
            color: #333;
            margin-bottom: 5px;
        }

        .welcome p {
            color: #666;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-avatar {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
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

        .stat-card i {
            font-size: 2rem;
            color: #667eea;
            margin-bottom: 15px;
        }

        .stat-card h3 {
            font-size: 2rem;
            margin-bottom: 5px;
            color: #333;
        }

        .stat-card p {
            color: #666;
        }

        .chart-container {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .section-title {
            margin-bottom: 20px;
            color: #333;
            font-size: 1.5rem;
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
            color: #333;
        }

        .attendance-percentage {
            font-weight: bold;
        }

        .percentage-high {
            color: #28a745;
        }

        .percentage-medium {
            color: #ffc107;
        }

        .percentage-low {
            color: #dc3545;
        }

        .logout-btn {
            background: rgba(255,255,255,0.1);
            color: white;
            border: 1px solid rgba(255,255,255,0.3);
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .logout-btn:hover {
            background: rgba(255,255,255,0.2);
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo">
            <h2><i class="fas fa-graduation-cap"></i> SMS</h2>
        </div>
        <ul class="nav-links">
            <li><a href="#" class="active"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="student_attendance.php"><i class="fas fa-calendar-check"></i> Attendance</a></li>
            <li><a href="student_marks.php"><i class="fas fa-chart-bar"></i> Marks & Grades</a></li>
            <li><a href="student_profile.php"><i class="fas fa-user"></i> Profile</a></li>
            <li><a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="header">
            <div class="welcome">
                <h1>Welcome, <?php echo $student['full_name']; ?>!</h1>
                <p>PRN: <?php echo $student['prn_number']; ?> | Class: <?php echo $student['class'] . ' ' . $student['division']; ?></p>
            </div>
            <div class="user-info">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($student['full_name'], 0, 1)); ?>
                </div>
                <form action="logout.php" method="POST">
                    <button type="submit" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </form>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <i class="fas fa-calendar-check"></i>
                <h3>
                    <?php 
                    $total_classes = 0;
                    $present_classes = 0;
                    foreach ($attendance_summary as $subject) {
                        $total_classes += $subject['total_classes'];
                        $present_classes += $subject['present'];
                    }
                    echo $total_classes > 0 ? round(($present_classes / $total_classes) * 100) : 0;
                    ?>%
                </h3>
                <p>Overall Attendance</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-book"></i>
                <h3><?php echo count($attendance_summary); ?></h3>
                <p>Subjects</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-bell"></i>
                <h3><?php echo count($notices); ?></h3>
                <p>New Notices</p>
            </div>
        </div>

        <div class="chart-container">
            <h3 class="section-title">Attendance Overview</h3>
            <canvas id="attendanceChart" height="100"></canvas>
        </div>

        <div class="table-container">
            <h3 class="section-title">Recent Marks</h3>
            <table>
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Exam Type</th>
                        <th>Marks Obtained</th>
                        <th>Total Marks</th>
                        <th>Percentage</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_marks as $mark): ?>
                    <tr>
                        <td><?php echo $mark['subject_name']; ?></td>
                        <td><?php echo $mark['exam_type']; ?></td>
                        <td><?php echo $mark['marks_obtained']; ?></td>
                        <td><?php echo $mark['total_marks']; ?></td>
                        <td class="attendance-percentage <?php
                            $percentage = ($mark['marks_obtained'] / $mark['total_marks']) * 100;
                            if ($percentage >= 75) echo 'percentage-high';
                            elseif ($percentage >= 50) echo 'percentage-medium';
                            else echo 'percentage-low';
                        ?>">
                            <?php echo round($percentage, 2); ?>%
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="table-container">
            <h3 class="section-title">Recent Notices</h3>
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Posted By</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($notices as $notice): ?>
                    <tr>
                        <td><?php echo $notice['title']; ?></td>
                        <td><?php echo $notice['posted_by']; ?></td>
                        <td><?php echo date('M d, Y', strtotime($notice['created_at'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Attendance Chart
        const ctx = document.getElementById('attendanceChart').getContext('2d');
        const attendanceChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: [<?php 
                    $labels = [];
                    foreach ($attendance_summary as $subject) {
                        $labels[] = "'" . $subject['subject_code'] . "'";
                    }
                    echo implode(', ', $labels);
                ?>],
                datasets: [{
                    label: 'Attendance Percentage',
                    data: [<?php 
                        $data = [];
                        foreach ($attendance_summary as $subject) {
                            $percentage = $subject['total_classes'] > 0 ? 
                                (($subject['present'] + $subject['late'] * 0.5) / $subject['total_classes']) * 100 : 0;
                            $data[] = round($percentage, 2);
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
<!-- Added to all dashboard files -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>