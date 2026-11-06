/* =====================================================================
   SISTEMA PRIVADO DE GESTION Y CONSULTA DOCUMENTAL
   Archivo: public/assets/js/app.js
   Descripcion: Interacciones del lado del cliente. JavaScript puro,
                sin dependencias externas.

   Modulos:
     1. Menu lateral (movil)
     2. Alertas descartables
     3. Confirmacion de acciones criticas
     4. Mostrar u ocultar contrasena
     5. Generador de contrasena segura
     6. Medidor de fortaleza
     7. Modales
     8. Zona de carga de archivos
     9. Validacion de formularios
    10. Autocompletado de fechas
   ===================================================================== */

(function () {
    'use strict';

    /* -----------------------------------------------------------------
       Utilidades
       ----------------------------------------------------------------- */

    /**
     * Selector multiple abreviado.
     */
    function todos(selector, contexto) {
        return Array.prototype.slice.call(
            (contexto || document).querySelectorAll(selector)
        );
    }

    /**
     * Muestra un mensaje emergente en la esquina de la pantalla.
     */
    function aviso(mensaje, tipo) {
        var contenedor = document.getElementById('avisosFlotantes');

        if (!contenedor) {
            contenedor = document.createElement('div');
            contenedor.id = 'avisosFlotantes';
            contenedor.className = 'avisos-flotantes';
            document.body.appendChild(contenedor);
        }

        var caja = document.createElement('div');
        caja.className = 'aviso-flotante aviso-flotante--' + (tipo || 'info');
        caja.setAttribute('role', 'status');
        caja.textContent = mensaje;

        contenedor.appendChild(caja);

        window.setTimeout(function () {
            caja.classList.add('aviso-flotante--saliendo');
            window.setTimeout(function () {
                if (caja.parentNode) {
                    caja.parentNode.removeChild(caja);
                }
            }, 300);
        }, 4200);
    }

    /* -----------------------------------------------------------------
       1. MENU LATERAL EN PANTALLAS PEQUENAS
       ----------------------------------------------------------------- */
    function iniciarMenuLateral() {
        var boton = document.getElementById('botonMenu');
        var lateral = document.getElementById('menuLateral');

        if (!boton || !lateral) {
            return;
        }

        boton.addEventListener('click', function () {
            lateral.classList.toggle('app__lateral--abierto');
        });

        // Cierra el menu al hacer clic fuera de el
        document.addEventListener('click', function (evento) {
            if (!lateral.classList.contains('app__lateral--abierto')) {
                return;
            }

            if (!lateral.contains(evento.target) && !boton.contains(evento.target)) {
                lateral.classList.remove('app__lateral--abierto');
            }
        });

        // Cierra el menu con la tecla Escape
        document.addEventListener('keydown', function (evento) {
            if (evento.key === 'Escape') {
                lateral.classList.remove('app__lateral--abierto');
            }
        });
    }

    /* -----------------------------------------------------------------
       2. ALERTAS DESCARTABLES
       ----------------------------------------------------------------- */
    function iniciarAlertas() {
        todos('.alerta__cerrar').forEach(function (boton) {
            boton.addEventListener('click', function () {
                var alerta = boton.closest('.alerta');

                if (!alerta) {
                    return;
                }

                alerta.style.transition = 'opacity 180ms ease';
                alerta.style.opacity = '0';

                window.setTimeout(function () {
                    if (alerta.parentNode) {
                        alerta.parentNode.removeChild(alerta);
                    }
                }, 180);
            });
        });

        // Las alertas de exito se ocultan solas
        todos('.alerta--exito').forEach(function (alerta) {
            window.setTimeout(function () {
                alerta.style.transition = 'opacity 400ms ease';
                alerta.style.opacity = '0';

                window.setTimeout(function () {
                    if (alerta.parentNode) {
                        alerta.parentNode.removeChild(alerta);
                    }
                }, 400);
            }, 6500);
        });
    }

    /* -----------------------------------------------------------------
       3. CONFIRMACION DE ACCIONES CRITICAS
       ----------------------------------------------------------------- */
    function iniciarConfirmaciones() {
        todos('[data-confirmar]').forEach(function (elemento) {
            var mensaje = elemento.getAttribute('data-confirmar');

            if (elemento.tagName === 'FORM') {
                elemento.addEventListener('submit', function (evento) {
                    // Si el navegador ya valido y todo esta correcto, se pregunta
                    if (!window.confirm(mensaje)) {
                        evento.preventDefault();
                        evento.stopPropagation();
                    }
                });
            } else {
                elemento.addEventListener('click', function (evento) {
                    if (!window.confirm(mensaje)) {
                        evento.preventDefault();
                        evento.stopPropagation();
                    }
                });
            }
        });
    }

    /* -----------------------------------------------------------------
       4. MOSTRAR U OCULTAR CONTRASENA
       ----------------------------------------------------------------- */
    function iniciarVerClaves() {
        todos('[data-ver-clave]').forEach(function (boton) {
            boton.addEventListener('click', function () {
                var campo = document.getElementById(boton.getAttribute('data-ver-clave'));

                if (!campo) {
                    return;
                }

                var oculta = campo.type === 'password';
                campo.type = oculta ? 'text' : 'password';
                boton.textContent = oculta ? 'Ocultar' : 'Ver';
                boton.setAttribute('aria-label',
                    oculta ? 'Ocultar la contrasena' : 'Mostrar la contrasena');
            });
        });
    }

    /* -----------------------------------------------------------------
       5. GENERADOR DE CONTRASENA SEGURA
       ----------------------------------------------------------------- */
    function generarClaveSegura(largo) {
        var mayusculas = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
        var minusculas = 'abcdefghijkmnopqrstuvwxyz';
        var numeros = '23456789';
        var simbolos = '!@#$%*?+=';
        var todosLosCaracteres = mayusculas + minusculas + numeros + simbolos;

        var clave = [
            mayusculas[Math.floor(Math.random() * mayusculas.length)],
            minusculas[Math.floor(Math.random() * minusculas.length)],
            numeros[Math.floor(Math.random() * numeros.length)],
            simbolos[Math.floor(Math.random() * simbolos.length)]
        ];

        for (var i = clave.length; i < (largo || 14); i++) {
            clave.push(todosLosCaracteres[Math.floor(Math.random() * todosLosCaracteres.length)]);
        }

        // Se mezclan los caracteres para que no queden agrupados por tipo
        for (var j = clave.length - 1; j > 0; j--) {
            var k = Math.floor(Math.random() * (j + 1));
            var temporal = clave[j];
            clave[j] = clave[k];
            clave[k] = temporal;
        }

        return clave.join('');
    }

    function iniciarGeneradorClaves() {
        todos('[data-generar-clave]').forEach(function (boton) {
            boton.addEventListener('click', function () {
                var destino = document.getElementById(boton.getAttribute('data-generar-clave'));

                if (!destino) {
                    return;
                }

                var clave = generarClaveSegura(14);
                destino.type = 'text';
                destino.value = clave;
                destino.dispatchEvent(new Event('input', { bubbles: true }));

                // Se copia al portapapeles si el navegador lo permite
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(clave).then(function () {
                        aviso('Contrasena generada y copiada al portapapeles.', 'exito');
                    }).catch(function () {
                        aviso('Contrasena generada. Copiela antes de guardar.', 'info');
                    });
                } else {
                    aviso('Contrasena generada. Copiela antes de guardar.', 'info');
                }
            });
        });
    }

    /* -----------------------------------------------------------------
       6. MEDIDOR DE FORTALEZA DE CONTRASENA
       ----------------------------------------------------------------- */
    function calcularFortaleza(clave) {
        var puntos = 0;

        if (clave.length >= 8)  { puntos++; }
        if (clave.length >= 12) { puntos++; }
        if (/[a-z]/.test(clave) && /[A-Z]/.test(clave)) { puntos++; }
        if (/[0-9]/.test(clave)) { puntos++; }
        if (/[^A-Za-z0-9]/.test(clave)) { puntos++; }

        // Se reduce a la escala de 1 a 4
        if (puntos <= 2) { return 1; }
        if (puntos === 3) { return 2; }
        if (puntos === 4) { return 3; }
        return 4;
    }

    function iniciarMedidorClaves() {
        todos('[data-medidor]').forEach(function (medidor) {
            var campo = document.getElementById(medidor.getAttribute('data-medidor'));
            var texto = medidor.querySelector('.campo__medidor-texto');

            if (!campo) {
                return;
            }

            var etiquetas = {
                1: 'Contrasena debil',
                2: 'Contrasena aceptable',
                3: 'Contrasena fuerte',
                4: 'Contrasena muy fuerte'
            };

            var evaluar = function () {
                var clave = campo.value;

                if (clave === '') {
                    medidor.removeAttribute('data-nivel');
                    if (texto) {
                        texto.textContent = 'Fortaleza de la contrasena';
                    }
                    return;
                }

                var nivel = calcularFortaleza(clave);
                medidor.setAttribute('data-nivel', String(nivel));

                if (texto) {
                    texto.textContent = etiquetas[nivel];
                }
            };

            campo.addEventListener('input', evaluar);
            evaluar();
        });
    }

    /* -----------------------------------------------------------------
       7. MODALES
       ----------------------------------------------------------------- */
    function iniciarModales() {
        var modales = todos('.modal');

        if (modales.length === 0) {
            return;
        }

        /**
         * Abre un modal y opcionalmente rellena el formulario con los
         * datos recibidos en los atributos data-* del boton.
         */
        function abrirModal(idModal, boton) {
            var modal = document.getElementById(idModal);

            if (!modal) {
                return;
            }

            var modo = boton.getAttribute('data-modo') || 'crear';
            var esEdicion = modo === 'editar';

            // Titulo dinamico
            var titulo = modal.querySelector('.modal__titulo');
            if (titulo) {
                var tituloBase = titulo.getAttribute('data-titulo-base')
                    || titulo.textContent.replace(/^(Nuevo|Nueva|Editar)\s*/i, '');
                titulo.setAttribute('data-titulo-base', tituloBase);
                titulo.textContent = (esEdicion ? 'Editar ' : 'Nuevo ') + tituloBase.toLowerCase();
            }

            // Se asignan los valores de todos los atributos data-* al
            // campo cuyo id termina en el nombre del atributo.
            var campoId = modal.querySelector('[type="hidden"][name="id"]');

            if (esEdicion) {
                if (campoId) {
                    campoId.value = boton.getAttribute('data-id') || '0';
                }

                Array.prototype.slice.call(boton.attributes).forEach(function (atributo) {
                    if (atributo.name.indexOf('data-') !== 0) {
                        return;
                    }

                    var nombre = atributo.name.substring(5);

                    // Campos que no son datos del formulario
                    if (['modo', 'id', 'abrir-modal'].indexOf(nombre) !== -1) {
                        return;
                    }

                    var campo = modal.querySelector('[name="' + nombre + '"]');

                    if (campo) {
                        if (campo.type === 'checkbox') {
                            campo.checked = atributo.value === '1';
                        } else {
                            campo.value = atributo.value;
                        }
                    }
                });
            } else {
                // Modo creacion: se limpia el formulario
                var formulario = modal.querySelector('form');

                if (formulario) {
                    formulario.reset();
                }

                if (campoId) {
                    campoId.value = '0';
                }

                // Foco en el primer campo de texto
                var primerCampo = modal.querySelector(
                    'input[type="text"]:not([type="hidden"]), textarea'
                );

                window.setTimeout(function () {
                    if (primerCampo) {
                        primerCampo.focus();
                    }
                }, 60);
            }

            modal.hidden = false;
            document.body.style.overflow = 'hidden';
        }

        function cerrarModal(modal) {
            modal.hidden = true;
            document.body.style.overflow = '';
        }

        // Botones que abren un modal
        todos('[data-abrir-modal]').forEach(function (boton) {
            boton.addEventListener('click', function () {
                abrirModal(boton.getAttribute('data-abrir-modal'), boton);
            });
        });

        // Elementos que cierran el modal
        todos('[data-cerrar-modal]').forEach(function (elemento) {
            elemento.addEventListener('click', function () {
                var modal = elemento.closest('.modal');

                if (modal) {
                    cerrarModal(modal);
                }
            });
        });

        // Cierre con la tecla Escape
        document.addEventListener('keydown', function (evento) {
            if (evento.key !== 'Escape') {
                return;
            }

            modales.forEach(function (modal) {
                if (!modal.hidden) {
                    cerrarModal(modal);
                }
            });
        });
    }

    /* -----------------------------------------------------------------
       8. ZONA DE CARGA DE ARCHIVOS
       ----------------------------------------------------------------- */
    function formatearPeso(bytes) {
        if (bytes < 1024) {
            return bytes + ' B';
        }
        if (bytes < 1048576) {
            return (bytes / 1024).toFixed(1) + ' KB';
        }
        return (bytes / 1048576).toFixed(2) + ' MB';
    }

    function iniciarZonaArchivo() {
        var zona = document.getElementById('zonaArchivo');

        if (!zona) {
            return;
        }

        var entrada = zona.querySelector('input[type="file"]');
        var seleccion = document.getElementById('archivoSeleccionado');

        if (!entrada) {
            return;
        }

        var pesoMaximo = parseInt(entrada.getAttribute('data-peso-maximo'), 10) || 0;
        var extensionesValidas = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt'];

        /**
         * Valida el archivo elegido antes de enviarlo al servidor.
         * La validacion definitiva se hace igualmente en PHP.
         */
        function validarArchivo(archivo) {
            var extension = archivo.name.split('.').pop().toLowerCase();

            if (extensionesValidas.indexOf(extension) === -1) {
                return {
                    ok: false,
                    error: 'Formato no permitido. Se aceptan: PDF, DOC, DOCX, XLS, XLSX y TXT.'
                };
            }

            if (pesoMaximo > 0 && archivo.size > pesoMaximo) {
                return {
                    ok: false,
                    error: 'El archivo supera el peso maximo permitido ('
                         + formatearPeso(pesoMaximo) + ').'
                };
            }

            if (archivo.size === 0) {
                return { ok: false, error: 'El archivo esta vacio.' };
            }

            return { ok: true };
        }

        function mostrarSeleccion(archivo) {
            if (!seleccion) {
                return;
            }

            var validacion = validarArchivo(archivo);

            if (!validacion.ok) {
                seleccion.innerHTML = '<strong class="texto-rojo">Archivo no valido</strong>'
                    + '<span>' + validacion.error + '</span>';
                zona.classList.add('zona-archivo--error');
                zona.classList.remove('zona-archivo--con-archivo');
                entrada.value = '';
                aviso(validacion.error, 'error');
                return;
            }

            var extension = archivo.name.split('.').pop().toUpperCase();

            seleccion.innerHTML =
                '<strong>' + archivo.name + '</strong>'
                + '<span class="texto-mini">'
                + extension + ' · ' + formatearPeso(archivo.size)
                + ' · Listo para cargar</span>';

            zona.classList.remove('zona-archivo--error');
            zona.classList.add('zona-archivo--con-archivo');
        }

        entrada.addEventListener('change', function () {
            if (entrada.files && entrada.files.length > 0) {
                mostrarSeleccion(entrada.files[0]);
            }
        });

        // Arrastrar y soltar
        ['dragenter', 'dragover'].forEach(function (nombreEvento) {
            zona.addEventListener(nombreEvento, function (evento) {
                evento.preventDefault();
                evento.stopPropagation();
                zona.classList.add('zona-archivo--activa');
            });
        });

        ['dragleave', 'drop'].forEach(function (nombreEvento) {
            zona.addEventListener(nombreEvento, function (evento) {
                evento.preventDefault();
                evento.stopPropagation();
                zona.classList.remove('zona-archivo--activa');
            });
        });

        zona.addEventListener('drop', function (evento) {
            var archivos = evento.dataTransfer && evento.dataTransfer.files;

            if (!archivos || archivos.length === 0) {
                return;
            }

            // Se asigna el archivo al input usando DataTransfer
            try {
                var transferencia = new DataTransfer();
                transferencia.items.add(archivos[0]);
                entrada.files = transferencia.files;
            } catch (error) {
                // Navegadores sin soporte de DataTransfer: se indica al usuario
                aviso('Arrastre no disponible en este navegador. Use el boton de seleccion.', 'info');
                return;
            }

            mostrarSeleccion(archivos[0]);
        });
    }

    /* -----------------------------------------------------------------
       9. VALIDACION DE FORMULARIOS EN EL CLIENTE
       ----------------------------------------------------------------- */
    function iniciarValidacion() {
        todos('form[novalidate]').forEach(function (formulario) {
            formulario.addEventListener('submit', function (evento) {
                var errores = [];

                // Campos obligatorios vacios
                todos('[required]', formulario).forEach(function (campo) {
                    if (campo.disabled || campo.type === 'hidden') {
                        return;
                    }

                    if (campo.type === 'checkbox' && !campo.checked) {
                        errores.push('Debe marcar: ' + etiquetaDe(campo));
                        resaltar(campo, true);
                        return;
                    }

                    if (campo.type !== 'checkbox' && campo.value.trim() === '') {
                        errores.push('El campo "' + etiquetaDe(campo) + '" es obligatorio.');
                        resaltar(campo, true);
                        return;
                    }

                    resaltar(campo, false);
                });

                // Formato de correo
                todos('input[type="email"]', formulario).forEach(function (campo) {
                    if (campo.value.trim() === '') {
                        return;
                    }

                    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(campo.value.trim())) {
                        errores.push('El correo electronico no tiene un formato valido.');
                        resaltar(campo, true);
                    }
                });

                // Coincidencia de contrasenas
                var clave = formulario.querySelector('[name="clave"], [name="clave_nueva"]');
                var confirmacion = formulario.querySelector('[name="clave2"], [name="clave_nueva2"]');

                if (clave && confirmacion && clave.value !== '' && confirmacion.value !== '') {
                    if (clave.value !== confirmacion.value) {
                        errores.push('La confirmacion no coincide con la contrasena.');
                        resaltar(confirmacion, true);
                    }
                }

                // Peso y formato de los archivos
                todos('input[type="file"]', formulario).forEach(function (campo) {
                    if (!campo.files || campo.files.length === 0) {
                        return;
                    }

                    var archivo = campo.files[0];
                    var pesoMaximo = parseInt(campo.getAttribute('data-peso-maximo'), 10) || 0;
                    var extension = archivo.name.split('.').pop().toLowerCase();

                    if (['pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt'].indexOf(extension) === -1) {
                        errores.push('El archivo "' + archivo.name + '" tiene un formato no permitido.');
                        resaltar(campo, true);
                    }

                    if (pesoMaximo > 0 && archivo.size > pesoMaximo) {
                        errores.push('El archivo "' + archivo.name + '" supera el peso maximo de '
                            + formatearPeso(pesoMaximo) + '.');
                        resaltar(campo, true);
                    }
                });

                if (errores.length > 0) {
                    evento.preventDefault();
                    evento.stopPropagation();

                    aviso(errores[0], 'error');

                    // Se lleva el foco al primer campo con problema
                    var primerError = formulario.querySelector('.campo__control--error');

                    if (primerError) {
                        primerError.focus();
                        primerError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }
            });
        });

        /**
         * Devuelve la etiqueta legible de un campo.
         */
        function etiquetaDe(campo) {
            if (campo.id) {
                var etiqueta = document.querySelector('label[for="' + campo.id + '"]');

                if (etiqueta) {
                    return etiqueta.textContent.replace('*', '').trim();
                }
            }

            return campo.name || campo.id || 'campo';
        }

        /**
         * Marca o desmarca visualmente un campo con error.
         */
        function resaltar(campo, conError) {
            if (campo.type === 'file' || campo.type === 'checkbox') {
                return;
            }

            campo.classList.toggle('campo__control--error', conError);
        }
    }

    /* -----------------------------------------------------------------
       10. AUTOCOMPLETADO DE FECHAS
       ----------------------------------------------------------------- */
    function iniciarFechas() {
        // Cuando un caso se marca como cerrado, se sugiere la fecha de hoy
        var selectEstado = document.getElementById('casoEstado');
        var campoCierre = document.getElementById('casoCierre');

        if (selectEstado && campoCierre) {
            selectEstado.addEventListener('change', function () {
                var estado = selectEstado.value;

                if ((estado === 'CERRADO' || estado === 'ARCHIVADO')
                    && campoCierre.value === '') {
                    campoCierre.value = new Date().toISOString().slice(0, 10);
                }
            });
        }

        // Al elegir una fecha "desde", se sugiere en "hasta" si esta vacia
        var desde = document.getElementById('fecha_desde');
        var hasta = document.getElementById('fecha_hasta');

        if (desde && hasta) {
            desde.addEventListener('change', function () {
                if (desde.value !== '' && hasta.value === '') {
                    hasta.value = desde.value;
                }

                hasta.min = desde.value;
            });
        }
    }

    /* -----------------------------------------------------------------
       11. PANEL DE PERMISOS SEGUN CONFIDENCIALIDAD
       ----------------------------------------------------------------- */
    function iniciarPermisosCondicionales() {
        var selector = document.querySelector('[data-confidencialidad]');

        if (!selector) {
            return;
        }

        var panel = document.getElementById('panelPermisos');

        if (!panel) {
            return;
        }

        var actualizar = function () {
            var valor = selector.value;
            var restringido = valor === 'RESERVADO' || valor === 'CONFIDENCIAL';
            panel.classList.toggle('campo--visible', restringido);
        };

        selector.addEventListener('change', actualizar);
        actualizar();
    }

    /* -----------------------------------------------------------------
       12. INICIALIZACION
       ----------------------------------------------------------------- */
    function iniciar() {
        iniciarMenuLateral();
        iniciarAlertas();
        iniciarConfirmaciones();
        iniciarVerClaves();
        iniciarGeneradorClaves();
        iniciarMedidorClaves();
        iniciarModales();
        iniciarZonaArchivo();
        iniciarValidacion();
        iniciarFechas();
        iniciarPermisosCondicionales();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', iniciar);
    } else {
        iniciar();
    }
})();
