<?php
require_once 'config.php';
require_once 'includes/session.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username']);
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = sanitizeInput($_POST['full_name']);
    $phone = sanitizeInput($_POST['phone']);
    $address = sanitizeInput($_POST['address']);
    
    // Validation
    if (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format';
    } else {
        $conn = getDBConnection();
        
        // Check if username exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $error = 'Username already exists';
        } else {
            // Check if email exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $error = 'Email already exists';
            } else {
                // Create user
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name, phone, address) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssss", $username, $email, $hashed_password, $full_name, $phone, $address);
                
                if ($stmt->execute()) {
                    setFlashMessage('success', 'Registration successful! Please login.');
                    header('Location: login.php');
                    exit;
                } else {
                    $error = 'Registration failed. Please try again.';
                }
            }
        }
        $stmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Car Workshop</title>
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
            max-width: 500px;
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

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        @media (max-width: 600px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }

        .password-requirements {
            font-size: 0.9rem;
            color: var(--text-color);
            margin-top: 0.5rem;
        }

        .password-requirements ul {
            list-style: none;
            padding-left: 0;
            margin: 0.5rem 0;
        }

        .password-requirements li {
            margin-bottom: 0.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .password-requirements li i {
            font-size: 0.8rem;
        }

        .requirement-met {
            color: var(--success-color);
        }

        .requirement-not-met {
            color: var(--error-color);
        }
    </style>
</head>
<body>
    <?php require_once 'includes/navbar.php'; ?>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <img src="assets/images/logo.png" alt="Car Workshop Logo">
                <h1>Create Account</h1>
                <p>Join our car workshop community</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" id="registerForm" onsubmit="return validateForm()">
                <div class="form-row">
                    <div class="form-group">
                        <label for="username">
                            <i class="fas fa-user"></i> Username
                        </label>
                        <input type="text" id="username" name="username" required minlength="3" maxlength="50">
                    </div>

                    <div class="form-group">
                        <label for="email">
                            <i class="fas fa-envelope"></i> Email
                        </label>
                        <input type="email" id="email" name="email" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="password">
                            <i class="fas fa-lock"></i> Password
                        </label>
                        <input type="password" id="password" name="password" required minlength="8">
                        <div class="password-requirements">
                            <p>Password must contain:</p>
                            <ul>
                                <li id="length"><i class="fas fa-circle"></i> At least 8 characters</li>
                                <li id="uppercase"><i class="fas fa-circle"></i> One uppercase letter</li>
                                <li id="lowercase"><i class="fas fa-circle"></i> One lowercase letter</li>
                                <li id="number"><i class="fas fa-circle"></i> One number</li>
                                <li id="special"><i class="fas fa-circle"></i> One special character</li>
                            </ul>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">
                            <i class="fas fa-lock"></i> Confirm Password
                        </label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="full_name">
                            <i class="fas fa-user-circle"></i> Full Name
                        </label>
                        <input type="text" id="full_name" name="full_name" required>
                    </div>

                    <div class="form-group">
                        <label for="phone">
                            <i class="fas fa-phone"></i> Phone Number
                        </label>
                        <input type="tel" id="phone" name="phone" required pattern="[0-9]{10,15}">
                    </div>
                </div>

                <div class="form-group">
                    <label for="address">
                        <i class="fas fa-map-marker-alt"></i> Address
                    </label>
                    <textarea id="address" name="address" required rows="3"></textarea>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    <i class="fas fa-user-plus"></i> Create Account
                </button>

                <div class="form-footer">
                    <p>Already have an account? <a href="login.php">Login here</a></p>
                </div>
            </form>
        </div>
    </div>

    <script>
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');
        const requirements = {
            length: document.getElementById('length'),
            uppercase: document.getElementById('uppercase'),
            lowercase: document.getElementById('lowercase'),
            number: document.getElementById('number'),
            special: document.getElementById('special')
        };

        function updateRequirement(element, met) {
            element.classList.remove(met ? 'requirement-not-met' : 'requirement-met');
            element.classList.add(met ? 'requirement-met' : 'requirement-not-met');
            element.querySelector('i').className = met ? 'fas fa-check-circle' : 'fas fa-circle';
        }

        function validatePassword() {
            const value = password.value;
            
            updateRequirement(requirements.length, value.length >= 8);
            updateRequirement(requirements.uppercase, /[A-Z]/.test(value));
            updateRequirement(requirements.lowercase, /[a-z]/.test(value));
            updateRequirement(requirements.number, /[0-9]/.test(value));
            updateRequirement(requirements.special, /[!@#$%^&*]/.test(value));

            return value.length >= 8 && 
                   /[A-Z]/.test(value) && 
                   /[a-z]/.test(value) && 
                   /[0-9]/.test(value) && 
                   /[!@#$%^&*]/.test(value);
        }

        function validateForm() {
            const isPasswordValid = validatePassword();
            const doPasswordsMatch = password.value === confirmPassword.value;

            if (!isPasswordValid) {
                alert('Please ensure your password meets all requirements.');
                return false;
            }

            if (!doPasswordsMatch) {
                alert('Passwords do not match.');
                return false;
            }

            return true;
        }

        password.addEventListener('input', validatePassword);
        confirmPassword.addEventListener('input', () => {
            if (confirmPassword.value !== password.value) {
                confirmPassword.setCustomValidity('Passwords do not match');
            } else {
                confirmPassword.setCustomValidity('');
            }
        });
    </script>
</body>
</html> 