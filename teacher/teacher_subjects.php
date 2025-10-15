<?php
include 'config.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] != 'subject' && $_SESSION['user_type'] != 'class')) {
    header('Location: index.html');
    exit();
}

$teacher_id = $_SESSION['user_id'];

// Get teacher's subjects
$subjects = $pdo->prepare("
    SELECT s.*, COUNT(DISTINCT st.prn_number) as student_count
    FROM subjects s 
    LEFT JOIN students st ON s.class = st.class 
    WHERE s.teacher_id = ? 
    AND st.status = 'approved'
    GROUP BY s.subject_code, s.subject_name, s.class
")->execute([$teacher_id])->fetchAll();

// Get subject statistics
$subject_stats = [];
foreach ($subjects as $subject) {
    $stats = $pdo->prepare("
        SELECT 
            COUNT(DISTINCT student_prn) as total_students,
            AVG(CASE WHEN status = 'present' THEN 1 ELSE 0 END) * 100 as avg_attendance,
            COUNT(DISTINCT DATE(date)) as classes_taken
        FROM attendance 
        WHERE subject_code = ? AND recorded_by = ?
    ")->execute([$subject['subject_code'], $teacher_id])->fetch();
    
    $subject_stats[$subject['subject_code']] = $stats;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Subjects - Teacher</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="sidebar">
        <div class="logo">
            <h2><i class="fas fa-chalkboard-teacher"></i> Teacher Panel</h2>
        </div>
        <ul class="nav-links">
            <li><a href="teacher_dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="teacher_attendance.php"><i class="fas fa-calendar-check"></i> Take Attendance</a></li>
            <li><a href="teacher_marks.php"><i class="fas fa-chart-bar"></i> Upload Marks</a></li>
            <li><a href="teacher_students.php"><i class="fas fa-user-graduate"></i> Students</a></li>
            <li><a href="#" class="active"><i class="fas fa-book"></i> My Subjects</a></li>
            <li><a href="teacher_reports.php"><i class="fas fa-chart-pie"></i> Reports</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="header">
            <h1>My Subjects</h1>
            <p>Manage and view your assigned subjects</p>
        </div>

        <?php if (empty($subjects)): ?>
            <div class="alert alert-warning">
                <i class="fas fa-info-circle"></i> No subjects assigned to you yet.
            </div>
        <?php else: ?>
            <div class="subjects-grid">
                <?php foreach ($subjects as $subject): ?>
                <?php $stats = $subject_stats[$subject['subject_code']] ?? []; ?>
                <div class="subject-card">
                    <div class="subject-header">
                        <h3><?php echo $subject['subject_name']; ?></h3>
                        <span class="subject-code"><?php echo $subject['subject_code']; ?></span>
                    </div>
                    <div class="subject-info">
                        <div class="info-item">
                            <i class="fas fa-users"></i>
                            <span><?php echo $subject['student_count']; ?> Students</span>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-chalkboard"></i>
                            <span><?php echo $stats['classes_taken'] ?? 0; ?> Classes</span>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-calendar-check"></i>
                            <span><?php echo round($stats['avg_attendance'] ?? 0, 1); ?>% Attendance</span>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-graduation-cap"></i>
                            <span><?php echo $subject['class'] ?: 'All Classes'; ?></span>
                        </div>
                    </div>
                    <div class="subject-actions">
                        <a href="teacher_attendance.php?subject=<?php echo $subject['subject_code']; ?>" 
                           class="btn btn-primary">
                            <i class="fas fa-calendar-check"></i> Take Attendance
                        </a>
                        <a href="teacher_marks.php?subject=<?php echo $subject['subject_code']; ?>" 
                           class="btn btn-success">
                            <i class="fas fa-upload"></i> Upload Marks
                        </a>
                        <a href="teacher_reports.php?subject=<?php echo $subject['subject_code']; ?>" 
                           class="btn btn-info">
                            <i class="fas fa-chart-bar"></i> View Reports
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Subject Statistics -->
        <?php if (!empty($subjects)): ?>
        <div class="table-container">
            <h3>Subject Performance Overview</h3>
            <table>
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Code</th>
                        <th>Class</th>
                        <th>Students</th>
                        <th>Classes Taken</th>
                        <th>Avg Attendance</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($subjects as $subject): ?>
                    <?php $stats = $subject_stats[$subject['subject_code']] ?? []; ?>
                    <tr>
                        <td><?php echo $subject['subject_name']; ?></td>
                        <td><?php echo $subject['subject_code']; ?></td>
                        <td><?php echo $subject['class'] ?: 'All'; ?></td>
                        <td><?php echo $subject['student_count']; ?></td>
                        <td><?php echo $stats['classes_taken'] ?? 0; ?></td>
                        <td class="<?php
                            $attendance = $stats['avg_attendance'] ?? 0;
                            if ($attendance >= 75) echo 'percentage-high';
                            elseif ($attendance >= 50) echo 'percentage-medium';
                            else echo 'percentage-low';
                        ?>">
                            <?php echo round($attendance, 1); ?>%
                        </td>
                        <td>
                            <span class="status-active">Active</span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <style>
        .subjects-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .subject-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .subject-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .subject-header h3 {
            margin: 0;
            font-size: 1.3rem;
        }

        .subject-code {
            background: rgba(255,255,255,0.2);
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.9rem;
        }

        .subject-info {
            padding: 20px;
        }

        .info-item {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .info-item i {
            width: 20px;
            margin-right: 10px;
            color: #667eea;
        }

        .subject-actions {
            padding: 15px 20px;
            background: #f8f9fa;
            border-top: 1px solid #e0e0e0;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .subject-actions .btn {
            padding: 8px 12px;
            font-size: 0.9rem;
        }
    </style>
</body>
</html>