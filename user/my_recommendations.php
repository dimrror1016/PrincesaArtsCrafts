<?php
session_start();
require_once "../config/db.php";
require_once "../functions/helpers.php";

if (!isUserLoggedIn()) redirect("login.php");
$user_id = $_SESSION['user_id'];

// Fetch recommendations
$stmt = $conn->prepare("
    SELECT r.rec_id, r.used_at, e.emotion_name, b.bouquet_name, b.description, b.image
    FROM recommendations r
    JOIN emotions e ON r.emotion_id = e.emotion_id
    JOIN bouquets b ON r.bouquet_id = b.bouquet_id
    WHERE r.user_id = ?
    ORDER BY r.used_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$recommendations = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Recommendations</title>
<link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
<div class="container">
<span class="navbar-brand">🌸 Floral Recommender</span>
<div class="d-flex">
<a href="emotion_input.php" class="btn btn-outline-light me-2">New Emotion</a>
<a href="../functions/logout.php" class="btn btn-danger">Logout</a>
</div>
</div>
</nav>

<div class="container mt-4 mb-5">
<h3 class="mb-4 text-center">My Recommendations 🌸</h3>

<div class="row">
<?php if (empty($recommendations)) { ?>
<div class="col-12"><p class="text-center">No recommendations yet.</p></div>
<?php } else { 
foreach ($recommendations as $r) { ?>
<div class="col-md-4 mb-3">
<div class="card shadow text-center">
<div class="card-body">
<h5><?= htmlspecialchars($r['bouquet_name']) ?></h5>
<p class="text-muted"><?= htmlspecialchars($r['description']) ?></p>
<p class="text-primary">Emotion: <?= htmlspecialchars($r['emotion_name']) ?></p>
<?php if (!empty($r['image'])): ?>
<img src="../admin/uploads/<?= htmlspecialchars($r['image']) ?>" class="img-fluid rounded shadow mb-2" width="200">
<?php endif; ?>
<p class="text-muted">Recommended on: <?= date("F d, Y h:i A", strtotime($r['used_at'])) ?></p>
<a href="customize.php?rec_id=<?= $r['rec_id'] ?>" class="btn btn-success btn-sm">Customize Again</a>
</div>
</div>
</div>
<?php } } ?>
</div>

</div>
<script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
