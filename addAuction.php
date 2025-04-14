<?php
// Include database connection and auth utilities
require 'connect.php';
require 'auth.php';

requireLogin();

// Fetch categories for dropdown
$stmt = $pdo->query('SELECT id, name FROM category');
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle auction creation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare('INSERT INTO auction (title, description, categoryId, endDate, userId) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$_POST['title'], $_POST['description'], $_POST['category'], $_POST['auction_end_date'], $_SESSION['user_id']]);
    header('Location: index.php');
    exit;
}

// Prepare content for layout
ob_start();
?>
<h1>Add Auction</h1>
<form method="POST">
    <label>Title</label><input type="text" name="title" required />
    <label>Description</label><textarea name="description" required></textarea>
    <label>Category</label>
    <select name="category" required>
        <?php foreach ($categories as $c): ?>
            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
        <?php endforeach; ?>
    </select>
    <label>End Date</label><input type="datetime-local" name="auction_end_date" required />
    <input type="submit" value="Add" />
</form>
<?php
$content = ob_get_clean();
$title = 'Add Auction';
require 'layout.php';
?>