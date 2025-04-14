<?php
// Include database connection and auth utilities
require 'connect.php';
require 'auth.php';

requireAdmin();

// Delete category if possible (may fail due to foreign key constraints)
$categoryId = $_GET['id'] ?? null;
if ($categoryId) {
    try {
        $stmt = $pdo->prepare('DELETE FROM category WHERE id = ?');
        $stmt->execute([$categoryId]);
    } catch (PDOException $e) {
        // Ignore errors (e.g., category in use)
    }
}
header('Location: adminCategories.php');
exit;
?>