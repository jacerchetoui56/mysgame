<?php
// login.php

session_start();

require 'config.php';

// Check if user is already logged in
if (isset($_SESSION['user_id']) or isset($_COOKIE['user_id'])) {
    header("Location: index.php");
    exit();
}

// Process login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate form data


    // Sanitize input data
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    // Retrieve user from the database
    $query = "SELECT id, password FROM users WHERE username='$username'";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Verify password
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            setcookie('user_id', $row['id'], time() + (86400 * 30), "/"); // 30 days
            header("Location: index.php");
            exit();
        } else {
            $error = "Invalid password";
        }
    } else {
        $error = "User not found";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Guessing Game - Login</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .wrapper {
            width: 360px;
            padding: 20px;
            border: solid 1px #dfdfdf;
            border-radius: 1rem;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <h2>Login</h2>
        <?php if (isset($error)) : ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        <form class="form-group" method="post" action="">
            <label for="username">Username:</label>
            <input type="text" name="username" class="form-control" required><br>
            <label for="password">Password:</label>
            <input type="password" name="password" required class="form-control"><br>
            <input class="btn btn-primary" type="submit" value="Login">
        </form>
        <p>You don't have an account? <a href="register.php">Register here</a>.</p>
    </div>

</body>

</html>