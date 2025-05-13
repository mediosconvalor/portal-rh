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
$correo_creador = $usuario['correo'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Moderador - Portal MCV</title>
  <link rel="icon" href="/portal-rh/img/logo.png">
  <link rel="stylesheet" href="/portal-rh/styles.css" />
  <script src="/portal-rh/script.js" defer></script>
  <script src="/portal-rh/dashboard/mod/script.js" defer></script>
  <link rel="stylesheet" href="/portal-rh/loader/loader.css">
  <script src="/portal-rh/loader/loader.js" defer></script>
</head>
<body>
  <?php include_once($_SERVER['DOCUMENT_ROOT'] . '/portal-rh/loader/loader.php'); ?>

  <header>
    <button class="nav-toggle" onclick="toggleMenu()">☰</button>
    <h1 id="saludoUsuario"></h1>
    <img src="/portal-rh/img/logo.png" alt="Logo MCV" class="logo">
  </header>

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

  <main>
    <h2>Panel del Moderador</h2>

    <section class="subir-nominas" style="margin-bottom: 60px;">
      <h3>Subir Comprobante de Nómina</h3>

      <form id="formularioRegistro">
        <label for="sucursal">Sucursal:</label>
        <select id="sucursal" required></select>

        <label for="nombre">Nombre del colaborador:</label>
        <div class="suggestions">
          <input type="text" id="nombre" autocomplete="off" required />
          <div id="sugerencias" class="suggestions-list"></div>
        </div>

        <label for="noSemana">Número de Semana:</label>
        <select id="noSemana" required></select>

        <label for="imagen">Comprobante de nómina:</label>
        <input type="file" id="imagen" accept="image/*,application/pdf" required />

        <!-- Botón con el id correcto -->
        <button type="submit" id="btnEnviarNominas">Subir nómina</button>

        <!-- Mensajes -->
        <div id="respuestaNominas" class="mensaje"></div>
      </form>
    </section>

    <section class="form-crear-usuario">
      <h3>Crear Usuarios</h3>
      <form id="formUsuario">
        <label>Nombre completo *</label>
        <input name="nombre" required placeholder="Ej: Ana Sofía Martínez Villegas">
        <label>RFC</label>
        <input name="rfc" id="rfc" maxlength="13" placeholder="Ej: VAML071004XYZ">
        <label>CURP</label>
        <input name="curp" id="curp" maxlength="18" placeholder="Ej: VAML071004MDFLNS09">
        <label>Número personal *</label>
        <input name="numero" required placeholder="Ej: 8144944246" maxlength="10">
        <label>Contacto de emergencia *</label>
        <input name="contactoEmergencia" required placeholder="Ej: Tania Orellan / 4498144268">
        <label>Correo del nuevo usuario *</label>
        <input name="correo" required type="email" placeholder="Ej: usuario@dominio.com">
        <label>Sucursal *</label>
        <select name="sucursal" required style="padding: 10px; margin-bottom: 16px; border-radius: 8px;">
          <option value="">Selecciona una sucursal</option>
          <option value="MCV">MCV</option>
          <option value="Querétaro">Querétaro</option>
          <option value="Aguascalientes">Aguascalientes</option>
          <option value="Monterrey">Monterrey</option>
        </select>
        <button type="submit" id="btnEnviar">Solicitar código de verificación</button>
        <div id="respuesta" class="mensaje"></div>
      </form>

      <form id="validacionCodigo" style="display:none; flex-direction: column; gap: 10px; margin-top: 20px;">
        <label>Código de verificación *</label>
        <input id="codigoVerificacion" placeholder="Ej: X9A-1B3" style="text-align:center; font-weight:bold;" required>
        <button type="button" id="btnValidar" onclick="verificarCodigo()">Validar y crear usuario</button>
        <button type="button" id="btnReenviar" onclick="reenviarCodigo()">Reenviar código</button>
      </form>
    </section>
  </main>

  <footer style="text-align: center; padding: 20px 10px; font-size: 0.9rem; color: inherit;">
    &copy; <span id="anioActual"></span> All Rights Reserved - MEDIOS CON VALOR | Developed by <a href="https://github.com/equis01" target="_blank" style="text-decoration:none; color:inherit;">equisx01</a>
  </footer>

  <script>
    let datosTemp = {};
    const scriptURL = "https://script.google.com/macros/s/AKfycbxVdzA8vZDCOe1Jresjf7s9laNfYYYkIOpbtALb10m0Nu_q4CEGtMYM1_gawrli_Naz/exec";

    document.addEventListener("DOMContentLoaded", () => {
      aplicarModoGuardado();
      mostrarSaludo("<?= $usuario['nombre']; ?>");
      const spanAnio = document.getElementById("anioActual");
      if (spanAnio) spanAnio.textContent = new Date().getFullYear();
    });

    const animarBoton = (btn, baseText) => {
      let i = 0;
      const interval = setInterval(() => {
        i = (i + 1) % 4;
        btn.textContent = baseText + ".".repeat(i);
      }, 500);
      return interval;
    };

    const bloquearBoton = (btn, tiempoMs) => {
      btn.disabled = true;
      setTimeout(() => {
        btn.disabled = false;
        btn.textContent = btn.dataset.originalText || "Solicitar código";
      }, tiempoMs);
    };

    document.getElementById("formUsuario").addEventListener("submit", function(e) {
      e.preventDefault();
      const form = new FormData(this);
      form.forEach((value, key) => datosTemp[key] = value);
      datosTemp.creador = "<?= $correo_creador ?>";
      datosTemp.usuario = datosTemp.correo.split("@")[0];

      const btn = document.getElementById("btnEnviar");
      btn.dataset.originalText = btn.textContent;
      const anim = animarBoton(btn, "Enviando");

      fetch(scriptURL, {
        method: "POST",
        body: JSON.stringify({ action: "solicitud_codigo", datos: datosTemp })
      })
      .then(res => res.json())
      .then(data => {
        console.log("Respuesta:", data);
        const msg = document.getElementById("respuesta");
        msg.textContent = data.success ? "✅ Código enviado correctamente." : "❌ " + data.message;
        msg.style.color = data.success ? "green" : "red";
        if (data.success) {
          const validForm = document.getElementById("validacionCodigo");
          const btnEnviar = document.getElementById("btnEnviar");
          validForm.style.display = "flex";
          btnEnviar.style.display = "none";
        }
      })
      .catch(err => console.error(err))
      .finally(() => {
        clearInterval(anim);
        bloquearBoton(btn, 60000);
      });
    });

    function verificarCodigo() {
      const codigo = document.getElementById("codigoVerificacion").value.trim();
      const btn = document.getElementById("btnValidar");
      btn.dataset.originalText = btn.textContent;
      const anim = animarBoton(btn, "Validando");

      fetch(scriptURL, {
        method: "POST",
        body: JSON.stringify({
          action: "verificarCodigo",
          correo: "<?= $correo_creador ?>",
          codigo: codigo,
          datos: datosTemp
        })
      })
      .then(res => res.json())
      .then(data => {
        const msg = document.getElementById("respuesta");
        if (data.success) {
          msg.textContent = "✅ Usuario creado correctamente.";
          msg.style.color = "green";
          document.getElementById("formUsuario").reset();
          document.getElementById("validacionCodigo").style.display = "none";
        } else {
          msg.textContent = "❌ " + data.message;
          msg.style.color = "red";
        }
      })
      .catch(err => console.error(err))
      .finally(() => {
        clearInterval(anim);
        btn.disabled = false;
        btn.textContent = btn.dataset.originalText;
      });
    }

    function reenviarCodigo() {
      const btn = document.getElementById("btnReenviar");
      btn.dataset.originalText = btn.textContent;
      const anim = animarBoton(btn, "Reenviando");

      fetch(scriptURL, {
        method: "POST",
        body: JSON.stringify({ action: "reenviar_codigo", datos: datosTemp })
      })
      .then(res => res.json())
      .then(data => {
        const msg = document.getElementById("respuesta");
        msg.textContent = data.success ? "✅ Código reenviado correctamente." : "❌ " + data.message;
        msg.style.color = data.success ? "green" : "red";
      })
      .catch(err => console.error(err))
      .finally(() => {
        clearInterval(anim);
        bloquearBoton(btn, 60000);
      });
    }
  </script>
</body>
</html>