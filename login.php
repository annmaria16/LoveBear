<?php
require_once 'config.php';

if (!empty($_SESSION['user'])) {
  if (!empty($_SESSION['role']) && $_SESSION['role'] === 'admin') header("Location: admin.php");
  else header("Location: index.php");
  exit;
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username'] ?? '');
  $password = $_POST['password'] ?? '';

  if (empty($username) || empty($password)) {
    $error = "All fields are required.";
  } else {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username=:u OR email=:u LIMIT 1");
    $stmt->execute([':u'=>$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
      // Save useful info in session
      $_SESSION['user_id'] = $user['id'];
      $_SESSION['user'] = $user['username'];
      $_SESSION['role'] = $user['role'];
      // optionally load saved address fields too
      $_SESSION['phone'] = $user['phone'] ?? null;
      $_SESSION['address'] = $user['address'] ?? null;
      $_SESSION['state'] = $user['state'] ?? null;
      $_SESSION['district'] = $user['district'] ?? null;
      $_SESSION['pincode'] = $user['pincode'] ?? null;

      if ($user['role'] === 'admin') header("Location: admin.php");
      else header("Location: index.php");
      exit;
    } else {
      $error = "Invalid username/email or password.";
    }
  }
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Login - LoveBear</title>
<style>
body{font-family:sans-serif;background:#fff6f8;color:#3a2c32;padding:40px}
.container{max-width:400px;margin:0 auto;background:#fff;padding:20px;border-radius:12px;box-shadow:0 6px 18px rgba(0,0,0,0.06)}
h1{text-align:center;color:#d26b8c}
input[type=text],input[type=password]{width:100%;padding:10px;margin:8px 0;border:1px solid #f0d7df;border-radius:8px}
.btn{background:#ffb6c1;border:none;padding:10px 16px;width:100%;border-radius:8px;cursor:pointer;font-weight:bold}
.btn:hover{background:#f79db1}
.error{background:#ffdede;padding:10px;border-radius:6px;color:#7a1a1a;margin-bottom:10px;text-align:center}
a{color:#d26b8c;text-decoration:none}
a:hover{text-decoration:underline}
</style>
</head>
<body>
<div class="container">
<h1>Welcome Back 💗</h1>
<?php if(!empty($error)): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
<form method="post">
  <input type="text" name="username" placeholder="Registered Email">
  <input type="password" name="password" placeholder="Password" required>
  <button class="btn" type="submit">Login</button>
</form>
<p style="text-align:center;margin-top:10px;">Don’t have an account? <a href="register.php">Register</a></p>
</div>
</body>
</html>
