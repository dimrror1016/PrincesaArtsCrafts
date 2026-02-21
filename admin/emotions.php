<?php
require_once "../config/db.php";
require_once "../functions/helpers.php";

if (!isset($_SESSION['admin_id'])) {
    redirect("login.php");
}

// Add emotion safely
if (isset($_POST['add'])) {
    $name = trim($_POST['emotion_name']);
    if ($name !== '') {
        $stmt = $conn->prepare("INSERT INTO emotions (emotion_name) VALUES (?)");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $stmt->close();
    }
}

// Delete emotion
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM emotions WHERE emotion_id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

// Fetch emotions
$emotions = $conn->query("SELECT * FROM emotions ORDER BY emotion_name ASC")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Emotions</title>
    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
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
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="#">Princesa Arts & Crafts</a>
            <div class="d-flex">
                <a href="../functions/logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">

        <div class="card shadow mb-4 p-4">
            <h3 class="mb-3">Add New Emotion</h3>
            <form method="POST" class="row g-3">
                <div class="col-md-6">
                    <input type="text" name="emotion_name" class="form-control" placeholder="Emotion Name" required>
                </div>
                <div class="col-md-6 d-grid">
                    <button type="submit" name="add" class="btn btn-success">Add Emotion</button>
                </div>
            </form>
        </div>

        <div class="card shadow p-4">
            <h3 class="mb-3">Existing Emotions</h3>
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Emotion</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($emotions as $e): ?>
                        <tr>
                            <td><?= $e['emotion_id'] ?></td>
                            <td><?= htmlspecialchars($e['emotion_name']) ?></td>
                            <td>
                                <a href="?delete=<?= $e['emotion_id'] ?>" class="btn btn-danger btn-sm"
                                    onclick="return confirm('Delete this emotion?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($emotions)): ?>
                        <tr>
                            <td colspan="3" class="text-center">No emotions added yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

    <script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>

</html>