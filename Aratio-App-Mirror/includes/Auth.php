<?php
/**
 * Clase de Autenticación
 */
class Auth {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    /**
     * Login de usuario
     */
    public function login($email, $password) {
        // Validar entrada
        if (empty($email) || empty($password)) {
            return ['success' => false, 'message' => 'Email y contraseña son requeridos'];
        }
        
        if (!isValidEmail($email)) {
            return ['success' => false, 'message' => 'Email inválido'];
        }
        
        // Buscar usuario
        $stmt = $this->db->prepare("
            SELECT * FROM usuarios 
            WHERE email = ? AND estado = 'activo'
        ");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if (!$user) {
            return ['success' => false, 'message' => 'Credenciales inválidas'];
        }
        
        // Verificar password
        if (!verifyPassword($password, $user['password'])) {
            return ['success' => false, 'message' => 'Credenciales inválidas'];
        }
        
        // Crear sesión
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['nombre'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_rol'] = $user['rol'];
        
        // Actualizar último acceso
        $stmt = $this->db->prepare("UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = ?");
        $stmt->execute([$user['id']]);
        
        return [
            'success' => true,
            'message' => 'Login exitoso',
            'user' => [
                'id' => $user['id'],
                'nombre' => $user['nombre'],
                'email' => $user['email'],
                'rol' => $user['rol']
            ]
        ];
    }
    
    /**
     * Logout
     */
    public function logout() {
        $_SESSION = [];
        session_destroy();
        return ['success' => true, 'message' => 'Sesión cerrada'];
    }
    
    /**
     * Registrar nuevo usuario
     */
    public function register($data) {
        // Validar datos
        $required = ['nombre', 'email', 'password'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return ['success' => false, 'message' => "El campo $field es requerido"];
            }
        }
        
        if (!isValidEmail($data['email'])) {
            return ['success' => false, 'message' => 'Email inválido'];
        }
        
        if (strlen($data['password']) < PASSWORD_MIN_LENGTH) {
            return ['success' => false, 'message' => 'La contraseña debe tener al menos ' . PASSWORD_MIN_LENGTH . ' caracteres'];
        }
        
        // Verificar si el email ya existe
        $stmt = $this->db->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$data['email']]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'El email ya está registrado'];
        }
        
        // Crear usuario
        $stmt = $this->db->prepare("
            INSERT INTO usuarios (nombre, email, password, telefono, rol)
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $success = $stmt->execute([
            $data['nombre'],
            $data['email'],
            hashPassword($data['password']),
            $data['telefono'] ?? null,
            $data['rol'] ?? 'colaborador'
        ]);
        
        if ($success) {
            return [
                'success' => true,
                'message' => 'Usuario registrado exitosamente',
                'user_id' => $this->db->lastInsertId()
            ];
        }
        
        return ['success' => false, 'message' => 'Error al registrar usuario'];
    }
    
    /**
     * Cambiar contraseña
     */
    public function changePassword($userId, $currentPassword, $newPassword) {
        // Obtener usuario
        $stmt = $this->db->prepare("SELECT password FROM usuarios WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        if (!$user) {
            return ['success' => false, 'message' => 'Usuario no encontrado'];
        }
        
        // Verificar contraseña actual
        if (!verifyPassword($currentPassword, $user['password'])) {
            return ['success' => false, 'message' => 'Contraseña actual incorrecta'];
        }
        
        // Validar nueva contraseña
        if (strlen($newPassword) < PASSWORD_MIN_LENGTH) {
            return ['success' => false, 'message' => 'La nueva contraseña debe tener al menos ' . PASSWORD_MIN_LENGTH . ' caracteres'];
        }
        
        // Actualizar contraseña
        $stmt = $this->db->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
        $success = $stmt->execute([hashPassword($newPassword), $userId]);
        
        if ($success) {
            return ['success' => true, 'message' => 'Contraseña actualizada exitosamente'];
        }
        
        return ['success' => false, 'message' => 'Error al actualizar contraseña'];
    }
    
    /**
     * Verificar si el usuario tiene acceso a una campaña
     */
    public function hasAccessToCampana($userId, $campanaId) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count 
            FROM usuarios_campanas 
            WHERE usuario_id = ? AND campana_id = ?
        ");
        $stmt->execute([$userId, $campanaId]);
        $result = $stmt->fetch();
        
        return $result['count'] > 0;
    }
    
    /**
     * Obtener campañas del usuario
     */
    public function getUserCampanas($userId) {
        $stmt = $this->db->prepare("
            SELECT c.*, uc.rol_campana
            FROM campanas c
            INNER JOIN usuarios_campanas uc ON c.id = uc.campana_id
            WHERE uc.usuario_id = ?
            ORDER BY c.estado DESC, c.nombre ASC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /**
     * ==========================================
     * RBAC - Control de Acceso Basado en Roles
     * ==========================================
     */

    /**
     * Verificar si el usuario es super-admin
     */
    public function isSuperAdmin($userId = null) {
        if ($userId === null) {
            return isset($_SESSION['user_rol']) && $_SESSION['user_rol'] === 'super-admin';
        }

        $stmt = $this->db->prepare("SELECT rol FROM usuarios WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        return $user && $user['rol'] === 'super-admin';
    }

    /**
     * Verificar si el usuario tiene un rol específico
     */
    public function hasRole($rol, $userId = null) {
        if ($userId === null) {
            $currentRol = $_SESSION['user_rol'] ?? 'colaborador';
            return $currentRol === $rol;
        }

        $stmt = $this->db->prepare("SELECT rol FROM usuarios WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        return $user && $user['rol'] === $rol;
    }

    /**
     * Verificar si el usuario tiene uno de varios roles
     */
    public function hasAnyRole($roles, $userId = null) {
        if (!is_array($roles)) {
            $roles = [$roles];
        }

        if ($userId === null) {
            $currentRol = $_SESSION['user_rol'] ?? 'colaborador';
            return in_array($currentRol, $roles);
        }

        $stmt = $this->db->prepare("SELECT rol FROM usuarios WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        return $user && in_array($user['rol'], $roles);
    }

    /**
     * Verificar si el usuario tiene permisos de administración general
     */
    public function isAdmin($userId = null) {
        return $this->hasAnyRole(['super-admin', 'admin-campana', 'admin', 'admin_diad'], $userId);
    }

    /**
     * Verificar permisos de gestión de usuarios
     */
    public function canManageUsers($userId = null) {
        return $this->hasAnyRole(['super-admin', 'admin', 'supervisor', 'supervisor_diad'], $userId);
    }

    /**
     * Verificar permisos de gestión de campañas
     */
    public function canManageCampanas($userId = null) {
        return $this->hasAnyRole(['super-admin', 'admin-campana', 'admin', 'admin_diad'], $userId);
    }

    /**
     * Verificar permisos de gestión de elecciones
     */
    public function canManageElecciones($userId = null) {
        return $this->hasAnyRole(['super-admin', 'admin'], $userId);
    }

    /**
     * Verificar permisos de gestión de candidatos
     */
    public function canManageCandidatos($userId = null) {
        return $this->hasAnyRole(['super-admin', 'admin-campana', 'admin', 'admin_diad'], $userId);
    }

    /**
     * Verificar permisos de gestión de grupos políticos
     */
    public function canManageGrupos($userId = null) {
        return $this->hasAnyRole(['super-admin', 'admin'], $userId);
    }

    /**
     * Verificar si es un coordinador o líder de alto nivel
     */
    public function isCoordinador($userId = null) {
        return $this->hasAnyRole(['super-admin', 'admin-campana', 'admin', 'supervisor', 'supervisor_diad', 'admin_diad'], $userId);
    }

    /**
     * Obtener información del usuario actual de la sesión
     */
    public function getCurrentUser() {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }

        return [
            'id' => $_SESSION['user_id'],
            'nombre' => $_SESSION['user_name'] ?? '',
            'email' => $_SESSION['user_email'] ?? '',
            'rol' => $_SESSION['user_rol'] ?? 'colaborador'
        ];
    }

    /**
     * Verificar si hay un usuario autenticado
     */
    public function isAuthenticated() {
        return isset($_SESSION['user_id']);
    }

    /**
     * Requerir autenticación (redirige al login si no está autenticado)
     */
    public function requireAuth() {
        if (!$this->isAuthenticated()) {
            header('Location: ' . url('login.php'));
            exit;
        }
    }

    /**
     * Requerir rol específico (redirige al dashboard si no tiene permisos)
     */
    public function requireRole($rol) {
        $this->requireAuth();

        if (!$this->hasRole($rol)) {
            $_SESSION['error_message'] = 'No tienes permisos para acceder a esta sección';
            header('Location: ' . url(''));
            exit;
        }
    }

    /**
     * Requerir super-admin (redirige al dashboard si no es super-admin)
     */
    public function requireSuperAdmin() {
        $this->requireAuth();

        if (!$this->isSuperAdmin()) {
            $_SESSION['error_message'] = 'Esta sección es solo para administradores';
            header('Location: ' . url(''));
            exit;
        }
    }
}
