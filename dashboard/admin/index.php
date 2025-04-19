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

$logs = [];
$urlLogs = "";
$response = @file_get_contents($urlLogs);
if ($response !== false) {
  $data = json_decode($response, true);
  if ($data && $data["success"]) {
    $logs = $data["registros"];
  }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Administrador - Portal MCV</title>
  <link rel="icon" href="https://mediosconvalor.com/wp-content/uploads/2019/03/cropped-favicon-32x32-1-32x32.png">
  <link rel="stylesheet" href="/portal-rh/styles.css" />
  <script src="/portal-rh/script.js" defer></script>
  <link rel="stylesheet" href="/portal-rh/loader/loader.css">
  <script src="/portal-rh/loader/loader.js" defer></script>
  <style>
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }
    th, td {
      padding: 10px;
      border: 1px solid #666;
      text-align: left;
    }
    th {
      cursor: pointer;
      background-color: #444;
    }
    input[type="search"] {
      padding: 8px;
      width: 100%;
      max-width: 300px;
      margin-top: 20px;
      border-radius: 6px;
      border: none;
    }
  </style>
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
    <h2>Panel del Administrador</h2>
    <p>Bienvenido, <?= $nombre ?>. Aquí podrás consultar logs y gestionar permisos de usuarios.</p>

    <input type="search" id="buscadorLogs" placeholder="Buscar en los logs...">

    <table id="tablaLogs">
      <thead>
        <tr>
          <th onclick="ordenarTabla(0)">Fecha</th>
          <th onclick="ordenarTabla(1)">Correo</th>
          <th onclick="ordenarTabla(2)">Acción</th>
          <th onclick="ordenarTabla(3)">Estado</th>
          <th onclick="ordenarTabla(4)">IP</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($logs as $log): ?>
          <tr>
            <td><?= htmlspecialchars($log["Fecha y Hora"] ?? "") ?></td>
            <td><?= htmlspecialchars($log["Correo"] ?? "") ?></td>
            <td><?= htmlspecialchars($log["Acción"] ?? "") ?></td>
            <td><?= htmlspecialchars($log["Estado"] ?? "") ?></td>
            <td><?= htmlspecialchars($log["IP"] ?? "") ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <h3>Gestión de Usuarios</h3>
    <div style="overflow-x: auto;">
      <table id="tablaUsuarios">
        <thead>
          <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Correo</th>
            <th>RFC</th>
            <th>Empleado</th>
            <th>Moderador</th>
            <th>Administrador</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </main>

  <footer style="text-align: center; padding: 20px 10px; font-size: 0.9rem; color: inherit;">
  &copy; <span id="anioActual"></span> All Rights Reserved - MEDIOS CON VALOR | Developed by <a href="https://github.com/equis01" style="text-decoration: none; color: inherit;" target="_blank">equisx01</a>
  </footer>

  <script>
    document.addEventListener("DOMContentLoaded", () => {
      aplicarModoGuardado();
      mostrarSaludo("<?= $usuario['nombre']; ?>");
      const spanAnio = document.getElementById("anioActual");
      if (spanAnio) spanAnio.textContent = new Date().getFullYear();

      const buscador = document.getElementById("buscadorLogs");
      const tabla = document.getElementById("tablaLogs");
      buscador.addEventListener("keyup", function () {
        const texto = this.value.toLowerCase();
        Array.from(tabla.tBodies[0].rows).forEach(row => {
          row.style.display = Array.from(row.cells).some(cell =>
            cell.textContent.toLowerCase().includes(texto)
          ) ? "" : "none";
        });
      });

      cargarUsuarios();
    });

    function ordenarTabla(indice) {
      const tabla = document.getElementById("tablaLogs");
      const filas = Array.from(tabla.tBodies[0].rows);
      const asc = !tabla.dataset.ordenAsc || tabla.dataset.columna !== indice.toString();

      filas.sort((a, b) => {
        const x = a.cells[indice].textContent.trim().toLowerCase();
        const y = b.cells[indice].textContent.trim().toLowerCase();
        return asc ? x.localeCompare(y) : y.localeCompare(x);
      });

      filas.forEach(f => tabla.tBodies[0].appendChild(f));
      tabla.dataset.ordenAsc = asc;
      tabla.dataset.columna = indice;
    }

    const urlUsuarios = "";

    async function cargarUsuarios() {
      try {
        const res = await fetch(urlUsuarios);
        const json = await res.json();
        if (!json.success) throw new Error("Error al obtener usuarios");

        const usuarios = json.usuarios;
        const tbody = document.querySelector("#tablaUsuarios tbody");
        tbody.innerHTML = "";

        usuarios.forEach(usuario => {
          const tr = document.createElement("tr");

          const checkbox = (campo) => {
            const input = document.createElement("input");
            input.type = "checkbox";
            input.checked = usuario[campo] === true;
            input.dataset.id = usuario["Usuario"];
            input.dataset.campo = campo.toLowerCase();
            input.addEventListener("change", actualizarPermiso);
            return input;
          };

          tr.innerHTML = `
            <td>${usuario["Usuario"]}</td>
            <td>${usuario["Nombre"]}</td>
            <td>${usuario["Correo"]}</td>
            <td>${usuario["RFC"]}</td>
          `;

          ["Empleado", "Moderador", "Administrador"].forEach(campo => {
            const td = document.createElement("td");
            td.appendChild(checkbox(campo));
            tr.appendChild(td);
          });

          tbody.appendChild(tr);
        });
      } catch (err) {
        console.error("Error al cargar usuarios:", err);
      }
    }

    async function actualizarPermiso(e) {
      const id = e.target.dataset.id;
      const fila = e.target.closest("tr");
      const checkboxes = fila.querySelectorAll("input[type=checkbox]");
      const permisos = {
        empleado: checkboxes[0].checked,
        moderador: checkboxes[1].checked,
        administrador: checkboxes[2].checked
      };

      const datos = {
        action: "actualizarPermisos",
        id,
        ...permisos
      };

      try {
        const res = await fetch(urlUsuarios, {
          method: "POST",
          body: JSON.stringify(datos),
          headers: { "Content-Type": "application/json" }
        });
        const respuesta = await res.json();
        if (!respuesta.success) {
          alert("❌ Error al guardar");
          e.target.checked = !e.target.checked;
        }
      } catch (error) {
        console.error("Error:", error);
        alert("❌ Error al conectar con servidor");
        e.target.checked = !e.target.checked;
      }
    }
  </script>
</body>
</html>