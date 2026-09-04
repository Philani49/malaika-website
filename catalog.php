<?php
require_once 'includes/config.php';
$pageTitle = 'Boutique';
$category = $_GET['category'] ?? 'All';
$params = [];
$sql = "SELECT * FROM products WHERE 1=1";
if ($category !== 'All') {
    $sql .= " AND category = ?";
    $params[] = $category;
}
$sql .= " ORDER BY category, name";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
require_once 'includes/header.php';
?>

<div class="container py-5">
    <div class="text-center mb-5">
        <h2 class="fw-bold">Clothing Boutique</h2>
        <p class="text-muted">Fashion for Women, Men, and Kids</p>
    </div>

    <!-- Category Tabs -->
    <div class="d-flex justify-content-center gap-2 mb-4">
        <a href="?category=All" class="btn <?php echo $category == 'All' ? 'btn-malaika' : 'btn-outline-dark'; ?>">All</a>
        <a href="?category=Women" class="btn <?php echo $category == 'Women' ? 'btn-malaika' : 'btn-outline-dark'; ?>">Women</a>
        <a href="?category=Men" class="btn <?php echo $category == 'Men' ? 'btn-malaika' : 'btn-outline-dark'; ?>">Men</a>
        <a href="?category=Kids" class="btn <?php echo $category == 'Kids' ? 'btn-malaika' : 'btn-outline-dark'; ?>">Kids</a>
    </div>

    <div class="row g-4">
        <?php foreach ($products as $p): ?>
        <div class="col-md-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm card-hover">
                <a href="product.php?id=<?php echo (int)$p['product_id']; ?>" class="text-decoration-none">
                    <?php if (!empty($p['image_url'])): ?>
                        <img src="<?php echo htmlspecialchars($p['image_url']); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>" class="card-img-top" style="height:260px;object-fit:cover;">
                    <?php else: ?>
                        <div class="bg-light d-flex align-items-center justify-content-center" style="height:260px;"><i class="bi bi-bag fs-1 text-muted"></i></div>
                    <?php endif; ?>
                </a>
                <div class="card-body">
                    <span class="badge bg-<?php echo $p['category'] == 'Women' ? 'danger' : ($p['category'] == 'Men' ? 'primary' : 'warning'); ?> mb-2"><?php echo $p['category']; ?></span>
                    <h6 class="card-title"><a class="text-decoration-none text-dark" href="product.php?id=<?php echo (int)$p['product_id']; ?>"><?php echo htmlspecialchars($p['name']); ?></a></h6>
                    <p class="text-muted small mb-1">Sizes: <?php echo htmlspecialchars($p['size']); ?></p>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold text-malaika">R <?php echo number_format($p['price'], 2); ?></span>
                        <span class="badge bg-<?php echo $p['stock_status'] == 'Available' ? 'success' : 'danger'; ?>"><?php echo $p['stock_status']; ?></span>
                    </div>
                    <?php if (isLoggedIn() && hasRole('Client') && $p['stock_status'] == 'Available'): ?>
                        <button class="btn btn-outline-danger btn-sm w-100 mt-2"><i class="bi bi-heart"></i> Add to Wishlist</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
