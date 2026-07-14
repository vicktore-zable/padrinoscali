<?php
/**
 * Modelo Curriculum
 * Gestión de hojas de vida de colaboradores
 *
 * @package App\Models
 * @author Sistema de Gestión de Colaboradores
 * @version 1.0
 */

namespace App\Models;

class Curriculum {
    /**
     * Nombre de la tabla
     */
    const TABLE = 'curriculum';

    /**
     * Instancia de Database
     * @var \Database
     */
    private \Database $db;

    /**
     * Constructor
     */
    public function __construct() {
        $this->db = \Database::getInstance();
    }

    /**
     * Obtener curriculum por ID
     *
     * @param int $id
     * @return array|null
     */
    public function getById(int $id): ?array {
        $sql = "SELECT c.*,
                       CONCAT(col.nombres, ' ', col.apellidos) as colaborador_nombre
                FROM " . self::TABLE . " c
                INNER JOIN colaboradores col ON c.colaborador_id = col.id
                WHERE c.id = ?";

        $result = $this->db->fetchOne($sql, [$id]);

        if ($result) {
            // Decodificar JSON
            $result['experiencia_laboral'] = $this->decodeJson($result['experiencia_laboral']);
            $result['formacion_academica'] = $this->decodeJson($result['formacion_academica']);
            $result['participacion_politica'] = $this->decodeJson($result['participacion_politica']);
            $result['hijos_data'] = $this->decodeJson($result['hijos_data'] ?? null);
        }

        return $result;
    }

    /**
     * Obtener curriculum por colaborador ID
     *
     * @param int $colaboradorId
     * @return array|null
     */
    public function getByColaboradorId(int $colaboradorId): ?array {
        $sql = "SELECT * FROM " . self::TABLE . " WHERE colaborador_id = ?";
        $result = $this->db->fetchOne($sql, [$colaboradorId]);

        if ($result) {
            $result['experiencia_laboral'] = $this->decodeJson($result['experiencia_laboral']);
            $result['formacion_academica'] = $this->decodeJson($result['formacion_academica']);
            $result['participacion_politica'] = $this->decodeJson($result['participacion_politica']);
            $result['hijos_data'] = $this->decodeJson($result['hijos_data'] ?? null);
        }

        return $result;
    }

    /**
     * Guardar curriculum (upsert: crea o actualiza)
     *
     * @param int $colaboradorId
     * @param array $data
     * @return bool
     */
    public function save(int $colaboradorId, array $data): bool {
        $data['colaborador_id'] = $colaboradorId;
        $existing = $this->getByColaboradorId($colaboradorId);

        if ($existing) {
            return $this->update($existing['id'], $data);
        } else {
            return (bool) $this->create($data);
        }
    }

    /**
     * Crear curriculum
     *
     * @param array $data
     * @return int|false
     */
    public function create(array $data) {
        // Verificar que no exista ya un curriculum para este colaborador
        if ($this->existsByColaboradorId($data['colaborador_id'])) {
            throw new \Exception('Ya existe un curriculum para este colaborador');
        }

        // Codificar arrays a JSON
        $data = $this->encodeJsonFields($data);

        return $this->db->insert(self::TABLE, $data);
    }

    /**
     * Actualizar curriculum
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool {
        if (!$this->exists($id)) {
            throw new \Exception('El curriculum no existe');
        }

        // Codificar arrays a JSON
        $data = $this->encodeJsonFields($data);

        // Remover campos no actualizables
        unset($data['id'], $data['colaborador_id'], $data['created_at']);

        $result = $this->db->update(self::TABLE, $data, 'id = ?', [$id]);
        return $result > 0;
    }

    /**
     * Eliminar curriculum
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool {
        $result = $this->db->delete(self::TABLE, 'id = ?', [$id]);
        return $result > 0;
    }

    /**
     * Verificar si existe por ID
     *
     * @param int $id
     * @return bool
     */
    public function exists(int $id): bool {
        return $this->db->exists(self::TABLE, 'id = ?', [$id]);
    }

    /**
     * Verificar si existe por colaborador ID
     *
     * @param int $colaboradorId
     * @return bool
     */
    public function existsByColaboradorId(int $colaboradorId): bool {
        return $this->db->exists(self::TABLE, 'colaborador_id = ?', [$colaboradorId]);
    }

    /**
     * Agregar experiencia laboral
     *
     * @param int $curriculumId
     * @param array $experiencia
     * @return bool
     */
    public function addExperienciaLaboral(int $curriculumId, array $experiencia): bool {
        $curriculum = $this->getById($curriculumId);
        if (!$curriculum) {
            throw new \Exception('Curriculum no encontrado');
        }

        $experiencias = $curriculum['experiencia_laboral'] ?? [];
        $experiencias[] = $experiencia;

        return $this->update($curriculumId, [
            'experiencia_laboral' => $experiencias
        ]);
    }

    /**
     * Agregar formación académica
     *
     * @param int $curriculumId
     * @param array $formacion
     * @return bool
     */
    public function addFormacionAcademica(int $curriculumId, array $formacion): bool {
        $curriculum = $this->getById($curriculumId);
        if (!$curriculum) {
            throw new \Exception('Curriculum no encontrado');
        }

        $formaciones = $curriculum['formacion_academica'] ?? [];
        $formaciones[] = $formacion;

        return $this->update($curriculumId, [
            'formacion_academica' => $formaciones
        ]);
    }

    /**
     * Agregar participación política
     *
     * @param int $curriculumId
     * @param array $participacion
     * @return bool
     */
    public function addParticipacionPolitica(int $curriculumId, array $participacion): bool {
        $curriculum = $this->getById($curriculumId);
        if (!$curriculum) {
            throw new \Exception('Curriculum no encontrado');
        }

        $participaciones = $curriculum['participacion_politica'] ?? [];
        $participaciones[] = $participacion;

        return $this->update($curriculumId, [
            'participacion_politica' => $participaciones
        ]);
    }

    /**
     * Codificar campos JSON
     *
     * @param array $data
     * @return array
     */
    private function encodeJsonFields(array $data): array {
        $jsonFields = ['experiencia_laboral', 'formacion_academica', 'participacion_politica'];

        foreach ($jsonFields as $field) {
            if (isset($data[$field])) {
                if (is_array($data[$field])) {
                    $data[$field] = json_encode($data[$field]);
                } elseif (is_string($data[$field]) && empty($data[$field])) {
                    $data[$field] = json_encode([]);
                }
            }
        }

        return $data;
    }

    /**
     * Decodificar JSON
     *
     * @param string|null $json
     * @return array
     */
    private function decodeJson(?string $json): array {
        if (empty($json)) {
            return [];
        }

        $decoded = json_decode($json, true);
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Buscar por habilidad o experiencia
     *
     * @param string $query
     * @param int $limit
     * @return array
     */
    public function search(string $query, int $limit = 20): array {
        $query = $this->db->escapeLike($query);

        $sql = "SELECT c.*,
                       CONCAT(col.nombres, ' ', col.apellidos) as colaborador_nombre,
                       col.documento
                FROM " . self::TABLE . " c
                INNER JOIN colaboradores col ON c.colaborador_id = col.id
                WHERE c.experiencia_laboral LIKE ?
                   OR c.formacion_academica LIKE ?
                   OR c.participacion_politica LIKE ?
                LIMIT ?";

        $param = "%$query%";
        $results = $this->db->fetchAll($sql, [$param, $param, $param, $limit]);

        foreach ($results as &$result) {
            $result['experiencia_laboral'] = $this->decodeJson($result['experiencia_laboral']);
            $result['formacion_academica'] = $this->decodeJson($result['formacion_academica']);
            $result['participacion_politica'] = $this->decodeJson($result['participacion_politica']);
        }

        return $results;
    }

    /**
     * Validar experiencia laboral
     *
     * @param array $experiencia
     * @return array Errores
     */
    public function validateExperiencia(array $experiencia): array {
        $errors = [];

        if (empty($experiencia['cargo'])) {
            $errors['cargo'] = 'El cargo es requerido';
        }

        if (empty($experiencia['empresa'])) {
            $errors['empresa'] = 'La empresa es requerida';
        }

        if (empty($experiencia['fecha_inicio'])) {
            $errors['fecha_inicio'] = 'La fecha de inicio es requerida';
        }

        // Validar fechas si están presentes
        if (!empty($experiencia['fecha_inicio']) && !empty($experiencia['fecha_fin'])) {
            if (strtotime($experiencia['fecha_inicio']) > strtotime($experiencia['fecha_fin'])) {
                $errors['fecha_fin'] = 'La fecha de fin debe ser posterior a la fecha de inicio';
            }
        }

        return $errors;
    }

    /**
     * Validar formación académica
     *
     * @param array $formacion
     * @return array Errores
     */
    public function validateFormacion(array $formacion): array {
        $errors = [];

        if (empty($formacion['titulo'])) {
            $errors['titulo'] = 'El título es requerido';
        }

        if (empty($formacion['institucion'])) {
            $errors['institucion'] = 'La institución es requerida';
        }

        if (empty($formacion['nivel'])) {
            $errors['nivel'] = 'El nivel de formación es requerido';
        }

        if (empty($formacion['fecha_inicio'])) {
            $errors['fecha_inicio'] = 'La fecha de inicio es requerida';
        }

        // Validar fechas
        if (!empty($formacion['fecha_inicio']) && !empty($formacion['fecha_fin'])) {
            if (strtotime($formacion['fecha_inicio']) > strtotime($formacion['fecha_fin'])) {
                $errors['fecha_fin'] = 'La fecha de fin debe ser posterior a la fecha de inicio';
            }
        }

        return $errors;
    }

    /**
     * Validar participación política
     *
     * @param array $participacion
     * @return array Errores
     */
    public function validateParticipacion(array $participacion): array {
        $errors = [];

        if (empty($participacion['cargo'])) {
            $errors['cargo'] = 'El cargo es requerido';
        }

        if (empty($participacion['organizacion'])) {
            $errors['organizacion'] = 'La organización es requerida';
        }

        if (empty($participacion['fecha_inicio'])) {
            $errors['fecha_inicio'] = 'La fecha de inicio es requerida';
        }

        // Validar fechas
        if (!empty($participacion['fecha_inicio']) && !empty($participacion['fecha_fin'])) {
            if (strtotime($participacion['fecha_inicio']) > strtotime($participacion['fecha_fin'])) {
                $errors['fecha_fin'] = 'La fecha de fin debe ser posterior a la fecha de inicio';
            }
        }

        return $errors;
    }
}
