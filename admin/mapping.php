<?php
require_once "../config/db.php";
require_once "../functions/helpers.php";
requireAdmin();

// Add Mapping
if (isset($_POST['add'])) {
    $emotion_id = intval($_POST['emotion_id']);
    $bouquet_id = intval($_POST['bouquet_id']);

    // Prevent duplicates
    $check = $conn->prepare("SELECT map_id FROM emotion_to_bouquet WHERE emotion_id = ? AND bouquet_id = ?");
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

// Update Mapping
if (isset($_POST['update'])) {
    $id = intval($_POST['map_id']);
    $emotion_id = intval($_POST['emotion_id']);
    $bouquet_id = intval($_POST['bouquet_id']);

    $stmt = $conn->prepare("UPDATE emotion_to_bouquet SET emotion_id=?, bouquet_id=? WHERE map_id=?");
    $stmt->bind_param("iii", $emotion_id, $bouquet_id, $id);
    $stmt->execute();
    $stmt->close();
}

// Delete Mapping
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM emotion_to_bouquet WHERE map_id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    redirect("mapping.php");
}

// Fetch Mappings
$mappings = $conn->query("
    SELECT m.map_id, e.emotion_name, b.bouquet_name
    FROM emotion_to_bouquet m
    JOIN emotions e ON e.emotion_id = m.emotion_id
    JOIN bouquets b ON b.bouquet_id = m.bouquet_id
    ORDER BY e.emotion_name ASC
")->fetch_all(MYSQLI_ASSOC);

// Fetch dropdown data
$emotions = $conn->query("SELECT emotion_id, emotion_name FROM emotions ORDER BY emotion_name ASC")->fetch_all(MYSQLI_ASSOC);
$bouquets = $conn->query("SELECT bouquet_id, bouquet_name FROM bouquets ORDER BY bouquet_name ASC")->fetch_all(MYSQLI_ASSOC);

// Check if editing
$edit_mapping = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $edit_mapping = $conn->query("SELECT * FROM emotion_to_bouquet WHERE map_id=$id")->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Emotion Mapping</title>
<link href="../assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">

<style>
body {
    background: #f7f5fb;
}
.card {
    border: none;
    border-radius: 16px;
    background: #ffffff;
}
.navbar {
    background: #e8dff5 !important;
}
.btn-primary {
    background: #cdb4db;
    border: none;
}
.btn-primary:hover {
    background: #b8a2c8;
}
.btn-success {
    background: #bde0fe;
    border: none;
    color: #000;
}
.btn-success:hover {
    background: #a2d2ff;
}
</style>
</head>

<body>

<nav class="navbar navbar-light mb-4">
    <div class="container">
        <span class="navbar-brand fw-bold">Emotion Mapping</span>
        <a href="dashboard.php" class="btn btn-outline-dark btn-sm">Dashboard</a>
    </div>
</nav>

<div class="container">

<!-- Form -->
<div class="card shadow-sm mb-4 p-4">
    <h4><?= $edit_mapping ? "Edit Mapping" : "Add New Mapping" ?></h4>
    <form method="POST" class="row g-3 mt-2">
        <div class="col-md-5">
            <select name="emotion_id" class="form-select" required>
                <option value="">Select Emotion</option>
                <?php foreach ($emotions as $e): ?>
                    <option value="<?= $e['emotion_id'] ?>"
                        <?= $edit_mapping && $edit_mapping['emotion_id']==$e['emotion_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($e['emotion_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-5">
            <select name="bouquet_id" class="form-select" required>
                <option value="">Select Bouquet</option>
                <?php foreach ($bouquets as $b): ?>
                    <option value="<?= $b['bouquet_id'] ?>"
                        <?= $edit_mapping && $edit_mapping['bouquet_id']==$b['bouquet_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($b['bouquet_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <?php if($edit_mapping): ?>
            <input type="hidden" name="map_id" value="<?= $edit_mapping['map_id'] ?>">
            <div class="col-md-2 d-grid">
                <button type="submit" name="update" class="btn btn-primary">Update</button>
                <a href="mapping.php" class="btn btn-secondary mt-2">Cancel</a>
            </div>
        <?php else: ?>
            <div class="col-md-2 d-grid">
                <button type="submit" name="add" class="btn btn-success">Add</button>
            </div>
        <?php endif; ?>
    </form>
</div>

<!-- Table -->
<div class="card shadow-sm p-4">
    <h4>Existing Mappings</h4>
    <table class="table table-borderless mt-3">
        <thead>
            <tr>
                <th>Emotion</th>
                <th>Bouquet</th>
                <th width="180">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($mappings as $m): ?>
                <tr>
                    <td><?= htmlspecialchars($m['emotion_name']) ?></td>
                    <td><?= htmlspecialchars($m['bouquet_name']) ?></td>
                    <td>
                        <a href="?edit=<?= $m['map_id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                        <a href="?delete=<?= $m['map_id'] ?>" 
                           class="btn btn-sm btn-danger"
                           onclick="return confirm('Delete this mapping?')">
                           Delete
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

</div>

<script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
