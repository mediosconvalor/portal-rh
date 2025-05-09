<?php
session_start();
$usuario = $_SESSION['usuario'] ?? null;
$nombre = $usuario['nombre'] ?? 'Invitado';
$primerNombre = explode(" ", $nombre)[0];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Error 404</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="icon" href="https://mediosconvalor.com/wp-content/uploads/2019/03/cropped-favicon-32x32-1-32x32.png" />
  <link rel="stylesheet" href="/portal-rh/styles.css" />
  <script src="/portal-rh/script.js" defer></script>
</head>
<body onload="verificarAccesoGeneral()">
  <header class="encabezado">
    <img src="/portal-rh/img/logo.png" alt="Logo MCV" class="logo" />
    <div class="saludo" id="saludo">
      Hola, <?= htmlspecialchars($primerNombre) ?>
    </div>
    <button class="menu-btn" id="menuBtn" onclick="toggleMenu()">☰</button>
  </header>

  <nav id="menuMobile" class="menu-mobile oculto">
    <a href="/portal-rh/dashboard/usuario/index.html">Inicio</a>
    <a href="/portal-rh/perfil/index.php">Perfil</a>
    <a href="/portal-rh/index.php" onclick="cerrarSesion()">Cerrar sesión</a>
  </nav>

  <main class="contenedor centrado">
    <h1 class="error404">Error 404</h1>
    <p class="mensaje-error">La página que buscas no fue encontrada 🧐</p>
    <a href="/portal-rh/dashboard/usuario/index.html" class="btn-volver">Volver al inicio</a>
  </main>

  <style>
    .centrado {
      text-align: center;
      padding: 60px 20px;
    }
    .error404 {
      font-size: 48px;
      margin-bottom: 10px;
      color: var(--textoPrincipal, #2c2f3a);
    }
    .mensaje-error {
      font-size: 20px;
      margin-bottom: 30px;
      color: var(--textoPrincipal, #444);
    }
    .btn-volver {
      display: inline-block;
      padding: 10px 20px;
      background-color: var(--verde, #00dc2a);
      color: white;
      text-decoration: none;
      border-radius: 8px;
      font-weight: bold;
    }
  </style>
</body>
</html>