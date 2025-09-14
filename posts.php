<?php
session_start();
if (!isset($_SESSION['user'])) { header("Location: login.php"); exit; }
include 'dbcon.php';
$result = $conn->query("SELECT * FROM posts ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Blog</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark px-3">
    <a class="navbar-brand" href="#">My Blog</a>
    <div class="ms-auto">
        <a href="create_post.php" class="btn btn-success me-2">+ New Post</a>
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>
</nav>

<div class="container mt-4">
<?php while ($row = $result->fetch_assoc()): ?>
    <div class="card mb-3 shadow-sm">
        <div class="card-body">
            <h5 class="card-title"><?= htmlspecialchars($row['title']) ?></h5>
            <p class="card-text"><?= nl2br(htmlspecialchars($row['content'])) ?></p>
            <small class="text-muted"><?= $row['created_at'] ?></small>
            <div class="mt-3">
                <a href="edit_post.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                <a href="delete_post.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this post?')">Delete</a>
            </div>
        </div>
    </div>
<?php endwhile; ?>
</div>
</body>
</html>