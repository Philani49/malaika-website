<?php
require_once '../includes/config.php';
requireRole('Admin');
$pageTitle = 'Manage Staff';
require_once '../includes/header.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="bi bi-gear"></i> Manage Staff</h4>
        <a href="dashboard.php" class="btn btn-outline-dark btn-sm"><i class="bi bi-arrow-left"></i> Back to Dashboard</a>
    </div>
    <div class="alert alert-info">
        <strong>Coming Soon:</strong> This page is under development. The admin dashboard structure is in place.
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>