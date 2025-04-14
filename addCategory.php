<?php
// Include database connection and auth utilities
require 'connect.php';
require 'auth.php';

requireAdmin();

// Handle category creation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare('INSERT INTO category (name) VALUES (?)');
    $stmt->execute([$_POST['name']]);
    header('Location: adminCategories.php');
    exit;
}

// Prepare content for layout
ob_start();
?>
<h1>Add Category</h1>
<form method="POST">
    <label>Name</label><input type="text" name="name" required />
    <input type="submit" value="Add" />
</form>
<?php
$content = ob_get_clean();
$title = 'Add Category';
require 'layout.php';
?>