<?php
require_once 'includes/config.php';
$pageTitle = 'Home';
require_once 'includes/header.php';

// Fetch featured services
$services = $pdo->query("SELECT * FROM services WHERE is_active = 1 LIMIT 3")->fetchAll();

// Fetch featured products (one from each category)
$products = $pdo->query("SELECT * FROM products WHERE stock_status = 'Available' GROUP BY category LIMIT 3")->fetchAll();
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container text-center">
        <h1 class="display-4 fw-bold mb-3">Welcome to Malaika</h1>
        <p class="lead mb-4">Premium Beauty Services & Curated Fashion Boutique</p>
        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <a href="services.php" class="btn btn-light btn-lg px-4"><i class="bi bi-scissors"></i> Book a Service</a>
            <a href="catalog.php" class="btn btn-outline-light btn-lg px-4"><i class="bi bi-bag"></i> Shop Boutique</a>
        </div>
    </div>
</section>

<!-- Services Preview -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Our Beauty Services</h2>
            <p class="text-muted">Professional treatments by Ms. Mogapi & Boitumelo</p>
        </div>
        <div class="row g-4">
            <?php foreach ($services as $s): ?>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm card-hover">
                    <div class="card-body text-center p-4">
                        <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:70px;height:70px;">
                            <i class="bi bi-<?php echo $s['category'] == 'Nails' ? 'hand-index' : ($s['category'] == 'Eyelashes' ? 'eye' : 'heart-pulse'); ?> fs-2 text-malaika"></i>
                        </div>
                        <h5 class="card-title"><?php echo htmlspecialchars($s['service_name']); ?></h5>
                        <p class="text-muted small"><?php echo htmlspecialchars($s['description']); ?></p>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <span class="fw-bold text-malaika">R <?php echo number_format($s['price'], 2); ?></span>
                            <span class="badge bg-secondary"><?php echo $s['duration_mins']; ?> min</span>
                        </div>
                        <?php if (isLoggedIn() && hasRole('Client')): ?>
                            <a href="client/book-appointment.php?service=<?php echo $s['service_id']; ?>" class="btn btn-malaika btn-sm w-100 mt-3">Book Now</a>
                        <?php elseif (!isLoggedIn()): ?>
                            <a href="login.php" class="btn btn-outline-malaika btn-sm w-100 mt-3">Login to Book</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-4">
            <a href="services.php" class="btn btn-outline-dark">View All Services <i class="bi bi-arrow-right"></i></a>
        </div>
    </div>
</section>

<!-- Boutique Preview -->
<section class="py-5 bg-white">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">New Arrivals</h2>
            <p class="text-muted">Browse our latest clothing collection</p>
        </div>
        <div class="row g-4">
            <?php foreach ($products as $p): ?>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm card-hover">
                    <div class="bg-light d-flex align-items-center justify-content-center" style="height:220px;">
                        <i class="bi bi-bag fs-1 text-muted"></i>
                    </div>
                    <div class="card-body">
                        <span class="badge bg-<?php echo $p['category'] == 'Women' ? 'danger' : ($p['category'] == 'Men' ? 'primary' : 'warning'); ?> mb-2"><?php echo $p['category']; ?></span>
                        <h5 class="card-title"><?php echo htmlspecialchars($p['name']); ?></h5>
                        <p class="text-muted small">Sizes: <?php echo htmlspecialchars($p['size']); ?></p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-malaika fs-5">R <?php echo number_format($p['price'], 2); ?></span>
                            <span class="badge bg-<?php echo $p['stock_status'] == 'Available' ? 'success' : 'danger'; ?>"><?php echo $p['stock_status']; ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-4">
            <a href="catalog.php" class="btn btn-outline-dark">Shop Full Collection <i class="bi bi-arrow-right"></i></a>
        </div>
    </div>
</section>

<!-- Features -->
<section class="py-5">
    <div class="container">
        <div class="row g-4 text-center">
            <div class="col-md-4">
                <i class="bi bi-calendar-check fs-1 text-malaika mb-3"></i>
                <h5>Easy Online Booking</h5>
                <p class="text-muted">Book your beauty appointments 24/7. No more phone calls or WhatsApp messages.</p>
            </div>
            <div class="col-md-4">
                <i class="bi bi-bag-check fs-1 text-malaika mb-3"></i>
                <h5>Curated Fashion</h5>
                <p class="text-muted">Browse our boutique collection online before visiting the store.</p>
            </div>
            <div class="col-md-4">
                <i class="bi bi-bell fs-1 text-malaika mb-3"></i>
                <h5>Appointment Reminders</h5>
                <p class="text-muted">Get automatic reminders so you never miss your scheduled service.</p>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
