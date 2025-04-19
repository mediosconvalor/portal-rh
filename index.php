<?php
session_start();
if (isset($_SESSION['usuario'])) {
  header('Location: dashboard/usuario/index.php');
  exit();
}

$error = $_SESSION['login_error'] ?? '';
unset($_SESSION['login_error']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Portal MCV</title>
  <link rel="icon" href="https://mediosconvalor.com/wp-content/uploads/2019/03/cropped-favicon-32x32-1-32x32.png">
  <link rel="stylesheet" href="/portal-rh/index/styles.css" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>
  <div class="login-container">
    <img src="/portal-rh/img/logo.png" alt="Logo MCV">
    <h4>No sólo decimos, lo hacemos</h4>
    <h2>Iniciar sesión</h2>
    <form action="/portal-rh/backend/auth.php" method="POST" id="loginForm">
      <input type="email" name="correo" placeholder="Correo electrónico" required>
      <input type="password" name="contrasena" placeholder="Contraseña" required>
      <input type="hidden" name="ubicacion" id="ubicacion">
      <button type="submit" id="btnEntrar">Entrar</button>
    </form>
    <a href="#">¿Olvidaste tu contraseña?</a>
    <?php if (!empty($error)): ?>
      <div class="error-message">❌ Acceso denegado: <?= $error ?></div>
    <?php endif; ?>
  </div>

  <script src="/portal-rh/index/script.js" defer></script>
</body>
</html>