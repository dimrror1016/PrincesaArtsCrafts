<?php
require_once "../config/db.php";
require_once "../functions/helpers.php";
requireAdmin(); // only admins can access

/* =========================
   STATISTICS
=========================*/
$total_users = $conn->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc()['total'];
$total_admins = $conn->query("SELECT COUNT(*) AS total FROM admins")->fetch_assoc()['total'];
$total_rec = $conn->query("SELECT COUNT(*) AS total FROM recommendations")->fetch_assoc()['total'];

$emotion_stats = $conn->query("
    SELECT e.emotion_name, COUNT(*) AS count
    FROM recommendations r
    JOIN emotions e ON e.emotion_id = r.emotion_id
    GROUP BY e.emotion_name
")->fetch_all(MYSQLI_ASSOC);

$gender_stats = $conn->query("
    SELECT gender, COUNT(*) AS count
    FROM users
    GROUP BY gender
")->fetch_all(MYSQLI_ASSOC);

/* =========================
   ACCOUNT REGISTRY (Admins)
=========================*/
$acc_success = "";
$acc_error   = "";

$superAdminExists = $conn->query("SELECT COUNT(*) AS total FROM admins WHERE role='super_admin'")
    ->fetch_assoc()['total'] > 0;

if (isset($_POST['register_admin'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $fullname = trim($_POST['fullname']);
    $email    = trim($_POST['email']);
    $role     = $_POST['role'];

    if (strlen($password) < 6) {
        $acc_error = "Password must be at least 6 characters";
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare(
            "INSERT INTO admins (username, email, password, fullname, role)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("sssss", $username, $email, $hash, $fullname, $role);

        if ($stmt->execute()) {
            $acc_success = "Admin account created successfully!";
        } else {
            $acc_error = "Username or email already exists";
        }
    }
}

/* =========================
   FETCH ADMINS AND USERS
=========================*/
$admins = $conn->query("SELECT admin_id, username, email, fullname, role, created_at FROM admins ORDER BY admin_id DESC")->fetch_all(MYSQLI_ASSOC);
$users  = $conn->query("SELECT user_id, CONCAT(firstname,' ',lastname) AS fullname, email, 'user' AS role, created_at FROM users ORDER BY user_id DESC")->fetch_all(MYSQLI_ASSOC);

/* Combine admins and users for Account Management */
$all_accounts = array_merge($admins, $users);

/* =========================
   BOUQUET ACTIONS
=========================*/
if (isset($_POST['add_bouquet'])) {
    $name = trim($_POST['bouquet_name']);
    $desc = trim($_POST['description']);
    $meaning = trim($_POST['floriography_meaning']);
    $image = $_FILES['bouquet_image']['name'];

    if (!empty($name)) {
        $stmt = $conn->prepare("INSERT INTO bouquets (bouquet_name, description, floriography_meaning, image) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $desc, $meaning, $image);
        $stmt->execute();
        $stmt->close();

        if (!empty($image)) {
            move_uploaded_file($_FILES['bouquet_image']['tmp_name'], "uploads/" . $image);
        }
    }
}

/* =========================
   EMOTION MAPPING
=========================*/
if (isset($_POST['add_mapping'])) {
    $emotion_id = intval($_POST['emotion_id']);
    $bouquet_id = intval($_POST['bouquet_id']);

    $check = $conn->prepare("SELECT map_id FROM emotion_to_bouquet WHERE emotion_id=? AND bouquet_id=?");
    $check->bind_param("ii", $emotion_id, $bouquet_id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows === 0) {
        $stmt = $conn->prepare("INSERT INTO emotion_to_bouquet (emotion_id, bouquet_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $emotion_id, $bouquet_id);
        $stmt->execute();
        $stmt->close();
    }
}

/* =========================
   FETCH DATA
=========================*/
$bouquets = $conn->query("SELECT * FROM bouquets ORDER BY bouquet_name ASC")->fetch_all(MYSQLI_ASSOC);
$emotions = $conn->query("SELECT emotion_id, emotion_name FROM emotions ORDER BY emotion_name")->fetch_all(MYSQLI_ASSOC);
$mappings = $conn->query("
    SELECT m.map_id, e.emotion_name, b.bouquet_name
    FROM emotion_to_bouquet m
    JOIN emotions e ON e.emotion_id = m.emotion_id
    JOIN bouquets b ON b.bouquet_id = m.bouquet_id
")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {background:#fdf6f0; font-family:'Segoe UI',sans-serif;}
#sidebar {width:250px; min-height:100vh;}
#sidebar .nav-link {padding:12px 20px; border-radius:8px; margin-bottom:5px; cursor:pointer;}
#sidebar .nav-link:hover {background:rgba(255,255,255,0.2);}
#sidebar .nav-link.active {background:#ffb6b9; color:#fff;}
.section {display:none;}
.card {border-radius:15px;}
.card h5 {color:#555;}
.btn-primary {background-color:#ffaaa5; border:none;}
.btn-primary:hover {background-color:#ff8a80;}
.btn-success {background-color:#ffd3b6; color:#333;}
.btn-success:hover {background-color:#ffc09f;}
.btn-outline-light {border-color:#ffaaa5; color:#ffaaa5;}
.btn-outline-light:hover {background-color:#ffaaa5; color:#fff;}
table th, table td {vertical-align: middle;}
</style>
</head>
<body>

<div class="d-flex">

    <!-- SIDEBAR -->
    <div class="bg-light p-3" id="sidebar">
        <h4 class="text-center mb-4">Admin Panel</h4>
        <ul class="nav flex-column">
            <li class="nav-item"><span class="nav-link active" data-target="dashboard">Dashboard</span></li>
            <li class="nav-item"><span class="nav-link" data-target="bouquets">Bouquets</span></li>
            <li class="nav-item"><span class="nav-link" data-target="mapping">Emotion Mapping</span></li>
            <li class="nav-item"><span class="nav-link" data-target="registry">Account Registry</span></li>
            <li class="nav-item"><span class="nav-link" data-target="accounts">Account Management</span></li>
            <li class="nav-item mt-4">
                <span class="btn btn-outline-light w-100" data-bs-toggle="modal" data-bs-target="#logoutModal">Logout</span>
            </li>
        </ul>
    </div>

    <!-- CONTENT -->
    <div class="flex-grow-1">
        <nav class="navbar navbar-light bg-white border-bottom px-4">
            <span class="navbar-brand mb-0 h5">Admin Dashboard</span>
        </nav>

        <div class="container-fluid p-4">

            <!-- DASHBOARD -->
            <div id="dashboard" class="section" style="display:block;">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="card shadow text-center p-4" style="background:#fff0f5;">
                            <h6>Total Users</h6>
                            <h2><?= $total_users ?></h2>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card shadow text-center p-4" style="background:#fff5e6;">
                            <h6>Total Admins</h6>
                            <h2><?= $total_admins ?></h2>
                        </div>
                    </div>
                </div>
                <div class="row mt-4">
                    <div class="col-md-6 mb-3">
                        <div class="card shadow p-4" style="background:#e0f7fa;">
                            <h5>Emotion Statistics</h5>
                            <canvas id="emotionChart"></canvas>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card shadow p-4" style="background:#f1f8e9;">
                            <h5>Gender Statistics</h5>
                            <canvas id="genderChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- BOUQUETS -->
            <div id="bouquets" class="section">
                <div class="card shadow p-4" style="background:#fff0f5;">
                    <h4>Bouquet Management</h4>
                    <form method="POST" enctype="multipart/form-data" class="row g-3 mb-4">
                        <div class="col-md-6"><input type="text" name="bouquet_name" class="form-control" placeholder="Bouquet Name" required></div>
                        <div class="col-md-6"><input type="file" name="bouquet_image" class="form-control"></div>
                        <div class="col-12"><textarea name="description" class="form-control" placeholder="Description"></textarea></div>
                        <div class="col-12"><input type="text" name="floriography_meaning" class="form-control" placeholder="Floriography Meaning"></div>
                        <div class="col-12 d-grid"><button type="submit" name="add_bouquet" class="btn btn-success">Add Bouquet</button></div>
                    </form>

                    <table class="table table-striped">
                        <thead>
                            <tr><th>Name</th><th>Meaning</th><th>Image</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bouquets as $b): ?>
                            <tr>
                                <td><?= htmlspecialchars($b['bouquet_name']) ?></td>
                                <td><?= htmlspecialchars($b['floriography_meaning']) ?></td>
                                <td><?php if ($b['image']): ?><img src="uploads/<?= $b['image'] ?>" width="60"><?php endif; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- EMOTION MAPPING -->
            <div id="mapping" class="section">
                <div class="card shadow p-4" style="background:#e0f7fa;">
                    <h4>Emotion Mapping</h4>
                    <form method="POST" class="row g-3 mb-4">
                        <div class="col-md-5">
                            <select name="emotion_id" class="form-select" required>
                                <option value="">Select Emotion</option>
                                <?php foreach ($emotions as $e): ?>
                                <option value="<?= $e['emotion_id'] ?>"><?= htmlspecialchars($e['emotion_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <select name="bouquet_id" class="form-select" required>
                                <option value="">Select Bouquet</option>
                                <?php foreach ($bouquets as $b): ?>
                                <option value="<?= $b['bouquet_id'] ?>"><?= htmlspecialchars($b['bouquet_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2 d-grid"><button type="submit" name="add_mapping" class="btn btn-primary">Add</button></div>
                    </form>

                    <table class="table table-striped">
                        <thead>
                            <tr><th>Emotion</th><th>Bouquet</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($mappings as $m): ?>
                            <tr>
                                <td><?= htmlspecialchars($m['emotion_name']) ?></td>
                                <td><?= htmlspecialchars($m['bouquet_name']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ACCOUNT REGISTRY (Admins) -->
            <div id="registry" class="section">
                <div class="card shadow p-4" style="background:#ffe0e0;">
                    <h4>Admin Account Registry</h4>

                    <?php if ($acc_error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($acc_error) ?></div>
                    <?php endif; ?>
                    <?php if ($acc_success): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($acc_success) ?></div>
                    <?php endif; ?>

                    <form method="POST" class="row g-3">
                        <div class="col-md-6"><input type="text" name="fullname" class="form-control" placeholder="Full Name" required></div>
                        <div class="col-md-6"><input type="text" name="username" class="form-control" placeholder="Username" required></div>
                        <div class="col-md-6"><input type="email" name="email" class="form-control" placeholder="Email" required></div>
                        <div class="col-md-6"><input type="password" name="password" class="form-control" placeholder="Password" required></div>
                        <div class="col-md-6">
                            <select name="role" class="form-select" required>
                                <option value="admin">Admin</option>
                                <option value="super_admin">Super Admin</option>
                            </select>
                        </div>
                        <div class="col-12 d-grid"><button type="submit" name="register_admin" class="btn btn-success">Create Admin</button></div>
                    </form>
                </div>
            </div>

            <!-- ACCOUNT MANAGEMENT (All Accounts) -->
            <div id="accounts" class="section">
                <div class="card shadow p-4" style="background:#e0ffe0;">
                    <h4>Account Management</h4>
                    <table class="table table-striped">
                        <thead>
                            <tr><th>Name</th><th>Email</th><th>Role</th><th>Created At</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_accounts as $a): ?>
                            <tr>
                                <td><?= htmlspecialchars($a['fullname']) ?></td>
                                <td><?= htmlspecialchars($a['email']) ?></td>
                                <td><?= htmlspecialchars($a['role']) ?></td>
                                <td><?= htmlspecialchars($a['created_at']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- LOGOUT MODAL -->
<div class="modal fade" id="logoutModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Confirm Logout</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">Are you sure you want to log out?</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="../functions/logout.php" class="btn btn-danger">Logout</a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const emotionLabels = <?= json_encode(array_column($emotion_stats, 'emotion_name')) ?>;
const emotionData = <?= json_encode(array_column($emotion_stats, 'count')) ?>;
const genderLabels = <?= json_encode(array_column($gender_stats, 'gender')) ?>;
const genderData = <?= json_encode(array_column($gender_stats, 'count')) ?>;

new Chart(document.getElementById('emotionChart'), {
    type: 'bar',
    data: {labels: emotionLabels, datasets:[{label:'Emotion Count', data:emotionData, backgroundColor:'#ffaaa5'}]}
});
new Chart(document.getElementById('genderChart'), {
    type: 'pie',
    data: {labels: genderLabels, datasets:[{label:'Users', data:genderData, backgroundColor:['#ffd3b6','#c7ceea','#a8edea','#ffdac1']}]}
});

// Tab switching
document.querySelectorAll('#sidebar .nav-link').forEach(link=>{
    link.addEventListener('click',()=>{
        document.querySelectorAll('.nav-link').forEach(l=>l.classList.remove('active'));
        link.classList.add('active');
        const target = link.getAttribute('data-target');
        document.querySelectorAll('.section').forEach(s=>s.style.display='none');
        document.getElementById(target).style.display='block';
    });
});
</script>
<script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
