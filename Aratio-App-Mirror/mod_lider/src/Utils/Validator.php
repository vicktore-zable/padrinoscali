<?php
/**
 * Clase Validator
 * Validación de datos de formularios
 *
 * @package App\Utils
 * @author Sistema de Gestión de Colaboradores
 * @version 1.0
 */

namespace App\Utils;

class Validator {
    /**
     * Datos a validar
     * @var array
     */
    private array $data;

    /**
     * Errores encontrados
     * @var array
     */
    private array $errors = [];

    /**
     * Constructor
     *
     * @param array $data
     */
    public function __construct(array $data) {
        $this->data = $data;
    }

    /**
     * Validar campos requeridos
     *
     * @param array $fields
     * @param string|null $message
     * @return self
     */
    public function required(array $fields, ?string $message = null): self {
        $message = $message ?? 'Este campo es requerido';

        foreach ($fields as $field) {
            if (!isset($this->data[$field]) || trim($this->data[$field]) === '') {
                $this->errors[$field] = $message;
            }
        }

        return $this;
    }

    /**
     * Validar email
     *
     * @param string $field
     * @param string|null $message
     * @return self
     */
    public function email(string $field, ?string $message = null): self {
        $message = $message ?? 'Email inválido';

        if (isset($this->data[$field]) && !empty($this->data[$field])) {
            if (!filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
                $this->errors[$field] = $message;
            }
        }

        return $this;
    }

    /**
     * Validar longitud mínima
     *
     * @param string $field
     * @param int $min
     * @param string|null $message
     * @return self
     */
    public function min(string $field, int $min, ?string $message = null): self {
        $message = $message ?? "Debe tener al menos $min caracteres";

        if (isset($this->data[$field]) && strlen($this->data[$field]) < $min) {
            $this->errors[$field] = $message;
        }

        return $this;
    }

    /**
     * Validar longitud máxima
     *
     * @param string $field
     * @param int $max
     * @param string|null $message
     * @return self
     */
    public function max(string $field, int $max, ?string $message = null): self {
        $message = $message ?? "No puede exceder $max caracteres";

        if (isset($this->data[$field]) && strlen($this->data[$field]) > $max) {
            $this->errors[$field] = $message;
        }

        return $this;
    }

    /**
     * Validar que sea numérico
     *
     * @param string $field
     * @param string|null $message
     * @return self
     */
    public function numeric(string $field, ?string $message = null): self {
        $message = $message ?? 'Debe ser un número';

        if (isset($this->data[$field]) && !is_numeric($this->data[$field])) {
            $this->errors[$field] = $message;
        }

        return $this;
    }

    /**
     * Validar que sea entero
     *
     * @param string $field
     * @param string|null $message
     * @return self
     */
    public function integer(string $field, ?string $message = null): self {
        $message = $message ?? 'Debe ser un número entero';

        if (isset($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_INT)) {
            $this->errors[$field] = $message;
        }

        return $this;
    }

    /**
     * Validar que sea fecha válida
     *
     * @param string $field
     * @param string $format
     * @param string|null $message
     * @return self
     */
    public function date(string $field, string $format = 'Y-m-d', ?string $message = null): self {
        $message = $message ?? 'Fecha inválida';

        if (isset($this->data[$field])) {
            $date = \DateTime::createFromFormat($format, $this->data[$field]);
            if (!$date || $date->format($format) !== $this->data[$field]) {
                $this->errors[$field] = $message;
            }
        }

        return $this;
    }

    /**
     * Validar que sea URL válida
     *
     * @param string $field
     * @param string|null $message
     * @return self
     */
    public function url(string $field, ?string $message = null): self {
        $message = $message ?? 'URL inválida';

        if (isset($this->data[$field]) && !empty($this->data[$field])) {
            if (!filter_var($this->data[$field], FILTER_VALIDATE_URL)) {
                $this->errors[$field] = $message;
            }
        }

        return $this;
    }

    /**
     * Validar que coincida con un patrón regex
     *
     * @param string $field
     * @param string $pattern
     * @param string|null $message
     * @return self
     */
    public function pattern(string $field, string $pattern, ?string $message = null): self {
        $message = $message ?? 'Formato inválido';

        if (isset($this->data[$field]) && !preg_match($pattern, $this->data[$field])) {
            $this->errors[$field] = $message;
        }

        return $this;
    }

    /**
     * Validar que esté en una lista de valores
     *
     * @param string $field
     * @param array $values
     * @param string|null $message
     * @return self
     */
    public function in(string $field, array $values, ?string $message = null): self {
        $message = $message ?? 'Valor no permitido';

        if (isset($this->data[$field]) && !in_array($this->data[$field], $values)) {
            $this->errors[$field] = $message;
        }

        return $this;
    }

    /**
     * Validar que dos campos coincidan
     *
     * @param string $field1
     * @param string $field2
     * @param string|null $message
     * @return self
     */
    public function match(string $field1, string $field2, ?string $message = null): self {
        $message = $message ?? 'Los campos no coinciden';

        if (isset($this->data[$field1], $this->data[$field2])) {
            if ($this->data[$field1] !== $this->data[$field2]) {
                $this->errors[$field1] = $message;
            }
        }

        return $this;
    }

    /**
     * Validar que sea único en la base de datos
     *
     * @param string $field
     * @param string $table
     * @param string|null $column
     * @param int|null $exceptId
     * @param string|null $message
     * @return self
     */
    public function unique(
        string $field,
        string $table,
        ?string $column = null,
        ?int $exceptId = null,
        ?string $message = null
    ): self {
        $message = $message ?? 'Este valor ya está en uso';
        $column = $column ?? $field;

        if (isset($this->data[$field])) {
            $db = \Database::getInstance();

            $where = "$column = ?";
            $params = [$this->data[$field]];

            if ($exceptId !== null) {
                $where .= " AND id != ?";
                $params[] = $exceptId;
            }

            if ($db->exists($table, $where, $params)) {
                $this->errors[$field] = $message;
            }
        }

        return $this;
    }

    /**
     * Validar valor mínimo (numérico)
     *
     * @param string $field
     * @param float $min
     * @param string|null $message
     * @return self
     */
    public function minValue(string $field, float $min, ?string $message = null): self {
        $message = $message ?? "El valor debe ser mayor o igual a $min";

        if (isset($this->data[$field]) && is_numeric($this->data[$field])) {
            if ((float)$this->data[$field] < $min) {
                $this->errors[$field] = $message;
            }
        }

        return $this;
    }

    /**
     * Validar valor máximo (numérico)
     *
     * @param string $field
     * @param float $max
     * @param string|null $message
     * @return self
     */
    public function maxValue(string $field, float $max, ?string $message = null): self {
        $message = $message ?? "El valor debe ser menor o igual a $max";

        if (isset($this->data[$field]) && is_numeric($this->data[$field])) {
            if ((float)$this->data[$field] > $max) {
                $this->errors[$field] = $message;
            }
        }

        return $this;
    }

    /**
     * Validar que sea alfanumérico
     *
     * @param string $field
     * @param string|null $message
     * @return self
     */
    public function alphanumeric(string $field, ?string $message = null): self {
        $message = $message ?? 'Solo se permiten letras y números';

        if (isset($this->data[$field]) && !ctype_alnum($this->data[$field])) {
            $this->errors[$field] = $message;
        }

        return $this;
    }

    /**
     * Validar que sea solo letras
     *
     * @param string $field
     * @param string|null $message
     * @return self
     */
    public function alpha(string $field, ?string $message = null): self {
        $message = $message ?? 'Solo se permiten letras';

        if (isset($this->data[$field]) && !ctype_alpha(str_replace(' ', '', $this->data[$field]))) {
            $this->errors[$field] = $message;
        }

        return $this;
    }

    /**
     * Validar archivo subido
     *
     * @param string $field
     * @param array $allowedTypes
     * @param int|null $maxSize En bytes
     * @param string|null $message
     * @return self
     */
    public function file(
        string $field,
        array $allowedTypes = [],
        ?int $maxSize = null,
        ?string $message = null
    ): self {
        if (!isset($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
            return $this;
        }

        if ($_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
            $this->errors[$field] = 'Error al subir el archivo';
            return $this;
        }

        // Validar tipo
        if (!empty($allowedTypes)) {
            $ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedTypes)) {
                $this->errors[$field] = $message ?? 'Tipo de archivo no permitido';
                return $this;
            }
        }

        // Validar tamaño
        if ($maxSize !== null && $_FILES[$field]['size'] > $maxSize) {
            $maxMB = round($maxSize / 1048576, 2);
            $this->errors[$field] = $message ?? "El archivo no debe exceder {$maxMB}MB";
            return $this;
        }

        return $this;
    }

    /**
     * Validación personalizada con callback
     *
     * @param string $field
     * @param callable $callback
     * @param string|null $message
     * @return self
     */
    public function custom(string $field, callable $callback, ?string $message = null): self {
        $message = $message ?? 'Validación fallida';

        if (isset($this->data[$field])) {
            if (!$callback($this->data[$field], $this->data)) {
                $this->errors[$field] = $message;
            }
        }

        return $this;
    }

    /**
     * Validar que el campo sea diferente de otro
     *
     * @param string $field1
     * @param string $field2
     * @param string|null $message
     * @return self
     */
    public function different(string $field1, string $field2, ?string $message = null): self {
        $message = $message ?? 'Los campos deben ser diferentes';

        if (isset($this->data[$field1], $this->data[$field2])) {
            if ($this->data[$field1] === $this->data[$field2]) {
                $this->errors[$field1] = $message;
            }
        }

        return $this;
    }

    /**
     * Validar fecha posterior a otra
     *
     * @param string $field
     * @param string $compareField
     * @param string|null $message
     * @return self
     */
    public function after(string $field, string $compareField, ?string $message = null): self {
        $message = $message ?? 'La fecha debe ser posterior';

        if (isset($this->data[$field], $this->data[$compareField])) {
            if (strtotime($this->data[$field]) <= strtotime($this->data[$compareField])) {
                $this->errors[$field] = $message;
            }
        }

        return $this;
    }

    /**
     * Validar fecha anterior a otra
     *
     * @param string $field
     * @param string $compareField
     * @param string|null $message
     * @return self
     */
    public function before(string $field, string $compareField, ?string $message = null): self {
        $message = $message ?? 'La fecha debe ser anterior';

        if (isset($this->data[$field], $this->data[$compareField])) {
            if (strtotime($this->data[$field]) >= strtotime($this->data[$compareField])) {
                $this->errors[$field] = $message;
            }
        }

        return $this;
    }

    /**
     * Validar contraseña segura
     *
     * @param string $field
     * @return self
     */
    public function strongPassword(string $field): self {
        if (isset($this->data[$field])) {
            $result = Security::validatePassword($this->data[$field]);
            if (!$result['valid']) {
                $this->errors[$field] = implode('. ', $result['errors']);
            }
        }

        return $this;
    }

    /**
     * Verificar si pasó la validación
     *
     * @return bool
     */
    public function passes(): bool {
        return empty($this->errors);
    }

    /**
     * Verificar si falló la validación
     *
     * @return bool
     */
    public function fails(): bool {
        return !$this->passes();
    }

    /**
     * Obtener errores
     *
     * @return array
     */
    public function errors(): array {
        return $this->errors;
    }

    /**
     * Obtener primer error de un campo
     *
     * @param string $field
     * @return string|null
     */
    public function first(string $field): ?string {
        return $this->errors[$field] ?? null;
    }

    /**
     * Agregar error manualmente
     *
     * @param string $field
     * @param string $message
     * @return self
     */
    public function addError(string $field, string $message): self {
        $this->errors[$field] = $message;
        return $this;
    }

    /**
     * Obtener datos validados
     *
     * @return array
     */
    public function validated(): array {
        return $this->data;
    }

    /**
     * Obtener solo campos específicos
     *
     * @param array $fields
     * @return array
     */
    public function only(array $fields): array {
        return array_intersect_key($this->data, array_flip($fields));
    }

    /**
     * Obtener todos excepto campos específicos
     *
     * @param array $fields
     * @return array
     */
    public function except(array $fields): array {
        return array_diff_key($this->data, array_flip($fields));
    }

    /**
     * Sanitizar datos
     *
     * @return self
     */
    public function sanitize(): self {
        $this->data = Security::sanitizeArray($this->data);
        return $this;
    }

    /**
     * Validar múltiples reglas a la vez
     *
     * @param array $rules ['field' => 'required|email|min:5']
     * @return self
     */
    public function rules(array $rules): self {
        foreach ($rules as $field => $ruleString) {
            $rules = explode('|', $ruleString);

            foreach ($rules as $rule) {
                if (strpos($rule, ':') !== false) {
                    [$method, $params] = explode(':', $rule, 2);
                    $params = explode(',', $params);
                } else {
                    $method = $rule;
                    $params = [];
                }

                if (method_exists($this, $method)) {
                    $this->$method($field, ...$params);
                }
            }
        }

        return $this;
    }
}
