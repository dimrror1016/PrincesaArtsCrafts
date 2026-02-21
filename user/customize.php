<?php
session_start();
require_once "../config/db.php";
require_once "../functions/helpers.php";

if (!isUserLoggedIn()) redirect("login.php");
$user_id = $_SESSION['user_id'];

if (!isset($_GET['rec_id'])) die("Recommendation ID missing.");
$rec_id = (int) $_GET['rec_id'];

/* Fetch recommendation details */
$stmt = $conn->prepare("
    SELECT r.rec_id, e.emotion_name, b.bouquet_name, b.description, b.image
    FROM recommendations r
    JOIN emotions e ON r.emotion_id = e.emotion_id
    JOIN bouquets b ON r.bouquet_id = b.bouquet_id
    WHERE r.rec_id = ? AND r.user_id = ?
");
$stmt->bind_param("ii", $rec_id, $user_id);
$stmt->execute();
$rec = $stmt->get_result()->fetch_assoc();
if (!$rec) die("Unauthorized access.");

/* Fetch all customizations */
$customizations = $conn->query("
    SELECT * FROM customizations 
    ORDER BY type, customization_name
")->fetch_all(MYSQLI_ASSOC);

/* Save customizations */
if (isset($_POST['save_customization'])) {
    $stmt = $conn->prepare("DELETE FROM recommendation_customizations WHERE rec_id = ?");
    $stmt->bind_param("i", $rec_id);
    $stmt->execute();

    if (!empty($_POST['customizations'])) {
        foreach ($_POST['customizations'] as $cust_id) {
            $stmt = $conn->prepare("
                INSERT INTO recommendation_customizations (rec_id, customization_id)
                VALUES (?, ?)
            ");
            $stmt->bind_param("ii", $rec_id, $cust_id);
            $stmt->execute();
        }
    }
    $success = "Customization saved successfully!";
}

/* Fetch saved customizations */
$stmt = $conn->prepare("
    SELECT c.customization_id
    FROM recommendation_customizations rc
    JOIN customizations c ON c.customization_id = rc.customization_id
    WHERE rc.rec_id = ?
");
$stmt->bind_param("i", $rec_id);
$stmt->execute();
$saved_ids = array_column($stmt->get_result()->fetch_all(MYSQLI_ASSOC), 'customization_id');
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Customize Bouquet</title>
<link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">

<style>
body {
    background: linear-gradient(135deg,#ffeef5,#f3e8ff,#f0fff4);
    font-family:'Segoe UI',sans-serif;
}

/* Floating flowers */
.flower{
    position:fixed;
    font-size:28px;
    opacity:.15;
    animation: float 12s infinite ease-in-out;
}
.flower:nth-child(1){ top:12%; left:6%;}
.flower:nth-child(2){ top:70%; left:90%;}
.flower:nth-child(3){ top:45%; left:82%;}
.flower:nth-child(4){ top:88%; left:15%;}
@keyframes float{
    0%,100%{ transform:translateY(0);}
    50%{ transform:translateY(-30px);}
}

.navbar{
    background:#ffffffcc;
    backdrop-filter:blur(8px);
}

.navbar-brand{
    color:#7a4e65 !important;
    font-weight:700;
}

.card{
    border:none;
    border-radius:24px;
    box-shadow:0 15px 40px rgba(0,0,0,0.08);
    transition:.25s;
}
.card:hover{
    transform:translateY(-6px);
    box-shadow:0 20px 45px rgba(0,0,0,0.12);
}

img{
    border-radius:16px;
    box-shadow:0 6px 20px rgba(0,0,0,.1);
}

.option-box{
    border:1px solid #e3e6ea;
    border-radius:12px;
    padding:12px 14px;
    transition:.25s;
    background:white;
    display:flex;
    align-items:center;
    cursor:pointer;
}
.option-box:hover{
    border-color:#cdb4db;
    background:#fdf2f7;
}
.option-box input{
    transform:scale(1.3);
    margin-right:10px;
}

.save-btn{
    border-radius:14px;
    padding:12px 25px;
    font-weight:600;
    background:linear-gradient(90deg,#8ec5a4,#cdb4db);
    border:none;
    color:#fff;
}
.save-btn:hover{
    opacity:.9;
}

.alert-success{
    border-radius:14px;
    background:#d4edda;
    color:#155724;
    font-weight:500;
}
</style>
</head>

<body>

<div class="flower">🌸</div>
<div class="flower">🌼</div>
<div class="flower">🌷</div>
<div class="flower">💐</div>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg shadow-sm py-3">
<div class="container">
<span class="navbar-brand">🌸 Princesa Arts & Crafts</span>
<div>
<a href="emotion_input.php" class="btn btn-outline-secondary btn-sm me-2">New Emotion</a>
<a href="my_recommendations.php" class="btn btn-outline-info btn-sm me-2">My History</a>
<a href="../functions/logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
</div>
</div>
</nav>

<div class="container mt-5 mb-5">

<!-- Bouquet Card -->
<div class="card shadow mb-4 p-4 text-center">
<h4 class="fw-bold"><?= htmlspecialchars($rec['bouquet_name']) ?></h4>
<p class="text-muted mb-2">Emotion: <?= htmlspecialchars($rec['emotion_name']) ?></p>
<p class="text-muted"><?= htmlspecialchars($rec['description']) ?></p>
<?php if (!empty($rec['image'])): ?>
<img src="../admin/uploads/<?= htmlspecialchars($rec['image']) ?>" 
     class="img-fluid rounded shadow mt-3" 
     style="max-width:320px;">
<?php endif; ?>
</div>

<?php if (isset($success)): ?>
<div class="alert alert-success text-center shadow-sm mb-4"><?= $success ?></div>
<?php endif; ?>

<!-- Customization Card -->
<div class="card shadow p-4">
<h5 class="fw-semibold mb-3">Customize Your Bouquet 🌸</h5>
<p class="text-muted small mb-4">
Choose decorations or upgrades to personalize your bouquet.
</p>

<form method="POST">
<div class="row g-3">
<?php foreach ($customizations as $c): ?>
<div class="col-md-4">
<label class="option-box w-100">
<input type="checkbox" name="customizations[]" value="<?= $c['customization_id'] ?>"
<?= in_array($c['customization_id'], $saved_ids) ? 'checked' : '' ?>>
<strong><?= htmlspecialchars($c['customization_name']) ?></strong>
<?php if($c['price']>0): ?>
<span class="text-success ms-1">(+₱<?= number_format($c['price'],2) ?>)</span>
<?php endif; ?>
</label>
</div>
<?php endforeach; ?>
</div>

<div class="text-center mt-4">
<button type="submit" name="save_customization" class="save-btn w-50">Save Customization</button>
</div>
</form>
</div>

</div>

<script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
