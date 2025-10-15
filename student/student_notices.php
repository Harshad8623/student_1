<?php
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'student') {
    header('Location: index.html');
    exit();
}

$student_prn = $_SESSION['user_id'];

// Get student class
$student = $pdo->prepare("SELECT class, division FROM students WHERE prn_number = ?")->execute([$student_prn])->fetch();

// Get notices for student
$notices = $pdo->prepare("
    SELECT * FROM notices 
    WHERE (target_audience = 'all' OR target_audience = 'students')
    AND (class IS NULL OR class = ?)
    ORDER BY created_at DESC
")->execute([$student['class']])->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notices - Student</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .notice-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            overflow: hidden;
        }

        .notice-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
        }

        .notice-body {
            padding: 20px;
        }

        .notice-meta {
            background: #f8f9fa;
            padding: 15px 20px;
            border-top: 1px solid #e0e0e0;
            font-size: 0.9rem;
            color: #666;
        }

        .notice-important {
            border-left: 4px solid #dc3545;
        }

        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .badge-important {
            background: #dc3545;
            color: white;
        }

        .badge-general {
            background: #6c757d;
            color: white;
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
            <li><a href="#" class="active"><i class="fas fa-bullhorn"></i> Notices</a></li>
            <li><a href="student_profile.php"><i class="fas fa-user"></i> Profile</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="header">
            <h1>Notices & Announcements</h1>
            <p>Stay updated with latest announcements</p>
        </div>

        <?php if (empty($notices)): ?>
        <div class="alert alert-warning">
            <i class="fas fa-info-circle"></i> No notices available at the moment.
        </div>
        <?php endif; ?>

        <?php foreach ($notices as $notice): ?>
        <div class="notice-card <?php echo $notice['target_audience'] == 'all' ? 'notice-important' : ''; ?>">
            <div class="notice-header">
                <div style="display: flex; justify-content: between; align-items: center;">
                    <h3 style="margin: 0; flex: 1;"><?php echo $notice['title']; ?></h3>
                    <span class="badge <?php echo $notice['target_audience'] == 'all' ? 'badge-important' : 'badge-general'; ?>">
                        <?php echo $notice['target_audience'] == 'all' ? 'Important' : 'General'; ?>
                    </span>
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
                    <i class="fas fa-users"></i> For: <?php echo $notice['class'] . ($notice['division'] ? ' ' . $notice['division'] : ''); ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</body>
</html>