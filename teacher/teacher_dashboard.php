<?php
include 'config.php';

// Check if teacher is logged in
if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] != 'subject' && $_SESSION['user_type'] != 'class')) {
    header('Location: index.html');
    exit();
}

$teacher_id = $_SESSION['user_id'];
$teacher_type = $_SESSION['user_type'];

// Get teacher data
$stmt = $pdo->prepare("SELECT * FROM teachers WHERE teacher_id = ?");
$stmt->execute([$teacher_id]);
$teacher = $stmt->fetch();

// Get statistics based on teacher type
if ($teacher_type == 'subject') {
    $students_count = $pdo->prepare("
        SELECT COUNT(DISTINCT s.prn_number) 
        FROM students s 
        JOIN subjects sub ON s.class = sub.class 
        WHERE sub.teacher_id = ? AND s.status = 'approved'
    ")->execute([$teacher_id])->fetchColumn();
    
    $attendance_count = $pdo->prepare("
        SELECT COUNT(*) 
        FROM attendance 
        WHERE recorded_by = ?
    ")->execute([$teacher_id])->fetchColumn();
    
    $marks_count = $pdo->prepare("
        SELECT COUNT(*) 
        FROM marks 
        WHERE uploaded_by = ?
    ")->execute([$teacher_id])->fetchColumn();
    
} else {
    // Class teacher - can see all students in their class
    $students_count = $pdo->prepare("
        SELECT COUNT(*) 
        FROM students 
        WHERE class = ? AND division = ? AND status = 'approved'
    ")->execute([$teacher['class_assigned'], $teacher['division_assigned']])->fetchColumn();
    
    $attendance_count = $pdo->prepare("
        SELECT COUNT(*) 
        FROM attendance a 
        JOIN students s ON a.student_prn = s.prn_number 
        WHERE s.class = ? AND s.division = ?
    ")->execute([$teacher['class_assigned'], $teacher['division_assigned']])->fetchColumn();
    
    $marks_count = $pdo->prepare("
        SELECT COUNT(*) 
        FROM marks m 
        JOIN students s ON m.student_prn = s.prn_number 
        WHERE s.class = ? AND s.division = ?
    ")->execute([$teacher['class_assigned'], $teacher['division_assigned']])->fetchColumn();
}

// Get recent activities
$recent_attendance = $pdo->prepare("
    SELECT a.*, s.full_name 
    FROM attendance a 
    JOIN students s ON a.student_prn = s.prn_number 
    WHERE a.recorded_by = ? 
    ORDER BY a.created_at DESC 
    LIMIT 5
")->execute([$teacher_id])->fetchAll();

// Get subject-wise attendance if subject teacher
if ($teacher_type == 'subject') {
    $subject_attendance = $pdo->prepare("
        SELECT 
            subject_code,
            COUNT(*) as total_classes,
            AVG(CASE WHEN status = 'present' THEN 1 ELSE 0 END) * 100 as attendance_percentage
        FROM attendance 
        WHERE recorded_by = ?
        GROUP BY subject_code
    ")->execute([$teacher_id])->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard - SMS</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Reuse styles from admin dashboard with minor modifications */
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
            justify-content: space-between;
            align-items: center;
        }

        .welcome h1 {
            color: #333;
            margin-bottom: 5px;
        }

        .welcome p {
            color: #666;
        }

        .teacher-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-top: 10px;
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
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
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

        .status-present { color: #28a745; font-weight: 600; }
        .status-absent { color: #dc3545; font-weight: 600; }
        .status-late { color: #ffc107; font-weight: 600; }

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

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .action-btn {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
            text-decoration: none;
            color: #333;
            transition: all 0.3s ease;
        }

        .action-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            color: #667eea;
        }

        .action-btn i {
            font-size: 2rem;
            margin-bottom: 10px;
            color: #667eea;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo">
            <h2><i class="fas fa-chalkboard-teacher"></i> Teacher Panel</h2>
        </div>
        <ul class="nav-links">
            <li><a href="#" class="active"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="teacher_attendance.php"><i class="fas fa-calendar-check"></i> Take Attendance</a></li>
            <li><a href="teacher_marks.php"><i class="fas fa-chart-bar"></i> Upload Marks</a></li>
            <li><a href="teacher_students.php"><i class="fas fa-user-graduate"></i> Students</a></li>
            <li><a href="teacher_reports.php"><i class="fas fa-chart-pie"></i> Reports</a></li>
            <li><a href="teacher_profile.php"><i class="fas fa-user"></i> Profile</a></li>
            <li><a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="header">
            <div class="welcome">
                <h1>Teacher Dashboard</h1>
                <p>Welcome, <?php echo $teacher['full_name']; ?>!</p>
                <div class="teacher-info">
                    <strong>Teacher ID:</strong> <?php echo $teacher['teacher_id']; ?> | 
                    <strong>Type:</strong> <?php echo ucfirst($teacher['type']); ?> Teacher |
                    <?php if ($teacher['type'] == 'subject'): ?>
                    <strong>Subject:</strong> <?php echo $teacher['subject']; ?>
                    <?php else: ?>
                    <strong>Class:</strong> <?php echo $teacher['class_assigned'] . ' ' . $teacher['division_assigned']; ?>
                    <?php endif; ?>
                </div>
            </div>
            <div class="user-info">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($teacher['full_name'], 0, 1)); ?>
                </div>
                <form action="logout.php" method="POST">
                    <button type="submit" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </form>
            </div>
        </div>

        <div class="quick-actions">
            <a href="teacher_attendance.php" class="action-btn">
                <i class="fas fa-calendar-check"></i>
                <h3>Take Attendance</h3>
                <p>Mark student attendance</p>
            </a>
            <a href="teacher_marks.php" class="action-btn">
                <i class="fas fa-upload"></i>
                <h3>Upload Marks</h3>
                <p>Enter exam results</p>
            </a>
            <a href="teacher_students.php" class="action-btn">
                <i class="fas fa-users"></i>
                <h3>View Students</h3>
                <p>Student list & details</p>
            </a>
            <a href="teacher_reports.php" class="action-btn">
                <i class="fas fa-chart-bar"></i>
                <h3>Generate Reports</h3>
                <p>Attendance & marks reports</p>
            </a>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <i class="fas fa-user-graduate"></i>
                <h3><?php echo $students_count; ?></h3>
                <p>Total Students</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-calendar-check"></i>
                <h3><?php echo $attendance_count; ?></h3>
                <p>Attendance Records</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-chart-bar"></i>
                <h3><?php echo $marks_count; ?></h3>
                <p>Marks Uploaded</p>
            </div>
        </div>

        <?php if ($teacher_type == 'subject' && !empty($subject_attendance)): ?>
        <div class="chart-container">
            <h3 class="section-title">Subject-wise Attendance</h3>
            <canvas id="attendanceChart" height="100"></canvas>
        </div>
        <?php endif; ?>

        <div class="table-container">
            <h3 class="section-title">Recent Attendance Activities</h3>
            <table>
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Subject</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_attendance as $record): ?>
                    <tr>
                        <td><?php echo $record['full_name']; ?></td>
                        <td><?php echo $record['subject_code']; ?></td>
                        <td><?php echo date('M d, Y', strtotime($record['date'])); ?></td>
                        <td class="status-<?php echo $record['status']; ?>">
                            <?php echo ucfirst($record['status']); ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($recent_attendance)): ?>
                    <tr>
                        <td colspan="4" style="text-align: center;">No recent attendance records</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if ($teacher_type == 'subject' && !empty($subject_attendance)): ?>
    <script>
        // Attendance Chart for Subject Teacher
        const ctx = document.getElementById('attendanceChart').getContext('2d');
        const attendanceChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: [<?php 
                    $labels = [];
                    foreach ($subject_attendance as $subject) {
                        $labels[] = "'" . $subject['subject_code'] . "'";
                    }
                    echo implode(', ', $labels);
                ?>],
                datasets: [{
                    label: 'Attendance Percentage',
                    data: [<?php 
                        $data = [];
                        foreach ($subject_attendance as $subject) {
                            $data[] = round($subject['attendance_percentage'], 2);
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
    <?php endif; ?>
</body>
</html>
<!-- Added to all dashboard files -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>