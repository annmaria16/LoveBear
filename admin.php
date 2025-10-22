<?php
// admin.php
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


if (empty($_SESSION['user']) || ($_SESSION['role'] ?? '') !== 'admin') {
  header("Location: login.php");
  exit;
}

$uploadDir = __DIR__ . '/uploads/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

$error = null;

// Handle create/edit/delete actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'create') {
        $name = trim($_POST['name'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        $price = number_format((float)($_POST['price'] ?? 0), 2, '.', '');
        $stock = (int)($_POST['stock'] ?? 0);
        $imageName = null;

        if (!empty($_FILES['image']['name'])) {
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','webp','gif','avif'];
            if (!in_array($ext, $allowed)) {
                $error = "Invalid image type. Allowed: " . implode(', ', $allowed);
            } else {
                $imageName = uniqid('p_') . '.' . $ext;
                move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $imageName);
            }
        }

        if (!isset($error)) {
            $stmt = $pdo->prepare("INSERT INTO products (name, description, price, image, stock) VALUES (:name, :desc, :price, :image, :stock)");
            $stmt->execute([
                ':name' => $name,
                ':desc' => $desc,
                ':price' => $price,
                ':image' => $imageName,
                ':stock' => $stock
            ]);
            header("Location: admin.php");
            exit;
        }
    }

    if ($action === 'delete' && !empty($_POST['id'])) {
        $id = (int)$_POST['id'];
        $p = getProduct($pdo, $id);
        if ($p && !empty($p['image']) && file_exists($uploadDir . $p['image'])) {
            @unlink($uploadDir . $p['image']);
        }
        $pdo->prepare("DELETE FROM products WHERE id = :id")->execute([':id' => $id]);
        header("Location: admin.php");
        exit;
    }

    if ($action === 'edit' && !empty($_POST['id'])) {
        $id = (int)$_POST['id'];
        $name = trim($_POST['name'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        $price = number_format((float)($_POST['price'] ?? 0), 2, '.', '');
        $stock = (int)($_POST['stock'] ?? 0);

        $p = getProduct($pdo, $id);
        $imageName = $p['image'] ?? null;

        if (!empty($_FILES['image']['name'])) {
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','webp','gif','avif'];
            if (!in_array($ext, $allowed)) {
                $error = "Invalid image type.";
            } else {
                if (!empty($imageName) && file_exists($uploadDir . $imageName)) @unlink($uploadDir . $imageName);
                $imageName = uniqid('p_') . '.' . $ext;
                move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $imageName);
            }
        }

        if (!isset($error)) {
            $stmt = $pdo->prepare("UPDATE products SET name=:name, description=:desc, price=:price, image=:image, stock=:stock WHERE id=:id");
            $stmt->execute([
                ':name'=>$name, ':desc'=>$desc, ':price'=>$price, ':image'=>$imageName, ':stock'=>$stock, ':id'=>$id
            ]);
            header("Location: admin.php");
            exit;
        }
    }
}

// Load products
$products = $pdo->query("SELECT * FROM products ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Admin - LoveBear</title>
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
  <style>
    :root { --light-pink: #ffe4ec; --soft-rose: #ffd6e0; --rose: #ffb6c1; --cream: #fff9f9; --deep-rose: #d26b8c; --text: #3a2c32; --muted: #766d70; --white: #ffffff; --shadow: 0 6px 18px rgba(0, 0, 0, 0.08); }
    body{font-family:'Nunito',sans-serif;background:linear-gradient(180deg,var(--cream),var(--light-pink));color:var(--text);margin:0;padding:0;}
    header { display:flex;justify-content:space-between;align-items:center;padding:20px 60px;background:rgba(255,255,255,0.7);backdrop-filter:blur(10px);box-shadow:0 2px 10px rgba(0,0,0,0.05);position:sticky;top:0;z-index:100;flex-wrap:wrap; }
    .logo { font-size:28px;font-weight:800;color:var(--deep-rose);letter-spacing:1px; }
    nav { display:flex;align-items:center;gap:20px;flex-wrap:wrap; }
    nav a { text-decoration:none;color:var(--text);font-weight:600;transition:color 0.3s ease; }
    nav a:hover { color:var(--deep-rose); }
    .wrap{max-width:1000px;margin:40px auto;padding:20px;}
    h1{color:var(--deep-rose);margin-bottom:20px;}
    form{background:#fff;border-radius:12px;padding:16px;box-shadow:var(--shadow);margin-bottom:20px}
    label{display:block;margin:8px 0 4px}
    input[type=text], textarea, input[type=number]{width:100%;padding:8px;border:1px solid #f0d7df;border-radius:6px}
    table{width:100%;border-collapse:collapse}
    th,td{padding:8px;border-bottom:1px solid #f2d6de;text-align:left}
    img{max-width:120px;border-radius:8px}
    .small{font-size:13px;color:var(--muted)}
    .btn{background:var(--rose);padding:8px 12px;border-radius:8px;border:none;cursor:pointer;font-weight:600;}
    .btn:hover{background:var(--soft-rose);}
    .danger{background:#f5a3af;}
  </style>
</head>
<body>

<header>
   <div class="logo"><a src="admin.php">LoveBear</div>
  <nav>
    <a href="index.php">Home</a>
    <div style="text-align:right;margin-bottom:10px">
      <form method="post" action="logout.php" style="display:inline">
        <button class="btn danger" type="submit" style="padding:4px 8px;font-size:13px;">Logout</button>
      </form>
    </div>
  </nav>
</header>

<div class="wrap">
  <h1>Admin Dashboard — LoveBear</h1>

  <?php if (!empty($error)): ?>
    <div style="padding:10px;background:#ffdede;border-radius:6px;color:#7a1a1a;margin-bottom:10px;"><?php echo e($error); ?></div>
  <?php endif; ?>

  <form method="post" enctype="multipart/form-data">
    <h2>Add new product</h2>
    <input type="hidden" name="action" value="create">
    <label>Name</label>
    <input type="text" name="name" required>
    <label>Description</label>
    <textarea name="description" rows="4"></textarea>
    <label>Price (INR)</label>
    <input type="number" name="price" step="0.01" required>
    <label>Stock (quantity)</label>
    <input type="number" name="stock" step="1" min="0" value="1" required>
    <label>Image (jpg, png, webp, avif)</label>
    <input type="file" name="image" accept="image/*">
    <div style="margin-top:10px">
      <button class="btn" type="submit">Create Product</button>
    </div>
  </form>

  <h2>Existing Products</h2>
  <table>
    <thead><tr><th>Image</th><th>Name</th><th>Price</th><th>Stock</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach ($products as $p): ?>
      <tr>
        <td>
          <?php if (!empty($p['image']) && file_exists('uploads/'.$p['image'])): ?>
            <img src="uploads/<?php echo e($p['image']); ?>" alt="">
          <?php else: ?>
            <div class="small">No image</div>
          <?php endif; ?>
        </td>
        <td><?php echo e($p['name']); ?><div class="small"><?php echo e($p['description']); ?></div></td>
        <td>₹ <?php echo number_format($p['price'],2); ?></td>
        <td><?php echo (int)$p['stock']; ?></td>
        <td>
          <button class="btn" onclick="document.getElementById('edit-<?php echo $p['id']; ?>').style.display='block'">Edit</button>
          <form method="post" style="display:inline" onsubmit="return confirm('Delete?');">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
            <button class="btn danger" type="submit">Delete</button>
          </form>
          <div id="edit-<?php echo $p['id']; ?>" style="display:none;margin-top:10px">
            <form method="post" enctype="multipart/form-data">
              <input type="hidden" name="action" value="edit">
              <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
              <label>Change name</label>
              <input type="text" name="name" value="<?php echo e($p['name']); ?>" required>
              <label>Description</label>
              <textarea name="description"><?php echo e($p['description']); ?></textarea>
              <label>Price</label>
              <input type="number" name="price" value="<?php echo $p['price']; ?>" step="0.01" required>
              <label>Stock</label>
              <input type="number" name="stock" value="<?php echo (int)$p['stock']; ?>" required>
              <label>Replace image</label>
              <input type="file" name="image" accept="image/*">
              <div style="margin-top:6px">
                <button class="btn" type="submit">Save</button>
                <button type="button" onclick="this.parentElement.parentElement.style.display='none'">Cancel</button>
              </div>
            </form>
          </div>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<script>
document.addEventListener("DOMContentLoaded", () => {
  const forms = document.querySelectorAll("form");

  forms.forEach(form => {
    form.querySelectorAll("input, textarea").forEach(field => {
      const errorMsg = document.createElement("div");
      errorMsg.className = "live-error";
      errorMsg.style.color = "#d02626";
      errorMsg.style.fontSize = "13px";
      errorMsg.style.display = "none";
      errorMsg.style.marginTop = "3px";
      field.insertAdjacentElement("afterend", errorMsg);

      const validateField = () => {
        let error = "";
        const value = field.value.trim();

        // Required field check
        if (field.hasAttribute("required") && !value && field.type !== "file") {
          error = "This field is required.";
        }

        // File validation (image required for create form)
        if (field.type === "file") {
          const isCreateForm = form.querySelector("input[name='action'][value='create']");
          const file = field.files[0];

          if (isCreateForm && !file) {
            error = "Image is required.";
          } else if (file) {
            const allowed = ["image/jpeg", "image/png", "image/webp", "image/avif", "image/gif"];
            if (!allowed.includes(file.type)) {
              error = "Invalid image type. Allowed: JPG, PNG, WEBP, AVIF, GIF.";
            }
          }
        }

        // Numeric validation
        if (field.type === "number" && value) {
          const num = parseFloat(value);
          if (isNaN(num)) {
            error = "Please enter a valid number.";
          } else if (field.name === "price" && num <= 0) {
            error = "Price must be greater than 0.";
          } else if (field.name === "stock" && num < 0) {
            error = "Stock cannot be negative.";
          }
        }

        // Show or hide errors
        if (error) {
          errorMsg.textContent = error;
          errorMsg.style.display = "block";
          field.style.borderColor = "#d02626";
        } else {
          errorMsg.textContent = "";
          errorMsg.style.display = "none";
          field.style.borderColor = "#ccc";
        }
      };

      field.addEventListener("input", validateField);
      field.addEventListener("change", validateField);
      field.addEventListener("blur", validateField);
    });

    // Prevent submission if validation fails
    form.addEventListener("submit", (e) => {
      let hasError = false;
      form.querySelectorAll("input, textarea").forEach(field => {
        const event = new Event("blur");
        field.dispatchEvent(event);
        const err = field.nextElementSibling;
        if (err && err.style.display === "block") {
          hasError = true;
        }
      });
      if (hasError) {
        e.preventDefault();
        alert("Please correct the highlighted errors before submitting.");
      }
    });
  });
});
</script>

</body>
</html>
