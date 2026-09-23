<?php
/**
 * Helpers compartidos de seguridad para mod_eventos.
 * Auth por campaña, CSRF en mutaciones, rate-limit y resolución de responsable.
 */

if (!function_exists('eventos_csrf_token')) {
    function eventos_csrf_token(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('eventos_csrf_verify')) {
    function eventos_csrf_verify(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        $sent = $_SERVER['HTTP_X_CSRF_TOKEN']
            ?? ($_SERVER['HTTP_X_CSRF'] ?? null)
            ?? (getJsonInput()['csrf_token'] ?? null);

        if (empty($_SESSION['csrf_token']) || empty($sent) || !hash_equals($_SESSION['csrf_token'], (string)$sent)) {
            jsonResponse(['success' => false, 'message' => 'Token de seguridad inválido. Recargue la página e intente nuevamente'], 419);
        }
    }
}

if (!function_exists('eventos_client_ip')) {
    function eventos_client_ip(): string
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        return substr((string)$ip, 0, 45);
    }
}

if (!function_exists('eventos_rate_limit')) {
    /**
     * Rate-limit por identificador. Usa rate_limit_check() si existe; si no, falla abierto.
     * Retorna ['allowed'=>bool,'remaining'=>int].
     */
    function eventos_rate_limit(string $identifier, int $max = 30, int $windowSeconds = 60): array
    {
        if (!function_exists('rate_limit_check')) {
            return ['allowed' => true, 'remaining' => $max];
        }
        $key = 'mod_eventos:' . $identifier;
        $result = rate_limit_check($key);
        if (!is_array($result)) {
            return ['allowed' => (bool)$result, 'remaining' => $max];
        }
        return [
            'allowed' => (bool)($result['allowed'] ?? true),
            'remaining' => (int)($result['remaining'] ?? $max),
        ];
    }
}

if (!function_exists('eventos_require_admin')) {
    /**
     * Exige sesión de admin (no líder) y, opcionalmente, acceso a una campaña.
     */
    function eventos_require_admin(?int $campanaId = null): array
    {
        requireAuth();
        $user = getSessionUser();
        if (!$user) {
            jsonResponse(['success' => false, 'message' => 'No autorizado'], 401);
        }
        if ($campanaId !== null && $campanaId > 0) {
            $auth = new Auth();
            if (!$auth->hasAccessToCampana($user['id'], $campanaId)) {
                jsonResponse(['success' => false, 'message' => 'No tienes acceso a esta campaña'], 403);
            }
        }
        return $user;
    }
}

if (!function_exists('eventos_campana_de_evento')) {
    function eventos_campana_de_evento($db, $eventoId): ?int
    {
        $stmt = $db->prepare('SELECT campana_id FROM eventos WHERE id = ?');
        $stmt->execute([$eventoId]);
        $row = $stmt->fetch();
        return $row ? (int)$row['campana_id'] : null;
    }
}

if (!function_exists('eventos_resolve_responsable')) {
    /**
     * Resuelve responsable_id: numérico → usuario existente; "col_<doc>" → usuario por
     * documento_colaborador o auto-creación desde colaboradores.
     * Devuelve el id de usuario o null.
     */
    function eventos_resolve_responsable($db, $responsableId, int $creadoPor): ?int
    {
        if ($responsableId === null || $responsableId === '') {
            return null;
        }
        if (is_numeric($responsableId)) {
            return (int)$responsableId;
        }
        if (strpos((string)$responsableId, 'col_') !== 0) {
            return null;
        }

        $doc = substr((string)$responsableId, 4);
        if ($doc === '') {
            return null;
        }

        $stmtU = $db->prepare('SELECT id FROM usuarios WHERE documento_colaborador = ?');
        $stmtU->execute([$doc]);
        $existe = $stmtU->fetch();
        if ($existe) {
            return (int)$existe['id'];
        }

        $stmtC = $db->prepare('SELECT nombres, apellidos, email, telefono FROM colaboradores WHERE documento = ?');
        $stmtC->execute([$doc]);
        $col = $stmtC->fetch();
        if (!$col) {
            return null;
        }

        // Contraseña temporal = documento; el usuario debe cambiarla en primer ingreso.
        $tmpPass = password_hash($doc, PASSWORD_BCRYPT);
        $db->prepare(
            "INSERT INTO usuarios (usuario, nombre, email, telefono, password, rol, estado, documento_colaborador, creado_por)
             VALUES (?, ?, ?, ?, ?, 'coordinador', 'activo', ?, ?)"
        )->execute([
            $doc,
            trim($col['nombres'] . ' ' . $col['apellidos']),
            $col['email'] ?? ($doc . '@aratio.tmp'),
            $col['telefono'] ?? '',
            $tmpPass,
            $doc,
            $creadoPor,
        ]);
        return (int)$db->lastInsertId();
    }
}

if (!function_exists('eventos_validate_fechas')) {
    /**
     * Valida que fecha_inicio sea anterior a fecha_fin cuando ambas existen.
     */
    function eventos_validate_fechas(string $inicio, ?string $fin): void
    {
        if ($fin === null || $fin === '') {
            return;
        }
        $tsIni = strtotime($inicio);
        $tsFin = strtotime($fin);
        if ($tsIni === false || $tsFin === false) {
            jsonResponse(['success' => false, 'message' => 'Formato de fecha inválido'], 400);
        }
        if ($tsIni >= $tsFin) {
            jsonResponse(['success' => false, 'message' => 'La fecha de inicio debe ser anterior a la fecha de fin'], 400);
        }
    }
}

if (!function_exists('eventos_validate_firma')) {
    /**
     * Valida tamaño/formato de la firma base64. Devuelve la firma saneada o null.
     */
    function eventos_validate_firma(?string $firma): ?string
    {
        if ($firma === null || $firma === '') {
            return null;
        }
        $maxBytes = 512 * 1024;
        if (strlen($firma) > $maxBytes) {
            jsonResponse(['success' => false, 'message' => 'La firma excede el tamaño permitido'], 413);
        }
        if (!preg_match('#^data:image/(png|jpeg|jpg|webp);base64,#i', $firma)) {
            jsonResponse(['success' => false, 'message' => 'Formato de firma inválido'], 400);
        }
        return $firma;
    }
}
