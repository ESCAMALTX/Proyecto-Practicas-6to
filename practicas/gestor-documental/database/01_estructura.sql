-- =====================================================================
--  SISTEMA PRIVADO DE GESTION Y CONSULTA DOCUMENTAL
--  Motor: MySQL 8.0  |  Cliente: MySQL Workbench
--  Archivo: 01_estructura.sql
--  Descripcion: Creacion de la base de datos, tablas, indices,
--               llaves foraneas, triggers y vistas.
-- =====================================================================

DROP DATABASE IF EXISTS gestor_documental;
CREATE DATABASE gestor_documental
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE gestor_documental;

-- =====================================================================
-- 1. SEGURIDAD: ROLES, AREAS Y USUARIOS
-- =====================================================================

-- ---------------------------------------------------------------------
-- Tabla: roles
-- Define los perfiles de acceso del sistema.
-- ---------------------------------------------------------------------
CREATE TABLE roles (
    id_rol          INT AUTO_INCREMENT PRIMARY KEY,
    nombre          VARCHAR(50)  NOT NULL UNIQUE,
    descripcion     VARCHAR(200) NULL,
    nivel_acceso    TINYINT      NOT NULL DEFAULT 1 COMMENT '1=Consulta 2=Gestion 3=Administracion',
    activo          TINYINT(1)   NOT NULL DEFAULT 1,
    fecha_creacion  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB COMMENT='Perfiles de acceso del sistema';


-- ---------------------------------------------------------------------
-- Tabla: areas
-- Areas o departamentos de la organizacion. Permiten segmentar
-- el acceso a los documentos por area del usuario.
-- ---------------------------------------------------------------------
CREATE TABLE areas (
    id_area         INT AUTO_INCREMENT PRIMARY KEY,
    nombre          VARCHAR(100) NOT NULL UNIQUE,
    descripcion     VARCHAR(200) NULL,
    activo          TINYINT(1)   NOT NULL DEFAULT 1,
    fecha_creacion  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB COMMENT='Areas o departamentos de la organizacion';


-- ---------------------------------------------------------------------
-- Tabla: usuarios
-- Almacena las cuentas. La contrasena se guarda SIEMPRE con hash
-- (password_hash de PHP / bcrypt), nunca en texto plano.
-- ---------------------------------------------------------------------
CREATE TABLE usuarios (
    id_usuario          INT AUTO_INCREMENT PRIMARY KEY,
    nombre_completo     VARCHAR(150) NOT NULL,
    documento           VARCHAR(30)  NOT NULL UNIQUE COMMENT 'Cedula o identificacion',
    correo              VARCHAR(150) NOT NULL UNIQUE,
    usuario             VARCHAR(50)  NOT NULL UNIQUE,
    clave_hash          VARCHAR(255) NOT NULL COMMENT 'Hash bcrypt generado por PHP',
    id_rol              INT          NOT NULL,
    id_area             INT          NULL,
    telefono            VARCHAR(30)  NULL,
    cargo               VARCHAR(100) NULL,
    estado              ENUM('ACTIVO','INACTIVO','BLOQUEADO') NOT NULL DEFAULT 'ACTIVO',
    intentos_fallidos   TINYINT      NOT NULL DEFAULT 0,
    ultimo_acceso       DATETIME     NULL,
    debe_cambiar_clave  TINYINT(1)   NOT NULL DEFAULT 0,
    fecha_creacion      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
                                     ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_usuario_rol
        FOREIGN KEY (id_rol)  REFERENCES roles(id_rol)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_usuario_area
        FOREIGN KEY (id_area) REFERENCES areas(id_area)
        ON UPDATE CASCADE ON DELETE SET NULL,

    INDEX idx_usuario_estado (estado),
    INDEX idx_usuario_rol    (id_rol)
) ENGINE=InnoDB COMMENT='Cuentas de usuario del sistema';


-- ---------------------------------------------------------------------
-- Tabla: permisos_rol
-- Permisos detallados por rol y modulo. Complementa el nivel_acceso.
-- ---------------------------------------------------------------------
CREATE TABLE permisos_rol (
    id_permiso   INT AUTO_INCREMENT PRIMARY KEY,
    id_rol       INT         NOT NULL,
    modulo       VARCHAR(50) NOT NULL COMMENT 'usuarios, documentos, categorias, auditoria, respaldo',
    puede_ver    TINYINT(1)  NOT NULL DEFAULT 0,
    puede_crear  TINYINT(1)  NOT NULL DEFAULT 0,
    puede_editar TINYINT(1)  NOT NULL DEFAULT 0,
    puede_eliminar TINYINT(1) NOT NULL DEFAULT 0,

    CONSTRAINT fk_permiso_rol
        FOREIGN KEY (id_rol) REFERENCES roles(id_rol)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT uq_rol_modulo UNIQUE (id_rol, modulo)
) ENGINE=InnoDB COMMENT='Permisos por rol y modulo';


-- =====================================================================
-- 2. CLASIFICACION DOCUMENTAL
-- =====================================================================

-- ---------------------------------------------------------------------
-- Tabla: categorias
-- ---------------------------------------------------------------------
CREATE TABLE categorias (
    id_categoria    INT AUTO_INCREMENT PRIMARY KEY,
    nombre          VARCHAR(100) NOT NULL UNIQUE,
    descripcion     VARCHAR(250) NULL,
    activo          TINYINT(1)   NOT NULL DEFAULT 1,
    fecha_creacion  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB COMMENT='Categorias de clasificacion documental';


-- ---------------------------------------------------------------------
-- Tabla: casos
-- Expedientes, procesos o casos a los que se asocia un documento.
-- ---------------------------------------------------------------------
CREATE TABLE casos (
    id_caso         INT AUTO_INCREMENT PRIMARY KEY,
    codigo          VARCHAR(50)  NOT NULL UNIQUE,
    titulo          VARCHAR(200) NOT NULL,
    descripcion     TEXT         NULL,
    id_area         INT          NULL,
    estado          ENUM('ABIERTO','EN_PROCESO','CERRADO','ARCHIVADO') NOT NULL DEFAULT 'ABIERTO',
    fecha_apertura  DATE         NOT NULL DEFAULT (CURRENT_DATE),
    fecha_cierre    DATE         NULL,
    fecha_creacion  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_caso_area
        FOREIGN KEY (id_area) REFERENCES areas(id_area)
        ON UPDATE CASCADE ON DELETE SET NULL,

    INDEX idx_caso_estado (estado)
) ENGINE=InnoDB COMMENT='Casos o expedientes institucionales';


-- ---------------------------------------------------------------------
-- Tabla: etiquetas
-- ---------------------------------------------------------------------
CREATE TABLE etiquetas (
    id_etiqueta INT AUTO_INCREMENT PRIMARY KEY,
    nombre      VARCHAR(60) NOT NULL UNIQUE
) ENGINE=InnoDB COMMENT='Etiquetas libres para clasificar documentos';


-- =====================================================================
-- 3. DOCUMENTOS Y VERSIONADO
-- =====================================================================

-- ---------------------------------------------------------------------
-- Tabla: documentos (registro maestro)
-- Guarda los metadatos. El contenido binario vive en el servidor de
-- archivos y se referencia mediante ruta_archivo.
-- ---------------------------------------------------------------------
CREATE TABLE documentos (
    id_documento     INT AUTO_INCREMENT PRIMARY KEY,
    codigo           VARCHAR(50)  NOT NULL UNIQUE COMMENT 'Codigo interno del documento',
    titulo           VARCHAR(250) NOT NULL,
    descripcion      TEXT         NULL,
    id_categoria     INT          NOT NULL,
    id_caso          INT          NULL,
    id_area          INT          NULL,
    id_responsable   INT          NOT NULL COMMENT 'Usuario que carga el documento',
    autor            VARCHAR(150) NULL COMMENT 'Autor o entidad emisora',
    fecha_documento  DATE         NOT NULL COMMENT 'Fecha del documento original',
    nivel_confidencialidad ENUM('PUBLICO','INTERNO','RESERVADO','CONFIDENCIAL')
                     NOT NULL DEFAULT 'INTERNO',
    palabra_clave    VARCHAR(300) NULL COMMENT 'Palabras separadas por coma, alimentan la busqueda',
    estado           ENUM('BORRADOR','EN_PROCESO','PUBLICADO','ARCHIVADO','ELIMINADO')
                     NOT NULL DEFAULT 'BORRADOR',
    version_actual   INT          NOT NULL DEFAULT 1,
    total_versiones  INT          NOT NULL DEFAULT 1,
    total_consultas  INT          NOT NULL DEFAULT 0,
    fecha_creacion   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
                                  ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_documento_categoria
        FOREIGN KEY (id_categoria) REFERENCES categorias(id_categoria)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_documento_caso
        FOREIGN KEY (id_caso) REFERENCES casos(id_caso)
        ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_documento_area
        FOREIGN KEY (id_area) REFERENCES areas(id_area)
        ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_documento_responsable
        FOREIGN KEY (id_responsable) REFERENCES usuarios(id_usuario)
        ON UPDATE CASCADE ON DELETE RESTRICT,

    INDEX idx_doc_estado      (estado),
    INDEX idx_doc_categoria   (id_categoria),
    INDEX idx_doc_caso        (id_caso),
    INDEX idx_doc_area        (id_area),
    INDEX idx_doc_confidencialidad (nivel_confidencialidad),
    INDEX idx_doc_fecha       (fecha_documento),
    FULLTEXT idx_doc_busqueda (titulo, descripcion, palabra_clave)
) ENGINE=InnoDB COMMENT='Registro maestro de documentos';


-- ---------------------------------------------------------------------
-- Tabla: versiones_documento
-- Historial de cada archivo cargado para un documento.
-- ---------------------------------------------------------------------
CREATE TABLE versiones_documento (
    id_version        INT AUTO_INCREMENT PRIMARY KEY,
    id_documento      INT          NOT NULL,
    numero_version    INT          NOT NULL,
    nombre_archivo    VARCHAR(255) NOT NULL COMMENT 'Nombre original del archivo',
    nombre_almacenado VARCHAR(255) NOT NULL COMMENT 'Nombre unico en el servidor',
    ruta_archivo      VARCHAR(400) NOT NULL,
    extension         VARCHAR(10)  NOT NULL,
    tipo_mime         VARCHAR(120) NOT NULL,
    peso_bytes        BIGINT       NOT NULL DEFAULT 0,
    hash_archivo      VARCHAR(64)  NOT NULL COMMENT 'SHA-256 para verificar integridad',
    paginas           INT          NULL,
    texto_extraido    LONGTEXT     NULL COMMENT 'Contenido de texto para busqueda por palabras',
    es_version_actual TINYINT(1)   NOT NULL DEFAULT 1,
    estado            ENUM('DISPONIBLE','OBSERVADO','RECHAZADO') NOT NULL DEFAULT 'DISPONIBLE',
    observacion       VARCHAR(300) NULL,
    id_usuario_carga  INT          NOT NULL,
    fecha_carga       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_version_documento
        FOREIGN KEY (id_documento) REFERENCES documentos(id_documento)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_version_usuario
        FOREIGN KEY (id_usuario_carga) REFERENCES usuarios(id_usuario)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT uq_documento_version UNIQUE (id_documento, numero_version),

    INDEX idx_version_actual (id_documento, es_version_actual),
    FULLTEXT idx_version_texto (texto_extraido)
) ENGINE=InnoDB COMMENT='Versiones de archivo de cada documento';


-- ---------------------------------------------------------------------
-- Tabla: documento_etiquetas
-- ---------------------------------------------------------------------
CREATE TABLE documento_etiquetas (
    id_documento INT NOT NULL,
    id_etiqueta  INT NOT NULL,

    PRIMARY KEY (id_documento, id_etiqueta),
    CONSTRAINT fk_de_documento
        FOREIGN KEY (id_documento) REFERENCES documentos(id_documento)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_de_etiqueta
        FOREIGN KEY (id_etiqueta) REFERENCES etiquetas(id_etiqueta)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB COMMENT='Relacion documento-etiqueta';


-- ---------------------------------------------------------------------
-- Tabla: permisos_documento
-- Control de acceso fino: autoriza a un usuario concreto o a toda
-- un area a consultar un documento marcado como reservado.
-- ---------------------------------------------------------------------
CREATE TABLE permisos_documento (
    id_permiso_doc INT AUTO_INCREMENT PRIMARY KEY,
    id_documento   INT         NOT NULL,
    id_usuario     INT         NULL,
    id_area        INT         NULL,
    puede_consultar TINYINT(1) NOT NULL DEFAULT 1,
    puede_descargar TINYINT(1) NOT NULL DEFAULT 0,
    fecha_otorgado  DATETIME   NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_pd_documento
        FOREIGN KEY (id_documento) REFERENCES documentos(id_documento)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_pd_usuario
        FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_pd_area
        FOREIGN KEY (id_area) REFERENCES areas(id_area)
        ON UPDATE CASCADE ON DELETE CASCADE,

    INDEX idx_pd_documento (id_documento)
) ENGINE=InnoDB COMMENT='Permisos especificos sobre documentos reservados';


-- =====================================================================
-- 4. CONSULTAS Y TRAZABILIDAD
-- =====================================================================

-- ---------------------------------------------------------------------
-- Tabla: consultas
-- Registra cada busqueda o consulta realizada por un usuario.
-- ---------------------------------------------------------------------
CREATE TABLE consultas (
    id_consulta     INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario      INT          NOT NULL,
    texto_consulta  VARCHAR(500) NOT NULL,
    tipo_consulta   ENUM('RAPIDA','AVANZADA','FILTRO') NOT NULL DEFAULT 'RAPIDA',
    filtros_aplicados VARCHAR(400) NULL COMMENT 'JSON con los filtros usados',
    total_resultados INT         NOT NULL DEFAULT 0,
    resultado_encontrado TINYINT(1) NOT NULL DEFAULT 1,
    tiempo_ms       INT          NOT NULL DEFAULT 0,
    direccion_ip    VARCHAR(45)  NULL,
    fecha_consulta  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_consulta_usuario
        FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
        ON UPDATE CASCADE ON DELETE CASCADE,

    INDEX idx_consulta_usuario (id_usuario),
    INDEX idx_consulta_fecha   (fecha_consulta),
    FULLTEXT idx_consulta_texto (texto_consulta)
) ENGINE=InnoDB COMMENT='Historial de consultas realizadas';


-- ---------------------------------------------------------------------
-- Tabla: consulta_resultados
-- Documentos devueltos por cada consulta y su nivel de coincidencia.
-- ---------------------------------------------------------------------
CREATE TABLE consulta_resultados (
    id_resultado    INT AUTO_INCREMENT PRIMARY KEY,
    id_consulta     INT          NOT NULL,
    id_documento    INT          NOT NULL,
    id_version      INT          NULL,
    fragmento       VARCHAR(500) NULL COMMENT 'Extracto donde se encontro la coincidencia',
    pagina          INT          NULL,
    relevancia      DECIMAL(6,3) NOT NULL DEFAULT 0 COMMENT 'Puntaje de coincidencia',
    fue_abierto     TINYINT(1)   NOT NULL DEFAULT 0,
    fecha           DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_cr_consulta
        FOREIGN KEY (id_consulta) REFERENCES consultas(id_consulta)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_cr_documento
        FOREIGN KEY (id_documento) REFERENCES documentos(id_documento)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_cr_version
        FOREIGN KEY (id_version) REFERENCES versiones_documento(id_version)
        ON UPDATE CASCADE ON DELETE SET NULL,

    INDEX idx_cr_consulta (id_consulta)
) ENGINE=InnoDB COMMENT='Documentos devueltos por cada consulta';


-- ---------------------------------------------------------------------
-- Tabla: historial_descargas
-- ---------------------------------------------------------------------
CREATE TABLE historial_descargas (
    id_descarga    INT AUTO_INCREMENT PRIMARY KEY,
    id_documento   INT         NOT NULL,
    id_version     INT         NULL,
    id_usuario     INT         NOT NULL,
    direccion_ip   VARCHAR(45) NULL,
    fecha_descarga DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_hd_documento
        FOREIGN KEY (id_documento) REFERENCES documentos(id_documento)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_hd_version
        FOREIGN KEY (id_version) REFERENCES versiones_documento(id_version)
        ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_hd_usuario
        FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
        ON UPDATE CASCADE ON DELETE CASCADE,

    INDEX idx_hd_documento (id_documento),
    INDEX idx_hd_fecha (fecha_descarga)
) ENGINE=InnoDB COMMENT='Registro de descargas de archivos';


-- ---------------------------------------------------------------------
-- Tabla: auditoria
-- Bitacora general del sistema. Registra accesos, cargas, cambios
-- y eliminaciones (acciones criticas).
-- ---------------------------------------------------------------------
CREATE TABLE auditoria (
    id_auditoria   BIGINT AUTO_INCREMENT PRIMARY KEY,
    id_usuario     INT          NULL,
    usuario_texto  VARCHAR(50)  NULL COMMENT 'Se conserva aunque se borre el usuario',
    modulo         VARCHAR(50)  NOT NULL,
    accion         VARCHAR(50)  NOT NULL COMMENT 'CREAR, ACTUALIZAR, ELIMINAR, LOGIN, LOGOUT, DESCARGAR, CONSULTAR',
    entidad        VARCHAR(50)  NULL COMMENT 'Tabla afectada',
    id_entidad     INT          NULL,
    descripcion    VARCHAR(500) NULL,
    datos_antes    TEXT         NULL COMMENT 'JSON del estado previo',
    datos_despues  TEXT         NULL COMMENT 'JSON del estado nuevo',
    direccion_ip   VARCHAR(45)  NULL,
    agente_usuario VARCHAR(255) NULL,
    resultado      ENUM('EXITO','FALLO','ADVERTENCIA') NOT NULL DEFAULT 'EXITO',
    fecha_evento   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_auditoria_usuario
        FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
        ON UPDATE CASCADE ON DELETE SET NULL,

    INDEX idx_aud_usuario (id_usuario),
    INDEX idx_aud_modulo  (modulo),
    INDEX idx_aud_accion  (accion),
    INDEX idx_aud_fecha   (fecha_evento)
) ENGINE=InnoDB COMMENT='Bitacora de auditoria del sistema';


-- ---------------------------------------------------------------------
-- Tabla: sesiones_usuario
-- Control de sesiones activas para forzar cierre por inactividad.
-- ---------------------------------------------------------------------
CREATE TABLE sesiones_usuario (
    id_sesion      BIGINT AUTO_INCREMENT PRIMARY KEY,
    id_usuario     INT          NOT NULL,
    token_sesion   VARCHAR(128) NOT NULL UNIQUE,
    direccion_ip   VARCHAR(45)  NULL,
    agente_usuario VARCHAR(255) NULL,
    fecha_inicio   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ultima_actividad DATETIME   NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_cierre   DATETIME     NULL,
    activa         TINYINT(1)   NOT NULL DEFAULT 1,

    CONSTRAINT fk_sesion_usuario
        FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
        ON UPDATE CASCADE ON DELETE CASCADE,

    INDEX idx_sesion_usuario (id_usuario, activa)
) ENGINE=InnoDB COMMENT='Sesiones activas de usuarios';


-- =====================================================================
-- 5. RESPALDO Y CONFIGURACION
-- =====================================================================

-- ---------------------------------------------------------------------
-- Tabla: respaldos
-- Registro de las copias de seguridad generadas.
-- ---------------------------------------------------------------------
CREATE TABLE respaldos (
    id_respaldo     INT AUTO_INCREMENT PRIMARY KEY,
    nombre_archivo  VARCHAR(255) NOT NULL,
    ruta_archivo    VARCHAR(400) NOT NULL,
    peso_bytes      BIGINT       NOT NULL DEFAULT 0,
    tipo            ENUM('COMPLETO','PARCIAL','SOLO_DATOS') NOT NULL DEFAULT 'COMPLETO',
    estado          ENUM('EN_PROCESO','COMPLETADO','FALLIDO') NOT NULL DEFAULT 'EN_PROCESO',
    id_usuario      INT          NOT NULL,
    observacion     VARCHAR(300) NULL,
    fecha_respaldo  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_respaldo_usuario
        FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
        ON UPDATE CASCADE ON DELETE RESTRICT,

    INDEX idx_respaldo_fecha (fecha_respaldo)
) ENGINE=InnoDB COMMENT='Registro de copias de seguridad';


-- ---------------------------------------------------------------------
-- Tabla: configuracion
-- Parametros generales del sistema.
-- ---------------------------------------------------------------------
CREATE TABLE configuracion (
    id_config      INT AUTO_INCREMENT PRIMARY KEY,
    clave          VARCHAR(60)  NOT NULL UNIQUE,
    valor          VARCHAR(300) NOT NULL,
    descripcion    VARCHAR(250) NULL,
    fecha_actualizacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
                                ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB COMMENT='Parametros de configuracion del sistema';


-- ---------------------------------------------------------------------
-- Tabla: notificaciones
-- Avisos al usuario sobre el estado de procesamiento de documentos.
-- ---------------------------------------------------------------------
CREATE TABLE notificaciones (
    id_notificacion INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario      INT          NOT NULL,
    titulo          VARCHAR(150) NOT NULL,
    mensaje         VARCHAR(500) NOT NULL,
    tipo            ENUM('INFO','EXITO','ADVERTENCIA','ERROR') NOT NULL DEFAULT 'INFO',
    enlace          VARCHAR(300) NULL,
    leida           TINYINT(1)   NOT NULL DEFAULT 0,
    fecha_creacion  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_notificacion_usuario
        FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
        ON UPDATE CASCADE ON DELETE CASCADE,

    INDEX idx_notif_usuario (id_usuario, leida)
) ENGINE=InnoDB COMMENT='Notificaciones para los usuarios';


-- ---------------------------------------------------------------------
-- Tabla: log_errores
-- Manejo de errores de la aplicacion.
-- ---------------------------------------------------------------------
CREATE TABLE log_errores (
    id_error       BIGINT AUTO_INCREMENT PRIMARY KEY,
    id_usuario     INT          NULL,
    archivo        VARCHAR(300) NULL,
    linea          INT          NULL,
    mensaje        VARCHAR(500) NOT NULL,
    traza          TEXT         NULL,
    url_solicitada VARCHAR(400) NULL,
    fecha_error    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_error_fecha (fecha_error)
) ENGINE=InnoDB COMMENT='Bitacora de errores de la aplicacion';
