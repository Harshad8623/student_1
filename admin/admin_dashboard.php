<?php
include 'config.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header('Location: index.html');
    exit();
}

// Get statistics
$students_count = $pdo->query("SELECT COUNT(*) FROM students WHERE status = 'approved'")->fetchColumn();
$pending_students = $pdo->query("SELECT COUNT(*) FROM students WHERE status = 'pending'")->fetchColumn();
$teachers_count = $pdo->query("SELECT COUNT(*) FROM teachers WHERE status = 'active'")->fetchColumn();
$subjects_count = $pdo->query("SELECT COUNT(*) FROM subjects")->fetchColumn();

// Get recent pending students
$pending_stmt = $pdo->query("SELECT * FROM students WHERE status = 'pending' ORDER BY created_at DESC LIMIT 5");
$pending_students_list = $pending_stmt->fetchAll();

// Get attendance statistics
$attendance_stats = $pdo->query("
    SELECT 
        AVG(CASE WHEN status = 'present' THEN 1 ELSE 0 END) * 100 as avg_attendance,
        COUNT(*) as total_records
    FROM attendance
")->fetch();

// Get class-wise statistics
$class_stats = $pdo->query("
    SELECT class, COUNT(*) as student_count 
    FROM students 
    WHERE status = 'approved' 
    GROUP BY class
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - SMS</title>
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

        .stat-card.pending {
            border-left: 4px solid #ffc107;
        }

        .stat-card.pending i {
            color: #ffc107;
        }

        .chart-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .chart-container {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
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

        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: background 0.3s ease;
        }

        .btn-approve {
            background: #28a745;
            color: white;
        }

        .btn-reject {
            background: #dc3545;
            color: white;
        }

        .btn:hover {
            opacity: 0.9;
        }

        .status-pending {
            color: #ffc107;
            font-weight: 600;
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

        .action-buttons {
            display: flex;
            gap: 10px;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo">
            <h2><i class="fas fa-user-shield"></i> Admin Panel</h2>
        </div>
        <ul class="nav-links">
            <li><a href="#" class="active"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="admin_students.php"><i class="fas fa-user-graduate"></i> Students</a></li>
            <li><a href="admin_teachers.php"><i class="fas fa-chalkboard-teacher"></i> Teachers</a></li>
            <li><a href="admin_subjects.php"><i class="fas fa-book"></i> Subjects</a></li>
            <li><a href="admin_attendance.php"><i class="fas fa-calendar-check"></i> Attendance</a></li>
            <li><a href="admin_reports.php"><i class="fas fa-chart-pie"></i> Reports</a></li>
            <li><a href="admin_notices.php"><i class="fas fa-bullhorn"></i> Notices</a></li>
            <li><a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="header">
            <div class="welcome">
                <h1>Admin Dashboard</h1>
                <p>Welcome, <?php echo $_SESSION['full_name']; ?>! Manage your institution efficiently.</p>
            </div>
            <div class="user-info">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($_SESSION['full_name'], 0, 1)); ?>
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
                <i class="fas fa-user-graduate"></i>
                <h3><?php echo $students_count; ?></h3>
                <p>Total Students</p>
            </div>
            <div class="stat-card pending">
                <i class="fas fa-clock"></i>
                <h3><?php echo $pending_students; ?></h3>
                <p>Pending Approvals</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-chalkboard-teacher"></i>
                <h3><?php echo $teachers_count; ?></h3>
                <p>Teachers</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-book"></i>
                <h3><?php echo $subjects_count; ?></h3>
                <p>Subjects</p>
            </div>
        </div>

        <div class="chart-grid">
            <div class="chart-container">
                <h3 class="section-title">Students by Class</h3>
                <canvas id="classChart" height="200"></canvas>
            </div>
            <div class="chart-container">
                <h3 class="section-title">Attendance Overview</h3>
                <canvas id="attendanceChart" height="200"></canvas>
            </div>
        </div>

        <div class="table-container">
            <h3 class="section-title">Pending Student Approvals</h3>
            <table>
                <thead>
                    <tr>
                        <th>PRN Number</th>
                        <th>Full Name</th>
                        <th>Class & Division</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Registration Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pending_students_list as $student): ?>
                    <tr>
                        <td><?php echo $student['prn_number']; ?></td>
                        <td><?php echo $student['full_name']; ?></td>
                        <td><?php echo $student['class'] . ' ' . $student['division']; ?></td>
                        <td><?php echo $student['email']; ?></td>
                        <td><?php echo $student['phone']; ?></td>
                        <td><?php echo date('M d, Y', strtotime($student['created_at'])); ?></td>
                        <td class="action-buttons">
                            <form action="admin_actions.php" method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="approve_student">
                                <input type="hidden" name="prn" value="<?php echo $student['prn_number']; ?>">
                                <button type="submit" class="btn btn-approve">Approve</button>
                            </form>
                            <form action="admin_actions.php" method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="reject_student">
                                <input type="hidden" name="prn" value="<?php echo $student['prn_number']; ?>">
                                <button type="submit" class="btn btn-reject">Reject</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($pending_students_list)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center;">No pending approvals</td>
                    </tr>
                    <?php endif; ?>
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
                        '#667eea', '#764ba2', '#f093fb', '#f5576c', '#4facfe', '#00f2fe'
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
                labels: ['Average Attendance', 'Absent/Late'],
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
<!-- Added to all dashboard files -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>