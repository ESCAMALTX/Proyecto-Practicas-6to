# Especificacion de Requerimientos de Software (SRS)
## Sistema Privado de Gestion y Consulta Documental
**Version 2.0 Unificada (Definitiva) - Basada en Librerias de Software (Sin Inteligencia Artificial)**

---

### Control de Versiones del Documento

| Version | Fecha | Tipo de Documento | Descripcion de Cambios y Alcance |
|---|---|---|---|
| 1.0 | 23/09/2026 | Documento Inicial | Propuesta preliminar: Sistema de consulta documental con enfoque de IA (RAG, modelos generativos, chat conversacional). |
| 2.0 | 30/09/2026 | Documento Tecnico | Reestructuracion: Eliminacion total de componentes de Inteligencia Artificial por razones de determinismo, auditoria y cumplimiento. Adopcion de pila PHP y MySQL. |
| 2.0 Unificada | 06/11/2026 | Documento Definitivo Consolidado | Fusion exhaustiva: Se unen los antecedentes organizacionales, objetivos, actores, casos de uso, 30 requerimientos funcionales, 12 requerimientos no funcionales, reglas de negocio y arquitectura 100% basada en librerias nativas (ZipArchive, FileInfo, PDO, Mbstring, OpenSSL). Redaccion formal y sobria sin emoticonos. |

---

### 1. Introduccion y Contexto Organizacional
El presente documento formaliza los requerimientos de software para el desarrollo y puesta en marcha del Sistema Privado de Gestion y Consulta Documental. En el entorno institucional contemporaneo, la gestion ordenada de documentos tecnicos, juridicos, administrativos y operativos constituye la base fundamental para garantizar el principio de legalidad, la seguridad de la informacion, la transparencia y la continuidad del negocio.

La aplicacion se disena bajo una arquitectura modular y privada, garantizando que el acceso y administracion de los documentos se encuentre estrictamente gobernado por perfiles de usuario, dependencias funcionales y criterios de confidencialidad previamente homologados.

---

### 2. Descripcion de la Necesidad
La institucion administra cotidianamente un volumen significativo de documentos en diversos formatos digitales (PDF, DOCX, XLSX, TXT) distribuidos a traves de multiples dependencias (Juridica, Administrativa, Recursos Humanos, Operaciones y Sistemas). Dicha dispersion ocasiona serias dificultades en la recuperacion oportuna de antecedentes, la verificacion de versiones vigentes y el control riguroso de la confidencialidad.

Se precisa una plataforma centralizada que permita almacenar, clasificar, catalogar, versionar y consultar todo el acervo documental de forma rapida y controlada, registrando de forma inalterable cada accion ejecutada sobre el patrimonio informativo de la institucion.

---

### 3. Planteamiento del Problema
La gestion documental manual o fragmentada en carpetas locales de red genera las siguientes problematicas criticas:
- Perdida excesiva de tiempo operativo en busquedas manuales sobre estructuras de directorios desordenadas.
- Dificultad e incertidumbre para localizar antecedentes historicos o expedientes de casos concluidos.
- Riesgo de trabajar sobre versiones obsoletas o borradores no aprobados de contratos, actas o directivas.
- Ausencia de trazabilidad y bitacora forense: imposibilidad de auditar quien consulto, descargo, modifico o retiro un archivo.
- Brechas de seguridad y fuga de informacion sensible por carecer de control de acceso granular basado en confidencialidad y areas.

---

### 4. Justificacion de la Arquitectura basada en Librerias (Cero IA)
A diferencia de la propuesta exploratoria preliminar (Version 1.0) que contemplaba el uso de componentes de Inteligencia Artificial Generativa y Chatbots, el analisis tecnico de riesgos, normatividad legal e infraestructura determino la eliminacion total de la IA a favor de un sistema fundamentado 100% en Librerias de Software Tradicionales y Consultas SQL Deterministas:

| Factor de Evaluacion | Riesgo de Sistemas con IA Generativa | Garantia con Librerias de Software (Proyecto) |
|---|---|---|
| Precision y Confiabilidad | Riesgo de "alucinaciones" o respuestas inventadas no presentes en los textos oficiales. | Determinismo matematico absoluto: los fragmentos devueltos corresponden exactamente al texto literal extraido. |
| Seguridad y Privacidad | Envio inadvertido de informacion confidencial a APIs en la nube de proveedores de IA. | Procesamiento 100% local en el servidor propio. Ningun dato abandona la infraestructura institucional. |
| Validez Juridica | Falta de explicabilidad y dificultad probatoria de inferencias generadas por modelos de caja negra. | Trazabilidad forense completa: coincidencia verificable entre el termino buscado y la fuente oficial. |
| Infraestructura y Costos | Altos requerimientos computacionales (GPUs dedicadas, consumo de tokens o licencias recurrentes). | Operacion altamente eficiente sobre servidores estandar PHP y MySQL, sin costos de licenciamiento IA. |

---

### 5. Objetivos del Proyecto

#### 5.1 Objetivo General
Disenar, desarrollar e implementar un Sistema Privado de Gestion y Consulta Documental que permita a los usuarios institucionales autorizados centralizar, catalogar, asegurar, versionar y consultar documentos oficiales mediante extraccion de texto con librerias nativas de software y busqueda estructurada, garantizando control de acceso por roles, trazabilidad auditada de operaciones y copias de seguridad de la base de datos.

#### 5.2 Objetivos Especificos
- Disenar una estructura relacional normalizada en MySQL con 20 entidades, llaves foraneas, vistas, procedimientos almacenados y triggers de automatizacion.
- Implementar un modulo seguro de autenticacion con control de acceso basado en roles (RBAC) y confidencialidad por dependencias (ABAC).
- Construir un gestor de archivos con verificacion binaria real de tipo MIME (FileInfo) e integridad criptografica SHA-256.
- Desarrollar componentes de extraccion de texto en memoria para formatos DOCX y XLSX mediante la libreria nativa ZipArchive, y procesamiento de flujos para PDF y TXT.
- Implementar un motor determinista de consulta y calificacion de relevancia documental basado en busqueda por palabras clave con extraccion de fragmentos exactos.
- Desarrollar una interfaz grafica institucional con patron de explorador de dos columnas (arbol jerarquico y tabla de documentos) e iconos tipificados por archivo.
- Establecer una bitacora inmutable de auditoria que registre cada operacion critica (alta, actualizacion, consulta, descarga y bloqueo de cuentas).
- Proveer un modulo de generacion, verificacion y descarga de respaldos logicos completos de la base de datos.

---

### 6. Alcance del Sistema

#### 6.1 Modulos y Funcionalidades Incluidas
- Modulo de Autenticacion y Seguridad: control de intentos fallidos (maximo 5), bloqueo de cuentas, forzado de cambio de clave inicial y cierre por inactividad a 30 minutos.
- Modulo de Gestion de Usuarios: altas, ediciones, desactivaciones y asignacion de roles y areas institucionales.
- Modulo de Roles y Permisos: matriz granular de permisos (ver, crear, editar, eliminar) para 8 modulos del sistema.
- Modulo de Gestion Documental: inventario maestro, carga de archivos, metadatos, clasificacion por categorias y casos, y control estricto de versionamiento.
- Modulo de Consulta Documental: busqueda estructurada por palabras clave con filtros cruzados de area, categoria, caso, fecha, etiqueta y nivel de confidencialidad.
- Modulo de Auditoria y Trazabilidad: consulta de eventos con direccion IP, agente de usuario, estado previo y estado posterior.
- Modulo de Respaldos: generacion manual de volcados SQL y descarga directa para custodia externa.
- Modulo de Reportes: visualizacion de indicadores de gestion y exportacion a formato CSV e impresion formal.

#### 6.2 Aspectos Fuera del Alcance (Exclusiones)
- No incluye sustitucion del juicio juridico o administrativo de los profesionales responsables.
- No incluye procesamiento probabilistico de lenguaje natural, chatbots, bases de datos vectoriales ni algoritmos de IA.
- No incluye digitalizacion fisica ni hardware de escaneo masivo.
- No incluye firma digital con certificado emitido por entidad de certificacion abierta (se implementa hash SHA-256 de integridad).
- No incluye exposicion publica o acceso abierto a usuarios no autenticados en la red institucional.

---

### 7. Actores del Sistema y Matriz de Roles

| Rol / Actor | Nivel de Acceso | Perfil Institucional | Responsabilidades y Alcance en el Sistema |
|---|---|---|---|
| Administrador | Nivel 3 (Total) | Jefe de Sistemas / Administrador de Seguridad | Control absoluto del sistema: gestion de usuarios, roles, permisos, configuracion general, bitacora de auditoria, generacion de respaldos y gestion documental global. |
| Responsable Documental | Nivel 2 (Gestion) | Abogado / Coordinador de Archivo y Correspondencia | Administracion operativa de documentos: carga de nuevos expedientes, actualizacion de versiones, clasificacion por categorias y casos, y publicacion o retiro de archivos. |
| Asesor | Nivel 1 (Consulta) | Asesor Juridico / Profesional Operativo | Consulta y lectura de documentos autorizados para su rol, area o nivel de confidencialidad. Visualizacion de fragmentos coincidentes y consulta de metadatos. |
| Servicio de Base de Datos | Sistema (Interno) | Eventos programados y Triggers MySQL | Ejecucion automatica de reglas de negocio: conteo de versiones, bloqueo tras intentos fallidos, actualizacion de consultas e integridad referencial. |

---

### 8. Requerimientos Funcionales Detallados (RF-001 a RF-030)

| Codigo | Nombre del Requerimiento | Prioridad | Descripcion Funcional y Criterio de Verificacion |
|---|---|---|---|
| RF-001 | Autenticacion de usuarios | Alta | Validar credenciales con hash bcrypt. Control de sesiones unicas y cierre automatico tras 30 minutos de inactividad. |
| RF-002 | Control de acceso por roles | Alta | Restringir pantallas y acciones segun el nivel (1=Asesor, 2=Responsable Documental, 3=Administrador). |
| RF-003 | Control de confidencialidad | Alta | Filtrar acceso a documentos segun clasificacion (PUBLICO, INTERNO, RESERVADO, CONFIDENCIAL) y area del usuario. |
| RF-004 | Inventario maestro documental | Alta | Mostrar lista paginada de documentos visibles con codigo, titulo, categoria, version, estado y fecha. |
| RF-005 | Carga y almacenamiento de archivos | Alta | Cargar archivos PDF, DOCX, XLSX y TXT hasta 20 MB con validacion de firma MIME real y renombrado seguro. |
| RF-006 | Registro de metadatos documentales | Alta | Registrar codigo unico, titulo, descripcion, categoria, caso, area, autor, fecha y palabras clave. |
| RF-007 | Extraccion de texto con ZipArchive | Alta | Extraer en memoria el texto plano de documentos DOCX y XLSX leyendo los XML internos sin software externo. |
| RF-008 | Extraccion de texto de PDF y TXT | Alta | Extraer secuencias de texto legibles de documentos PDF no protegidos y archivos de texto plano UTF-8. |
| RF-009 | Busqueda determinista por palabras | Alta | Localizar documentos por coincidencia de terminos en metadatos y texto extraido, ordenados por puntaje. |
| RF-010 | Filtros cruzados avanzados | Alta | Filtrar por categoria, area, caso, estado, nivel de confidencialidad, etiqueta y rango de fechas. |
| RF-011 | Extraccion de fragmentos de evidencia | Alta | Presentar el parrafo exacto de 320 caracteres donde aparece la palabra buscada, resaltando el termino. |
| RF-012 | Control de versiones documentales | Alta | Crear nuevas versiones de un documento conservando historico, numero correlativo y archivo fisico. |
| RF-013 | Ficha tecnica del documento | Media | Mostrar detalles completos: ficha de metadatos, historial de versiones, eventos de auditoria y descargas. |
| RF-014 | Descarga segura de archivos | Alta | Permitir la descarga de versiones autorizadas verificando previamente la integridad SHA-256 en disco. |
| RF-015 | Registro de consultas documentales | Alta | Almacenar cada termino buscado, filtros aplicados, tiempo de respuesta en milisegundos y usuario ejecutor. |
| RF-016 | Gestion de categorias | Media | Crear, editar, activar y desactivar categorias documentales con validacion de no duplicidad de nombres. |
| RF-017 | Gestion de casos y expedientes | Media | Administrar codigos y descripciones de casos asignados a areas institucionales para agrupar documentos. |
| RF-018 | Panel de control e indicadores | Media | Desplegar metricas cuantitativas de documentos activos, espacio en disco, usuarios y resumen de consultas. |
| RF-019 | Etiquetado documental | Media | Asociar etiquetas tematicas a los documentos para enriquecer los criterios de busqueda y agrupacion. |
| RF-020 | Aviso de informacion insuficiente | Alta | Si la busqueda no encuentra coincidencias, informar formalmente la ausencia de evidencia y sugerir pasos. |
| RF-021 | Trazabilidad de auditoria | Alta | Registrar en bitacora inmutable fecha, hora, usuario, IP, modulo, accion, estado previo y posterior. |
| RF-022 | Administracion de usuarios | Alta | Crear y actualizar usuarios, reestablecer contrasenas, activar, inactivar o desbloquear cuentas. |
| RF-023 | Cambio de contrasena obligatorio | Alta | Exigir al usuario el cambio de clave en el primer inicio de sesion o tras reestablecimiento administrativo. |
| RF-024 | Paginacion eficiente de datos | Media | Segmentar listados extensos en bloques de 10 registros utilizando consultas SQL OFFSET y LIMIT optimizadas. |
| RF-025 | Generacion de respaldos SQL | Alta | Crear copias de seguridad de la estructura y datos de la base de datos MySQL en formato .sql descargable. |
| RF-026 | Descarga de copias de seguridad | Alta | Permitir al Administrador descargar archivos de respaldo almacenados en el directorio protegido del servidor. |
| RF-027 | Configuracion general del sistema | Media | Parametrizar nombre de la institucion, tamano maximo de subida, limites de inactividad e intentos. |
| RF-028 | Exportacion de reportes en CSV | Media | Descargar inventarios, estadisticas de consulta y bitacoras en formato plano CSV compatible con hojas de calculo. |
| RF-029 | Verificacion de integridad SHA-256 | Alta | Calcular y registrar el hash SHA-256 al subir un archivo y comprobarlo antes de cualquier descarga. |
| RF-030 | Interfaz tipo ISOTools con arbol | Media | Disponer de navegador de dos columnas con arbol jerarquico de carpetas e iconos graficos por extension. |

---

### 9. Requerimientos No Funcionales (RNF-001 a RNF-012)

| Codigo | Categoria | Parametro / Estandar | Especificacion Tecnica |
|---|---|---|---|
| RNF-001 | Seguridad | Sentencias Preparadas PDO | El 100% de las sentencias SQL ejecutan parametros parametrizados mediante PDO, eliminando riesgo de inyeccion SQL. |
| RNF-002 | Rendimiento | Tiempo de Respuesta | El tiempo de ejecucion en busquedas estructuradas sobre 10.000 registros no debe superar los 500 milisegundos. |
| RNF-003 | Seguridad | Cifrado de Contrasenas | Almacenamiento de claves mediante algoritmo hash bcrypt con coste criptografico 10 nativo de PHP. |
| RNF-004 | Seguridad | Proteccion Cross-Site | Escapado total de caracteres HTML (htmlspecialchars UTF-8) y validacion estricta de tokens CSRF en formularios POST. |
| RNF-005 | Disponibilidad | Operatividad Local | Disponibilidad del 99.5% en entorno intranet, sin depender de conexion a internet ni servicios de terceros. |
| RNF-006 | Seguridad | Control de Sesion | Regeneracion automatica de identificador de sesion tras login, directivas HttpOnly y SameSite=Lax en cookies. |
| RNF-007 | Compatibilidad | Pila de Software Abierta | Compatibilidad comprobada en PHP 8.0 a 8.4, MySQL 8.0 y MariaDB 10.4+, ejecutable en XAMPP, WAMP o Linux. |
| RNF-008 | Mantenibilidad | Arquitectura MVC Ligera | Separacion estricta de responsabilidades en carpetas app/core, app/models, app/controllers y app/views. |
| RNF-009 | Integridad | Criptografia SHA-256 | Calculo obligatorio de hash SHA-256 de cada archivo físico para deteccion de adulteracion o corrupcion. |
| RNF-010 | Seguridad | Mitigacion Path Traversal | Almacenamiento en nombres ofuscados (UUID/Hexadecimal) y verificacion estricta de rutas con realpath(). |
| RNF-011 | Auditoria | Inalterabilidad de Bitacora | La tabla de auditoria no posee opciones de modificacion o eliminacion desde la interfaz de la aplicacion. |
| RNF-012 | Usabilidad | Diseno Responsive Adaptativo | Interfaz visual en tonos corporativos ISOTools, usable tanto en estaciones de trabajo como en dispositivos portatiles. |

---

### 10. Reglas de Negocio Institucionales (RN-001 a RN-010)

| Codigo | Nombre de la Regla | Condicion y Comportamiento Obligatorio |
|---|---|---|
| RN-001 | Acceso Exclusivo Autenticado | Ninguna funcionalidad del sistema (salvo la pantalla de login) es accesible sin una sesion de usuario activa y valida. |
| RN-002 | Privilegio de Confidencialidad | Los documentos marcados como RESERVADO o CONFIDENCIAL solo son visibles para usuarios pertenecientes a la misma area o con nivel Administrador. |
| RN-003 | Exclusividad de Version Vigente | Un documento puede tener multiples versiones en su historico, pero exactamente una version debe poseer la marca de version actual. |
| RN-004 | Validacion Binaria de Subida | Un archivo solo es aceptado si su contenido binario real verificado por FileInfo coincide con la lista blanca (PDF, DOCX, XLSX, TXT). |
| RN-005 | Bloqueo por Fuerza Bruta | Al registrarse 5 intentos fallidos consecutivos de inicio de sesion, el trigger de base de datos marca automaticamente el usuario como BLOQUEADO. |
| RN-006 | Trazabilidad de Cambios de Estado | Cualquier modificacion en el estado de un documento (Borrador -> Publicado -> Archivado) debe registrarse en la bitacora de auditoria. |
| RN-007 | No Eliminacion Fisica en Cascada | La eliminacion de documentos en operacion normal realiza un borrado logico (estado ELIMINADO), preservando los archivos fisicos para peritaje. |
| RN-008 | Control de Duplicidad Criptografica | El sistema rechaza la carga de un archivo cuyo hash SHA-256 coincida exactamente con una version ya activa del mismo documento. |
| RN-009 | Explicabilidad y Cero Invencion | El motor de busqueda solo entrega extractos literales verificados de los textos. Si no hay coincidencia, debe declarar formalmente ausencia de evidencia. |
| RN-010 | Unicidad de Codigos Institucionales | Los codigos de identificacion documental (ej. DOC-2026-0001) son unicos a nivel institucional y generados correlativamente. |

---

### 11. Arquitectura Conceptual y Tecnologica
La solucion opera bajo un modelo por capas completamente desacoplado:
1. **Capa de Presentacion**: HTML5 semantico, CSS3 corporativo (ISOTools) y JavaScript Vanilla puro, sin dependencias de frameworks externos.
2. **Capa de Controladores**: PHP 8.0+ estructurado en controladores orientados a objetos que procesan las peticiones del usuario y coordinan la logica de negocio.
3. **Capa de Nucleo y Librerias**:
   - `ZipArchive`: Extraccion de texto en memoria leyendo los XML internos de DOCX y XLSX.
   - `FileInfo (finfo)`: Inspeccion de cabeceras binarias (Magic Bytes) para validacion real de tipos MIME.
   - `PDO MySQL`: Capa de abstraccion de base de datos con sentencias preparadas.
   - `Mbstring`: Procesamiento y normalizacion de cadenas UTF-8.
   - `Hash Lib (SHA-256)`: Generacion de huellas digitales criptograficas.
4. **Capa de Persistencia y Datos**: Base de datos MySQL 8.0 / MariaDB 10.4+ normalizada en 20 tablas InnoDB, provista de triggers, procedimientos almacenados y vistas optimizadas.
5. **Capa de Archivos**: Almacenamiento seguro en disco con nombres ofuscados y permisos restrictivos (0640).

---

### 12. Diccionario de Tablas de Base de Datos
1. `roles`: Perfiles de acceso (Administrador, Responsable Documental, Asesor).
2. `areas`: Dependencias institucionales.
3. `usuarios`: Cuentas, credenciales bcrypt y estado.
4. `permisos_rol`: Matriz de permisos para los 8 modulos.
5. `categorias`: Catalogo tipologico de documentos.
6. `casos`: Agrupadores de expedientes.
7. `documentos`: Entidad maestra de documentos institucionales.
8. `versiones_documento`: Archivos físicos, texto extraido, tamano y hash SHA-256.
9. `etiquetas`: Descriptores tematicos.
10. `documento_etiquetas`: Tabla asociativa muchos a muchos.
11. `consultas`: Registro de busquedas estructuradas y tiempo de respuesta.
12. `consulta_resultados`: Documentos recuperados por consulta.
13. `auditoria`: Bitacora inalterable de operaciones con estado previo/posterior.
14. `notificaciones`: Avisos institucionales y de seguridad.
15. `respaldos`: Historial de copias de seguridad de la base de datos.
16. `configuracion`: Parametros globales del sistema.
17. `sesiones_usuario`: Control de conexiones activas.
18. `historial_descargas`: Trazabilidad de descargas con usuario, fecha e IP.
19. `permisos_documento`: Excepciones de acceso documental.
20. `log_errores`: Registro de excepciones tecnicas del servidor.
