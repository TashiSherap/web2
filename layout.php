<?php
// Include database and auth utilities only once
include_once 'connect.php';
include_once 'auth.php';

// Fetch categories for navigation (used on every page)
$stmt = $pdo->query('SELECT id, name FROM category');
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($title ?? 'Carbuy Auctions') ?> - Carbuy</title>
    <link rel="stylesheet" href="carbuy.css" />
</head>
<body>
    <header>
        <!-- Stylized Carbuy title with colored spans -->
        <h1><span class="C">C</span><span class="a">a</span><span class="r">r</span><span class="b">b</span><span class="u">u</span><span class="y">y</span></h1>
        <!-- Search form, links to search.php -->
        <form action="search.php">
            <input type="text" name="search" placeholder="Search for a car" />
            <input type="submit" name="submit" value="Search" />
        </form>
    </header>

    <nav>
        <ul>
            <!-- Dynamic category links -->
            <?php foreach ($categories as $c): ?>
                <li><a class="categoryLink" href="category.php?id=<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></a></li>
            <?php endforeach; ?>
            <!-- Login/Logout link based on user status -->
            <li><a href="<?= isLoggedIn() ? 'logout.php' : 'login.php' ?>"><?= isLoggedIn() ? 'Logout' : 'Login' ?></a></li>
            <!-- Show Register link if not logged in -->
            <?php if (!isLoggedIn()): ?>
                <li><a href="register.php">Register</a></li>
            <?php endif; ?>
            <!-- Show Add Auction link if logged in -->
            <?php if (isLoggedIn()): ?>
                <li><a href="addAuction.php">Add Auction</a></li>
            <?php endif; ?>
            <!-- Show Edit Auction link on auction page if user owns it -->
            <?php if (isset($showEditAuction) && $showEditAuction): ?>
                <li><a href="editAuction.php?id=<?= $auctionId ?>">Edit Auction</a></li>
            <?php endif; ?>
            <!-- Show Manage Categories link if admin -->
            <?php if (isAdmin()): ?>
                <li><a href="adminCategories.php">Manage Categories</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <!-- Banner image -->
    <img src="banners/1.jpg" alt="Banner" />

    <main>
        <!-- Page content will be injected here -->
        <?= $content ?>
        <!-- Footer consistent across all pages -->
        <footer>© Carbuy 2025</footer>
    </main>
</body>
</html>