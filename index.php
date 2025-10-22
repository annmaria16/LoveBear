<?php
// index.php
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

// require login
if (empty($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// Simple Cart class (session-backed)
class Cart {
    private $pdo;
    public function __construct($pdo) {
        $this->pdo = $pdo;
        if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
    }

    public function add($productId, $qty=1) {
        $product = getProduct($this->pdo, $productId);
        if (!$product) return false;
        $stock = (int)$product['stock'];
        if ($stock <= 0) return false;
        $current = isset($_SESSION['cart'][$productId]) ? (int)$_SESSION['cart'][$productId] : 0;
        $newQty = min($stock, $current + $qty);
        $_SESSION['cart'][$productId] = $newQty;
        return true;
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

    public function getItems() {
        return $_SESSION['cart'];
    }

    public function clear() {
        $_SESSION['cart'] = [];
    }
}

$cart = new Cart($pdo);

// Handle add-to-cart actions (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $id = (int)$_POST['id'];
        $qty = isset($_POST['qty']) ? max(1, (int)$_POST['qty']) : 1;
        $cart->add($id, $qty);
        // Buy Now -> go to cart
        if (!empty($_POST['buy_now'])) {
            header("Location: cart.php");
            exit;
        } else {
            header("Location: index.php");
            exit;
        }
    }
}

// Fetch products
$stmt = $pdo->query("SELECT * FROM products ORDER BY created_at DESC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>LoveBear – “Every hug tells a story.”</title>
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
  <style>
    /* your CSS (same as earlier) */
    :root { --light-pink: #ffe4ec; --soft-rose: #ffd6e0; --rose: #ffb6c1; --cream: #fff9f9; --deep-rose: #d26b8c; --text: #3a2c32; --muted: #766d70; --white: #ffffff; --shadow: 0 6px 18px rgba(0, 0, 0, 0.08); --glow: 0 0 20px rgba(255, 182, 193, 0.3); } 
    * { box-sizing: border-box; margin: 0; padding: 0; }
     body { font-family: 'Nunito', sans-serif; background: linear-gradient(180deg, var(--cream), var(--light-pink)); color: var(--text); overflow-x: hidden; } 
     header { display: flex; justify-content: space-between; align-items: center; padding: 20px 60px; background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(10px); box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05); position: sticky; top: 0; z-index: 100; flex-wrap: wrap; }
      .logo { font-size: 28px; font-weight: 800; color: var(--deep-rose); letter-spacing: 1px; }
       nav { display: flex; align-items: center; gap: 20px; flex-wrap: wrap; position: relative; } 
       nav a { text-decoration: none; color: var(--text); font-weight: 600; transition: color 0.3s ease; }
        nav a:hover { color: var(--deep-rose); } 
        .search-container { position: relative; }
         .search-bar { display: flex; align-items: center; background: var(--white); border-radius: 999px; box-shadow: var(--shadow); overflow: hidden; padding: 5px 15px; } 
         .search-bar input { border: none; outline: none; padding: 8px 10px; font-size: 14px; width: 200px; background: transparent; }
          .search-bar button { background: var(--rose); border: none; border-radius: 999px; padding: 8px 14px; font-weight: 700; cursor: pointer; color: var(--text); transition: 0.3s; } 
          .search-bar button:hover { background: var(--soft-rose); }
           .search-results { position: absolute; top: 50px; left: 0; width: 100%; background: var(--white); border-radius: 12px; box-shadow: var(--shadow); overflow: hidden; display: none; z-index: 200; } 
           .search-results .result-item { display: flex; align-items: center; gap: 10px; padding: 10px 14px; border-bottom: 1px solid #f2d6de; cursor: pointer; transition: background 0.2s; }
            .search-results .result-item:hover { background: var(--soft-rose); } 
            .search-results img { width: 40px; height: 40px; border-radius: 8px; object-fit: cover; }
             .search-results h4 { font-size: 15px; color: var(--deep-rose); } 
             .hero { display: flex; align-items: center; justify-content: center; gap: 60px; padding: 80px 60px; flex-wrap: wrap; text-align: left; } 
             .hero-content { max-width: 500px; animation: fadeInUp 1s ease; } 
             .hero h1 { font-size: 48px; font-weight: 800; color: var(--deep-rose); margin-bottom: 16px; line-height: 1.2; }
              .hero p { font-size: 18px; color: var(--muted); margin-bottom: 28px; }
               .hero img { width: 340px; border-radius: 28px; box-shadow: var(--shadow), var(--glow); transition: transform 0.3s ease; animation: float 4s ease-in-out infinite; } 
               .hero img:hover { transform: scale(1.03); } 
               .btn { background: linear-gradient(90deg, var(--soft-rose), var(--rose)); color: var(--text); padding: 14px 32px; border-radius: 999px; text-decoration: none; font-weight: 700; box-shadow: var(--shadow); transition: all 0.3s ease; } 
               .btn:hover { transform: translateY(-3px); box-shadow: var(--glow); }
                @keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-8px); } }
                 @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
                  .products { padding: 80px 60px; background: rgba(255, 255, 255, 0.6); } 
                  .section-title { text-align: center; font-size: 34px; color: var(--deep-rose); font-weight: 800; margin-bottom: 40px; }
                   .product-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 28px; justify-content: center; } .card { background: var(--white); border-radius: 20px; box-shadow: var(--shadow); padding: 20px; text-align: center; transition: transform 0.3s ease, box-shadow 0.3s ease; } 
                   .card:hover { transform: translateY(-6px); box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12), var(--glow); }
                    .card img {
    width: 100%;
    aspect-ratio: 1 / 1;
    border-radius: 16px;
    margin-bottom: 14px;
    object-fit: cover;
}

                     .card h3 { font-size: 20px; color: var(--deep-rose); margin-bottom: 8px; } 
    .card p { font-size: 15px; color: var(--muted); margin-bottom: 18px; } 
   .button-group { display: flex; justify-content: center; gap: 10px; } 
   .btn-secondary { background: #fff0f3; color: var(--deep-rose); border: 2px solid #ffd6e0; } 
   .btn-secondary:hover { background: #ffd6e0; box-shadow: var(--glow); } 
   footer { background: rgba(255, 255, 255, 0.7); text-align: center; padding: 24px; font-size: 14px; color: var(--muted); border-top: 1px solid rgba(0, 0, 0, 0.05); }
  @media (max-width: 900px) { .product-grid { grid-template-columns: repeat(2, 1fr); } }
   @media (max-width: 600px) { .product-grid { grid-template-columns: 1fr; } }

  </style>
</head>
<body>

<header>
  <div class="logo"><a src="index.php">LoveBear</div>
  <nav>
    <a href="index.php">Home</a>
    <a href="cart.php">Cart (<?php echo array_sum($_SESSION['cart'] ?? []); ?>)</a>
    <div class="search-container">
      <div class="search-bar">
        <input type="text" id="searchInput" placeholder="Search soft toys..." onkeyup="liveSearch()">
        <button onclick="liveSearch()">Search</button>
      </div>
      <div id="searchResults" class="search-results"></div>
    </div>
    <div style="text-align:right;margin-bottom:10px">
      <form method="post" action="logout.php" style="display:inline">
        <button class="btn danger" type="submit" style="padding:4px 8px;font-size:13px;">Logout</button>
      </form>
    </div>
  </nav>
</header>

<section class="hero">
  <div class="hero-content">
    <h1>“Every hug tells a story.”</h1>
    <p>Discover cuddly companions made with love and care. Designed to bring warmth, comfort, and a smile to every heart.</p>
    <a href="#products" class="btn">Shop Now</a>
  </div>
  <?php
    // pick first product image if exists
    $heroImg = 'images/pic1.avif';
    
  ?>
  <img src="<?php echo e($heroImg); ?>" alt="Soft toy collection">
</section>

<section class="products" id="products">
  <h2 class="section-title">Our Cuddly Collection</h2>
  <div class="product-grid" id="productGrid">
    <?php foreach ($products as $p):
      $imgPath = !empty($p['image']) && file_exists('uploads/'.$p['image']) ? 'uploads/' . $p['image'] : 'images/pic2.jpg';
      $safeName = e($p['name']);
    ?>
      <div class="card" data-name="<?php echo $safeName; ?>" data-img="<?php echo $imgPath; ?>">
        <img src="<?php echo $imgPath; ?>" alt="<?php echo $safeName; ?>">
        <h3><?php echo $safeName; ?></h3>
        <p><?php echo e($p['description']); ?></p>
        <p style="font-weight:800; color:var(--deep-rose);">₹ <?php echo number_format($p['price'],2); ?></p>
        <?php if ((int)$p['stock'] <= 0): ?>
          <p style="color:#c0392b; font-weight:700;">Out of stock</p>
          <div class="button-group">
            <button class="btn" disabled>Buy Now</button>
            <button class="btn btn-secondary" disabled>Add to Cart</button>
          </div>
        <?php else: ?>
          <div class="button-group">
            <form method="post" style="display:inline-block">
              <input type="hidden" name="action" value="add">
              <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
              <input type="hidden" name="qty" value="1">
              <button class="btn" type="submit" name="buy_now" value="1">Buy Now</button>
            </form>

            <form method="post" style="display:inline-block">
              <input type="hidden" name="action" value="add">
              <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
              <input type="hidden" name="qty" value="1">
              <button class="btn btn-secondary" type="submit">Add to Cart</button>
            </form>
          </div>
          <p style="font-size:13px; color:var(--muted); margin-top:8px;">Stock: <?php echo (int)$p['stock']; ?></p>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<footer>
  © <?php echo date('Y'); ?> LoveBear | Soft Toys for Babies
</footer>

<script>
function normalize(str) {
  return str.toLowerCase().replace(/[^a-z0-9]/g, '');
}

function liveSearch() {
  const query = normalize(document.getElementById('searchInput').value);
  const cards = document.querySelectorAll('.card');
  const resultsContainer = document.getElementById('searchResults');
  resultsContainer.innerHTML = '';
  resultsContainer.style.display = 'none';

  if (!query) {
    cards.forEach(c => c.style.display = 'block');
    return;
  }

  const matches = [];
  cards.forEach(card => {
    const name = normalize(card.getAttribute('data-name') || '');
    if (name.includes(query)) matches.push(card);
  });

  cards.forEach(c => c.style.display = 'none');
  matches.forEach(m => m.style.display = 'block');

  if (matches.length) {
    resultsContainer.style.display = 'block';
    matches.slice(0, 5).forEach(card => {
      const name = card.getAttribute('data-name');
      const img = card.getAttribute('data-img');
      const item = document.createElement('div');
      item.classList.add('result-item');
      item.innerHTML = `<img src="${img}" alt="${name}"><h4>${name}</h4>`;
      item.onclick = () => {
        document.getElementById('searchInput').value = name;
        resultsContainer.style.display = 'none';
        // filter cards
        cards.forEach(c => c.style.display = (c.getAttribute('data-name') === name) ? 'block' : 'none');
      };
      resultsContainer.appendChild(item);
    });
  }
}
</script>

</body>
</html>
