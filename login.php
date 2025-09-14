<?php
session_start();
include __DIR__. '/dbcon.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $uname = trim($_POST['username']);
    $upass = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, password, role FROM users WHERE username=?");
    $stmt->bind_param("s", $uname);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($upass, $user['password'])) {
            $_SESSION['userid'] = $user['id'];
            $_SESSION['username'] = $uname;
            $_SESSION['role'] = $user['role'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "User not found!";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card shadow-lg p-4 rounded">
        <h3 class="text-center mb-4">Login</h3>
        <?php if(isset($error)) echo "<div class='alert alert-danger text-center'>$error</div>"; ?>

        <form method="POST">
            <div class="mb-3">
                <label>Username</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>
        <p class="text-center mt-3">Not registered? <a href="register.php">Register here</a></p>
    </div>
</div>
</body>
</html>