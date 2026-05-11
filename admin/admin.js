document.addEventListener('DOMContentLoaded', () => {
    const plantaBtns  = document.querySelectorAll('.planta-btn');
    const planos      = document.querySelectorAll('.plano-svg');
    const modal       = document.getElementById('modal');
    const modalCerrar = document.getElementById('modal-cerrar');
    const modalTitulo = document.getElementById('modal-titulo');
    const modalEstado = document.getElementById('modal-estado');
    const modalInfo   = document.getElementById('modal-info');

    const COLORES = {
        disponible: '#7A9E7E',
        reservado:  '#C0614A',
        ocupado:    '#D32F2F'
    };

    // — Selector de planta —
    plantaBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            plantaBtns.forEach(b => b.classList.remove('activo'));
            btn.classList.add('activo');
            planos.forEach(p => p.style.display = 'none');
            const planoActivo = document.getElementById('plano-' + btn.dataset.planta);
            if (planoActivo) planoActivo.style.display = 'block';
        });
    });

    const btnInicial = document.querySelector('.planta-btn.activo');
    if (btnInicial) btnInicial.click();

    // — Cerrar modal —
    modalCerrar.addEventListener('click', () => modal.classList.remove('activo'));
    modal.addEventListener('click', e => { if (e.target === modal) modal.classList.remove('activo'); });

    // — Clic en habitación —
    document.querySelectorAll('.habitacion-svg').forEach(hab => {
        hab.addEventListener('click', () => pintarModal(hab.dataset.num));
    });

    // — Cargar y pintar colores —
    cargarHabitaciones();

    function cargarHabitaciones() {
        fetch('api/habitaciones.php?accion=listar')
            .then(res => res.json())
            .then(data => {
                if (data && data.success) pintarColores(data.habitaciones);
            })
            .catch(err => console.error('Error cargando habitaciones:', err));
    }

    function pintarColores(habitaciones) {
        const mapa = {};
        habitaciones.forEach(h => { mapa[parseInt(h.numero)] = h.estado; });

        document.querySelectorAll('.habitacion-svg').forEach(rect => {
            const num    = parseInt(rect.dataset.num);
            const estado = mapa[num] || 'disponible';
            rect.dataset.estado = estado;
            rect.setAttribute('fill', COLORES[estado] || COLORES.disponible);
        });
    }

    // — Modal con detalle —
    function pintarModal(numero) {
        modalTitulo.textContent = 'Habitación ' + numero;
        modalEstado.textContent = 'Cargando...';
        modalEstado.className   = 'modal__estado';
        modalInfo.innerHTML     = '';
        modal.classList.add('activo');

        fetch('api/habitaciones.php?accion=detalle&num=' + encodeURIComponent(numero))
            .then(res => res.json())
            .then(data => {
                if (!data || !data.success) {
                    modalInfo.innerHTML = '<p>No se pudo cargar la información.</p>';
                    return;
                }
                const h = data.habitacion;

                modalEstado.textContent = h.estado.charAt(0).toUpperCase() + h.estado.slice(1);
                modalEstado.className   = 'modal__estado modal__estado--' + h.estado;

                let html = '';
                html += fila('Tipo',   h.tipo  || '—');
                html += fila('Precio', h.precio ? h.precio + ' €/mes' : '—');

                if (h.inquilino) {
                    html += separador();
                    html += fila('Nombre',       h.inquilino.nombre + ' ' + h.inquilino.apellidos);
                    html += fila('Nacionalidad', h.inquilino.nacionalidad || '—');
                    html += fila('Documento',    h.inquilino.documento    || '—');
                    html += fila('Email',        '<a href="mailto:' + h.inquilino.email + '">' + h.inquilino.email + '</a>');
                    html += fila('Teléfono',     h.inquilino.telefono     || '—');
                }

                if (h.contrato) {
                    html += separador();
                    html += fila('Desde',        h.contrato.fecha_inicio);
                    html += fila('Hasta',        h.contrato.fecha_fin);
                    html += fila('Tipo contrato', h.contrato.tipo_contrato.replace(/_/g, ' '));
                    html += fila('Precio/mes',   h.contrato.precio_mensual + ' €');
                    html += fila('Fianza',       h.contrato.fianza + ' €');
                }

                modalInfo.innerHTML = html;
            })
            .catch(err => {
                console.error('Error cargando detalle:', err);
                modalInfo.innerHTML = '<p>Error de conexión.</p>';
            });
    }

    function fila(label, valor) {
        return `<div class="modal__info-item">
            <span class="modal__info-label">${label}</span>
            <span class="modal__info-valor">${valor}</span>
        </div>`;
    }

    function separador() {
        return '<hr style="border:none;border-top:1px solid #f2e8d9;margin:4px 0;">';
    }
});