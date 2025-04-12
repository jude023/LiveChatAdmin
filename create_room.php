<?php
session_start();
require_once 'functions.php';

// Redirect if not logged in
if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit;
}

// Handle room creation
if (isset($_POST['create_room'])) {
    $roomName = trim($_POST['room_name']);
    
    if (!empty($roomName)) {
        // Create room using the file storage function
        $roomId = createRoom($roomName, $_SESSION['username']);
        
        // Redirect to the room
        header('Location: chat_room.php?room_id=' . $roomId);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Room - Live Chat Room</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Create a New Chat Room</h1>
            <a href="index.php" class="btn btn-primary">Back to Dashboard</a>
        </header>

        <main>
            <div class="room-form">
                <form method="post" action="">
                    <div class="form-group">
                        <label for="room_name">Room Name:</label>
                        <input type="text" id="room_name" name="room_name" placeholder="Enter room name" required>
                    </div>
                    <button type="submit" name="create_room" class="btn btn-success">Create Room</button>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
