<?php
require_once 'config.php';

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
header('Content-Type: application/json');

if (empty($_SESSION['user'])) {
    echo json_encode(['ok' => false, 'msg' => 'Not logged in']);
    exit;
}

$rp_payment_id = $_POST['razorpay_payment_id'] ?? null;
$rp_order_id = $_POST['razorpay_order_id'] ?? null;
$rp_signature = $_POST['razorpay_signature'] ?? null;

if (!$rp_payment_id || !$rp_order_id || !$rp_signature) {
    echo json_encode(['ok' => false, 'msg' => 'Missing payment data']);
    exit;
}

// Verify signature
$expected_signature = hash_hmac('sha256', $rp_order_id . '|' . $rp_payment_id, RAZORPAY_KEY_SECRET);
if (!hash_equals($expected_signature, $rp_signature)) {
    echo json_encode(['ok' => false, 'msg' => 'Invalid payment signature']);
    exit;
}

// Fetch cart
$items = $_SESSION['cart'] ?? [];
if (empty($items)) {
    echo json_encode(['ok' => false, 'msg' => 'Cart empty']);
    exit;
}

$in = implode(',', array_fill(0, count($items), '?'));
$stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($in)");
$stmt->execute(array_keys($items));
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total = 0;
$order_items = [];
foreach ($rows as $r) {
    $pid = $r['id'];
    $qty = $items[$pid];
    $subtotal = $r['price'] * $qty;
    $total += $subtotal;
    $order_items[] = [
        'id' => $r['id'],
        'name' => $r['name'],
        'price' => $r['price'],
        'qty' => $qty,
        'subtotal' => $subtotal
    ];
}

// Shipping snapshot
$user = getUserById($pdo, $_SESSION['user_id']);
$shipping = [
    'name' => $_SESSION['shipping_name'] ?? $user['username'],
    'phone' => $_SESSION['phone'] ?? $user['phone'],
    'address' => $_SESSION['address'] ?? $user['address'],
    'state' => $_SESSION['state'] ?? $user['state'],
    'district' => $_SESSION['district'] ?? $user['district'],
    'pincode' => $_SESSION['pincode'] ?? $user['pincode'],
];

try {
    $pdo->beginTransaction();

    // Insert order
    $stmt = $pdo->prepare("INSERT INTO orders (user_id, items, total, razorpay_order_id, razorpay_payment_id, payment_status, shipping_address)
        VALUES (:user_id, :items, :total, :r_order, :r_payment, :status, :shipping)");
    $stmt->execute([
        ':user_id' => $_SESSION['user_id'],
        ':items' => json_encode($order_items),
        ':total' => $total,
        ':r_order' => $rp_order_id,
        ':r_payment' => $rp_payment_id,
        ':status' => 'paid',
        ':shipping' => json_encode($shipping)
    ]);

    $order_id = $pdo->lastInsertId();

    // Update stock
    $update = $pdo->prepare("UPDATE products SET stock = stock - :qty WHERE id = :id AND stock >= :qty");
    foreach ($items as $pid => $qty) {
        $update->execute([':qty' => $qty, ':id' => $pid]);
        if ($update->rowCount() === 0) {
            throw new Exception("Insufficient stock for product ID $pid");
        }
    }

    $pdo->commit();

    // Clear cart
    $_SESSION['cart'] = [];

    echo json_encode([
        'ok' => true,
        'msg' => 'Payment successful',
        'redirect' => 'order_success.php?id=' . $order_id
    ]);
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['ok' => false, 'msg' => 'Error: ' . $e->getMessage()]);
    exit;
}
?>
