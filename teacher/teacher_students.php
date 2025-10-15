<?php
include 'config.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] != 'subject' && $_SESSION['user_type'] != 'class')) {
    header('Location: index.html');
    exit();
}

$teacher_id = $_SESSION['user_id'];
$teacher_type = $_SESSION['user_type'];

// Get teacher details
$teacher = $pdo->prepare("SELECT * FROM teachers WHERE teacher_id = ?")->execute([$teacher_id])->fetch();

// Get students based on teacher type
if ($teacher_type == 'subject') {
    // Get students for teacher's subjects
    $students = $pdo->prepare("
        SELECT DISTINCT s.* 
        FROM students s 
        JOIN subjects sub ON s.class = sub.class 
        WHERE sub.teacher_id = ? AND s.status = 'approved'
        ORDER BY s.class, s.division, s.full_name
    ")->execute([$teacher_id])->fetchAll();
    
    $title = "Students for My Subjects";
} else {
    // Class teacher - get students in their class
    $students = $pdo->prepare("
        SELECT s.* 
        FROM students s 
        WHERE s.class = ? AND s.division = ? AND s.status = 'approved'
        ORDER BY s.full_name
    ")->execute([$teacher['class_assigned'], $teacher['division_assigned']])->fetchAll();
    
    $title = "Students in " . $teacher['class_assigned'] . ' ' . $teacher['division_assigned'];
}

// Get student statistics
if (!empty($students)) {
    $student_prns = array_column($students, 'prn_number');
    $placeholders = str_repeat('?,', count($student_prns) - 1) . '?';
    
    // Get attendance statistics
    $attendance_stats = $pdo->prepare("
        SELECT 
            student_prn,
            COUNT(*) as total_classes,
            AVG(CASE WHEN status = 'present' THEN 1 ELSE 0 END) * 100 as attendance_percentage
        FROM attendance 
        WHERE student_prn IN ($placeholders)
        GROUP BY student_prn
    ")->execute($student_prns)->fetchAll(PDO::FETCH_KEY_PAIR);
    
    // Get marks statistics
    $marks_stats = $pdo->prepare("
        SELECT 
            student_prn,
            AVG(marks_obtained / total_marks * 100) as avg_percentage
        FROM marks 
        WHERE student_prn IN ($placeholders)
        GROUP BY student_prn
    ")->execute($student_prns)->fetchAll(PDO::FETCH_KEY_PAIR);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Students - Teacher</title>
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
            <li><a href="#" class="active"><i class="fas fa-user-graduate"></i> Students</a></li>
            <li><a href="teacher_reports.php"><i class="fas fa-chart-pie"></i> Reports</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="header">
            <h1><?php echo $title; ?></h1>
            <p>View and monitor student progress</p>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>PRN Number</th>
                        <th>Full Name</th>
                        <th>Class & Division</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Attendance %</th>
                        <th>Avg Marks %</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                    <?php
                    $attendance_percentage = isset($attendance_stats[$student['prn_number']]) ? 
                        round($attendance_stats[$student['prn_number']]['attendance_percentage'], 1) : 0;
                    $marks_percentage = isset($marks_stats[$student['prn_number']]) ? 
                        round($marks_stats[$student['prn_number']], 1) : 0;
                    ?>
                    <tr>
                        <td><?php echo $student['prn_number']; ?></td>
                        <td><?php echo $student['full_name']; ?></td>
                        <td><?php echo $student['class'] . ' ' . $student['division']; ?></td>
                        <td><?php echo $student['email']; ?></td>
                        <td><?php echo $student['phone']; ?></td>
                        <td class="<?php
                            if ($attendance_percentage >= 75) echo 'percentage-high';
                            elseif ($attendance_percentage >= 50) echo 'percentage-medium';
                            else echo 'percentage-low';
                        ?>">
                            <?php echo $attendance_percentage; ?>%
                        </td>
                        <td class="<?php
                            if ($marks_percentage >= 75) echo 'percentage-high';
                            elseif ($marks_percentage >= 50) echo 'percentage-medium';
                            else echo 'percentage-low';
                        ?>">
                            <?php echo $marks_percentage; ?>%
                        </td>
                        <td class="status-<?php echo $student['status']; ?>">
                            <?php echo ucfirst($student['status']); ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($students)): ?>
                    <tr>
                        <td colspan="8" style="text-align: center;">No students found</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if (!empty($students)): ?>
        <div class="table-container">
            <h3>Quick Statistics</h3>
            <div class="stats-cards" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <div class="stat-card">
                    <h3><?php echo count($students); ?></h3>
                    <p>Total Students</p>
                </div>
                <div class="stat-card">
                    <h3>
                        <?php
                        $total_attendance = 0;
                        $student_count = 0;
                        foreach ($students as $student) {
                            if (isset($attendance_stats[$student['prn_number']])) {
                                $total_attendance += $attendance_stats[$student['prn_number']]['attendance_percentage'];
                                $student_count++;
                            }
                        }
                        echo $student_count > 0 ? round($total_attendance / $student_count, 1) : 0;
                        ?>%
                    </h3>
                    <p>Avg Attendance</p>
                </div>
                <div class="stat-card">
                    <h3>
                        <?php
                        $total_marks = 0;
                        $student_count = 0;
                        foreach ($students as $student) {
                            if (isset($marks_stats[$student['prn_number']])) {
                                $total_marks += $marks_stats[$student['prn_number']];
                                $student_count++;
                            }
                        }
                        echo $student_count > 0 ? round($total_marks / $student_count, 1) : 0;
                        ?>%
                    </h3>
                    <p>Avg Marks</p>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>