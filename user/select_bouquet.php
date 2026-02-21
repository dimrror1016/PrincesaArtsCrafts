<?php
session_start();
require_once "../config/db.php";
require_once "../functions/helpers.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Ensure we have candidates
if (!isset($_SESSION['candidate_bouquets']) || !isset($_SESSION['candidate_emotion_id'])) {
    // If someone directly accesses this page, send them back
    header("Location: emotion_input.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$candidates = $_SESSION['candidate_bouquets'];
$emotionId = $_SESSION['candidate_emotion_id'];
$emotionName = $_SESSION['candidate_emotion_name'] ?? 'Unknown';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Select your bouquet</title>
    <link href="../assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f9f9f9; }
        .card { border-radius: 15px; }
        .hero-header { font-weight: 600; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="#">Princesa Arts & Crafts</a>
        <div class="d-flex">
            <a href="../functions/logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <h3 class="text-center hero-header mb-4">We felt you’re feeling <span class="text-primary"><?= htmlspecialchars($emotionName) ?></span></h3>
    <p class="text-center text-muted mb-4">Choose one bouquet you like best</p>

    <div class="row">
        <?php foreach ($candidates as $b): ?>
            <div class="col-md-4 mb-4">
                <div class="card shadow h-100">
                    <?php if (!empty($b['image'])): ?>
                        <img src="../admin/uploads/<?= htmlspecialchars($b['image']) ?>"
                             class="card-img-top"
                             style="height:200px; object-fit:cover">
                    <?php endif; ?>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?= htmlspecialchars($b['bouquet_name']) ?></h5>
                        <?php if(!empty($b['floriography_meaning'])): ?>
                            <p class="card-text">
                                <strong>Meaning:</strong> <?= htmlspecialchars($b['floriography_meaning']) ?>
                            </p>
                        <?php endif; ?>
                        <?php if(!empty($b['description'])): ?>
                            <p class="card-text"><?= htmlspecialchars($b['description']) ?></p>
                        <?php endif; ?>

                        <form method="POST" action="confirm_choice.php" class="mt-auto">
                            <input type="hidden" name="chosen_bouquet_id" value="<?= $b['bouquet_id'] ?>">
                            <button type="submit" class="btn btn-success w-100">Choose this one</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="text-center mt-3">
        <a href="emotion_input.php" class="btn btn-secondary">Go back</a>
    </div>
</div>

<script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
