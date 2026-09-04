<?php
require_once 'includes/config.php';
$pageTitle = 'Services';
$services = $pdo->query("SELECT * FROM services WHERE is_active = 1 ORDER BY category, price")->fetchAll();
require_once 'includes/header.php';
?>

<div class="container py-5">
    <div class="text-center mb-5">
        <h2 class="fw-bold">Our Beauty Services</h2>
        <p class="text-muted">Professional treatments tailored to your needs</p>
    </div>
    <div class="row g-4">
        <?php foreach ($services as $s): ?>
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 border-0 shadow-sm card-hover">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <span class="badge bg-<?php echo $s['category'] == 'Nails' ? 'danger' : ($s['category'] == 'Eyelashes' ? 'purple' : 'info'); ?> bg-opacity-10 text-<?php echo $s['category'] == 'Nails' ? 'danger' : ($s['category'] == 'Eyelashes' ? 'purple' : 'info'); ?>"><?php echo $s['category']; ?></span>
                        <span class="fw-bold text-malaika fs-5">R <?php echo number_format($s['price'], 2); ?></span>
                    </div>
                    <h5 class="card-title"><?php echo htmlspecialchars($s['service_name']); ?></h5>
                    <p class="text-muted"><?php echo htmlspecialchars($s['description']); ?></p>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <span class="text-muted"><i class="bi bi-clock"></i> <?php echo $s['duration_mins']; ?> minutes</span>
                        <?php if (isLoggedIn() && hasRole('Client')): ?>
                            <a href="client/book-appointment.php?service=<?php echo $s['service_id']; ?>" class="btn btn-malaika btn-sm">Book Now</a>
                        <?php elseif (!isLoggedIn()): ?>
                            <a href="login.php" class="btn btn-outline-malaika btn-sm">Login to Book</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
