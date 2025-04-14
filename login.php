<?php
// Include database connection and auth utilities
require 'connect.php';
require 'auth.php'; // Ensure session is started

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare('SELECT id, password, name, isAdmin FROM user WHERE LOWER(email) = LOWER(?)');
    $stmt->execute([$_POST['email']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        $passwordMatch = password_verify($_POST['password'], $user['password']);
        error_log("Password Match: " . ($passwordMatch ? "Yes" : "No") . " | Input: {$_POST['password']} | Stored Hash: {$user['password']}");
        if ($passwordMatch) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['is_admin'] = $user['isAdmin'];
            header('Location: index.php');
            exit;
        } else {
            $error = 'Wrong email or password';
        }
    } else {
        $error = 'Wrong email or password';
        error_log("User not found for email: {$_POST['email']}");
    }
}

// Prepare content for layout
ob_start();
?>
<h1>Login</h1>
<?php if (isset($error)): ?><p><?= $error ?></p><?php endif; ?>
<form method="POST">
    <label>Email</label><input type="email" name="email" required />
    <label>Password</label><input type="password" name="password" required />
    <input type="submit" value="Login" />
</form>
<?php
$content = ob_get_clean();
$title = 'Login';
require 'layout.php';
?>