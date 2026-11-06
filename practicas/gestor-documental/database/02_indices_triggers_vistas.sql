-- =====================================================================
--  SISTEMA PRIVADO DE GESTION Y CONSULTA DOCUMENTAL
--  Archivo: 02_indices_triggers_vistas.sql
--  Descripcion: Objetos programables de apoyo sobre MySQL 8.0
--  Ejecutar DESPUES de 01_estructura.sql
-- =====================================================================

USE gestor_documental;

DELIMITER $$

-- =====================================================================
-- 1. TRIGGERS
-- =====================================================================

-- ---------------------------------------------------------------------
-- Al insertar una version nueva, actualiza los contadores del
-- documento maestro automaticamente.
-- ---------------------------------------------------------------------
DROP TRIGGER IF EXISTS tr_version_after_insert $$
CREATE TRIGGER tr_version_after_insert
AFTER INSERT ON versiones_documento
FOR EACH ROW
BEGIN
    UPDATE documentos
       SET version_actual   = NEW.numero_version,
           total_versiones  = (SELECT COUNT(*)
                                 FROM versiones_documento
                                WHERE id_documento = NEW.id_documento),
           fecha_actualizacion = CURRENT_TIMESTAMP
     WHERE id_documento = NEW.id_documento;

    -- Solo una version puede estar marcada como actual
    IF NEW.es_version_actual = 1 THEN
        UPDATE versiones_documento
           SET es_version_actual = 0
         WHERE id_documento = NEW.id_documento
           AND id_version <> NEW.id_version;
    END IF;
END $$


-- ---------------------------------------------------------------------
-- Al incrementar el contador de consultas de un documento.
-- ---------------------------------------------------------------------
DROP TRIGGER IF EXISTS tr_consulta_resultado_insert $$
CREATE TRIGGER tr_consulta_resultado_insert
AFTER INSERT ON consulta_resultados
FOR EACH ROW
BEGIN
    UPDATE documentos
       SET total_consultas = total_consultas + 1
     WHERE id_documento = NEW.id_documento;
END $$


-- ---------------------------------------------------------------------
-- Bloquea el primer intento fallido de sesion y marca la cuenta
-- como BLOQUEADA al superar 5 intentos.
-- ---------------------------------------------------------------------
DROP TRIGGER IF EXISTS tr_usuario_intento_fallido $$
CREATE TRIGGER tr_usuario_intento_fallido
BEFORE UPDATE ON usuarios
FOR EACH ROW
BEGIN
    IF NEW.intentos_fallidos >= 5 AND OLD.intentos_fallidos < 5 THEN
        SET NEW.estado = 'BLOQUEADO';
    END IF;
END $$


-- ---------------------------------------------------------------------
-- Al crear un usuario, se generan notificaciones de bienvenida.
-- ---------------------------------------------------------------------
DROP TRIGGER IF EXISTS tr_usuario_bienvenida $$
CREATE TRIGGER tr_usuario_bienvenida
AFTER INSERT ON usuarios
FOR EACH ROW
BEGIN
    INSERT INTO notificaciones (id_usuario, titulo, mensaje, tipo)
    VALUES (NEW.id_usuario,
            'Bienvenido al sistema',
            CONCAT('Su cuenta fue creada correctamente. Usuario: ', NEW.usuario),
            'EXITO');
END $$


-- ---------------------------------------------------------------------
-- Notifica al responsable cuando un documento pasa a PUBLICADO.
-- ---------------------------------------------------------------------
DROP TRIGGER IF EXISTS tr_documento_publicado $$
CREATE TRIGGER tr_documento_publicado
AFTER UPDATE ON documentos
FOR EACH ROW
BEGIN
    IF NEW.estado = 'PUBLICADO' AND OLD.estado <> 'PUBLICADO' THEN
        INSERT INTO notificaciones (id_usuario, titulo, mensaje, tipo, enlace)
        VALUES (NEW.id_responsable,
                'Documento publicado',
                CONCAT('El documento "', NEW.titulo, '" quedo publicado y disponible para consulta.'),
                'EXITO',
                CONCAT('index.php?modulo=documentos&accion=ver&id=', NEW.id_documento));
    END IF;
END $$


DELIMITER ;


-- =====================================================================
-- 2. VISTAS
-- =====================================================================

-- ---------------------------------------------------------------------
-- Vista: v_usuarios_completo
-- ---------------------------------------------------------------------
DROP VIEW IF EXISTS v_usuarios_completo;
CREATE VIEW v_usuarios_completo AS
SELECT u.id_usuario,
       u.nombre_completo,
       u.documento,
       u.correo,
       u.usuario,
       u.telefono,
       u.cargo,
       u.estado,
       u.intentos_fallidos,
       u.debe_cambiar_clave,
       u.ultimo_acceso,
       u.fecha_creacion,
       r.id_rol,
       r.nombre        AS rol,
       r.nivel_acceso,
       a.id_area,
       a.nombre        AS area
  FROM usuarios u
  INNER JOIN roles r ON r.id_rol = u.id_rol
  LEFT  JOIN areas a ON a.id_area = u.id_area;


-- ---------------------------------------------------------------------
-- Vista: v_documentos_completo
-- Centraliza los metadatos mas consultados por el frontend.
-- ---------------------------------------------------------------------
DROP VIEW IF EXISTS v_documentos_completo;
CREATE VIEW v_documentos_completo AS
SELECT d.id_documento,
       d.codigo,
       d.titulo,
       d.descripcion,
       d.autor,
       d.fecha_documento,
       d.nivel_confidencialidad,
       d.palabra_clave,
       d.estado,
       d.version_actual,
       d.total_versiones,
       d.total_consultas,
       d.fecha_creacion,
       c.id_categoria,
       c.nombre        AS categoria,
       ca.id_caso,
       ca.codigo       AS codigo_caso,
       ca.titulo       AS caso,
       ar.id_area,
       ar.nombre       AS area,
       u.id_usuario    AS id_responsable,
       u.nombre_completo AS responsable,
       v.id_version,
       v.nombre_archivo,
       v.extension,
       v.peso_bytes,
       v.paginas
  FROM documentos d
  INNER JOIN categorias c  ON c.id_categoria = d.id_categoria
  LEFT  JOIN casos ca      ON ca.id_caso     = d.id_caso
  LEFT  JOIN areas ar      ON ar.id_area     = d.id_area
  INNER JOIN usuarios u    ON u.id_usuario   = d.id_responsable
  LEFT  JOIN versiones_documento v
         ON v.id_documento = d.id_documento AND v.es_version_actual = 1;


-- ---------------------------------------------------------------------
-- Vista: v_auditoria_detalle
-- ---------------------------------------------------------------------
DROP VIEW IF EXISTS v_auditoria_detalle;
CREATE VIEW v_auditoria_detalle AS
SELECT a.id_auditoria,
       a.fecha_evento,
       COALESCE(u.nombre_completo, a.usuario_texto, 'Sistema') AS usuario,
       a.modulo,
       a.accion,
       a.entidad,
       a.id_entidad,
       a.descripcion,
       a.direccion_ip,
       a.resultado
  FROM auditoria a
  LEFT JOIN usuarios u ON u.id_usuario = a.id_usuario;


-- ---------------------------------------------------------------------
-- Vista: v_estadisticas_documentos
-- Alimenta el panel de indicadores del administrador.
-- ---------------------------------------------------------------------
DROP VIEW IF EXISTS v_estadisticas_documentos;
CREATE VIEW v_estadisticas_documentos AS
SELECT c.id_categoria,
       c.nombre                       AS categoria,
       COUNT(d.id_documento)          AS total_documentos,
       SUM(CASE WHEN d.estado = 'PUBLICADO' THEN 1 ELSE 0 END) AS publicados,
       SUM(CASE WHEN d.estado = 'BORRADOR'  THEN 1 ELSE 0 END) AS borradores,
       SUM(CASE WHEN d.estado = 'ARCHIVADO' THEN 1 ELSE 0 END) AS archivados,
       COALESCE(SUM(d.total_consultas), 0) AS total_consultas
  FROM categorias c
  LEFT JOIN documentos d ON d.id_categoria = c.id_categoria
                        AND d.estado <> 'ELIMINADO'
 GROUP BY c.id_categoria, c.nombre;


-- ---------------------------------------------------------------------
-- Vista: v_documentos_por_area
-- ---------------------------------------------------------------------
DROP VIEW IF EXISTS v_documentos_por_area;
CREATE VIEW v_documentos_por_area AS
SELECT a.id_area,
       a.nombre AS area,
       COUNT(d.id_documento) AS total_documentos,
       SUM(CASE WHEN d.nivel_confidencialidad IN ('RESERVADO','CONFIDENCIAL')
                THEN 1 ELSE 0 END) AS documentos_reservados
  FROM areas a
  LEFT JOIN documentos d ON d.id_area = a.id_area
                        AND d.estado <> 'ELIMINADO'
 GROUP BY a.id_area, a.nombre;


-- ---------------------------------------------------------------------
-- Vista: v_consultas_recientes
-- ---------------------------------------------------------------------
DROP VIEW IF EXISTS v_consultas_recientes;
CREATE VIEW v_consultas_recientes AS
SELECT c.id_consulta,
       c.fecha_consulta,
       u.nombre_completo AS usuario,
       c.texto_consulta,
       c.tipo_consulta,
       c.total_resultados,
       c.resultado_encontrado,
       c.tiempo_ms
  FROM consultas c
  INNER JOIN usuarios u ON u.id_usuario = c.id_usuario;


-- ---------------------------------------------------------------------
-- Vista: v_sesiones_activas
-- ---------------------------------------------------------------------
DROP VIEW IF EXISTS v_sesiones_activas;
CREATE VIEW v_sesiones_activas AS
SELECT s.id_sesion,
       s.id_usuario,
       u.nombre_completo,
       s.direccion_ip,
       s.fecha_inicio,
       s.ultima_actividad
  FROM sesiones_usuario s
  INNER JOIN usuarios u ON u.id_usuario = s.id_usuario
 WHERE s.activa = 1;


-- =====================================================================
-- 3. PROCEDIMIENTOS ALMACENADOS
-- =====================================================================

DELIMITER $$

-- ---------------------------------------------------------------------
-- Registra un evento de auditoria de forma estandar.
-- ---------------------------------------------------------------------
DROP PROCEDURE IF EXISTS sp_registrar_auditoria $$
CREATE PROCEDURE sp_registrar_auditoria(
    IN p_id_usuario   INT,
    IN p_usuario      VARCHAR(50),
    IN p_modulo       VARCHAR(50),
    IN p_accion       VARCHAR(50),
    IN p_entidad      VARCHAR(50),
    IN p_id_entidad   INT,
    IN p_descripcion  VARCHAR(500),
    IN p_ip           VARCHAR(45),
    IN p_resultado    VARCHAR(20)
)
BEGIN
    INSERT INTO auditoria (id_usuario, usuario_texto, modulo, accion, entidad,
                           id_entidad, descripcion, direccion_ip, agente_usuario, resultado)
    VALUES (p_id_usuario, p_usuario, p_modulo, p_accion, p_entidad,
            p_id_entidad, p_descripcion, p_ip, USER(), p_resultado);
END $$


-- ---------------------------------------------------------------------
-- Cuenta los documentos visibles segun el rol y el area del usuario.
-- Regla de negocio: nivel_acceso 3 ve todo, 2 ve su area mas los
-- documentos INTERNOS, 1 solo ve su area con nivel PUBLICO o INTERNO.
-- ---------------------------------------------------------------------
DROP PROCEDURE IF EXISTS sp_documentos_visibles $$
CREATE PROCEDURE sp_documentos_visibles(IN p_id_usuario INT)
BEGIN
    DECLARE v_nivel   TINYINT;
    DECLARE v_area    INT;

    SELECT r.nivel_acceso, u.id_area
      INTO v_nivel, v_area
      FROM usuarios u
      INNER JOIN roles r ON r.id_rol = u.id_rol
     WHERE u.id_usuario = p_id_usuario;

    IF v_nivel >= 3 THEN
        SELECT * FROM v_documentos_completo WHERE estado = 'PUBLICADO';
    ELSEIF v_nivel = 2 THEN
        SELECT * FROM v_documentos_completo
         WHERE estado = 'PUBLICADO'
           AND (id_area = v_area OR nivel_confidencialidad = 'INTERNO');
    ELSE
        SELECT * FROM v_documentos_completo
         WHERE estado = 'PUBLICADO'
           AND (id_area = v_area OR id_area IS NULL)
           AND nivel_confidencialidad IN ('PUBLICO','INTERNO');
    END IF;
END $$


-- ---------------------------------------------------------------------
-- Resumen de indicadores para el panel de administracion.
-- ---------------------------------------------------------------------
DROP PROCEDURE IF EXISTS sp_indicadores_panel $$
CREATE PROCEDURE sp_indicadores_panel()
BEGIN
    SELECT
        (SELECT COUNT(*) FROM usuarios  WHERE estado = 'ACTIVO')                AS usuarios_activos,
        (SELECT COUNT(*) FROM usuarios  WHERE estado = 'INACTIVO')              AS usuarios_inactivos,
        (SELECT COUNT(*) FROM documentos WHERE estado = 'PUBLICADO')            AS documentos_publicados,
        (SELECT COUNT(*) FROM documentos WHERE estado = 'BORRADOR')             AS documentos_borrador,
        (SELECT COUNT(*) FROM documentos WHERE estado = 'ARCHIVADO')            AS documentos_archivados,
        (SELECT COUNT(*) FROM categorias WHERE activo = 1)                      AS categorias_activas,
        (SELECT COUNT(*) FROM casos     WHERE estado IN ('ABIERTO','EN_PROCESO')) AS casos_abiertos,
        (SELECT COUNT(*) FROM consultas WHERE DATE(fecha_consulta) = CURRENT_DATE) AS consultas_hoy,
        (SELECT COUNT(*) FROM consultas)                                        AS consultas_totales,
        (SELECT COALESCE(SUM(peso_bytes),0) FROM versiones_documento)           AS peso_total_bytes;
END $$


DELIMITER ;


-- =====================================================================
-- 4. EVENTOS PROGRAMADOS
-- =====================================================================

SET GLOBAL event_scheduler = ON;

DELIMITER $$

-- ---------------------------------------------------------------------
-- Cierra sesiones inactivas por mas de 30 minutos (gestion de sesiones).
-- ---------------------------------------------------------------------
DROP EVENT IF EXISTS ev_cerrar_sesiones_inactivas $$
CREATE EVENT IF NOT EXISTS ev_cerrar_sesiones_inactivas
ON SCHEDULE EVERY 15 MINUTE
DO
BEGIN
    UPDATE sesiones_usuario
       SET activa = 0,
           fecha_cierre = CURRENT_TIMESTAMP
     WHERE activa = 1
       AND ultima_actividad < DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 30 MINUTE);
END $$

-- ---------------------------------------------------------------------
-- Elimina notificaciones leidas con mas de 90 dias (retencion).
-- ---------------------------------------------------------------------
DROP EVENT IF EXISTS ev_limpiar_notificaciones $$
CREATE EVENT IF NOT EXISTS ev_limpiar_notificaciones
ON SCHEDULE EVERY 1 DAY
DO
BEGIN
    DELETE FROM notificaciones
     WHERE leida = 1
       AND fecha_creacion < DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 90 DAY);
END $$

-- ---------------------------------------------------------------------
-- Limpia errores antiguos (retencion de logs).
-- ---------------------------------------------------------------------
DROP EVENT IF EXISTS ev_limpiar_errores $$
CREATE EVENT IF NOT EXISTS ev_limpiar_errores
ON SCHEDULE EVERY 1 DAY
DO
BEGIN
    DELETE FROM log_errores
     WHERE fecha_error < DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 180 DAY);
END $$

DELIMITER ;
