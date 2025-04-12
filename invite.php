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

// Check if user is the room creator
if ($room['created_by'] !== $username) {
    header('Location: chat_room.php?room_id=' . $roomId);
    exit;
}

// Get the room code for invitation
$roomCode = $room['code'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invite Users - <?php echo htmlspecialchars($room['name']); ?></title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .copy-success {
            color: green;
            margin-top: 10px;
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Invite Users to <?php echo htmlspecialchars($room['name']); ?></h1>
            <a href="chat_room.php?room_id=<?php echo $roomId; ?>" class="btn btn-primary">Back to Chat</a>
        </header>

        <main>
            <div class="invite-container">
                <p>Share this room code with others to invite them to join your chat room:</p>
                <div class="room-code">
                    <span id="roomCode"><?php echo htmlspecialchars($roomCode); ?></span>
                    <button onclick="copyRoomCode()" class="btn btn-primary">Copy Code</button>
                </div>
                <p id="copySuccess" class="copy-success">Code copied to clipboard!</p>
                
                <p>Users can join by entering this code on the "Join Room" page.</p>
                
                <h3>Current Members</h3>
                <ul class="users-list">
                    <?php foreach ($room['members'] as $member): ?>
                        <li><?php echo htmlspecialchars($member); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </main>
    </div>

    <script>
        function copyRoomCode() {
            var roomCode = document.getElementById("roomCode");
            var textArea = document.createElement("textarea");
            textArea.value = roomCode.textContent;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand("Copy");
            textArea.remove();
            
            var copySuccess = document.getElementById("copySuccess");
            copySuccess.style.display = "block";
            
            setTimeout(function() {
                copySuccess.style.display = "none";
            }, 2000);
        }
    </script>
</body>
</html>
