<?php
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

class Cart {
    private $pdo;
    public function __construct($pdo) {
        $this->pdo = $pdo;
        if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
    }

    public function update($productId, $qty) {
        $product = getProduct($this->pdo, $productId);
        if (!$product) return false;
        $stock = (int)$product['stock'];
        $qty = max(1, min($stock, (int)$qty));
        $_SESSION['cart'][$productId] = $qty;
        return true;
    }

    public function remove($productId) {
        unset($_SESSION['cart'][$productId]);
    }

    public function items() {
        return $_SESSION['cart'];
    }
}

$cart = new Cart($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['action'] === 'update_single') {
        $cart->update((int)$_POST['id'], (int)$_POST['qty']);
        echo json_encode(['ok'=>true]);
        exit;
    }
    if ($_POST['action'] === 'remove') {
        $cart->remove((int)$_POST['id']);
        header("Location: cart.php");
        exit;
    }
    if ($_POST['action'] === 'checkout') {
        header("Location: shipping.php");
        exit;
    }
}

$items = $cart->items();
$productRows = [];
$total = 0.0;

if ($items) {
    $in = implode(',', array_fill(0, count($items), '?'));
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($in)");
    $stmt->execute(array_keys($items));
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $r) {
        $pid = $r['id'];
        $qty = $items[$pid];
        $subtotal = $r['price'] * $qty;
        $productRows[] = ['product'=>$r, 'qty'=>$qty, 'subtotal'=>$subtotal];
        $total += $subtotal;
    }
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Your Cart - LoveBear</title>
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
<style>
:root { --light-pink: #ffe4ec; --soft-rose: #ffd6e0; --rose: #ffb6c1; --cream: #fff9f9; --deep-rose: #d26b8c; --text: #3a2c32; --muted: #766d70; --white: #ffffff; --shadow: 0 6px 18px rgba(0,0,0,0.08); --glow: 0 0 20px rgba(255,182,193,0.3); }
body{font-family:'Nunito',sans-serif;background:linear-gradient(180deg,var(--cream),var(--light-pink));color:var(--text);padding:0;margin:0;}
header {display:flex;justify-content:space-between;align-items:center;padding:20px 60px;background:rgba(255,255,255,0.7);backdrop-filter:blur(10px);box-shadow:0 2px 10px rgba(0,0,0,0.05);position:sticky;top:0;z-index:100;flex-wrap:wrap;}
.logo{font-size:28px;font-weight:800;color:var(--deep-rose);}
nav{display:flex;align-items:center;gap:20px;flex-wrap:wrap;}
nav a{text-decoration:none;color:var(--text);font-weight:600;transition:0.3s;}
nav a:hover{color:var(--deep-rose);}
.wrap{max-width:900px;margin:40px auto;padding:0 20px;}
h1{text-align:center;color:var(--deep-rose);}
table{width:100%;border-collapse:collapse;background:var(--white);border-radius:8px;box-shadow:var(--shadow);margin-top:20px;}
th,td{padding:12px;border-bottom:1px solid #f2d6de;text-align:left;}
img{width:90px;border-radius:8px;}
.qty-controls{display:flex;align-items:center;gap:6px;}
.qty-btn{background:var(--soft-rose);border:none;border-radius:6px;width:58px;height:28px;cursor:pointer;font-weight:bold;}
.checkout-btn{margin-top:20px;width:100%;padding:14px 0;font-size:18px;border-radius:999px;background:linear-gradient(90deg,var(--soft-rose),var(--rose));font-weight:700;color:var(--text);border:none;cursor:pointer;}
.checkout-btn:hover{transform:translateY(-3px);box-shadow:var(--glow);}
</style>
</head>
<body>

<header>
  <div class="logo">LoveBear</div>
  <nav>
    <a href="index.php">Home</a>
    <a href="cart.php">Cart (<?php echo array_sum($_SESSION['cart'] ?? []); ?>)</a>
    <form method="post" action="logout.php" style="display:inline">
      <button class="qty-btn" type="submit">Logout</button>
    </form>
  </nav>
</header>

<div class="wrap">
<h1>Your Cart</h1>

<?php if (empty($productRows)): ?>
<p>Your cart is empty. <a href="index.php">Go shopping</a></p>
<?php else: ?>
<table>
<thead><tr><th>Item</th><th>Price</th><th>Qty</th><th>Subtotal</th><th></th></tr></thead>
<tbody id="cartBody">
<?php foreach ($productRows as $row):
$p = $row['product'];
$qty = $row['qty'];
$max = (int)$p['stock'];
$subtotal = $row['subtotal'];
$img = !empty($p['image']) && file_exists('uploads/'.$p['image']) ? 'uploads/'.$p['image'] : 'images/pic2.jpg';
?>
<tr data-id="<?php echo $p['id']; ?>" data-price="<?php echo $p['price']; ?>" data-max="<?php echo $max; ?>">
<td><img src="<?php echo e($img); ?>" alt=""><br><?php echo e($p['name']); ?></td>
<td>₹ <?php echo number_format($p['price'],2); ?></td>
<td>
  <div class="qty-controls">
    <button class="qty-btn minus">−</button>
    <span class="qty"><?php echo $qty; ?></span>
    <button class="qty-btn plus">+</button>
  </div>
</td>
<td class="subtotal">₹ <?php echo number_format($subtotal,2); ?></td>
<td>
  <form method="post">
    <input type="hidden" name="action" value="remove">
    <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
    <button class="qty-btn" style="background:#f5a3af;">x</button>
  </form>
</td>
</tr>
<?php endforeach; ?>
<tr>
<td colspan="3" style="text-align:right;font-weight:800">Total</td>
<td colspan="2" style="font-weight:800" id="cartTotal">₹ <?php echo number_format($total,2); ?></td>
</tr>
</tbody>
</table>

<form method="post">
<input type="hidden" name="action" value="checkout">
<button class="checkout-btn" type="submit">Checkout — Pay ₹ <span id="checkoutTotal"><?php echo number_format($total,2); ?></span></button>
</form>
<?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const rows = document.querySelectorAll('#cartBody tr[data-id]');
  const cartTotalEl = document.getElementById('cartTotal');
  const checkoutTotalEl = document.getElementById('checkoutTotal');

  function updateTotal() {
    let total = 0;
    rows.forEach(r => {
      const price = parseFloat(r.dataset.price);
      const qty = parseInt(r.querySelector('.qty').textContent);
      const subtotal = price * qty;
      r.querySelector('.subtotal').textContent = '₹ ' + subtotal.toFixed(2);
      total += subtotal;
    });
    cartTotalEl.textContent = '₹ ' + total.toFixed(2);
    checkoutTotalEl.textContent = total.toFixed(2);
  }

  function sendUpdate(id, qty) {
    fetch('cart.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: new URLSearchParams({action:'update_single', id, qty})
    });
  }

  rows.forEach(r => {
    const plus = r.querySelector('.plus');
    const minus = r.querySelector('.minus');
    const qtyEl = r.querySelector('.qty');
    const max = parseInt(r.dataset.max);
    const id = parseInt(r.dataset.id);

    plus.addEventListener('click', () => {
      let q = parseInt(qtyEl.textContent);
      if (q < max) {
        q++;
        qtyEl.textContent = q;
        sendUpdate(id, q);
        updateTotal();
      } else {
        
        plus.disabled = true;
        plus.style.opacity = '0.5';
        alert('You have reached the maximum stock limit for this item.');
      }
    });

    minus.addEventListener('click', () => {
      let q = parseInt(qtyEl.textContent);
      if (q > 1) {
        q--;
        qtyEl.textContent = q;
        sendUpdate(id, q);
        updateTotal();
        plus.disabled = false;
        plus.style.opacity = '1';
      }
    });
  });
});
</script>


</body>
</html>
