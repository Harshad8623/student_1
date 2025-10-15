<?php
include 'config.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] != 'subject' && $_SESSION['user_type'] != 'class')) {
    header('Location: index.html');
    exit();
}

$teacher_id = $_SESSION['user_id'];
$teacher_type = $_SESSION['user_type'];

// Get teacher's subjects if subject teacher
if ($teacher_type == 'subject') {
    $subjects = $pdo->prepare("SELECT * FROM subjects WHERE teacher_id = ?")->execute([$teacher_id])->fetchAll();
} else {
    // Class teacher can take attendance for all subjects in their class
    $subjects = $pdo->query("SELECT DISTINCT subject_code, subject_name FROM subjects")->fetchAll();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $subject_code = $_POST['subject_code'];
    $date = $_POST['date'];
    $attendance_data = $_POST['attendance'];
    
    try {
        $pdo->beginTransaction();
        
        foreach ($attendance_data as $student_prn => $status) {
            // Check if attendance already exists for this date and subject
            $check_stmt = $pdo->prepare("SELECT id FROM attendance WHERE student_prn = ? AND subject_code = ? AND date = ?");
            $check_stmt->execute([$student_prn, $subject_code, $date]);
            
            if ($check_stmt->rowCount() > 0) {
                // Update existing record
                $update_stmt = $pdo->prepare("UPDATE attendance SET status = ?, recorded_by = ? WHERE student_prn = ? AND subject_code = ? AND date = ?");
                $update_stmt->execute([$status, $teacher_id, $student_prn, $subject_code, $date]);
            } else {
                // Insert new record
                $insert_stmt = $pdo->prepare("INSERT INTO attendance (student_prn, subject_code, date, status, recorded_by) VALUES (?, ?, ?, ?, ?)");
                $insert_stmt->execute([$student_prn, $subject_code, $date, $status, $teacher_id]);
            }
        }
        
        $pdo->commit();
        $success = "Attendance saved successfully!";
        
    } catch(PDOException $e) {
        $pdo->rollBack();
        $error = "Error saving attendance: " . $e->getMessage();
    }
}

// Get students based on teacher type
if ($teacher_type == 'subject') {
    // Get students for the selected subject
    if (isset($_GET['subject']) && isset($_GET['date'])) {
        $subject_code = $_GET['subject'];
        $date = $_GET['date'];
        
        $students = $pdo->prepare("
            SELECT s.*, a.status as attendance_status 
            FROM students s 
            JOIN subjects sub ON s.class = sub.class 
            LEFT JOIN attendance a ON s.prn_number = a.student_prn AND a.subject_code = ? AND a.date = ?
            WHERE sub.subject_code = ? AND s.status = 'approved'
            ORDER BY s.full_name
        ")->execute([$subject_code, $date, $subject_code])->fetchAll();
    }
} else {
    // Class teacher - get all students in their class
    $teacher = $pdo->prepare("SELECT * FROM teachers WHERE teacher_id = ?")->execute([$teacher_id])->fetch();
    
    if (isset($_GET['subject']) && isset($_GET['date'])) {
        $subject_code = $_GET['subject'];
        $date = $_GET['date'];
        
        $students = $pdo->prepare("
            SELECT s.*, a.status as attendance_status 
            FROM students s 
            LEFT JOIN attendance a ON s.prn_number = a.student_prn AND a.subject_code = ? AND a.date = ?
            WHERE s.class = ? AND s.division = ? AND s.status = 'approved'
            ORDER BY s.full_name
        ")->execute([$subject_code, $date, $teacher['class_assigned'], $teacher['division_assigned']])->fetchAll();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Take Attendance - SMS</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Reuse styles from previous pages */
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
        }

        .form-container {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
        }

        .form-group select, .form-group input {
            width: 100%;
            padding: 10px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 1rem;
        }

        .btn {
            padding: 10px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
        }

        .attendance-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .attendance-table th, .attendance-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        .attendance-table th {
            background: #f8f9fa;
            font-weight: 600;
        }

        .attendance-options {
            display: flex;
            gap: 10px;
        }

        .attendance-btn {
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9rem;
        }

        .btn-present { background: #28a745; color: white; }
        .btn-absent { background: #dc3545; color: white; }
        .btn-late { background: #ffc107; color: white; }

        .selected {
            transform: scale(1.05);
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo">
            <h2><i class="fas fa-chalkboard-teacher"></i> Teacher Panel</h2>
        </div>
        <ul class="nav-links">
            <li><a href="teacher_dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="#" class="active"><i class="fas fa-calendar-check"></i> Take Attendance</a></li>
            <li><a href="teacher_marks.php"><i class="fas fa-chart-bar"></i> Upload Marks</a></li>
            <li><a href="teacher_students.php"><i class="fas fa-user-graduate"></i> Students</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="header">
            <h1>Take Attendance</h1>
            <p>Mark student attendance for your subjects</p>
        </div>

        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="form-container">
            <form method="GET" action="">
                <div class="form-group">
                    <label for="subject">Select Subject</label>
                    <select id="subject" name="subject" required>
                        <option value="">Select Subject</option>
                        <?php foreach ($subjects as $subject): ?>
                        <option value="<?php echo $subject['subject_code']; ?>" 
                                <?php if (isset($_GET['subject']) && $_GET['subject'] == $subject['subject_code']) echo 'selected'; ?>>
                            <?php echo $subject['subject_name'] . ' (' . $subject['subject_code'] . ')'; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="date">Date</label>
                    <input type="date" id="date" name="date" value="<?php echo isset($_GET['date']) ? $_GET['date'] : date('Y-m-d'); ?>" required>
                </div>
                <button type="submit" class="btn">Load Students</button>
            </form>
        </div>

        <?php if (isset($students) && !empty($students)): ?>
        <div class="form-container">
            <form method="POST" action="">
                <input type="hidden" name="subject_code" value="<?php echo $_GET['subject']; ?>">
                <input type="hidden" name="date" value="<?php echo $_GET['date']; ?>">
                
                <h3>Mark Attendance for <?php echo $_GET['subject']; ?> on <?php echo date('F d, Y', strtotime($_GET['date'])); ?></h3>
                
                <table class="attendance-table">
                    <thead>
                        <tr>
                            <th>PRN</th>
                            <th>Student Name</th>
                            <th>Class</th>
                            <th>Attendance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?php echo $student['prn_number']; ?></td>
                            <td><?php echo $student['full_name']; ?></td>
                            <td><?php echo $student['class'] . ' ' . $student['division']; ?></td>
                            <td>
                                <div class="attendance-options">
                                    <button type="button" class="attendance-btn btn-present <?php echo ($student['attendance_status'] == 'present') ? 'selected' : ''; ?>" 
                                            data-student="<?php echo $student['prn_number']; ?>" data-status="present">
                                        Present
                                    </button>
                                    <button type="button" class="attendance-btn btn-absent <?php echo ($student['attendance_status'] == 'absent') ? 'selected' : ''; ?>" 
                                            data-student="<?php echo $student['prn_number']; ?>" data-status="absent">
                                        Absent
                                    </button>
                                    <button type="button" class="attendance-btn btn-late <?php echo ($student['attendance_status'] == 'late') ? 'selected' : ''; ?>" 
                                            data-student="<?php echo $student['prn_number']; ?>" data-status="late">
                                        Late
                                    </button>
                                </div>
                                <input type="hidden" name="attendance[<?php echo $student['prn_number']; ?>]" 
                                       id="attendance_<?php echo $student['prn_number']; ?>" 
                                       value="<?php echo $student['attendance_status'] ?: 'present'; ?>">
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <button type="submit" class="btn" style="margin-top: 20px;">
                    <i class="fas fa-save"></i> Save Attendance
                </button>
            </form>
        </div>
        <?php elseif (isset($_GET['subject'])): ?>
        <div class="form-container">
            <p>No students found for the selected criteria.</p>
        </div>
        <?php endif; ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const attendanceButtons = document.querySelectorAll('.attendance-btn');
            
            attendanceButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const student = this.getAttribute('data-student');
                    const status = this.getAttribute('data-status');
                    
                    // Remove selected class from all buttons for this student
                    const studentButtons = document.querySelectorAll(`[data-student="${student}"]`);
                    studentButtons.forEach(btn => btn.classList.remove('selected'));
                    
                    // Add selected class to clicked button
                    this.classList.add('selected');
                    
                    // Update hidden input value
                    document.getElementById(`attendance_${student}`).value = status;
                });
            });
        });
    </script>
</body>
</html>