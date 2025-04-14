<?php
// Include database connection to define $pdo
require 'connect.php';

// Fetch 10 auctions ending soonest, joining with category for name
$stmt = $pdo->query('SELECT a.id, a.title, a.description, c.name AS category, a.endDate 
                     FROM auction a JOIN category c ON a.categoryId = c.id 
                     ORDER BY a.endDate ASC LIMIT 10');
$auctions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Prepare content for the layout
ob_start();
?>
<h1>Latest Car Listings</h1>
<?php if (empty($auctions)): ?>
    <p>No auctions available at the moment.</p>
<?php else: ?>
    <!-- List auctions in carList format -->
    <ul class="carList">
        <?php foreach ($auctions as $a): ?>
            <li>
                <img src="car.png" alt="<?= htmlspecialchars($a['title']) ?>">
                <article>
                    <h2><?= htmlspecialchars($a['title']) ?></h2>
                    <h3><?= htmlspecialchars($a['category']) ?></h3>
                    <!-- Show first 200 chars of description -->
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
$title = 'Carbuy Auctions';
require 'layout.php';
?>