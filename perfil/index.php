<?php
session_start();

if (!isset($_SESSION['usuario'])) {
  if (isset($_COOKIE['login_token'])) {
    $token = $_COOKIE['login_token'];
    $tokenPath = __DIR__ . "/../tokens/{$token}.json";
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

// Validar acceso exclusivo si no tiene permisos
if (
  empty($usuario['empleado']) &&
  empty($usuario['moderador']) &&
  empty($usuario['administrador'])
) {
  if (strpos($_SERVER['REQUEST_URI'], '/perfil/index.php') === false) {
    header('Location: /portal-rh/perfil/index.php');
    exit();
  }
}

$nombre = $usuario['nombre'];

setlocale(LC_TIME, 'es_ES.UTF-8'); // Asegura que el nombre del mes esté en español

function formatearFecha($fechaISO) {
  $timestamp = strtotime($fechaISO);
  return strftime('%d-%B-%Y', $timestamp);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Perfil - Portal MCV</title>
  <link rel="icon" href="https://mediosconvalor.com/wp-content/uploads/2019/03/cropped-favicon-32x32-1-32x32.png">
  <link rel="stylesheet" href="/portal-rh/styles.css" />
  <script src="/portal-rh/script.js" defer></script>
</head>
<body>
  <header>
    <button class="nav-toggle" onclick="toggleMenu()">☰</button>
    <h1 id="saludoUsuario"></h1>
    <img src="/portal-rh/img/logo.png" alt="Logo MCV" class="logo">
  </header>

  <nav class="menu-mobile" id="menuMobile">
    <?php if (!empty($usuario['empleado']) || !empty($usuario['moderador']) || !empty($usuario['administrador'])): ?>
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
    <?php else: ?>
      <a href="/portal-rh/perfil/index.php">Perfil</a>
    <?php endif; ?>
    <a href="#" id="modoToggle" onclick="cambiarModo()">Modo Claro</a>
    <a href="/portal-rh/logout.php" style="color: red;">Cerrar sesión</a>
  </nav>

  <main>
    <h2>Mi Perfil</h2>

    <?php if (empty($usuario['empleado']) && empty($usuario['moderador']) && empty($usuario['administrador'])): ?>
      <div style="background: #ffd3d3; color: #800; padding: 10px; border-radius: 8px; margin-bottom: 20px;">
        Actualmente no tienes ningún acceso activo al sistema.
      </div>
    <?php endif; ?>

    <div style="padding: 20px; background: rgba(255,255,255,0.05); border-radius: 10px;">
      <p><strong>Nombre:</strong> <?= htmlspecialchars($usuario['nombre']) ?></p>
      <p><strong>RFC:</strong> <?= htmlspecialchars($usuario['rfc']) ?></p>
      <p><strong>Número personal:</strong> <?= htmlspecialchars($usuario['numPersonal']) ?></p>
      <p><strong>CURP:</strong> <?= htmlspecialchars($usuario['curp']) ?></p>
      <p><strong>Contacto de emergencia:</strong> <?= htmlspecialchars($usuario['contactoEmergencia']) ?></p>
      <p><strong>Jefe directo:</strong> <?= htmlspecialchars($usuario['contactoJefe']) ?></p>
      <p><strong>Domicilio:</strong> <?= htmlspecialchars($usuario['domicilio'] ?? 'No disponible') ?></p>
      <p><strong>Correo:</strong> <?= htmlspecialchars($usuario['correo']) ?></p>
      <p><strong>Sucursal:</strong> <?= htmlspecialchars($usuario['sucursal']) ?></p>
      <p><strong>Puesto:</strong> <?= htmlspecialchars($usuario['puesto']) ?></p>
      <p><strong>Fecha de nacimiento:</strong> <?= formatearFecha($usuario['fechaNacimiento']) ?></p>
      <p><strong>Fecha de inicio:</strong> <?= formatearFecha($usuario['fechaInicio']) ?></p>
      <p><strong>Días laborando:</strong> <?= htmlspecialchars($usuario['diasLaborando']) ?> días</p>
    </div>
  </main>

  <footer style="text-align: center; padding: 20px 10px; font-size: 0.9rem; color: inherit;">
    &copy; <span id="anioActual"></span> All Rights Reserved - MEDIOS CON VALOR 
    | Developed by <a href="https://github.com/equis01" style="text-decoration: none; color: inherit;">equisx01</a>
  </footer>

  <script>
    document.addEventListener("DOMContentLoaded", () => {
      aplicarModoGuardado();
      mostrarSaludo("<?= $usuario['nombre']; ?>");
      const spanAnio = document.getElementById("anioActual");
      if (spanAnio) spanAnio.textContent = new Date().getFullYear();
    });
  </script>
</body>
</html>