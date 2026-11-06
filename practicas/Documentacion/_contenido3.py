# -*- coding: utf-8 -*-
"""Parte 3 del contenido: secciones 14 a 19 y cierre."""


def agregar(C):
    # -----------------------------------------------------------------
    # 14. INFRAESTRUCTURA Y TECNOLOGIA
    # -----------------------------------------------------------------
    C(('titulo', 1, '14. Infraestructura y tecnologia'))

    C(('titulo', 2, '14.1. Tecnologias utilizadas'))
    C(('tabla', ['Componente', 'Tecnologia'],
       [['Frontend - estructura', 'HTML5'],
        ['Frontend - presentacion',
         'CSS3 en siete hojas modulares cargadas en orden de cascada: tokens, '
         'layout, formularios, componentes, avisos, autenticacion e impresion.'],
        ['Frontend - interactividad',
         'JavaScript sin dependencias externas: validacion de formularios, modales, '
         'medidor de contrasena, zona de carga y confirmaciones.'],
        ['Backend', 'PHP 8 con arquitectura MVC y programacion orientada a objetos.'],
        ['Base de datos', 'MySQL 8.0 con motor InnoDB y cotejamiento utf8mb4.'],
        ['Administracion de la base de datos', 'MySQL Workbench 8.0.'],
        ['Servidor web', 'Apache (XAMPP) o el servidor integrado de PHP.'],
        ['Versionamiento del esquema',
         'Tres scripts SQL secuenciales reproducibles.']],
       [4.6, 11.6]))

    C(('titulo', 2, '14.2. Componentes retirados respecto a la version 1.0'))
    C(('tabla', ['Componente anterior', 'Reemplazo en la version actual'],
       [['React 18 + TypeScript + Vite + Tailwind + shadcn/ui',
         'HTML5, CSS3 y JavaScript sin dependencias'],
        ['Python 3.12 + FastAPI + SQLAlchemy 2.0', 'PHP 8 sin frameworks'],
        ['PostgreSQL 16 y pgvector', 'MySQL 8.0 con indices FULLTEXT'],
        ['PostgreSQL FTS y Qdrant', 'MySQL FULLTEXT y busqueda por coincidencia'],
        ['PyMuPDF, python-docx, Tesseract OCR',
         'Extraccion nativa en PHP con ZipArchive y funciones de cadena'],
        ['LangChain / LlamaIndex', 'Consultas SQL deterministas'],
        ['Embeddings BGE-M3 o E5', 'Eliminado'],
        ['Ollama / vLLM con Llama 3.1, Mistral o Qwen', 'Eliminado'],
        ['MinIO o S3', 'Sistema de archivos del servidor'],
        ['JWT + OAuth2', 'Sesiones PHP nativas con cookies HttpOnly'],
        ['Prometheus, Grafana, Loki, Sentry',
         'Tabla log_errores y bitacora de auditoria'],
        ['Docker y Docker Compose', 'Instalacion directa sobre XAMPP']],
       [7.0, 9.2]))

    C(('titulo', 2, '14.3. Requisitos de infraestructura'))
    C(('tabla', ['Recurso', 'Requisito minimo', 'Recomendado'],
       [['Procesador', '2 nucleos', '4 nucleos'],
        ['Memoria RAM', '4 GB', '8 GB'],
        ['Almacenamiento', '20 GB', '100 GB con respaldo externo'],
        ['Sistema operativo', 'Windows, Linux o macOS', 'Linux en produccion'],
        ['PHP', '8.0 con pdo_mysql, fileinfo, zip y mbstring', '8.2 o superior'],
        ['MySQL', '8.0', '8.0 con respaldo automatizado'],
        ['Conectividad', 'Red local o intranet', 'HTTPS con certificado TLS'],
        ['Unidad de respaldo', 'Carpeta compartida',
         'Almacenamiento externo o en red']],
       [3.6, 6.4, 6.2]))
    C(('aviso', 'Reduccion significativa de requisitos',
       'Al eliminar los componentes de inteligencia artificial, el sistema ya no '
       'requiere GPU, ni memoria adicional para el modelo de lenguaje, ni una base '
       'de datos vectorial, ni contenedores. Esto reduce de forma notable el costo '
       'de infraestructura y simplifica la operacion, que ahora se limita a un '
       'servidor web con PHP y MySQL.', 'D9F2E4'))
    C(('salto',))

    # -----------------------------------------------------------------
    # 15. MODELO DE DATOS
    # -----------------------------------------------------------------
    C(('titulo', 1, '15. Modelo de datos'))
    C(('parrafo',
       'La base de datos se denomina gestor_documental y consta de veinte tablas con '
       'motor InnoDB y cotejamiento utf8mb4. Se complementa con cinco triggers, '
       'siete vistas, tres procedimientos almacenados y tres eventos programados.'))

    C(('titulo', 2, '15.1. Tablas del sistema'))
    C(('tabla', ['Grupo', 'Tabla', 'Proposito'],
       [['Seguridad', 'roles', 'Perfiles de acceso con su nivel de privilegio'],
        ['Seguridad', 'areas', 'Dependencias de la organizacion'],
        ['Seguridad', 'usuarios', 'Cuentas con credenciales y estado'],
        ['Seguridad', 'permisos_rol', 'Matriz de permisos por rol y modulo'],
        ['Seguridad', 'sesiones_usuario', 'Control de sesiones activas'],
        ['Clasificacion', 'categorias', 'Categorias documentales'],
        ['Clasificacion', 'casos', 'Expedientes o procesos'],
        ['Clasificacion', 'etiquetas', 'Etiquetas libres'],
        ['Documental', 'documentos', 'Registro maestro con los metadatos'],
        ['Documental', 'versiones_documento', 'Historial de archivos por version'],
        ['Documental', 'documento_etiquetas', 'Relacion documento-etiqueta'],
        ['Documental', 'permisos_documento',
         'Autorizaciones sobre documentos reservados'],
        ['Trazabilidad', 'consultas', 'Historial de busquedas'],
        ['Trazabilidad', 'consulta_resultados',
         'Documentos devueltos por cada busqueda'],
        ['Trazabilidad', 'historial_descargas', 'Registro de descargas'],
        ['Trazabilidad', 'auditoria', 'Bitacora general de acciones'],
        ['Control', 'respaldos', 'Registro de copias de seguridad'],
        ['Control', 'configuracion', 'Parametros del sistema'],
        ['Control', 'notificaciones', 'Avisos a los usuarios'],
        ['Control', 'log_errores', 'Bitacora de errores de la aplicacion']],
       [2.8, 4.4, 9.0]))

    C(('titulo', 2, '15.2. Objetos programables'))
    C(('tabla', ['Tipo', 'Nombre', 'Funcion'],
       [['Trigger', 'tr_version_after_insert',
         'Actualiza los contadores de versiones y garantiza que solo una version '
         'este marcada como vigente.'],
        ['Trigger', 'tr_consulta_resultado_insert',
         'Incrementa el contador de consultas del documento.'],
        ['Trigger', 'tr_usuario_intento_fallido',
         'Bloquea la cuenta al superar el limite de intentos fallidos.'],
        ['Trigger', 'tr_usuario_bienvenida',
         'Genera una notificacion de bienvenida al crear un usuario.'],
        ['Trigger', 'tr_documento_publicado',
         'Notifica al responsable cuando un documento pasa a publicado.'],
        ['Vista', 'v_usuarios_completo', 'Usuarios con su rol y area resueltos'],
        ['Vista', 'v_documentos_completo',
         'Documentos con todos los metadatos y la version vigente'],
        ['Vista', 'v_auditoria_detalle',
         'Bitacora con el nombre del usuario resuelto'],
        ['Vista', 'v_estadisticas_documentos',
         'Conteos por categoria para el panel'],
        ['Vista', 'v_documentos_por_area', 'Distribucion documental por area'],
        ['Vista', 'v_consultas_recientes', 'Historial de consultas con usuario'],
        ['Vista', 'v_sesiones_activas', 'Sesiones abiertas en el sistema'],
        ['Procedimiento', 'sp_registrar_auditoria',
         'Registro estandarizado de eventos'],
        ['Procedimiento', 'sp_documentos_visibles',
         'Documentos visibles para un usuario segun su nivel y area'],
        ['Procedimiento', 'sp_indicadores_panel',
         'Resumen de indicadores del sistema'],
        ['Evento', 'ev_cerrar_sesiones_inactivas',
         'Cierra sesiones inactivas cada 15 minutos'],
        ['Evento', 'ev_limpiar_notificaciones',
         'Elimina notificaciones leidas de mas de 90 dias'],
        ['Evento', 'ev_limpiar_errores', 'Elimina errores de mas de 180 dias']],
       [2.4, 4.8, 9.0]))

    C(('titulo', 2, '15.3. Niveles de confidencialidad'))
    C(('tabla', ['Nivel', 'Quien puede consultarlo'],
       [['PUBLICO', 'Cualquier usuario autenticado, sin importar su area.'],
        ['INTERNO', 'Usuarios del area responsable y responsables documentales.'],
        ['RESERVADO', 'Solo usuarios o areas con permiso explicito otorgado.'],
        ['CONFIDENCIAL',
         'Solo usuarios o areas explicitamente autorizados por el administrador.']],
       [3.4, 12.8]))

    # -----------------------------------------------------------------
    # 16. CRITERIOS DE ACEPTACION
    # -----------------------------------------------------------------
    C(('titulo', 1, '16. Criterios de aceptacion'))
    C(('parrafo',
       'El sistema se considera aceptado cuando se cumplen verificablemente los '
       'siguientes criterios:'))
    for criterio in [
        'Un administrador puede crear un usuario, asignarle un rol y verificar que quede registrado.',
        'Un usuario autorizado puede cargar un documento y verificar su estado.',
        'El sistema rechaza un archivo cuyo tipo real no corresponda a su extension.',
        'El sistema rechaza un archivo que supere el peso maximo configurado.',
        'El sistema rechaza un archivo identico a otro ya registrado, indicando cual es.',
        'Un asesor solo puede consultar los documentos permitidos para su rol, area y nivel de confidencialidad.',
        'Al intentar abrir un documento fuera de su alcance, el asesor recibe un mensaje de acceso denegado.',
        'El usuario puede buscar por palabras clave y recibe los documentos coincidentes ordenados por relevancia.',
        'Cada resultado muestra el fragmento exacto donde aparece el termino, con el termino resaltado.',
        'Cuando no hay coincidencias, el sistema lo informa y ofrece sugerencias.',
        'Los filtros avanzados restringen correctamente los resultados.',
        'Al cargar una nueva version, la anterior se conserva y puede descargarse.',
        'La verificacion de integridad confirma que el hash del archivo coincide con el registrado.',
        'Todas las acciones criticas aparecen en la bitacora con usuario, fecha y direccion IP.',
        'Un usuario sin sesion es redirigido al inicio de sesion en cualquier modulo protegido.',
        'Tras cinco intentos fallidos, la cuenta queda bloqueada automaticamente.',
        'El administrador puede generar un respaldo y descargarlo en formato SQL.',
        'Los reportes se exportan correctamente a formato CSV y son legibles en Excel.',
        'La version imprimible del reporte de indicadores se genera sin errores.',
        'La interfaz es legible y operable en una pantalla de 1366 x 768 pixeles.',
    ]:
        C(('numerada', criterio))
    C(('aviso', 'Criterios retirados',
       'Se eliminan los criterios de la version anterior referidos a "respuestas '
       'sustentadas en contenido recuperado", "respuestas fundamentadas" y '
       '"alucinaciones verificadas". Al no existir generacion automatica de texto, '
       'estos criterios dejan de ser aplicables.', 'FBE0E0'))
    C(('salto',))

    # -----------------------------------------------------------------
    # 17. RIESGOS
    # -----------------------------------------------------------------
    C(('titulo', 1, '17. Riesgos'))
    C(('tabla', ['ID', 'Riesgo', 'Impacto', 'Mitigacion'],
       [['R-01', 'Acceso indebido a documentos reservados', 'Alto',
         'RBAC por rol, ABAC por area, niveles de confidencialidad, permisos '
         'explicitos y auditoria de cada intento denegado.'],
        ['R-02', 'Carga de archivos maliciosos', 'Alto',
         'Validacion del tipo MIME real con finfo, lista blanca de extensiones, '
         'renombrado aleatorio de archivos y bloqueo de ejecucion en la carpeta de '
         'cargas.'],
        ['R-03', 'Perdida de informacion', 'Alto',
         'Respaldo completo de la base de datos, copia periodica de la carpeta de '
         'archivos y almacenamiento del respaldo fuera del servidor.'],
        ['R-04', 'Documentos desactualizados o duplicados', 'Medio',
         'Versionado obligatorio, rechazo de archivos duplicados por hash y revision '
         'periodica del inventario.'],
        ['R-05', 'Degradacion del rendimiento por volumen', 'Medio',
         'Indices sobre los campos de filtro, indices FULLTEXT para el texto y '
         'paginacion obligatoria en todos los listados.'],
        ['R-06', 'Extraccion de texto incompleta', 'Medio',
         'Documentacion clara de las limitaciones. Los documentos siguen siendo '
         'localizables por sus metadatos y palabras clave.'],
        ['R-07', 'Credenciales de demostracion en produccion', 'Alto',
         'El sistema obliga al cambio de contrasena en el primer ingreso y solicita '
         'el cambio en el panel de perfil.'],
        ['R-08', 'Configuracion incorrecta de permisos de carpeta', 'Medio',
         'Verificacion de permisos en la pantalla de configuracion e instrucciones '
         'explicitas en la documentacion de instalacion.'],
        ['R-09', 'Fallo en la generacion del respaldo', 'Medio',
         'Manejo de errores con registro del fallo y recomendacion de usar mysqldump '
         'como alternativa desde linea de comandos.'],
        ['R-10', 'Sesiones abandonadas abiertas', 'Medio',
         'Cierre automatico por inactividad y evento programado que depura las '
         'sesiones inactivas cada quince minutos.']],
       [1.4, 4.6, 1.8, 8.4]))
    C(('aviso', 'Riesgos eliminados',
       'Desaparecen los riesgos propios de la inteligencia artificial: alucinaciones '
       'del modelo, dependencia de un proveedor externo de IA y mala extraccion '
       'mediante OCR. Al no existir generacion de texto, el sistema no puede producir '
       'informacion que no este en los documentos registrados.', 'D9F2E4'))

    # -----------------------------------------------------------------
    # 18. FASES DEL PROYECTO
    # -----------------------------------------------------------------
    C(('titulo', 1, '18. Fases del proyecto'))
    C(('tabla', ['Fase', 'Actividad', 'Entregable'],
       [['1', 'Levantamiento y validacion de requerimientos',
         'Documento de requerimientos aprobado'],
        ['2', 'Diseno de la base de datos y del modelo de seguridad',
         'Scripts SQL de estructura, objetos y datos iniciales'],
        ['3', 'Desarrollo de la autenticacion, los usuarios y los permisos',
         'Modulo de acceso y modulo de usuarios funcionales'],
        ['4', 'Desarrollo del modulo de gestion documental',
         'Carga, versionado, edicion y descarga de documentos'],
        ['5', 'Implementacion de la busqueda y los filtros avanzados',
         'Buscador funcional con extraccion de texto'],
        ['6', 'Desarrollo de la auditoria y los respaldos',
         'Bitacora consultable y generacion de copias SQL'],
        ['7', 'Desarrollo de reportes e indicadores',
         'Panel de indicadores y exportacion a CSV'],
        ['8', 'Pruebas funcionales, de seguridad y de usabilidad',
         'Informe de pruebas y correcciones aplicadas'],
        ['9', 'Implementacion piloto',
         'Sistema en operacion con un grupo reducido de usuarios'],
        ['10', 'Capacitacion, documentacion y puesta en produccion',
         'Manual de usuario y sistema en produccion']],
       [1.4, 7.0, 7.8]))

    # -----------------------------------------------------------------
    # 19. INFORMACION PENDIENTE DE VALIDAR
    # -----------------------------------------------------------------
    C(('titulo', 1, '19. Informacion pendiente de validar'))
    C(('parrafo',
       'Los siguientes aspectos requieren confirmacion por parte de la organizacion '
       'antes de la puesta en produccion:'))
    C(('tabla', ['Aspecto', 'Dato requerido'],
       [['Volumen documental',
         'Cantidad de documentos actuales y crecimiento mensual estimado.'],
        ['Formatos utilizados',
         'Proporcion de cada formato para dimensionar la extraccion de texto.'],
        ['Usuarios y perfiles',
         'Numero de usuarios por rol y distribucion por areas.'],
        ['Clasificacion actual',
         'Estructura de categorias y casos vigente en la organizacion.'],
        ['Confidencialidad',
         'Criterios para clasificar un documento como reservado o confidencial.'],
        ['Politica de contrasenas',
         'Requisitos de complejidad y vigencia definidos por la organizacion.'],
        ['Retencion documental',
         'Plazos de conservacion y procedimiento de eliminacion autorizada.'],
        ['Infraestructura',
         'Servidor disponible, sistema operativo y capacidad de almacenamiento.'],
        ['Modalidad de acceso',
         'Acceso local, en intranet o remoto mediante VPN.'],
        ['Respaldo',
         'Frecuencia requerida y ubicacion del almacenamiento externo.'],
        ['Certificado TLS',
         'Disponibilidad de un certificado para habilitar HTTPS en produccion.'],
        ['Politica de proteccion de datos',
         'Normativa aplicable y responsable del tratamiento de la informacion.']],
       [4.4, 11.8]))
    C(('salto',))

    # -----------------------------------------------------------------
    # CIERRE
    # -----------------------------------------------------------------
    C(('titulo', 1, 'Resumen del cambio de alcance'))
    C(('parrafo',
       'La version 2.0 de este documento redefine el proyecto como un sistema de '
       'gestion y consulta documental construido exclusivamente con HTML, CSS, PHP y '
       'MySQL, administrado mediante MySQL Workbench.'))
    C(('parrafo', 'Los cambios principales respecto a la version 1.0 son:'))
    C(('tabla', ['Aspecto', 'Version 1.0', 'Version 2.0'],
       [['Tecnologias',
         'React, Python, PostgreSQL, Docker, modelos de lenguaje',
         'HTML, CSS, PHP y MySQL'],
        ['Consulta', 'Chat en lenguaje natural con respuestas generadas',
         'Buscador por palabras clave con filtros avanzados'],
        ['Resultado al usuario', 'Texto redactado por el modelo',
         'Fragmento literal del documento original'],
        ['Componentes de IA', 'RAG, embeddings, LLM, OCR', 'Ninguno'],
        ['Base de datos vectorial', 'pgvector o Qdrant', 'No aplica'],
        ['Hardware requerido', 'CPU o GPU para inferencia',
         'Servidor web convencional'],
        ['Costo de infraestructura', 'Elevado por los recursos de IA',
         'Reducido a un servidor web y una base de datos'],
        ['Riesgo de informacion inventada', 'Presente', 'Inexistente por diseno'],
        ['Complejidad de mantenimiento', 'Alta, multiples tecnologias',
         'Baja, un solo lenguaje de servidor']],
       [3.6, 6.2, 6.4]))
    C(('aviso', 'Consideracion final',
       'La decision de retirar la inteligencia artificial simplifica de forma '
       'significativa el desarrollo, la operacion y el mantenimiento del sistema. El '
       'resultado es una aplicacion mas predecible, mas economica de operar y con '
       'trazabilidad total: cada dato mostrado al usuario proviene directamente de un '
       'documento registrado por la organizacion, sin intermediacion de ningun '
       'proceso de inferencia.', 'DBEAF7'))
    C(('fin',))
