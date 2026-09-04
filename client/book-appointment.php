<?php
require_once '../includes/config.php';
requireRole('Client');

$pageTitle = 'Book Appointment';

$client_stmt = $pdo->prepare("SELECT client_id FROM clients WHERE user_id = ?");
$client_stmt->execute([$_SESSION['user_id']]);
$client_id = $client_stmt->fetchColumn();

$services = $pdo->query("SELECT * FROM services WHERE is_active = 1")->fetchAll();
$staff = $pdo->query("SELECT st.*, u.full_name FROM staff st JOIN users u ON st.user_id = u.user_id WHERE st.is_available = 1")->fetchAll();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $service_id = $_POST['service_id'] ?? '';
    $staff_id = $_POST['staff_id'] ?? null;
    $booking_date = $_POST['booking_date'] ?? '';
    $time_slot = $_POST['time_slot'] ?? '';
    $notes = trim($_POST['notes'] ?? '');

    if (empty($service_id) || empty($booking_date) || empty($time_slot)) {
        $error = 'Please fill in all required fields.';
    } else {
        $check = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE staff_id = ? AND booking_date = ? AND time_slot = ? AND status != 'Cancelled'");
        $check->execute([$staff_id, $booking_date, $time_slot]);
        if ($check->fetchColumn() > 0) {
            $error = 'This time slot is already booked. Please select another.';
        } else {
            $stmt = $pdo->prepare("INSERT INTO bookings (client_id, staff_id, service_id, booking_date, time_slot, status, notes) VALUES (?, ?, ?, ?, ?, 'Pending', ?)");
            $stmt->execute([$client_id, $staff_id ?: null, $service_id, $booking_date, $time_slot, $notes]);
            setFlash('success', 'Appointment booked successfully! Awaiting confirmation.');
            redirect('my-bookings.php');
        }
    }
}

require_once '../includes/header.php';
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0"><i class="bi bi-calendar-plus"></i> Book Appointment</h4>
                <a href="dashboard.php" class="btn btn-outline-dark btn-sm">Back</a>
            </div>

            <?php if ($error): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Select Service</label>
                            <select name="service_id" class="form-select" required>
                                <option value="">Choose a service...</option>
                                <?php foreach ($services as $s): ?>
                                <option value="<?php echo $s['service_id']; ?>"><?php echo htmlspecialchars($s['service_name']); ?> — R <?php echo number_format($s['price'], 2); ?> (<?php echo $s['duration_mins']; ?> min)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Preferred Staff (Optional)</label>
                            <select name="staff_id" class="form-select">
                                <option value="">No preference</option>
                                <?php foreach ($staff as $st): ?>
                                <option value="<?php echo $st['staff_id']; ?>"><?php echo htmlspecialchars($st['full_name']); ?> (<?php echo $st['position']; ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Select Date</label>
                            <input type="date" name="booking_date" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Select Time Slot</label>
                            <select name="time_slot" class="form-select" required>
                                <option value="">Choose time...</option>
                                <?php for ($h = 9; $h <= 16; $h++): ?>
                                <option value="<?php echo sprintf('%02d:00:00', $h); ?>"><?php echo sprintf('%02d:00', $h); ?></option>
                                <option value="<?php echo sprintf('%02d:30:00', $h); ?>"><?php echo sprintf('%02d:30', $h); ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Notes (Optional)</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Any special requests..."></textarea>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-malaika btn-lg">Confirm Booking</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>