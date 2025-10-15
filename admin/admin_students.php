<?php
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header('Location: index.html');
    exit();
}

// Handle student actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        $prn = $_POST['prn'];
        
        if ($action == 'approve') {
            $stmt = $pdo->prepare("UPDATE students SET status = 'approved' WHERE prn_number = ?");
            $stmt->execute([$prn]);
        } elseif ($action == 'reject') {
            $stmt = $pdo->prepare("UPDATE students SET status = 'rejected' WHERE prn_number = ?");
            $stmt->execute([$prn]);
        } elseif ($action == 'delete') {
            $stmt = $pdo->prepare("DELETE FROM students WHERE prn_number = ?");
            $stmt->execute([$prn]);
        }
    }
}

// Get all students
$students = $pdo->query("SELECT * FROM students ORDER BY created_at DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students - Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Reuse admin dashboard styles */
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

        .table-container {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
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
        }

        .btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.8rem;
            margin: 2px;
        }

        .btn-approve { background: #28a745; color: white; }
        .btn-reject { background: #dc3545; color: white; }
        .btn-delete { background: #6c757d; color: white; }
        .btn-edit { background: #007bff; color: white; }

        .status-pending { color: #ffc107; font-weight: 600; }
        .status-approved { color: #28a745; font-weight: 600; }
        .status-rejected { color: #dc3545; font-weight: 600; }

        .search-box {
            margin-bottom: 20px;
        }

        .search-box input {
            padding: 10px;
            width: 300px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
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
            <li><a href="#" class="active"><i class="fas fa-user-graduate"></i> Students</a></li>
            <li><a href="admin_teachers.php"><i class="fas fa-chalkboard-teacher"></i> Teachers</a></li>
            <li><a href="admin_subjects.php"><i class="fas fa-book"></i> Subjects</a></li>
            <li><a href="admin_reports.php"><i class="fas fa-chart-pie"></i> Reports</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="header">
            <h1>Manage Students</h1>
            <p>Approve, reject, or manage student accounts</p>
        </div>

        <div class="search-box">
            <input type="text" id="searchInput" placeholder="Search students...">
        </div>

        <div class="table-container">
            <table id="studentsTable">
                <thead>
                    <tr>
                        <th>PRN Number</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Class & Division</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Registration Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                    <tr>
                        <td><?php echo $student['prn_number']; ?></td>
                        <td><?php echo $student['full_name']; ?></td>
                        <td><?php echo $student['email']; ?></td>
                        <td><?php echo $student['class'] . ' ' . $student['division']; ?></td>
                        <td><?php echo $student['phone']; ?></td>
                        <td class="status-<?php echo $student['status']; ?>">
                            <?php echo ucfirst($student['status']); ?>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($student['created_at'])); ?></td>
                        <td>
                            <?php if ($student['status'] == 'pending'): ?>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="approve">
                                <input type="hidden" name="prn" value="<?php echo $student['prn_number']; ?>">
                                <button type="submit" class="btn btn-approve">Approve</button>
                            </form>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="reject">
                                <input type="hidden" name="prn" value="<?php echo $student['prn_number']; ?>">
                                <button type="submit" class="btn btn-reject">Reject</button>
                            </form>
                            <?php endif; ?>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="prn" value="<?php echo $student['prn_number']; ?>">
                                <button type="submit" class="btn btn-delete" onclick="return confirm('Are you sure?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function() {
            const searchText = this.value.toLowerCase();
            const rows = document.querySelectorAll('#studentsTable tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchText) ? '' : 'none';
            });
        });
    </script>
</body>
</html>