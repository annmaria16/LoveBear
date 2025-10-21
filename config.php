<?php
// config.php
// DB + helpers + Razorpay keys

// DB credentials - adjust if needed
$host = 'localhost';
$db   = 'lovebear';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

// Start session if not already
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Razorpay keys (you provided these)
define('RAZORPAY_KEY_ID', 'rzp_test_4GCxMOoqwqydp6');
define('RAZORPAY_KEY_SECRET', '1mlfmOmQcstOlmTtCztPYXFB');

// Helper: get product by id
function getProduct(PDO $pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    return $stmt->fetch();
}

// Helper: get user by id
function getUserById(PDO $pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    return $stmt->fetch();
}

// Escape output
function e($s) {
    return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
}

// Ensure cart exists for session
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
?>
