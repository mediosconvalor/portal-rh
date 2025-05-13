// === Modo claro/oscuro ===
function cambiarModo() {
  const body = document.body;
  const esClaro = body.classList.toggle('light-mode');
  localStorage.setItem('modo', esClaro ? 'claro' : 'oscuro');
  actualizarTextoModo(esClaro);
}

function aplicarModoGuardado() {
  const modo = localStorage.getItem('modo');
  const esClaro = modo === 'claro';
  if (esClaro) document.body.classList.add('light-mode');
  actualizarTextoModo(esClaro);
}

function actualizarTextoModo(esClaro) {
  const enlace = document.getElementById("modoToggle");
  if (enlace) enlace.textContent = esClaro ? "Modo Oscuro" : "Modo Claro";
}

// === Menú hamburguesa ===
function toggleMenu() {
  const menu = document.getElementById("menuMobile");
  const boton = document.querySelector(".nav-toggle");

  menu.classList.toggle("show"); // <- CAMBIADO DE "open" A "show"
  boton.classList.toggle("rotate");

  boton.textContent = menu.classList.contains("show") ? "✖" : "☰";
}

// === Saludo dinámico ===
function mostrarSaludo(nombreCompleto) {
  const primerNombre = nombreCompleto.split(" ")[0];
  const saludo = document.getElementById("saludoUsuario");
  if (saludo) {
    saludo.innerText = window.innerWidth <= 768
      ? `Hola, ${primerNombre}`
      : `BIENVENIDO, ${nombreCompleto}`;
  }
}

// === Funciones laborales (opcional para otras vistas) ===
function calcularTiempoTrabajado(fechaInicio) {
  const inicio = new Date(fechaInicio);
  const hoy = new Date();
  let years = hoy.getFullYear() - inicio.getFullYear();
  let months = hoy.getMonth() - inicio.getMonth();
  let days = hoy.getDate() - inicio.getDate();

  if (days < 0) {
    months--;
    days += new Date(hoy.getFullYear(), hoy.getMonth(), 0).getDate();
  }
  if (months < 0) {
    years--;
    months += 12;
  }

  return { years, months, days };
}

function calcularDiasParaCumple(fechaNacimiento) {
  const hoy = new Date();
  const cumple = new Date(fechaNacimiento);
  cumple.setFullYear(hoy.getFullYear());
  if (cumple < hoy) cumple.setFullYear(hoy.getFullYear() + 1);
  const diff = Math.ceil((cumple - hoy) / (1000 * 60 * 60 * 24));
  return diff;
}

function mostrarDatosLaboralesYCumple(fechaInicio, fechaNacimiento) {
  const tiempo = calcularTiempoTrabajado(fechaInicio);
  const dias = calcularDiasParaCumple(fechaNacimiento);
  document.getElementById("tiempoTrabajado").textContent =
    `${tiempo.years} años, ${tiempo.months} meses, ${tiempo.days} días`;
  document.getElementById("diasParaCumple").textContent =
    `${dias} días`;
}

document.addEventListener("DOMContentLoaded", () => {
  aplicarModoGuardado();
});