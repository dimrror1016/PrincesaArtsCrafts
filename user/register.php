<?php
require_once "../config/db.php";
require_once "../functions/helpers.php";

if (isset($_POST['register'])) {
    $fname = trim($_POST['firstname']);
    $lname = trim($_POST['lastname']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $gender = $_POST['gender'];
    $age = intval($_POST['age']);
    $location = $_POST['location'];
    $marital = $_POST['marital_status'];

    if ($age < 1 || $age > 120) {
        $error = "Please enter a valid age between 1 and 120.";
    } else {
        $check = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $checkResult = $check->get_result();

        if ($checkResult->num_rows > 0) {
            $error = "Email already registered.";
        } else {

            $stmt = $conn->prepare("INSERT INTO users 
                (firstname, lastname, email, password, gender, age, location, marital_status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

            $stmt->bind_param(
                "sssssiss",
                $fname,
                $lname,
                $email,
                $password,
                $gender,
                $age,
                $location,
                $marital
            );

            if ($stmt->execute()) {
                redirect("../login.php");
            } else {
                $error = "Registration failed: " . $stmt->error;
            }
        }
    }
}

$lagunaCities = [
    'Alaminos',
    'Bay',
    'Biñan',
    'Cabuyao',
    'Calamba',
    'Calauan',
    'Cavinti',
    'Famy',
    'Kalayaan',
    'Liliw',
    'Los Baños',
    'Luisiana',
    'Lumban',
    'Mabitac',
    'Magdalena',
    'Majayjay',
    'Nagcarlan',
    'Paete',
    'Pagsanjan',
    'Pakil',
    'Pangil',
    'Pila',
    'Rizal',
    'San Pablo',
    'San Pedro',
    'Santa Cruz',
    'Santa Maria',
    'Santa Rosa',
    'Siniloan',
    'Victoria'
];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account</title>
    <link href="../assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            padding:20px 20px 20px 20px ; 
            background: linear-gradient(135deg, #ffeef5, #f3e8ff, #f0fff4);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Segoe UI', sans-serif;
        }

        .card {
            border: none;
            border-radius: 22px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
        }

        .title {
            color: #7a4e65;
            font-weight: 700;
        }

        .form-label {
            color: #7a6a73;
            font-weight: 500;
        }

        .form-control,
        .form-select {
            border-radius: 12px;
            padding: 12px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #d291bc;
            box-shadow: 0 0 0 .2rem rgba(210, 145, 188, .25);
        }

        .btn-success {
            background: linear-gradient(90deg, #8ec5a4, #cdb4db);
            border: none;
            border-radius: 12px;
            padding: 12px;
            font-weight: 600;
            letter-spacing: .5px;
        }

        .btn-success:hover {
            opacity: .9;
        }

        .return-btn {
            border-radius: 10px;
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
            <div class="col-md-6">

                <div class="card p-4">

                    <h3 class="text-center title mb-4">🌸 Create Your Account</h3>

                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <form method="POST">

                        <div class="mb-3">
                            <label class="form-label">First Name</label>
                            <input type="text" name="firstname" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="lastname" class="form-control" required>
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
                            <label class="form-label">Gender</label>
                            <select name="gender" class="form-select" required>
                                <option value="">Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Age</label>
                            <input type="number" name="age" class="form-control" min="1" max="120" required
                                oninput="if(this.value>120)this.value=120; if(this.value<1)this.value=1;">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">City (Laguna)</label>
                            <select name="location" class="form-select" required>
                                <option value="">Select City</option>
                                <?php foreach ($lagunaCities as $city): ?>
                                    <option value="<?= $city ?>"><?= $city ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Marital Status</label>
                            <select name="marital_status" class="form-select" required>
                                <option value="">Select Status</option>
                                <option value="Single">Single</option>
                                <option value="Married">Married</option>
                                <option value="Widowed">Widowed</option>
                            </select>
                        </div>

                        <button type="submit" name="register" class="btn btn-success w-100">
                            Create Account
                        </button>

                    </form>

                    <div class="mt-3 text-center">
                        <a href="../login.php">Already have an account? Login</a><br>
                        <a href="../index.php" class="btn btn-secondary mt-2 return-btn">Return to Home</a>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>

</html>