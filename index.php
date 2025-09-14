<?php
include 'dbcon.php';

// Handle search
$search = "";
if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
}

// Pagination setup
$limit = 2; // posts per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Count total posts
$count_sql = "SELECT COUNT(*) as total FROM posts WHERE title LIKE ? OR content LIKE ?";
$stmt = $conn->prepare($count_sql);
$like = "%$search%";
$stmt->bind_param("ss", $like, $like);
$stmt->execute();
$total = $stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total / $limit);

// Fetch posts with search + pagination
$sql = "SELECT * FROM posts WHERE title LIKE ? OR content LIKE ? ORDER BY created_at DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssii", $like, $like, $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Posts with Search & Pagination</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<div class="container mt-4">
  <h2 class="mb-3">Posts</h2>

  <!-- Search Form -->
  <form method="get" class="mb-3 d-flex">
    <input type="text" name="search" class="form-control me-2" placeholder="Search posts..." value="<?= htmlspecialchars($search) ?>">
    <button type="submit" class="btn btn-primary">Search</button>
  </form>

 <!-- Posts List -->
<div class="list-group">
  <?php if ($result->num_rows > 0): ?>
    <?php while ($row = $result->fetch_assoc()): ?>
      <div class="list-group-item">
        <h5>
          <a href="post.php?id=<?= $row['id'] ?>" class="text-decoration-none">
            <?= htmlspecialchars($row['title']) ?>
          </a>
        </h5>
        <p><?= nl2br(htmlspecialchars(substr($row['content'], 0, 150))) ?>...</p>
        <small class="text-muted">Posted on <?= $row['created_at'] ?></small>

        <div class="mt-2">
          <a href="post.php?id=<?= $row['id'] ?>" class="btn btn-info btn-sm">View</a>
          <a href="edit_post.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
          <a href="delete.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm"
             onclick="return confirm('Are you sure you want to delete this post?');">Delete</a>
        </div>
      </div>
    <?php endwhile; ?>
  <?php else: ?>
    <p class="text-muted">No posts found.</p>
  <?php endif; ?>
</div>

<!-- Create New Post -->
<div class="mt-3">
  <a href="create_post.php" class="btn btn-success">+ New Post</a>
</div>


  <!-- Pagination -->
  <nav class="mt-3">
    <ul class="pagination">
      <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
          <a class="page-link" href="?search=<?= urlencode($search) ?>&page=<?= $i ?>"><?= $i ?></a>
        </li>
      <?php endfor; ?>
    </ul>
  </nav>
</div>

</body>
</html>
