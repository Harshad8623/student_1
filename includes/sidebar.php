<?php
// Common sidebar component that can be included in all pages

function render_sidebar($active_page = 'dashboard') {
    $user_type = $_SESSION['user_type'] ?? '';
    $full_name = $_SESSION['full_name'] ?? '';
    
    if (!$user_type) return '';
    
    $sidebar_links = [];
    
    switch ($user_type) {
        case 'admin':
            $sidebar_links = [
                'dashboard' => ['icon' => 'fas fa-home', 'text' => 'Dashboard', 'link' => 'admin_dashboard.php'],
                'students' => ['icon' => 'fas fa-user-graduate', 'text' => 'Students', 'link' => 'admin_students.php'],
                'teachers' => ['icon' => 'fas fa-chalkboard-teacher', 'text' => 'Teachers', 'link' => 'admin_teachers.php'],
                'subjects' => ['icon' => 'fas fa-book', 'text' => 'Subjects', 'link' => 'admin_subjects.php'],
                'reports' => ['icon' => 'fas fa-chart-pie', 'text' => 'Reports', 'link' => 'admin_reports.php'],
                'notices' => ['icon' => 'fas fa-bullhorn', 'text' => 'Notices', 'link' => 'admin_notices.php'],
                'settings' => ['icon' => 'fas fa-cog', 'text' => 'Settings', 'link' => 'admin_settings.php']
            ];
            $panel_name = 'Admin Panel';
            break;
            
        case 'subject':
        case 'class':
            $sidebar_links = [
                'dashboard' => ['icon' => 'fas fa-home', 'text' => 'Dashboard', 'link' => 'teacher_dashboard.php'],
                'attendance' => ['icon' => 'fas fa-calendar-check', 'text' => 'Take Attendance', 'link' => 'teacher_attendance.php'],
                'marks' => ['icon' => 'fas fa-chart-bar', 'text' => 'Upload Marks', 'link' => 'teacher_marks.php'],
                'students' => ['icon' => 'fas fa-user-graduate', 'text' => 'Students', 'link' => 'teacher_students.php'],
                'subjects' => ['icon' => 'fas fa-book', 'text' => 'My Subjects', 'link' => 'teacher_subjects.php'],
                'reports' => ['icon' => 'fas fa-chart-pie', 'text' => 'Reports', 'link' => 'teacher_reports.php'],
                'profile' => ['icon' => 'fas fa-user', 'text' => 'Profile', 'link' => 'teacher_profile.php']
            ];
            $panel_name = 'Teacher Panel';
            break;
            
        case 'student':
            $sidebar_links = [
                'dashboard' => ['icon' => 'fas fa-home', 'text' => 'Dashboard', 'link' => 'student_dashboard.php'],
                'attendance' => ['icon' => 'fas fa-calendar-check', 'text' => 'Attendance', 'link' => 'student_attendance.php'],
                'marks' => ['icon' => 'fas fa-chart-bar', 'text' => 'Marks & Grades', 'link' => 'student_marks.php'],
                'notices' => ['icon' => 'fas fa-bullhorn', 'text' => 'Notices', 'link' => 'student_notices.php'],
                'timetable' => ['icon' => 'fas fa-calendar-alt', 'text' => 'Timetable', 'link' => 'student_timetable.php'],
                'assignments' => ['icon' => 'fas fa-tasks', 'text' => 'Assignments', 'link' => 'student_assignment.php'],
                'profile' => ['icon' => 'fas fa-user', 'text' => 'Profile', 'link' => 'student_profile.php']
            ];
            $panel_name = 'Student Portal';
            break;
    }
    
    ob_start();
    ?>
    <div class="sidebar">
        <div class="logo">
            <h2><i class="fas fa-graduation-cap"></i> <?php echo $panel_name; ?></h2>
            <div class="user-info" style="margin-top: 10px; font-size: 0.9rem; opacity: 0.8;">
                <i class="fas fa-user"></i> <?php echo $full_name; ?>
            </div>
        </div>
        <ul class="nav-links">
            <?php foreach ($sidebar_links as $key => $link): ?>
            <li>
                <a href="<?php echo $link['link']; ?>" class="<?php echo $active_page == $key ? 'active' : ''; ?>">
                    <i class="<?php echo $link['icon']; ?>"></i> <?php echo $link['text']; ?>
                </a>
            </li>
            <?php endforeach; ?>
            <li>
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </li>
        </ul>
    </div>
    <?php
    return ob_get_clean();
}
?>