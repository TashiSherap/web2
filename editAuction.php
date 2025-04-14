<?php
// Include database connection and auth utilities
require 'connect.php';
require 'auth.php';

requireLogin();

// Validate auction ID and ownership
$auctionId = $_GET['id'] ?? null;
if (!$auctionId) { header('Location: index.php'); exit; }
$stmt = $pdo->prepare('SELECT * FROM auction WHERE id = ? AND userId = ?');
$stmt->execute([$auctionId, $_SESSION['user_id']]);
$auction = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$auction) { header('Location: index.php'); exit; }

// Fetch categories for dropdown
$stmt = $pdo->query('SELECT id, name FROM category');
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle auction update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare('UPDATE auction SET title = ?, description = ?, categoryId = ?, endDate = ? WHERE id = ? AND userId = ?');
    $stmt->execute([$_POST['title'], $_POST['description'], $_POST['category'], $_POST['auction_end_date'], $auctionId, $_SESSION['user_id']]);
    header("Location: auction.php?id=$auctionId");
    exit;
}

// Prepare content for layout
ob_start();
?>
<h1>Edit Auction</h1>
<form method="POST">
    <label>Title</label><input type="text" name="title" value="<?= htmlspecialchars($auction['title']) ?>" required />
    <label>Description</label><textarea name="description" required><?= htmlspecialchars($auction['description']) ?></textarea>
    <label>Category</label>
    <select name="category" required>
        <?php foreach ($categories as $c): ?>
            <option value="<?= $c['id'] ?>" <?= $c['id'] == $auction['categoryId'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($c['name']) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <label>End Date</label><input type="datetime-local" name="auction_end_date" value="<?= str_replace(' ', 'T', $auction['endDate']) ?>" required />
    <input type="submit" value="Update" />
</form>
<?php
$content = ob_get_clean();
$title = 'Edit Auction';
require 'layout.php';
?>