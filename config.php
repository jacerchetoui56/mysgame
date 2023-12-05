<?php 


    // Connect to the database (replace these values with your database credentials)
    $conn = new mysqli("localhost", "root", "", "game");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }