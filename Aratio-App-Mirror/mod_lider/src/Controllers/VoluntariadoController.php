<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Colaborador;

class VoluntariadoController extends Controller {
    private Colaborador $colaboradorModel;

    public function __construct() {
        parent::__construct();
        $this->colaboradorModel = new Colaborador();
    }

    public function misVoluntarios() {
        if (!$this->isAuthenticated()) {
            $this->redirect('?page=portal_login');
            return;
        }

        $liderDocumento = $_SESSION['user']['documento_colaborador'] ?? '';
        if (empty($liderDocumento)) {
            $this->setFlash('Error: no se pudo identificar tu perfil.', 'error');
            $this->redirect('?page=portal_dashboard');
            return;
        }

        $db = \Database::getInstance();

        $stmt = $db->prepare("
            SELECT c.id, c.nombres, c.documento, c.telefono, c.territorio,
                   c.voluntario_fase, c.voluntario_intereses, c.voluntario_disponibilidad,
                   c.created_at
            FROM colaboradores c
            WHERE c.lider_directo = ? AND c.perfil = 'Voluntario'
            ORDER BY FIELD(c.voluntario_fase, 'asignado','contactado','activado','comprometido','movilizado'), c.created_at DESC
        ");
        $stmt->execute([$liderDocumento]);
        $voluntarios = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $total = count($voluntarios);
        $por_fase = [];
        $fases = ['asignado','contactado','activado','comprometido','movilizado'];
        foreach ($fases as $f) $por_fase[$f] = 0;
        foreach ($voluntarios as $v) {
            $f = $v['voluntario_fase'] ?? 'asignado';
            $por_fase[$f] = ($por_fase[$f] ?? 0) + 1;
        }

        $this->view('portal.mis_voluntarios', [
            'title' => 'Mis Voluntarios',
            'voluntarios' => $voluntarios,
            'total' => $total,
            'por_fase' => $por_fase,
            'fases' => $fases,
        ]);
    }

    public function avanzarFase() {
        if (!$this->isAuthenticated()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'No autenticado']);
            return;
        }

        $voluntarioId = (int)($_POST['id'] ?? 0);
        $nuevaFase = $_POST['fase'] ?? '';

        $fasesValidas = ['contactado','activado','comprometido','movilizado'];
        if (!in_array($nuevaFase, $fasesValidas)) {
            echo json_encode(['success' => false, 'message' => 'Fase inválida']);
            return;
        }

        $liderDocumento = $_SESSION['user']['documento_colaborador'] ?? '';

        try {
            $db = \Database::getInstance();
            $stmt = $db->prepare("
                SELECT id, voluntario_fase FROM colaboradores 
                WHERE id = ? AND lider_directo = ? AND perfil = 'Voluntario'
            ");
            $stmt->execute([$voluntarioId, $liderDocumento]);
            $voluntario = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$voluntario) {
                echo json_encode(['success' => false, 'message' => 'Voluntario no encontrado o no te pertenece']);
                return;
            }

            $orden = ['asignado' => 0, 'contactado' => 1, 'activado' => 2, 'comprometido' => 3, 'movilizado' => 4];
            $actual = $orden[$voluntario['voluntario_fase']] ?? 0;
            $nueva = $orden[$nuevaFase] ?? 0;

            if ($nueva <= $actual) {
                echo json_encode(['success' => false, 'message' => 'Solo puedes avanzar a la siguiente fase']);
                return;
            }

            if ($nueva > $actual + 1) {
                echo json_encode(['success' => false, 'message' => 'Debes avanzar una fase a la vez']);
                return;
            }

            $stmt = $db->prepare("UPDATE colaboradores SET voluntario_fase = ? WHERE id = ?");
            $stmt->execute([$nuevaFase, $voluntarioId]);

            $stmt2 = $db->prepare("
                INSERT INTO actividad_colaborador (colaborador_id, tipo, descripcion, metadata_json, creado_por, creado_en)
                VALUES (?, 'voluntario_fase', ?, ?, ?, NOW())
            ");
            $stmt2->execute([
                $voluntarioId,
                "Voluntario avanzó a fase: $nuevaFase",
                json_encode(['fase_anterior' => $voluntario['voluntario_fase'], 'fase_nueva' => $nuevaFase, 'avanzado_por' => $liderDocumento]),
                $liderDocumento
            ]);

            echo json_encode(['success' => true, 'message' => 'Fase actualizada correctamente', 'fase' => $nuevaFase]);

        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error al actualizar fase']);
        }
    }

    private function isAuthenticated(): bool {
        return isset($_SESSION['user'])
            && isset($_SESSION['user']['tipo_usuario'])
            && $_SESSION['user']['tipo_usuario'] === 'lider';
    }
}
