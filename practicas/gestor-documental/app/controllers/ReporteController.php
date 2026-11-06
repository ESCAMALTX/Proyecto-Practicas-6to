<?php
/**
 * =====================================================================
 * Archivo: app/controllers/ReporteController.php
 * Descripcion: Reportes e indicadores exportables (RF-018, RF-025,
 *              RF-028).
 * =====================================================================
 */

class ReporteController
{
    /**
     * Panel de reportes.
     */
    public function index(): void
    {
        Sesion::exigirPermiso('reportes', 'ver');

        $totales        = Documento::totales();
        $porCategoria   = Documento::porCategoria();
        $porArea        = Documento::porArea();
        $masConsultados = Documento::masConsultados(10);
        $masFrecuentes  = Consulta::masFrecuentes(10);
        $sinResultados  = Consulta::sinResultados(10);
        $descargas      = Consulta::descargasPorDocumento(10);
        $consultasPorDia = Consulta::porDia(14);
        $tiempoPromedio = Consulta::tiempoPromedio();

        // Distribucion por nivel de confidencialidad
        $porNivel = Database::todos(
            "SELECT nivel_confidencialidad, COUNT(*) AS total
               FROM documentos
              WHERE estado <> 'ELIMINADO'
              GROUP BY nivel_confidencialidad
              ORDER BY total DESC"
        );

        // Distribucion por estado
        $porEstado = Database::todos(
            'SELECT estado, COUNT(*) AS total
               FROM documentos
              GROUP BY estado
              ORDER BY total DESC'
        );

        // Usuarios mas activos en consultas
        $usuariosActivos = Database::todos(
            'SELECT u.nombre_completo, r.nombre AS rol, COUNT(c.id_consulta) AS total
               FROM usuarios u
               INNER JOIN roles r ON r.id_rol = u.id_rol
               LEFT JOIN consultas c ON c.id_usuario = u.id_usuario
              GROUP BY u.id_usuario
              HAVING total > 0
              ORDER BY total DESC
              LIMIT 10'
        );

        $tituloPagina = 'Reportes e indicadores';
        $csrfToken    = Sesion::tokenCsrf();

        require RUTA_VISTAS . '/reportes/index.php';
    }

    /**
     * Exporta el inventario documental a CSV (RF-025).
     */
    public function exportarInventario(): void
    {
        Sesion::exigirPermiso('reportes', 'ver');

        $usuario = Sesion::usuario();

        $documentos = Documento::listar([], $usuario, 10000, 0);

        $lineas   = [];
        $lineas[] = 'Codigo;Titulo;Categoria;Area;Caso;Responsable;Autor;'
                  . 'Fecha documento;Confidencialidad;Estado;Version;'
                  . 'Archivo;Peso bytes;Consultas';

        foreach ($documentos as $doc) {
            $lineas[] = implode(';', [
                $doc['codigo'],
                str_replace(';', ',', (string) $doc['titulo']),
                $doc['categoria'],
                (string) $doc['area'],
                (string) $doc['caso'],
                $doc['responsable'],
                str_replace(';', ',', (string) $doc['autor']),
                $doc['fecha_documento'],
                $doc['nivel_confidencialidad'],
                $doc['estado'],
                $doc['version_actual'],
                str_replace(';', ',', (string) $doc['nombre_archivo']),
                (string) $doc['peso_bytes'],
                $doc['total_consultas'],
            ]);
        }

        Auditoria::registrar('reportes', 'EXPORTAR',
            'Exportacion del inventario documental (' . count($documentos) . ' registros).',
            ['entidad' => 'documentos']);

        $this->enviarCsv($lineas, 'inventario_documental_' . date('Ymd_His'));
    }

    /**
     * Exporta el historial de consultas a CSV.
     */
    public function exportarConsultas(): void
    {
        Sesion::exigirPermiso('reportes', 'ver');

        $usuario = Sesion::usuario();
        $filas   = Consulta::historial($usuario, '', 10000, 0);

        $lineas   = [];
        $lineas[] = 'Fecha;Usuario;Consulta;Tipo;Resultados;Encontrado;Tiempo ms;IP';

        foreach ($filas as $fila) {
            $lineas[] = implode(';', [
                $fila['fecha_consulta'],
                $fila['usuario'],
                str_replace(';', ',', (string) $fila['texto_consulta']),
                $fila['tipo_consulta'],
                $fila['total_resultados'],
                $fila['resultado_encontrado'] ? 'SI' : 'NO',
                $fila['tiempo_ms'],
                (string) $fila['direccion_ip'],
            ]);
        }

        Auditoria::registrar('reportes', 'EXPORTAR',
            'Exportacion del historial de consultas (' . count($filas) . ' registros).',
            ['entidad' => 'consultas']);

        $this->enviarCsv($lineas, 'historial_consultas_' . date('Ymd_His'));
    }

    /**
     * Exporta el inventario de usuarios (solo administracion).
     */
    public function exportarUsuarios(): void
    {
        Sesion::exigirNivel(3);

        $usuarios = Usuario::listar('', 0, '', 10000, 0);

        $lineas   = [];
        $lineas[] = 'Nombre completo;Documento;Correo;Usuario;Rol;Area;Cargo;'
                  . 'Estado;Ultimo acceso;Fecha creacion';

        foreach ($usuarios as $u) {
            $lineas[] = implode(';', [
                str_replace(';', ',', $u['nombre_completo']),
                $u['documento'],
                $u['correo'],
                $u['usuario'],
                $u['rol'],
                (string) $u['area'],
                str_replace(';', ',', (string) $u['cargo']),
                $u['estado'],
                (string) $u['ultimo_acceso'],
                $u['fecha_creacion'],
            ]);
        }

        Auditoria::registrar('reportes', 'EXPORTAR',
            'Exportacion del inventario de usuarios (' . count($usuarios) . ' registros).',
            ['entidad' => 'usuarios']);

        $this->enviarCsv($lineas, 'inventario_usuarios_' . date('Ymd_His'));
    }

    /**
     * Vista imprimible de los indicadores generales.
     */
    public function imprimir(): void
    {
        Sesion::exigirPermiso('reportes', 'ver');

        $totales         = Documento::totales();
        $porCategoria    = Documento::porCategoria();
        $porArea         = Documento::porArea();
        $porNivel        = Database::todos(
            "SELECT nivel_confidencialidad, COUNT(*) AS total
               FROM documentos WHERE estado <> 'ELIMINADO'
              GROUP BY nivel_confidencialidad ORDER BY total DESC"
        );
        $masConsultados  = Documento::masConsultados(10);
        $tiempoPromedio  = Consulta::tiempoPromedio();
        $generadoPor     = Sesion::dato('nombre_completo');

        Auditoria::registrar('reportes', 'IMPRIMIR',
            'Generacion del reporte de indicadores para impresion.',
            ['entidad' => 'documentos']);

        require RUTA_VISTAS . '/reportes/imprimir.php';
    }

    /**
     * Envia un arreglo de lineas como archivo CSV descargable.
     */
    private function enviarCsv(array $lineas, string $nombreBase): void
    {
        $contenido = "\xEF\xBB\xBF" . implode("\n", $lineas);

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $nombreBase . '.csv"');
        header('Content-Length: ' . strlen($contenido));
        header('X-Content-Type-Options: nosniff');

        echo $contenido;
        exit;
    }
}
