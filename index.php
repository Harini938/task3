<?php

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Blog - Home</title>

   
    <link rel="stylesheet" href="style.css">

  
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand" href="#">My Blog</a>
            <div class="d-flex">
                <a href="login.php" class="btn btn-outline-primary me-2">Login</a>
                <a href="register.php" class="btn btn-primary">Register</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        
        <input type="text" id="searchInput" class="form-control mb-4" placeholder="🔍 Search posts...">

        <div class="row">
            <div class="col-md-4">
                <div class="card p-3 mb-3">
                    <h5>First Post</h5>
                    <p>This is the first blog post content.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-3 mb-3">
                    <h5>Second Post</h5>
                    <p>This is another example of a blog post content.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-3 mb-3">
                    <h5>Third Post</h5>
                    <p>Some more content goes here for testing search and dark mode.</p>
                </div>
            </div>
        </div>

      
        <nav>
            <ul class="pagination justify-content-center mt-4">
                <li class="page-item"><a class="page-link" href="?page=1">1</a></li>
                <li class="page-item"><a class="page-link" href="?page=2">2</a></li>
                <li class="page-item"><a class="page-link" href="?page=3">3</a></li>
            </ul>
        </nav>
    </div>

   
    <script src="script.js"></script>
</body>
</html>


