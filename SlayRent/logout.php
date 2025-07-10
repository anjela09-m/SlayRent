<?php
session_start();
session_unset(); // Clear all session variables
session_destroy(); // End the session

// Redirect to homepage
header("Location: index.php");
exit();
