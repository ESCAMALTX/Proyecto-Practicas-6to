<?php
/**
 * =====================================================================
 * Archivo: app/core/Helper.php
 * Descripcion: Funciones de apoyo reutilizables en toda la aplicacion
 *              (escapado, validacion, formato, paginacion).
 * =====================================================================
 */

class Helper
{
    /**
     * Escapa texto para mostrarlo en HTML y prevenir XSS.
     */
    public static function e($valor): string
    {
        return htmlspecialchars((string) $valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Escapa y devuelve un guion si el valor esta vacio.
     */
    public static function eo($valor, string $alterno = '—'): string
    {
        $texto = trim((string) $valor);
        return $texto === '' ? $alterno : self::e($texto);
    }

    /**
     * Obtiene un valor de $_POST con limpieza basica.
     */
    public static function post(string $campo, $porDefecto = ''): string
    {
        $valor = $_POST[$campo] ?? $porDefecto;
        return is_string($valor) ? trim($valor) : $porDefecto;
    }

    /**
     * Obtiene un valor de $_GET con limpieza basica.
     */
    public static function get(string $campo, $porDefecto = ''): string
    {
        $valor = $_GET[$campo] ?? $porDefecto;
        return is_string($valor) ? trim($valor) : $porDefecto;
    }

    /**
     * Obtiene un valor entero de $_GET o $_POST.
     */
    public static function entero(string $campo, int $porDefecto = 0, string $origen = 'get'): int
    {
        $valor = $origen === 'post' ? ($_POST[$campo] ?? null) : ($_GET[$campo] ?? null);
        return $valor === null || $valor === '' ? $porDefecto : (int) $valor;
    }

    /**
     * Traduce el nombre del modulo y la accion a etiquetas legibles.
     */
    public static function tituloModulo(string $modulo): string
    {
        $titulos = [
            'panel'       => 'Panel de control',
            'documentos'  => 'Gestion documental',
            'categorias'  => 'Categorias',
            'casos'       => 'Casos',
            'usuarios'    => 'Usuarios',
            'roles'       => 'Roles y permisos',
            'consultas'   => 'Consultas',
            'auditoria'   => 'Auditoria',
            'respaldo'    => 'Respaldos',
            'reportes'    => 'Reportes',
            'perfil'      => 'Mi perfil',
        ];

        return $titulos[$modulo] ?? ucfirst($modulo);
    }

    /**
     * Formatea un peso en bytes a una unidad legible.
     */
    public static function pesoLegible(int $bytes): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }

        $unidades = ['B', 'KB', 'MB', 'GB', 'TB'];
        $indice   = (int) floor(log($bytes, 1024));
        $indice   = min($indice, count($unidades) - 1);

        return number_format($bytes / pow(1024, $indice), 2) . ' ' . $unidades[$indice];
    }

    /**
     * Formatea una fecha para mostrarla al usuario.
     */
    public static function fecha(?string $fecha, string $formato = 'd/m/Y'): string
    {
        if (empty($fecha) || $fecha === '0000-00-00') {
            return '—';
        }

        $marca = strtotime($fecha);
        return $marca === false ? '—' : date($formato, $marca);
    }

    /**
     * Formatea fecha y hora.
     */
    public static function fechaHora(?string $fecha): string
    {
        return self::fecha($fecha, 'd/m/Y H:i');
    }

    /**
     * Devuelve un texto tipo "hace 5 minutos".
     */
    public static function hace(?string $fecha): string
    {
        if (empty($fecha)) {
            return '—';
        }

        $segundos = time() - (int) strtotime($fecha);

        if ($segundos < 60)      return 'hace unos segundos';
        if ($segundos < 3600)    return 'hace ' . floor($segundos / 60) . ' min';
        if ($segundos < 86400)   return 'hace ' . floor($segundos / 3600) . ' h';
        if ($segundos < 2592000) return 'hace ' . floor($segundos / 86400) . ' dias';

        return self::fecha($fecha);
    }

    /**
     * Genera un codigo consecutivo con el formato PREFIJO-AAAA-NNNN.
     */
    public static function generarCodigo(string $tabla, string $columna, string $prefijo): string
    {
        $anio = date('Y');

        $ultimo = Database::valor(
            "SELECT {$columna} FROM {$tabla}
              WHERE {$columna} LIKE ?
              ORDER BY {$columna} DESC LIMIT 1",
            ["{$prefijo}-{$anio}-%"]
        );

        $secuencia = 1;
        if ($ultimo) {
            $partes    = explode('-', $ultimo);
            $secuencia = ((int) end($partes)) + 1;
        }

        return sprintf('%s-%s-%04d', $prefijo, $anio, $secuencia);
    }

    /**
     * URL directa a un modulo y accion.
     */
    public static function url(string $modulo, string $accion = '', array $parametros = []): string
    {
        $query = array_merge(['modulo' => $modulo], $accion ? ['accion' => $accion] : [], $parametros);
        return 'index.php?' . http_build_query($query);
    }

    /**
     * Devuelve la clase CSS del badge segun el estado del documento.
     */
    public static function claseEstado(string $estado): string
    {
        return match (strtoupper($estado)) {
            'PUBLICADO', 'ACTIVO', 'COMPLETADO', 'EXITO', 'DISPONIBLE' => 'badge badge--exito',
            'BORRADOR', 'INACTIVO', 'INFO'                            => 'badge badge--neutro',
            'EN_PROCESO', 'ABIERTO', 'ADVERTENCIA'                    => 'badge badge--aviso',
            'ARCHIVADO', 'CERRADO'                                    => 'badge badge--archivado',
            'ELIMINADO', 'BLOQUEADO', 'FALLIDO', 'ERROR', 'RECHAZADO' => 'badge badge--error',
            default                                                   => 'badge badge--neutro',
        };
    }

    /**
     * Devuelve la clase CSS del badge segun el nivel de confidencialidad.
     */
    public static function claseConfidencialidad(string $nivel): string
    {
        return match (strtoupper($nivel)) {
            'PUBLICO'     => 'badge badge--exito',
            'INTERNO'     => 'badge badge--neutro',
            'RESERVADO'   => 'badge badge--aviso',
            'CONFIDENCIAL'=> 'badge badge--error',
            default       => 'badge badge--neutro',
        };
    }

    /**
     * Devuelve el icono de texto para cada extension de archivo.
     */
    public static function iconoArchivo(string $extension): string
    {
        return match (strtolower($extension)) {
            'pdf'         => 'PDF',
            'doc', 'docx' => 'DOC',
            'xls', 'xlsx' => 'XLS',
            'txt'         => 'TXT',
            default       => 'FILE',
        };
    }

    /**
     * Genera los datos de paginacion.
     */
    public static function paginar(int $totalRegistros, int $paginaActual, int $porPagina = ITEMS_POR_PAGINA): array
    {
        $totalPaginas = max(1, (int) ceil($totalRegistros / $porPagina));
        $paginaActual = max(1, min($paginaActual, $totalPaginas));

        return [
            'total'          => $totalRegistros,
            'por_pagina'     => $porPagina,
            'pagina_actual'  => $paginaActual,
            'total_paginas'  => $totalPaginas,
            'offset'         => ($paginaActual - 1) * $porPagina,
            'desde'          => $totalRegistros === 0 ? 0 : (($paginaActual - 1) * $porPagina) + 1,
            'hasta'          => min($paginaActual * $porPagina, $totalRegistros),
        ];
    }

    /**
     * Crea el extracto donde aparece el termino buscado.
     */
    public static function extracto(?string $texto, string $termino, int $largo = 220): string
    {
        if (empty($texto)) {
            return '';
        }

        $texto = preg_replace('/\s+/', ' ', $texto);
        $pos   = mb_stripos($texto, $termino);

        if ($pos === false) {
            return mb_substr($texto, 0, $largo) . (mb_strlen($texto) > $largo ? '...' : '');
        }

        $inicio  = max(0, $pos - 80);
        $recorte = mb_substr($texto, $inicio, $largo);

        return ($inicio > 0 ? '...' : '') . $recorte . (mb_strlen($texto) > $inicio + $largo ? '...' : '');
    }

    /**
     * Resalta el termino buscado dentro de un texto ya escapado.
     */
    public static function resaltar(string $texto, string $termino): string
    {
        if (trim($termino) === '') {
            return $texto;
        }

        $seguro = self::e($texto);
        return preg_replace(
            '/(' . preg_quote(self::e($termino), '/') . ')/iu',
            '<mark>$1</mark>',
            $seguro
        );
    }

    /**
     * Devuelve la direccion IP del cliente.
     */
    public static function ip(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    /**
     * Devuelve el navegador del cliente.
     */
    public static function agente(): string
    {
        return mb_substr($_SERVER['HTTP_USER_AGENT'] ?? 'Desconocido', 0, 255);
    }

    /**
     * Valida el formato de un correo electronico.
     */
    public static function correoValido(string $correo): bool
    {
        return filter_var($correo, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Valida la fortaleza de la contrasena: minimo 8 caracteres,
     * una mayuscula, una minuscula, un numero y un simbolo.
     */
    public static function claveFuerte(string $clave): bool
    {
        return strlen($clave) >= 8
            && preg_match('/[A-Z]/', $clave) === 1
            && preg_match('/[a-z]/', $clave) === 1
            && preg_match('/[0-9]/', $clave) === 1
            && preg_match('/[^A-Za-z0-9]/', $clave) === 1;
    }

    /**
     * Devuelve true si la peticion es POST.
     */
    public static function esPost(): bool
    {
        return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
    }

    /**
     * Redirige a una URL terminando la ejecucion.
     */
    public static function redirigir(string $url): void
    {
        header("Location: {$url}");
        exit;
    }

    /**
     * Limpia un nombre de archivo dejando solo caracteres seguros.
     */
    public static function limpiarNombreArchivo(string $nombre): string
    {
        $nombre = preg_replace('/[^\w\.\- ]/u', '', $nombre);
        $nombre = preg_replace('/\s+/', '_', trim($nombre));
        return $nombre === '' ? 'archivo' : $nombre;
    }

    /**
     * Convierte un arreglo a JSON seguro para auditoria.
     */
    public static function aJson($datos): string
    {
        return json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}';
    }
}
