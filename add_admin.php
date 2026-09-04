<?php
require_once 'includes/config.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $password  = $_POST['password'] ?? '';
    $phone     = trim($_POST['phone'] ?? '');
    $role      = 'Admin';

    if (empty($full_name) || empty($email) || empty($password)) {
        $message = '<div class="alert alert-danger">All fields are required.</div>';
    } else {
        // Check if email already exists
        $check = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
        $check->execute([$email]);
        if ($check->fetch()) {
            $message = '<div class="alert alert-danger">This email is already registered.</div>';
        } else {
            // Hash the password properly so password_verify() will work
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("
                INSERT INTO users (full_name, email, password_hash, phone, role, is_active, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, 1, NOW(), NOW())
            ");
            $stmt->execute([$full_name, $email, $hash, $phone, $role]);

            $message = '<div class="alert alert-success">
                <strong>Admin added successfully!</strong><br>
                Email: ' . htmlspecialchars($email) . '<br>
                Password: ' . htmlspecialchars($password) . '
            </div>';
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Add New Admin</h5>
                </div>
                <div class="card-body">
                    <?= $message ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="full_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="text" name="password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" placeholder="e.g. 0712345678">
                        </div>
                        <button type="submit" class="btn btn-success w-100">Create Admin</button>
                    </form>
                    <div class="mt-3 text-center">
                        <a href="login.php" class="text-decoration-none">Back to Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>