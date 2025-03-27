<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Authentication System</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<?php
session_start();

// Database configuration
$host = 'localhost';
$dbname = 'paradisesoft_net2025';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create users table if it doesn't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Handle registration
if (isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->execute([$username, $password]);
        $success = "Registration successful! Please login.";
    } catch (PDOException $e) {
        $error = "Username already exists!";
    }
}

// Handle login
if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
    } else {
        $error = "Invalid credentials!";
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

// Show appropriate content based on user state
if (isset($_SESSION['user_id'])) {
    // Dashboard
    ?>
    <div class="container dashboard">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
        <div class="dashboard-content">
            <p>This is your personal dashboard.</p>
            <button onclick="window.location.href='index.php?logout=1'">Logout</button>
        </div>
    </div>
    <?php
} else {
    // Show login/registration form
    ?>
    <div class="container">
        <h1><?php echo isset($_GET['register']) ? 'Register' : 'Login'; ?></h1>
        
        <?php if (isset($error)) { ?>
            <div class="error"><?php echo $error; ?></div>
        <?php } ?>
        <?php if (isset($success)) { ?>
            <div class="success"><?php echo $success; ?></div>
        <?php } ?>

        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" name="<?php echo isset($_GET['register']) ? 'register' : 'login'; ?>">
                <?php echo isset($_GET['register']) ? 'Register' : 'Login'; ?>
            </button>
        </form>
        
        <div class="link">
            <a href="index.php<?php echo isset($_GET['register']) ? '' : '?register=1'; ?>">
                <?php echo isset($_GET['register']) ? 'Already have an account? Login' : 'Need an account? Register'; ?>
            </a>
        </div>
    </div>
    <?php
}
?>
</body>
</html>