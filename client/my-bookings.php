<?php
require_once '../includes/config.php';
requireRole('Client');

$pageTitle = 'My Bookings';

$client_stmt = $pdo->prepare("SELECT client_id FROM clients WHERE user_id = ?");
$client_stmt->execute([$_SESSION['user_id']]);
$client_id = $client_stmt->fetchColumn();

if (isset($_GET['cancel'])) {
    $stmt = $pdo->prepare("UPDATE bookings SET status = 'Cancelled' WHERE booking_id = ? AND client_id = ? AND status = 'Pending'");
    $stmt->execute([$_GET['cancel'], $client_id]);
    setFlash('success', 'Booking cancelled successfully.');
    header("Location: my-bookings.php");
    exit();
}

$stmt = $pdo->prepare("SELECT b.*, s.service_name, s.price, s.duration_mins, su.full_name as staff_name,
    p.status as payment_status, p.payment_method
    FROM bookings b 
    JOIN services s ON b.service_id = s.service_id 
    LEFT JOIN staff st ON b.staff_id = st.staff_id 
    LEFT JOIN users su ON st.user_id = su.user_id 
    LEFT JOIN payments p ON b.booking_id = p.booking_id
    WHERE b.client_id = ? 
    ORDER BY b.booking_date DESC, b.time_slot DESC");
$stmt->execute([$client_id]);
$bookings = $stmt->fetchAll();

require_once '../includes/header.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="bi bi-calendar-week"></i> My Bookings</h4>
        <a href="book-appointment.php" class="btn btn-malaika btn-sm"><i class="bi bi-plus"></i> New Booking</a>
    </div>

    <?php if (empty($bookings)): ?>
        <div class="alert alert-info">You have no bookings yet. <a href="book-appointment.php">Book now</a>.</div>
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
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $b): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($b['service_name']); ?><br><small class="text-muted">R <?php echo number_format($b['price'], 2); ?></small></td>
                            <td><?php echo date('d M Y', strtotime($b['booking_date'])); ?></td>
                            <td><?php echo date('H:i', strtotime($b['time_slot'])); ?></td>
                            <td><?php echo htmlspecialchars($b['staff_name'] ?? 'TBA'); ?></td>
                            <td><span class="badge bg-<?php echo $b['status'] == 'Confirmed' ? 'success' : ($b['status'] == 'Pending' ? 'warning' : 'secondary'); ?>"><?php echo $b['status']; ?></span></td>
                            <td>
                                <?php if ($b['payment_status'] == 'Paid'): ?>
                                    <span class="badge bg-success"><i class="bi bi-check"></i> Paid</span>
                                <?php elseif ($b['payment_status'] == 'Pending'): ?>
                                    <span class="badge bg-warning text-dark">Pending</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Unpaid</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($b['status'] == 'Pending' && $b['payment_status'] != 'Paid'): ?>
                                    <a href="pay-booking.php?booking_id=<?php echo $b['booking_id']; ?>" class="btn btn-malaika btn-sm mb-1"><i class="bi bi-credit-card"></i> Pay Now</a>
                                <?php endif; ?>
                                <?php if ($b['status'] == 'Pending'): ?>
                                <a href="?cancel=<?php echo $b['booking_id']; ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Cancel this booking?')">Cancel</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>