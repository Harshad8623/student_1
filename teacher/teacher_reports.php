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

// Get subjects taught by teacher
$subjects = $pdo->prepare("SELECT * FROM subjects WHERE teacher_id = ?")->execute([$teacher_id])->fetchAll();

// Generate report based on filters
$report_data = [];
if (isset($_GET['generate_report'])) {
    $subject_code = $_GET['subject_code'];
    $report_type = $_GET['report_type'];
    $date_from = $_GET['date_from'];
    $date_to = $_GET['date_to'];
    
    if ($report_type == 'attendance') {
        $report_data = $pdo->prepare("
            SELECT 
                s.prn_number,
                s.full_name,
                s.class,
                s.division,
                COUNT(a.id) as total_classes,
                SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present,
                SUM(CASE WHEN a.status = 'late' THEN 1 ELSE 0 END) as late,
                SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as absent,
                (SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) + 
                 SUM(CASE WHEN a.status = 'late' THEN 0.5 ELSE 0 END)) / COUNT(a.id) * 100 as percentage
            FROM students s
            JOIN attendance a ON s.prn_number = a.student_prn
            WHERE a.subject_code = ? AND a.recorded_by = ?
            AND a.date BETWEEN ? AND ?
            GROUP BY s.prn_number, s.full_name, s.class, s.division
            ORDER BY percentage DESC
        ")->execute([$subject_code, $teacher_id, $date_from, $date_to])->fetchAll();
    } elseif ($report_type == 'performance') {
        $report_data = $pdo->prepare("
            SELECT 
                s.prn_number,
                s.full_name,
                s.class,
                s.division,
                COUNT(m.id) as exam_count,
                AVG(m.marks_obtained / m.total_marks * 100) as avg_percentage,
                MAX(m.marks_obtained / m.total_marks * 100) as max_percentage,
                MIN(m.marks_obtained / m.total_marks * 100) as min_percentage
            FROM students s
            JOIN marks m ON s.prn_number = m.student_prn
            WHERE m.subject_code = ? AND m.uploaded_by = ?
            AND m.uploaded_at BETWEEN ? AND ?
            GROUP BY s.prn_number, s.full_name, s.class, s.division
            ORDER BY avg_percentage DESC
        ")->execute([$subject_code, $teacher_id, $date_from, $date_to])->fetchAll();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Teacher</title>
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
            <li><a href="#" class="active"><i class="fas fa-chart-pie"></i> Reports</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="header">
            <h1>Generate Reports</h1>
            <p>Create detailed reports for your subjects</p>
        </div>

        <div class="report-filters">
            <h3>Report Parameters</h3>
            <form method="GET">
                <div class="filter-group">
                    <div class="form-group">
                        <label>Report Type</label>
                        <select name="report_type" required>
                            <option value="attendance">Attendance Report</option>
                            <option value="performance">Performance Report</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Subject</label>
                        <select name="subject_code" required>
                            <option value="">Select Subject</option>
                            <?php foreach ($subjects as $subject): ?>
                            <option value="<?php echo $subject['subject_code']; ?>">
                                <?php echo $subject['subject_name']; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Date From</label>
                        <input type="date" name="date_from" value="<?php echo date('Y-m-01'); ?>">
                    </div>
                    <div class="form-group">
                        <label>Date To</label>
                        <input type="date" name="date_to" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="form-group">
                        <button type="submit" name="generate_report" class="btn btn-primary">
                            <i class="fas fa-chart-bar"></i> Generate Report
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <?php if (isset($_GET['generate_report']) && !empty($report_data)): ?>
        <div class="table-container">
            <h3>
                <?php echo ucfirst($_GET['report_type']); ?> Report - 
                <?php 
                $subject_name = $pdo->prepare("SELECT subject_name FROM subjects WHERE subject_code = ?")
                    ->execute([$_GET['subject_code']])->fetchColumn();
                echo $subject_name; 
                ?>
            </h3>
            
            <?php if ($_GET['report_type'] == 'attendance'): ?>
            <table>
                <thead>
                    <tr>
                        <th>PRN</th>
                        <th>Student Name</th>
                        <th>Class</th>
                        <th>Total Classes</th>
                        <th>Present</th>
                        <th>Late</th>
                        <th>Absent</th>
                        <th>Attendance %</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($report_data as $row): ?>
                    <tr>
                        <td><?php echo $row['prn_number']; ?></td>
                        <td><?php echo $row['full_name']; ?></td>
                        <td><?php echo $row['class'] . ' ' . $row['division']; ?></td>
                        <td><?php echo $row['total_classes']; ?></td>
                        <td><?php echo $row['present']; ?></td>
                        <td><?php echo $row['late']; ?></td>
                        <td><?php echo $row['absent']; ?></td>
                        <td class="<?php
                            if ($row['percentage'] >= 75) echo 'percentage-high';
                            elseif ($row['percentage'] >= 50) echo 'percentage-medium';
                            else echo 'percentage-low';
                        ?>">
                            <?php echo round($row['percentage'], 1); ?>%
                        </td>
                        <td>
                            <?php
                            if ($row['percentage'] >= 75) echo 'Good';
                            elseif ($row['percentage'] >= 50) echo 'Average';
                            else echo 'Poor';
                            ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <?php elseif ($_GET['report_type'] == 'performance'): ?>
            <table>
                <thead>
                    <tr>
                        <th>PRN</th>
                        <th>Student Name</th>
                        <th>Class</th>
                        <th>Exams Taken</th>
                        <th>Average %</th>
                        <th>Highest %</th>
                        <th>Lowest %</th>
                        <th>Grade</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($report_data as $row): ?>
                    <?php
                    $grade = '';
                    if ($row['avg_percentage'] >= 80) $grade = 'A';
                    elseif ($row['avg_percentage'] >= 60) $grade = 'B';
                    else $grade = 'C';
                    ?>
                    <tr>
                        <td><?php echo $row['prn_number']; ?></td>
                        <td><?php echo $row['full_name']; ?></td>
                        <td><?php echo $row['class'] . ' ' . $row['division']; ?></td>
                        <td><?php echo $row['exam_count']; ?></td>
                        <td class="<?php
                            if ($row['avg_percentage'] >= 75) echo 'percentage-high';
                            elseif ($row['avg_percentage'] >= 50) echo 'percentage-medium';
                            else echo 'percentage-low';
                        ?>">
                            <?php echo round($row['avg_percentage'], 1); ?>%
                        </td>
                        <td><?php echo round($row['max_percentage'], 1); ?>%</td>
                        <td><?php echo round($row['min_percentage'], 1); ?>%</td>
                        <td><strong><?php echo $grade; ?></strong></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
            
            <div style="margin-top: 20px;">
                <button onclick="window.print()" class="btn btn-primary">
                    <i class="fas fa-print"></i> Print Report
                </button>
                <button onclick="exportToExcel()" class="btn btn-success">
                    <i class="fas fa-file-excel"></i> Export to Excel
                </button>
            </div>
        </div>
        <?php elseif (isset($_GET['generate_report'])): ?>
        <div class="alert alert-warning">
            No data found for the selected criteria.
        </div>
        <?php endif; ?>
    </div>

    <script>
        function exportToExcel() {
            // Simple Excel export (in real implementation, use a library)
            const table = document.querySelector('table');
            const html = table.outerHTML;
            const url = 'data:application/vnd.ms-excel;charset=utf-8,' + encodeURIComponent(html);
            const link = document.createElement('a');
            link.href = url;
            link.download = 'report.xls';
            link.click();
        }
    </script>
</body>
</html>