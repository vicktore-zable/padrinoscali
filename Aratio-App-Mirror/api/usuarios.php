<?php
/**
 * API: Usuarios
 * Endpoints para CRUD de usuarios (Solo Super-Admin)
 */
require_once __DIR__ . '/../config/config.php';
requireAuth();

$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$user = getSessionUser();
$auth = new Auth();

// El acceso a la API está filtrado por el método.
// GET permite acceso a admin-campana y supervisor bajo filtros.
// POST, PUT, DELETE permiten acceso si canManageUsers, pero los no-super-admin tienen restricciones de campaña y rol.
if (!$auth->canManageUsers()) {
    jsonResponse(['success' => false, 'message' => 'No tienes permisos para acceder a esta información'], 403);
}

$campanaActivaId = $_SESSION['campana_activa'] ?? null;
$isSuperAdmin = $auth->isSuperAdmin();

header('Content-Type: application/json; charset=utf-8');

try {
    switch ($method) {
        case 'GET':
            // Si se proporciona ID, obtener un usuario específico
            if (isset($_GET['id'])) {
                $stmt = $db->prepare("
                    SELECT id, nombre, email, telefono, rol, estado, ultimo_acceso, created_at
                    FROM usuarios
                    WHERE id = ?
                ");
                $stmt->execute([$_GET['id']]);
                $usuario = $stmt->fetch();

                if (!$usuario) {
                    jsonResponse(['success' => false, 'message' => 'Usuario no encontrado'], 404);
                }

                // Si no es super-admin, verificar que el usuario pertenezca a la campaña activa del solicitante
                if (!$isSuperAdmin) {
                    if (!$campanaActivaId) {
                        jsonResponse(['success' => false, 'message' => 'Debe seleccionar una campaña activa'], 400);
                    }
                    $stmtCheck = $db->prepare("SELECT 1 FROM usuarios_campanas WHERE usuario_id = ? AND campana_id = ?");
                    $stmtCheck->execute([$_GET['id'], $campanaActivaId]);
                    if (!$stmtCheck->fetch()) {
                        jsonResponse(['success' => false, 'message' => 'No tienes permisos para ver este usuario'], 403);
                    }
                }

                // Obtener campañas asignadas
                $stmt = $db->prepare("
                    SELECT c.id, c.codigo, c.nombre, uc.rol_campana
                    FROM campanas c
                    INNER JOIN usuarios_campanas uc ON c.id = uc.campana_id
                    WHERE uc.usuario_id = ?
                ");
                $stmt->execute([$_GET['id']]);
                $usuario['campanas'] = $stmt->fetchAll();

                jsonResponse(['success' => true, 'data' => $usuario]);
            } else {
                // Listar todos los usuarios
                $busqueda = $_GET['busqueda'] ?? '';
                $rol = $_GET['rol'] ?? '';

                $sql = "
                    SELECT u.id, u.nombre, u.email, u.telefono, u.rol, u.estado, u.ultimo_acceso, u.created_at
                    FROM usuarios u
                ";
                
                if (!$isSuperAdmin) {
                    if (!$campanaActivaId) {
                        jsonResponse(['success' => true, 'data' => []]); // No campaign, no users
                    }
                    $sql .= " INNER JOIN usuarios_campanas uc ON u.id = uc.usuario_id ";
                }

                $sql .= " WHERE 1=1 ";
                $params = [];

                if (!$isSuperAdmin) {
                    $sql .= " AND uc.campana_id = ? ";
                    $params[] = $campanaActivaId;
                }

                if (!empty($busqueda)) {
                    $sql .= " AND (nombre LIKE ? OR email LIKE ?)";
                    $params[] = "%$busqueda%";
                    $params[] = "%$busqueda%";
                }

                if (!empty($rol)) {
                    $sql .= " AND rol = ?";
                    $params[] = $rol;
                }

                $sql .= " ORDER BY created_at DESC";

                $stmt = $db->prepare($sql);
                $stmt->execute($params);
                $usuarios = $stmt->fetchAll();

                // Para cada usuario, obtener el conteo de campañas asignadas
                foreach ($usuarios as &$usuario) {
                    $stmt = $db->prepare("SELECT COUNT(*) as total FROM usuarios_campanas WHERE usuario_id = ?");
                    $stmt->execute([$usuario['id']]);
                    $result = $stmt->fetch();
                    $usuario['total_campanas'] = (int)$result['total'];
                }

                jsonResponse(['success' => true, 'data' => $usuarios]);
            }
            break;

        case 'POST':
            $data = getJsonInput();

            // Validar campos requeridos
            $required = ['nombre', 'email', 'password', 'rol'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    jsonResponse(['success' => false, 'message' => "El campo $field es requerido"], 400);
                }
            }

            // Validar email
            if (!isValidEmail($data['email'])) {
                jsonResponse(['success' => false, 'message' => 'Email inválido'], 400);
            }

            // Verificar que el email no exista
            $stmt = $db->prepare("SELECT id FROM usuarios WHERE email = ?");
            $stmt->execute([$data['email']]);
            if ($stmt->fetch()) {
                jsonResponse(['success' => false, 'message' => 'El email ya está registrado'], 400);
            }

            // Validar password
            if (strlen($data['password']) < PASSWORD_MIN_LENGTH) {
                jsonResponse(['success' => false, 'message' => 'La contraseña debe tener al menos ' . PASSWORD_MIN_LENGTH . ' caracteres'], 400);
            }

            // Validar rol
            $rolesValidos = ['super-admin', 'admin-campana', 'coordinador', 'colaborador', 'veedor'];
            if (!in_array($data['rol'], $rolesValidos)) {
                jsonResponse(['success' => false, 'message' => 'Rol inválido'], 400);
            }

            // Si no es super-admin, forzar el rol y la campaña
            if (!$isSuperAdmin) {
                if (!$campanaActivaId) {
                    jsonResponse(['success' => false, 'message' => 'Debe seleccionar una campaña activa para crear usuarios'], 400);
                }
                $data['rol'] = 'colaborador'; // Solo pueden crear colaboradores
                $data['campanas'] = [$campanaActivaId]; // Solo en su campaña
            }

            // Insertar usuario
            $stmt = $db->prepare("
                INSERT INTO usuarios (nombre, email, password, telefono, rol, estado)
                VALUES (?, ?, ?, ?, ?, 'activo')
            ");
            $stmt->execute([
                $data['nombre'],
                $data['email'],
                hashPassword($data['password']),
                $data['telefono'] ?? null,
                $data['rol']
            ]);

            $usuarioId = $db->lastInsertId();

            // Asignar campañas si se proporcionaron
            if (!empty($data['campanas']) && is_array($data['campanas'])) {
                $stmtCampana = $db->prepare("
                    INSERT INTO usuarios_campanas (usuario_id, campana_id, rol_campana)
                    VALUES (?, ?, 'colaborador')
                ");
                foreach ($data['campanas'] as $campanaId) {
                    $stmtCampana->execute([$usuarioId, $campanaId]);
                }
            }

            jsonResponse(['success' => true, 'message' => 'Usuario creado exitosamente', 'id' => $usuarioId]);
            break;

        case 'PUT':
            $data = getJsonInput();

            if (empty($data['id'])) {
                jsonResponse(['success' => false, 'message' => 'ID de usuario requerido'], 400);
            }

            // Verificar que el usuario exista
            $stmt = $db->prepare("SELECT id, email FROM usuarios WHERE id = ?");
            $stmt->execute([$data['id']]);
            $usuarioActual = $stmt->fetch();

            if (!$usuarioActual) {
                jsonResponse(['success' => false, 'message' => 'Usuario no encontrado'], 404);
            }

            // Si no es super-admin, verificar que el usuario pertenezca a su campaña
            if (!$isSuperAdmin) {
                if (!$campanaActivaId) {
                    jsonResponse(['success' => false, 'message' => 'Debe seleccionar una campaña activa'], 400);
                }
                $stmtCheck = $db->prepare("SELECT 1 FROM usuarios_campanas WHERE usuario_id = ? AND campana_id = ?");
                $stmtCheck->execute([$data['id'], $campanaActivaId]);
                if (!$stmtCheck->fetch()) {
                    jsonResponse(['success' => false, 'message' => 'No tienes permisos para editar este usuario'], 403);
                }
                
                // No permitir cambiar rol ni campañas para no-super-admin
                unset($data['rol']);
                unset($data['campanas']);
            }

            // Verificar email único (si se está cambiando)
            if (!empty($data['email']) && $data['email'] !== $usuarioActual['email']) {
                if (!isValidEmail($data['email'])) {
                    jsonResponse(['success' => false, 'message' => 'Email inválido'], 400);
                }

                $stmt = $db->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
                $stmt->execute([$data['email'], $data['id']]);
                if ($stmt->fetch()) {
                    jsonResponse(['success' => false, 'message' => 'El email ya está en uso'], 400);
                }
            }

            // Construir query de actualización
            $updates = [];
            $params = [];

            if (!empty($data['nombre'])) {
                $updates[] = "nombre = ?";
                $params[] = $data['nombre'];
            }

            if (!empty($data['email'])) {
                $updates[] = "email = ?";
                $params[] = $data['email'];
            }

            if (!empty($data['telefono'])) {
                $updates[] = "telefono = ?";
                $params[] = $data['telefono'];
            }

            if (!empty($data['rol'])) {
                $rolesValidos = ['super-admin', 'admin-campana', 'coordinador', 'colaborador', 'veedor'];
                if (!in_array($data['rol'], $rolesValidos)) {
                    jsonResponse(['success' => false, 'message' => 'Rol inválido'], 400);
                }
                $updates[] = "rol = ?";
                $params[] = $data['rol'];
            }

            if (isset($data['estado'])) {
                $updates[] = "estado = ?";
                $params[] = $data['estado'];
            }

            // Si se proporciona una nueva contraseña
            if (!empty($data['password'])) {
                if (strlen($data['password']) < PASSWORD_MIN_LENGTH) {
                    jsonResponse(['success' => false, 'message' => 'La contraseña debe tener al menos ' . PASSWORD_MIN_LENGTH . ' caracteres'], 400);
                }
                $updates[] = "password = ?";
                $params[] = hashPassword($data['password']);
            }

            if (empty($updates)) {
                jsonResponse(['success' => false, 'message' => 'No hay datos para actualizar'], 400);
            }

            $params[] = $data['id'];
            $sql = "UPDATE usuarios SET " . implode(', ', $updates) . " WHERE id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);

            // Actualizar campañas asignadas si se proporcionaron
            if (isset($data['campanas']) && is_array($data['campanas'])) {
                // Eliminar asignaciones actuales
                $stmt = $db->prepare("DELETE FROM usuarios_campanas WHERE usuario_id = ?");
                $stmt->execute([$data['id']]);

                // Insertar nuevas asignaciones
                if (!empty($data['campanas'])) {
                    $stmtCampana = $db->prepare("
                        INSERT INTO usuarios_campanas (usuario_id, campana_id, rol_campana)
                        VALUES (?, ?, 'colaborador')
                    ");
                    foreach ($data['campanas'] as $campanaId) {
                        $stmtCampana->execute([$data['id'], $campanaId]);
                    }
                }
            }

            jsonResponse(['success' => true, 'message' => 'Usuario actualizado exitosamente']);
            break;

        case 'DELETE':
            $id = $_GET['id'] ?? null;

            if (empty($id)) {
                jsonResponse(['success' => false, 'message' => 'ID de usuario requerido'], 400);
            }

            // No permitir eliminar el propio usuario
            if ($id == $user['id']) {
                jsonResponse(['success' => false, 'message' => 'No puedes eliminar tu propio usuario'], 400);
            }

            // Verificar que el usuario exista
            $stmt = $db->prepare("SELECT id FROM usuarios WHERE id = ?");
            $stmt->execute([$id]);
            if (!$stmt->fetch()) {
                jsonResponse(['success' => false, 'message' => 'Usuario no encontrado'], 404);
            }

            // Si no es super-admin, verificar pertenencia a campaña
            if (!$isSuperAdmin) {
                if (!$campanaActivaId) {
                    jsonResponse(['success' => false, 'message' => 'Debe seleccionar una campaña activa'], 400);
                }
                $stmtCheck = $db->prepare("SELECT 1 FROM usuarios_campanas WHERE usuario_id = ? AND campana_id = ?");
                $stmtCheck->execute([$id, $campanaActivaId]);
                if (!$stmtCheck->fetch()) {
                    jsonResponse(['success' => false, 'message' => 'No tienes permisos para eliminar este usuario'], 403);
                }
            }

            // Eliminar asignaciones de campañas
            $stmt = $db->prepare("DELETE FROM usuarios_campanas WHERE usuario_id = ?");
            $stmt->execute([$id]);

            // Eliminar usuario
            $stmt = $db->prepare("DELETE FROM usuarios WHERE id = ?");
            $stmt->execute([$id]);

            jsonResponse(['success' => true, 'message' => 'Usuario eliminado exitosamente']);
            break;

        default:
            jsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
    }
} catch (Exception $e) {
    error_log("Error en API usuarios: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()], 500);
}
