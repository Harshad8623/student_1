<?php
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'student') {
    header('Location: index.html');
    exit();
}

$student_prn = $_SESSION['user_id'];

// Get student data
$stmt = $pdo->prepare("SELECT * FROM students WHERE prn_number = ?");
$stmt->execute([$student_prn]);
$student = $stmt->fetch();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $parent_name = $_POST['parent_name'];
    $parent_phone = $_POST['parent_phone'];
    
    $update_stmt = $pdo->prepare("UPDATE students SET phone = ?, address = ?, parent_name = ?, parent_phone = ? WHERE prn_number = ?");
    $update_stmt->execute([$phone, $address, $parent_name, $parent_phone, $student_prn]);
    
    $success = "Profile updated successfully!";
    
    // Refresh student data
    $stmt->execute([$student_prn]);
    $student = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Student</title>
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

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
        }

        .form-group input, .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 1rem;
        }

        .form-group input:read-only {
            background: #f8f9fa;
            color: #6c757d;
        }

        .btn {
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
        }

        .info-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo">
            <h2><i class="fas fa-graduation-cap"></i> SMS</h2>
        </div>
        <ul class="nav-links">
            <li><a href="student_dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="student_attendance.php"><i class="fas fa-calendar-check"></i> Attendance</a></li>
            <li><a href="student_marks.php"><i class="fas fa-chart-bar"></i> Marks & Grades</a></li>
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
                    <?php echo strtoupper(substr($student['full_name'], 0, 1)); ?>
                </div>
                <h2><?php echo $student['full_name']; ?></h2>
                <p>PRN: <?php echo $student['prn_number']; ?> | Status: 
                    <span style="color: <?php echo $student['status'] == 'approved' ? '#28a745' : '#ffc107'; ?>">
                        <?php echo ucfirst($student['status']); ?>
                    </span>
                </p>
            </div>

            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label>PRN Number</label>
                        <input type="text" value="<?php echo $student['prn_number']; ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" value="<?php echo $student['full_name']; ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" value="<?php echo $student['email']; ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="tel" name="phone" value="<?php echo $student['phone']; ?>">
                    </div>
                    <div class="form-group">
                        <label>Class</label>
                        <input type="text" value="<?php echo $student['class']; ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>Division</label>
                        <input type="text" value="<?php echo $student['division']; ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>Date of Birth</label>
                        <input type="text" value="<?php echo $student['date_of_birth'] ? date('M d, Y', strtotime($student['date_of_birth'])) : 'Not set'; ?>" readonly>
                    </div>
                    <div class="form-group full-width">
                        <label>Address</label>
                        <textarea name="address" rows="3"><?php echo $student['address']; ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Parent/Guardian Name</label>
                        <input type="text" name="parent_name" value="<?php echo $student['parent_name']; ?>">
                    </div>
                    <div class="form-group">
                        <label>Parent/Guardian Phone</label>
                        <input type="tel" name="parent_phone" value="<?php echo $student['parent_phone']; ?>">
                    </div>
                </div>
                
                <button type="submit" class="btn">
                    <i class="fas fa-save"></i> Update Profile
                </button>
            </form>
        </div>
    </div>
</body>
</html>