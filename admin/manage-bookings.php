<?php
require_once '../includes/config.php';
requireRole('Admin');

$pageTitle = 'Manage Bookings';

// Handle status update
if (isset($_POST['update_status'])) {
    $booking_id = intval($_POST['booking_id']);
    $new_status = $_POST['new_status'];
    $pdo->prepare("UPDATE bookings SET status = ? WHERE booking_id = ?")->execute([$new_status, $booking_id]);
    setFlash('success', 'Booking status updated.');
    header("Location: manage-bookings.php");
    exit();
}

// Handle assign staff
if (isset($_POST['assign_staff'])) {
    $booking_id = intval($_POST['booking_id']);
    $staff_id = intval($_POST['staff_id']);
    $pdo->prepare("UPDATE bookings SET staff_id = ? WHERE booking_id = ?")->execute([$staff_id ?: null, $booking_id]);
    setFlash('success', 'Staff assigned successfully.');
    header("Location: manage-bookings.php");
    exit();
}

$status_filter = $_GET['status'] ?? 'All';
$sql = "SELECT b.*, s.service_name, s.price, s.duration_mins, u.full_name as client_name, u.phone as client_phone, su.full_name as staff_name, p.status as payment_status, p.payment_method
    FROM bookings b 
    JOIN services s ON b.service_id = s.service_id 
    JOIN clients c ON b.client_id = c.client_id 
    JOIN users u ON c.user_id = u.user_id 
    LEFT JOIN staff st ON b.staff_id = st.staff_id 
    LEFT JOIN users su ON st.user_id = su.user_id 
    LEFT JOIN payments p ON b.booking_id = p.booking_id
    WHERE 1=1";
$params = [];

if ($status_filter !== 'All') {
    $sql .= " AND b.status = ?";
    $params[] = $status_filter;
}

$sql .= " ORDER BY b.booking_date DESC, b.time_slot DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$bookings = $stmt->fetchAll();

$staff_list = $pdo->query("SELECT st.staff_id, u.full_name, st.position FROM staff st JOIN users u ON st.user_id = u.user_id WHERE st.is_available = 1")->fetchAll();

require_once '../includes/header.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="bi bi-calendar-week"></i> Manage Bookings</h4>
        <a href="dashboard.php" class="btn btn-outline-dark btn-sm"><i class="bi bi-arrow-left"></i> Back to Dashboard</a>
    </div>

    <!-- Filter Tabs -->
    <div class="d-flex gap-2 mb-3">
        <a href="?status=All" class="btn <?php echo $status_filter == 'All' ? 'btn-malaika' : 'btn-outline-dark'; ?> btn-sm">All</a>
        <a href="?status=Pending" class="btn <?php echo $status_filter == 'Pending' ? 'btn-malaika' : 'btn-outline-dark'; ?> btn-sm">Pending</a>
        <a href="?status=Confirmed" class="btn <?php echo $status_filter == 'Confirmed' ? 'btn-malaika' : 'btn-outline-dark'; ?> btn-sm">Confirmed</a>
        <a href="?status=Completed" class="btn <?php echo $status_filter == 'Completed' ? 'btn-malaika' : 'btn-outline-dark'; ?> btn-sm">Completed</a>
        <a href="?status=Cancelled" class="btn <?php echo $status_filter == 'Cancelled' ? 'btn-malaika' : 'btn-outline-dark'; ?> btn-sm">Cancelled</a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Client</th>
                        <th>Service</th>
                        <th>Date & Time</th>
                        <th>Staff</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th style="width:200px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $b): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($b['client_name']); ?></strong><br>
                            <small class="text-muted"><?php echo htmlspecialchars($b['client_phone']); ?></small>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($b['service_name']); ?><br>
                            <small class="text-muted">R <?php echo number_format($b['price'], 2); ?> &bull; <?php echo $b['duration_mins']; ?> min</small>
                        </td>
                        <td>
                            <?php echo date('d M Y', strtotime($b['booking_date'])); ?><br>
                            <strong><?php echo date('H:i', strtotime($b['time_slot'])); ?></strong>
                        </td>
                        <td>
                            <form method="POST" class="d-flex gap-1">
                                <input type="hidden" name="booking_id" value="<?php echo $b['booking_id']; ?>">
                                <select name="staff_id" class="form-select form-select-sm" style="width:120px;" onchange="this.form.submit()">
                                    <option value="">Unassigned</option>
                                    <?php foreach ($staff_list as $st): ?>
                                    <option value="<?php echo $st['staff_id']; ?>" <?php echo ($b['staff_name'] == $st['full_name']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($st['full_name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="hidden" name="assign_staff" value="1">
                            </form>
                        </td>
                        <td><span class="badge bg-<?php echo $b['status'] == 'Confirmed' ? 'success' : ($b['status'] == 'Pending' ? 'warning' : ($b['status'] == 'Completed' ? 'primary' : 'secondary')); ?>"><?php echo $b['status']; ?></span></td>
                        <td>
                            <?php if ($b['payment_status'] == 'Paid'): ?>
                                <span class="badge bg-success"><i class="bi bi-check"></i> <?php echo $b['payment_method'] ?? 'Paid'; ?></span>
                            <?php elseif ($b['payment_status'] == 'Pending'): ?>
                                <span class="badge bg-warning text-dark"><?php echo $b['payment_method'] ?? 'Pending'; ?></span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Unpaid</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <form method="POST" class="d-flex gap-1">
                                <input type="hidden" name="booking_id" value="<?php echo $b['booking_id']; ?>">
                                <select name="new_status" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="Pending" <?php echo $b['status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="Confirmed" <?php echo $b['status'] == 'Confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                    <option value="Completed" <?php echo $b['status'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                    <option value="Cancelled" <?php echo $b['status'] == 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                                <input type="hidden" name="update_status" value="1">
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($bookings)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">No bookings found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>