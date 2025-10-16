<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}
$user = $_SESSION['user'];
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Bem-vindo</title>
</head>
<body>
  <h2>Bem-vindo, <?php echo htmlspecialchars($user); ?>!</h2>
  <p>Área protegida (simulada).</p>
  <p><a href="logout.php">Sair</a></p>
</body>
</html>

