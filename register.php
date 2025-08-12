<?php
session_start();
require_once 'db_config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    
    // ตรวจสอบรหัสผ่าน
    if ($password !== $confirm_password) {
        $error = "รหัสผ่านไม่ตรงกัน";
    } elseif (strlen($password) < 6) {
        $error = "รหัสผ่านต้องมีความยาวอย่างน้อย 6 ตัวอักษร";
    } else {
        // ตรวจสอบว่ามี username หรือ email ซ้ำหรือไม่
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "ชื่อผู้ใช้หรืออีเมลนี้มีอยู่แล้วในระบบ";
        } else {
            // เข้ารหัสรหัสผ่าน
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // บันทึกผู้ใช้ใหม่
            $stmt = $conn->prepare("INSERT INTO users (username, password, name, email, role) VALUES (?, ?, ?, ?, 'user')");
            $stmt->bind_param("ssss", $username, $hashed_password, $name, $email);
            
            if ($stmt->execute()) {
                $_SESSION['registration_success'] = true;
                header("Location: login.php");
                exit();
            } else {
                $error = "เกิดข้อผิดพลาดในการสมัครสมาชิก";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สมัครสมาชิก - Beautiful Register</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Kanit', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow-x: hidden;
            position: relative;
        }

        /* Animated Background Elements */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: 
                radial-gradient(circle at 25% 25%, rgba(255,255,255,0.1) 3px, transparent 3px),
                radial-gradient(circle at 75% 75%, rgba(255,255,255,0.1) 2px, transparent 2px),
                radial-gradient(circle at 50% 50%, rgba(255,255,255,0.05) 4px, transparent 4px);
            background-size: 60px 60px, 40px 40px, 80px 80px;
            animation: sweaterPattern 25s linear infinite;
            z-index: 1;
        }

        @keyframes sweaterPattern {
            0% { background-position: 0 0, 0 0, 0 0; }
            100% { background-position: 60px 60px, 40px 40px, 80px 80px; }
        }

        /* Floating Geometric Shapes */
        .shape {
            position: absolute;
            opacity: 0.1;
            animation: floatShape 8s ease-in-out infinite;
            z-index: 1;
        }

        .shape:nth-child(1) { 
            width: 100px; height: 100px; 
            background: linear-gradient(45deg, #ff6b6b, #ee5a24);
            border-radius: 50%; 
            left: 10%; top: 20%; 
            animation-delay: 0s; 
        }

        .shape:nth-child(2) { 
            width: 60px; height: 60px; 
            background: linear-gradient(45deg, #48cae4, #0077b6);
            border-radius: 20px; 
            right: 15%; top: 30%; 
            animation-delay: 2s; 
        }

        .shape:nth-child(3) { 
            width: 80px; height: 80px; 
            background: linear-gradient(45deg, #06ffa5, #00d4aa);
            clip-path: polygon(50% 0%, 0% 100%, 100% 100%);
            left: 20%; bottom: 25%; 
            animation-delay: 4s; 
        }

        .shape:nth-child(4) { 
            width: 120px; height: 120px; 
            background: linear-gradient(45deg, #ffd93d, #ffcd3c);
            border-radius: 50%; 
            right: 10%; bottom: 15%; 
            animation-delay: 6s; 
        }

        @keyframes floatShape {
            0%, 100% { transform: translateY(0) rotate(0deg) scale(1); }
            25% { transform: translateY(-20px) rotate(90deg) scale(1.1); }
            50% { transform: translateY(0) rotate(180deg) scale(0.9); }
            75% { transform: translateY(-15px) rotate(270deg) scale(1.05); }
        }

        .container {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 480px;
            padding: 20px;
        }

        .auth-form {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(25px);
            border-radius: 25px;
            padding: 40px;
            box-shadow: 
                0 25px 50px rgba(0, 0, 0, 0.1),
                0 0 0 1px rgba(255, 255, 255, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.2);
            animation: slideInUp 1s ease-out;
            position: relative;
            overflow: hidden;
        }

        .auth-form::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
            animation: shimmer 4s ease-in-out infinite;
        }

        @keyframes shimmer {
            0% { left: -100%; }
            50% { left: 100%; }
            100% { left: 100%; }
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(60px) scale(0.9);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .form-header {
            text-align: center;
            margin-bottom: 35px;
            position: relative;
            z-index: 1;
        }

        .logo {
            width: 90px;
            height: 90px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            animation: logoFloat 3s ease-in-out infinite;
            position: relative;
            overflow: hidden;
        }

        .logo::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.3), transparent);
            animation: logoShine 3s ease-in-out infinite;
        }

        .logo i {
            font-size: 2.2rem;
            color: white;
            z-index: 1;
        }

        @keyframes logoFloat {
            0%, 100% { transform: translateY(0) scale(1); }
            50% { transform: translateY(-8px) scale(1.05); }
        }

        @keyframes logoShine {
            0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
            50% { transform: translateX(100%) translateY(100%) rotate(45deg); }
            100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
        }

        .form-title {
            color: #2d3748;
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 10px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .form-subtitle {
            color: #718096;
            font-size: 1.1rem;
            font-weight: 300;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 15px;
            margin-bottom: 25px;
            font-size: 0.95rem;
            animation: shake 0.6s ease-in-out;
            position: relative;
            overflow: hidden;
        }

        .alert-danger {
            background: linear-gradient(135deg, #fed7d7, #feb2b2);
            color: #c53030;
            border-left: 5px solid #e53e3e;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-8px); }
            20%, 40%, 60%, 80% { transform: translateX(8px); }
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-label {
            display: block;
            margin-bottom: 10px;
            color: #2d3748;
            font-weight: 600;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-label i {
            color: #667eea;
            font-size: 1rem;
        }

        .input-container {
            position: relative;
        }

        .form-input {
            width: 100%;
            padding: 18px 55px 18px 18px;
            border: 2px solid #e2e8f0;
            border-radius: 15px;
            font-size: 1rem;
            font-family: 'Kanit', sans-serif;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
        }

        .form-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 
                0 0 0 4px rgba(102, 126, 234, 0.1),
                0 10px 25px rgba(102, 126, 234, 0.15);
            transform: translateY(-3px);
            background: rgba(255, 255, 255, 1);
        }

        .form-input::placeholder {
            color: #a0aec0;
            transition: all 0.3s ease;
        }

        .form-input:focus::placeholder {
            color: transparent;
        }

        .input-icon {
            position: absolute;
            right: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #a0aec0;
            font-size: 1.3rem;
            transition: all 0.3s ease;
        }

        .password-toggle {
            position: absolute;
            right: 18px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1.3rem;
            color: #a0aec0;
            transition: all 0.3s ease;
            z-index: 2;
            padding: 5px;
            border-radius: 50%;
        }

        .password-toggle:hover {
            color: #667eea;
            background: rgba(102, 126, 234, 0.1);
            transform: translateY(-50%) scale(1.1);
        }

        .btn {
            width: 100%;
            padding: 18px;
            border: none;
            border-radius: 15px;
            font-size: 1.2rem;
            font-weight: 600;
            font-family: 'Kanit', sans-serif;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            margin-bottom: 25px;
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(102, 126, 234, 0.4);
        }

        .btn-primary:active {
            transform: translateY(-1px);
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.6s ease;
        }

        .btn:hover::before {
            left: 100%;
        }

        .form-footer {
            text-align: center;
        }

        .form-footer p {
            color: #718096;
            font-size: 0.95rem;
        }

        .form-link {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            position: relative;
        }

        .form-link::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            transition: width 0.3s ease;
        }

        .form-link:hover {
            color: #5a67d8;
        }

        .form-link:hover::after {
            width: 100%;
        }

        /* Password Strength Indicator */
        .password-strength {
            height: 4px;
            background: #e2e8f0;
            border-radius: 2px;
            margin-top: 8px;
            overflow: hidden;
        }

        .strength-bar {
            height: 100%;
            width: 0;
            transition: all 0.3s ease;
            border-radius: 2px;
        }

        .strength-weak { background: #f56565; width: 25%; }
        .strength-fair { background: #ed8936; width: 50%; }
        .strength-good { background: #38b2ac; width: 75%; }
        .strength-strong { background: #48bb78; width: 100%; }

        /* Loading Animation */
        .loading {
            display: none;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        .spinner {
            width: 24px;
            height: 24px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-top: 3px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Success Animation */
        .success-animation {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1000;
        }

        .checkmark {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: #48bb78;
            position: relative;
            animation: scaleIn 0.6s ease-out;
        }

        .checkmark::after {
            content: '✓';
            color: white;
            font-size: 3rem;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            animation: checkmarkAppear 0.4s ease-out 0.3s both;
        }

        @keyframes scaleIn {
            0% { transform: scale(0); }
            100% { transform: scale(1); }
        }

        @keyframes checkmarkAppear {
            0% { opacity: 0; transform: translate(-50%, -50%) scale(0); }
            100% { opacity: 1; transform: translate(-50%, -50%) scale(1); }
        }

        /* Responsive Design */
        @media (max-width: 480px) {
            .container {
                padding: 15px;
            }
            
            .auth-form {
                padding: 30px 25px;
                border-radius: 20px;
            }
            
            .form-title {
                font-size: 2rem;
            }
            
            .logo {
                width: 80px;
                height: 80px;
            }
        }
    </style>
</head>
<body>
    <!-- Animated Background Shapes -->
    <div class="shape"></div>
    <div class="shape"></div>
    <div class="shape"></div>
    <div class="shape"></div>

    <div class="container">
        <form id="registerForm" class="auth-form" method="POST" action="register.php">
            <div class="form-header">
                <div class="logo">
                    <i class="fas fa-user-plus"></i>
                </div>
                <h2 class="form-title">สมัครสมาชิก</h2>
                <p class="form-subtitle">สร้างบัญชีใหม่เพื่อเริ่มต้นการใช้งาน</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <div class="success-animation" id="successAnimation">
                <div class="checkmark"></div>
            </div>

            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-user"></i> ชื่อผู้ใช้
                </label>
                <div class="input-container">
                    <input type="text" class="form-input" id="username" name="username" required 
                           placeholder="กรุณาใส่ชื่อผู้ใช้">
                    <i class="fas fa-user input-icon"></i>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-id-card"></i> ชื่อ-นามสกุล
                </label>
                <div class="input-container">
                    <input type="text" class="form-input" id="name" name="name" required 
                           placeholder="กรุณาใส่ชื่อ-นามสกุล">
                    <i class="fas fa-id-card input-icon"></i>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-envelope"></i> อีเมล
                </label>
                <div class="input-container">
                    <input type="email" class="form-input" id="email" name="email" required 
                           placeholder="กรุณาใส่อีเมล">
                    <i class="fas fa-envelope input-icon"></i>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-lock"></i> รหัสผ่าน
                </label>
                <div class="input-container">
                    <input type="password" class="form-input" id="password" name="password" required 
                           placeholder="กรุณาใส่รหัสผ่าน">
                    <button type="button" class="password-toggle" onclick="togglePassword('password')">
                        <i class="far fa-eye"></i>
                    </button>
                </div>
                <div class="password-strength">
                    <div class="strength-bar" id="strengthBar"></div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-lock"></i> ยืนยันรหัสผ่าน
                </label>
                <div class="input-container">
                    <input type="password" class="form-input" id="confirm_password" name="confirm_password" required 
                           placeholder="กรุณาใส่รหัสผ่านอีกครั้ง">
                    <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                        <i class="far fa-eye"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" id="registerBtn">
                <span class="btn-text">สมัครสมาชิก</span>
                <div class="loading" id="registerLoading">
                    <div class="spinner"></div>
                </div>
            </button>

            <div class="form-footer">
                <p>มีบัญชีแล้ว? <a href="login.php" class="form-link">เข้าสู่ระบบ</a></p>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Custom SweetAlert2 Theme
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

        // Password Toggle Function
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const button = input.nextElementSibling;
            const icon = button.querySelector('i');
            
            if (input.getAttribute('type') === 'password') {
                input.setAttribute('type', 'text');
                icon.classList.remove('far', 'fa-eye');
                icon.classList.add('far', 'fa-eye-slash');
                
                // Animation effect
                button.style.transform = 'translateY(-50%) scale(1.2) rotate(10deg)';
                setTimeout(() => {
                    button.style.transform = 'translateY(-50%) scale(1)';
                }, 200);
            } else {
                input.setAttribute('type', 'password');
                icon.classList.remove('far', 'fa-eye-slash');
                icon.classList.add('far', 'fa-eye');
                
                // Animation effect
                button.style.transform = 'translateY(-50%) scale(1.2) rotate(-10deg)';
                setTimeout(() => {
                    button.style.transform = 'translateY(-50%) scale(1)';
                }, 200);
            }
        }

        // Password Strength Checker
        function checkPasswordStrength(password) {
            const strengthBar = document.getElementById('strengthBar');
            let strength = 0;
            
            if (password.length >= 6) strength++;
            if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
            if (password.match(/\d/)) strength++;
            if (password.match(/[^a-zA-Z\d]/)) strength++;
            
            strengthBar.className = 'strength-bar';
            
            switch (strength) {
                case 1:
                    strengthBar.classList.add('strength-weak');
                    break;
                case 2:
                    strengthBar.classList.add('strength-fair');
                    break;
                case 3:
                    strengthBar.classList.add('strength-good');
                    break;
                case 4:
                    strengthBar.classList.add('strength-strong');
                    break;
                default:
                    strengthBar.style.width = '0';
            }
        }

        // Form Validation and Animation
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = {
                username: document.getElementById('username').value.trim(),
                name: document.getElementById('name').value.trim(),
                email: document.getElementById('email').value.trim(),
                password: document.getElementById('password').value,
                confirm_password: document.getElementById('confirm_password').value
            };
            
            const registerBtn = document.getElementById('registerBtn');
            const btnText = registerBtn.querySelector('.btn-text');
            const loading = document.getElementById('registerLoading');
            
            // Validation
            if (!formData.username) {
                showValidationError('กรุณาใส่ชื่อผู้ใช้', 'username');
                return;
            }
            
            if (formData.username.length < 3) {
                showValidationError('ชื่อผู้ใช้ต้องมีความยาวอย่างน้อย 3 ตัวอักษร', 'username');
                return;
            }
            
            if (!formData.name) {
                showValidationError('กรุณาใส่ชื่อ-นามสกุล', 'name');
                return;
            }
            
            if (!formData.email) {
                showValidationError('กรุณาใส่อีเมล', 'email');
                return;
            }
            
            if (!validateEmail(formData.email)) {
                showValidationError('รูปแบบอีเมลไม่ถูกต้อง', 'email');
                return;
            }
            
            if (!formData.password) {
                showValidationError('กรุณาใส่รหัสผ่าน', 'password');
                return;
            }
            
            if (formData.password.length < 6) {
                showValidationError('รหัสผ่านต้องมีความยาวอย่างน้อย 6 ตัวอักษร', 'password');
                return;
            }
            
            if (formData.password !== formData.confirm_password) {
                showValidationError('รหัสผ่านไม่ตรงกัน', 'confirm_password');
                return;
            }
            
            // Loading Animation
            btnText.style.display = 'none';
            loading.style.display = 'block';
            registerBtn.disabled = true;
            registerBtn.style.background = 'linear-gradient(135deg, #a0aec0, #718096)';
            
            // Show success animation
            setTimeout(() => {
                // Submit the actual form
                this.submit();
            }, 1500);
        });

        function showValidationError(message, fieldId) {
            Swal.fire({
                icon: 'warning',
                title: 'ข้อมูลไม่ถูกต้อง',
                text: message,
                confirmButtonColor: '#667eea',
                background: 'linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%)',
                customClass: {
                    popup: 'animate__animated animate__shakeX'
                }
            });
            
            const field = document.getElementById(fieldId);
            field.focus();
            field.style.borderColor = '#e53e3e';
            field.style.animation = 'shake 0.5s ease-in-out';
            
            setTimeout(() => {
                field.style.borderColor = '#e2e8f0';
                field.style.animation = '';
            }, 1000);
        }

        function validateEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }

        // Input Focus Animations and Validation
        document.querySelectorAll('.form-input').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'translateY(-3px)';
                this.parentElement.style.boxShadow = '0 10px 25px rgba(102, 126, 234, 0.15)';
                
                const icon = this.parentElement.querySelector('.input-icon');
                if (icon) {
                    icon.style.color = '#667eea';
                    icon.style.transform = 'translateY(-50%) scale(1.1)';
                }
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'translateY(0)';
                this.parentElement.style.boxShadow = 'none';
                
                const icon = this.parentElement.querySelector('.input-icon');
                if (icon) {
                    icon.style.color = '#a0aec0';
                    icon.style.transform = 'translateY(-50%) scale(1)';
                }
            });
            
            // Real-time validation
            input.addEventListener('input', function() {
                const value = this.value.trim();
                
                if (this.id === 'password') {
                    checkPasswordStrength(value);
                }
                
                if (this.id === 'confirm_password') {
                    const password = document.getElementById('password').value;
                    if (value && value !== password) {
                        this.style.borderColor = '#e53e3e';
                        this.style.background = 'rgba(229, 62, 62, 0.05)';
                    } else if (value && value === password) {
                        this.style.borderColor = '#48bb78';
                        this.style.background = 'rgba(72, 187, 120, 0.05)';
                    } else {
                        this.style.borderColor = '#e2e8f0';
                        this.style.background = 'rgba(255, 255, 255, 0.9)';
                    }
                } else if (this.id === 'email') {
                    if (value && !validateEmail(value)) {
                        this.style.borderColor = '#ed8936';
                        this.style.background = 'rgba(237, 137, 54, 0.05)';
                    } else if (value && validateEmail(value)) {
                        this.style.borderColor = '#48bb78';
                        this.style.background = 'rgba(72, 187, 120, 0.05)';
                    } else {
                        this.style.borderColor = '#e2e8f0';
                        this.style.background = 'rgba(255, 255, 255, 0.9)';
                    }
                } else {
                    if (value) {
                        this.style.borderColor = '#48bb78';
                        this.style.background = 'rgba(72, 187, 120, 0.05)';
                    } else {
                        this.style.borderColor = '#e2e8f0';
                        this.style.background = 'rgba(255, 255, 255, 0.9)';
                    }
                }
            });
        });

        // Check for PHP errors and show SweetAlert
        <?php if ($error): ?>
        Swal.fire({
            icon: 'error',
            title: 'สมัครสมาชิกไม่สำเร็จ',
            text: '<?php echo $error; ?>',
            confirmButtonColor: '#667eea',
            background: 'linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%)',
            customClass: {
                popup: 'animate__animated animate__shakeX'
            },
            showClass: {
                popup: 'animate__animated animate__fadeInDown'
            },
            hideClass: {
                popup: 'animate__animated animate__fadeOutUp'
            }
        });
        <?php endif; ?>

        // Welcome Animation on Page Load
        window.addEventListener('load', function() {
            // Show welcome toast
            Toast.fire({
                icon: 'info',
                title: 'สร้างบัญชีใหม่',
                text: 'กรอกข้อมูลให้ครบถ้วนเพื่อสมัครสมาชิก',
                background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                color: 'white'
            });
            
            // Add entrance animation to form elements
            const elements = document.querySelectorAll('.form-group, .btn, .form-footer');
            elements.forEach((el, index) => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(30px)';
                
                setTimeout(() => {
                    el.style.transition = 'all 0.8s cubic-bezier(0.4, 0, 0.2, 1)';
                    el.style.opacity = '1';
                    el.style.transform = 'translateY(0)';
                }, index * 150 + 800);
            });
        });

        // Add floating animation to logo with more complex movement
        const logo = document.querySelector('.logo');
        let angle = 0;
        
        setInterval(() => {
            angle += 0.02;
            const yOffset = Math.sin(angle) * 3;
            const xOffset = Math.cos(angle * 0.7) * 1;
            const rotation = Math.sin(angle * 0.5) * 2;
            
            logo.style.transform = `translate(${xOffset}px, ${yOffset}px) rotate(${rotation}deg)`;
        }, 50);

        // Add typing effect to subtitle with cursor
        function typeWriter(element, text, speed = 80) {
            element.innerHTML = '';
            let i = 0;
            const cursor = '<span class="typing-cursor">|</span>';
            
            function typing() {
                if (i < text.length) {
                    element.innerHTML = text.slice(0, i + 1) + cursor;
                    i++;
                    setTimeout(typing, speed);
                } else {
                    // Remove cursor after typing is complete
                    setTimeout(() => {
                        element.innerHTML = text;
                    }, 1000);
                }
            }
            
            typing();
        }

        // Add CSS for typing cursor
        const cursorStyle = document.createElement('style');
        cursorStyle.textContent = `
            .typing-cursor {
                animation: blink 1s infinite;
                color: #667eea;
            }
            
            @keyframes blink {
                0%, 50% { opacity: 1; }
                51%, 100% { opacity: 0; }
            }
        `;
        document.head.appendChild(cursorStyle);

        // Start typing effect after page load
        setTimeout(() => {
            const subtitle = document.querySelector('.form-subtitle');
            const originalText = subtitle.textContent;
            typeWriter(subtitle, originalText, 60);
        }, 1200);

        // Add interactive background shapes
        document.querySelectorAll('.shape').forEach((shape, index) => {
            shape.addEventListener('click', function() {
                this.style.transform = 'scale(1.5) rotate(180deg)';
                this.style.opacity = '0.3';
                
                // Create ripple effect
                const ripple = document.createElement('div');
                ripple.style.position = 'absolute';
                ripple.style.left = this.style.left;
                ripple.style.top = this.style.top;
                ripple.style.width = '20px';
                ripple.style.height = '20px';
                ripple.style.background = 'rgba(255, 255, 255, 0.3)';
                ripple.style.borderRadius = '50%';
                ripple.style.animation = 'ripple 1s ease-out';
                ripple.style.pointerEvents = 'none';
                
                document.body.appendChild(ripple);
                
                setTimeout(() => {
                    this.style.transform = '';
                    this.style.opacity = '0.1';
                    document.body.removeChild(ripple);
                }, 1000);
            });
        });

        // Add ripple animation CSS
        const rippleStyle = document.createElement('style');
        rippleStyle.textContent = `
            @keyframes ripple {
                0% {
                    transform: scale(1);
                    opacity: 0.3;
                }
                100% {
                    transform: scale(20);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(rippleStyle);

        // Mouse movement parallax effect for shapes
        document.addEventListener('mousemove', function(e) {
            const shapes = document.querySelectorAll('.shape');
            const x = (e.clientX / window.innerWidth - 0.5) * 2;
            const y = (e.clientY / window.innerHeight - 0.5) * 2;
            
            shapes.forEach((shape, index) => {
                const speed = (index + 1) * 0.3;
                const xPos = x * speed * 15;
                const yPos = y * speed * 15;
                const rotation = x * speed * 5;
                
                shape.style.transform = `translate(${xPos}px, ${yPos}px) rotate(${rotation}deg)`;
            });
        });

        // Add form field completion progress
        function updateProgress() {
            const inputs = document.querySelectorAll('.form-input');
            let filledInputs = 0;
            
            inputs.forEach(input => {
                if (input.value.trim()) filledInputs++;
            });
            
            const progress = (filledInputs / inputs.length) * 100;
            
            // Update form header with progress
            const title = document.querySelector('.form-title');
            if (progress === 100) {
                title.style.background = 'linear-gradient(135deg, #48bb78, #38a169)';
                title.style.webkitBackgroundClip = 'text';
                title.style.webkitTextFillColor = 'transparent';
                
                // Show completion celebration
                if (!title.dataset.celebrated) {
                    title.dataset.celebrated = 'true';
                    Toast.fire({
                        icon: 'success',
                        title: 'ข้อมูลครบถ้วน!',
                        text: 'พร้อมสมัครสมาชิกแล้ว',
                        background: 'linear-gradient(135deg, #48bb78 0%, #38a169 100%)',
                        color: 'white'
                    });
                }
            } else {
                title.style.background = 'linear-gradient(135deg, #667eea, #764ba2)';
                title.style.webkitBackgroundClip = 'text';
                title.style.webkitTextFillColor = 'transparent';
                title.dataset.celebrated = 'false';
            }
        }

        // Add progress tracking to all inputs
        document.querySelectorAll('.form-input').forEach(input => {
            input.addEventListener('input', updateProgress);
        });

        // Add success animation for form submission
        function showSuccessAnimation() {
            const successAnimation = document.getElementById('successAnimation');
            successAnimation.style.display = 'block';
            
            setTimeout(() => {
                successAnimation.style.display = 'none';
            }, 2000);
        }

        // Add form shake animation for errors
        function shakeForm() {
            const form = document.querySelector('.auth-form');
            form.style.animation = 'shake 0.6s ease-in-out';
            
            setTimeout(() => {
                form.style.animation = '';
            }, 600);
        }

        // Add breath animation to the main form
        setInterval(() => {
            const form = document.querySelector('.auth-form');
            form.style.transform = 'scale(1.002)';
            
            setTimeout(() => {
                form.style.transform = 'scale(1)';
            }, 2000);
        }, 4000);

        // Add field validation messages
        function showFieldMessage(fieldId, message, type = 'error') {
            const field = document.getElementById(fieldId);
            const container = field.parentElement;
            
            // Remove existing message
            const existingMessage = container.querySelector('.field-message');
            if (existingMessage) {
                existingMessage.remove();
            }
            
            // Create new message
            const messageDiv = document.createElement('div');
            messageDiv.className = `field-message field-message-${type}`;
            messageDiv.innerHTML = `<i class="fas fa-${type === 'error' ? 'exclamation-circle' : 'check-circle'}"></i> ${message}`;
            messageDiv.style.cssText = `
                position: absolute;
                top: 100%;
                left: 0;
                font-size: 0.8rem;
                margin-top: 5px;
                padding: 5px 10px;
                border-radius: 5px;
                color: ${type === 'error' ? '#e53e3e' : '#48bb78'};
                background: ${type === 'error' ? 'rgba(229, 62, 62, 0.1)' : 'rgba(72, 187, 120, 0.1)'};
                border: 1px solid ${type === 'error' ? 'rgba(229, 62, 62, 0.3)' : 'rgba(72, 187, 120, 0.3)'};
                animation: slideDown 0.3s ease-out;
            `;
            
            container.style.position = 'relative';
            container.appendChild(messageDiv);
            
            // Auto remove success messages
            if (type === 'success') {
                setTimeout(() => {
                    if (messageDiv.parentElement) {
                        messageDiv.remove();
                    }
                }, 3000);
            }
        }

        // Add slideDown animation
        const slideDownStyle = document.createElement('style');
        slideDownStyle.textContent = `
            @keyframes slideDown {
                0% {
                    opacity: 0;
                    transform: translateY(-10px);
                }
                100% {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
        `;
        document.head.appendChild(slideDownStyle);
    </script>
</body>
</html>