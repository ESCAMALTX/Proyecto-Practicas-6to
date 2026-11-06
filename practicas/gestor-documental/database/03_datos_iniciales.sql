-- =====================================================================
--  SISTEMA PRIVADO DE GESTION Y CONSULTA DOCUMENTAL
--  Archivo: 03_datos_iniciales.sql
--  Descripcion: Roles, areas, permisos, usuarios, categorias y
--               configuracion inicial del sistema.
--  Ejecutar DESPUES de 01_estructura.sql y 02_indices_triggers_vistas.sql
-- =====================================================================

USE gestor_documental;

-- =====================================================================
-- 1. ROLES
-- =====================================================================
INSERT INTO roles (nombre, descripcion, nivel_acceso) VALUES
('Administrador', 'Control general del sistema: usuarios, permisos, documentos, categorias, auditoria y respaldo.', 3),
('Responsable Documental', 'Administra el contenido: carga, actualiza, clasifica y retira documentos.', 2),
('Asesor', 'Usuario operativo: consulta documentos autorizados y revisa las fuentes encontradas.', 1);


-- =====================================================================
-- 2. AREAS
-- =====================================================================
INSERT INTO areas (nombre, descripcion) VALUES
('Juridica',      'Area legal y de asesoria juridica.'),
('Administrativa','Gestion administrativa y financiera.'),
('Recursos Humanos','Gestion del personal.'),
('Operaciones',   'Procesos operativos y de servicio.'),
('Sistemas',      'Tecnologia de la informacion.');


-- =====================================================================
-- 3. PERMISOS POR ROL
-- =====================================================================
-- Administrador: acceso total a todos los modulos
INSERT INTO permisos_rol (id_rol, modulo, puede_ver, puede_crear, puede_editar, puede_eliminar)
SELECT r.id_rol, m.modulo, 1, 1, 1, 1
  FROM roles r
  CROSS JOIN (SELECT 'usuarios' AS modulo UNION ALL
              SELECT 'documentos' UNION ALL
              SELECT 'categorias' UNION ALL
              SELECT 'casos' UNION ALL
              SELECT 'auditoria' UNION ALL
              SELECT 'respaldo' UNION ALL
              SELECT 'configuracion' UNION ALL
              SELECT 'reportes') m
 WHERE r.nombre = 'Administrador';

-- Responsable Documental: gestion documental sin usuarios ni respaldo
INSERT INTO permisos_rol (id_rol, modulo, puede_ver, puede_crear, puede_editar, puede_eliminar)
SELECT r.id_rol, m.modulo, m.ver, m.crear, m.editar, m.eliminar
  FROM roles r
  CROSS JOIN (SELECT 'usuarios'     AS modulo, 0 AS ver, 0 AS crear, 0 AS editar, 0 AS eliminar UNION ALL
              SELECT 'documentos',   1, 1, 1, 1 UNION ALL
              SELECT 'categorias',   1, 1, 1, 0 UNION ALL
              SELECT 'casos',        1, 1, 1, 0 UNION ALL
              SELECT 'auditoria',    1, 0, 0, 0 UNION ALL
              SELECT 'respaldo',     0, 0, 0, 0 UNION ALL
              SELECT 'configuracion',0, 0, 0, 0 UNION ALL
              SELECT 'reportes',     1, 0, 0, 0) m
 WHERE r.nombre = 'Responsable Documental';

-- Asesor: solo consulta
INSERT INTO permisos_rol (id_rol, modulo, puede_ver, puede_crear, puede_editar, puede_eliminar)
SELECT r.id_rol, m.modulo, m.ver, 0, 0, 0
  FROM roles r
  CROSS JOIN (SELECT 'usuarios'     AS modulo, 0 AS ver UNION ALL
              SELECT 'documentos',   1 UNION ALL
              SELECT 'categorias',   1 UNION ALL
              SELECT 'casos',        1 UNION ALL
              SELECT 'auditoria',    0 UNION ALL
              SELECT 'respaldo',     0 UNION ALL
              SELECT 'configuracion',0 UNION ALL
              SELECT 'reportes',     0) m
 WHERE r.nombre = 'Asesor';


-- =====================================================================
-- 4. USUARIOS INICIALES
-- =====================================================================
-- Contrasenas de demostracion (deben cambiarse en produccion):
--   admin    / Admin123*     -> Administrador
--   abogado  / Abogado123*   -> Responsable Documental
--   asesor   / Asesor123*    -> Asesor
-- El hash fue generado con password_hash() de PHP / bcrypt (coste 10).
-- =====================================================================
INSERT INTO usuarios
    (nombre_completo, documento, correo, usuario, clave_hash, id_rol, id_area, telefono, cargo, estado, debe_cambiar_clave)
VALUES
('Administrador del Sistema',
 '1000000001',
 'admin@institucion.local',
 'admin',
 '$2b$10$i4K1O5CXzlA2pDebshz0vOg..vXZK27QI2ofZ6NILOv8q1jdsNOPi',
 (SELECT id_rol FROM roles WHERE nombre = 'Administrador'),
 (SELECT id_area FROM areas WHERE nombre = 'Sistemas'),
 '3000000000',
 'Jefe de Sistemas',
 'ACTIVO', 1),

('Ana Maria Rodriguez',
 '1000000002',
 'ana.rodriguez@institucion.local',
 'abogado',
 '$2b$10$F6kLQ2/DnPV8ADeBsTVw7uMZD39.XOHxXfem9iQsFwnjk.slloUwq',
 (SELECT id_rol FROM roles WHERE nombre = 'Responsable Documental'),
 (SELECT id_area FROM areas WHERE nombre = 'Juridica'),
 '3000000001',
 'Abogada - Responsable Documental',
 'ACTIVO', 1),

('Carlos Andres Perez',
 '1000000003',
 'carlos.perez@institucion.local',
 'asesor',
 '$2b$10$5NP/FhuwTpUPWcvcftT18ub.P2TsxZmkOzQyypas3csO91P4L.b/m',
 (SELECT id_rol FROM roles WHERE nombre = 'Asesor'),
 (SELECT id_area FROM areas WHERE nombre = 'Administrativa'),
 '3000000002',
 'Asesor Administrativo',
 'ACTIVO', 1);


-- =====================================================================
-- 5. CATEGORIAS
-- =====================================================================
INSERT INTO categorias (nombre, descripcion) VALUES
('Contratos',        'Contratos, convenios y acuerdos institucionales.'),
('Normatividad',     'Leyes, decretos, resoluciones y circulares.'),
('Actas',            'Actas de reunion, comite y consejo.'),
('Conceptos',        'Conceptos juridicos y respuestas a consultas.'),
('Informes',         'Informes de gestion, tecnico y financiero.'),
('Politicas',        'Politicas internas y procedimientos.'),
('Correspondencia',  'Comunicaciones oficiales recibidas y enviadas.'),
('Certificados',     'Certificaciones y constancias expedidas.');


-- =====================================================================
-- 6. CASOS DE EJEMPLO
-- =====================================================================
INSERT INTO casos (codigo, titulo, descripcion, id_area, estado, fecha_apertura) VALUES
('CASO-2026-001', 'Proceso de contratacion de servicios de aseo',
 'Proceso de seleccion y contratacion de servicios generales.',
 (SELECT id_area FROM areas WHERE nombre = 'Administrativa'), 'EN_PROCESO', '2026-01-15'),

('CASO-2026-002', 'Solicitud de concepto juridico sobre contratacion directa',
 'Analisis de viabilidad de contratacion directa segun normatividad vigente.',
 (SELECT id_area FROM areas WHERE nombre = 'Juridica'), 'ABIERTO', '2026-02-03'),

('CASO-2026-003', 'Actualizacion del manual de funciones',
 'Revision y actualizacion del manual de funciones por area.',
 (SELECT id_area FROM areas WHERE nombre = 'Recursos Humanos'), 'ABIERTO', '2026-03-10');


-- =====================================================================
-- 7. ETIQUETAS
-- =====================================================================
INSERT INTO etiquetas (nombre) VALUES
('urgente'), ('vigente'), ('historico'), ('confidencial'), ('revision-2026'),
('contratacion'), ('laboral'), ('presupuesto'), ('auditoria'), ('comite');


-- =====================================================================
-- 8. CONFIGURACION DEL SISTEMA
-- =====================================================================
INSERT INTO configuracion (clave, valor, descripcion) VALUES
('nombre_sistema',        'Sistema Privado de Gestion y Consulta Documental', 'Nombre que se muestra en la interfaz'),
('version_sistema',       '1.0.0',            'Version actual de la aplicacion'),
('items_por_pagina',      '10',               'Cantidad de registros por pagina en los listados'),
('max_intentos_login',    '5',                'Intentos fallidos antes de bloquear la cuenta'),
('minutos_inactividad',   '30',               'Minutos de inactividad antes de cerrar la sesion'),
('peso_maximo_archivo_mb','20',               'Peso maximo permitido por archivo en MB'),
('extensiones_permitidas','pdf,docx,doc,xlsx,xls,txt', 'Formatos de archivo aceptados'),
('dias_retencion_logs',   '365',              'Dias de conservacion de la bitacora de auditoria'),
('respaldo_automatico',   'SI',               'Indica si el respaldo automatico esta habilitado'),
('correo_soporte',        'soporte@institucion.local', 'Correo de contacto para soporte');


-- =====================================================================
-- 9. DOCUMENTOS DE DEMOSTRACION
-- =====================================================================
INSERT INTO documentos
    (codigo, titulo, descripcion, id_categoria, id_caso, id_area, id_responsable,
     autor, fecha_documento, nivel_confidencialidad, palabra_clave, estado)
VALUES
('DOC-2026-0001',
 'Contrato de prestacion de servicios de aseo 2026',
 'Contrato suscrito para la prestacion de servicios generales de aseo y cafeteria.',
 (SELECT id_categoria FROM categorias WHERE nombre = 'Contratos'),
 (SELECT id_caso FROM casos WHERE codigo = 'CASO-2026-001'),
 (SELECT id_area FROM areas WHERE nombre = 'Administrativa'),
 (SELECT id_usuario FROM usuarios WHERE usuario = 'abogado'),
 'Representante Legal',
 '2026-01-20', 'INTERNO', 'contrato,aseo,servicios,2026', 'PUBLICADO'),

('DOC-2026-0002',
 'Concepto juridico sobre contratacion directa',
 'Analisis de las causales de contratacion directa y recomendaciones.',
 (SELECT id_categoria FROM categorias WHERE nombre = 'Conceptos'),
 (SELECT id_caso FROM casos WHERE codigo = 'CASO-2026-002'),
 (SELECT id_area FROM areas WHERE nombre = 'Juridica'),
 (SELECT id_usuario FROM usuarios WHERE usuario = 'abogado'),
 'Oficina Juridica',
 '2026-02-10', 'RESERVADO', 'concepto,contratacion,directa,juridico', 'PUBLICADO'),

('DOC-2026-0003',
 'Acta de comite de contratacion No. 03',
 'Acta de la sesion ordinaria del comite de contratacion.',
 (SELECT id_categoria FROM categorias WHERE nombre = 'Actas'),
 (SELECT id_caso FROM casos WHERE codigo = 'CASO-2026-001'),
 (SELECT id_area FROM areas WHERE nombre = 'Administrativa'),
 (SELECT id_usuario FROM usuarios WHERE usuario = 'abogado'),
 'Secretaria del Comite',
 '2026-03-05', 'INTERNO', 'acta,comite,contratacion', 'PUBLICADO'),

('DOC-2026-0004',
 'Politica de tratamiento de datos personales',
 'Politica interna para el tratamiento y proteccion de datos personales.',
 (SELECT id_categoria FROM categorias WHERE nombre = 'Politicas'),
 NULL,
 (SELECT id_area FROM areas WHERE nombre = 'Juridica'),
 (SELECT id_usuario FROM usuarios WHERE usuario = 'admin'),
 'Direccion Juridica',
 '2026-01-05', 'PUBLICO', 'politica,datos,personales,privacidad', 'PUBLICADO'),

('DOC-2026-0005',
 'Informe de gestion primer trimestre 2026',
 'Informe consolidado de la gestion administrativa del primer trimestre.',
 (SELECT id_categoria FROM categorias WHERE nombre = 'Informes'),
 NULL,
 (SELECT id_area FROM areas WHERE nombre = 'Administrativa'),
 (SELECT id_usuario FROM usuarios WHERE usuario = 'admin'),
 'Direccion Administrativa',
 '2026-04-05', 'INTERNO', 'informe,gestion,trimestre,2026', 'BORRADOR'),

('DOC-2026-0006',
 'Manual de funciones actualizado',
 'Manual de funciones y competencias laborales por cargo.',
 (SELECT id_categoria FROM categorias WHERE nombre = 'Normatividad'),
 (SELECT id_caso FROM casos WHERE codigo = 'CASO-2026-003'),
 (SELECT id_area FROM areas WHERE nombre = 'Recursos Humanos'),
 (SELECT id_usuario FROM usuarios WHERE usuario = 'abogado'),
 'Talento Humano',
 '2026-03-15', 'INTERNO', 'manual,funciones,cargos,competencias', 'PUBLICADO');


-- =====================================================================
-- 10. ETIQUETAS APLICADAS A LOS DOCUMENTOS
-- =====================================================================
INSERT INTO documento_etiquetas (id_documento, id_etiqueta)
SELECT d.id_documento, e.id_etiqueta
  FROM documentos d
  CROSS JOIN etiquetas e
 WHERE (d.codigo = 'DOC-2026-0001' AND e.nombre IN ('contratacion','vigente'))
    OR (d.codigo = 'DOC-2026-0002' AND e.nombre IN ('confidencial','urgente'))
    OR (d.codigo = 'DOC-2026-0003' AND e.nombre IN ('comite','vigente'))
    OR (d.codigo = 'DOC-2026-0005' AND e.nombre IN ('revision-2026'))
    OR (d.codigo = 'DOC-2026-0006' AND e.nombre IN ('laboral','vigente'));


-- =====================================================================
-- 11. PERMISOS ESPECIALES SOBRE DOCUMENTOS RESERVADOS
-- =====================================================================
-- El concepto juridico reservado solo lo puede consultar el area Juridica.
INSERT INTO permisos_documento (id_documento, id_area, puede_consultar, puede_descargar)
SELECT d.id_documento,
       (SELECT id_area FROM areas WHERE nombre = 'Juridica'),
       1, 1
  FROM documentos d
 WHERE d.codigo = 'DOC-2026-0002';


-- =====================================================================
-- 12. CONSULTAS DE DEMOSTRACION
-- =====================================================================
INSERT INTO consultas (id_usuario, texto_consulta, tipo_consulta, filtros_aplicados, total_resultados, resultado_encontrado, tiempo_ms, direccion_ip)
VALUES
((SELECT id_usuario FROM usuarios WHERE usuario = 'asesor'),
 'contrato aseo', 'RAPIDA', NULL, 1, 1, 45, '127.0.0.1'),
((SELECT id_usuario FROM usuarios WHERE usuario = 'asesor'),
 'politica datos personales', 'AVANZADA',
 '{"categoria":"Politicas"}', 1, 1, 62, '127.0.0.1'),
((SELECT id_usuario FROM usuarios WHERE usuario = 'abogado'),
 'acta comite contratacion', 'FILTRO',
 '{"area":"Administrativa","estado":"PUBLICADO"}', 1, 1, 38, '127.0.0.1');


-- =====================================================================
-- 13. AUDITORIA DE DEMOSTRACION
-- =====================================================================
INSERT INTO auditoria (id_usuario, usuario_texto, modulo, accion, entidad, id_entidad, descripcion, direccion_ip, resultado)
VALUES
((SELECT id_usuario FROM usuarios WHERE usuario = 'admin'),
 'admin', 'autenticacion', 'LOGIN', 'usuarios', 1,
 'Inicio de sesion exitoso.', '127.0.0.1', 'EXITO'),
((SELECT id_usuario FROM usuarios WHERE usuario = 'abogado'),
 'abogado', 'documentos', 'CREAR', 'documentos', 1,
 'Carga del documento DOC-2026-0001.', '127.0.0.1', 'EXITO'),
((SELECT id_usuario FROM usuarios WHERE usuario = 'abogado'),
 'abogado', 'documentos', 'ACTUALIZAR', 'documentos', 1,
 'Cambio de estado del documento DOC-2026-0001 a PUBLICADO.', '127.0.0.1', 'EXITO');


-- =====================================================================
-- VERIFICACION
-- =====================================================================
SELECT 'roles'                AS tabla, COUNT(*) AS registros FROM roles
UNION ALL SELECT 'areas',                COUNT(*) FROM areas
UNION ALL SELECT 'permisos_rol',         COUNT(*) FROM permisos_rol
UNION ALL SELECT 'usuarios',             COUNT(*) FROM usuarios
UNION ALL SELECT 'categorias',           COUNT(*) FROM categorias
UNION ALL SELECT 'casos',                COUNT(*) FROM casos
UNION ALL SELECT 'etiquetas',            COUNT(*) FROM etiquetas
UNION ALL SELECT 'documentos',           COUNT(*) FROM documentos
UNION ALL SELECT 'configuracion',        COUNT(*) FROM configuracion
UNION ALL SELECT 'consultas',            COUNT(*) FROM consultas
UNION ALL SELECT 'auditoria',            COUNT(*) FROM auditoria;
