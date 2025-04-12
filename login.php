<?php
session_start();

// If already logged in, redirect to room selection
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    // If user is already in a room, go to chat
    if (isset($_SESSION['current_room'])) {
        header('Location: index.php');
    } else {
        header('Location: join_room.php');
    }
    exit;
}

$error = '';

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    
    if (empty($username)) {
        $error = 'Please enter a username';
    } else {
        // Simple login - just store the username
        $_SESSION['username'] = htmlspecialchars($username);
        $_SESSION['logged_in'] = true;
        
        // Initialize rooms array if it doesn't exist
        if (!isset($_SESSION['rooms'])) {
            $_SESSION['rooms'] = [];
        }
        
        // Redirect to room selection
        header('Location: join_room.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Real-time Chat</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-6 offset-md-3">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-center">Join Chat</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <form method="post" action="">
                            <div class="mb-3">
                                <label for="username" class="form-label">Choose a Username</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Join Chat</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>