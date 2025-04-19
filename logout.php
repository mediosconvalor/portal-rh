<?php
session_start();

// Si ya viene desde fetch() con ubicación, cerramos sesión
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $input = json_decode(file_get_contents("php://input"), true);
  if (isset($_SESSION['usuario']) && isset($input['email'])) {
    require_once __DIR__ . '/config/urls.php';
    $urls = require __DIR__ . '/config/urls.php';
    $url = $urls['GAS_NOMINA_URL'];

    $correo = $input['email'];
    $ubicacion = $input['ubicacion'] ?? 'No disponible';

    $data = [
      "action" => "logout",
      "email" => $correo,
      "ubicacion" => $ubicacion
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    $response = curl_exec($ch);
    curl_close($ch);

    session_destroy();
    setcookie("login_token", "", time() - 3600, "/");

    http_response_code(200);
    echo "Logout exitoso";
    exit;
  }
}

// Obtener nombre para despedida
$nombreUsuario = $_SESSION['usuario']['nombre'] ?? '';
$nombreCorto = explode(" ", $nombreUsuario)[0];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Cerrando sesión</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" href="https://mediosconvalor.com/wp-content/uploads/2019/03/cropped-favicon-32x32-1-32x32.png">
  <style>
    html, body {
      margin: 0;
      padding: 0;
      font-family: 'Segoe UI', sans-serif;
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      background-color: var(--fondo);
      transition: background-color 0.3s ease;
    }

    #loader {
      text-align: center;
      animation: shrink 2s ease-in-out forwards;
      animation-delay: 3s;
    }

    .tree {
      font-size: 80px;
      line-height: 0.8;
    }

    .mensaje {
      font-size: 20px;
      margin-top: 20px;
      color: var(--texto);
      border-right: 2px solid var(--texto);
      white-space: nowrap;
      overflow: hidden;
      width: 0;
      max-width: 100%;
      animation: escribir 2s steps(30, end) 1s forwards;
    }

    @keyframes escribir {
      from { width: 0 }
      to { width: 20ch }
    }

    @keyframes shrink {
      0% { transform: scale(1); opacity: 1; }
      100% { transform: scale(0.6); opacity: 1; }
    }

    :root[data-tema="claro"] {
      --fondo: #ffffff;
      --texto: #2c2f3a;
    }

    :root[data-tema="oscuro"] {
      --fondo: #2c2f3a;
      --texto: #ffffff;
    }
  </style>
</head>
<body>
  <div id="loader">
    <div class="tree">🌳</div>
    <div class="mensaje">Hasta pronto, <?= htmlspecialchars($nombreCorto) ?> 👋</div>
  </div>

  <script>
    const correo = <?= json_encode($_SESSION['usuario']['correo'] ?? '') ?>;
    const tema = localStorage.getItem("modo") === "claro" ? "claro" : "oscuro";
    document.documentElement.setAttribute("data-tema", tema);

    function cerrarSesion() {
      if (!correo) {
        window.location.href = "/portal-rh/index.php";
        return;
      }

      if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
          (pos) => {
            const ubicacion = `Lat: ${pos.coords.latitude}, Lng: ${pos.coords.longitude}`;
            enviarLogout(correo, ubicacion);
          },
          () => enviarLogout(correo, "Ubicación no permitida"),
          { timeout: 5000 }
        );
      } else {
        enviarLogout(correo, "Geolocalización no soportada");
      }
    }

    function enviarLogout(email, ubicacion) {
      fetch(location.href, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ email, ubicacion })
      }).then(() => {
        setTimeout(() => {
          window.location.href = "/portal-rh/index.php";
        }, 4000);
      });
    }

    cerrarSesion();
  </script>
</body>
</html>