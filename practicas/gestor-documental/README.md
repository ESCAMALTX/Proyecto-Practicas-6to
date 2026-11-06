# Sistema Privado de Gestión y Consulta Documental

Aplicación web desarrollada con **HTML, CSS, PHP y MySQL** para centralizar,
clasificar y consultar documentos institucionales con control de acceso por
roles, trazabilidad completa y copias de seguridad.

> **Nota:** esta versión **no utiliza inteligencia artificial**. La consulta
> documental se resuelve con búsqueda por palabras clave y filtros avanzados
> sobre los metadatos y el texto extraído de los archivos.

---

## 1. Requisitos del entorno

| Componente | Versión mínima | Observación |
|---|---|---|
| PHP | 8.0 o superior | Requiere las extensiones `pdo_mysql`, `fileinfo` y `zip` |
| MySQL | 8.0 | También funciona con MariaDB 10.4 o superior |
| MySQL Workbench | 8.0 | Cliente gráfico para administrar la base de datos |
| Servidor web | Apache o similar | XAMPP, WAMP, Laragon o el servidor integrado de PHP |

### Extensiones de PHP necesarias

Verifique que estén habilitadas en `php.ini` (quite el `;` inicial si aparecen comentadas):

```ini
extension=pdo_mysql
extension=fileinfo
extension=zip
extension=mbstring
extension=openssl
```

- `pdo_mysql` → conexión a la base de datos.
- `fileinfo` → verificación del tipo real de los archivos cargados.
- `zip` → extracción de texto de archivos DOCX y XLSX.

---

## 2. Instalación paso a paso

### Paso 1 — Copiar el proyecto

Coloque la carpeta `gestor-documental` dentro del directorio público del servidor:

- **XAMPP (Windows):** `C:\xampp\htdocs\gestor-documental`
- **WAMP:** `C:\wamp64\www\gestor-documental`
- **Laragon:** `C:\laragon\www\gestor-documental`

### Paso 2 — Crear la base de datos

Abra **MySQL Workbench** y ejecute los tres scripts **en este orden**:

1. `database/01_estructura.sql` → crea la base de datos, las 20 tablas, los índices y las llaves foráneas.
2. `database/02_indices_triggers_vistas.sql` → crea 5 triggers, 7 vistas, 3 procedimientos y 3 eventos.
3. `database/03_datos_iniciales.sql` → carga roles, áreas, usuarios, categorías, casos y documentos de ejemplo.

**Forma recomendada (MySQL Workbench):**

1. Menú `File → Open SQL Script...` y seleccione `01_estructura.sql`.
2. Presione el rayo ⚡ para ejecutar todo el script.
3. Repita con los scripts `02_` y `03_` en ese orden.
4. Actualice el panel de esquemas (botón de refrescar) para ver `gestor_documental`.

**Forma alternativa (línea de comandos):**

```bash
mysql -u root -p < database/01_estructura.sql
mysql -u root -p < database/02_indices_triggers_vistas.sql
mysql -u root -p < database/03_datos_iniciales.sql
```

### Paso 3 — Configurar la conexión

Abra `config/config.php` y ajuste las credenciales de su servidor MySQL:

```php
define('DB_HOST',    '127.0.0.1');
define('DB_PUERTO',  3306);
define('DB_NOMBRE',  'gestor_documental');
define('DB_USUARIO', 'root');
define('DB_CLAVE',   '12345');    // <- cambie por su contraseña
```

> **Importante:** en producción cambie `ENTORNO` a `'produccion'` para que los
> errores detallados dejen de mostrarse en pantalla.

### Paso 4 — Verificar permisos de escritura

El servidor web necesita poder escribir en estas carpetas:

- `public/uploads/` → archivos cargados por los usuarios.
- `respaldos/` → copias de seguridad generadas (se crea automáticamente).
- `logs/` → registro de errores (se crea automáticamente).

En Windows con XAMPP suelen tener permisos por defecto. En Linux:

```bash
chmod -R 775 public/uploads respaldos logs
chown -R www-data:www-data public/uploads respaldos logs
```

### Paso 5 — Ajustar los límites de subida (opcional)

Si necesita cargar archivos mayores a 2 MB, edite `php.ini`:

```ini
upload_max_filesize = 20M
post_max_size       = 24M
max_execution_time  = 300
```

Reinicie Apache después del cambio. El límite también se configura en la
pantalla **Configuración** del sistema.

### Paso 6 — Ingresar al sistema

Abra en el navegador:

```
http://localhost/gestor-documental/public/
```

---

## 3. Credenciales iniciales

| Rol | Usuario | Contraseña | Nivel |
|---|---|---|---|
| Administrador | `admin` | `Admin123*` | 3 — Acceso total |
| Responsable documental | `abogado` | `Abogado123*` | 2 — Gestión |
| Asesor | `asesor` | `Asesor123*` | 1 — Consulta |

⚠️ **Cambie estas contraseñas antes de usar el sistema en producción.**
El sistema solicitará el cambio en el primer ingreso.

---

## 4. Estructura del proyecto

```
gestor-documental/
│
├── config/
│   ├── config.php               Parámetros globales, rutas y credenciales
│   └── database.php             Conexión PDO (patrón Singleton)
│
├── database/
│   ├── 01_estructura.sql        Base de datos, tablas, índices y llaves
│   ├── 02_indices_triggers_vistas.sql  Triggers, vistas, procedimientos, eventos
│   └── 03_datos_iniciales.sql   Roles, usuarios, categorías y datos de ejemplo
│
├── app/
│   ├── core/                    Núcleo reutilizable
│   │   ├── Database.php         Conexión y consultas preparadas
│   │   ├── Sesion.php           Sesiones, RBAC y protección CSRF
│   │   ├── Helper.php           Utilidades (escapado, formato, paginación)
│   │   ├── Validador.php        Validación de formularios
│   │   ├── Auditoria.php        Escritura de la bitácora
│   │   └── GestorArchivos.php   Validación, almacenamiento y extracción de texto
│   │
│   ├── models/                  Acceso a datos
│   │   ├── Usuario.php
│   │   ├── Documento.php
│   │   ├── Catalogo.php
│   │   ├── Consulta.php
│   │   ├── Respaldo.php
│   │   └── AuditoriaModelo.php
│   │
│   ├── controllers/             Lógica de cada módulo
│   │   ├── AuthController.php       Login, registro, cambio de clave
│   │   ├── PanelController.php      Panel y consulta documental
│   │   ├── DocumentoController.php  CRUD documental y versionado
│   │   ├── CatalogoController.php   Categorías, casos, áreas, configuración
│   │   ├── UsuarioController.php    Usuarios, roles y permisos
│   │   ├── AuditoriaController.php  Bitácora y respaldos
│   │   └── ReporteController.php    Indicadores y exportación
│   │
│   └── views/                   Vistas HTML
│       ├── partials/            header.php, footer.php
│       ├── auth/                login, registro, cambiar_clave
│       ├── panel/               index, buscar, detalle_consulta, perfil
│       ├── documentos/          index, crear, ver, editar
│       ├── categorias/          index, casos
│       ├── usuarios/            index, crear, editar, roles, areas, configuracion
│       ├── auditoria/           index, detalle, respaldos
│       └── reportes/            index, imprimir
│
├── public/                      Único directorio expuesto al navegador
│   ├── index.php                Punto de entrada y enrutador
│   ├── assets/
│   │   ├── css/
│   │   │   ├── tokens.css       Variables, reset y tipografía
│   │   │   ├── layout.css       Estructura, menú, botones, tarjetas, tablas
│   │   │   ├── formularios.css  Campos, modales, interruptores
│   │   │   ├── componentes.css  Métricas, resultados, gráficos, paginación
│   │   │   ├── avisos.css       Avisos flotantes
│   │   │   ├── auth.css         Pantallas de login y registro
│   │   │   └── impresion.css    Reportes imprimibles y responsivo
│   │   └── js/app.js            Interacciones del cliente
│   └── uploads/                 Archivos cargados (organizados por año/mes)
│
├── respaldos/                   Copias de seguridad en formato SQL
├── logs/                        Registro de errores
└── README.md
```

---

## 5. Módulos del sistema

| Módulo | Descripción | Nivel mínimo |
|---|---|---|
| Panel de control | Indicadores, documentos recientes, notificaciones | 1 |
| Consulta documental | Búsqueda por palabras clave y filtros avanzados | 1 |
| Documentos | Inventario, ficha, versiones, descarga | 1 (gestión: 2) |
| Categorías | Clasificación temática de documentos | 2 |
| Casos | Expedientes que agrupan documentos | 2 |
| Usuarios | Creación, edición, estados y claves | 2 (admin: 3) |
| Roles y permisos | Matriz de permisos por módulo | 3 |
| Áreas | Dependencias de la organización | 3 |
| Auditoría | Bitácora de todas las acciones críticas | 2 (admin: 3) |
| Respaldos | Generación y descarga de copias SQL | 3 |
| Reportes | Indicadores exportables a CSV y versión imprimible | 2 |
| Configuración | Parámetros generales del sistema | 3 |

---

## 6. Arquitectura y Consulta Documental

El sistema resuelve la gestión y consulta documental mediante librerías de software estándar y consultas estructuradas en MySQL, sin requerir componentes de inteligencia artificial. El procesamiento y la extracción se realizan de manera determinista y local.

### Librerías Utilizadas

| Librería / Componente | Función en la Arquitectura |
|---|---|
| **ZipArchive (PHP Lib)** | Descompresión en memoria y lectura de árboles XML (word/document.xml, xl/sharedStrings.xml) para extraer texto de archivos .docx y .xlsx. |
| **info / Fileinfo (PHP Lib)** | Inspección de firmas binarias (Magic Bytes) para validación estricta de tipos MIME reales. |
| **PDO MySQL (PHP Data Objects)** | Conexión e interacción segura mediante sentencias SQL preparadas contra inyección. |
| **mbstring (PHP Multibyte)** | Normalización, búsqueda y codificación de texto multilingüe en UTF-8. |
| **OpenSSL / Hash Lib (PHP Lib)** | Cálculo de hashes criptográficos SHA-256 para validación de integridad documental. |

El flujo de consulta documental es el siguiente: El flujo es el siguiente:

1. El usuario escribe palabras clave en el buscador.
2. Opcionalmente aplica filtros: categoría, área, caso, estado,
   confidencialidad, etiqueta y rango de fechas.
3. El sistema busca coincidencias en:
   - el título y el código del documento,
   - la descripción y las palabras clave,
   - **el texto extraído del archivo** (PDF, DOCX, XLSX y TXT).
4. Se calcula un puntaje de coincidencia y se ordenan los resultados.
5. Cada resultado muestra el **fragmento exacto** donde aparece el término,
   resaltado, junto con el documento de origen.
6. La consulta queda registrada en la bitácora con su tiempo de respuesta.
7. Si no hay coincidencias, el sistema lo informa y sugiere alternativas.

### Extracción de texto

| Formato | Método | Requisito |
|---|---|---|
| `.txt` | Lectura directa con detección de codificación | — |
| `.docx` | Lectura del XML interno del contenedor ZIP | Extensión `zip` |
| `.xlsx` | Lectura de `sharedStrings.xml` y las hojas | Extensión `zip` |
| `.pdf` | Extracción básica de flujos de texto no cifrados | — |
| `.doc` / `.xls` | No soportado (formatos binarios antiguos) | — |

> Los PDF escaneados como imagen **no** producen texto extraíble. Tampoco los
> PDF protegidos con contraseña. En esos casos el documento sigue siendo
> consultable por sus metadatos.

---

## 7. Seguridad implementada

| Mecanismo | Implementación |
|---|---|
| Contraseñas | Hash bcrypt con `password_hash()` (coste 10) |
| Inyección SQL | 100 % de las consultas usan sentencias preparadas con PDO |
| XSS | Todo texto se escapa con `htmlspecialchars()` al mostrarse |
| CSRF | Token por sesión validado en todos los formularios POST |
| Fijación de sesión | `session_regenerate_id(true)` al iniciar sesión |
| Cookies | `HttpOnly` y `SameSite=Lax` |
| Inactividad | Cierre automático a los 30 minutos |
| Fuerza bruta | Bloqueo de la cuenta tras 5 intentos fallidos |
| Archivos maliciosos | Validación del tipo MIME real con `finfo`, no de la extensión |
| Path traversal | Verificación de que la ruta esté dentro de `public/uploads` |
| Integridad | Hash SHA-256 verificado en cada descarga |
| Duplicados | Rechazo de archivos con el mismo hash ya registrado |
| Trazabilidad | Bitácora con estado anterior y posterior de cada cambio |
| Autorización | RBAC por rol (3 niveles) y ABAC por área y confidencialidad |

---

## 8. Respaldos

### Desde la aplicación

Vaya a **Respaldos** y presione *Generar respaldo ahora*. Elija:

- **Completo** → estructura de tablas y datos.
- **Solo datos** → únicamente las sentencias `INSERT`.

El archivo `.sql` se guarda en `respaldos/` y puede descargarse.

### Desde la línea de comandos

```bash
# Generar
mysqldump -u root -p --routines --triggers --events gestor_documental > respaldo.sql

# Restaurar
mysql -u root -p gestor_documental < respaldo.sql
```

### Restaurar desde MySQL Workbench

1. Menú `Server → Data Import`.
2. Seleccione `Import from Self-Contained File` y ubique el archivo `.sql`.
3. En `Default Target Schema` escriba `gestor_documental`.
4. Presione `Start Import`.

> **Los archivos de `public/uploads/` no se incluyen en el respaldo SQL.**
> Copie esa carpeta aparte con regularidad.

---

## 9. Solución de problemas

### «No fue posible conectar con la base de datos»

- Verifique que el servicio MySQL esté iniciado.
- Confirme que la base de datos `gestor_documental` exista.
- Revise las credenciales en `config/config.php`.

### «Failed opening required .../app/core/Database.php»

Los separadores de ruta se normalizan en `config.php`. Si persiste, confirme que
el archivo `app/core/Database.php` exista en esa ubicación exacta.

### La extracción de texto de DOCX o XLSX no funciona

Habilite la extensión `zip` en `php.ini` y reinicie el servidor web.
Verifíquelo en **Configuración → Información del entorno**.

### Los archivos grandes no se cargan

Ajuste `upload_max_filesize` y `post_max_size` en `php.ini`, y también el
parámetro *Peso máximo por archivo* en la pantalla de **Configuración**.

### El respaldo falla

Asegúrese de que la carpeta `respaldos/` tenga permisos de escritura.
Para bases grandes, aumente `max_execution_time` en `php.ini`.

### Error «Access denied for user 'root'»

La contraseña de `config.php` no coincide con la del servidor MySQL.
En XAMPP la clave de `root` suele estar vacía; en instalaciones nativas de
MySQL suele tener una contraseña definida durante la instalación.

---

## 10. Puesta en producción

Antes de exponer el sistema a usuarios reales:

1. Cambie `ENTORNO` a `'produccion'` en `config/config.php`.
2. Cambie las contraseñas de los tres usuarios de demostración.
3. Cree un usuario MySQL dedicado con permisos solo sobre `gestor_documental`.
4. Configure HTTPS con un certificado TLS válido.
5. Restrinja el acceso a `config/`, `app/`, `database/`, `logs/` y `respaldos/`
   desde la configuración del servidor web.
6. Programe respaldos automáticos de la base de datos y de `public/uploads/`.
7. Verifique la política de retención de la bitácora (mínimo 12 meses).

### Ejemplo de `.htaccess` para Apache

```apache
# Denegar el acceso directo a las carpetas internas
RedirectMatch 404 ^/gestor-documental/(app|config|database|logs|respaldos)/

# Bloquear la ejecución de scripts dentro de uploads
<Directory "public/uploads">
    php_flag engine off
</Directory>
```

---

## 11. Correspondencia con los requerimientos

| ID | Requerimiento | Dónde se implementa |
|---|---|---|
| RF-001 | Inicio y cierre de sesión | `AuthController`, `Sesion` |
| RF-002 | Crear, desactivar y consultar usuarios | `UsuarioController` |
| RF-003 | Permisos por rol y área | `Sesion::puede()`, `permisos_rol` |
| RF-004 | Carga documental | `DocumentoController::crear()` |
| RF-005 | Validación de archivos | `GestorArchivos::subir()` |
| RF-006 | Metadatos del documento | Tabla `documentos` |
| RF-007 | Extracción de contenido | `GestorArchivos::extraerTexto()` |
| RF-008 | Indexación para búsqueda | Índices `FULLTEXT` |
| RF-009 | Búsqueda por términos | `Documento::buscar()` |
| RF-013 | Fuentes utilizadas | Fragmentos resaltados en resultados |
| RF-014 | Control de acceso documental | `Documento::puedeAcceder()` |
| RF-015 | Historial de consultas | Tabla `consultas` |
| RF-016 | Gestión documental | Editar, archivar, versionar |
| RF-017 | Auditoría | Tabla `auditoria` y `Auditoria.php` |
| RF-018 | Indicadores | `PanelController`, `ReporteController` |
| RF-019 | Respaldos | `Respaldo::generar()` |
| RF-020 | Manejo de errores | Mensajes de «sin resultados» |
| RF-021 | Versionado | Tabla `versiones_documento` |
| RF-024 | Búsqueda avanzada | Filtros combinados |
| RF-025 | Exportación | CSV de inventario, consultas y usuarios |
| RF-027 | Retención | Eventos programados y depuración |
| RF-028 | Panel administrativo | `panel/index.php` |
| RF-029 | Multi-formato | PDF, DOCX, DOC, XLSX, XLS, TXT |
| RF-030 | Historial de consultas | `panel/detalle_consulta.php` |

| ID | Requerimiento no funcional | Implementación |
|---|---|---|
| RNF-001 | Protección contra accesos no autorizados | Sesiones, RBAC, CSRF |
| RNF-002 | Confidencialidad documental | Niveles y permisos explícitos |
| RNF-006 | Usabilidad | Interfaz en español, responsive, WCAG 2.1 AA |
| RNF-007 | Trazabilidad | Bitácora con datos antes/después |
| RNF-008 | Mantenibilidad | Arquitectura MVC, código documentado |
| RNF-009 | Integridad documental | Hash SHA-256 verificado |
| RNF-011 | Respaldos | Módulo de respaldos |
| RNF-017 | Idioma español | Toda la interfaz |
| RNF-018 | Accesibilidad | Foco visible, ARIA, contraste AA |
| RNF-019 | Cifrado en reposo | Delegado a la infraestructura |
| RNF-020 | Retención de logs | 365 días, depuración manual y automática |

---

## 12. Tecnologías utilizadas

| Capa | Tecnología |
|---|---|
| Presentación | HTML5, CSS3 (7 hojas modulares), JavaScript (ES5+, sin dependencias) |
| Lógica de servidor | PHP 8 con arquitectura MVC y programación orientada a objetos |
| Base de datos | MySQL 8.0 (InnoDB, utf8mb4, FULLTEXT, triggers, vistas, procedimientos, eventos) |
| Administración de la BD | MySQL Workbench 8.0 |
| Servidor web | Apache (XAMPP) |

---

**Sistema Privado de Gestión y Consulta Documental** — versión 1.0.0
