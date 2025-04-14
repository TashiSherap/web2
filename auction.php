<?php
require 'connect.php';
require 'auth.php';

// Get auction ID from URL
$auctionId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch auction details, including the owner's name and category name
$stmt = $pdo->prepare('SELECT a.*, u.name AS user_name, c.name AS category_name 
                       FROM auction a 
                       JOIN user u ON a.userId = u.id 
                       JOIN category c ON a.categoryId = c.id 
                       WHERE a.id = ?');
$stmt->execute([$auctionId]);
$auction = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$auction) {
    header('Location: error.php?message=' . urlencode('Auction not found'));
    exit;
}

// Fetch the highest bid for this auction
$stmt = $pdo->prepare('SELECT MAX(amount) AS highest_bid FROM bid WHERE auctionId = ?');
$stmt->execute([$auctionId]);
$highestBid = $stmt->fetch(PDO::FETCH_ASSOC)['highest_bid'] ?? 0;

// Update auction's currentBid if a higher bid exists
if ($highestBid > ($auction['currentBid'] ?? 0)) {
    $stmt = $pdo->prepare('UPDATE auction SET currentBid = ? WHERE id = ?');
    $stmt->execute([$highestBid, $auctionId]);
    $auction['currentBid'] = $highestBid;
}

// Check if the logged-in user owns the auction and no bids exist (for edit functionality)
$showEditAuction = false;
if (isLoggedIn() && $auction['userId'] === $_SESSION['user_id']) {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM bid WHERE auctionId = ?');
    $stmt->execute([$auctionId]);
    $bidCount = $stmt->fetchColumn();
    if ($bidCount == 0) {
        $showEditAuction = true;
    }
}

// Fetch reviews for this auction
$stmt = $pdo->prepare('SELECT r.*, u.name AS reviewer_name 
                       FROM review r 
                       JOIN user u ON r.userId = u.id 
                       WHERE r.auctionId = ? 
                       ORDER BY r.createdAt DESC');
$stmt->execute([$auctionId]);
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle bid submission
$bidError = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_bid'])) {
    if (!isLoggedIn()) {
        header('Location: login.php?redirect=auction.php?id=' . $auctionId);
        exit;
    }

    $bidAmount = floatval($_POST['bid_amount']);
    $currentUserId = $_SESSION['user_id'];

    // Validate bid
    if ($currentUserId === $auction['userId']) {
        $bidError = 'You cannot bid on your own auction';
    } elseif ($bidAmount <= $highestBid) {
        $bidError = 'Your bid must be higher than the current highest bid (£' . number_format($highestBid, 2) . ')';
    } elseif ($bidAmount <= 0) {
        $bidError = 'Bid amount must be greater than 0';
    } elseif ($bidAmount > 10000000) {
        $bidError = 'Bid amount cannot exceed £10,000,000';
    } elseif (strtotime($auction['endDate']) <= time()) {
        $bidError = 'This auction has ended';
    } else {
        // Insert the bid
        $stmt = $pdo->prepare('INSERT INTO bid (auctionId, userId, amount) VALUES (?, ?, ?)');
        $stmt->execute([$auctionId, $currentUserId, $bidAmount]);

        // Update auction's currentBid
        $stmt = $pdo->prepare('UPDATE auction SET currentBid = ? WHERE id = ?');
        $stmt->execute([$bidAmount, $auctionId]);

        // Refresh the page to show the updated bid
        header('Location: auction.php?id=' . $auctionId);
        exit;
    }
}

// Handle review submission
$reviewError = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    if (!isLoggedIn()) {
        header('Location: login.php?redirect=auction.php?id=' . $auctionId);
        exit;
    }

    $comment = trim($_POST['comment']);
    $currentUserId = $_SESSION['user_id'];

    // Validate review
    if ($currentUserId === $auction['userId']) {
        $reviewError = 'You cannot review your own auction';
    } elseif (empty($comment)) {
        $reviewError = 'Comment cannot be empty';
    } else {
        // Insert the review
        $stmt = $pdo->prepare('INSERT INTO review (auctionId, userId, comment, createdAt) VALUES (?, ?, ?, NOW())');
        $stmt->execute([$auctionId, $currentUserId, $comment]);

        // Refresh the page to show the new review
        header('Location: auction.php?id=' . $auctionId);
        exit;
    }
}

// Prepare content for layout
ob_start();
?>
<h1><?= htmlspecialchars($auction['title']) ?></h1>
<p>Posted by: <?= htmlspecialchars($auction['user_name']) ?></p>
<p>Category: <?= htmlspecialchars($auction['category_name']) ?></p>
<p>Description: <?= htmlspecialchars($auction['description'] ?? 'No description provided.') ?></p>
<p class="price">Current Bid: £<?= number_format($auction['currentBid'], 2) ?></p>
<time>End Date: <?= htmlspecialchars($auction['endDate']) ?></time>

<?php if ($showEditAuction): ?>
    <p><a href="edit_auction.php?id=<?= $auctionId ?>">Edit Auction</a></p>
<?php endif; ?>

<?php if (strtotime($auction['endDate']) > time()): ?>
    <?php if (isLoggedIn() && $auction['userId'] !== $_SESSION['user_id']): ?>
        <h2>Place a Bid</h2>
        <?php if ($bidError): ?>
            <p style="color: red;"><?= htmlspecialchars($bidError) ?></p>
        <?php endif; ?>
        <form method="POST" class="bid">
            <label>Your Bid (£)</label>
            <input type="number" name="bid_amount" step="0.01" min="<?= ($auction['currentBid'] > $highestBid ? $auction['currentBid'] : $highestBid) + 0.01 ?>" max="10000000" required />
            <input type="submit" name="place_bid" value="Place Bid" />
        </form>
    <?php elseif (!isLoggedIn()): ?>
        <p><a href="login.php?redirect=auction.php?id=<?= $auctionId ?>">Log in</a> to place a bid.</p>
    <?php else: ?>
        <p>You cannot bid on your own auction.</p>
    <?php endif; ?>
<?php else: ?>
    <p style="color: red;">This auction has ended.</p>
<?php endif; ?>

<h2>Reviews</h2>
<?php if (empty($reviews)): ?>
    <p>No reviews yet. Be the first to leave a review!</p>
<?php else: ?>
    <ul>
        <?php foreach ($reviews as $review): ?>
            <li>
                <strong><?= htmlspecialchars($review['reviewer_name']) ?></strong>
                - <time><?= htmlspecialchars($review['createdAt']) ?></time>
                <p><?= htmlspecialchars($review['comment']) ?></p>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php if (isLoggedIn() && $auction['userId'] !== $_SESSION['user_id']): ?>
    <h3>Leave a Review</h3>
    <?php if ($reviewError): ?>
        <p style="color: red;"><?= htmlspecialchars($reviewError) ?></p>
    <?php endif; ?>
    <form method="POST" class="review">
        <label>Comment:</label>
        <textarea name="comment" rows="3" required></textarea>
        <input type="submit" name="submit_review" value="Submit Review" />
    </form>
<?php elseif (!isLoggedIn()): ?>
    <p><a href="login.php?redirect=auction.php?id=<?= $auctionId ?>">Log in</a> to leave a review.</p>
<?php else: ?>
    <p>You cannot review your own auction.</p>
<?php endif; ?>

<?php
$content = ob_get_clean();
$title = $auction['title'];
require 'layout.php';
?>