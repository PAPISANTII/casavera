
// ================================================
// CASA VERA — main.js
// ================================================

document.addEventListener("DOMContentLoaded", () => {

  // -----------------------------------------------
  // 1. RELLENAR PRECIOS DINÁMICAMENTE
  // -----------------------------------------------
  document.querySelectorAll("[data-precio]").forEach(elemento => {
    const tipo = elemento.getAttribute("data-precio");
    if (CASAVERA.precios[tipo] !== undefined) {
      elemento.textContent = CASAVERA.precios[tipo] + " €/mes";
    }
  });

  // Fianzas
  const fianzas = CASAVERA.fianzas();
  document.querySelectorAll("[data-fianza]").forEach(elemento => {
    const tipo = elemento.getAttribute("data-fianza");
    if (fianzas[tipo] !== undefined) {
      elemento.textContent = fianzas[tipo] + " €";
    }
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
  const slides = document.querySelectorAll(".carrusel__slide");
  const miniaturas = document.querySelectorAll(".carrusel__miniatura");
  const flechaIzq = document.getElementById("flecha-izq");
  const flechaDer = document.getElementById("flecha-der");

  // Solo ejecutar si hay carrusel en la página
  if (slides.length === 0) return;

  let indiceActual = 0;

  function irASlide(nuevoIndice) {
    // Quitar activo del slide actual
    slides[indiceActual].classList.remove("activo");
    miniaturas[indiceActual].classList.remove("activo");

    // Si el slide actual es un vídeo, pausarlo
    const iframeActual = slides[indiceActual].querySelector("iframe");
    if (iframeActual) {
      // Recargar el src para pausar el vídeo de YouTube
      iframeActual.src = iframeActual.src;
    }

    // Actualizar índice
    indiceActual = nuevoIndice;

    // Activar nuevo slide
    slides[indiceActual].classList.add("activo");
    miniaturas[indiceActual].classList.add("activo");

    // Scroll a miniatura activa si se sale de la vista
    miniaturas[indiceActual].scrollIntoView({
      behavior: "smooth",
      block: "nearest",
      inline: "center"
    });
  }

  function siguiente() {
    const nuevo = (indiceActual + 1) % slides.length;
    irASlide(nuevo);
  }

  function anterior() {
    const nuevo = (indiceActual - 1 + slides.length) % slides.length;
    irASlide(nuevo);
  }

  // Flechas
  if (flechaDer) flechaDer.addEventListener("click", siguiente);
  if (flechaIzq) flechaIzq.addEventListener("click", anterior);

  // Miniaturas
  miniaturas.forEach((miniatura, indice) => {
    miniatura.addEventListener("click", () => {
      irASlide(indice);
    });
  });

  // Navegación con teclado
  document.addEventListener("keydown", (e) => {
    if (e.key === "ArrowRight") siguiente();
    if (e.key === "ArrowLeft") anterior();
  });

});
