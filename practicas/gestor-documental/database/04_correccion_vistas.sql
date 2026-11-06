-- Correccion aplicada sobre una base de datos ya instalada.
-- Agrega las columnas que faltaban en la vista de usuarios.
USE gestor_documental;

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

SELECT 'VISTA v_usuarios_completo ACTUALIZADA' AS resultado;
