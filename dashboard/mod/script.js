// /portal-rh/dashboard/mod/script.js
;(function() {
  'use strict';

  console.log('→ script-mod.js cargado');

  // — Funciones internas (no quedan en global) —
  function animarBoton(btn, baseText) {
    let i = 0;
    return setInterval(() => {
      i = (i + 1) % 4;
      btn.textContent = baseText + '.'.repeat(i);
    }, 500);
  }

  function desbloquearBoton(btn) {
    btn.disabled = false;
    btn.textContent = btn.dataset.originalText || 'Subir nómina';
  }

  function getSemanaHoy() {
    const hoy = new Date();
    const primer = new Date(hoy.getFullYear(), 0, 1);
    const diff = hoy - primer;
    const offset = primer.getDay() * 24 * 60 * 60 * 1000;
    return Math.ceil((diff + offset) / (7 * 24 * 60 * 60 * 1000));
  }

  // — Lógica principal —
  document.addEventListener('DOMContentLoaded', () => {
    console.log('DOMContentLoaded — inicializando módulo de nóminas');

    const form   = document.getElementById('formularioRegistro');
    const btn    = document.getElementById('btnEnviarNominas');
    const respEl = document.getElementById('respuestaNominas');
    const URL    = '/portal-rh/dashboard/mod/nominas.php';

    if (!form) {
      console.warn('⚠️ #formularioRegistro no encontrado, abortando módulo de nóminas.');
      return;
    }
    if (!btn || !respEl) {
      console.warn('⚠️ Faltan #btnEnviarNominas o #respuestaNominas');
      return;
    }

    let nombresPorSucursal = {};

    // 1) Fetch inicial para poblar sucursales y semanas
    console.log('Fetch inicial:', URL);
    fetch(URL)
      .then(r => r.json())
      .then(data => {
        console.log('Datos recibidos:', data);
        // sucursales
        const sucSel = document.getElementById('sucursal');
        data.sucursales.forEach(s => {
          const o = document.createElement('option');
          o.value = o.textContent = s;
          sucSel.appendChild(o);
        });
        // semanas
        const semSel = document.getElementById('noSemana');
        const semAct = getSemanaHoy();
        data.semanas.forEach(n => {
          const o = document.createElement('option');
          o.value = n;
          o.textContent = 'Semana ' + n;
          if (n === semAct) o.selected = true;
          semSel.appendChild(o);
        });
        // nombres para autocomplete
        nombresPorSucursal = data.nombresPorSucursal;
      })
      .catch(err => {
        console.error('Error init nóminas:', err);
        respEl.textContent = 'No se pudieron cargar las sucursales.';
        respEl.style.color = 'red';
      });

    // 2) Autocomplete de nombre de colaborador
    const inpNombre = document.getElementById('nombre');
    const sugerBox  = document.getElementById('sugerencias');
    inpNombre.addEventListener('input', () => {
      sugerBox.innerHTML = '';
      const txt = inpNombre.value.toLowerCase();
      const lista = nombresPorSucursal[ document.getElementById('sucursal').value ] || [];
      lista
        .filter(n => n.toLowerCase().includes(txt))
        .forEach(n => {
          const div = document.createElement('div');
          div.textContent = n;
          div.onclick = () => {
            inpNombre.value = n;
            sugerBox.innerHTML = '';
          };
          sugerBox.appendChild(div);
        });
    });
    document.getElementById('sucursal').addEventListener('change', () => {
      inpNombre.value = '';
      sugerBox.innerHTML = '';
    });

    // 3) Envío del formulario con animación
    form.addEventListener('submit', e => {
      e.preventDefault();
      console.log('⏳ Enviando formulario de nóminas');

      const file = document.getElementById('imagen').files[0];
      if (!file) {
        respEl.textContent = 'Selecciona un archivo.';
        respEl.style.color = 'red';
        return;
      }

      // animación
      btn.dataset.originalText = btn.textContent;
      btn.disabled = true;
      const anim = animarBoton(btn, 'Subiendo');

      const reader = new FileReader();
      reader.onloadend = () => {
        const payload = {
          noSemana:     document.getElementById('noSemana').value,
          nombre:       inpNombre.value,
          sucursal:     document.getElementById('sucursal').value,
          imagenBase64: reader.result.split(',')[1],
          tipoImagen:   file.type
        };
        console.log('Payload:', payload);

        fetch(URL, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        })
        .then(r => r.json())
        .then(j => {
          console.log('Respuesta POST:', j);
          clearInterval(anim);
          desbloquearBoton(btn);

          if (j.success) {
            respEl.textContent = `✅ ¡Información enviada! Archivo: ${file.name}`;
            respEl.style.color = 'green';
            form.reset();
          } else {
            respEl.textContent = `❌ ${j.message}`;
            respEl.style.color = 'red';
          }
        })
        .catch(err => {
          console.error('Error POST nóminas:', err);
          clearInterval(anim);
          desbloquearBoton(btn);
          respEl.textContent = '❌ Error al enviar.';
          respEl.style.color = 'red';
        });
      };
      reader.readAsDataURL(file);
    });
  });
})();