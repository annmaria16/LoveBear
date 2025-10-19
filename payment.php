<?php
// payment.php
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

// Ensure cart exists
$items = $_SESSION['cart'] ?? [];
if (empty($items)) {
    header("Location: cart.php?msg=" . urlencode("Cart is empty."));
    exit;
}

// Build order items & total
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

// Convert to paise for Razorpay
$amountPaise = (int)round($total * 100);

// Create Razorpay order via API
$curl = curl_init();
$postData = json_encode([
    'amount' => $amountPaise,
    'currency' => 'INR',
    'receipt' => 'rcpt_' . time(),
    'payment_capture' => 1
]);

curl_setopt_array($curl, [
    CURLOPT_URL => "https://api.razorpay.com/v1/orders",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_USERPWD => RAZORPAY_KEY_ID . ":" . RAZORPAY_KEY_SECRET,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $postData,
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json"
    ],
]);

$response = curl_exec($curl);
$err = curl_error($curl);
curl_close($curl);

if ($err) {
    die("Razorpay request error: " . $err);
}

$rpOrder = json_decode($response, true);
if (empty($rpOrder['id'])) {
    die("Failed to create Razorpay order. Response: " . $response);
}

$razorpay_order_id = $rpOrder['id'];

// store minimal order draft server-side (optional)
// We'll record final order after payment succeeds

// provide data to client
$user = getUserById($pdo, $_SESSION['user_id']);
$shipping = [
    'name' => $_SESSION['shipping_name'] ?? $user['username'],
    'phone' => $_SESSION['phone'] ?? $user['phone'],
    'address' => $_SESSION['address'] ?? $user['address'],
    'state' => $_SESSION['state'] ?? $user['state'],
    'district' => $_SESSION['district'] ?? $user['district'],
    'pincode' => $_SESSION['pincode'] ?? $user['pincode'],
];

?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Payment - LoveBear</title>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<style>
  :root {
    --light-pink: #ffe4ec;
    --soft-rose: #ffd6e0;
    --rose: #ffb6c1;
    --deep-rose: #d26b8c;
    --cream: #fff9f9;
    --text: #3a2c32;
    --muted: #766d70;
    --white: #ffffff;
    --shadow: 0 6px 18px rgba(0,0,0,0.08);
    --glow: 0 0 20px rgba(255,182,193,0.3);
  }

  * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Nunito', sans-serif; }

  body {
    background: linear-gradient(180deg, var(--cream), var(--light-pink));
    color: var(--text);
    padding: 40px 20px;
  }

  .wrap {
    max-width: 900px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 30px;
  }

  h1 {
    text-align: center;
    font-size: 36px;
    font-weight: 800;
    color: var(--deep-rose);
    margin-bottom: 20px;
  }

  .card {
    background: var(--white);
    border-radius: 24px;
    padding: 30px;
    box-shadow: var(--shadow);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
  }

  .card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.12), var(--glow);
  }

  h3, h4 {
    color: var(--deep-rose);
    margin-bottom: 12px;
  }

  ul {
    list-style: none;
    margin-bottom: 16px;
  }

  ul li {
    padding: 8px 0;
    border-bottom: 1px solid var(--soft-rose);
    font-size: 16px;
    display: flex;
    justify-content: space-between;
  }

  p {
    font-size: 15px;
    color: var(--muted);
    line-height: 1.5;
  }

  .total {
    font-weight: 800;
    color: var(--deep-rose);
    font-size: 18px;
    margin-top: 12px;
  }

  .shipping-info {
    background: var(--soft-rose);
    padding: 18px;
    border-radius: 16px;
    margin-top: 16px;
    font-size: 15px;
    line-height: 1.4;
    color: var(--text);
  }

  #rzp-button {
    display: block;
    width: 100%;
    text-align: center;
    margin-top: 28px;
    padding: 16px 0;
    font-size: 18px;
    font-weight: 700;
    border: none;
    border-radius: 999px;
    background: linear-gradient(90deg, var(--soft-rose), var(--rose));
    color: var(--text);
    cursor: pointer;
    box-shadow: var(--shadow);
    transition: all 0.3s ease;
  }

  #rzp-button:hover {
    transform: translateY(-3px);
    box-shadow: var(--glow);
  }

  @media (max-width: 600px) {
    .wrap {
      padding: 0 10px;
    }
    ul li {
      flex-direction: column;
      align-items: flex-start;
    }
    .total {
      text-align: right;
    }
  }
</style>
</head>
<body>
<div class="wrap">
  <h1>Checkout</h1>

  <div class="card">
    <h3>Order Summary</h3>
    <ul>
      <?php foreach ($order_items as $oi): ?>
        <li>
          <span><?php echo e($oi['name']); ?> × <?php echo (int)$oi['qty']; ?></span>
          <span>₹ <?php echo number_format($oi['subtotal'],2); ?></span>
        </li>
      <?php endforeach; ?>
    </ul>
    <p class="total">Total: ₹ <?php echo number_format($total,2); ?></p>

    <h4>Shipping Details</h4>
    <div class="shipping-info">
      <?php echo e($shipping['name']); ?>, <?php echo e($shipping['phone']); ?><br>
      <?php echo nl2br(e($shipping['address'])); ?><br>
      <?php echo e($shipping['district']); ?>, <?php echo e($shipping['state']); ?> — <?php echo e($shipping['pincode']); ?>
    </div>

    <button id="rzp-button">Pay ₹ <?php echo number_format($total,2); ?></button>
  </div>
</div>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
const options = {
    "key": "<?php echo RAZORPAY_KEY_ID; ?>",
    "amount": "<?php echo $amountPaise; ?>",
    "currency": "INR",
    "name": "LoveBear",
    "description": "Order Payment",
    "order_id": "<?php echo $razorpay_order_id; ?>",
    "handler": function (response){
    $.post('success.php', {
        razorpay_payment_id: response.razorpay_payment_id,
        razorpay_order_id: response.razorpay_order_id,
        razorpay_signature: response.razorpay_signature
    }, function(data){
        try {
    const res = (typeof data === 'string') ? JSON.parse(data) : data;
    if (res.ok && res.redirect) {
    // Optional: show a temporary success popup without blocking
    const notice = document.createElement('div');
    notice.textContent = '✅ Payment Successful! Redirecting...';
    notice.style.position = 'fixed';
    notice.style.top = '20px';
    notice.style.right = '20px';
    notice.style.background = '#d1fae5';
    notice.style.color = '#065f46';
    notice.style.padding = '12px 18px';
    notice.style.borderRadius = '8px';
    notice.style.boxShadow = '0 4px 12px rgba(0,0,0,0.1)';
    notice.style.fontWeight = 'bold';
    document.body.appendChild(notice);

    setTimeout(() => {
        window.location.href = res.redirect;
    }, 1500); // 1.5s delay for a smoother transition
}
 else if (res.msg) {
        alert('⚠️ ' + res.msg);
    } else {
        alert('⚠️ Unexpected server reply: ' + JSON.stringify(res));
    }
} catch(e) {
    alert('⚠️ Failed to process server response: ' + data);
}

    });
},

    "prefill": {
        "name": "<?php echo e($shipping['name']); ?>",
        "email": "<?php echo e($user['email']); ?>",
        "contact": "<?php echo e($shipping['phone']); ?>"
    },
    "theme": {
        "color": "#d26b8c"
    }
};

document.getElementById('rzp-button').onclick = function(e){
    const rzp = new Razorpay(options);
    rzp.open();
    e.preventDefault();
};
</script>
</body>
</html>
