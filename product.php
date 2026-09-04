<?php
require_once 'includes/config.php';
$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();
if (!$product) { setFlash('danger','Product not found.'); redirect('catalog.php'); }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isLoggedIn() || !hasRole('Client')) { setFlash('danger','Please login as a client to add products to your cart.'); redirect('login.php'); }
    if ($product['stock_status'] !== 'Available') { setFlash('danger','This product is currently sold out.'); }
    else { $_SESSION['cart'][$id] = ($_SESSION['cart'][$id] ?? 0) + max(1,(int)($_POST['quantity'] ?? 1)); setFlash('success','Product added to your cart.'); redirect('cart.php'); }
}
$pageTitle = $product['name']; require_once 'includes/header.php'; ?>
<div class="container py-5"><div class="row g-4"><div class="col-md-6">
<?php if (!empty($product['image_url'])): ?><img src="<?php echo htmlspecialchars($product['image_url']); ?>" class="img-fluid rounded shadow-sm w-100" style="max-height:600px;object-fit:cover;" alt="<?php echo htmlspecialchars($product['name']); ?>"><?php else: ?><div class="bg-light p-5 text-center"><i class="bi bi-bag fs-1"></i></div><?php endif; ?>
</div><div class="col-md-6"><span class="badge bg-secondary"><?php echo htmlspecialchars($product['category']); ?></span><h2 class="mt-2"><?php echo htmlspecialchars($product['name']); ?></h2><h3 class="text-malaika">R <?php echo number_format($product['price'],2); ?></h3><p><?php echo nl2br(htmlspecialchars($product['description'] ?? '')); ?></p><p><strong>Sizes:</strong> <?php echo htmlspecialchars($product['size']); ?></p>
<form method="post" class="row g-3"><div class="col-4"><label class="form-label">Quantity</label><input type="number" min="1" value="1" name="quantity" class="form-control"></div><div class="col-12"><button class="btn btn-malaika btn-lg" <?php echo $product['stock_status'] !== 'Available' ? 'disabled' : ''; ?>><i class="bi bi-cart-plus"></i> Add to Cart</button> <a href="cart.php" class="btn btn-outline-dark btn-lg">View Cart</a></div></form></div></div></div>
<?php require_once 'includes/footer.php'; ?>