# -*- coding: utf-8 -*-
"""Parte 2 del contenido: secciones 6 a 13."""


def agregar(C):
    # -----------------------------------------------------------------
    # 6. ACTORES
    # -----------------------------------------------------------------
    C(('titulo', 1, '6. Actores del sistema'))
    C(('tabla', ['Actor', 'Descripcion', 'Funciones principales', 'Nivel'],
       [['Administrador', 'Responsable del control general del sistema.',
         'Gestionar usuarios, roles, permisos, areas, documentos, auditoria, '
         'respaldos y configuracion.', '3'],
        ['Responsable documental', 'Encargado de administrar el contenido documental.',
         'Cargar, clasificar, actualizar, versionar, archivar y retirar documentos. '
         'Administrar categorias y casos.', '2'],
        ['Asesor', 'Usuario operativo que consulta informacion.',
         'Buscar documentos autorizados, revisar el contenido y consultar su propio '
         'historial de busquedas.', '1'],
        ['Sistema', 'Componente automatizado.',
         'Validar permisos, extraer texto de los archivos, calcular hashes de '
         'integridad, registrar la auditoria y ejecutar las tareas programadas.', '-']],
       [3.0, 3.8, 7.4, 1.4]))
    C(('aviso', 'Actor retirado',
       'El actor "Sistema de IA" de la version anterior queda eliminado. Sus '
       'funciones de recuperacion de informacion son reemplazadas por consultas '
       'SQL deterministas sobre los indices de texto de la base de datos.',
       'FBE0E0'))

    # -----------------------------------------------------------------
    # 7. REQUERIMIENTOS FUNCIONALES
    # -----------------------------------------------------------------
    C(('titulo', 1, '7. Requerimientos funcionales'))
    C(('parrafo',
       'A continuacion se listan los requerimientos funcionales del sistema. La '
       'columna "Prioridad" indica la criticidad de cada uno.'))

    C(('titulo', 2, '7.1. Autenticacion y usuarios'))
    C(('tabla', ['ID', 'Requerimiento', 'Prioridad'],
       [['RF-001', 'El sistema debera permitir el inicio y el cierre de sesion con '
                   'credenciales validas.', 'Alta'],
        ['RF-002', 'El administrador debera crear, editar, desactivar, bloquear y '
                   'consultar usuarios.', 'Alta'],
        ['RF-003', 'El sistema debera aplicar permisos segun el rol y el area del '
                   'usuario.', 'Alta'],
        ['RF-004', 'El sistema debera bloquear la cuenta tras cinco intentos '
                   'fallidos de inicio de sesion.', 'Alta'],
        ['RF-005', 'El sistema debera cerrar la sesion tras el periodo de '
                   'inactividad configurado.', 'Media'],
        ['RF-006', 'El usuario debera poder cambiar su propia contrasena validando '
                   'la anterior.', 'Alta'],
        ['RF-007', 'El administrador debera poder restablecer la contrasena de un '
                   'usuario, dejandola como temporal.', 'Media'],
        ['RF-008', 'El sistema debera permitir el registro publico de cuentas con '
                   'rol Asesor unicamente.', 'Media']],
       [1.8, 12.4, 2.0]))

    C(('titulo', 2, '7.2. Gestion documental'))
    C(('tabla', ['ID', 'Requerimiento', 'Prioridad'],
       [['RF-009', 'Los usuarios autorizados podran cargar documentos en los '
                   'formatos definidos.', 'Alta'],
        ['RF-010', 'El sistema debera validar el peso, la extension y el tipo MIME '
                   'real del archivo antes de almacenarlo.', 'Alta'],
        ['RF-011', 'El sistema debera calcular un hash SHA-256 de cada archivo y '
                   'rechazar los duplicados exactos.', 'Alta'],
        ['RF-012', 'Cada documento debera tener titulo, codigo, categoria, fecha, '
                   'area y responsable antes de estar disponible.', 'Alta'],
        ['RF-013', 'El sistema debera extraer el texto de los archivos PDF, DOCX, '
                   'XLSX y TXT para ampliar la busqueda.', 'Alta'],
        ['RF-014', 'Los usuarios autorizados podran editar los metadatos de un '
                   'documento.', 'Alta'],
        ['RF-015', 'Los usuarios autorizados podran cargar nuevas versiones '
                   'conservando las anteriores.', 'Alta'],
        ['RF-016', 'El sistema debera permitir archivar y marcar como eliminado un '
                   'documento, conservando la trazabilidad.', 'Alta'],
        ['RF-017', 'Solo el administrador podra eliminar definitivamente un '
                   'documento, previa confirmacion por codigo.', 'Media'],
        ['RF-018', 'El sistema debera verificar la integridad del archivo mediante '
                   'su hash antes de cada descarga.', 'Alta'],
        ['RF-019', 'El sistema debera permitir la descarga de cualquier version '
                   'anterior del documento.', 'Media'],
        ['RF-020', 'El sistema debera soportar al menos los formatos PDF, DOCX, DOC, '
                   'XLSX, XLS y TXT.', 'Alta'],
        ['RF-021', 'El sistema debera permitir asociar etiquetas libres a los '
                   'documentos.', 'Media']],
       [1.8, 12.4, 2.0]))

    C(('titulo', 2, '7.3. Busqueda y consulta documental'))
    C(('tabla', ['ID', 'Requerimiento', 'Prioridad'],
       [['RF-022', 'El usuario debera poder buscar documentos por palabras clave.', 'Alta'],
        ['RF-023', 'La busqueda debera abarcar titulo, codigo, descripcion, palabras '
                   'clave y el texto extraido del archivo.', 'Alta'],
        ['RF-024', 'El sistema debera calcular y mostrar un puntaje de coincidencia '
                   'para ordenar los resultados.', 'Media'],
        ['RF-025', 'El sistema debera mostrar el fragmento exacto donde aparece el '
                   'termino buscado, resaltado.', 'Alta'],
        ['RF-026', 'El usuario debera poder filtrar por categoria, area, caso, '
                   'estado, confidencialidad, etiqueta y rango de fechas.', 'Media'],
        ['RF-027', 'El sistema debera indicar claramente cuando no encuentre '
                   'informacion suficiente, ofreciendo sugerencias.', 'Alta'],
        ['RF-028', 'El sistema debera registrar cada consulta realizada con su '
                   'tiempo de respuesta y resultados.', 'Media'],
        ['RF-029', 'El usuario debera poder consultar el detalle y las fuentes de '
                   'sus consultas anteriores.', 'Media'],
        ['RF-030', 'El sistema debera impedir que la busqueda exponga documentos '
                   'fuera del alcance del usuario.', 'Alta']],
       [1.8, 12.4, 2.0]))

    C(('titulo', 2, '7.4. Clasificacion y catalogos'))
    C(('tabla', ['ID', 'Requerimiento', 'Prioridad'],
       [['RF-031', 'El sistema debera permitir administrar las categorias '
                   'documentales.', 'Alta'],
        ['RF-032', 'El sistema debera permitir administrar los casos o expedientes '
                   'y asociarlos a las areas.', 'Media'],
        ['RF-033', 'El administrador debera poder administrar las areas de la '
                   'organizacion.', 'Alta'],
        ['RF-034', 'Una categoria o caso con documentos asociados no podra '
                   'eliminarse, solo desactivarse o archivarse.', 'Alta'],
        ['RF-035', 'El sistema debera clasificar cada documento con un nivel de '
                   'confidencialidad: publico, interno, reservado o confidencial.', 'Alta'],
        ['RF-036', 'Para los documentos reservados, el sistema debera permitir '
                   'otorgar permisos a usuarios o areas concretas.', 'Alta']],
       [1.8, 12.4, 2.0]))

    C(('titulo', 2, '7.5. Auditoria, respaldo y reportes'))
    C(('tabla', ['ID', 'Requerimiento', 'Prioridad'],
       [['RF-037', 'El sistema debera registrar en una bitacora los accesos, cargas, '
                   'modificaciones, descargas y eliminaciones.', 'Alta'],
        ['RF-038', 'La bitacora debera conservar el estado anterior y posterior de '
                   'cada cambio.', 'Media'],
        ['RF-039', 'El administrador debera poder filtrar y exportar la bitacora a '
                   'formato CSV.', 'Media'],
        ['RF-040', 'El administrador debera poder aplicar politicas de retencion y '
                   'depurar registros antiguos.', 'Media'],
        ['RF-041', 'El administrador debera poder generar copias de seguridad '
                   'completas o solo de datos.', 'Alta'],
        ['RF-042', 'El sistema debera permitir descargar y eliminar los archivos de '
                   'respaldo.', 'Alta'],
        ['RF-043', 'El sistema debera presentar indicadores de uso, documentos y '
                   'consultas en el panel principal.', 'Media'],
        ['RF-044', 'El sistema debera exportar el inventario documental, el '
                   'historial de consultas y el listado de usuarios a CSV.', 'Media'],
        ['RF-045', 'El sistema debera generar una version imprimible del reporte de '
                   'indicadores.', 'Baja'],
        ['RF-046', 'El sistema debera notificar al usuario sobre el estado de '
                   'procesamiento de sus cargas.', 'Baja'],
        ['RF-047', 'El administrador debera poder modificar los parametros generales '
                   'del sistema.', 'Media'],
        ['RF-048', 'El sistema debera enviar a la papelera logica los documentos '
                   'eliminados, conservando sus archivos.', 'Media']],
       [1.8, 12.4, 2.0]))

    C(('aviso', 'Requerimientos retirados de la version 1.0',
       'Se eliminan los requerimientos por depender de inteligencia artificial: '
       '"Chat documental", "Recuperacion contextual", "Respuestas fundamentadas", '
       '"Fuentes de respuesta generada", "OCR" y "Evaluacion IA". El requerimiento '
       'de "Fuentes" se reformula como RF-025: mostrar el fragmento donde aparece el '
       'termino buscado dentro del documento original. El requerimiento '
       '"Multi-formato" se conserva como RF-020 pero sin OCR.', 'FBE0E0'))

    # -----------------------------------------------------------------
    # 8. REQUERIMIENTOS NO FUNCIONALES
    # -----------------------------------------------------------------
    C(('titulo', 1, '8. Requerimientos no funcionales'))
    C(('tabla', ['ID', 'Categoria', 'Requerimiento'],
       [['RNF-001', 'Seguridad', 'Las contrasenas se almacenan con hash bcrypt. '
                                  'Ninguna credencial se guarda en texto plano.'],
        ['RNF-002', 'Seguridad', 'Todas las consultas a la base de datos usan '
                                  'sentencias preparadas para prevenir inyeccion SQL.'],
        ['RNF-003', 'Seguridad', 'Todo texto mostrado al usuario se escapa para '
                                  'prevenir XSS.'],
        ['RNF-004', 'Seguridad', 'Los formularios POST incluyen y validan un token '
                                  'CSRF por sesion.'],
        ['RNF-005', 'Confidencialidad', 'Los documentos no se exponen a usuarios sin '
                                        'permisos sobre su area o nivel de '
                                        'confidencialidad.'],
        ['RNF-006', 'Integridad', 'Cada archivo se verifica mediante hash SHA-256 '
                                  'antes de su descarga.'],
        ['RNF-007', 'Integridad', 'La validacion del tipo de archivo se realiza '
                                  'sobre el contenido real, no sobre la extension '
                                  'declarada.'],
        ['RNF-008', 'Rendimiento', 'Las busquedas deberan resolverse en menos de 3 '
                                   'segundos para el 90 % de las consultas sobre un '
                                   'volumen de 10 000 documentos.'],
        ['RNF-009', 'Disponibilidad', 'Disponibilidad minima del 99 % en el horario '
                                      'operativo definido.'],
        ['RNF-010', 'Capacidad', 'Soporte inicial para 50 usuarios concurrentes y '
                                 '10 000 documentos.'],
        ['RNF-011', 'Escalabilidad', 'La arquitectura debera permitir incorporar '
                                     'nuevos modulos sin reescribir el nucleo.'],
        ['RNF-012', 'Usabilidad', 'La interfaz debera ser comprensible para usuarios '
                                  'con conocimientos basicos de ofimatica.'],
        ['RNF-013', 'Trazabilidad', 'Toda operacion relevante quedara registrada con '
                                    'usuario, fecha, direccion IP y resultado.'],
        ['RNF-014', 'Mantenibilidad', 'El codigo estara modularizado siguiendo el '
                                      'patron MVC y documentado.'],
        ['RNF-015', 'Mantenibilidad', 'Las hojas de estilo estaran separadas por '
                                      'responsabilidad y cargadas en orden de '
                                      'cascada definido.'],
        ['RNF-016', 'Respaldo', 'Deberan existir mecanismos de copia y recuperacion. '
                                'RPO maximo 24 h y RTO maximo 4 h.'],
        ['RNF-017', 'Idioma', 'La interfaz y todos los mensajes estaran en espanol.'],
        ['RNF-018', 'Accesibilidad', 'Debera cumplir criterios basicos WCAG 2.1 AA: '
                                     'contraste suficiente, foco visible y navegacion '
                                     'por teclado.'],
        ['RNF-019', 'Compatibilidad', 'La aplicacion debera funcionar en las '
                                      'versiones actuales de Chrome, Edge y Firefox.'],
        ['RNF-020', 'Auditoria', 'Los registros de auditoria se conservaran minimo '
                                 '12 meses.'],
        ['RNF-021', 'Privacidad', 'La solucion respetara las politicas internas y la '
                                  'normativa aplicable sobre proteccion de datos.'],
        ['RNF-022', 'Portabilidad', 'La aplicacion debera operar en cualquier '
                                    'servidor con PHP 8 y MySQL 8 sin dependencias '
                                    'externas.']],
       [2.0, 2.6, 11.6]))
    C(('aviso', 'Requerimientos no funcionales retirados',
       'Se eliminan "Confiabilidad de IA" (indicar cuando no existe evidencia '
       'suficiente generada por un modelo) y "Cifrado" en su forma original '
       'asociada a los embeddings. La indicacion de ausencia de informacion se '
       'conserva, pero como requerimiento funcional RF-027, aplicado al buscador.',
       'FBE0E0'))

    # -----------------------------------------------------------------
    # 9. REGLAS DE NEGOCIO
    # -----------------------------------------------------------------
    C(('titulo', 1, '9. Reglas de negocio'))
    for regla in [
        'Solo los usuarios autenticados acceden al sistema.',
        'Los permisos determinan que documentos puede consultar cada usuario.',
        'El nivel de acceso del rol y el area del usuario determinan el alcance de la consulta.',
        'Solo los usuarios con permiso de creacion cargan documentos.',
        'Todo documento debe tener identificacion y metadatos minimos antes de estar disponible.',
        'Un documento reservado o confidencial solo es accesible si existe un permiso explicito.',
        'Si la informacion buscada no esta presente, el sistema debe comunicarlo claramente.',
        'El sistema no interpreta, resume ni genera contenido: solo devuelve coincidencias.',
        'Las acciones criticas deben quedar registradas en la auditoria.',
        'Una categoria o caso con documentos asociados no se elimina, se desactiva.',
        'La eliminacion definitiva esta restringida al administrador y exige confirmacion por codigo.',
        'Los archivos no se eliminan del servidor mientras exista una version registrada, salvo en la eliminacion definitiva.',
        'Un archivo con el mismo hash de otro ya registrado se rechaza por duplicado.',
        'Las contrasenas deben cumplir la politica de complejidad definida.',
        'Los registros de auditoria no pueden ser modificados ni eliminados por usuarios no administradores.',
    ]:
        C(('numerada', regla))

    # -----------------------------------------------------------------
    # 10. CASOS DE USO
    # -----------------------------------------------------------------
    C(('titulo', 1, '10. Casos de uso principales'))
    C(('tabla', ['ID', 'Caso de uso', 'Actor principal', 'Requerimientos'],
       [['CU-01', 'Iniciar sesion', 'Todos', 'RF-001, RF-004, RF-005'],
        ['CU-02', 'Cerrar sesion', 'Todos', 'RF-001'],
        ['CU-03', 'Gestionar usuarios y roles', 'Administrador', 'RF-002, RF-003'],
        ['CU-04', 'Gestionar areas', 'Administrador', 'RF-033'],
        ['CU-05', 'Cargar documento', 'Responsable documental', 'RF-009 a RF-013'],
        ['CU-06', 'Clasificar documento', 'Responsable documental',
         'RF-012, RF-035, RF-036'],
        ['CU-07', 'Cargar nueva version', 'Responsable documental', 'RF-015, RF-019'],
        ['CU-08', 'Editar metadatos', 'Responsable documental', 'RF-014'],
        ['CU-09', 'Archivar o eliminar documento', 'Administrador',
         'RF-016, RF-017, RF-048'],
        ['CU-10', 'Buscar documentos', 'Asesor, Responsable', 'RF-022 a RF-026'],
        ['CU-11', 'Revisar el fragmento encontrado', 'Asesor', 'RF-025'],
        ['CU-12', 'Descargar documento', 'Segun permisos', 'RF-018, RF-019'],
        ['CU-13', 'Consultar historial propio', 'Asesor', 'RF-028, RF-029'],
        ['CU-14', 'Revisar auditoria', 'Administrador', 'RF-037 a RF-040'],
        ['CU-15', 'Generar respaldo', 'Administrador', 'RF-041, RF-042'],
        ['CU-16', 'Consultar indicadores', 'Administrador', 'RF-043'],
        ['CU-17', 'Exportar reportes', 'Administrador', 'RF-044, RF-045'],
        ['CU-18', 'Configurar el sistema', 'Administrador', 'RF-047'],
        ['CU-19', 'Validar permisos', 'Sistema', 'RF-003, RF-030, RNF-005'],
        ['CU-20', 'Registrar auditoria', 'Sistema', 'RF-037, RNF-013']],
       [1.6, 5.4, 4.0, 5.2]))
    C(('aviso', 'Casos de uso retirados',
       'Se eliminan los casos "Recuperar informacion relevante" y "Generar '
       'respuesta fundamentada", ambos asignados al Sistema de IA en la version '
       'anterior. La recuperacion de informacion ahora es una operacion de consulta '
       'SQL, cubierta por CU-10.', 'FBE0E0'))
    C(('salto',))

    # -----------------------------------------------------------------
    # 11. ARQUITECTURA
    # -----------------------------------------------------------------
    C(('titulo', 1, '11. Arquitectura conceptual'))
    C(('parrafo',
       'La aplicacion sigue el patron Modelo-Vista-Controlador (MVC) sobre una '
       'arquitectura web clasica de tres capas. No existen componentes de '
       'inteligencia artificial ni servicios externos de inferencia.'))

    C(('titulo', 2, '11.1. Capas del sistema'))
    C(('tabla', ['Capa', 'Responsabilidad', 'Componentes'],
       [['Presentacion',
         'Interfaz con el usuario. Estructura HTML, presentacion CSS e '
         'interacciones en JavaScript.',
         'Vistas PHP, 7 hojas de estilo modulares, app.js'],
        ['Control',
         'Recibe la peticion, valida los datos, aplica las reglas de negocio y '
         'selecciona la vista.',
         '7 controladores, enrutador index.php'],
        ['Modelo', 'Acceso a datos y representacion de las entidades del dominio.',
         '6 modelos, capa PDO'],
        ['Nucleo', 'Servicios transversales reutilizables por todos los modulos.',
         'Database, Sesion, Helper, Validador, Auditoria, GestorArchivos'],
        ['Persistencia', 'Almacenamiento estructurado de la informacion.',
         'MySQL 8.0 con InnoDB y utf8mb4'],
        ['Almacenamiento',
         'Conservacion de los archivos originales en el sistema de archivos.',
         'public/uploads organizado por ano y mes']],
       [2.8, 7.0, 6.4]))

    C(('titulo', 2, '11.2. Flujo de una peticion'))
    C(('parrafo',
       'Toda peticion sigue la misma secuencia, garantizando que los controles de '
       'seguridad se apliquen de forma uniforme:'))
    for paso in [
        'El navegador solicita index.php indicando modulo y accion.',
        'El enrutador carga la configuracion, el nucleo y los modelos.',
        'Se inicia o recupera la sesion y se verifica la autenticacion.',
        'Se comprueba el permiso del rol para el modulo solicitado.',
        'Si la peticion es POST, se valida el token CSRF.',
        'El controlador valida los datos de entrada con la clase Validador.',
        'El modelo ejecuta las consultas SQL mediante sentencias preparadas.',
        'La accion se registra en la bitacora de auditoria.',
        'Se carga la vista correspondiente con los datos obtenidos.',
        'El resultado se devuelve al navegador.',
    ]:
        C(('numerada', paso))

    C(('titulo', 2, '11.3. Componentes retirados'))
    C(('tabla', ['Componente de la version 1.0', 'Estado en la version 2.0'],
       [['Capa RAG (recuperacion y generacion)', 'Eliminada por completo'],
        ['Base de datos vectorial (pgvector, Qdrant)',
         'Eliminada; se usan indices FULLTEXT de MySQL'],
        ['Modelo de lenguaje (LLM)', 'Eliminado'],
        ['Generacion de embeddings', 'Eliminada'],
        ['Framework de orquestacion (LangChain, LlamaIndex)', 'Eliminado'],
        ['Chat conversacional', 'Reemplazado por formulario de busqueda'],
        ['OCR (Tesseract)', 'Eliminado'],
        ['Generacion automatica de respuestas', 'Eliminada']],
       [7.2, 9.0]))

    # -----------------------------------------------------------------
    # 12. SEGURIDAD
    # -----------------------------------------------------------------
    C(('titulo', 1, '12. Seguridad'))
    C(('parrafo',
       'El sistema implementa los siguientes mecanismos de proteccion, '
       'verificables en el codigo fuente:'))
    C(('tabla', ['Mecanismo', 'Implementacion tecnica'],
       [['Almacenamiento de contrasenas',
         'Hash bcrypt generado con password_hash() con coste 10. Nunca se almacena '
         'ni se transmite la contrasena en texto plano.'],
        ['Prevencion de inyeccion SQL',
         'El 100 % de las consultas utiliza sentencias preparadas con PDO y '
         'parametros vinculados. No se concatenan valores en el SQL.'],
        ['Prevencion de XSS',
         'Todo dato mostrado se procesa con htmlspecialchars() con las banderas '
         'ENT_QUOTES y ENT_SUBSTITUTE.'],
        ['Proteccion CSRF',
         'Cada sesion genera un token aleatorio de 32 bytes que se valida con '
         'hash_equals() en todos los formularios POST.'],
        ['Fijacion de sesion',
         'Se ejecuta session_regenerate_id(true) al autenticar al usuario.'],
        ['Seguridad de cookies',
         'Las cookies de sesion se emiten con HttpOnly y SameSite=Lax.'],
        ['Control de inactividad',
         'La sesion se cierra automaticamente tras el periodo configurado (30 '
         'minutos por defecto).'],
        ['Proteccion contra fuerza bruta',
         'El contador de intentos fallidos se incrementa en cada fallo. Al llegar a '
         'cinco, un trigger marca la cuenta como bloqueada.'],
        ['Validacion de archivos',
         'Se verifica el tipo MIME real con finfo y se compara contra una lista '
         'blanca por extension. No se confia en el tipo declarado por el navegador.'],
        ['Prevencion de path traversal',
         'Antes de eliminar un archivo se comprueba con realpath que la ruta '
         'resuelta este dentro de la carpeta de cargas.'],
        ['Nombres de archivo seguros',
         'Los archivos se guardan con un nombre generado aleatoriamente, lo que '
         'evita sobrescrituras y la ejecucion de nombres maliciosos.'],
        ['Integridad de archivos',
         'Cada archivo tiene un hash SHA-256 que se verifica antes de cada descarga. '
         'Si no coincide, la descarga se cancela y se registra.'],
        ['Control de acceso (RBAC)',
         'Tres niveles de acceso y una matriz de permisos por modulo con operaciones '
         'de ver, crear, editar y eliminar.'],
        ['Control de acceso (ABAC)',
         'El area del usuario y el nivel de confidencialidad del documento '
         'restringen el alcance de forma adicional al rol.'],
        ['Permisos explicitos',
         'Los documentos reservados o confidenciales requieren una autorizacion '
         'nominal por usuario o por area.'],
        ['Auditoria',
         'Toda accion critica se registra con usuario, fecha, direccion IP, '
         'navegador, resultado y los estados anterior y posterior.'],
        ['Manejo de errores',
         'En produccion no se exponen mensajes tecnicos al usuario. Los errores se '
         'registran en archivo y en la tabla log_errores.']],
       [4.6, 11.6]))
    C(('aviso', 'Cifrado en reposo',
       'El cifrado del almacenamiento se delega a la infraestructura (cifrado de '
       'disco o volumen). La aplicacion no implementa cifrado propio de archivos, ya '
       'que la version anterior delegaba esta funcion a un componente retirado. Se '
       'recomienda habilitar BitLocker o LUKS en el servidor de produccion.',
       'DBEAF7'))
    C(('salto',))

    # -----------------------------------------------------------------
    # 13. FLUJO DE LA CONSULTA
    # -----------------------------------------------------------------
    C(('titulo', 1, '13. Flujo de la consulta documental'))
    C(('parrafo',
       'Este apartado reemplaza por completo al componente RAG de la version '
       'anterior. Describe como el sistema localiza informacion sin recurrir a '
       'inteligencia artificial.'))

    C(('titulo', 2, '13.1. Secuencia de la busqueda'))
    for paso in [
        'El usuario escribe uno o varios terminos en el buscador.',
        'Opcionalmente selecciona filtros: categoria, area, caso, estado, '
        'confidencialidad, etiqueta y rango de fechas.',
        'El sistema construye la clausula de acceso segun el rol y el area del '
        'usuario. Esta condicion se aplica siempre, antes de cualquier criterio de '
        'busqueda.',
        'Se ejecuta una consulta SQL con coincidencia parcial de texto sobre cinco '
        'campos: titulo, codigo, descripcion, palabras clave y el texto extraido del '
        'archivo.',
        'Para cada resultado se calcula un puntaje ponderado: 100 puntos si coincide '
        'el titulo, 60 si coinciden las palabras clave, 30 si coincide la '
        'descripcion, 20 si coincide el codigo y 10 si coincide el texto extraido.',
        'Los resultados se ordenan de mayor a menor puntaje.',
        'Se localiza la posicion del termino dentro del texto y se extrae un '
        'fragmento de contexto de 320 caracteres alrededor de la coincidencia.',
        'El termino buscado se resalta visualmente dentro del fragmento.',
        'La consulta se registra con su texto, filtros, cantidad de resultados y '
        'tiempo de respuesta en milisegundos.',
        'Si no hay coincidencias, se informa al usuario y se le presentan '
        'sugerencias concretas para reformular la busqueda.',
    ]:
        C(('numerada', paso))

    C(('titulo', 2, '13.2. Extraccion de texto de los archivos'))
    C(('parrafo',
       'La extraccion amplia el alcance de la busqueda al interior de los '
       'documentos. Es una operacion de lectura de formato, no de interpretacion de '
       'contenido.'))
    C(('tabla', ['Formato', 'Metodo de extraccion', 'Requisito tecnico'],
       [['TXT', 'Lectura directa del archivo con deteccion de codificacion y '
                'conversion a UTF-8.', 'Ninguno'],
        ['DOCX', 'Descompresion del contenedor ZIP y lectura del documento XML '
                 'interno, eliminando las etiquetas.', 'Extension zip de PHP'],
        ['XLSX', 'Descompresion del contenedor y lectura del archivo de cadenas '
                 'compartidas y de los valores de cada hoja.', 'Extension zip de PHP'],
        ['PDF', 'Lectura basica de los flujos de contenido de texto de archivos no '
                'cifrados.', 'Ninguno'],
        ['DOC / XLS', 'No soportado. Son formatos binarios propietarios que requieren '
                      'conversion externa.', '-']],
       [2.4, 9.6, 4.2]))
    C(('aviso', 'Limitaciones conocidas de la extraccion',
       'Los documentos PDF escaneados como imagen no contienen texto recuperable, '
       'por lo que no aportan contenido al buscador; siguen siendo localizables por '
       'sus metadatos. Los archivos protegidos con contrasena tampoco pueden '
       'procesarse. Estas limitaciones no se resuelven con OCR, porque esa '
       'tecnologia fue retirada del alcance.'))

    C(('titulo', 2, '13.3. Comparacion con la version anterior'))
    C(('tabla', ['Aspecto', 'Version 1.0 (con IA)', 'Version 2.0 (actual)'],
       [['Forma de consultar', 'Chat en lenguaje natural',
         'Formulario de busqueda con palabras clave'],
        ['Procesamiento', 'Embeddings y modelo de lenguaje',
         'Consulta SQL con coincidencia de texto'],
        ['Origen de la respuesta', 'Texto generado por el modelo',
         'Fragmento literal del documento original'],
        ['Trazabilidad de la fuente', 'Referencia inferida por el modelo',
         'Fragmento exacto con posicion verificable'],
        ['Riesgo de informacion inventada', 'Presente',
         'Nulo: el sistema no genera texto'],
        ['Dependencia externa', 'Modelo local o proveedor externo', 'Ninguna'],
        ['Requisitos de hardware', 'CPU o GPU para inferencia',
         'Servidor web convencional'],
        ['Requisitos de software', 'Python, contenedores, base vectorial',
         'PHP y MySQL unicamente']],
       [3.6, 6.2, 6.4]))
    C(('salto',))
