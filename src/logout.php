<?php
// logout.php
// Ends the user session and redirects to login page (index.php)

session_start();

// Remove all session variables
session_unset();

// Destroy the session completely
session_destroy();

// Redirect to the login page (index.php)
header("Location: index.php");
exit;
