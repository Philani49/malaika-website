<?php
require_once '../includes/config.php';
requireRole('Staff');

$pageTitle = 'Staff Dashboard';

// Get staff_id for current user
$staff_stmt = $pdo->prepare("SELECT staff_id FROM staff WHERE user_id = ?");
$staff_stmt->execute([$_SESSION['user_id']]);
$staff = $staff_stmt->fetch();
$staff_id = $staff ? $staff['staff_id'] : null;

// Today's bookings
$today = date('Y-m-d');
$bookings_stmt = $pdo->prepare("SELECT b.*, s.service_name, s.duration_mins, u.full_name as client_name 
    FROM bookings b 
    JOIN services s ON b.service_id = s.service_id 
    JOIN clients c ON b.client_id = c.client_id 
    JOIN users u ON c.user_id = u.user_id 
    WHERE b.booking_date = ? AND b.status != 'Cancelled'
    ORDER BY b.time_slot");
$bookings_stmt->execute([$today]);
$today_bookings = $bookings_stmt->fetchAll();

// Inventory stats
$inventory = $pdo->query("SELECT category, COUNT(*) as total, SUM(CASE WHEN stock_status = 'Sold Out' THEN 1 ELSE 0 END) as sold_out FROM products GROUP BY category")->fetchAll();

require_once '../includes/header.php';
?>

<style>
.staff-header { background: #2c3e50; color: white; padding: 20px 0; }
.booking-block { border-left: 4px solid var(--malaika-green); padding: 15px; background: white; border-radius: 8px; margin-bottom: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
.booking-block.massage { border-left-color: #e74c3c; }
.booking-block.eyelashes { border-left-color: #9b59b6; }
</style>

<div class="staff-header">
    <div class="container d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-0"><i class="bi bi-person-badge"></i> Staff Portal</h4>
            <small>Malaika Beauty Parlor & Boutique</small>
        </div>
        <a href="../logout.php" class="btn btn-outline-light btn-sm"><i class="bi bi-box-arrow-right"></i> Logout</a>
    </div>
</div>

<div class="container py-4">
    <h5 class="mb-4">Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?></h5>

    <div class="row g-4">
        <!-- Daily Calendar -->
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0"><i class="bi bi-calendar-day"></i> Daily Schedule — <?php echo date('l, d F Y'); ?></h6>
                <a href="calendar.php" class="btn btn-sm btn-outline-dark">Full Calendar</a>
            </div>

            <?php if (empty($today_bookings)): ?>
                <div class="alert alert-info">No bookings scheduled for today.</div>
            <?php else: ?>
                <?php foreach ($today_bookings as $b): 
                    $cssClass = strtolower($b['service_name']);
                    if (strpos($cssClass, 'massage') !== false) $cssClass = 'massage';
                    elseif (strpos($cssClass, 'eyelash') !== false) $cssClass = 'eyelashes';
                    else $cssClass = 'nails';
                ?>
                <div class="booking-block <?php echo $cssClass; ?>">
                    <div class="d-flex justify-content-between">
                        <div>
                            <strong><?php echo date('H:i', strtotime($b['time_slot'])); ?></strong> — 
                            <span class="fw-bold"><?php echo htmlspecialchars($b['service_name']); ?></span>
                            <span class="badge bg-secondary ms-2"><?php echo $b['duration_mins']; ?> min</span>
                        </div>
                        <span class="badge bg-<?php echo $b['status'] == 'Confirmed' ? 'success' : 'warning'; ?>"><?php echo $b['status']; ?></span>
                    </div>
                    <div class="mt-2">
                        <i class="bi bi-person"></i> <strong><?php echo htmlspecialchars($b['client_name']); ?></strong>
                        <?php if ($b['notes']): ?>
                            <div class="text-muted small mt-1"><i class="bi bi-chat-left-text"></i> <?php echo htmlspecialchars($b['notes']); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-bold">Quick Actions</div>
                <div class="list-group list-group-flush">
                    <a href="calendar.php" class="list-group-item list-group-item-action"><i class="bi bi-calendar-week text-primary"></i> View Calendar</a>
                    <a href="inventory.php" class="list-group-item list-group-item-action"><i class="bi bi-box-seam text-success"></i> Update Inventory</a>
                </div>
            </div>

            <!-- Inventory Summary -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold">Inventory Status</div>
                <div class="card-body">
                    <?php foreach ($inventory as $inv): ?>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span><?php echo $inv['category']; ?></span>
                        <div>
                            <span class="badge bg-success"><?php echo $inv['total'] - $inv['sold_out']; ?> available</span>
                            <?php if ($inv['sold_out'] > 0): ?>
                            <span class="badge bg-danger"><?php echo $inv['sold_out']; ?> sold out</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
