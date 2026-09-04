<?php
require_once '../includes/config.php';
requireRole('Admin');

$pageTitle = 'Admin Dashboard';
$stats = getAdminStats($pdo);
$upcoming = getUpcomingAppointments($pdo, 5);

// Get recent activity
$recent = $pdo->query("SELECT b.*, u.full_name, s.service_name 
    FROM bookings b 
    JOIN clients c ON b.client_id = c.client_id 
    JOIN users u ON c.user_id = u.user_id 
    JOIN services s ON b.service_id = s.service_id 
    ORDER BY b.created_at DESC LIMIT 5")->fetchAll();

// Payment stats
$total_revenue = $pdo->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status = 'Paid'")->fetchColumn();
$pending_payments = $pdo->query("SELECT COUNT(*) FROM payments WHERE status = 'Pending'")->fetchColumn();
$month_revenue = $pdo->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status = 'Paid' AND MONTH(transaction_date) = MONTH(CURDATE()) AND YEAR(transaction_date) = YEAR(CURDATE())")->fetchColumn();

require_once '../includes/header.php';
?>

<style>
.admin-header { background: #1a1a2e; color: white; padding: 20px 0; }
.stat-card { border-radius: 12px; border: none; box-shadow: 0 2px 12px rgba(0,0,0,0.08); }
.stat-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
.quick-action { border-radius: 12px; border: 1px solid #eee; padding: 20px; text-align: center; transition: all 0.2s; background: white; }
.quick-action:hover { background: #f8f9fa; transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
.quick-action i { font-size: 1.8rem; margin-bottom: 10px; display: block; }
</style>

<div class="admin-header">
    <div class="container d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-0"><i class="bi bi-shield-lock"></i> Malaika Admin Panel</h4>
            <small>Beauty Parlor & Boutique Management</small>
        </div>
        <a href="../logout.php" class="btn btn-outline-light btn-sm"><i class="bi bi-box-arrow-right"></i> Logout</a>
    </div>
</div>

<div class="container py-4">
    <h5 class="mb-4">Welcome back, <?php echo htmlspecialchars($_SESSION['full_name']); ?></h5>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-success bg-opacity-10 text-success me-3"><i class="bi bi-calendar-check"></i></div>
                    <div>
                        <h3 class="mb-0"><?php echo $stats['today_bookings']; ?></h3>
                        <small class="text-muted">Today's Bookings</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning me-3"><i class="bi bi-currency-dollar"></i></div>
                    <div>
                        <h3 class="mb-0">R <?php echo number_format($stats['today_revenue'], 2); ?></h3>
                        <small class="text-muted">Revenue (Today)</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-info bg-opacity-10 text-info me-3"><i class="bi bi-people"></i></div>
                    <div>
                        <h3 class="mb-0"><?php echo $stats['active_staff']; ?></h3>
                        <small class="text-muted">Active Staff</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-danger bg-opacity-10 text-danger me-3"><i class="bi bi-box-seam"></i></div>
                    <div>
                        <h3 class="mb-0"><?php echo $stats['inventory_items']; ?></h3>
                        <small class="text-muted">Inventory Items <span class="badge bg-danger ms-1"><?php echo $stats['low_stock']; ?> sold out</span></small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue Stats Row -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card stat-card h-100 border-start border-4 border-success">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Total Revenue</h6>
                    <h3 class="mb-0 text-success">R <?php echo number_format($total_revenue, 2); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card h-100 border-start border-4 border-primary">
                <div class="card-body">
                    <h6 class="text-muted mb-1">This Month</h6>
                    <h3 class="mb-0 text-primary">R <?php echo number_format($month_revenue, 2); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card h-100 border-start border-4 border-warning">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Pending Payments</h6>
                    <h3 class="mb-0 text-warning"><?php echo $pending_payments; ?></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Quick Actions -->
        <div class="col-lg-8">
            <h6 class="fw-bold mb-3">Quick Actions</h6>
            <div class="row g-3">
                <div class="col-md-4">
                    <a href="manage-bookings.php" class="text-decoration-none text-dark">
                        <div class="quick-action">
                            <i class="bi bi-calendar-week text-primary"></i>
                            <div class="fw-bold">Manage Bookings</div>
                            <small class="text-muted">View & edit appointments</small>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="manage-products.php" class="text-decoration-none text-dark">
                        <div class="quick-action">
                            <i class="bi bi-bag text-success"></i>
                            <div class="fw-bold">Manage Products</div>
                            <small class="text-muted">Add & update inventory</small>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="manage-services.php" class="text-decoration-none text-dark">
                        <div class="quick-action">
                            <i class="bi bi-scissors text-danger"></i>
                            <div class="fw-bold">Manage Services</div>
                            <small class="text-muted">Beauty service pricing</small>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="manage-staff.php" class="text-decoration-none text-dark">
                        <div class="quick-action">
                            <i class="bi bi-person-badge text-info"></i>
                            <div class="fw-bold">Manage Staff</div>
                            <small class="text-muted">Add & manage team</small>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="manage-clients.php" class="text-decoration-none text-dark">
                        <div class="quick-action">
                            <i class="bi bi-people-fill text-warning"></i>
                            <div class="fw-bold">Clients</div>
                            <small class="text-muted">View registered clients</small>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="payments.php" class="text-decoration-none text-dark">
                        <div class="quick-action">
                            <i class="bi bi-credit-card text-success"></i>
                            <div class="fw-bold">Payments</div>
                            <small class="text-muted">Revenue & transactions</small>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Recent Activity -->
            <h6 class="fw-bold mt-4 mb-3">Recent Activity</h6>
            <div class="card border-0 shadow-sm">
                <div class="list-group list-group-flush">
                    <?php foreach ($recent as $r): ?>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <span class="badge bg-<?php echo $r['status'] == 'Confirmed' ? 'success' : ($r['status'] == 'Pending' ? 'warning' : 'secondary'); ?> me-2"><?php echo $r['status']; ?></span>
                            <strong><?php echo htmlspecialchars($r['full_name']); ?></strong> booked <em><?php echo htmlspecialchars($r['service_name']); ?></em>
                            <div class="text-muted small"><?php echo date('d M Y', strtotime($r['booking_date'])); ?> at <?php echo $r['time_slot']; ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Upcoming Appointments -->
        <div class="col-lg-4">
            <h6 class="fw-bold mb-3">Upcoming Appointments</h6>
            <div class="card border-0 shadow-sm">
                <div class="list-group list-group-flush">
                    <?php foreach ($upcoming as $u): ?>
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between">
                            <strong><?php echo date('H:i', strtotime($u['time_slot'])); ?></strong>
                            <span class="badge bg-malaika"><?php echo $u['service_name']; ?></span>
                        </div>
                        <div class="small"><?php echo htmlspecialchars($u['client_name']); ?></div>
                        <div class="text-muted small"><?php echo date('d M Y', strtotime($u['booking_date'])); ?> &bull; <?php echo htmlspecialchars($u['staff_name'] ?? 'Unassigned'); ?></div>
                    </div>
                    <?php endforeach; ?>
                    <?php if (empty($upcoming)): ?>
                    <div class="list-group-item text-muted text-center py-4">No upcoming appointments</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>