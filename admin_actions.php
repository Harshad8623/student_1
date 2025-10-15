<?php
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header('Location: index.html');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    
    try {
        if ($action == 'approve_student') {
            $prn = $_POST['prn'];
            $stmt = $pdo->prepare("UPDATE students SET status = 'approved' WHERE prn_number = ?");
            $stmt->execute([$prn]);
            
        } elseif ($action == 'reject_student') {
            $prn = $_POST['prn'];
            $stmt = $pdo->prepare("UPDATE students SET status = 'rejected' WHERE prn_number = ?");
            $stmt->execute([$prn]);
            
        } elseif ($action == 'add_teacher') {
            $teacher_id = $_POST['teacher_id'];
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $full_name = $_POST['full_name'];
            $email = $_POST['email'];
            $phone = $_POST['phone'];
            $subject = $_POST['subject'];
            $type = $_POST['type'];
            $class_assigned = $_POST['class_assigned'];
            $division_assigned = $_POST['division_assigned'];
            
            $stmt = $pdo->prepare("INSERT INTO teachers (teacher_id, password, full_name, email, phone, subject, type, class_assigned, division_assigned) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$teacher_id, $password, $full_name, $email, $phone, $subject, $type, $class_assigned, $division_assigned]);
            
        } elseif ($action == 'add_subject') {
            $subject_code = $_POST['subject_code'];
            $subject_name = $_POST['subject_name'];
            $teacher_id = $_POST['teacher_id'];
            $class = $_POST['class'];
            
            $stmt = $pdo->prepare("INSERT INTO subjects (subject_code, subject_name, teacher_id, class) VALUES (?, ?, ?, ?)");
            $stmt->execute([$subject_code, $subject_name, $teacher_id, $class]);
        }
        
        header('Location: ' . $_SERVER['HTTP_REFERER'] . '?success=action_completed');
        exit();
        
    } catch(PDOException $e) {
        die("Action error: " . $e->getMessage());
    }
}
?>