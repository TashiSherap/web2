<?php
// Include database connection to define $pdo
require 'connect.php';

// Validate category ID
$categoryId = $_GET['id'] ?? null;
if (!$categoryId) { header('Location: index.php'); exit; }

// Fetch category name
$stmt = $pdo->prepare('SELECT name FROM category WHERE id = ?');
$stmt->execute([$categoryId]);
$category = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$category) { header('Location: index.php'); exit; }

// Fetch auctions in this category
$stmt = $pdo->prepare('SELECT a.id, a.title, a.description, c.name AS category, a.endDate 
                       FROM auction a JOIN category c ON a.categoryId = c.id 
                       WHERE a.categoryId = ? ORDER BY a.endDate ASC');
$stmt->execute([$categoryId]);
$auctions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Prepare content for layout
ob_start();
?>
<h1><?= htmlspecialchars($category['name']) ?></h1>
<?php if (empty($auctions)): ?>
    <p>No auctions in this category.</p>
<?php else: ?>
    <ul class="carList">
        <?php foreach ($auctions as $a): ?>
            <li>
                <img src="car.png" alt="<?= htmlspecialchars($a['title']) ?>">
                <article>
                    <h2><?= htmlspecialchars($a['title']) ?></h2>
                    <h3><?= htmlspecialchars($a['category']) ?></h3>
                    <p><?= htmlspecialchars(substr($a['description'], 0, 200)) . (strlen($a['description']) > 200 ? '...' : '') ?></p>
                    <p class="price">Ends: <?= htmlspecialchars($a['endDate']) ?></p>
                    <a href="auction.php?id=<?= $a['id'] ?>" class="more auctionLink">More >></a>
                </article>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
<?php
$content = ob_get_clean();
$title = htmlspecialchars($category['name']);
require 'layout.php';
?>