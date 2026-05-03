<?php
/**
 * DatabaseManager - Singleton para gestión de conexiones persistentes
 * Reduce el throttling de conexiones en Hostinger mediante reutilización
 */

class DatabaseManager {
    private static $instance = null;
    private $connection = null;
    private $config = null;
    private $queryCount = 0;
    private $slowQueryThreshold = 1.0; // segundos
    
    /**
     * Constructor privado para patrón Singleton
     */
    private function __construct() {
        // La configuración ya debería estar cargada via config.php
        if (defined('DB_HOST')) {
            $this->config = [
                'host' => DB_HOST,
                'database' => DB_NAME,
                'username' => DB_USER,
                'password' => DB_PASS,
                'charset' => DB_CHARSET ?? 'utf8mb4'
            ];
        } else {
            // Fallback para scripts que no usen el flujo normal
            if (file_exists(dirname(__DIR__) . '/config/config.php')) {
                require_once dirname(__DIR__) . '/config/config.php';
                $this->config = [
                    'host' => DB_HOST,
                    'database' => DB_NAME,
                    'username' => DB_USER,
                    'password' => DB_PASS,
                    'charset' => DB_CHARSET ?? 'utf8mb4'
                ];
            }
        }
    }
    
    /**
     * Obtener instancia única (Singleton)
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Obtener conexión PDO reutilizable
     */
    public function getConnection() {
        if ($this->connection === null || !$this->isConnected()) {
            $this->connect();
        }
        return $this->connection;
    }
    
    /**
     * Establecer conexión persistente a MySQL
     */
    private function connect() {
        try {
            $dsn = sprintf(
                "mysql:host=%s;dbname=%s;charset=%s",
                $this->config['host'],
                $this->config['database'],
                $this->config['charset']
            );
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => true, // ⭐ Conexión persistente
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . $this->config['charset'],
                PDO::ATTR_TIMEOUT => 5 // Timeout de 5 segundos
            ];
            
            $this->connection = new PDO(
                $dsn,
                $this->config['username'],
                $this->config['password'],
                $options
            );

            // Sincronizar zona horaria con PHP (America/Bogota -05:00)
            $offset = date('P');
            $this->connection->exec("SET time_zone = '{$offset}'");
            
            error_log("DatabaseManager: Conexión establecida (persistente)");
        } catch (PDOException $e) {
            error_log("DatabaseManager Error: " . $e->getMessage());
            throw new Exception("No se pudo conectar a la base de datos");
        }
    }
    
    /**
     * Verificar si la conexión está activa
     */
    private function isConnected() {
        try {
            if ($this->connection === null) {
                return false;
            }
            $this->connection->query('SELECT 1');
            return true;
        } catch (PDOException $e) {
            error_log("DatabaseManager: Conexión perdida, reconectando...");
            return false;
        }
    }
    
    /**
     * Ejecutar query con logging de queries lentas
     */
    public function query($sql, $params = []) {
        $startTime = microtime(true);
        $conn = $this->getConnection();
        
        try {
            if (empty($params)) {
                $result = $conn->query($sql);
            } else {
                $stmt = $conn->prepare($sql);
                $stmt->execute($params);
                $result = $stmt;
            }
            
            $this->queryCount++;
            $queryTime = microtime(true) - $startTime;
            
            // Log de queries lentas
            if ($queryTime > $this->slowQueryThreshold) {
                error_log(sprintf(
                    "Slow Query [%.3fs]: %s",
                    $queryTime,
                    substr($sql, 0, 100)
                ));
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Query Error: " . $e->getMessage() . " | SQL: " . $sql);
            throw $e;
        }
    }
    
    /**
     * Obtener estadísticas de uso
     */
    public function getStats() {
        return [
            'query_count' => $this->queryCount,
            'is_connected' => $this->isConnected(),
            'persistent' => true
        ];
    }
    
    /**
     * Prevenir clonación
     */
    private function __clone() {}
    
    /**
     * Prevenir deserialización
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}
