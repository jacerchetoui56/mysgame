<?php

// index.php

session_start();

require 'config.php';

// Check if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// get the given game to see its attemps history, the game_id is passed as a query string
if (isset($_GET['game_id'])) {
    // getting the game (the mys_number) to show it in the history page
    $query = "SELECT * FROM games WHERE id = {$_GET['game_id']}";
    $result = $conn->query($query);
    if (!($result->num_rows > 0)) {
        echo "<!DOCTYPE html>
        <html lang='en'>
        
        <head>
            <link href='https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css' rel='stylesheet'>
        
        </head>
        
        <body>
            <div class='container mt-5'>
                <div class='wrapper bg-light p-4 rounded'>
                    <!-- back button -->
                    <a href='index.php' class='btn btn-secondary mb-4'>Back</a>
                    <h2 class='h2 my-4'>Game {$_GET['game_id']} Does Not Exist </h2>
        
                </div>
            </div>
        </body>
        
        </html>";
        exit();
    }
    $game = $result->fetch_assoc();
    $mys_number = $game['mys_number'];
    $game_id = $_GET['game_id'];
    $query = "SELECT * FROM attemps WHERE game_id = $game_id";
    $result = $conn->query($query);
    $attemps = [];
    while ($row = $result->fetch_assoc()) {
        $attemps[] = $row;
    }
} else {
    echo "<h1>Jeu {$_GET['game_id']} n'existe pas</h1>";
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body>
    <div class="container mt-5">
        <div class="wrapper bg-light p-4 rounded">
            <!-- back button -->
            <a href="index.php" class="btn btn-secondary mb-4">Back</a>
            <h2 class="h2 my-4">History of game <?= $_GET["game_id"] ?> </h2>
            <ul>
                <?php foreach ($attemps as $attemp) : ?>
                    <!-- write "correct" in front of the correct one -->
                    <?php if ($attemp['value'] == $mys_number) : ?>
                        <li class="text-success"><?= $attemp['value'] ?> (correct)</li>
                    <?php else : ?>
                        <li> <?= $attemp['value'] ?></li>
                    <?php endif; ?>
                <?php endforeach; ?>

            </ul>
        </div>
    </div>
</body>

</html>