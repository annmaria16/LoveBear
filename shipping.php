<?php
// shipping.php
require_once 'config.php';

// Prevent browser caching (so back button doesn’t show logged-in pages)
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Redirect if not logged in
if (empty($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}


if (empty($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// Collect cart items and total
$items = $_SESSION['cart'] ?? [];
if (empty($items)) {
    header("Location: cart.php?msg=" . urlencode("Cart is empty. Add items before checkout."));
    exit;
}

$in = implode(',', array_fill(0, count($items), '?'));
$stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($in)");
$stmt->execute(array_keys($items));
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
$total = 0;
foreach ($rows as $r) {
    $pid = $r['id'];
    $qty = $items[$pid];
    $total += $r['price'] * $qty;
}

// Prefill from session or DB
$user = getUserById($pdo, $_SESSION['user_id']);
$phone = $_SESSION['phone'] ?? $user['phone'] ?? '';
$address = $_SESSION['address'] ?? $user['address'] ?? '';
$state = $_SESSION['state'] ?? $user['state'] ?? '';
$district = $_SESSION['district'] ?? $user['district'] ?? '';
$pincode = $_SESSION['pincode'] ?? $user['pincode'] ?? '';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $district = trim($_POST['district'] ?? '');
    $pincode = trim($_POST['pincode'] ?? '');

    if (empty($name) || empty($phone) || empty($address) || empty($state) || empty($district) || empty($pincode)) {
        $error = "All fields are required.";
    } else {
        // Save shipping into users table (overwrite saved address)
        $stmt = $pdo->prepare("UPDATE users SET username = :name, phone = :phone, address = :address, state = :state, district = :district, pincode = :pincode WHERE id = :id");
        // We update username to reflect name too OR you can store separate shipping name; we keep it simple
        $stmt->execute([
            ':name' => $name,
            ':phone' => $phone,
            ':address' => $address,
            ':state' => $state,
            ':district' => $district,
            ':pincode' => $pincode,
            ':id' => $_SESSION['user_id']
        ]);

        // Update session for convenience
        $_SESSION['phone'] = $phone;
        $_SESSION['address'] = $address;
        $_SESSION['state'] = $state;
        $_SESSION['district'] = $district;
        $_SESSION['pincode'] = $pincode;
        $_SESSION['shipping_name'] = $name;

        // Move to payment page
        header("Location: payment.php");
        exit;
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Shipping Details - LoveBear</title>
  <style>
    body{font-family:sans-serif;padding:24px;background:linear-gradient(180deg,#fff9f9,#ffe4ec);color:#3a2c32}
    .wrap{max-width:700px;margin:0 auto}
    form{background:#fff;padding:18px;border-radius:12px;box-shadow:0 6px 18px rgba(0,0,0,0.06)}
    label{display:block;margin:10px 0 6px}
    input, textarea, select{width:100%;padding:10px;border-radius:8px;border:1px solid #f0d7df}
    .btn{background:#ffb6c1;padding:10px 14px;border-radius:8px;border:none;cursor:pointer}
    .error{background:#ffdede;padding:10px;border-radius:6px;color:#7a1a1a;margin-bottom:10px}
  </style>
</head>
<body>
<div class="wrap">
  <h1>Shipping Details</h1>
  <?php if($error): ?><div class="error"><?php echo e($error); ?></div><?php endif; ?>
  <form method="post">
    <label>Full name</label>
    <input type="text" name="name" value="<?php echo e($_SESSION['shipping_name'] ?? $_SESSION['user'] ?? ''); ?>" required>
    <label>Phone</label>
    <input type="text" name="phone" value="<?php echo e($phone); ?>" required>
    <label>Address</label>
    <textarea name="address" rows="3" required><?php echo e($address); ?></textarea>
    <label>State</label>
    <input type="text" name="state" value="<?php echo e($state); ?>" required>
    <label>District</label>
    <input type="text" name="district" value="<?php echo e($district); ?>" required>
    <label>Pincode</label>
    <input type="text" name="pincode" value="<?php echo e($pincode); ?>" required>
    <div style="margin-top:12px">
      <button class="btn" type="submit">Continue to Payment — ₹ <?php echo number_format($total,2); ?></button>
      <a href="cart.php" style="margin-left:12px">Back to cart</a>
    </div>
  </form>
</div>
</body>
</html>
