<?php
require_once '../includes/config.php';
requireRole('Client');

$pageTitle = 'My Wishlist';

$client_stmt = $pdo->prepare("SELECT client_id FROM clients WHERE user_id = ?");
$client_stmt->execute([$_SESSION['user_id']]);
$client_id = $client_stmt->fetchColumn();

if (isset($_GET['remove'])) {
    $pdo->prepare("DELETE FROM wishlist WHERE wishlist_id = ? AND client_id = ?")->execute([$_GET['remove'], $client_id]);
    setFlash('success', 'Item removed from wishlist.');
    header("Location: wishlist.php");
    exit();
}

$stmt = $pdo->prepare("SELECT w.*, p.name, p.category, p.price, p.size, p.stock_status 
    FROM wishlist w 
    JOIN products p ON w.product_id = p.product_id 
    WHERE w.client_id = ? 
    ORDER BY w.date_added DESC");
$stmt->execute([$client_id]);
$items = $stmt->fetchAll();

require_once '../includes/header.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="bi bi-heart"></i> My Wishlist</h4>
        <a href="../catalog.php" class="btn btn-outline-dark btn-sm">Continue Shopping</a>
    </div>

    <?php if (empty($items)): ?>
        <div class="alert alert-info">Your wishlist is empty. <a href="../catalog.php">Browse the boutique</a>.</div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($items as $item): ?>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="bg-light d-flex align-items-center justify-content-center" style="height:180px;">
                        <i class="bi bi-bag fs-1 text-muted"></i>
                    </div>
                    <div class="card-body">
                        <span class="badge bg-<?php echo $item['category'] == 'Women' ? 'danger' : ($item['category'] == 'Men' ? 'primary' : 'warning'); ?> mb-2"><?php echo $item['category']; ?></span>
                        <h6><?php echo htmlspecialchars($item['name']); ?></h6>
                        <p class="text-muted small">Sizes: <?php echo htmlspecialchars($item['size']); ?></p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-malaika">R <?php echo number_format($item['price'], 2); ?></span>
                            <span class="badge bg-<?php echo $item['stock_status'] == 'Available' ? 'success' : 'danger'; ?>"><?php echo $item['stock_status']; ?></span>
                        </div>
                        <a href="?remove=<?php echo $item['wishlist_id']; ?>" class="btn btn-outline-danger btn-sm w-100 mt-2"><i class="bi bi-trash"></i> Remove</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>