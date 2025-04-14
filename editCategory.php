<?php
// Include database connection and auth utilities
require 'connect.php';
require 'auth.php';

requireAdmin();

// Validate category ID
$categoryId = $_GET['id'] ?? null;
if (!$categoryId) { header('Location: adminCategories.php'); exit; }
$stmt = $pdo->prepare('SELECT name FROM category WHERE id = ?');
$stmt->execute([$categoryId]);
$category = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$category) { header('Location: adminCategories.php'); exit; }

// Handle category update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare('UPDATE category SET name = ? WHERE id = ?');
    $stmt->execute([$_POST['name'], $categoryId]);
    header('Location: adminCategories.php');
    exit;
}

// Prepare content for layout
ob_start();
?>
<h1>Edit Category</h1>
<form method="POST">
    <label>Name</label><input type="text" name="name" value="<?= htmlspecialchars($category['name']) ?>" required />
    <input type="submit" value="Update" />
</form>
<?php
$content = ob_get_clean();
$title = 'Edit Category';
require 'layout.php';
?>