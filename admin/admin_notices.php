<?php
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header('Location: index.html');
    exit();
}

// Handle notice actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_notice'])) {
        $title = $_POST['title'];
        $content = $_POST['content'];
        $target_audience = $_POST['target_audience'];
        $class = $_POST['class'];
        $division = $_POST['division'];
        $posted_by = $_SESSION['user_id'];
        
        $stmt = $pdo->prepare("INSERT INTO notices (title, content, posted_by, target_audience, class, division) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $content, $posted_by, $target_audience, $class, $division]);
        
        $success = "Notice published successfully!";
    } elseif (isset($_POST['delete_notice'])) {
        $notice_id = $_POST['notice_id'];
        $stmt = $pdo->prepare("DELETE FROM notices WHERE id = ?");
        $stmt->execute([$notice_id]);
        $success = "Notice deleted successfully!";
    }
}

// Get all notices
$notices = $pdo->query("SELECT * FROM notices ORDER BY created_at DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Notices - Admin</title>
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
            width: 600px;
            max-width: 90%;
            max-height: 80vh;
            overflow-y: auto;
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

        .notice-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            overflow: hidden;
        }

        .notice-header {
            background: #f8f9fa;
            padding: 15px 20px;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .notice-body {
            padding: 20px;
        }

        .notice-meta {
            background: #f8f9fa;
            padding: 10px 20px;
            border-top: 1px solid #e0e0e0;
            font-size: 0.9rem;
            color: #666;
        }

        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .badge-all { background: #dc3545; color: white; }
        .badge-students { background: #007bff; color: white; }
        .badge-teachers { background: #6c757d; color: white; }
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
            <li><a href="admin_subjects.php"><i class="fas fa-book"></i> Subjects</a></li>
            <li><a href="admin_reports.php"><i class="fas fa-chart-pie"></i> Reports</a></li>
            <li><a href="#" class="active"><i class="fas fa-bullhorn"></i> Notices</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="header">
            <h1>Manage Notices</h1>
            <p>Create and manage announcements and notices</p>
        </div>

        <button class="btn-add" onclick="openModal()">
            <i class="fas fa-plus"></i> Create New Notice
        </button>

        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <div class="notices-container">
            <?php foreach ($notices as $notice): ?>
            <div class="notice-card">
                <div class="notice-header">
                    <h3 style="margin: 0; flex: 1;"><?php echo $notice['title']; ?></h3>
                    <div>
                        <span class="badge badge-<?php echo $notice['target_audience']; ?>">
                            <?php echo ucfirst($notice['target_audience']); ?>
                        </span>
                        <form method="POST" style="display: inline; margin-left: 10px;">
                            <input type="hidden" name="notice_id" value="<?php echo $notice['id']; ?>">
                            <button type="submit" name="delete_notice" class="btn btn-danger" 
                                    onclick="return confirm('Are you sure you want to delete this notice?')">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
                <div class="notice-body">
                    <p style="white-space: pre-line; line-height: 1.6;"><?php echo $notice['content']; ?></p>
                </div>
                <div class="notice-meta">
                    <div style="display: flex; justify-content: space-between;">
                        <span>
                            <i class="fas fa-user"></i> Posted by: <?php echo $notice['posted_by']; ?>
                        </span>
                        <span>
                            <i class="fas fa-clock"></i> <?php echo date('F j, Y g:i A', strtotime($notice['created_at'])); ?>
                        </span>
                    </div>
                    <?php if ($notice['class']): ?>
                    <div style="margin-top: 5px;">
                        <i class="fas fa-users"></i> Target: <?php echo $notice['class'] . ($notice['division'] ? ' ' . $notice['division'] : ''); ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Add Notice Modal -->
    <div id="addNoticeModal" class="modal">
        <div class="modal-content">
            <h2>Create New Notice</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Notice Title</label>
                    <input type="text" name="title" required>
                </div>
                <div class="form-group">
                    <label>Content</label>
                    <textarea name="content" rows="6" required></textarea>
                </div>
                <div class="form-group">
                    <label>Target Audience</label>
                    <select name="target_audience" required onchange="toggleClassFields()">
                        <option value="all">Everyone</option>
                        <option value="students">Students Only</option>
                        <option value="teachers">Teachers Only</option>
                    </select>
                </div>
                <div id="classFields" style="display: none;">
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
                    <div class="form-group">
                        <label>Division (Optional)</label>
                        <select name="division">
                            <option value="">All Divisions</option>
                            <option value="A">A</option>
                            <option value="B">B</option>
                            <option value="C">C</option>
                        </select>
                    </div>
                </div>
                <button type="submit" name="add_notice" class="btn-add">Publish Notice</button>
                <button type="button" onclick="closeModal()" style="background: #6c757d; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">Cancel</button>
            </form>
        </div>
    </div>

    <script>
        function openModal() {
            document.getElementById('addNoticeModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('addNoticeModal').style.display = 'none';
        }

        function toggleClassFields() {
            const target = document.querySelector('select[name="target_audience"]').value;
            const classFields = document.getElementById('classFields');
            classFields.style.display = (target === 'students') ? 'block' : 'none';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('addNoticeModal');
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>