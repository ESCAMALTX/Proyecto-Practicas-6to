<?php
/**
 * =====================================================================
 * Archivo: app/core/Validador.php
 * Descripcion: Validacion de datos de entrada de formularios.
 * =====================================================================
 */

class Validador
{
    private array $datos;
    private array $errores = [];
    private array $etiquetas;

    public function __construct(array $datos, array $etiquetas = [])
    {
        $this->datos     = $datos;
        $this->etiquetas = $etiquetas;
    }

    /**
     * Devuelve el nombre legible de un campo.
     */
    private function etiqueta(string $campo): string
    {
        return $this->etiquetas[$campo] ?? ucfirst(str_replace('_', ' ', $campo));
    }

    private function valor(string $campo)
    {
        $valor = $this->datos[$campo] ?? '';
        return is_string($valor) ? trim($valor) : $valor;
    }

    // -----------------------------------------------------------------
    // Reglas
    // -----------------------------------------------------------------

    public function requerido(string $campo, string $mensaje = ''): self
    {
        if ($this->valor($campo) === '' || $this->valor($campo) === null) {
            $this->errores[$campo] = $mensaje ?: "El campo {$this->etiqueta($campo)} es obligatorio.";
        }
        return $this;
    }

    public function largoMinimo(string $campo, int $minimo, string $mensaje = ''): self
    {
        $valor = (string) $this->valor($campo);
        if ($valor !== '' && mb_strlen($valor) < $minimo) {
            $this->errores[$campo] = $mensaje
                ?: "El campo {$this->etiqueta($campo)} debe tener al menos {$minimo} caracteres.";
        }
        return $this;
    }

    public function largoMaximo(string $campo, int $maximo, string $mensaje = ''): self
    {
        $valor = (string) $this->valor($campo);
        if (mb_strlen($valor) > $maximo) {
            $this->errores[$campo] = $mensaje
                ?: "El campo {$this->etiqueta($campo)} no debe superar {$maximo} caracteres.";
        }
        return $this;
    }

    public function correo(string $campo, string $mensaje = ''): self
    {
        $valor = (string) $this->valor($campo);
        if ($valor !== '' && !filter_var($valor, FILTER_VALIDATE_EMAIL)) {
            $this->errores[$campo] = $mensaje
                ?: "El campo {$this->etiqueta($campo)} no tiene un formato de correo valido.";
        }
        return $this;
    }

    public function soloAlfanumerico(string $campo, string $mensaje = ''): self
    {
        $valor = (string) $this->valor($campo);
        if ($valor !== '' && !preg_match('/^[A-Za-z0-9_.\-]+$/', $valor)) {
            $this->errores[$campo] = $mensaje
                ?: "El campo {$this->etiqueta($campo)} solo admite letras, numeros, punto, guion y guion bajo.";
        }
        return $this;
    }

    public function numerico(string $campo, string $mensaje = ''): self
    {
        $valor = (string) $this->valor($campo);
        if ($valor !== '' && !is_numeric($valor)) {
            $this->errores[$campo] = $mensaje
                ?: "El campo {$this->etiqueta($campo)} debe ser numerico.";
        }
        return $this;
    }

    public function enteroPositivo(string $campo, string $mensaje = ''): self
    {
        $valor = $this->valor($campo);
        if ($valor !== '' && (!is_numeric($valor) || (int) $valor <= 0)) {
            $this->errores[$campo] = $mensaje
                ?: "Debe seleccionar una opcion valida en {$this->etiqueta($campo)}.";
        }
        return $this;
    }

    public function fecha(string $campo, string $mensaje = ''): self
    {
        $valor = (string) $this->valor($campo);
        if ($valor === '') {
            return $this;
        }

        $objeto = DateTime::createFromFormat('Y-m-d', $valor);
        if (!$objeto || $objeto->format('Y-m-d') !== $valor) {
            $this->errores[$campo] = $mensaje
                ?: "El campo {$this->etiqueta($campo)} no es una fecha valida.";
        }
        return $this;
    }

    public function fechaNoFutura(string $campo, string $mensaje = ''): self
    {
        $valor = (string) $this->valor($campo);
        if ($valor !== '' && strtotime($valor) > strtotime(date('Y-m-d'))) {
            $this->errores[$campo] = $mensaje
                ?: "La fecha de {$this->etiqueta($campo)} no puede ser posterior a hoy.";
        }
        return $this;
    }

    public function enLista(string $campo, array $permitidos, string $mensaje = ''): self
    {
        $valor = (string) $this->valor($campo);
        if ($valor !== '' && !in_array($valor, $permitidos, true)) {
            $this->errores[$campo] = $mensaje
                ?: "El valor de {$this->etiqueta($campo)} no es valido.";
        }
        return $this;
    }

    public function claveFuerte(string $campo, string $mensaje = ''): self
    {
        $valor = (string) $this->valor($campo);
        if ($valor !== '' && !Helper::claveFuerte($valor)) {
            $this->errores[$campo] = $mensaje
                ?: 'La contrasena debe tener minimo 8 caracteres, una mayuscula, '
                 . 'una minuscula, un numero y un simbolo.';
        }
        return $this;
    }

    public function coinciden(string $campo, string $campoConfirmacion, string $mensaje = ''): self
    {
        if ($this->valor($campo) !== $this->valor($campoConfirmacion)) {
            $this->errores[$campoConfirmacion] = $mensaje
                ?: 'La confirmacion no coincide con la contrasena ingresada.';
        }
        return $this;
    }

    public function personalizado(string $campo, bool $condicion, string $mensaje): self
    {
        if (!$condicion) {
            $this->errores[$campo] = $mensaje;
        }
        return $this;
    }

    // -----------------------------------------------------------------
    // Resultados
    // -----------------------------------------------------------------

    public function pasa(): bool
    {
        return empty($this->errores);
    }

    public function errores(): array
    {
        return $this->errores;
    }

    public function primerError(): ?string
    {
        return empty($this->errores) ? null : reset($this->errores);
    }
}
