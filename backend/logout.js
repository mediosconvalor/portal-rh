// Suponiendo que PHP te imprime el correo así:
  const correo = "<?php echo $_SESSION['usuario']['correo']; ?>";

  function cerrarSesion() {
    // Paso 1: Obtener ubicación
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(
        (pos) => {
          const ubicacion = `Lat: ${pos.coords.latitude}, Lng: ${pos.coords.longitude}`;
          enviarLogout(correo, ubicacion);
        },
        () => {
          enviarLogout(correo, "Ubicación no permitida");
        },
        { timeout: 5000 }
      );
    } else {
      enviarLogout(correo, "Geolocalización no soportada");
    }
  }

  // Paso 2: Enviar a GAS
  function enviarLogout(email, ubicacion) {
    fetch("/portal-rh/backend/logout.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json"
      },
      body: JSON.stringify({ email, ubicacion })
    }).finally(() => {
      // Redirigir aunque falle el GAS
      window.location.href = "/portal-rh/index.php";
    });
  }

  // Ejecutar al cargar el archivo
  cerrarSesion();