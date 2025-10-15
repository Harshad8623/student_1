<?php
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header('Location: index.html');
    exit();
}

// Handle subject actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_subject'])) {
        $subject_code = $_POST['subject_code'];
        $subject_name = $_POST['subject_name'];
        $teacher_id = $_POST['teacher_id'];
        $class = $_POST['class'];
        
        $stmt = $pdo->prepare("INSERT INTO subjects (subject_code, subject_name, teacher_id, class) VALUES (?, ?, ?, ?)");
        $stmt->execute([$subject_code, $subject_name, $teacher_id, $class]);
        
        $success = "Subject added successfully!";
    } elseif (isset($_POST['delete_subject'])) {
        $subject_code = $_POST['subject_code'];
        $stmt = $pdo->prepare("DELETE FROM subjects WHERE subject_code = ?");
        $stmt->execute([$subject_code]);
        $success = "Subject deleted successfully!";
    }
}

// Get all subjects with teacher names
$subjects = $pdo->query("
    SELECT s.*, t.full_name as teacher_name 
    FROM subjects s 
    LEFT JOIN teachers t ON s.teacher_id = t.teacher_id 
    ORDER BY s.subject_code
")->fetchAll();

// Get all teachers for dropdown
$teachers = $pdo->query("SELECT teacher_id, full_name FROM teachers WHERE status = 'active'")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Subjects - Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 30px;
            border-radius: 10px;
            width: 500px;
            max-width: 90%;
        }

        .btn-add {
            background: #28a745;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo">
            <h2><i class="fas fa-user-shield"></i> Admin Panel</h2>
        </div>
        <ul class="nav-links">
            <li><a href="admin_dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="admin_students.php"><i class="fas fa-user-graduate"></i> Students</a></li>
            <li><a href="admin_teachers.php"><i class="fas fa-chalkboard-teacher"></i> Teachers</a></li>
            <li><a href="#" class="active"><i class="fas fa-book"></i> Subjects</a></li>
            <li><a href="admin_reports.php"><i class="fas fa-chart-pie"></i> Reports</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="header">
            <h1>Manage Subjects</h1>
            <p>Add and manage subjects and assign teachers</p>
        </div>

        <button class="btn-add" onclick="openModal()">
            <i class="fas fa-plus"></i> Add New Subject
        </button>

        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Subject Code</th>
                        <th>Subject Name</th>
                        <th>Assigned Teacher</th>
                        <th>Class</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($subjects as $subject): ?>
                    <tr>
                        <td><?php echo $subject['subject_code']; ?></td>
                        <td><?php echo $subject['subject_name']; ?></td>
                        <td><?php echo $subject['teacher_name'] ?: 'Not assigned'; ?></td>
                        <td><?php echo $subject['class'] ?: 'All'; ?></td>
                        <td>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="subject_code" value="<?php echo $subject['subject_code']; ?>">
                                <button type="submit" name="delete_subject" class="btn btn-danger" 
                                        onclick="return confirm('Are you sure you want to delete this subject?')">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Subject Modal -->
    <div id="addSubjectModal" class="modal">
        <div class="modal-content">
            <h2>Add New Subject</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Subject Code</label>
                    <input type="text" name="subject_code" required>
                </div>
                <div class="form-group">
                    <label>Subject Name</label>
                    <input type="text" name="subject_name" required>
                </div>
                <div class="form-group">
                    <label>Assign Teacher</label>
                    <select name="teacher_id">
                        <option value="">Select Teacher</option>
                        <?php foreach ($teachers as $teacher): ?>
                        <option value="<?php echo $teacher['teacher_id']; ?>">
                            <?php echo $teacher['full_name'] . ' (' . $teacher['teacher_id'] . ')'; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Class (Optional)</label>
                    <select name="class">
                        <option value="">All Classes</option>
                        <option value="FY">First Year</option>
                        <option value="SY">Second Year</option>
                        <option value="TY">Third Year</option>
                        <option value="BE">Final Year</option>
                    </select>
                </div>
                <button type="submit" name="add_subject" class="btn-add">Add Subject</button>
                <button type="button" onclick="closeModal()" style="background: #6c757d; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">Cancel</button>
            </form>
        </div>
    </div>

    <script>
        function openModal() {
            document.getElementById('addSubjectModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('addSubjectModal').style.display = 'none';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('addSubjectModal');
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>