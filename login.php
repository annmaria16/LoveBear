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

      $_SESSION['user_id'] = $user['id'];
      $_SESSION['user'] = $user['username'];
      $_SESSION['role'] = $user['role'];

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

      return true;
    } else {
      $this->error = "Invalid username/email or password.";
      return false;
    }
  }
}

if (!empty($_SESSION['user'])) {
  header("Location: " . ($_SESSION['role']==='admin' ? 'admin.php' : 'index.php'));
  exit;
}

$auth = new UserAuth($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username'] ?? '');
  $password = $_POST['password'] ?? '';
  if ($auth->login($username, $password)) {
    header("Location: " . ($_SESSION['role']==='admin' ? 'admin.php' : 'index.php'));
    exit;
  }
}

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
<<<<<<< HEAD
.valid{border-color:#88cc88}
.invalid{border-color:#e57373}

</style>
</head>
<body>
<div class="container">
<h1>Welcome Back 💗</h1>

<?php if(!empty($auth->error)): ?><div class="error"><?= htmlspecialchars($auth->error) ?></div><?php endif; ?>
<form method="post" id="loginForm">
  <input type="text" id="username" name="username" placeholder="Registered Email">
  <input type="password" id="password" name="password" placeholder="Password" required>

<?php if(!empty($error)): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
<form method="post">
  <input type="text" name="username" placeholder="Registered Email">
  <input type="password" name="password" placeholder="Password" required>
  <button class="btn" type="submit">Login</button>
</form>
<p style="text-align:center;margin-top:10px;">Don’t have an account? <a href="register.php">Register</a></p>
</div>


<script>
document.addEventListener('DOMContentLoaded', ()=>{
  const username=document.getElementById('username');
  const password=document.getElementById('password');
  const form=document.getElementById('loginForm');

  username.addEventListener('input', ()=>{
    if(username.value.trim().length>=3){
      username.classList.add('valid');
      username.classList.remove('invalid');
    }else{
      username.classList.add('invalid');
      username.classList.remove('valid');
    }
  });

  password.addEventListener('input', ()=>{
    if(password.value.trim().length>=5){
      password.classList.add('valid');
      password.classList.remove('invalid');
    }else{
      password.classList.add('invalid');
      password.classList.remove('valid');
    }
  });

  form.addEventListener('submit', e=>{
    if(username.classList.contains('invalid') || password.classList.contains('invalid')){
      e.preventDefault();
      alert("Please fix highlighted fields before submitting.");
    }
  });
});
</script>
</body>
</html>
