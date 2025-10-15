<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - SMS</title>
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

        .form-group input, .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: #667eea;
        }

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
            <i class="fas fa-lock"></i>
            <h1>Reset Password</h1>
            <p>Enter your details to reset your password</p>
        </div>

        <?php
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            include 'config.php';
            
            $user_type = $_POST['user_type'];
            $username = $_POST['username'];
            $email = $_POST['email'];
            
            // In a real application, you would:
            // 1. Verify the user exists
            // 2. Generate a reset token
            // 3. Send email with reset link
            // 4. Store token in database
            
            $success = "If an account exists with these details, you will receive a password reset link.";
        }
        ?>

        <?php if (isset($success)): ?>
            <div class="alert alert-success">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>I am a</label>
                <select name="user_type" required>
                    <option value="">Select User Type</option>
                    <option value="student">Student</option>
                    <option value="teacher">Teacher</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>
                    <?php
                    $label = "Username";
                    if (isset($_POST['user_type'])) {
                        if ($_POST['user_type'] == 'student') $label = "PRN Number";
                        elseif ($_POST['user_type'] == 'teacher' || $_POST['user_type'] == 'admin') $label = "Teacher ID";
                    }
                    echo $label;
                    ?>
                </label>
                <input type="text" name="username" required>
            </div>
            
            <div class="form-group">
                <label>Registered Email</label>
                <input type="email" name="email" required>
            </div>
            
            <button type="submit" class="btn">
                <i class="fas fa-paper-plane"></i> Send Reset Link
            </button>
        </form>
        
        <div class="back-link">
            <p>Remember your password? <a href="index.html">Back to Login</a></p>
        </div>
    </div>

    <script>
        document.querySelector('select[name="user_type"]').addEventListener('change', function() {
            const label = this.value === 'student' ? 'PRN Number' : 
                         (this.value === 'teacher' || this.value === 'admin') ? 'Teacher ID' : 'Username';
            document.querySelector('input[name="username"]').previousElementSibling.textContent = label;
        });
    </script>
</body>
</html>