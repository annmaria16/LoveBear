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
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';
  $confirm = $_POST['confirm'] ?? '';

  if (empty($username) || empty($email) || empty($password) || empty($confirm)) {
    $error = "All fields are required.";
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = "Invalid email format.";
  } elseif ($password !== $confirm) {
    $error = "Passwords do not match.";
  } elseif (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d).{5,}$/', $password)) {
    $error = "Password must include 1 uppercase, 1 lowercase, 1 number, and be at least 5 characters.";
  } else {
    // Case-sensitive username + unique email check
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email OR BINARY username = :username LIMIT 1");
    $stmt->execute([':email'=>$email, ':username'=>$username]);

    if ($stmt->fetch()) {
      $error = "Username or email already exists.";
    } else {
      $hash = password_hash($password, PASSWORD_DEFAULT);
      $pdo->prepare("INSERT INTO users (username,email,password,role) VALUES (:u,:e,:p,'user')")
          ->execute([':u'=>$username, ':e'=>$email, ':p'=>$hash]);

      $_SESSION['user_id'] = $pdo->lastInsertId();
      $_SESSION['user'] = $username;
      $_SESSION['role'] = 'user';
      header("Location: index.php");
      exit;
    }
  }
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Register - LoveBear</title>
<style>
body{font-family:sans-serif;background:#fff6f8;color:#3a2c32;padding:40px}
.container{max-width:400px;margin:0 auto;background:#fff;padding:20px;border-radius:12px;box-shadow:0 6px 18px rgba(0,0,0,0.06)}
h1{text-align:center;color:#d26b8c}
input[type=text],input[type=email],input[type=password]{width:100%;padding:10px;margin:8px 0;border:1px solid #f0d7df;border-radius:8px}
.btn{background:#ffb6c1;border:none;padding:10px 16px;width:100%;border-radius:8px;cursor:pointer;font-weight:bold}
.btn:hover{background:#f79db1}
.error{background:#ffdede;padding:10px;border-radius:6px;color:#7a1a1a;margin-bottom:10px;text-align:center}
a{color:#d26b8c;text-decoration:none}
a:hover{text-decoration:underline}
</style>
</head>
<body>
<div class="container">
<h1>Create Account 💕</h1>
<?php if(!empty($error)): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
<form method="post">
  <input type="text" name="username" placeholder="Username" required>
  <input type="email" name="email" placeholder="Email" required>
  <input type="password" name="password" placeholder="Password" required>
  <input type="password" name="confirm" placeholder="Confirm Password" required>
  <button class="btn" type="submit">Register</button>
</form>
<p style="text-align:center;margin-top:10px;">Already have an account? <a href="login.php">Login</a></p>
</div>
</body>
</html>
