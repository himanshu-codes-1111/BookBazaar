<?php
session_start();

// Destroy the session to log the user out
session_unset();
session_destroy();

// Redirect to homepage or login page after logout
header("Location: ../home.html");
exit();
?>
