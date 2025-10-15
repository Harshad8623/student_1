<?php
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header('Location: index.html');
    exit();
}

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_settings'])) {
        $institution_name = $_POST['institution_name'];
        $academic_year = $_POST['academic_year'];
        $attendance_threshold = $_POST['attendance_threshold'];
        
        // In real application, you would save these to a settings table
        $success = "Settings updated successfully!";
    }
}

// Default settings (in real app, load from database)
$settings = [
    'institution_name' => 'ABC College of Engineering',
    'academic_year' => '2024-2025',
    'attendance_threshold' => '75'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Settings - Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
            <li><a href="admin_notices.php"><i class="fas fa-bullhorn"></i> Notices</a></li>
            <li><a href="#" class="active"><i class="fas fa-cog"></i> Settings</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="header">
            <h1>System Settings</h1>
            <p>Configure system preferences and institution details</p>
        </div>

        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <div class="settings-container">
            <div class="table-container">
                <form method="POST">
                    <h3>Institution Settings</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Institution Name</label>
                            <input type="text" name="institution_name" value="<?php echo $settings['institution_name']; ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Academic Year</label>
                            <input type="text" name="academic_year" value="<?php echo $settings['academic_year']; ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Attendance Threshold (%)</label>
                            <input type="number" name="attendance_threshold" value="<?php echo $settings['attendance_threshold']; ?>" min="1" max="100" required>
                            <small>Minimum attendance percentage required</small>
                        </div>
                    </div>

                    <h3>System Configuration</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Student Registration</label>
                            <select name="student_registration">
                                <option value="open" selected>Open - Anyone can register</option>
                                <option value="closed">Closed - Admin approval only</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Default Password Policy</label>
                            <select name="password_policy">
                                <option value="simple" selected>Simple (6 characters minimum)</option>
                                <option value="medium">Medium (8 characters with numbers)</option>
                                <option value="strong">Strong (8+ characters with special characters)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Result Publication</label>
                            <select name="result_publication">
                                <option value="immediate" selected>Immediate after upload</option>
                                <option value="scheduled">Scheduled date</option>
                            </select>
                        </div>
                    </div>

                    <h3>Academic Settings</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Grading System</label>
                            <select name="grading_system">
                                <option value="percentage" selected>Percentage</option>
                                <option value="cgpa">CGPA</option>
                                <option value="grades">Letter Grades</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Exam Types</label>
                            <div style="margin-top: 10px;">
                                <label style="display: block;">
                                    <input type="checkbox" name="exam_types[]" value="unit_test" checked> Unit Tests
                                </label>
                                <label style="display: block;">
                                    <input type="checkbox" name="exam_types[]" value="mid_term" checked> Mid Term
                                </label>
                                <label style="display: block;">
                                    <input type="checkbox" name="exam_types[]" value="final" checked> Final Exam
                                </label>
                                <label style="display: block;">
                                    <input type="checkbox" name="exam_types[]" value="assignment" checked> Assignments
                                </label>
                            </div>
                        </div>
                    </div>

                    <button type="submit" name="update_settings" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Settings
                    </button>
                </form>
            </div>

            <div class="table-container">
                <h3>System Information</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <strong>PHP Version:</strong> <?php echo phpversion(); ?>
                    </div>
                    <div class="info-item">
                        <strong>Database:</strong> MySQL
                    </div>
                    <div class="info-item">
                        <strong>Server Software:</strong> <?php echo $_SERVER['SERVER_SOFTWARE']; ?>
                    </div>
                    <div class="info-item">
                        <strong>System Status:</strong> <span style="color: #28a745;">● Operational</span>
                    </div>
                </div>
            </div>

            <div class="table-container">
                <h3>Quick Actions</h3>
                <div class="action-buttons">
                    <button class="btn btn-warning">
                        <i class="fas fa-database"></i> Backup Database
                    </button>
                    <button class="btn btn-info">
                        <i class="fas fa-sync"></i> Clear Cache
                    </button>
                    <button class="btn btn-secondary">
                        <i class="fas fa-file-export"></i> Export Data
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .settings-container {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
        }

        .info-item {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
            border-left: 4px solid #667eea;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
    </style>
</body>
</html>