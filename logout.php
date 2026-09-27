<?php

// Start the session
session_start();

// Clear all session variables
session_unset();

// Destroy the current session
session_destroy();

// Redirect the admin back to the login page
header("Location: index.php");
exit();

?>