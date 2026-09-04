<?php
require_once 'includes/config.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
 foreach ($_POST['qty'] ?? [] as $id=>$qty) { $id=(int)$id; $qty=(int)$qty; if ($qty>0) $_SESSION['cart'][$id]=$qty; else unset($_SESSION['cart'][$id]); }
 setFlash('success','Cart updated.'); redirect('cart.php');
}
$cart=$_SESSION['cart'] ?? []; $products=[]; $total=0;
if ($cart) { $ids=array_keys($cart); $ph=implode(',',array_fill(0,count($ids),'?')); $st=$pdo->prepare("SELECT * FROM products WHERE product_id IN ($ph)"); $st->execute($ids); foreach($st->fetchAll() as $p){$p['qty']=$cart[$p['product_id']];$p['line_total']=$p['qty']*$p['price'];$total+=$p['line_total'];$products[]=$p;} }
$pageTitle='Shopping Cart'; require_once 'includes/header.php'; ?>
<div class="container py-5"><h2>Shopping Cart</h2><?php if(!$products): ?><div class="alert alert-info">Your cart is empty. <a href="catalog.php">Visit the boutique</a>.</div><?php else: ?><form method="post"><div class="card shadow-sm border-0"><div class="card-body table-responsive"><table class="table align-middle"><thead><tr><th>Product</th><th>Price</th><th>Quantity</th><th>Total</th></tr></thead><tbody><?php foreach($products as $p): ?><tr><td><?php echo htmlspecialchars($p['name']); ?></td><td>R <?php echo number_format($p['price'],2); ?></td><td><input type="number" min="0" class="form-control" style="width:90px" name="qty[<?php echo $p['product_id']; ?>]" value="<?php echo $p['qty']; ?>"></td><td>R <?php echo number_format($p['line_total'],2); ?></td></tr><?php endforeach; ?></tbody></table><div class="d-flex justify-content-between"><button class="btn btn-outline-secondary">Update Cart</button><h4>Subtotal: R <?php echo number_format($total,2); ?></h4></div></div></div><div class="mt-3 text-end"><a href="checkout.php" class="btn btn-malaika btn-lg">Proceed to Checkout</a></div></form><?php endif; ?></div>
<?php require_once 'includes/footer.php'; ?>