<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_type = $_POST['user_type'];
    $username = $_POST['username'];
    $password = $_POST['password'];

    try {
        if ($user_type == 'student') {
            $stmt = $pdo->prepare("SELECT * FROM students WHERE prn_number = ? AND status = 'approved'");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['prn_number'];
                $_SESSION['user_type'] = 'student';
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['class'] = $user['class'];
                $_SESSION['division'] = $user['division'];
                header('Location: student_dashboard.php');
                exit();
            }
        } 
        elseif ($user_type == 'teacher') {
            $stmt = $pdo->prepare("SELECT * FROM teachers WHERE teacher_id = ? AND status = 'active'");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['teacher_id'];
                $_SESSION['user_type'] = $user['type'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['subject'] = $user['subject'];
                header('Location: teacher_dashboard.php');
                exit();
            }
        }
        elseif ($user_type == 'admin') {
            $stmt = $pdo->prepare("SELECT * FROM teachers WHERE teacher_id = ? AND type = 'class' AND status = 'active'");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['teacher_id'];
                $_SESSION['user_type'] = 'admin';
                $_SESSION['full_name'] = $user['full_name'];
                header('Location: admin_dashboard.php');
                exit();
            }
        }
        
        // If login fails
        header('Location: index.html?error=invalid_credentials');
        exit();
        
    } catch(PDOException $e) {
        die("Login error: " . $e->getMessage());
    }
}
?>