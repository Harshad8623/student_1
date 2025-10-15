<?php
include 'config.php';

// Check if token is provided (in real app, verify token from email)
if (!isset($_GET['token']) && !isset($_SESSION['reset_user'])) {
    header('Location: forgot_password.php');
    exit();
}

// Handle password reset
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long!";
    } else {
        // In real application, you would:
        // 1. Verify the reset token
        // 2. Get user ID from token
        // 3. Update password
        // 4. Delete used token
        
        // For demo purposes, we'll use session
        if (isset($_SESSION['reset_user'])) {
            $user_type = $_SESSION['reset_user']['type'];
            $username = $_SESSION['reset_user']['username'];
            
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            if ($user_type == 'student') {
                $stmt = $pdo->prepare("UPDATE students SET password = ? WHERE prn_number = ?");
            } else {
                $stmt = $pdo->prepare("UPDATE teachers SET password = ? WHERE teacher_id = ?");
            }
            
            $stmt->execute([$hashed_password, $username]);
            
            $success = "Password reset successfully! You can now login with your new password.";
            unset($_SESSION['reset_user']);
        } else {
            $error = "Invalid or expired reset token!";
        }
    }
}

// For demo - simulate token verification
if (isset($_GET['token']) && $_GET['token'] == 'demo') {
    $_SESSION['reset_user'] = [
        'type' => 'student',
        'username' => 'STU001'
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - SMS</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 450px;
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo i {
            font-size: 3rem;
            color: #667eea;
            margin-bottom: 15px;
        }

        .logo h1 {
            color: #333;
            margin-bottom: 10px;
        }

        .logo p {
            color: #666;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }

        .password-strength {
            margin-top: 5px;
            font-size: 0.9rem;
        }

        .strength-weak { color: #dc3545; }
        .strength-medium { color: #ffc107; }
        .strength-strong { color: #28a745; }

        .btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            color: #667eea;
            text-decoration: none;
        }

        .back-link a:hover {
            text-decoration: underline;
        }

        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <i class="fas fa-key"></i>
            <h1>Set New Password</h1>
            <p>Create a strong new password for your account</p>
        </div>

        <?php if (isset($success)): ?>
            <div class="alert alert-success">
                <?php echo $success; ?>
                <div style="margin-top: 10px;">
                    <a href="index.html" class="btn">Go to Login</a>
                </div>
            </div>
        <?php else: ?>

            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" id="resetForm">
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="password" id="password" required 
                           onkeyup="checkPasswordStrength()">
                    <div class="password-strength" id="passwordStrength">
                        Password strength: <span id="strengthText">None</span>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" id="confirm_password" required
                           onkeyup="checkPasswordMatch()">
                    <div class="password-strength" id="passwordMatch" style="display: none;">
                        Passwords do not match!
                    </div>
                </div>
                
                <button type="submit" class="btn" id="submitBtn">
                    <i class="fas fa-save"></i> Reset Password
                </button>
            </form>
            
            <div class="back-link">
                <p><a href="index.html">Back to Login</a></p>
            </div>

        <?php endif; ?>
    </div>

    <script>
        function checkPasswordStrength() {
            const password = document.getElementById('password').value;
            const strengthText = document.getElementById('strengthText');
            const strengthDiv = document.getElementById('passwordStrength');
            
            let strength = 0;
            let text = 'Weak';
            let className = 'strength-weak';
            
            if (password.length >= 6) strength++;
            if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
            if (password.match(/\d/)) strength++;
            if (password.match(/[^a-zA-Z\d]/)) strength++;
            
            if (strength >= 4) {
                text = 'Strong';
                className = 'strength-strong';
            } else if (strength >= 2) {
                text = 'Medium';
                className = 'strength-medium';
            }
            
            strengthText.textContent = text;
            strengthText.className = className;
        }
        
        function checkPasswordMatch() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const matchDiv = document.getElementById('passwordMatch');
            
            if (confirmPassword && password !== confirmPassword) {
                matchDiv.style.display = 'block';
                matchDiv.style.color = '#dc3545';
            } else {
                matchDiv.style.display = 'none';
            }
        }
        
        document.getElementById('resetForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                return false;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters long!');
                return false;
            }
            
            return true;
        });
    </script>
</body>
</html>