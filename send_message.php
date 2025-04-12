<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

// Check if message is provided
if (!isset($_POST['message']) || empty($_POST['message'])) {
    echo json_encode(['success' => false, 'message' => 'No message provided']);
    exit;
}

// Initialize messages array if it doesn't exist
if (!isset($_SESSION['messages'])) {
    $_SESSION['messages'] = [];
}

// Get current time
$time = date('h:i A');

// Add message to session
$_SESSION['messages'][] = [
    'username' => $_SESSION['username'],
    'text' => htmlspecialchars($_POST['message']),
    'time' => $time
];

// Limit the number of messages stored (optional)
if (count($_SESSION['messages']) > 100) {
    array_shift($_SESSION['messages']); // Remove oldest message
}

echo json_encode(['success' => true]);
?>