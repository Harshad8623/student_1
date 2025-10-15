<?php
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header('Location: index.html');
    exit();
}

// Handle teacher actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_teacher'])) {
        $teacher_id = $_POST['teacher_id'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $full_name = $_POST['full_name'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $subject = $_POST['subject'];
        $type = $_POST['type'];
        $class_assigned = $_POST['class_assigned'];
        $division_assigned = $_POST['division_assigned'];
        
        $stmt = $pdo->prepare("INSERT INTO teachers (teacher_id, password, full_name, email, phone, subject, type, class_assigned, division_assigned) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$teacher_id, $password, $full_name, $email, $phone, $subject, $type, $class_assigned, $division_assigned]);
        
        $success = "Teacher added successfully!";
    }
}

// Get all teachers
$teachers = $pdo->query("SELECT * FROM teachers ORDER BY created_at DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Teachers - Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Reuse admin styles */
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

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .form-group input, .form-group select {
            width: 100%;
            padding: 8px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
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
        <!-- Same sidebar as admin_students.php -->
        <div class="logo">
            <h2><i class="fas fa-user-shield"></i> Admin Panel</h2>
        </div>
        <ul class="nav-links">
            <li><a href="admin_dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="admin_students.php"><i class="fas fa-user-graduate"></i> Students</a></li>
            <li><a href="#" class="active"><i class="fas fa-chalkboard-teacher"></i> Teachers</a></li>
            <li><a href="admin_subjects.php"><i class="fas fa-book"></i> Subjects</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="header">
            <h1>Manage Teachers</h1>
            <p>Add and manage teacher accounts</p>
        </div>

        <button class="btn-add" onclick="openModal()">
            <i class="fas fa-plus"></i> Add New Teacher
        </button>

        <?php if (isset($success)): ?>
            <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Teacher ID</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Subject</th>
                        <th>Type</th>
                        <th>Class Assigned</th>
                        <th>Status</th>
                        <th>Join Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($teachers as $teacher): ?>
                    <tr>
                        <td><?php echo $teacher['teacher_id']; ?></td>
                        <td><?php echo $teacher['full_name']; ?></td>
                        <td><?php echo $teacher['email']; ?></td>
                        <td><?php echo $teacher['subject']; ?></td>
                        <td><?php echo ucfirst($teacher['type']); ?> Teacher</td>
                        <td><?php echo $teacher['class_assigned'] . ' ' . $teacher['division_assigned']; ?></td>
                        <td class="status-<?php echo $teacher['status']; ?>">
                            <?php echo ucfirst($teacher['status']); ?>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($teacher['created_at'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Teacher Modal -->
    <div id="addTeacherModal" class="modal">
        <div class="modal-content">
            <h2>Add New Teacher</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Teacher ID</label>
                    <input type="text" name="teacher_id" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="tel" name="phone">
                </div>
                <div class="form-group">
                    <label>Subject</label>
                    <input type="text" name="subject">
                </div>
                <div class="form-group">
                    <label>Type</label>
                    <select name="type" required>
                        <option value="subject">Subject Teacher</option>
                        <option value="class">Class Teacher</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Class Assigned</label>
                    <select name="class_assigned">
                        <option value="">Select Class</option>
                        <option value="FY">First Year</option>
                        <option value="SY">Second Year</option>
                        <option value="TY">Third Year</option>
                        <option value="BE">Final Year</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Division Assigned</label>
                    <select name="division_assigned">
                        <option value="">Select Division</option>
                        <option value="A">A</option>
                        <option value="B">B</option>
                        <option value="C">C</option>
                    </select>
                </div>
                <button type="submit" name="add_teacher" class="btn-add">Add Teacher</button>
                <button type="button" onclick="closeModal()" style="background: #6c757d;">Cancel</button>
            </form>
        </div>
    </div>

    <script>
        function openModal() {
            document.getElementById('addTeacherModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('addTeacherModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('addTeacherModal');
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>