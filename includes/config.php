<?php
session_start();

// Database Configuration
$host = 'localhost';
$dbname = 'malaika_db';
$username = 'root';
$password = ''; // XAMPP default is empty

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Base URL (adjust if needed)
define('BASE_URL', '/malaika-website/');

// Helper: Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Helper: Check role
function hasRole($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

// Helper: Redirect
function redirect($path) {
    header("Location: " . BASE_URL . $path);
    exit();
}

// Helper: Flash message
function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// Role-based access guard
function requireRole($role) {
    if (!isLoggedIn()) {
        setFlash('danger', 'Please login to access this page.');
        redirect('login.php');
    }
    if (!hasRole($role)) {
        setFlash('danger', 'Access denied. Insufficient permissions.');
        redirect('index.php');
    }
}

// Get today's stats for admin dashboard
function getAdminStats($pdo) {
    $stats = [];
    $stats['today_bookings'] = $pdo->query("SELECT COUNT(*) FROM bookings WHERE booking_date = CURDATE()")->fetchColumn();
    $stats['today_revenue'] = $pdo->query("SELECT COALESCE(SUM(amount),0) FROM payments p JOIN bookings b ON p.booking_id = b.booking_id WHERE b.booking_date = CURDATE() AND p.status = 'Paid'")->fetchColumn();
    $stats['active_staff'] = $pdo->query("SELECT COUNT(*) FROM staff WHERE is_available = 1")->fetchColumn();
    $stats['inventory_items'] = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    $stats['low_stock'] = $pdo->query("SELECT COUNT(*) FROM products WHERE stock_status = 'Sold Out'")->fetchColumn();
    return $stats;
}

// Get upcoming appointments
function getUpcomingAppointments($pdo, $limit = 5) {
    $limit = (int)$limit; // safety cast to integer
    
    $stmt = $pdo->prepare("SELECT b.*, s.service_name, u.full_name as client_name, st.position, su.full_name as staff_name 
        FROM bookings b 
        JOIN services s ON b.service_id = s.service_id 
        JOIN clients c ON b.client_id = c.client_id 
        JOIN users u ON c.user_id = u.user_id 
        LEFT JOIN staff st ON b.staff_id = st.staff_id 
        LEFT JOIN users su ON st.user_id = su.user_id 
        WHERE b.booking_date >= CURDATE() AND b.status != 'Cancelled'
        ORDER BY b.booking_date, b.time_slot 
        LIMIT $limit");
    
    $stmt->execute();
    return $stmt->fetchAll();
}
?>
