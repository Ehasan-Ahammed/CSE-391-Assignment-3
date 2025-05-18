<?php
require_once 'config.php';
require_once 'includes/session.php';

// Redirect if already logged in as both
if (isLoggedIn() && isAdmin()) {
    header('Location: index.php');
    exit;
}

$error = '';
$adminTab = isset($_GET['admin']) && $_GET['admin'] == '1';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username']);
    $password = $_POST['password'];
    $userType = $_POST['user_type'];
    
    $conn = getDBConnection();
    
    if ($userType === 'admin') {
        $stmt = $conn->prepare("SELECT * FROM admins WHERE username = ?");
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    }
    
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            if ($userType === 'admin') {
                loginAdmin($user);
                header('Location: admin.php');
            } else {
                loginUser($user);
                header('Location: index.php');
            }
            exit;
        }
    }
    
    $error = 'Invalid username or password';
    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Car Workshop</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            padding: 2rem;
        }

        .auth-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            width: 100%;
            max-width: 400px;
            padding: 2rem;
        }

        .auth-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .auth-header img {
            width: 80px;
            height: 80px;
            margin-bottom: 1rem;
        }

        .auth-header h1 {
            color: var(--primary-color);
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }

        .auth-tabs {
            display: flex;
            margin-bottom: 2rem;
            border-bottom: 2px solid #eee;
        }

        .auth-tab {
            flex: 1;
            text-align: center;
            padding: 1rem;
            cursor: pointer;
            color: var(--text-color);
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .auth-tab.active {
            color: var(--secondary-color);
            border-bottom: 2px solid var(--secondary-color);
            margin-bottom: -2px;
        }

        .auth-form {
            display: none;
        }

        .auth-form.active {
            display: block;
        }

        .form-footer {
            text-align: center;
            margin-top: 1.5rem;
            color: var(--text-color);
        }

        .form-footer a {
            color: var(--secondary-color);
            text-decoration: none;
        }

        .form-footer a:hover {
            text-decoration: underline;
        }

        .social-login {
            margin-top: 2rem;
            text-align: center;
        }

        .social-login p {
            color: var(--text-color);
            margin-bottom: 1rem;
        }

        .social-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }

        .social-button {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            transition: transform 0.3s ease;
        }

        .social-button:hover {
            transform: translateY(-3px);
        }

        .facebook { background-color: #1877f2; }
        .google { background-color: #db4437; }
        .twitter { background-color: #1da1f2; }
    </style>
</head>
<body>
    <?php require_once 'includes/navbar.php'; ?>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <img src="assets/images/logo.png" alt="Car Workshop Logo">
                <h1>Welcome Back</h1>
                <p>Sign in to your account</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <div class="auth-tabs">
                <div class="auth-tab active" onclick="switchTab('user')">User Login</div>
                <div class="auth-tab" onclick="switchTab('admin')">Admin Login</div>
            </div>

            <form method="POST" action="" class="auth-form active" id="userForm">
                <input type="hidden" name="user_type" value="user">
                
                <div class="form-group">
                    <label for="username">
                        <i class="fas fa-user"></i> Username
                    </label>
                    <input type="text" id="username" name="username" required>
                </div>

                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i> Password
                    </label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    <i class="fas fa-sign-in-alt"></i> Sign In
                </button>

                <div class="form-footer">
                    <p>Don't have an account? <a href="register.php">Register here</a></p>
                </div>
            </form>

            <form method="POST" action="" class="auth-form" id="adminForm">
                <input type="hidden" name="user_type" value="admin">
                
                <div class="form-group">
                    <label for="admin_username">
                        <i class="fas fa-user-shield"></i> Admin Username
                    </label>
                    <input type="text" id="admin_username" name="username" required>
                </div>

                <div class="form-group">
                    <label for="admin_password">
                        <i class="fas fa-lock"></i> Admin Password
                    </label>
                    <input type="password" id="admin_password" name="password" required>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    <i class="fas fa-sign-in-alt"></i> Admin Sign In
                </button>
            </form>

            <div class="social-login">
                <p>Or sign in with</p>
                <div class="social-buttons">
                    <a href="#" class="social-button facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="social-button google">
                        <i class="fab fa-google"></i>
                    </a>
                    <a href="#" class="social-button twitter">
                        <i class="fab fa-twitter"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function switchTab(type) {
            // Update tabs
            document.querySelectorAll('.auth-tab').forEach(tab => tab.classList.remove('active'));
            document.querySelector(`.auth-tab:${type === 'user' ? 'first-child' : 'last-child'}`).classList.add('active');
            
            // Update forms
            document.querySelectorAll('.auth-form').forEach(form => form.classList.remove('active'));
            document.getElementById(`${type}Form`).classList.add('active');
        }
        // On page load, open the correct tab
        window.onload = function() {
            <?php if ($adminTab): ?>
            switchTab('admin');
            <?php else: ?>
            switchTab('user');
            <?php endif; ?>
        };
    </script>
</body>
</html> 