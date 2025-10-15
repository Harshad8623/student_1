<?php
include 'config.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] != 'subject' && $_SESSION['user_type'] != 'class')) {
    header('Location: index.html');
    exit();
}

$teacher_id = $_SESSION['user_id'];

// Get teacher's subjects
$subjects = $pdo->prepare("SELECT * FROM subjects WHERE teacher_id = ?")->execute([$teacher_id])->fetchAll();

// Handle marks upload
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $subject_code = $_POST['subject_code'];
    $exam_type = $_POST['exam_type'];
    $marks_data = $_POST['marks'];
    
    try {
        $pdo->beginTransaction();
        
        foreach ($marks_data as $student_prn => $mark_data) {
            if (!empty($mark_data['obtained']) && !empty($mark_data['total'])) {
                $insert_stmt = $pdo->prepare("INSERT INTO marks (student_prn, subject_code, exam_type, marks_obtained, total_marks, uploaded_by) VALUES (?, ?, ?, ?, ?, ?)");
                $insert_stmt->execute([$student_prn, $subject_code, $exam_type, $mark_data['obtained'], $mark_data['total'], $teacher_id]);
            }
        }
        
        $pdo->commit();
        $success = "Marks uploaded successfully!";
        
    } catch(PDOException $e) {
        $pdo->rollBack();
        $error = "Error uploading marks: " . $e->getMessage();
    }
}

// Get students for selected subject
if (isset($_GET['subject'])) {
    $subject_code = $_GET['subject'];
    $students = $pdo->prepare("
        SELECT s.* 
        FROM students s 
        JOIN subjects sub ON s.class = sub.class 
        WHERE sub.subject_code = ? AND s.status = 'approved'
        ORDER BY s.full_name
    ")->execute([$subject_code])->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Marks - SMS</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Reuse styles from teacher pages */
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

        .marks-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .marks-table th, .marks-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        .marks-table th {
            background: #f8f9fa;
            font-weight: 600;
        }

        .marks-input {
            width: 80px;
            padding: 5px;
            border: 1px solid #ddd;
            border-radius: 3px;
            text-align: center;
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
            <li><a href="teacher_attendance.php"><i class="fas fa-calendar-check"></i> Take Attendance</a></li>
            <li><a href="#" class="active"><i class="fas fa-chart-bar"></i> Upload Marks</a></li>
            <li><a href="teacher_students.php"><i class="fas fa-user-graduate"></i> Students</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="header">
            <h1>Upload Marks</h1>
            <p>Enter student marks for exams and assignments</p>
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
                <button type="submit" class="btn">Load Students</button>
            </form>
        </div>

        <?php if (isset($students) && !empty($students)): ?>
        <div class="form-container">
            <form method="POST" action="">
                <input type="hidden" name="subject_code" value="<?php echo $_GET['subject']; ?>">
                
                <div class="form-group">
                    <label for="exam_type">Exam Type</label>
                    <select id="exam_type" name="exam_type" required>
                        <option value="">Select Exam Type</option>
                        <option value="Unit Test 1">Unit Test 1</option>
                        <option value="Unit Test 2">Unit Test 2</option>
                        <option value="Mid Term">Mid Term</option>
                        <option value="Final Exam">Final Exam</option>
                        <option value="Assignment">Assignment</option>
                        <option value="Practical">Practical</option>
                    </select>
                </div>
                
                <h3>Enter Marks for <?php echo $_GET['subject']; ?></h3>
                
                <table class="marks-table">
                    <thead>
                        <tr>
                            <th>PRN</th>
                            <th>Student Name</th>
                            <th>Class</th>
                            <th>Marks Obtained</th>
                            <th>Total Marks</th>
                            <th>Percentage</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?php echo $student['prn_number']; ?></td>
                            <td><?php echo $student['full_name']; ?></td>
                            <td><?php echo $student['class'] . ' ' . $student['division']; ?></td>
                            <td>
                                <input type="number" step="0.01" class="marks-input obtained-marks" 
                                       name="marks[<?php echo $student['prn_number']; ?>][obtained]" 
                                       data-student="<?php echo $student['prn_number']; ?>">
                            </td>
                            <td>
                                <input type="number" step="0.01" class="marks-input total-marks" 
                                       name="marks[<?php echo $student['prn_number']; ?>][total]" 
                                       data-student="<?php echo $student['prn_number']; ?>">
                            </td>
                            <td class="percentage-cell" id="percentage_<?php echo $student['prn_number']; ?>">
                                -
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <button type="submit" class="btn" style="margin-top: 20px;">
                    <i class="fas fa-upload"></i> Upload Marks
                </button>
            </form>
        </div>
        <?php elseif (isset($_GET['subject'])): ?>
        <div class="form-container">
            <p>No students found for the selected subject.</p>
        </div>
        <?php endif; ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const obtainedInputs = document.querySelectorAll('.obtained-marks');
            const totalInputs = document.querySelectorAll('.total-marks');
            
            function calculatePercentage(obtained, total) {
                if (obtained && total && total > 0) {
                    return ((obtained / total) * 100).toFixed(2);
                }
                return null;
            }
            
            function updatePercentage(studentId, obtained, total) {
                const percentage = calculatePercentage(obtained, total);
                const percentageCell = document.getElementById(`percentage_${studentId}`);
                
                if (percentage !== null) {
                    percentageCell.textContent = percentage + '%';
                    
                    // Color code based on percentage
                    if (percentage >= 75) {
                        percentageCell.style.color = '#28a745';
                    } else if (percentage >= 50) {
                        percentageCell.style.color = '#ffc107';
                    } else {
                        percentageCell.style.color = '#dc3545';
                    }
                } else {
                    percentageCell.textContent = '-';
                    percentageCell.style.color = '#333';
                }
            }
            
            obtainedInputs.forEach(input => {
                input.addEventListener('input', function() {
                    const studentId = this.getAttribute('data-student');
                    const obtained = parseFloat(this.value);
                    const totalInput = document.querySelector(`.total-marks[data-student="${studentId}"]`);
                    const total = parseFloat(totalInput.value);
                    
                    updatePercentage(studentId, obtained, total);
                });
            });
            
            totalInputs.forEach(input => {
                input.addEventListener('input', function() {
                    const studentId = this.getAttribute('data-student');
                    const total = parseFloat(this.value);
                    const obtainedInput = document.querySelector(`.obtained-marks[data-student="${studentId}"]`);
                    const obtained = parseFloat(obtainedInput.value);
                    
                    updatePercentage(studentId, obtained, total);
                });
            });
        });
    </script>
</body>
</html>