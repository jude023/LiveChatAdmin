<?php
session_start();
require_once 'functions.php';

// Redirect if not logged in
if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit;
}

// Initialize error message
$errorMsg = '';

// Check if code is provided in URL (from index.php)
if (isset($_GET['code'])) {
    $roomCode = $_GET['code'];
    $roomInfo = getRoomByCode($roomCode);
    
    if ($roomInfo) {
        $roomId = $roomInfo['id'];
        $username = $_SESSION['username'];
        
        // Add user to room
        addUserToRoom($roomId, $username);
        
        // Redirect to the room
        header('Location: chat_room.php?room_id=' . $roomId);
        exit;
    } else {
        $errorMsg = 'Room not found. Please check the code and try again.';
    }
}

// Handle room joining from form
if (isset($_POST['join_room'])) {
    $roomCode = trim($_POST['room_code']);
    
    if (!empty($roomCode)) {
        $roomInfo = getRoomByCode($roomCode);
        
        if ($roomInfo) {
            $roomId = $roomInfo['id'];
            $username = $_SESSION['username'];
            
            // Add user to room
            addUserToRoom($roomId, $username);
            
            // Redirect to the room
            header('Location: chat_room.php?room_id=' . $roomId);
            exit;
        } else {
            $errorMsg = 'Room not found. Please check the code and try again.';
        }
    } else {
        $errorMsg = 'Please enter a room code.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join Room - Live Chat Room</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Join a Chat Room</h1>
            <a href="index.php" class="btn btn-primary">Back to Dashboard</a>
        </header>

        <main>
            <div class="room-form">
                <?php if (!empty($errorMsg)): ?>
                    <div class="error-message"><?php echo $errorMsg; ?></div>
                <?php endif; ?>
                
                <form method="post" action="">
                    <div class="form-group">
                        <label for="room_code">Room Code:</label>
                        <input type="text" id="room_code" name="room_code" placeholder="Enter room code" required>
                    </div>
                    <button type="submit" name="join_room" class="btn btn-success">Join Room</button>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
