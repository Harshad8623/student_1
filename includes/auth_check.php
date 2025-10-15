<?php
// Authentication middleware for protecting pages

function require_auth($required_type = null) {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
        header('Location: index.html');
        exit();
    }
    
    if ($required_type) {
        if (is_array($required_type)) {
            if (!in_array($_SESSION['user_type'], $required_type)) {
                header('Location: index.html');
                exit();
            }
        } else {
            if ($_SESSION['user_type'] != $required_type) {
                header('Location: index.html');
                exit();
            }
        }
    }
}

function get_redirect_url() {
    if (!isset($_SESSION['user_type'])) {
        return 'index.html';
    }
    
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

function is_admin() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'admin';
}

function is_teacher() {
    return isset($_SESSION['user_type']) && ($_SESSION['user_type'] == 'subject' || $_SESSION['user_type'] == 'class');
}

function is_student() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'student';
}

function get_user_id() {
    return $_SESSION['user_id'] ?? null;
}

function get_user_type() {
    return $_SESSION['user_type'] ?? null;
}

function get_user_name() {
    return $_SESSION['full_name'] ?? 'User';
}
?>