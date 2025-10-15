<?php
// Common utility functions

function sanitize_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function is_logged_in() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_type']);
}

function require_login() {
    if (!is_logged_in()) {
        redirect('index.html');
    }
}

function require_admin() {
    require_login();
    if ($_SESSION['user_type'] != 'admin') {
        redirect('index.html');
    }
}

function require_teacher() {
    require_login();
    if ($_SESSION['user_type'] != 'subject' && $_SESSION['user_type'] != 'class') {
        redirect('index.html');
    }
}

function require_student() {
    require_login();
    if ($_SESSION['user_type'] != 'student') {
        redirect('index.html');
    }
}

function get_user_dashboard() {
    if (!is_logged_in()) return 'index.html';
    
    switch ($_SESSION['user_type']) {
        case 'admin':
            return 'admin_dashboard.php';
        case 'subject':
        case 'class':
            return 'teacher_dashboard.php';
        case 'student':
            return 'student_dashboard.php';
        default:
            return 'index.html';
    }
}

function calculate_grade($percentage) {
    if ($percentage >= 80) return ['grade' => 'A', 'points' => 4.0];
    elseif ($percentage >= 70) return ['grade' => 'B', 'points' => 3.0];
    elseif ($percentage >= 60) return ['grade' => 'C', 'points' => 2.0];
    elseif ($percentage >= 50) return ['grade' => 'D', 'points' => 1.0];
    else return ['grade' => 'F', 'points' => 0.0];
}

function format_date($date) {
    return date('M j, Y', strtotime($date));
}

function format_datetime($datetime) {
    return date('M j, Y g:i A', strtotime($datetime));
}
?>