<?php
require_once "../config/db.php";
require_once "../functions/helpers.php";
requireAdmin();

// Add Bouquet
if (isset($_POST['add'])) {
    $name = trim($_POST['bouquet_name']);
    $desc = trim($_POST['description']);
    $floriography = trim($_POST['floriography_meaning']);
    $image = $_FILES['bouquet_image']['name'];

    $target = "uploads/" . basename($image);

    if (!empty($name)) {
        $stmt = $conn->prepare("INSERT INTO bouquets (bouquet_name, description, floriography_meaning, image) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $desc, $floriography, $image);
        $stmt->execute();
        $stmt->close();

        if (!empty($image)) {
            move_uploaded_file($_FILES['bouquet_image']['tmp_name'], $target);
        }
    }
}

// Update Bouquet
if (isset($_POST['update'])) {
    $id = intval($_POST['bouquet_id']);
    $name = trim($_POST['bouquet_name']);
    $desc = trim($_POST['description']);
    $floriography = trim($_POST['floriography_meaning']);
    $image = $_FILES['bouquet_image']['name'];

    if (!empty($name)) {
        if (!empty($image)) {
            $target = "uploads/" . basename($image);
            move_uploaded_file($_FILES['bouquet_image']['tmp_name'], $target);
            $stmt = $conn->prepare("UPDATE bouquets SET bouquet_name=?, description=?, floriography_meaning=?, image=? WHERE bouquet_id=?");
            $stmt->bind_param("ssssi", $name, $desc, $floriography, $image, $id);
        } else {
            $stmt = $conn->prepare("UPDATE bouquets SET bouquet_name=?, description=?, floriography_meaning=? WHERE bouquet_id=?");
            $stmt->bind_param("sssi", $name, $desc, $floriography, $id);
        }
        $stmt->execute();
        $stmt->close();
    }
}

// Delete Bouquet
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM bouquets WHERE bouquet_id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    redirect("bouquets.php");
}

$bouquets = $conn->query("SELECT * FROM bouquets ORDER BY bouquet_name ASC")->fetch_all(MYSQLI_ASSOC);

$edit_bouquet = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $edit_bouquet = $conn->query("SELECT * FROM bouquets WHERE bouquet_id=$id")->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Bouquets</title>
<link href="../assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">

<style>
body { background: #f7f5fb; }
.card {
    border-radius: 16px;
    border: none;
}
.navbar {
    background: #e8dff5 !important;
}
.btn-primary {
    background: #cdb4db;
    border: none;
}
.btn-success {
    background: #bde0fe;
    border: none;
    color: #000;
}
img {
    border-radius: 10px;
}
</style>
</head>

<body>

<nav class="navbar navbar-light mb-4">
    <div class="container">
        <span class="navbar-brand fw-bold">Manage Bouquets</span>
        <a href="dashboard.php" class="btn btn-outline-dark btn-sm">Dashboard</a>
    </div>
</nav>

<div class="container">

<!-- Form -->
<div class="card shadow-sm mb-4 p-4">
    <h4><?= $edit_bouquet ? "Edit Bouquet" : "Add New Bouquet" ?></h4>
    <form method="POST" enctype="multipart/form-data" class="row g-3 mt-2">

        <div class="col-md-6">
            <input type="text" name="bouquet_name" class="form-control" required
                placeholder="Bouquet Name"
                value="<?= $edit_bouquet ? htmlspecialchars($edit_bouquet['bouquet_name']) : '' ?>">
        </div>

        <div class="col-md-6">
            <input type="file" name="bouquet_image" class="form-control">
        </div>

        <div class="col-12">
            <textarea name="description" class="form-control" rows="2"
                placeholder="Description"><?= $edit_bouquet ? htmlspecialchars($edit_bouquet['description']) : '' ?></textarea>
        </div>

        <div class="col-12">
            <input type="text" name="floriography_meaning" class="form-control"
                placeholder="Floriography Meaning"
                value="<?= $edit_bouquet ? htmlspecialchars($edit_bouquet['floriography_meaning']) : '' ?>">
        </div>

        <?php if($edit_bouquet): ?>
            <input type="hidden" name="bouquet_id" value="<?= $edit_bouquet['bouquet_id'] ?>">
            <div class="col-12 d-grid">
                <button type="submit" name="update" class="btn btn-primary">Update</button>
                <a href="bouquets.php" class="btn btn-secondary mt-2">Cancel</a>
            </div>
        <?php else: ?>
            <div class="col-12 d-grid">
                <button type="submit" name="add" class="btn btn-success">Add Bouquet</button>
            </div>
        <?php endif; ?>
    </form>
</div>

<!-- Table -->
<div class="card shadow-sm p-4">
    <h4>Bouquet List</h4>
    <table class="table table-borderless mt-3 align-middle">
        <thead>
            <tr>
                <th>Name</th>
                <th>Description</th>
                <th>Floriography</th>
                <th>Image</th>
                <th width="180">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($bouquets as $b): ?>
                <tr>
                    <td><?= htmlspecialchars($b['bouquet_name']) ?></td>
                    <td><?= htmlspecialchars($b['description']) ?></td>
                    <td><?= htmlspecialchars($b['floriography_meaning']) ?></td>
                    <td>
                        <?php if(!empty($b['image'])): ?>
                            <img src="uploads/<?= $b['image'] ?>" width="70">
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="?edit=<?= $b['bouquet_id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                        <a href="?delete=<?= $b['bouquet_id'] ?>" 
                           class="btn btn-sm btn-danger"
                           onclick="return confirm('Delete this bouquet?')">
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
