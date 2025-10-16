<?php
session_start();

// Usuários em memória (username => password)
$USERS = [
    "admin"   => "admin123",
    "labuser" => "WeakPass123",
    "guest"   => "guest"
];

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    if (array_key_exists($username, $USERS) && $USERS[$username] === $password) {
        // Login bem-sucedido
        $_SESSION['user'] = $username;
        header("Location: welcome.php");
        exit;
    } else {
        // Mensagem de erro intencional e consistente
        $error = "Credenciais inválidas";
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Login Lab (PHP)</title>
  <style>
    body { font-family: Arial, sans-serif; padding: 20px; }
    .error { color: red; }
    .box { max-width: 360px; margin: 40px auto; padding: 20px; border: 1px solid #ddd; border-radius: 6px; }
    label { display:block; margin-top:8px; }
  </style>
</head>
<body>
  <div class="box">
    <h2>Login - Lab (PHP)</h2>
    <?php if ($error): ?>
      <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <form method="post" action="">
      <label>Usuário:
        <input type="text" name="username" required autofocus>
      </label>
      <label>Senha:
        <input type="password" name="password" required>
      </label>
      <div style="margin-top:12px">
        <button type="submit">Entrar</button>
      </div>
    </form>
    <p style="font-size:12px;color:#666;margin-top:12px">Usuários de teste: <code>admin/admin123</code>, <code>labuser/WeakPass123</code></p>
  </div>
</body>
</html>

