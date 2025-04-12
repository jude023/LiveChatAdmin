<?php
session_start();
require_once 'functions.php';

// Check if user is logged in
$loggedIn = isset($_SESSION['username']);

// Handle logout
if (isset($_GET['logout'])) {
    unset($_SESSION['username']);
    header('Location: index.php');
    exit;
}

// Handle login
if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    
    if (!empty($username)) {
        // Save user to file storage
        saveUser($username);
        
        // Set current user in session
        $_SESSION['username'] = $username;
        header('Location: index.php');
        exit;
    }
}

// Load all chat data
$chatData = loadChatData();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Chat Room</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Live Chat Room</h1>
            <?php if($loggedIn): ?>
                <div class="user-info">
                    <p>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></p>
                    <a href="?logout=1" class="btn btn-danger">Logout</a>
                </div>
            <?php endif; ?>
        </header>

        <main>
            <?php if(!$loggedIn): ?>
                <!-- Login Form -->
                <div class="login-form">
                    <h2>Enter Your Username</h2>
                    <form method="post" action="">
                        <div class="form-group">
                            <input type="text" name="username" placeholder="Username" required>
                        </div>
                        <button type="submit" name="login" class="btn btn-primary">Enter Chat</button>
                    </form>
                </div>
            <?php else: ?>
                <!-- Dashboard -->
                <div class="dashboard">
                    <div class="room-actions">
                        <h2>Chat Rooms</h2>
                        <div class="action-buttons">
                            <a href="create_room.php" class="btn btn-primary">Create Room</a>
                            <a href="join_room.php" class="btn btn-success">Join Room</a>
                        </div>
                    </div>

                    <div class="my-rooms">
                        <h3>Available Rooms</h3>
                        <ul class="room-list">
                            <?php
                            // Display rooms where user is a member
                            $username = $_SESSION['username'];
                            $userRooms = [];
                            
                            if (!empty($chatData['rooms'])) {
                                foreach ($chatData['rooms'] as $roomId => $room) {
                                    // Check if user is a member of this room
                                    if (isset($room['members']) && is_array($room['members']) && in_array($username, $room['members'])) {
                                        $userRooms[$roomId] = $room;
                                    }
                                }
                            }
                            
                            if (!empty($userRooms)) {
                                foreach ($userRooms as $roomId => $room) {
                                    echo '<li class="room-item">';
                                    echo '<div class="room-info">';
                                    echo '<h4>' . htmlspecialchars($room['name']) . '</h4>';
                                    echo '<p>Created by: ' . htmlspecialchars($room['created_by']) . '</p>';
                                    echo '<p>Room Code: ' . htmlspecialchars($room['code']) . '</p>';
                                    echo '</div>';
                                    echo '<div class="room-actions">';
                                    echo '<a href="chat_room.php?room_id=' . $roomId . '" class="btn btn-primary">Enter</a>';
                                    if ($room['created_by'] === $username) {
                                        echo '<a href="invite.php?room_id=' . $roomId . '" class="btn btn-success">Invite</a>';
                                    }
                                    echo '</div>';
                                    echo '</li>';
                                }
                            } else {
                                echo '<li class="no-rooms">You are not a member of any rooms yet.</li>';
                            }
                            ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
