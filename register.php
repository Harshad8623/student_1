<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $prn = $_POST['prn'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $class = $_POST['class'];
    $division = $_POST['division'];
    $dob = $_POST['dob'];
    $address = $_POST['address'];
    $parent_name = $_POST['parent_name'];
    $parent_phone = $_POST['parent_phone'];

    try {
        // Check if PRN already exists
        $check_stmt = $pdo->prepare("SELECT id FROM students WHERE prn_number = ?");
        $check_stmt->execute([$prn]);
        
        if ($check_stmt->rowCount() > 0) {
            header('Location: student_register.html?error=prn_exists');
            exit();
        }

        // Insert new student
        $stmt = $pdo->prepare("INSERT INTO students (prn_number, password, full_name, email, phone, class, division, date_of_birth, address, parent_name, parent_phone) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->execute([$prn, $password, $full_name, $email, $phone, $class, $division, $dob, $address, $parent_name, $parent_phone]);
        
        header('Location: index.html?success=registered');
        exit();
        
    } catch(PDOException $e) {
        die("Registration error: " . $e->getMessage());
    }
}
?>