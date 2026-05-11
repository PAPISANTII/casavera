
// ================================================
// CASA VERA — main.js
// ================================================

document.addEventListener("DOMContentLoaded", () => {

    // ================================
  // FORMULARIO DE CONTACTO
  // ================================

  const formulario = document.getElementById('formulario-contacto');

  if (formulario) {
    formulario.addEventListener('submit', async (e) => {
      e.preventDefault();

      const boton = formulario.querySelector('.formulario__boton');
      const textoOriginal = boton.textContent;

      // Limpiar mensajes anteriores
      const mensajeAnterior = formulario.querySelector('.formulario__mensaje');
      if (mensajeAnterior) mensajeAnterior.remove();

      // Estado cargando
      boton.textContent = 'Enviando...';
      boton.disabled = true;

      try {
        const datos = new FormData(formulario);

        const respuesta = await fetch('enviar.php', {
          method: 'POST',
          body: datos
        });

        const resultado = await respuesta.json();
        const mensaje = document.createElement('div');
        mensaje.classList.add('formulario__mensaje');

        if (resultado.ok) {
          mensaje.classList.add('formulario__mensaje--ok');
          mensaje.textContent = '✓ Mensaje enviado correctamente. Te respondemos en menos de 24 horas.';
          formulario.reset();
        } else {
          mensaje.classList.add('formulario__mensaje--error');
          mensaje.textContent = resultado.errores
            ? resultado.errores.join(' ')
            : 'Ha ocurrido un error. Inténtalo de nuevo.';
        }

        formulario.appendChild(mensaje);
        mensaje.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

      } catch (error) {
        const mensaje = document.createElement('div');
        mensaje.classList.add('formulario__mensaje', 'formulario__mensaje--error');
        mensaje.textContent = 'Error de conexión. Comprueba tu internet e inténtalo de nuevo.';
        formulario.appendChild(mensaje);
      }

      boton.textContent = textoOriginal;
      boton.disabled = false;
    });
  }

  // -----------------------------------------------
  // 1. RELLENAR PRECIOS DINÁMICAMENTE
  // -----------------------------------------------
  // Esto — lee de la BD via API
  // Añade ?v= con timestamp para saltarte el caché siempre
  fetch('https://casaveraestudiantes.es/admin/api/precios.php?v=' + Date.now())
      .then(res => res.json())
      .then(data => {
          if (!data || !data.success) return;
          const precios = data.precios;

          document.querySelectorAll('[data-precio]').forEach(elemento => {
              const tipo = elemento.getAttribute('data-precio');
              if (precios[tipo] !== undefined) {
                  elemento.textContent = precios[tipo] + ' €/mes';
              }
          });

          // Fianzas = precio / 2
          document.querySelectorAll('[data-fianza]').forEach(elemento => {
              const tipo = elemento.getAttribute('data-fianza');
              if (precios[tipo] !== undefined) {
                  elemento.textContent = Math.round(precios[tipo] / 2) + ' €';
              }
          });
      })
      .catch(() => {
          // Fallback: si falla la API usa los precios del config.js
          document.querySelectorAll('[data-precio]').forEach(elemento => {
              const tipo = elemento.getAttribute('data-precio');
              if (CASAVERA.precios[tipo] !== undefined) {
                  elemento.textContent = CASAVERA.precios[tipo] + ' €/mes';
              }
          });
      });

  // -----------------------------------------------
  // 2. MENÚ HAMBURGUESA Y DROPDOWN
  // -----------------------------------------------
  const botonMenu = document.getElementById("boton-menu");
  const menuPrincipal = document.getElementById("menu-principal");
  const itemDesplegable = document.querySelector(".menu__desplegable");

  // Abrir/cerrar menú móvil
  if (botonMenu && menuPrincipal) {
    botonMenu.addEventListener("click", () => {
      menuPrincipal.classList.toggle("abierto");
    });

    menuPrincipal.addEventListener("click", (e) => {
      const rect = menuPrincipal.getBoundingClientRect();
      const clickY = e.clientY - rect.top;
      if (clickY < 45) {
        menuPrincipal.classList.remove("abierto");
      }
    });

    menuPrincipal.querySelectorAll("a").forEach(enlace => {
      enlace.addEventListener("click", () => {
        const esPadreDesplegable = enlace.parentElement.classList.contains("menu__desplegable");
        if (!esPadreDesplegable) {
          menuPrincipal.classList.remove("abierto");
        }
      });
    });
  }
  /*
  // Dropdown escritorio con JS
  if (itemDesplegable) {
    itemDesplegable.addEventListener("mouseenter", () => {
      itemDesplegable.classList.add("abierto");
    });

    itemDesplegable.addEventListener("mouseleave", () => {
      itemDesplegable.classList.remove("abierto");
    });

    // Clic en "Habitaciones" en escritorio abre/cierra el dropdown
    // sin navegar, solo si estamos en escritorio
    const enlacePadre = itemDesplegable.querySelector("a");
    enlacePadre.addEventListener("click", (e) => {
      if (window.innerWidth > 768) {
        e.preventDefault();
        itemDesplegable.classList.toggle("abierto");
      }
    });
  }
    */


  // -----------------------------------------------
  // 3. CARRUSEL
  // -----------------------------------------------
  function iniciarCarrusel() {
    const slides = document.querySelectorAll(".carrusel__slide");
    const miniaturas = document.querySelectorAll(".carrusel__miniatura");
    const flechaIzq = document.getElementById("flecha-izq");
    const flechaDer = document.getElementById("flecha-der");

    if (slides.length === 0) return; // ← ahora solo sale de esta función

    let indiceActual = 0;

    function irASlide(nuevoIndice) {
      slides[indiceActual].classList.remove("activo");
      miniaturas[indiceActual].classList.remove("activo");
      const iframeActual = slides[indiceActual].querySelector("iframe");
      if (iframeActual) iframeActual.src = iframeActual.src;
      indiceActual = nuevoIndice;
      slides[indiceActual].classList.add("activo");
      miniaturas[indiceActual].classList.add("activo");
      miniaturas[indiceActual].scrollIntoView({ behavior: "smooth", block: "nearest", inline: "center" });
    }

    function siguiente() { irASlide((indiceActual + 1) % slides.length); }
    function anterior() { irASlide((indiceActual - 1 + slides.length) % slides.length); }

    if (flechaDer) flechaDer.addEventListener("click", siguiente);
    if (flechaIzq) flechaIzq.addEventListener("click", anterior);
    miniaturas.forEach((miniatura, indice) => miniatura.addEventListener("click", () => irASlide(indice)));
    document.addEventListener("keydown", (e) => {
      if (e.key === "ArrowRight") siguiente();
      if (e.key === "ArrowLeft") anterior();
    });
  }

  iniciarCarrusel();

});
