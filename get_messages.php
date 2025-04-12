<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

// Initialize messages array if it doesn't exist
if (!isset($_SESSION['messages'])) {
    $_SESSION['messages'] = [];
}

echo json_encode([
    'success' => true,
    'messages' => $_SESSION['messages']
]);
?>