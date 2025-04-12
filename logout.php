<?php
session_start();

// Add system message about user leaving (if logged in)
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true && isset($_SESSION['username'])) {
    if (!isset($_SESSION['messages'])) {
        $_SESSION['messages'] = [];
    }
    
    $_SESSION['messages'][] = [
        'username' => 'System',
        'text' => $_SESSION['username'] . ' has left the chat',
        'time' => date('h:i A')
    ];
}

// Clear user session data
$_SESSION['logged_in'] = false;
$_SESSION['username'] = null;

// Redirect to login page
header('Location: login.php');
exit;
?>