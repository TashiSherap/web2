<?php
// Include database connection to define $pdo
require 'connect.php';

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare('INSERT INTO user (email, password, name) VALUES (?, ?, ?)');
        $stmt->execute([$_POST['email'], password_hash($_POST['password'], PASSWORD_DEFAULT), $_POST['name']]);
        header('Location: login.php');
        exit;
    } catch (PDOException $e) {
        $error = 'Error: ' . $e->getMessage();
    }
}

// Prepare content for layout
ob_start();
?>
<h1>Register</h1>
<?php if (isset($error)): ?><p><?= $error ?></p><?php endif; ?>
<form method="POST">
    <label>Email</label><input type="email" name="email" required />
    <label>Password</label><input type="password" name="password" required />
    <label>Name</label><input type="text" name="name" required />
    <input type="submit" value="Register" />
</form>
<?php
$content = ob_get_clean();
$title = 'Register';
require 'layout.php';
?>