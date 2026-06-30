<?php

require_once __DIR__ . '/WhatsAppCloudApi.php';
require_once __DIR__ . '/ActivityLogger.php';

class WorkflowEngine
{
    private static ?PDO $db = null;

    const TRIGGER_REGISTRADO = 'colaborador.registrado';
    const TRIGGER_EVENTO_PROXIMO = 'evento.proximo';
    const TRIGGER_EVENTO_FINALIZADO = 'evento.finalizado';
    const TRIGGER_DONACION_RECIBIDA = 'donacion.recibida';
    const TRIGGER_INACTIVO = 'colaborador.inactivo_30d';

    private static function db(): PDO
    {
        if (self::$db === null) {
            self::$db = getDB();
        }
        return self::$db;
    }

    public static function trigger(string $evento, array $datos = [], ?int $colaboradorId = null): void
    {
        $db = self::db();

        $stmt = $db->prepare("
            SELECT * FROM workflow_reglas 
            WHERE trigger_evento = ? AND activo = TRUE 
            ORDER BY prioridad ASC
        ");
        $stmt->execute([$evento]);
        $reglas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($reglas as $regla) {
            try {
                $condiciones = json_decode($regla['condiciones_json'], true) ?? [];

                if (!self::evaluarCondiciones($condiciones, $datos)) {
                    continue;
                }

                $logId = self::logEjecucion($regla['id'], $colaboradorId, $evento);

                $acciones = json_decode($regla['acciones_json'], true) ?? [];
                $exitosas = 0;
                $fallidas = 0;

                foreach ($acciones as $accion) {
                    $resultado = self::ejecutarAccion($accion, $datos, $colaboradorId);
                    if ($resultado) {
                        $exitosas++;
                    } else {
                        $fallidas++;
                        if ($this->needsDeferred($accion)) {
                            self::encolarAccion($regla['id'], $logId, $colaboradorId, $datos, $accion);
                        }
                    }
                }

                $db->prepare("
                    UPDATE workflow_log SET resultado = ?, acciones_ejecutadas = ?, acciones_fallidas = ? WHERE id = ?
                ")->execute([
                    $fallidas > 0 ? ($exitosas > 0 ? 'pendiente' : 'fallido') : 'exitoso',
                    $exitosas,
                    $fallidas,
                    $logId
                ]);

                $db->prepare("
                    UPDATE workflow_reglas SET 
                        ejecuciones_total = ejecuciones_total + 1,
                        ejecuciones_exitosas = ejecuciones_exitosas + ?,
                        ejecuciones_fallidas = ejecuciones_fallidas + ?
                    WHERE id = ?
                ")->execute([$exitosas > 0 ? 1 : 0, $fallidas > 0 ? 1 : 0, $regla['id']]);

            } catch (Throwable $e) {
                error_log("ALAS Workflow error: regla={$regla['id']} error=" . $e->getMessage());
            }
        }
    }

    private static function evaluarCondiciones(array $condiciones, array $datos): bool
    {
        if (empty($condiciones)) return true;

        if (isset($condiciones['dias_antes'])) {
            $fechaEvento = $datos['fecha_evento'] ?? $datos['fecha'] ?? null;
            if (!$fechaEvento) return false;

            $fecha = new DateTime($fechaEvento);
            $ahora = new DateTime();
            $diff = $ahora->diff($fecha)->days;
            if ($fecha > $ahora) {
                $diasHasta = (int)$ahora->diff($fecha)->format('%a');
                if ($diasHasta !== (int)$condiciones['dias_antes']) {
                    return false;
                }
            }
        }

        if (isset($condiciones['dias'])) {
            $db = self::db();
            $colabId = $datos['colaborador_id'] ?? null;
            if (!$colabId) return false;

            $stmt = $db->prepare("
                SELECT MAX(creado_en) FROM actividad_colaborador 
                WHERE colaborador_id = ?
            ");
            $stmt->execute([$colabId]);
            $ultimaActividad = $stmt->fetchColumn();

            if ($ultimaActividad) {
                $diasInactivo = (new DateTime())->diff(new DateTime($ultimaActividad))->days;
                if ($diasInactivo < (int)$condiciones['dias']) {
                    return false;
                }
            }
        }

        return true;
    }

    private static function ejecutarAccion(array $accion, array $datos, ?int $colaboradorId): bool
    {
        $tipo = $accion['tipo'] ?? '';

        switch ($tipo) {
            case 'whatsapp':
                return self::accionWhatsapp($accion, $datos, $colaboradorId);

            case 'asignar_lider':
                return self::accionAsignarLider($accion, $datos, $colaboradorId);

            case 'cambiar_estado':
                return self::accionCambiarEstado($accion, $datos, $colaboradorId);

            case 'notificar_lider':
                return self::accionNotificarLider($accion, $datos, $colaboradorId);

            default:
                error_log("ALAS: Acción desconocida: {$tipo}");
                return false;
        }
    }

    private static function accionWhatsapp(array $accion, array $datos, ?int $colaboradorId): bool
    {
        if (!$colaboradorId) return false;

        $db = self::db();
        $stmt = $db->prepare("
            SELECT id, nombres, apellidos, telefono, telefono_whatsapp 
            FROM colaboradores WHERE id = ?
        ");
        $stmt->execute([$colaboradorId]);
        $colab = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$colab) return false;

        $telefono = $colab['telefono_whatsapp'] ?: $colab['telefono'];
        if (empty($telefono)) return false;

        $plantilla = $accion['plantilla'] ?? '';
        $variablesEsperadas = $accion['variables'] ?? [];

        $variables = [];
        foreach ($variablesEsperadas as $var) {
            switch ($var) {
                case 'nombre':
                    $variables[] = $colab['nombres'];
                    break;
                case 'evento':
                    $variables[] = $datos['evento']['nombre'] ?? $datos['nombre_evento'] ?? 'el evento';
                    break;
                case 'hora':
                    $fecha = $datos['evento']['fecha'] ?? $datos['fecha_evento'] ?? '';
                    $variables[] = $fecha ? date('g:i A', strtotime($fecha)) : '';
                    break;
                case 'lugar':
                    $variables[] = $datos['evento']['lugar'] ?? $datos['lugar'] ?? '';
                    break;
                case 'monto':
                    $monto = $datos['donacion']['monto'] ?? $datos['monto'] ?? '';
                    $variables[] = number_format((float)$monto, 0);
                    break;
                default:
                    $variables[] = $datos[$var] ?? '';
            }
        }

        $api = new WhatsAppCloudApi();
        $resultado = $api->sendTemplate($telefono, $plantilla, $variables);

        if (!$resultado['success']) {
            $resultado = $api->sendText($telefono, "Hola {$colab['nombres']}, gracias por ser parte de Padrinos Cali.");
        }

        ActivityLogger::log(
            $colaboradorId,
            'whatsapp_enviado',
            "ALAS: " . ($resultado['success'] ? "Enviado" : "Fallido") . " template {$plantilla}",
            ['plantilla' => $plantilla, 'success' => $resultado['success'], 'error' => $resultado['error'] ?? null],
            'whatsapp_mensajes',
            null
        );

        return $resultado['success'];
    }

    private static function accionAsignarLider(array $accion, array $datos, ?int $colaboradorId): bool
    {
        if (!$colaboradorId) return false;

        $db = self::db();
        $colab = $datos['colaborador'] ?? [];

        if (empty($colab['territorio_id'])) {
            $stmt = $db->prepare("SELECT territorio_id FROM colaboradores WHERE id = ?");
            $stmt->execute([$colaboradorId]);
            $colab = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        $territorioId = $colab['territorio_id'] ?? null;
        if (!$territorioId) return false;

        $stmt = $db->prepare("
            SELECT id FROM colaboradores 
            WHERE territorio_id = ? AND perfil LIKE '%lider%' AND (estado IS NULL OR estado NOT IN ('inactivo','Inactivo'))
            ORDER BY dato_potencial DESC LIMIT 1
        ");
        $stmt->execute([$territorioId]);
        $lider = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($lider) {
            $stmt = $db->prepare("UPDATE colaboradores SET lider_directo = ? WHERE id = ?");
            $stmt->execute([$lider['id'], $colaboradorId]);

            ActivityLogger::log(
                $colaboradorId,
                'lider_cambio',
                "Asignado automáticamente al líder del territorio",
                ['lider_id' => $lider['id'], 'motivo' => 'asignacion_automatica_alas']
            );
            return true;
        }

        return false;
    }

    private static function accionCambiarEstado(array $accion, array $datos, ?int $colaboradorId): bool
    {
        if (!$colaboradorId) return false;

        $db = self::db();
        $nuevoEstado = $accion['estado'] ?? 'Decrecio';
        $motivo = $accion['motivo'] ?? 'evaluacion_automatica_alas';

        $stmt = $db->prepare("SELECT estado FROM colaboradores WHERE id = ?");
        $stmt->execute([$colaboradorId]);
        $actual = $stmt->fetchColumn();

        if ($actual === $nuevoEstado) return true;

        $stmt = $db->prepare("UPDATE colaboradores SET estado = ? WHERE id = ?");
        $stmt->execute([$nuevoEstado, $colaboradorId]);

        ActivityLogger::log(
            $colaboradorId,
            'estado_cambio',
            "ALAS: Estado {$actual} → {$nuevoEstado}",
            ['estado_anterior' => $actual, 'estado_nuevo' => $nuevoEstado, 'motivo' => $motivo]
        );

        return true;
    }

    private static function accionNotificarLider(array $accion, array $datos, ?int $colaboradorId): bool
    {
        if (!$colaboradorId) return false;

        $db = self::db();
        $stmt = $db->prepare("
            SELECT l.telefono_whatsapp, l.nombres, c.nombres as colaborador_nombre
            FROM colaboradores c
            JOIN colaboradores l ON c.lider_directo = l.id
            WHERE c.id = ?
        ");
        $stmt->execute([$colaboradorId]);
        $info = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$info || empty($info['telefono_whatsapp'])) return false;

        $api = new WhatsAppCloudApi();
        $mensaje = "Hola {$info['nombres']}, tu colaborador {$info['colaborador_nombre']} ha realizado una nueva actividad en Padrinos Cali.";
        $api->sendText($info['telefono_whatsapp'], $mensaje);

        return true;
    }

    private static function logEjecucion(int $reglaId, ?int $colaboradorId, string $evento): int
    {
        $db = self::db();
        $stmt = $db->prepare("
            INSERT INTO workflow_log (regla_id, colaborador_id, trigger_evento, resultado)
            VALUES (?, ?, ?, 'pendiente')
        ");
        $stmt->execute([$reglaId, $colaboradorId, $evento]);
        return (int)$db->lastInsertId();
    }

    private static function encolarAccion(int $reglaId, int $logId, ?int $colaboradorId, array $datos, array $accion): void
    {
        $db = self::db();
        $programado = null;

        if ($accion['tipo'] === 'whatsapp' && !$colaboradorId) {
            $programado = date('Y-m-d H:i:s', strtotime('+5 minutes'));
        }

        $stmt = $db->prepare("
            INSERT INTO workflow_acciones_pendientes 
                (regla_id, workflow_log_id, colaborador_id, datos_json, accion_tipo, accion_params, programado_para, estado)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')
        ");
        $stmt->execute([
            $reglaId, $logId, $colaboradorId,
            json_encode($datos, JSON_UNESCAPED_UNICODE),
            $accion['tipo'],
            json_encode($accion, JSON_UNESCAPED_UNICODE),
            $programado
        ]);
    }

    private static function needsDeferred(array $accion): bool
    {
        $diferidas = ['whatsapp'];
        return in_array($accion['tipo'] ?? '', $diferidas);
    }

    public static function procesarPendientes(): int
    {
        $db = self::db();
        $procesadas = 0;

        $stmt = $db->query("
            SELECT * FROM workflow_acciones_pendientes 
            WHERE estado = 'pending' 
              AND (programado_para IS NULL OR programado_para <= NOW())
            ORDER BY id ASC 
            LIMIT 50
        ");
        $pendientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($pendientes as $p) {
            try {
                $db->prepare("UPDATE workflow_acciones_pendientes SET estado = 'processing' WHERE id = ?")
                   ->execute([$p['id']]);

                $datos = json_decode($p['datos_json'], true) ?? [];
                $accion = json_decode($p['accion_params'], true) ?? [];
                $resultado = self::ejecutarAccion($accion, $datos, (int)$p['colaborador_id']);

                $nuevoEstado = $resultado ? 'completed' : 'failed';
                $db->prepare("UPDATE workflow_acciones_pendientes SET estado = ?, procesado_en = NOW() WHERE id = ?")
                   ->execute([$nuevoEstado, $p['id']]);

                if ($resultado) {
                    $db->prepare("UPDATE workflow_log SET resultado = 'exitoso', acciones_ejecutadas = acciones_ejecutadas + 1 WHERE id = ?")
                       ->execute([$p['workflow_log_id']]);
                }

                $procesadas++;
            } catch (Throwable $e) {
                error_log("ALAS procesar pendiente error: " . $e->getMessage());
                $reintentos = (int)$p['reintentos'] + 1;
                if ($reintentos >= 3) {
                    $db->prepare("UPDATE workflow_acciones_pendientes SET estado = 'failed', error = ?, reintentos = ? WHERE id = ?")
                       ->execute([$e->getMessage(), $reintentos, $p['id']]);
                } else {
                    $db->prepare("UPDATE workflow_acciones_pendientes SET estado = 'pending', reintentos = ?, programado_para = DATE_ADD(NOW(), INTERVAL 5 MINUTE) WHERE id = ?")
                       ->execute([$reintentos, $p['id']]);
                }
            }
        }

        return $procesadas;
    }

    public static function getStats(): array
    {
        $db = self::db();

        $hoy = $db->query("
            SELECT COUNT(*) as total, 
                   SUM(CASE WHEN resultado = 'exitoso' THEN 1 ELSE 0 END) as exitosos,
                   SUM(CASE WHEN resultado = 'fallido' THEN 1 ELSE 0 END) as fallidos
            FROM workflow_log WHERE DATE(ejecutado_en) = CURDATE()
        ")->fetch(PDO::FETCH_ASSOC);

        $mes = $db->query("
            SELECT COUNT(*) as total,
                   SUM(CASE WHEN resultado = 'exitoso' THEN 1 ELSE 0 END) as exitosos
            FROM workflow_log 
            WHERE MONTH(ejecutado_en) = MONTH(CURDATE()) AND YEAR(ejecutado_en) = YEAR(CURDATE())
        ")->fetch(PDO::FETCH_ASSOC);

        $reglasActivas = (int)$db->query("SELECT COUNT(*) FROM workflow_reglas WHERE activo = TRUE")->fetchColumn();

        $stm = $db->query("
            SELECT r.nombre, wl.trigger_evento, COUNT(*) as ejecuciones
            FROM workflow_log wl
            JOIN workflow_reglas r ON wl.regla_id = r.id
            WHERE DATE(wl.ejecutado_en) = CURDATE()
            GROUP BY r.id
            ORDER BY ejecuciones DESC LIMIT 5
        ");

        return [
            'hoy' => $hoy,
            'mes' => $mes,
            'reglas_activas' => $reglasActivas,
            'top_reglas' => $stm->fetchAll(PDO::FETCH_ASSOC)
        ];
    }
}
