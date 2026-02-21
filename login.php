<?php
session_start();
require_once "config/db.php";
require_once "functions/helpers.php";

if (isset($_POST['login'])) {
    $identifier = trim(strtolower($_POST['identifier']));
    $pass = $_POST['password'];

    /* =======================
       1️⃣ CHECK ADMINS FIRST
    ======================= */
    $stmt = $conn->prepare(
        "SELECT admin_id, password, role 
         FROM admins 
         WHERE username = ? OR email = ?"
    );
    $stmt->bind_param("ss", $identifier, $identifier);
    $stmt->execute();
    $adminResult = $stmt->get_result();

    if ($adminResult->num_rows === 1) {
        $admin = $adminResult->fetch_assoc();

        if (password_verify($pass, $admin['password'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = $admin['admin_id'];
            $_SESSION['admin_role'] = $admin['role'];
            redirect("admin/dashboard.php");
        } else {
            $error = "Wrong password";
        }
    } else {

        /* =======================
           2️⃣ CHECK USERS
        ======================= */
        $stmt = $conn->prepare(
            "SELECT user_id, password 
             FROM users 
             WHERE email = ?"
        );
        $stmt->bind_param("s", $identifier);
        $stmt->execute();
        $userResult = $stmt->get_result();

        if ($userResult->num_rows === 1) {
            $user = $userResult->fetch_assoc();

            if (password_verify($pass, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['user_id'];
                redirect("user/emotion_input.php");
            } else {
                $error = "Wrong password";
            }
        } else {
            $error = "Account not found";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Login | Princesa Arts & Crafts</title>

    <link href="assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!-- Google Fonts for Floral / Handwriting -->
    <link href="https://fonts.googleapis.com/css2?family=Parisienne&family=Great+Vibes&display=swap" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #ffeef5, #f3e8ff, #f0fff4);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Segoe UI', sans-serif;
        }

        .login-card {
            border: none;
            border-radius: 22px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
            background: #ffffff;
            opacity: 0;
            transform: translateY(30px);
            animation: fadeInUp 0.8s forwards;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .login-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Floral / Elegant headings */
        .login-title {
            color: #7a4e65;
            font-weight: 700;
            font-family: 'Great Vibes', cursive;
            font-size: 2.2rem;
        }

        .brand-header h4 {
            font-family: 'Parisienne', cursive;
            font-size: 2rem;
            color: #7a4e65;
        }

        .form-label {
            color: #7a6a73;
            font-weight: 500;
        }

        .form-control {
            border-radius: 12px;
            padding: 12px;
            font-family: 'Segoe UI', sans-serif;
            /* keep input readable */
        }

        .form-control:focus {
            border-color: #d291bc;
            box-shadow: 0 0 0 0.2rem rgba(210, 145, 188, 0.25);
        }

        .btn-primary {
            background: linear-gradient(90deg, #d291bc, #cdb4db);
            border: none;
            border-radius: 12px;
            padding: 12px;
            font-weight: 600;
            letter-spacing: .5px;
            transition: transform 0.2s ease, opacity 0.2s ease;
            font-family: 'Parisienne', cursive;
            /* floral button */
            font-size: 1.1rem;
        }

        .btn-primary:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }

        .register-link a {
            color: #b5838d;
            text-decoration: none;
            font-weight: 500;
            font-family: 'Segoe UI', sans-serif;
            /* keep readable */
        }

        .register-link a:hover {
            text-decoration: underline;
        }

        .password-toggle {
            font-size: 0.9rem;
            color: #7a4e65;
            cursor: pointer;
            user-select: none;
        }

        .forgot-link {
            font-size: 0.9rem;
        }

        @media (max-width: 576px) {
            .login-card {
                padding: 20px;
            }
        }

        /* floating flowers */
        .flower {
            position: fixed;
            font-size: 50px;
            opacity: 0.15;
            animation: float 12s infinite ease-in-out;
            pointer-events: none;
            z-index: 0;
        }

        /* flower positions with random delays */
        .flower:nth-child(1) {
            top: 10%;
            left: 5%;
            animation-delay: 0s;
        }

        .flower:nth-child(2) {
            top: 70%;
            left: 90%;
            animation-delay: 1s;
        }

        .flower:nth-child(3) {
            top: 40%;
            left: 85%;
            animation-delay: 2s;
        }

        .flower:nth-child(4) {
            top: 85%;
            left: 15%;
            animation-delay: 3s;
        }

        .flower:nth-child(5) {
            top: 20%;
            left: 50%;
            animation-delay: 4s;
        }

        .flower:nth-child(6) {
            top: 60%;
            left: 10%;
            animation-delay: 5s;
        }

        .flower:nth-child(7) {
            top: 35%;
            left: 30%;
            animation-delay: 6s;
        }

        .flower:nth-child(8) {
            top: 75%;
            left: 70%;
            animation-delay: 7s;
        }

        .flower:nth-child(9) {
            top: 5%;
            left: 80%;
            animation-delay: 8s;
        }

        .flower:nth-child(10) {
            top: 50%;
            left: 95%;
            animation-delay: 9s;
        }

        .flower:nth-child(11) {
            top: 30%;
            left: 60%;
            animation-delay: 10s;
        }

        .flower:nth-child(12) {
            top: 80%;
            left: 40%;
            animation-delay: 11s;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-30px);
            }
        }
    </style>
</head>

<body>
    <!-- floating flowers -->
    <div class="flower">🌸</div>
    <div class="flower">🌼</div>
    <div class="flower">🌷</div>
    <div class="flower">💐</div>
    <div class="flower">🌹</div>
    <div class="flower">🌻</div>
    <div class="flower">🌺</div>
    <div class="flower">🥀</div>
    <div class="flower">💮</div>
    <div class="flower">🌿</div>
    <div class="flower">🍀</div>
    <div class="flower">🌾</div>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">

                <div class="card login-card p-4">

                    <div class="brand-header">
                        <h4>🌸 Princesa Arts & Crafts</h4>
                    </div>

                    <h3 class="text-center login-title mb-4">Welcome</h3>

                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <form method="POST">

                        <div class="mb-3">
                            <label for="identifier" class="form-label">Email or Username</label>
                            <input type="text" name="identifier" id="identifier" class="form-control" autocomplete="username" required>
                        </div>

                        <div class="mb-2">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" name="password" id="password" class="form-control" autocomplete="current-password" required>
                            <div class="d-flex justify-content-between mt-1">
                                <span class="password-toggle" onclick="togglePassword()">Show password</span>
                                <a href="user/forgot_password.php" class="forgot-link">Forgot password?</a>
                            </div>
                        </div>

                        <button type="submit" name="login" class="btn btn-primary w-100 mt-3">
                            Login
                        </button>

                    </form>

                    <div class="mt-3 text-center register-link">
                        <a href="user/register.php">Create an account</a>
                    </div>

                </div>

            </div>
        </div>
    </div>

    <script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>

    <script>
        function togglePassword() {
            const pw = document.getElementById('password');
            pw.type = pw.type === 'password' ? 'text' : 'password';
        }
    </script>

</body>

</html>