<?php
session_start();
require_once 'config.php';

class UserAuth {
    private $pdo;
    public $error = "";

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function register($username, $email, $password, $confirm) {
        if (empty($username) || empty($email) || empty($password) || empty($confirm)) {
            $this->error = "All fields are required.";
            return false;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error = "Invalid email format.";
            return false;
        }

        if ($password !== $confirm) {
            $this->error = "Passwords do not match.";
            return false;
        }

        if (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d).{5,}$/', $password)) {
            $this->error = "Password must include uppercase, lowercase, number, and be 5+ chars.";
            return false;
        }

        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = :email OR BINARY username = :username LIMIT 1");
        $stmt->execute([':email' => $email, ':username' => $username]);

        if ($stmt->fetch()) {
            $this->error = "Username or email already exists.";
            return false;
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $this->pdo->prepare("INSERT INTO users (username,email,password,role) VALUES (:u,:e,:p,'user')")
            ->execute([':u' => $username, ':e' => $email, ':p' => $hash]);

        $_SESSION['user_id'] = $this->pdo->lastInsertId();
        $_SESSION['user'] = $username;
        $_SESSION['role'] = 'user';

        return true;
    }
}

if (!empty($_SESSION['user'])) {
    header("Location: " . ($_SESSION['role'] === 'admin' ? 'admin.php' : 'index.php'));
    exit;
}

$auth = new UserAuth($pdo);

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    if (!$auth->register($username, $email, $password, $confirm)) {
        $error = $auth->error;
    } else {
        header("Location: index.php");
        exit;
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
input[type=text],input[type=email],input[type=password]{width:100%;padding:10px;margin:8px 0;border:1px solid #f0d7df;border-radius:8px;font-size:15px}
.btn{background:#ffb6c1;border:none;padding:10px 16px;width:100%;border-radius:8px;cursor:pointer;font-weight:bold}
.btn:hover{background:#f79db1}
.error{background:#ffdede;padding:10px;border-radius:6px;color:#7a1a1a;margin-bottom:10px;text-align:center}
a{color:#d26b8c;text-decoration:none}
a:hover{text-decoration:underline}
.invalid{border-color:#e57373}
.msg{font-size:13px;margin-bottom:6px;color:#e57373;display:none}
</style>
</head>
<body>
<div class="container">
<h1>Create Account 💕</h1>

<?php if(!empty($error)): ?>
<div class="error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="post" id="registerForm" novalidate>
  <label>Username</label>
  <input type="text" id="username" name="username" placeholder="Username" required>
  <div id="userMsg" class="msg"></div>

  <label>Email</label>
  <input type="email" id="email" name="email" placeholder="Email" required>
  <div id="emailMsg" class="msg"></div>

  <label>Password</label>
  <input type="password" id="password" name="password" placeholder="Password" required>
  <div id="passMsg" class="msg"></div>

  <label>Confirm Password</label>
  <input type="password" id="confirm" name="confirm" placeholder="Confirm Password" required>
  <div id="confirmMsg" class="msg"></div>

  <button class="btn" type="submit">Register</button>
</form>

<p style="text-align:center;margin-top:10px;">Already have an account? <a href="login.php">Login</a></p>
</div>

<script>
document.addEventListener('DOMContentLoaded', ()=>{
  const username=document.getElementById('username');
  const email=document.getElementById('email');
  const password=document.getElementById('password');
  const confirm=document.getElementById('confirm');
  const form=document.getElementById('registerForm');

  const userMsg=document.getElementById('userMsg');
  const emailMsg=document.getElementById('emailMsg');
  const passMsg=document.getElementById('passMsg');
  const confirmMsg=document.getElementById('confirmMsg');

  function showError(input, msgElem, message) {
    if (message) {
      input.classList.add('invalid');
      msgElem.textContent = message;
      msgElem.style.display = 'block';
    } else {
      input.classList.remove('invalid');
      msgElem.style.display = 'none';
    }
  }

  function validateUsername() {
    const value = username.value.trim();
    showError(username, userMsg, value.length >= 3 ? "" : "At least 3 characters required.");
  }

  function validateEmail() {
    const value = email.value.trim();
    const valid = /^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(value);
    showError(email, emailMsg, valid ? "" : "Invalid email address.");
  }

  function validatePassword() {
    const value = password.value;
    const valid = /^(?=.*[A-Z])(?=.*[a-z])(?=.*\d).{5,}$/.test(value);
    showError(password, passMsg, valid ? "" : "Use uppercase, lowercase, number (min 5 chars).");
  }

  function validateConfirm() {
    const valid = confirm.value === password.value && confirm.value !== "";
    showError(confirm, confirmMsg, valid ? "" : "Passwords do not match.");
  }

  username.addEventListener('input', validateUsername);
  email.addEventListener('input', validateEmail);
  password.addEventListener('input', ()=>{validatePassword(); validateConfirm();});
  confirm.addEventListener('input', validateConfirm);

  form.addEventListener('submit', e=>{
    validateUsername(); validateEmail(); validatePassword(); validateConfirm();
    if(document.querySelectorAll('.invalid').length){
      e.preventDefault();
      alert("Please fix errors before submitting.");
    }
  });
});
</script>
</body>
</html>
