<?php
// File to store all chat data
define('DATA_FILE', 'chat_data.json');

/**
 * Load all chat data from file
 */
function loadChatData() {
    if (file_exists(DATA_FILE)) {
        $jsonData = file_get_contents(DATA_FILE);
        $data = json_decode($jsonData, true);
        if (is_array($data)) {
            return $data;
        }
    }
    
    // Return default structure if file doesn't exist or is invalid
    return [
        'users' => [],
        'rooms' => []
    ];
}

/**
 * Save all chat data to file
 */
function saveChatData($data) {
    $jsonData = json_encode($data, JSON_PRETTY_PRINT);
    file_put_contents(DATA_FILE, $jsonData, LOCK_EX);
}

/**
 * Get user by username
 */
function getUser($username) {
    $data = loadChatData();
    
    foreach ($data['users'] as $user) {
        if ($user['username'] === $username) {
            return $user;
        }
    }
    
    return null;
}

/**
 * Add or update user
 */
function saveUser($username) {
    $data = loadChatData();
    
    // Check if user already exists
    $userExists = false;
    foreach ($data['users'] as &$user) {
        if ($user['username'] === $username) {
            $userExists = true;
            $user['last_active'] = date('Y-m-d H:i:s');
            break;
        }
    }
    
    // Add new user if doesn't exist
    if (!$userExists) {
        $data['users'][] = [
            'username' => $username,
            'created_at' => date('Y-m-d H:i:s'),
            'last_active' => date('Y-m-d H:i:s')
        ];
    }
    
    saveChatData($data);
}

/**
 * Get room by ID
 */
function getRoom($roomId) {
    $data = loadChatData();
    
    if (isset($data['rooms'][$roomId])) {
        return $data['rooms'][$roomId];
    }
    
    return null;
}

/**
 * Get room by code
 */
function getRoomByCode($roomCode) {
    $data = loadChatData();
    
    foreach ($data['rooms'] as $roomId => $room) {
        if (isset($room['code']) && strtoupper($room['code']) === strtoupper($roomCode)) {
            return [
                'id' => $roomId,
                'room' => $room
            ];
        }
    }
    
    return null;
}

/**
 * Create a new room
 */
function createRoom($roomName, $createdBy) {
    $data = loadChatData();
    
    // Generate a unique room code
    $roomCode = generateRoomCode();
    
    // Generate a unique room ID
    $roomId = 'room_' . uniqid();
    
    // Create the room
    $data['rooms'][$roomId] = [
        'name' => $roomName,
        'code' => $roomCode,
        'created_by' => $createdBy,
        'created_at' => date('Y-m-d H:i:s'),
        'members' => [$createdBy],
        'banned_members' => [], // Add banned members array
        'messages' => []
    ];
    
    saveChatData($data);
    
    return $roomId;
}

/**
 * Add user to room
 */
function addUserToRoom($roomId, $username) {
    $data = loadChatData();
    
    if (isset($data['rooms'][$roomId])) {
        // Check if user is banned
        if (isset($data['rooms'][$roomId]['banned_members']) && 
            in_array($username, $data['rooms'][$roomId]['banned_members'])) {
            return false; // User is banned, cannot join
        }
        
        // Check if user is already a member
        if (!in_array($username, $data['rooms'][$roomId]['members'])) {
            // Add user to room members
            $data['rooms'][$roomId]['members'][] = $username;
            
            // Add system message about new user
            $data['rooms'][$roomId]['messages'][] = [
                'username' => 'System',
                'text' => $username . ' has joined the room.',
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
            saveChatData($data);
            return true;
        }
    }
    
    return false;
}

/**
 * Remove user from room and ban them
 */
function removeUserFromRoom($roomId, $username) {
    $data = loadChatData();
    
    if (isset($data['rooms'][$roomId])) {
        // Find user in members array
        $key = array_search($username, $data['rooms'][$roomId]['members']);
        
        if ($key !== false) {
            // Remove user from members array
            unset($data['rooms'][$roomId]['members'][$key]);
            
            // Re-index the array
            $data['rooms'][$roomId]['members'] = array_values($data['rooms'][$roomId]['members']);
            
            // Add user to banned members
            if (!isset($data['rooms'][$roomId]['banned_members'])) {
                $data['rooms'][$roomId]['banned_members'] = [];
            }
            
            if (!in_array($username, $data['rooms'][$roomId]['banned_members'])) {
                $data['rooms'][$roomId]['banned_members'][] = $username;
            }
            
            // Add system message about user removal
            $data['rooms'][$roomId]['messages'][] = [
                'username' => 'System',
                'text' => $username . ' has been removed from the room.',
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
            saveChatData($data);
            return true;
        }
    }
    
    return false;
}

/**
 * Check if user is banned from room
 */
function isUserBanned($roomId, $username) {
    $data = loadChatData();
    
    if (isset($data['rooms'][$roomId]) && 
        isset($data['rooms'][$roomId]['banned_members'])) {
        return in_array($username, $data['rooms'][$roomId]['banned_members']);
    }
    
    return false;
}

/**
 * Unban user from room
 */
function unbanUserFromRoom($roomId, $username) {
    $data = loadChatData();
    
    if (isset($data['rooms'][$roomId]) && 
        isset($data['rooms'][$roomId]['banned_members'])) {
        
        $key = array_search($username, $data['rooms'][$roomId]['banned_members']);
        
        if ($key !== false) {
            // Remove user from banned members
            unset($data['rooms'][$roomId]['banned_members'][$key]);
            
            // Re-index the array
            $data['rooms'][$roomId]['banned_members'] = array_values($data['rooms'][$roomId]['banned_members']);
            
            saveChatData($data);
            return true;
        }
    }
    
    return false;
}

/**
 * Add message to room
 */
function addMessage($roomId, $username, $message) {
    $data = loadChatData();
    
    if (isset($data['rooms'][$roomId])) {
        // Add message to the room
        $data['rooms'][$roomId]['messages'][] = [
            'username' => $username,
            'text' => $message,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        saveChatData($data);
        return true;
    }
    
    return false;
}

/**
 * Get rooms where user is a member
 */
function getUserRooms($username) {
    $data = loadChatData();
    $userRooms = [];
    
    foreach ($data['rooms'] as $roomId => $room) {
        if (in_array($username, $room['members'])) {
            $userRooms[$roomId] = $room;
        }
    }
    
    return $userRooms;
}

/**
 * Generate a random room code
 */
function generateRoomCode($length = 6) {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $code = '';
    
    for ($i = 0; $i < $length; $i++) {
        $code .= $characters[rand(0, strlen($characters) - 1)];
    }
    
    return $code;
}
?>