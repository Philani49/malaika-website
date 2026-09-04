<?php
require_once '../includes/config.php';
requireRole('Admin');

$pageTitle = 'Manage Services';

// Handle Delete
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM services WHERE service_id = ?")->execute([$_GET['delete']]);
    setFlash('success', 'Service deleted successfully.');
    header("Location: manage-services.php");
    exit();
}

// Handle Add/Edit
$error = '';
$edit_service = null;

if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM services WHERE service_id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_service = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $service_id = $_POST['service_id'] ?? null;
    $service_name = trim($_POST['service_name'] ?? '');
    $category = $_POST['category'] ?? '';
    $duration_mins = intval($_POST['duration_mins'] ?? 0);
    $price = floatval($_POST['price'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if (empty($service_name) || empty($category) || $duration_mins <= 0 || $price <= 0) {
        $error = 'Please fill in all required fields.';
    } else {
        if ($service_id) {
            $stmt = $pdo->prepare("UPDATE services SET service_name=?, category=?, duration_mins=?, price=?, description=?, is_active=? WHERE service_id=?");
            $stmt->execute([$service_name, $category, $duration_mins, $price, $description, $is_active, $service_id]);
            setFlash('success', 'Service updated successfully.');
        } else {
            $stmt = $pdo->prepare("INSERT INTO services (service_name, category, duration_mins, price, description, is_active) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$service_name, $category, $duration_mins, $price, $description, $is_active]);
            setFlash('success', 'Service added successfully.');
        }
        header("Location: manage-services.php");
        exit();
    }
}

$services = $pdo->query("SELECT * FROM services ORDER BY category, price")->fetchAll();

require_once '../includes/header.php';
?>

<style>
.admin-header { background: #1a1a2e; color: white; padding: 15px 0; }
</style>

<div class="admin-header">
    <div class="container d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-shield-lock"></i> Admin Panel</h5>
        <a href="dashboard.php" class="btn btn-outline-light btn-sm"><i class="bi bi-speedometer2"></i> Dashboard</a>
    </div>
</div>

<div class="container py-4">
    <div class="row g-4">
        <!-- Service Form -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold">
                    <?php echo $edit_service ? '<i class="bi bi-pencil"></i> Edit Service' : '<i class="bi bi-plus-circle"></i> Add New Service'; ?>
                </div>
                <div class="card-body">
                    <?php if ($error): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>
                    <form method="POST">
                        <?php if ($edit_service): ?>
                            <input type="hidden" name="service_id" value="<?php echo $edit_service['service_id']; ?>">
                        <?php endif; ?>

                        <div class="mb-3">
                            <label class="form-label">Service Name *</label>
                            <input type="text" name="service_name" class="form-control" required value="<?php echo htmlspecialchars($edit_service['service_name'] ?? ''); ?>" placeholder="e.g. Gel Nails">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Category *</label>
                            <select name="category" class="form-select" required>
                                <option value="">Select...</option>
                                <option value="Nails" <?php echo ($edit_service['category'] ?? '') === 'Nails' ? 'selected' : ''; ?>>Nails</option>
                                <option value="Eyelashes" <?php echo ($edit_service['category'] ?? '') === 'Eyelashes' ? 'selected' : ''; ?>>Eyelashes</option>
                                <option value="Massage" <?php echo ($edit_service['category'] ?? '') === 'Massage' ? 'selected' : ''; ?>>Massage</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Duration (minutes) *</label>
                            <input type="number" name="duration_mins" class="form-control" required min="1" value="<?php echo $edit_service['duration_mins'] ?? ''; ?>" placeholder="e.g. 45">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Price (R) *</label>
                            <input type="number" name="price" class="form-control" required step="0.01" min="0" value="<?php echo $edit_service['price'] ?? ''; ?>" placeholder="e.g. 350.00">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Service description"><?php echo htmlspecialchars($edit_service['description'] ?? ''); ?></textarea>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" name="is_active" class="form-check-input" id="is_active" <?php echo ($edit_service['is_active'] ?? 1) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="is_active">Active (visible to clients)</label>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-malaika">
                                <?php echo $edit_service ? '<i class="bi bi-check"></i> Update Service' : '<i class="bi bi-plus"></i> Add Service'; ?>
                            </button>
                        </div>
                        <?php if ($edit_service): ?>
                            <a href="manage-services.php" class="btn btn-outline-secondary w-100 mt-2">Cancel Edit</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>

        <!-- Service List -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold">
                    <i class="bi bi-scissors"></i> All Services (<?php echo count($services); ?>)
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Service</th>
                                <th>Category</th>
                                <th>Duration</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th style="width:120px">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($services as $s): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($s['service_name']); ?></strong>
                                    <div class="small text-muted"><?php echo htmlspecialchars(substr($s['description'] ?? '', 0, 40)); ?><?php echo strlen($s['description'] ?? '') > 40 ? '...' : ''; ?></div>
                                </td>
                                <td><span class="badge bg-<?php echo $s['category'] == 'Nails' ? 'danger' : ($s['category'] == 'Eyelashes' ? 'purple' : 'info'); ?>"><?php echo $s['category']; ?></span></td>
                                <td><?php echo $s['duration_mins']; ?> min</td>
                                <td class="fw-bold">R <?php echo number_format($s['price'], 2); ?></td>
                                <td><span class="badge bg-<?php echo $s['is_active'] ? 'success' : 'secondary'; ?>"><?php echo $s['is_active'] ? 'Active' : 'Inactive'; ?></span></td>
                                <td>
                                    <a href="?edit=<?php echo $s['service_id']; ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                    <a href="?delete=<?php echo $s['service_id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this service?')"><i class="bi bi-trash"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>