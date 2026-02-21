<?php
session_start();
require_once "../config/db.php";
require_once "../functions/helpers.php";

if (!isUserLoggedIn()) redirect("login.php");

$user_id = $_SESSION['user_id'];

if (!isset($_SESSION['bouquet_choices'])) {
    $_SESSION['error'] = "Please enter your emotion first.";
    redirect("emotion_input.php");
}

$emotionId = $_SESSION['bouquet_choices']['emotion_id'];
$bouquets  = $_SESSION['bouquet_choices']['bouquets'];

$stmt = $conn->prepare("SELECT emotion_name FROM emotions WHERE emotion_id = ?");
$stmt->bind_param("i", $emotionId);
$stmt->execute();
$emotionRow = $stmt->get_result()->fetch_assoc();
$emotionName = $emotionRow['emotion_name'] ?? 'Unknown';

if (isset($_POST['select_bouquet'])) {
    $bouquet_id = (int) $_POST['bouquet_id'];

    $stmt = $conn->prepare("
        INSERT INTO recommendations (user_id, emotion_id, bouquet_id)
        VALUES (?, ?, ?)
    ");
    $stmt->bind_param("iii", $user_id, $emotionId, $bouquet_id);
    $stmt->execute();

    $rec_id = $stmt->insert_id;
    unset($_SESSION['bouquet_choices']);
    redirect("customize.php?rec_id=" . $rec_id);
}

function getBouquetStats($conn, $bouquet_id) {
    $stmt = $conn->prepare("
        SELECT u.gender, COUNT(*) AS total
        FROM recommendations r
        JOIN users u ON r.user_id = u.user_id
        WHERE r.bouquet_id = ?
        GROUP BY u.gender
    ");
    $stmt->bind_param("i", $bouquet_id);
    $stmt->execute();
    $gender = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $stmt = $conn->prepare("
        SELECT u.location, COUNT(*) AS total
        FROM recommendations r
        JOIN users u ON r.user_id = u.user_id
        WHERE r.bouquet_id = ?
        GROUP BY u.location
        ORDER BY total DESC
    ");
    $stmt->bind_param("i", $bouquet_id);
    $stmt->execute();
    $location = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    return [$gender, $location];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Select Your Bouquet</title>
<link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">

<style>
body{
    background: linear-gradient(135deg,#ffeef5,#f3e8ff,#f0fff4);
    font-family:'Segoe UI',sans-serif;
}

/* floating flowers */
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

.badge{
    background:linear-gradient(90deg,#cdb4db,#ffc8dd);
    color:#5a3c55;
    font-weight:600;
    padding:8px 12px;
    border-radius:10px;
}

img{
    border-radius:16px;
    box-shadow:0 6px 20px rgba(0,0,0,.1);
}

.stats-box{
    background:#faf5ff;
    border-radius:12px;
    padding:10px;
    margin-bottom:10px;
}

.btn-success{
    background:linear-gradient(90deg,#8ec5a4,#cdb4db);
    border:none;
    border-radius:14px;
    padding:12px;
    font-weight:600;
}

.btn-success:hover{ opacity:.9; }
</style>
</head>

<body>

<div class="flower">🌸</div>
<div class="flower">🌼</div>
<div class="flower">🌷</div>
<div class="flower">💐</div>

<nav class="navbar shadow-sm py-3">
<div class="container">
<span class="navbar-brand">🌸 Princesa Arts & Crafts</span>
<div>
<a href="emotion_input.php" class="btn btn-outline-secondary me-2">Return</a>
<a href="../functions/logout.php" class="btn btn-outline-danger">Logout</a>
</div>
</div>
</nav>

<div class="container mt-5 mb-5">
<h3 class="text-center mb-4">Bouquets Perfect For Your Emotion 🌸</h3>

<div class="row">

<?php foreach ($bouquets as $b): ?>

<?php
$stmt = $conn->prepare("SELECT floriography_meaning FROM bouquets WHERE bouquet_id = ?");
$stmt->bind_param("i", $b['bouquet_id']);
$stmt->execute();
$floriography = $stmt->get_result()->fetch_assoc()['floriography_meaning'] ?? '';

[$genderStats, $locationStats] = getBouquetStats($conn, $b['bouquet_id']);
?>

<div class="col-md-4 mb-4">
<div class="card h-100">
<div class="card-body text-center">

<h5 class="fw-bold"><?= htmlspecialchars($b['bouquet_name']) ?></h5>

<span class="badge mb-2">
Emotion Match: <?= htmlspecialchars($emotionName) ?>
</span>

<p class="text-muted mt-2"><?= htmlspecialchars($b['description']) ?></p>

<p class="small"><strong>Floriography:</strong><br>
<?= htmlspecialchars($floriography) ?></p>

<?php if (!empty($b['image'])): ?>
<img src="../admin/uploads/<?= htmlspecialchars($b['image']) ?>" class="img-fluid mb-3" width="220">
<?php endif; ?>

<div class="stats-box text-start small">
<strong>Popularity by Gender</strong>
<ul class="mb-1">
<?php foreach ($genderStats as $g): ?>
<li><?= htmlspecialchars($g['gender']) ?> — <?= $g['total'] ?></li>
<?php endforeach; ?>
<?php if (empty($genderStats)): ?><li>No data yet</li><?php endif; ?>
</ul>
</div>

<div class="stats-box text-start small">
<strong>Popularity by Location</strong>
<ul>
<?php foreach ($locationStats as $l): ?>
<li><?= htmlspecialchars($l['location']) ?> — <?= $l['total'] ?></li>
<?php endforeach; ?>
<?php if (empty($locationStats)): ?><li>No data yet</li><?php endif; ?>
</ul>
</div>

<form method="POST">
<input type="hidden" name="bouquet_id" value="<?= $b['bouquet_id'] ?>">
<button type="submit" name="select_bouquet" class="btn btn-success w-100 mt-2">
Select This Bouquet
</button>
</form>

</div>
</div>
</div>

<?php endforeach; ?>

</div>
</div>

<script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
