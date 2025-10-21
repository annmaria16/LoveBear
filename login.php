<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'config.php';

class UserAuth {
  private $pdo;
  public $error = "";

  public function __construct($pdo) {
    $this->pdo = $pdo;
  }

  public function login($username, $password) {
    if (empty($username) || empty($password)) {
      $this->error = "All fields are required.";
      return false;
    }

    $stmt = $this->pdo->prepare("SELECT * FROM users WHERE username=:u OR email=:u LIMIT 1");
    $stmt->execute([':u' => $username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
      $_SESSION['user_id'] = $user['id'];
      $_SESSION['user'] = $user['username'];
      $_SESSION['role'] = $user['role'];
      $_SESSION['phone'] = $user['phone'] ?? null;
      $_SESSION['address'] = $user['address'] ?? null;
      $_SESSION['state'] = $user['state'] ?? null;
      $_SESSION['district'] = $user['district'] ?? null;
      $_SESSION['pincode'] = $user['pincode'] ?? null;
      return true;
    } else {
      $this->error = "Invalid username/email or password.";
      return false;
    }
  }
}

// redirect if already logged in
if (!empty($_SESSION['user'])) {
  header("Location: " . ($_SESSION['role'] === 'admin' ? 'admin.php' : 'index.php'));
  exit;
}

$auth = new UserAuth($pdo);

// handle login attempt
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username'] ?? '');
  $password = $_POST['password'] ?? '';
  if ($auth->login($username, $password)) {
    header("Location: " . ($_SESSION['role'] === 'admin' ? 'admin.php' : 'index.php'));
    exit;
  }
}
?>

<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Login - LoveBear</title>
<style>
body {
  font-family: sans-serif;
  background: #fff6f8;
  color: #3a2c32;
  padding: 40px;
}
.container {
  max-width: 400px;
  margin: 0 auto;
  background: #fff;
  padding: 20px;
  border-radius: 12px;
  box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
}
h1 {
  text-align: center;
  color: #d26b8c;
}
input[type=text],
input[type=password] {
  width: 100%;
  padding: 10px;
  margin: 8px 0;
  border: 1px solid #f0d7df;
  border-radius: 8px;
}
.btn {
  background: #ffb6c1;
  border: none;
  padding: 10px 16px;
  width: 100%;
  border-radius: 8px;
  cursor: pointer;
  font-weight: bold;
}
.btn:hover {
  background: #f79db1;
}
.error {
  background: #ffdede;
  padding: 10px;
  border-radius: 6px;
  color: #7a1a1a;
  margin-bottom: 10px;
  text-align: center;
}
a {
  color: #d26b8c;
  text-decoration: none;
}
a:hover {
  text-decoration: underline;
}
.valid { border-color: #88cc88; }
.invalid { border-color: #e57373; }
</style>
</head>
<body>
<div class="container">
  <h1>Welcome Back 💗</h1>

  <?php if (!empty($error)): ?>
    <div class="error"><?= e($error) ?></div>
  <?php endif; ?>

  <form method="post" id="loginForm">
    <input type="text" id="username" name="username" placeholder="Registered Email or Username" required>
    <input type="password" id="password" name="password" placeholder="Password" required>
    <button class="btn" type="submit">Login</button>
  </form>

  <p style="text-align:center;margin-top:10px;">
    Don’t have an account? <a href="register.php">Register</a>
  </p>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const username = document.getElementById('username');
  const password = document.getElementById('password');
  const form = document.getElementById('loginForm');

  function validateField(field, minLength) {
    if (field.value.trim().length >= minLength) {
      field.classList.add('valid');
      field.classList.remove('invalid');
      return true;
    } else {
      field.classList.add('invalid');
      field.classList.remove('valid');
      return false;
    }
  }

  username.addEventListener('input', () => validateField(username, 3));
  password.addEventListener('input', () => validateField(password, 5));

  form.addEventListener('submit', e => {
    const userOK = validateField(username, 3);
    const passOK = validateField(password, 5);
    if (!userOK || !passOK) {
      e.preventDefault();
      alert("Please fill all fields correctly before submitting.");
    }
  });
});
</script>
</body>
</html>
