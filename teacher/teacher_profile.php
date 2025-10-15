<?php
include 'config.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] != 'subject' && $_SESSION['user_type'] != 'class')) {
    header('Location: index.html');
    exit();
}

$teacher_id = $_SESSION['user_id'];

// Get teacher data
$stmt = $pdo->prepare("SELECT * FROM teachers WHERE teacher_id = ?");
$stmt->execute([$teacher_id]);
$teacher = $stmt->fetch();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    
    $update_stmt = $pdo->prepare("UPDATE teachers SET phone = ?, email = ? WHERE teacher_id = ?");
    $update_stmt->execute([$phone, $email, $teacher_id]);
    
    $success = "Profile updated successfully!";
    
    // Refresh teacher data
    $stmt->execute([$teacher_id]);
    $teacher = $stmt->fetch();
    
    // Update session
    $_SESSION['full_name'] = $teacher['full_name'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Teacher</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .profile-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            max-width: 800px;
            margin: 0 auto;
        }

        .profile-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .avatar {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
            margin: 0 auto 15px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .info-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }

        .stat-item {
            text-align: center;
            padding: 15px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
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
            <li><a href="teacher_marks.php"><i class="fas fa-chart-bar"></i> Upload Marks</a></li>
            <li><a href="teacher_students.php"><i class="fas fa-user-graduate"></i> Students</a></li>
            <li><a href="teacher_reports.php"><i class="fas fa-chart-pie"></i> Reports</a></li>
            <li><a href="#" class="active"><i class="fas fa-user"></i> Profile</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="header">
            <h1>My Profile</h1>
            <p>Manage your personal information</p>
        </div>

        <?php if (isset($success)): ?>
            <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <div class="profile-container">
            <div class="profile-header">
                <div class="avatar">
                    <?php echo strtoupper(substr($teacher['full_name'], 0, 1)); ?>
                </div>
                <h2><?php echo $teacher['full_name']; ?></h2>
                <p>Teacher ID: <?php echo $teacher['teacher_id']; ?> | 
                   Type: <?php echo ucfirst($teacher['type']); ?> Teacher</p>
            </div>

            <div class="info-card">
                <h3>Teaching Information</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label><strong>Subject:</strong></label>
                        <p><?php echo $teacher['subject'] ?: 'Not assigned'; ?></p>
                    </div>
                    <div class="form-group">
                        <label><strong>Class Assigned:</strong></label>
                        <p><?php echo $teacher['class_assigned'] ?: 'Not assigned'; ?></p>
                    </div>
                    <div class="form-group">
                        <label><strong>Division Assigned:</strong></label>
                        <p><?php echo $teacher['division_assigned'] ?: 'Not assigned'; ?></p>
                    </div>
                    <div class="form-group">
                        <label><strong>Status:</strong></label>
                        <p class="status-<?php echo $teacher['status']; ?>">
                            <?php echo ucfirst($teacher['status']); ?>
                        </p>
                    </div>
                </div>
            </div>

            <form method="POST">
                <h3>Personal Information</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Teacher ID</label>
                        <input type="text" value="<?php echo $teacher['teacher_id']; ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" value="<?php echo $teacher['full_name']; ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" value="<?php echo $teacher['email']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="tel" name="phone" value="<?php echo $teacher['phone']; ?>">
                    </div>
                    <div class="form-group">
                        <label>Teacher Type</label>
                        <input type="text" value="<?php echo ucfirst($teacher['type']); ?> Teacher" readonly>
                    </div>
                    <div class="form-group">
                        <label>Join Date</label>
                        <input type="text" value="<?php echo date('M d, Y', strtotime($teacher['created_at'])); ?>" readonly>
                    </div>
                </div>
                
                <button type="submit" class="btn">
                    <i class="fas fa-save"></i> Update Profile
                </button>
            </form>

            <!-- Teacher Statistics -->
            <?php
            // Get teacher statistics
            $attendance_count = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE recorded_by = ?")
                ->execute([$teacher_id])->fetchColumn();
            $marks_count = $pdo->prepare("SELECT COUNT(*) FROM marks WHERE uploaded_by = ?")
                ->execute([$teacher_id])->fetchColumn();
            $subjects_count = $pdo->prepare("SELECT COUNT(*) FROM subjects WHERE teacher_id = ?")
                ->execute([$teacher_id])->fetchColumn();
            ?>

            <div class="info-card">
                <h3>Teaching Statistics</h3>
                <div class="stats-grid">
                    <div class="stat-item">
                        <h3><?php echo $attendance_count; ?></h3>
                        <p>Attendance Records</p>
                    </div>
                    <div class="stat-item">
                        <h3><?php echo $marks_count; ?></h3>
                        <p>Marks Uploaded</p>
                    </div>
                    <div class="stat-item">
                        <h3><?php echo $subjects_count; ?></h3>
                        <p>Subjects</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>