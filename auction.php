<?php
// Include database connection and auth utilities
require 'connect.php';
require 'auth.php';

// Validate auction ID
$auctionId = $_GET['id'] ?? null;
if (!$auctionId) { header('Location: index.php'); exit; }

// Fetch auction details with category and seller name
$stmt = $pdo->prepare('SELECT a.*, c.name AS category, u.name AS seller 
                       FROM auction a JOIN category c ON a.categoryId = c.id 
                       JOIN user u ON a.userId = u.id WHERE a.id = ?');
$stmt->execute([$auctionId]);
$auction = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$auction) { header('Location: index.php'); exit; }

// Get highest bid
$stmt = $pdo->prepare('SELECT amount FROM bid WHERE auctionId = ? ORDER BY amount DESC LIMIT 1');
$stmt->execute([$auctionId]);
$highestBid = $stmt->fetchColumn() ?: 0;

// Fetch reviews for the seller
$stmt = $pdo->prepare('SELECT r.*, u.name AS reviewer FROM review r JOIN user u ON r.reviewerId = u.id WHERE r.userId = ?');
$stmt->execute([$auction['userId']]);
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate time left for auction
$timeLeft = (new DateTime($auction['endDate']))->diff(new DateTime());
$timeLeftStr = ($timeLeft->invert ? 'Ended' : "{$timeLeft->h} hours {$timeLeft->i} minutes");

// Handle bid submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bid'])) {
    requireLogin();
    $bidAmount = floatval($_POST['bid']);
    if ($bidAmount > $highestBid) {
        $stmt = $pdo->prepare('INSERT INTO bid (amount, auctionId, userId) VALUES (?, ?, ?)');
        $stmt->execute([$bidAmount, $auctionId, $_SESSION['user_id']]);
        header("Location: auction.php?id=$auctionId");
        exit;
    } else {
        $error = "Bid must be higher than £$highestBid";
    }
}

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reviewtext'])) {
    requireLogin();
    $stmt = $pdo->prepare('INSERT INTO review (reviewText, userId, reviewerId, datePosted) VALUES (?, ?, ?, ?)');
    $stmt->execute([$_POST['reviewtext'], $auction['userId'], $_SESSION['user_id'], date('Y-m-d H:i:s')]);
    header("Location: auction.php?id=$auctionId");
    exit;
}

// Prepare content for layout
ob_start();
?>
<h1><?= htmlspecialchars($auction['title']) ?></h1>
<article class="car">
    <img src="car.png" alt="<?= htmlspecialchars($auction['title']) ?>">
    <section class="details">
        <h2><?= htmlspecialchars($auction['title']) ?></h2>
        <h3><?= htmlspecialchars($auction['category']) ?></h3>
        <p>Auction created by <a href="#"><?= htmlspecialchars($auction['seller']) ?></a></p>
        <p class="price">Current bid: £<?= number_format($highestBid, 2) ?></p>
        <time>Time left: <?= $timeLeftStr ?></time>
        <!-- Show bid form if logged in and auction hasn't ended -->
        <?php if (isLoggedIn() && $timeLeftStr !== 'Ended'): ?>
            <form action="" method="POST" class="bid">
                <input type="text" name="bid" placeholder="Enter bid amount" />
                <input type="submit" value="Place bid" />
            </form>
            <?php if (isset($error)): ?><p><?= $error ?></p><?php endif; ?>
        <?php endif; ?>
    </section>
    <section class="description">
        <p><?= htmlspecialchars($auction['description']) ?></p>
    </section>
    <section class="reviews">
        <h2>Reviews of <?= htmlspecialchars($auction['seller']) ?></h2>
        <ul>
            <?php foreach ($reviews as $r): ?>
                <li><strong><?= htmlspecialchars($r['reviewer']) ?> said </strong>
                    <?= htmlspecialchars($r['reviewText']) ?>
                    <em><?= htmlspecialchars($r['datePosted']) ?></em></li>
            <?php endforeach; ?>
        </ul>
        <!-- Show review form if logged in -->
        <?php if (isLoggedIn()): ?>
            <form method="POST">
                <label>Add your review</label>
                <textarea name="reviewtext"></textarea>
                <input type="submit" name="submit" value="Add Review" />
            </form>
        <?php endif; ?>
    </section>
</article>
<?php
$content = ob_get_clean();
$title = htmlspecialchars($auction['title']);
$showEditAuction = isLoggedIn() && $auction['userId'] == ($_SESSION['user_id'] ?? 0);
require 'layout.php';
?>