window.addEventListener('load', () => {
  const loader = document.querySelector('.loader-container');
  const tree = document.querySelector('.loader-svg');
  const modo = localStorage.getItem("modo");

  const duracion = 1500; // ⏳ Cambia aquí la duración total en ms

  if (loader && tree) {
    loader.style.setProperty('--fondo-loader', modo === "claro" ? '#e0f1f3' : '#2c2f3a');

    setTimeout(() => {
      tree.classList.add('hide'); // animar árbol
      setTimeout(() => {
        loader.style.display = 'none';
      }, 500); // espera a que termine el shrink
    }, duracion);
  }
});