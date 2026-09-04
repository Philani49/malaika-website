<?php
require_once '../includes/config.php';
requireRole('Staff');

$pageTitle = 'Inventory Management';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'], $_POST['status'])) {
    $stmt = $pdo->prepare("UPDATE products SET stock_status = ? WHERE product_id = ?");
    $stmt->execute([$_POST['status'], $_POST['product_id']]);
    setFlash('success', 'Inventory updated successfully.');
    header("Location: inventory.php");
    exit();
}

$category = $_GET['category'] ?? 'All';
$sql = "SELECT * FROM products WHERE 1=1";
$params = [];
if ($category !== 'All') {
    $sql .= " AND category = ?";
    $params[] = $category;
}
$sql .= " ORDER BY category, name";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

require_once '../includes/header.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="bi bi-box-seam"></i> Inventory Management</h4>
        <a href="dashboard.php" class="btn btn-outline-dark btn-sm"><i class="bi bi-arrow-left"></i> Back</a>
    </div>

    <div class="d-flex gap-2 mb-3">
        <a href="?category=All" class="btn <?php echo $category == 'All' ? 'btn-malaika' : 'btn-outline-dark'; ?> btn-sm">All</a>
        <a href="?category=Women" class="btn <?php echo $category == 'Women' ? 'btn-malaika' : 'btn-outline-dark'; ?> btn-sm">Women</a>
        <a href="?category=Men" class="btn <?php echo $category == 'Men' ? 'btn-malaika' : 'btn-outline-dark'; ?> btn-sm">Men</a>
        <a href="?category=Kids" class="btn <?php echo $category == 'Kids' ? 'btn-malaika' : 'btn-outline-dark'; ?> btn-sm">Kids</a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $p): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($p['name']); ?></strong><br><small class="text-muted">Sizes: <?php echo htmlspecialchars($p['size']); ?></small></td>
                        <td><span class="badge bg-<?php echo $p['category'] == 'Women' ? 'danger' : ($p['category'] == 'Men' ? 'primary' : 'warning'); ?>"><?php echo $p['category']; ?></span></td>
                        <td>R <?php echo number_format($p['price'], 2); ?></td>
                        <td><span class="badge bg-<?php echo $p['stock_status'] == 'Available' ? 'success' : 'danger'; ?>"><?php echo $p['stock_status']; ?></span></td>
                        <td>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="product_id" value="<?php echo $p['product_id']; ?>">
                                <input type="hidden" name="status" value="<?php echo $p['stock_status'] == 'Available' ? 'Sold Out' : 'Available'; ?>">
                                <button type="submit" class="btn btn-sm <?php echo $p['stock_status'] == 'Available' ? 'btn-outline-danger' : 'btn-outline-success'; ?>">
                                    <?php echo $p['stock_status'] == 'Available' ? 'Mark Sold Out' : 'Mark Available'; ?>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>