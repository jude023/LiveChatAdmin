<?php
session_start();
require_once 'functions.php';

// Redirect if not logged in
if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit;
}

// Check if room_id is provided
if (!isset($_GET['room_id'])) {
    header('Location: index.php');
    exit;
}

$roomId = $_GET['room_id'];

// Handle user removal (for host only)
if (isset($_GET['remove_user']) && !empty($_GET['remove_user'])) {
    $userToRemove = $_GET['remove_user'];
    $currentUser = $_SESSION['username'];
    
    // Get room data
    $room = getRoom($roomId);
    
    // Check if current user is the host
    if ($room && $room['created_by'] === $currentUser) {
        // Don't allow removing the host
        if ($userToRemove !== $currentUser) {
            // Remove the user
            removeUserFromRoom($roomId, $userToRemove);
            
            // If the removed user is viewing this page, they will be redirected to index
            if ($userToRemove === $_SESSION['username']) {
                header('Location: index.php?removed=1');
                exit;
            }
        }
    }
    
    // Redirect back to the room
    header('Location: chat_room.php?room_id=' . $roomId);
    exit;
}

// Get room data from file storage
$room = getRoom($roomId);

// Check if room exists
if (!$room) {
    // For debugging
    echo "Room not found: " . $roomId;
    echo "<pre>";
    $data = loadChatData();
    print_r($data);
    echo "</pre>";
    exit;
}

$username = $_SESSION['username'];

// Check if user is a member of the room
if (!in_array($username, $room['members'])) {
    // If user was removed, redirect to index
    if (isset($_GET['removed'])) {
        header('Location: index.php?removed=1');
        exit;
    }
    
    // Add user to room if not already a member
    addUserToRoom($roomId, $username);
    // Refresh room data
    $room = getRoom($roomId);
}

// Handle new message
if (isset($_POST['send_message'])) {
    $messageText = trim($_POST['message']);
    
    if (!empty($messageText)) {
        // Add message to the room
        addMessage($roomId, $username, $messageText);
        
        // Redirect to prevent form resubmission
        header('Location: chat_room.php?room_id=' . $roomId);
        exit;
    }
}

// Check if user is the host
$isHost = ($room['created_by'] === $username);

// Reverse messages array to show newest messages at the top
$messages = !empty($room['messages']) ? array_reverse($room['messages']) : [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($room['name']); ?> - Live Chat Room</title>
    <link rel="stylesheet" href="styles.css">
    <meta http-equiv="refresh" content="5"> <!-- Auto-refresh every 5 seconds for "real-time" updates -->
    <style>
        .users-list li {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        
        .remove-user {
            color: #dc3545;
            cursor: pointer;
            font-size: 14px;
        }
        
        .remove-user:hover {
            text-decoration: underline;
        }
        
        .host-badge {
            background-color: #28a745;
            color: white;
            font-size: 10px;
            padding: 2px 5px;
            border-radius: 3px;
            margin-left: 5px;
        }
        
        /* Reverse chat display */
        .messages {
            display: flex;
            flex-direction: column-reverse;
            padding: 15px;
            overflow-y: auto;
            max-height: calc(70vh - 60px);
        }
        
        .message {
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 8px;
            max-width: 70%;
        }
        
        .message.sent {
            background-color: #dcf8c6;
            align-self: flex-end;
            margin-left: auto;
        }
        
        .message.received {
            background-color: #f1f0f0;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1><?php echo htmlspecialchars($room['name']); ?></h1>
            <div class="room-info">
                <p>Room Code: <?php echo htmlspecialchars($room['code']); ?></p>
                <a href="index.php" class="btn btn-primary">Back to Dashboard</a>
            </div>
        </header>

        <main>
            <div class="chat-container">
                <div class="users-panel">
                    <h3>Users in Room</h3>
                    <ul class="users-list">
                        <?php foreach ($room['members'] as $member): ?>
                            <li>
                                <div>
                                    <?php echo htmlspecialchars($member); ?>
                                    <?php if ($member === $room['created_by']): ?>
                                        <span class="host-badge">Host</span>
                                    <?php endif; ?>
                                </div>
                                <?php if ($isHost && $member !== $username): ?>
                                    <a href="?room_id=<?php echo $roomId; ?>&remove_user=<?php echo urlencode($member); ?>" 
                                       class="remove-user" 
                                       onclick="return confirm('Are you sure you want to remove <?php echo htmlspecialchars($member); ?> from the room?');">
                                        Remove
                                    </a>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php if ($isHost): ?>
                        <div class="invite-section">
                            <a href="invite.php?room_id=<?php echo $roomId; ?>" class="btn btn-success">Invite Users</a>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="chat-panel">
                    <div id="messages" class="messages">
                        <?php if (!empty($messages)): ?>
                            <?php foreach ($messages as $message): ?>
                                <div class="message <?php echo ($message['username'] === $username) ? 'sent' : 'received'; ?>">
                                    <div class="sender"><?php echo htmlspecialchars($message['username']); ?></div>
                                    <div class="content"><?php echo htmlspecialchars($message['text']); ?></div>
                                    <div class="time"><?php echo htmlspecialchars($message['timestamp']); ?></div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="no-messages">No messages yet. Start the conversation!</div>
                        <?php endif; ?>
                    </div>
                    
                    <form method="post" action="" class="message-input">
                        <input type="text" name="message" placeholder="Type your message..." required>
                        <button type="submit" name="send_message" class="btn btn-primary">Send</button>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Check if user was removed from the room
        function checkIfRemoved() {
            // Make an AJAX request to check if user is still in the room
            var xhr = new XMLHttpRequest();
            xhr.open('GET', 'check_membership.php?room_id=<?php echo $roomId; ?>', true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    var response = JSON.parse(xhr.responseText);
                    if (!response.isMember) {
                        // Redirect to index if user is no longer a member
                        window.location.href = 'index.php?removed=1';
                    }
                }
            };
            xhr.send();
        }
        
        // Check membership every 5 seconds
        setInterval(checkIfRemoved, 5000);
    </script>
</body>
</html>
