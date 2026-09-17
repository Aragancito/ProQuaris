/**
 * Animaciones globales de ProQuaris.
 * Este archivo se enlaza UNA sola vez desde sidebar.php (que a su vez se
 * incluye en todas las vistas), así que todo lo de aquí aplica a todo el
 * sistema sin tener que tocar cada página por separado.
 *
 * Reglas que sigue este script:
 * - Nunca cambia colores ni estilos que ya existen, solo agrega movimiento.
 * - Todo es "best effort": si un selector no existe en la página actual,
 *   simplemente no hace nada (no lanza errores).
 * - Respeta prefers-reduced-motion.
 */
(function () {
    'use strict';

    const prefiereMenosMovimiento = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    document.addEventListener('DOMContentLoaded', function () {
        crearModalConfirmacion();
        crearContenedorToasts();
        mostrarToastDesdeURL();

        if (prefiereMenosMovimiento) return;

        animarEntradaTarjetasKPI();
        animarContadoresKPI();
        animarFilasDeTabla();
        activarRippleEnBotones();
        resaltarLinkActivoSidebar();
    });

    // --- Si la URL trae ?msg=... (lo agregan los controladores tras una acción),
    // se muestra como toast y se limpia la URL para que no reaparezca al refrescar.
    function mostrarToastDesdeURL() {
        const params = new URLSearchParams(window.location.search);
        const mensaje = params.get('msg');
        if (!mensaje) return;

        const tipo = params.get('tipo') === 'error' ? 'error' : 'exito';
        setTimeout(function () { window.mostrarToast(decodeURIComponent(mensaje), tipo); }, 100);

        params.delete('msg');
        params.delete('tipo');
        const query = params.toString();
        const nuevaURL = window.location.pathname + (query ? '?' + query : '');
        window.history.replaceState({}, '', nuevaURL);
    }

    // --- 0) Modal de confirmación: reemplaza los confirm() nativos ---
    // Cualquier <a> o <button> con el atributo data-confirm="mensaje" usa esto
    // automáticamente, sin tener que escribir JS adicional en cada vista.
    function crearModalConfirmacion() {
        if (document.querySelector('.confirm-overlay')) return; // Ya existe (por si se llama dos veces).

        const overlay = document.createElement('div');
        overlay.className = 'confirm-overlay';
        overlay.innerHTML =
            '<div class="confirm-box">' +
                '<div class="confirm-icono">⚠️</div>' +
                '<div class="confirm-mensaje" id="confirmMensajeTexto"></div>' +
                '<div class="confirm-botones">' +
                    '<button type="button" class="btn-cancel" id="confirmBtnCancelar">Cancelar</button>' +
                    '<button type="button" class="btn-delete" id="confirmBtnAceptar">Sí, continuar</button>' +
                '</div>' +
            '</div>';
        document.body.appendChild(overlay);

        let elementoPendiente = null;

        function cerrarModal() {
            overlay.classList.remove('activo');
            elementoPendiente = null;
        }

        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) cerrarModal();
        });

        document.getElementById('confirmBtnCancelar').addEventListener('click', cerrarModal);

        document.getElementById('confirmBtnAceptar').addEventListener('click', function () {
            const elemento = elementoPendiente;
            cerrarModal();
            if (!elemento) return;

            if (elemento.tagName === 'A') {
                window.location.href = elemento.getAttribute('href');
            } else if (elemento.form) {
                elemento.form.submit();
            } else if (elemento.tagName === 'BUTTON' && elemento.type === 'submit') {
                elemento.closest('form') && elemento.closest('form').submit();
            }
        });

        document.addEventListener('click', function (e) {
            const disparador = e.target.closest('[data-confirm]');
            if (!disparador) return;

            e.preventDefault();
            elementoPendiente = disparador;
            document.getElementById('confirmMensajeTexto').textContent = disparador.getAttribute('data-confirm');
            overlay.classList.add('activo');
        });
    }

    // --- Toasts: aviso flotante reutilizable. Uso desde cualquier vista:
    // mostrarToast('Orden creada con éxito'); o mostrarToast('Algo falló', 'error');
    function crearContenedorToasts() {
        if (document.querySelector('.toast-contenedor')) return;
        const contenedor = document.createElement('div');
        contenedor.className = 'toast-contenedor';
        document.body.appendChild(contenedor);
    }

    window.mostrarToast = function (mensaje, tipo) {
        const contenedor = document.querySelector('.toast-contenedor');
        if (!contenedor) return;

        const toast = document.createElement('div');
        toast.className = 'toast' + (tipo === 'error' ? ' toast-error' : '');
        toast.textContent = mensaje;
        contenedor.appendChild(toast);

        requestAnimationFrame(function () { toast.classList.add('mostrar'); });

        setTimeout(function () {
            toast.classList.remove('mostrar');
            setTimeout(function () { toast.remove(); }, 300);
        }, 3500);
    };

    // --- 1) Las tarjetas KPI entran una tras otra, no todas de golpe ---
    function animarEntradaTarjetasKPI() {
        const tarjetas = document.querySelectorAll('.kpi-card');
        tarjetas.forEach(function (tarjeta, i) {
            tarjeta.style.opacity = '0';
            tarjeta.style.animation = 'fadeInUp 0.45s ease forwards';
            tarjeta.style.animationDelay = (i * 0.08) + 's';
        });
    }

    // --- 2) Los números de las tarjetas KPI cuentan desde 0 hasta su valor ---
    function animarContadoresKPI() {
        const valores = document.querySelectorAll('.kpi-value');

        valores.forEach(function (elemento) {
            const textoOriginal = elemento.textContent.trim();
            const coincidencia = textoOriginal.match(/-?[\d.,]+/);
            if (!coincidencia) return; // No hay número reconocible, se deja como está.

            const numeroTexto = coincidencia[0];
            // Formato es-CO: '.' separa miles, ',' separa decimales.
            const numeroLimpio = numeroTexto.replace(/\./g, '').replace(',', '.');
            const valorFinal = parseFloat(numeroLimpio);
            if (isNaN(valorFinal)) return;

            const decimales = numeroTexto.includes(',') ? numeroTexto.split(',')[1].length : 0;
            const prefijo = textoOriginal.slice(0, coincidencia.index);
            const sufijo = textoOriginal.slice(coincidencia.index + numeroTexto.length);

            const duracionMs = 700;
            const inicioTiempo = performance.now();

            function paso(tiempoActual) {
                const progreso = Math.min((tiempoActual - inicioTiempo) / duracionMs, 1);
                // easeOutQuad: arranca rápido y frena suave al llegar.
                const progresoSuavizado = 1 - (1 - progreso) * (1 - progreso);
                const valorActual = valorFinal * progresoSuavizado;

                const valorFormateado = valorActual.toLocaleString('es-CO', {
                    minimumFractionDigits: decimales,
                    maximumFractionDigits: decimales
                });

                elemento.textContent = prefijo + valorFormateado + sufijo;

                if (progreso < 1) {
                    requestAnimationFrame(paso);
                } else {
                    elemento.textContent = textoOriginal; // Deja el texto exacto original al terminar.
                }
            }

            requestAnimationFrame(paso);
        });
    }

    // --- 3) Las filas de las tablas entran escalonadas, no todas a la vez ---
    function animarFilasDeTabla() {
        const filas = document.querySelectorAll('.table-container table tbody tr');
        filas.forEach(function (fila, i) {
            fila.style.setProperty('--fila', i);
        });
    }

    // --- 4) Efecto "ripple" (onda) al hacer click en botones/acciones ---
    function activarRippleEnBotones() {
        const selector = '.btn-primary, .btn-edit, .btn-delete, .btn-cancel, .btn-action';

        document.addEventListener('click', function (evento) {
            const boton = evento.target.closest(selector);
            if (!boton) return;

            const rect = boton.getBoundingClientRect();
            const tamano = Math.max(rect.width, rect.height);
            const onda = document.createElement('span');

            onda.className = 'ripple-efecto';
            onda.style.width = onda.style.height = tamano + 'px';
            onda.style.left = (evento.clientX - rect.left - tamano / 2) + 'px';
            onda.style.top = (evento.clientY - rect.top - tamano / 2) + 'px';

            boton.appendChild(onda);
            setTimeout(function () { onda.remove(); }, 600);
        });
    }

    // --- 5) Resalta en el sidebar el link de la página en la que estás ---
    function resaltarLinkActivoSidebar() {
        const rutaActual = window.location.pathname + window.location.search;
        const links = document.querySelectorAll('.nav-menu .nav-item');

        links.forEach(function (link) {
            const href = link.getAttribute('href') || '';
            const rutaLink = href.split('?')[0];
            const accionLink = (href.split('accion=')[1] || '').split('&')[0];
            const accionActual = (rutaActual.split('accion=')[1] || '').split('&')[0];

            const mismoControlador = rutaActual.indexOf(rutaLink) !== -1 || window.location.pathname.indexOf(rutaLink) !== -1;
            const mismaAccion = accionLink && accionActual && accionLink === accionActual;

            if (mismoControlador && (mismaAccion || !accionLink)) {
                link.style.backgroundColor = 'rgba(99, 102, 241, 0.12)';
                link.style.borderLeft = '3px solid var(--color-principal)';
                link.style.paddingLeft = '17px';
            }
        });
    }
})();