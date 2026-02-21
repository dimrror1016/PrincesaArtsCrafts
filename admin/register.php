<?php
require_once "../config/db.php";
require_once "../functions/helpers.php";

// ----------------------------
// Check if any super admin exists
// ----------------------------
$superAdminExists = $conn->query("SELECT COUNT(*) AS total FROM admins WHERE role='super_admin'")
    ->fetch_assoc()['total'] > 0;

// If a super admin exists, enforce requireSuperAdmin()
if ($superAdminExists) {
    requireSuperAdmin();
}

$success = "";
$error   = "";

// Handle form submission
if (isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $fullname = trim($_POST['fullname']);
    $role     = $_POST['role'];

    if (strlen($password) < 6) {
        $error = "Password must be at least 6 characters";
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare(
            "INSERT INTO admins (username, email, password, fullname, role)
                    VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("sssss", $username, $email, $password, $fullname, $role);

        if ($stmt->execute()) {
            $success = "Account created successfully!";
        } else {
            $error = "Username already exists";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Create Admin Account</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .floating-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 50px;
            height: 50px;
            background-color: #0d6efd;
            /* Bootstrap primary */
            color: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            font-size: 24px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            transition: background-color 0.3s, transform 0.3s;
            z-index: 1000;
        }

        .floating-btn:hover {
            background-color: #0b5ed7;
            /* Darker blue on hover */
            transform: scale(1.1);
        }
    </style>

</head>

<body>
    <a href="dashboard.php"
        class="floating-btn"
        title="Return to Dashboard">
        &#8592;
    </a>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">

                <div class="card shadow">
                    <div class="card-body">
                        <h3 class="text-center mb-4">Create Admin Account</h3>

                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>

                        <?php if ($success): ?>
                            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="fullname" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" name="username" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Role</label>
                                <select name="role" class="form-select" required>
                                    <option value="admin">Admin</option>
                                    <option value="super_admin">Super Admin</option>
                                </select>
                            </div>

                            <button name="register" class="btn btn-dark w-100">Create Account</button>
                        </form>

                        <?php if (!$superAdminExists): ?>
                            <p class="mt-3 text-muted text-center">
                                * First super admin can be created without logging in
                            </p>
                        <?php endif; ?>

                    </div>
                </div>

            </div>
        </div>
    </div>

</body>

</html>