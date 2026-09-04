<?php
require_once '../includes/config.php';
requireRole('Staff');

$pageTitle = 'Staff Calendar';
$view_date = $_GET['date'] ?? date('Y-m-d');

$stmt = $pdo->prepare("SELECT b.*, s.service_name, s.duration_mins, u.full_name as client_name, su.full_name as staff_name 
    FROM bookings b 
    JOIN services s ON b.service_id = s.service_id 
    JOIN clients c ON b.client_id = c.client_id 
    JOIN users u ON c.user_id = u.user_id 
    LEFT JOIN staff st ON b.staff_id = st.staff_id 
    LEFT JOIN users su ON st.user_id = su.user_id 
    WHERE b.booking_date = ? AND b.status != 'Cancelled'
    ORDER BY b.time_slot");
$stmt->execute([$view_date]);
$bookings = $stmt->fetchAll();

$prev = date('Y-m-d', strtotime($view_date . ' -1 day'));
$next = date('Y-m-d', strtotime($view_date . ' +1 day'));

require_once '../includes/header.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="bi bi-calendar-week"></i> Appointment Calendar</h4>
        <a href="dashboard.php" class="btn btn-outline-dark btn-sm"><i class="bi bi-arrow-left"></i> Back to Dashboard</a>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body d-flex justify-content-between align-items-center">
            <a href="?date=<?php echo $prev; ?>" class="btn btn-outline-secondary"><i class="bi bi-chevron-left"></i> Prev</a>
            <h5 class="mb-0"><?php echo date('l, d F Y', strtotime($view_date)); ?></h5>
            <a href="?date=<?php echo $next; ?>" class="btn btn-outline-secondary">Next <i class="bi bi-chevron-right"></i></a>
        </div>
    </div>

    <?php if (empty($bookings)): ?>
        <div class="alert alert-info text-center">No appointments for this day.</div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($bookings as $b): 
                $color = 'success';
                if (stripos($b['service_name'], 'massage') !== false) $color = 'danger';
                elseif (stripos($b['service_name'], 'eyelash') !== false) $color = 'purple';
            ?>
            <div class="col-12">
                <div class="card border-start border-4 border-<?php echo $color; ?> shadow-sm">
                    <div class="card-body d-flex justify-content-between">
                        <div>
                            <h6 class="mb-1"><?php echo date('H:i', strtotime($b['time_slot'])); ?> — <?php echo htmlspecialchars($b['service_name']); ?></h6>
                            <p class="mb-1"><i class="bi bi-person"></i> <strong><?php echo htmlspecialchars($b['client_name']); ?></strong></p>
                            <?php if ($b['notes']): ?><small class="text-muted"><i class="bi bi-chat-left-text"></i> <?php echo htmlspecialchars($b['notes']); ?></small><?php endif; ?>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-<?php echo $b['status'] == 'Confirmed' ? 'success' : 'warning'; ?>"><?php echo $b['status']; ?></span>
                            <div class="small text-muted mt-1"><?php echo $b['duration_mins']; ?> min</div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>