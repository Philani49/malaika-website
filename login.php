<?php
require_once 'includes/config.php';

if (isLoggedIn()) {
    if (hasRole('Admin')) redirect('admin/dashboard.php');
    if (hasRole('Staff')) redirect('staff/dashboard.php');
    redirect('client/dashboard.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'Client';

    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = ? AND is_active = 1");
        $stmt->execute([$email, $role]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            // Update last login for admin
            if ($role === 'Admin') {
                $pdo->prepare("UPDATE admin SET last_login = NOW() WHERE user_id = ?")
                    ->execute([$user['user_id']]);
                redirect('admin/dashboard.php');
            } elseif ($role === 'Staff') {
                redirect('staff/dashboard.php');
            } else {
                redirect('client/dashboard.php');
            }
        } else {
            $error = 'Invalid email, password, or role selected.';
        }
    }
}

$pageTitle = 'Login';
require_once 'includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow border-0">
                <div class="card-header bg-malaika text-white text-center py-4">
                    <h4 class="mb-0"><i class="bi bi-stars"></i> Malaika</h4>
                    <small>Beauty Parlor & Boutique</small>
                </div>
                <div class="card-body p-4">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control" required placeholder="Enter your email">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required placeholder="Enter your password">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Login As</label>
                            <select name="role" class="form-select">
                                <option value="Client">Client</option>
                                <option value="Staff">Staff</option>
                                <option value="Admin">Administrator</option>
                            </select>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-malaika btn-lg">Log In</button>
                        </div>
                    </form>
                    <div class="text-center mt-3">
                        <a href="#" class="text-decoration-none text-muted small">Forgot password?</a>
                    </div>
                </div>
                <div class="card-footer bg-light text-center py-3">
                    <small class="text-muted">New here? <a href="register.php" class="text-decoration-none text-malaika fw-bold">Create an account</a></small>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
