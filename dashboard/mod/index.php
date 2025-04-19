<?php
session_start();

if (!isset($_SESSION['usuario'])) {
  if (isset($_COOKIE['login_token'])) {
    $token = $_COOKIE['login_token'];
    $tokenPath = __DIR__ . "/../../../tokens/{$token}.json";
    if (file_exists($tokenPath)) {
      $_SESSION['usuario'] = json_decode(file_get_contents($tokenPath), true);
    } else {
      header('Location: /portal-rh/index.php');
      exit();
    }
  } else {
    header('Location: /portal-rh/index.php');
    exit();
  }
}

$usuario = $_SESSION['usuario'];
$nombre = $usuario['nombre'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Moderador - Portal MCV</title>
  <link rel="icon" href="https://mediosconvalor.com/wp-content/uploads/2019/03/cropped-favicon-32x32-1-32x32.png">
  <link rel="stylesheet" href="/portal-rh/styles.css" />
  <script src="/portal-rh/script.js" defer></script>
  <link rel="stylesheet" href="/portal-rh/loader/loader.css">
  <script src="/portal-rh/loader/loader.js" defer></script>
</head>
<body>
  <?php include_once($_SERVER['DOCUMENT_ROOT'] . '/portal-rh/loader/loader.php'); ?>
  <!-- Header -->
  <header>
    <button class="nav-toggle" onclick="toggleMenu()">☰</button>
    <h1 id="saludoUsuario"></h1>
    <img src="/portal-rh/img/logo.png" alt="Logo MCV" class="logo">
  </header>

  <!-- Menú -->
  <nav class="menu-mobile" id="menuMobile">
    <a href="/portal-rh/dashboard/usuario/index.php">Inicio</a>
    <a href="/portal-rh/perfil/index.php">Perfil</a>
    <a href="/portal-rh/dashboard/usuario/nomina/index.php">Nóminas</a>
    <a href="/portal-rh/dashboard/usuario/capacitacion/index.php">Capacitaciones</a>
    <?php if (!empty($usuario['moderador'])): ?>
      <a href="/portal-rh/dashboard/mod/index.php">Panel Moderador</a>
    <?php endif; ?>
    <?php if (!empty($usuario['administrador'])): ?>
      <a href="/portal-rh/dashboard/admin/index.php">Panel Administrador</a>
    <?php endif; ?>
    <a href="#" id="modoToggle" onclick="cambiarModo()">Modo Claro</a>
    <a href="/portal-rh/logout.php" style="color: red;">Cerrar sesión</a>
  </nav>

  <!-- Contenido -->
  <main>
    <h2>Panel del Moderador</h2>

    <!-- Iframe de Nóminas -->
    <section style="margin-bottom: 60px;">
      <h3>Subir Comprobante de Nómina</h3>
      <iframe 
        src="https://script.google.com/macros/s/AKfycbzgMbfckcL2SJYJzSO36RLwrL3pha8vC-Ehn27cBDGtZ4lQCfSTh38q0sEra4jAnKnt/exec"
        width="100%" 
        height="510" 
        style="border:none; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.2);">
      </iframe>
    </section>
  </main>

  <footer style="text-align: center; padding: 20px 10px; font-size: 0.9rem; color: inherit;">
  &copy; <span id="anioActual"></span> All Rights Reserved - MEDIOS CON VALOR | Developed by <a href="https://github.com/equis01" style="text-decoration: none; color: inherit;" target="_blank">equisx01</a>
  </footer>

  <script>
    document.addEventListener("DOMContentLoaded", () => {
      aplicarModoGuardado();
      mostrarSaludo("<?= $usuario['nombre']; ?>");
    });
    document.addEventListener("DOMContentLoaded", () => {
    aplicarModoGuardado();

    const spanAnio = document.getElementById("anioActual");
    if (spanAnio) {
      spanAnio.textContent = new Date().getFullYear();
    }
    });
  </script>
</body>
</html>