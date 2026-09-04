<?php
require_once '../includes/config.php';
requireRole('Client');

$pageTitle = 'My Account';

// Get client_id
$client_stmt = $pdo->prepare("SELECT client_id FROM clients WHERE user_id = ?");
$client_stmt->execute([$_SESSION['user_id']]);
$client = $client_stmt->fetch();
$client_id = $client ? $client['client_id'] : null;

// My bookings
$bookings_stmt = $pdo->prepare("SELECT b.*, s.service_name, s.price, s.duration_mins, st.position, su.full_name as staff_name, p.status as payment_status
    FROM bookings b 
    JOIN services s ON b.service_id = s.service_id 
    LEFT JOIN staff st ON b.staff_id = st.staff_id 
    LEFT JOIN users su ON st.user_id = su.user_id 
    LEFT JOIN payments p ON b.booking_id = p.booking_id
    WHERE b.client_id = ? 
    ORDER BY b.booking_date DESC, b.time_slot DESC LIMIT 5");
$bookings_stmt->execute([$client_id]);
$my_bookings = $bookings_stmt->fetchAll();

// My wishlist count
$wishlist_count = $pdo->prepare("SELECT COUNT(*) FROM wishlist WHERE client_id = ?");
$wishlist_count->execute([$client_id]);
$wishlist_total = $wishlist_count->fetchColumn();

// Total spent
$total_spent = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM payments p JOIN bookings b ON p.booking_id = b.booking_id WHERE b.client_id = ? AND p.status = 'Paid'");
$total_spent->execute([$client_id]);
$total_spent_amount = $total_spent->fetchColumn();

require_once '../includes/header.php';
?>

<div class="container py-4">
    <h4 class="mb-4">Welcome back, <?php echo htmlspecialchars($_SESSION['full_name']); ?></h4>

    <div class="row g-4">
        <!-- Quick Stats -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center p-3">
                <i class="bi bi-calendar-check fs-1 text-malaika mb-2"></i>
                <h5><?php echo count($my_bookings); ?></h5>
                <small class="text-muted">My Bookings</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center p-3">
                <i class="bi bi-heart fs-1 text-danger mb-2"></i>
                <h5><?php echo $wishlist_total; ?></h5>
                <small class="text-muted">Wishlist Items</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center p-3">
                <i class="bi bi-credit-card fs-1 text-success mb-2"></i>
                <h5>R <?php echo number_format($total_spent_amount, 2); ?></h5>
                <small class="text-muted">Total Spent</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center p-3">
                <i class="bi bi-bag fs-1 text-primary mb-2"></i>
                <h5>Shop</h5>
                <small class="text-muted"><a href="../catalog.php">Browse Boutique</a></small>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-2">
        <!-- My Bookings -->
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0">My Appointments</h6>
                <a href="book-appointment.php" class="btn btn-malaika btn-sm"><i class="bi bi-plus"></i> Book New</a>
            </div>

            <?php if (empty($my_bookings)): ?>
                <div class="alert alert-info">You have no bookings yet. <a href="book-appointment.php">Book your first appointment</a>.</div>
            <?php else: ?>
                <div class="card border-0 shadow-sm">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Service</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Staff</th>
                                    <th>Status</th>
                                    <th>Payment</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($my_bookings as $b): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($b['service_name']); ?></td>
                                    <td><?php echo date('d M Y', strtotime($b['booking_date'])); ?></td>
                                    <td><?php echo date('H:i', strtotime($b['time_slot'])); ?></td>
                                    <td><?php echo htmlspecialchars($b['staff_name'] ?? 'TBA'); ?></td>
                                    <td><span class="badge bg-<?php echo $b['status'] == 'Confirmed' ? 'success' : ($b['status'] == 'Pending' ? 'warning' : 'secondary'); ?>"><?php echo $b['status']; ?></span></td>
                                    <td>
                                        <?php if ($b['payment_status'] == 'Paid'): ?>
                                            <span class="badge bg-success">Paid</span>
                                        <?php elseif ($b['payment_status'] == 'Pending'): ?>
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        <?php else: ?>
                                            <a href="pay-booking.php?booking_id=<?php echo $b['booking_id']; ?>" class="badge bg-secondary text-decoration-none">Pay Now</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="text-end mt-2">
                    <a href="my-bookings.php" class="text-decoration-none">View all bookings <i class="bi bi-arrow-right"></i></a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-bold">Quick Links</div>
                <div class="list-group list-group-flush">
                    <a href="book-appointment.php" class="list-group-item list-group-item-action"><i class="bi bi-calendar-plus text-malaika"></i> Book Appointment</a>
                    <a href="my-bookings.php" class="list-group-item list-group-item-action"><i class="bi bi-calendar-week text-primary"></i> My Bookings</a>
                    <a href="wishlist.php" class="list-group-item list-group-item-action"><i class="bi bi-heart text-danger"></i> My Wishlist</a>
                    <a href="../catalog.php" class="list-group-item list-group-item-action"><i class="bi bi-bag text-success"></i> Shop Boutique</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>