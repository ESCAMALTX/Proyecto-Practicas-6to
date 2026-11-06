<?php
/**
 * =====================================================================
 * Archivo: app/core/GestorArchivos.php
 * Descripcion: Validacion, almacenamiento y extraccion de texto de
 *              los archivos cargados (RF-005, RF-007, RF-029).
 *
 *  Validaciones aplicadas:
 *   - Error de subida de PHP
 *   - Peso maximo permitido
 *   - Extension dentro de la lista blanca
 *   - Tipo MIME real detectado con finfo (no confia en el navegador)
 *   - Verificacion de que el archivo es realmente subido por HTTP
 *   - Nombre generado aleatoriamente para evitar sobrescrituras y
 *     ataques de traversal
 *   - Hash SHA-256 para comprobar integridad (RNF-009)
 * =====================================================================
 */

class GestorArchivos
{
    /**
     * Procesa la subida de un archivo y devuelve sus metadatos.
     *
     * @return array{ok:bool, error?:string, datos?:array}
     */
    public static function subir(array $archivo, string $subcarpeta = 'documentos'): array
    {
        // 1. Verificar errores de subida de PHP
        if (!isset($archivo['error']) || is_array($archivo['error'])) {
            return ['ok' => false, 'error' => 'Solicitud de archivo no valida.'];
        }

        if ($archivo['error'] !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'error' => self::mensajeErrorSubida((int) $archivo['error'])];
        }

        // 2. Verificar que el archivo proviene realmente de una subida HTTP
        if (!is_uploaded_file($archivo['tmp_name'])) {
            return ['ok' => false, 'error' => 'El archivo no fue cargado correctamente.'];
        }

        // 3. Verificar peso
        if ($archivo['size'] <= 0) {
            return ['ok' => false, 'error' => 'El archivo esta vacio.'];
        }

        if ($archivo['size'] > PESO_MAXIMO_BYTES) {
            return ['ok' => false,
                    'error' => 'El archivo supera el peso maximo permitido de ' . PESO_MAXIMO_MB . ' MB.'];
        }

        // 4. Verificar extension
        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        if (!array_key_exists($extension, EXTENSIONES_PERMITIDAS)) {
            return ['ok' => false,
                    'error' => 'Formato no permitido. Se aceptan: '
                             . strtoupper(implode(', ', array_keys(EXTENSIONES_PERMITIDAS))) . '.'];
        }

        // 5. Verificar tipo MIME real con finfo
        $finfo    = new finfo(FILEINFO_MIME_TYPE);
        $mimeReal = $finfo->file($archivo['tmp_name']) ?: 'application/octet-stream';

        if (!in_array($mimeReal, EXTENSIONES_PERMITIDAS[$extension], true)) {
            // Caso especial: los .docx y .xlsx son contenedores ZIP
            $esZipValido = in_array($mimeReal, ['application/zip'], true)
                        && in_array($extension, ['docx', 'xlsx'], true);

            if (!$esZipValido) {
                return ['ok' => false,
                        'error' => "El contenido del archivo no corresponde a su extension ({$extension}). "
                                 . 'Verifique que el archivo no este danado.'];
            }
        }

        // 6. Preparar la carpeta destino
        $destino = RUTA_UPLOADS . DIRECTORY_SEPARATOR . $subcarpeta
                 . DIRECTORY_SEPARATOR . date('Y') . DIRECTORY_SEPARATOR . date('m');

        if (!is_dir($destino) && !@mkdir($destino, 0775, true)) {
            return ['ok' => false, 'error' => 'No fue posible crear la carpeta de almacenamiento.'];
        }

        // 7. Nombre unico y seguro para almacenar
        $nombreAlmacenado = date('Ymd_His') . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
        $rutaCompleta     = $destino . DIRECTORY_SEPARATOR . $nombreAlmacenado;

        if (!move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
            return ['ok' => false, 'error' => 'No fue posible guardar el archivo en el servidor.'];
        }

        // Permisos restrictivos: solo lectura para el servidor web
        @chmod($rutaCompleta, 0640);

        // 8. Hash de integridad
        $hash = hash_file('sha256', $rutaCompleta);

        // 9. Ruta relativa que se guarda en la base de datos
        $rutaRelativa = str_replace(
            [RUTA_BASE . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR],
            ['', '/'],
            $rutaCompleta
        );

        return [
            'ok'    => true,
            'datos' => [
                'nombre_original'   => $archivo['name'],
                'nombre_almacenado' => $nombreAlmacenado,
                'ruta_archivo'      => $rutaRelativa,
                'ruta_absoluta'     => $rutaCompleta,
                'extension'         => $extension,
                'tipo_mime'         => $mimeReal,
                'peso_bytes'        => (int) $archivo['size'],
                'hash_archivo'      => $hash,
            ],
        ];
    }

    /**
     * Elimina un archivo fisico del servidor.
     */
    public static function eliminar(string $rutaRelativa): bool
    {
        $rutaAbsoluta = RUTA_BASE . DIRECTORY_SEPARATOR
                      . str_replace('/', DIRECTORY_SEPARATOR, $rutaRelativa);

        // Confirmar que la ruta esta dentro de la carpeta de uploads
        $rutaReal     = realpath($rutaAbsoluta);
        $carpetaReal  = realpath(RUTA_UPLOADS);

        if ($rutaReal === false || $carpetaReal === false) {
            return false;
        }

        if (!str_starts_with($rutaReal, $carpetaReal)) {
            return false; // Intento de traversal: se rechaza
        }

        return @unlink($rutaReal);
    }

    /**
     * Extrae el texto de un archivo para alimentar la busqueda por
     * palabras (RF-007, RF-008). No usa servicios externos.
     */
    public static function extraerTexto(string $rutaAbsoluta, string $extension): ?string
    {
        return match (strtolower($extension)) {
            'txt'         => self::extraerTxt($rutaAbsoluta),
            'docx'        => self::extraerDocx($rutaAbsoluta),
            'xlsx'        => self::extraerXlsx($rutaAbsoluta),
            'pdf'         => self::extraerPdf($rutaAbsoluta),
            default       => null, // .doc y .xls antiguos requieren conversion externa
        };
    }

    // -----------------------------------------------------------------
    // Extractores
    // -----------------------------------------------------------------

    private static function extraerTxt(string $ruta): ?string
    {
        $contenido = @file_get_contents($ruta);
        if ($contenido === false) {
            return null;
        }

        // Detectar codificacion y convertir a UTF-8
        if (!mb_check_encoding($contenido, 'UTF-8')) {
            $contenido = mb_convert_encoding($contenido, 'UTF-8', 'ISO-8859-1');
        }

        return self::normalizar($contenido);
    }

    /**
     * Un archivo .docx es un ZIP cuyo texto vive en word/document.xml.
     */
    private static function extraerDocx(string $ruta): ?string
    {
        if (!class_exists('ZipArchive')) {
            return null;
        }

        $zip = new ZipArchive();
        if ($zip->open($ruta) !== true) {
            return null;
        }

        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        if ($xml === false) {
            return null;
        }

        // Convertir saltos de parrafo en saltos de linea antes de limpiar
        $xml  = preg_replace('/<\/w:p>/', "\n", $xml);
        $texto = strip_tags($xml);
        $texto = html_entity_decode($texto, ENT_QUOTES | ENT_XML1, 'UTF-8');

        return self::normalizar($texto);
    }

    /**
     * Un archivo .xlsx guarda el texto en xl/sharedStrings.xml.
     */
    private static function extraerXlsx(string $ruta): ?string
    {
        if (!class_exists('ZipArchive')) {
            return null;
        }

        $zip = new ZipArchive();
        if ($zip->open($ruta) !== true) {
            return null;
        }

        $partes = [];

        if (($compartidos = $zip->getFromName('xl/sharedStrings.xml')) !== false) {
            $xml = preg_replace('/<\/si>/', "\n", $compartidos);
            $partes[] = html_entity_decode(strip_tags($xml), ENT_QUOTES | ENT_XML1, 'UTF-8');
        }

        // Tambien se leen los valores en linea de cada hoja
        for ($i = 1; $i <= 10; $i++) {
            $hoja = $zip->getFromName("xl/worksheets/sheet{$i}.xml");
            if ($hoja === false) {
                break;
            }
            foreach (['<v>', '<t>'] as $etiqueta) {
                if (preg_match_all('/' . preg_quote($etiqueta, '/') . '(.*?)' .
                        preg_quote(str_replace('<', '</', $etiqueta), '/') . '/s',
                        $hoja, $coincidencias)) {
                    $partes[] = implode(' ', $coincidencias[1]);
                }
            }
        }

        $zip->close();

        return empty($partes) ? null : self::normalizar(implode("\n", $partes));
    }

    /**
     * Extraccion basica de texto de un PDF no cifrado.
     * Lee los flujos de contenido y recupera el texto entre BT y ET.
     */
    private static function extraerPdf(string $ruta): ?string
    {
        $contenido = @file_get_contents($ruta);
        if ($contenido === false) {
            return null;
        }

        // PDF protegido con contrasena: no se puede extraer
        if (str_contains($contenido, '/Encrypt')) {
            return null;
        }

        $texto = '';

        // Recupera el texto de los operadores Tj, TJ y ' dentro de BT..ET
        if (preg_match_all('/\((?:\\\\.|[^\\\\()])*\)/s', $contenido, $coincidencias)) {
            foreach ($coincidencias[0] as $cadena) {
                $limpio = substr($cadena, 1, -1);
                $limpio = preg_replace('/\\\\([()\\\\])/', '$1', $limpio);
                $limpio = preg_replace('/\\\\[0-7]{1,3}/', ' ', $limpio);
                $texto .= $limpio . ' ';
            }
        }

        // Texto en hexadecimal <...>
        if (preg_match_all('/<([0-9A-Fa-f\s]+)>/s', $contenido, $hex)) {
            foreach ($hex[1] as $bloque) {
                $binario = @hex2bin(preg_replace('/\s+/', '', $bloque));
                if ($binario !== false) {
                    $texto .= preg_replace('/[^\P{C}\n]+/u', '', $binario) . ' ';
                }
            }
        }

        $texto = trim($texto);

        return $texto === '' ? null : self::normalizar($texto);
    }

    /**
     * Limpia el texto extraido: normaliza espacios y recorta el largo.
     */
    private static function normalizar(string $texto): string
    {
        $texto = str_replace(["\r\n", "\r", "\t"], ["\n", "\n", ' '], $texto);
        $texto = preg_replace('/[ ]{2,}/', ' ', $texto);
        $texto = preg_replace('/\n{3,}/', "\n\n", $texto);
        $texto = preg_replace('/[^\P{C}\n]+/u', '', $texto);

        // LONGTEXT admite mas, pero se recorta para no degradar el rendimiento
        return mb_substr(trim($texto), 0, 400000);
    }

    /**
     * Traduce el codigo de error de subida de PHP.
     */
    private static function mensajeErrorSubida(int $codigo): string
    {
        return match ($codigo) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE =>
                'El archivo supera el peso maximo permitido de ' . PESO_MAXIMO_MB . ' MB.',
            UPLOAD_ERR_PARTIAL   => 'El archivo se subio de forma incompleta. Intente nuevamente.',
            UPLOAD_ERR_NO_FILE   => 'No se selecciono ningun archivo.',
            UPLOAD_ERR_NO_TMP_DIR=> 'El servidor no tiene carpeta temporal configurada.',
            UPLOAD_ERR_CANT_WRITE=> 'El servidor no pudo escribir el archivo en disco.',
            UPLOAD_ERR_EXTENSION => 'Una extension de PHP detuvo la subida del archivo.',
            default              => 'Ocurrio un error desconocido durante la subida del archivo.',
        };
    }
}
