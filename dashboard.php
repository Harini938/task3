<?php
session_start();
include __DIR__ . '/dbcon.php';

// If user not logged in, redirect to login page
if (!isset($_SESSION['userid'])) {
    header("Location: login.php");
    exit();
}

$userid   = $_SESSION['userid'];
$username = $_SESSION['username'];
$userrole = $_SESSION['role'];

// ------------------ ADD / UPDATE / DELETE POST ------------------
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title   = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    // UPDATE
    if (isset($_POST['edit_id']) && $_POST['edit_id'] !== '') {
        $edit_id = (int)$_POST['edit_id'];

        if ($userrole == 'admin') {
            $stmt = $conn->prepare("UPDATE posts SET title=?, content=? WHERE id=?");
            $stmt->bind_param("ssi", $title, $content, $edit_id);
        } elseif ($userrole == 'editor') {
            $stmt = $conn->prepare("UPDATE posts SET title=?, content=? WHERE id=? AND user_id=?");
            $stmt->bind_param("ssii", $title, $content, $edit_id, $userid);
        }
        $stmt->execute();
        $stmt->close();
    }
    // DELETE
    elseif (isset($_POST['delete_id'])) {
        $delete_id = (int)$_POST['delete_id'];

        if ($userrole == 'admin') {
            $stmt = $conn->prepare("DELETE FROM posts WHERE id=?");
            $stmt->bind_param("i", $delete_id);
        } elseif ($userrole == 'editor') {
            $stmt = $conn->prepare("DELETE FROM posts WHERE id=? AND user_id=?");
            $stmt->bind_param("ii", $delete_id, $userid);
        }
        $stmt->execute();
        $stmt->close();
    }
    // ADD
    else {
        if ($userrole == 'admin' || $userrole == 'editor') {
            $stmt = $conn->prepare("INSERT INTO posts (title, content, user_id) VALUES (?, ?, ?)");
            $stmt->bind_param("ssi", $title, $content, $userid);
            $stmt->execute();
            $stmt->close();
        }
    }

    header("Location: dashboard.php");
    exit();
}

// ------------------ SEARCH & PAGINATION ------------------
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$limit  = 3;
$page   = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start  = ($page - 1) * $limit;

if ($search !== '') {
    $search_sql = "%{$search}%";
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM posts WHERE title LIKE ? OR content LIKE ?");
    $stmt->bind_param("ss", $search_sql, $search_sql);
    $stmt->execute();
    $result = $stmt->get_result();
    $totalPosts = $result->fetch_assoc()['total'];
    $stmt->close();

    $stmt = $conn->prepare("SELECT posts.*, users.username 
                            FROM posts 
                            JOIN users ON posts.user_id = users.id 
                            WHERE posts.title LIKE ? OR posts.content LIKE ?
                            ORDER BY posts.created_at DESC 
                            LIMIT ?, ?");
    $stmt->bind_param("ssii", $search_sql, $search_sql, $start, $limit);
    $stmt->execute();
    $posts = $stmt->get_result();
} else {
    $totalPostsResult = $conn->query("SELECT COUNT(*) as total FROM posts");
    $totalPosts = $totalPostsResult->fetch_assoc()['total'];

    $posts = $conn->query("SELECT posts.*, users.username 
                           FROM posts 
                           JOIN users ON posts.user_id = users.id 
                           ORDER BY posts.created_at DESC 
                           LIMIT $start, $limit");
}

$totalPages = ceil($totalPosts / $limit);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body.dark-mode { background-color: #121212; color: #e0e0e0; }
        .card.dark-mode { background-color: #1e1e1e; color: #e0e0e0; }
        .btn-toggle { position: fixed; top: 10px; right: 10px; z-index: 1000; }
    </style>
</head>
<body>
<button class="btn btn-secondary btn-toggle" onclick="toggleDarkMode()">Toggle Dark Mode</button>

<div class="container mt-5">
    <h3>Welcome, <?= htmlspecialchars($username) ?> (<?= htmlspecialchars($userrole) ?>) 
        <a href="logout.php" class="btn btn-danger btn-sm float-end">Logout</a>
    </h3>

    <!-- Search Form -->
    <form class="mt-3 mb-3" method="GET">
        <div class="input-group">
            <input type="text" name="search" class="form-control" placeholder="Search posts..." value="<?= htmlspecialchars($search) ?>">
            <button class="btn btn-primary" type="submit">Search</button>
        </div>
    </form>

    <!-- Add/Edit Post Form (Only Admin & Editor) -->
    <?php if ($userrole == 'admin' || $userrole == 'editor'): ?>
    <div class="card mt-4 p-4 shadow" id="addPostCard">
        <h5 id="formTitle">Add New Post</h5>
        <form method="POST" id="postForm">
            <input type="hidden" name="edit_id" id="edit_id">
            <div class="mb-3">
                <label>Title</label>
                <input type="text" name="title" id="title" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Content</label>
                <textarea name="content" id="content" class="form-control" rows="4" required></textarea>
            </div>
            <button type="submit" class="btn btn-success" id="submitBtn">Publish</button>
            <button type="button" class="btn btn-secondary" id="cancelEdit" style="display:none;" onclick="cancelEdit()">Cancel</button>
        </form>
    </div>
    <?php endif; ?>

    <!-- Posts List -->
    <h5 class="mt-5">All Posts</h5>
    <?php while($row = $posts->fetch_assoc()): ?>
        <div class="card mb-3 shadow-sm p-3" id="postCard">
            <h6><?= htmlspecialchars($row['title']) ?></h6>
            <p><?= nl2br(htmlspecialchars($row['content'])) ?></p>
            <small>By <?= htmlspecialchars($row['username']) ?> on <?= $row['created_at'] ?></small>
            
            <?php if($userrole == 'admin' || ($userrole == 'editor' && $row['user_id'] == $userid)): ?>
                <div class="mt-2">
                    <button class="btn btn-sm btn-primary" 
                        onclick="editPost(<?= $row['id'] ?>, '<?= addslashes($row['title']) ?>', '<?= addslashes($row['content']) ?>')">
                        Edit
                    </button>
                    <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this post?');">
                        <input type="hidden" name="delete_id" value="<?= $row['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    <?php endwhile; ?>

    <!-- Pagination -->
    <nav aria-label="Page navigation">
      <ul class="pagination">
        <?php if($page > 1): ?>
          <li class="page-item"><a class="page-link" href="?page=<?= $page-1 ?>&search=<?= urlencode($search) ?>">Previous</a></li>
        <?php endif; ?>

        <?php for($i=1; $i<=$totalPages; $i++): ?>
          <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
            <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
          </li>
        <?php endfor; ?>

        <?php if($page < $totalPages): ?>
          <li class="page-item"><a class="page-link" href="?page=<?= $page+1 ?>&search=<?= urlencode($search) ?>">Next</a></li>
        <?php endif; ?>
      </ul>
    </nav>
</div>

<script>
function toggleDarkMode() {
    document.body.classList.toggle('dark-mode');
    document.querySelectorAll('.card').forEach(card => card.classList.toggle('dark-mode'));
}

function editPost(id, title, content) {
    document.getElementById('edit_id').value = id;
    document.getElementById('title').value = title;
    document.getElementById('content').value = content;
    document.getElementById('formTitle').innerText = 'Edit Post';
    document.getElementById('submitBtn').innerText = 'Update';
    document.getElementById('cancelEdit').style.display = 'inline-block';
}

function cancelEdit() {
    document.getElementById('edit_id').value = '';
    document.getElementById('title').value = '';
    document.getElementById('content').value = '';
    document.getElementById('formTitle').innerText = 'Add New Post';
    document.getElementById('submitBtn').innerText = 'Publish';
    document.getElementById('cancelEdit').style.display = 'none';
}
</script>
</body>
</html>