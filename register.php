<?php
// register.php

session_start();

require 'config.php';

// Check if user is already logged in
if (isset($_SESSION['user_id']) or isset($_COOKIE['user_id'])) {
    header("Location: index.php");
    exit();
}

// Process registration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate form data
    if (empty($_POST['username'])) {
        $error = "Please provide a username";
    } elseif (empty($_POST['password'])) {
        $error = "Please provide a password";
    } elseif (empty($_POST['name'])) {
        $error = "Please provide a name";
    }
    // Sanitize input data
    $username = $conn->real_escape_string($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $name = $conn->real_escape_string($_POST['name']);

    // check if the user already exists
    $query = "SELECT id FROM users WHERE username='$username'";
    $result = $conn->query($query);
    if ($result->num_rows > 0) {
        $error = "User already exists";
    } else {
        // Insert user into the database
        $query = "INSERT INTO users (username, password, name) VALUES ('$username', '$password', '$name')";
        $conn->query($query);
        // Redirect to login page
        header("Location: login.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Mysterious Number - Registration</title>
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

        <h2>Register</h2>
        <?php if (isset($error)) : ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        <form class="form-group" method="post" action="">
            <label for="name">Name:</label>
            <input class="form-control" type="text" name="name" required><br>
            <label for="username">Username:</label>
            <input class="form-control" type="text" name="username" required><br>

            <label for="password">Password:</label>
            <input class="form-control" type="password" name="password" required><br>

            <input class="btn btn-primary" type="submit" value="Register">
        </form>
        <p>Already have an account? <a href="login.php">Login here</a>.</p>
    </div>

</body>

</html>