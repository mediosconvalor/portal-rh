document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("loginForm");
    const btn = document.getElementById("btnEntrar");
  
    let etapa = 0;
    let intervalo;
  
    function iniciarCargandoAnimado() {
      intervalo = setInterval(() => {
        btn.textContent = ["🌱 Cargando.", "🌿 Cargando..", "🌳 Cargando..."][etapa];
        etapa = (etapa + 1) % 3;
      }, 500);
    }
  
    form.addEventListener("submit", function () {
      btn.disabled = true;
      iniciarCargandoAnimado();
    });
  
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has("error")) {
      clearInterval(intervalo);
      btn.disabled = false;
      btn.textContent = "Entrar";
    }
  });  