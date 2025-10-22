<?php
// order_success.php
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

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    echo "Order ID missing.";
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $id]);
$order = $stmt->fetch();
if (!$order) {
    echo "Order not found.";
    exit;
}

$items = json_decode($order['items'], true);
$shipping = json_decode($order['shipping_address'], true);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Order Complete - LoveBear</title>
  <style>
    :root { --light-pink: #ffe4ec; --soft-rose: #ffd6e0; --rose: #ffb6c1; --cream: #fff9f9; --deep-rose: #d26b8c; --text: #3a2c32; --muted: #766d70; --white: #ffffff; --shadow: 0 6px 18px rgba(0,0,0,0.08); --glow: 0 0 20px rgba(255,182,193,0.3); }
    body{font-family:sans-serif;padding:24px;background:linear-gradient(180deg,#fff9f9,#ffe4ec);color:#3a2c32}
    header {display:flex;justify-content:space-between;align-items:center;padding:20px 60px;background:rgba(255,255,255,0.7);backdrop-filter:blur(10px);box-shadow:0 2px 10px rgba(0,0,0,0.05);position:sticky;top:0;z-index:100;flex-wrap:wrap;}
.logo{font-size:28px;font-weight:800;color:var(--deep-rose);}
nav{display:flex;align-items:center;gap:20px;flex-wrap:wrap;}
nav a{text-decoration:none;color:var(--text);font-weight:600;transition:0.3s;}
nav a:hover{color:var(--deep-rose);}
    .wrap{max-width:800px;margin:0 auto}
    .card{background:#fff;padding:18px;border-radius:12px;box-shadow:0 6px 18px rgba(0,0,0,0.06)}
    .qty-btn{background:var(--soft-rose);border:none;border-radius:6px;width:58px;height:28px;cursor:pointer;font-weight:bold;}
  </style>
</head>
<body>
  <header>
   <div class="logo"><a src="index.php">LoveBear</div>
  <nav>
    <a href="index.php">Home</a>
    <a href="cart.php">Cart (<?php echo array_sum($_SESSION['cart'] ?? []); ?>)</a>
    <form method="post" action="logout.php" style="display:inline">
      <button class="qty-btn" type="submit">Logout</button>
    </form>
  </nav>
</header>
  <h1 style="color:#d26b8c;">🎉 Payment Successful!</h1>
<p>Your order has been confirmed. Below is your bill and order summary.</p>
<div class="wrap">
  <h1>Thank you for your order ❤️</h1>
  <div class="card">
    <p><strong>Order #</strong> <?php echo e($order['id']); ?></p>
    <p><strong>Payment ID</strong> <?php echo e($order['razorpay_payment_id']); ?></p>
    <h3>Items</h3>
    <ul>
      <?php foreach ($items as $it): ?>
        <li><?php echo e($it['name']); ?> × <?php echo (int)$it['qty']; ?> — ₹ <?php echo number_format($it['subtotal'],2); ?></li>
      <?php endforeach; ?>
    </ul>
    <p style="font-weight:800">Total Paid: ₹ <?php echo number_format($order['total'],2); ?></p>
    <h3>Shipping</h3>
    <p><?php echo e($shipping['name']); ?>, <?php echo e($shipping['phone']); ?><br>
       <?php echo nl2br(e($shipping['address'])); ?><br>
       <?php echo e($shipping['district']); ?>, <?php echo e($shipping['state']); ?> — <?php echo e($shipping['pincode']); ?></p>

    <p><a href="index.php">Continue shopping</a></p>
  </div>
</div>
</body>
</html>
