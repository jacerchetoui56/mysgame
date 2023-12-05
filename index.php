<?php
// index.php

session_start();

require 'config.php';

if (isset($_COOKIE['user_id'])) {
    $_SESSION['user_id'] = $_COOKIE['user_id'];
}

// Check if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $user_id = $_SESSION['user_id'] ?? $_COOKIE['user_id'];
    // getting the user name
    $query = "SELECT name FROM users WHERE id = {$user_id}";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    $_SESSION['username'] = $row['name'];
    // checking if the user has an unfinished game in the games table
    $unfinished_game_query = "SELECT * FROM games WHERE user_id = {$user_id} AND finished = 0";
    $unfinished_game_result = $conn->query($unfinished_game_query);
    if ($unfinished_game_result->num_rows > 0) {
        $row = $unfinished_game_result->fetch_assoc();
        // we have an unfinished game
        $mys_number = $row['mys_number'];
        $attemps = $row['attemps'];
        $game_id = $row['id'];
        $_SESSION['game_id'] = $game_id;
        $_SESSION['mys_number'] = $mys_number;
        $_SESSION['tries'] = $attemps;
    } else {
        // Initialize the number to guess if not set in the session
        // creating a new game in the games table
        $mys_number = rand(1, 100);
        $attemps = 0;
        $user_id = $_SESSION['user_id'];
        $query = "INSERT INTO games (user_id, mys_number, attemps) VALUES ('$user_id', '$mys_number', '$attemps')";
        $conn->query($query);
        $game_id = $conn->insert_id;
        $_SESSION['game_id'] = $game_id;
        $_SESSION['mys_number'] = $mys_number;
        $_SESSION['tries'] = $attemps;
    }
}

// Process guess
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    unset($_SESSION["message"]);
    $guess = $_POST['guess'];
    // Increment number of tries
    $_SESSION['tries']++;
    $sql = "UPDATE games SET attemps = attemps + 1 WHERE id = {$_SESSION['game_id']}";
    $conn->query($sql);
    // create an attemp in the attemps table
    $game_id = $_SESSION['game_id'];
    $query = "INSERT INTO attemps (game_id, value, user_id) VALUES ('$game_id', '$guess', '{$_SESSION['user_id']}')";
    $conn->query($query);

    $number_to_guess = $_SESSION['mys_number'];

    // Check if guess is correct
    if ($guess === $number_to_guess) {
        // Update high scores in the database
        $user_id = $_SESSION['user_id'];
        $score = $_SESSION['tries'];

        // considered the game finished
        $sql = "UPDATE games SET finished = 1 WHERE id = {$_SESSION['game_id']}";
        $conn->query($sql);

        // Reset the game
        unset($_SESSION['number_to_guess']);
        unset($_SESSION['tries']);
        unset($_SESSION['game_id']);
        unset($_SESSION['mys_number']);
        $_SESSION['message'] = "Congratulations! You guessed the correct number in $score attemps.";
        $_SESSION["win"] = 'true';
        header("Location: index.php");
        exit();
    } else {
        unset($_SESSION["win"]);
        $message = ($guess < $number_to_guess) ? "Try a higher number." : "Try a lower number.";
        $_SESSION['message'] = $message;
        header("Location: index.php");
        exit();
    }
}

$highscores_query = "SELECT games.id, users.name, MIN(games.attemps) as min_score, games.game_date as date FROM games INNER JOIN users ON games.user_id = users.id WHERE games.finished = 1 GROUP BY users.username ORDER BY min_score ASC LIMIT 8";
$highscores_result = $conn->query($highscores_query);

$user_history_query = "SELECT * FROM games WHERE user_id = {$_SESSION['user_id']} AND finished = 1 ORDER BY game_date DESC LIMIT 10";
$user_history_result = $conn->query($user_history_query);

// retrieving the attemps from the attemps table
$current_attemps_query =  "SELECT * FROM attemps WHERE game_id = {$_SESSION['game_id']} ORDER BY id DESC";
$current_attemps_result = $conn->query($current_attemps_query);
$attemps_number = $current_attemps_result->num_rows;
$attemps_list = [];
while ($row = $current_attemps_result->fetch_assoc()) {
    $attemps_list[] = $row;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Mysterious Number</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .wrapper {
            width: 360px;
            padding: 20px;
        }

        .history h3 {
            cursor: pointer;
            user-select: none;
        }

        .history ul {
            display: none;
        }

        .history.show ul {
            display: block;
        }

        .history h3 i {
            transform: rotate(90deg);
        }

        .history h3 img {
            width: 20px;
            height: 20px;
            margin-left: 10px;
            transition: transform 0.15s ease-in-out;
        }

        .history h3 img:first-child {
            width: 35px;
            height: 35px;
            margin-right: 10px;
        }

        .history.show h3 img:nth-child(2) {
            transform: rotate(90deg);
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

</head>

<body>
    <div class="container mt-5">
        <div class="wrapper w-100 bg-light p-4 rounded">
            <div class="d-flex justify-content-between align-items-center border-bottom pb-1">
                <h5><?= $_SESSION['username'] ?></h5>
                <a class="btn btn-danger mt-2" href="logout.php" class="mt-3">Logout</a>
            </div>
            <h2 class="h2 my-4 text-center">Mysterious Number</h2>
            <form method="post" action="" class="mt-4">
                <h5>Number of attemps: <?= $attemps ?></h5>
                <div class="form-group">
                    <label for="guess">Guess the number:</label>
                    <input type="number" class="form-control" name="guess" required>
                </div>
                <input type="submit" class="btn btn-primary" value="Guess">
            </form>
            <?php
            // Display messages
            if (isset($_SESSION['message'])) {
                if (isset($_SESSION["win"])) {
                    echo "<p class='alert alert-success mt-3'>{$_SESSION['message']}</p>";
                } else {
                    echo "<p class='alert alert-info  mt-3'>{$_SESSION['message']}</p>";
                }
                unset($_SESSION['message']);
            } else if ($attemps > 0) {
                echo "<p class='alert alert-warning mt-3'>You have an unfinished game!</p>";
            }
            ?>
            <?php if (count($attemps_list) > 0) : ?>
                <h5>Current guess attemps:</h5>
                <ul class=''>
                    <?php foreach ($attemps_list as $attemp) : ?>
                        <li><?= $attemp['value'] ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <hr>
            <?php
            // Display high scores from the database
            if ($highscores_result->num_rows > 0) {
                echo "<h4>Leaderboard</h4>";
                echo "<ul class='list-group bg-light border-primary'>";
                while ($row = $highscores_result->fetch_assoc()) {
                    echo "<li class='list-group-item'>{$row['name']} - 
                        <a href='attemps_history.php?game_id={$row['id']}'>{$row['min_score']} attemps</a>
                    - {$row['date']}</li>";
                }
                echo "</ul>";
            }
            ?>
            <hr>

            <div class="history">
                <h3><img src="history.svg" alt="">History <img src="chevron.svg" alt=""></h3>
                <?php
                // Display user history from the database
                if ($user_history_result->num_rows > 0) {
                    echo "<ul>";
                    while ($row = $user_history_result->fetch_assoc()) {
                        echo "<li>
                        <a href='attemps_history.php?game_id={$row['id']}'>Game {$row['id']}</a> - {$row['attemps']} attemps - {$row['game_date']}
                        </li>";
                    }
                    echo "</ul>";
                }
                ?>

            </div>
        </div>
    </div>

    <script>
        console.log("hello")
        const history = document.querySelector('.history');
        const historyBtn = document.querySelector('.history h3');
        historyBtn.addEventListener('click', () => {
            history.classList.toggle('show');
        })
    </script>
</body>


</html>