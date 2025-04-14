<?php
// Include database connection and auth utilities
require 'connect.php';
require 'auth.php';

requireAdmin();

// Fetch all categories
$stmt = $pdo->query('SELECT id, name FROM category');
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Prepare content for layout
ob_start();
?>
<h1>Categories</h1>
<a href="addCategory.php">Add New</a>
<ul>
    <?php foreach ($categories as $c): ?>
        <li>
            <?= htmlspecialchars($c['name']) ?>
            <a href="editCategory.php?id=<?= $c['id'] ?>">Edit</a>
            <a href="deleteCategory.php?id=<?= $c['id'] ?>" onclick="return confirm('Sure?');">Delete</a>
        </li>
    <?php endforeach; ?>
</ul>
<?php
$content = ob_get_clean();
$title = 'Manage Categories';
require 'layout.php';
?>