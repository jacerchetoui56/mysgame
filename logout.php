<?php
// logout.php

session_start();

// Destroy the current session
session_destroy();

// deleting the cookies
setcookie('user_id', '', time() - 3600, "/");

// Redirect to the login page
header("Location: login.php");

exit();
