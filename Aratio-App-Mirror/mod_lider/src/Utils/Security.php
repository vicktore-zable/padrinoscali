<?php
/**
 * Clase Security
 * Funciones de seguridad (CSRF, XSS, encriptación)
 *
 * @package App\Utils
 * @author Sistema de Gestión de Colaboradores
 * @version 1.0
 */

namespace App\Utils;

class Security
{
    /**
     * Generar token CSRF
     *
     * @return string
     */
    public static function generateCsrfToken(): string
    {
        if (!isset($_SESSION['csrf_token']) || empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Verificar token CSRF
     *
     * @param string $token
     * @return bool
     */
    public static function verifyCsrfToken(string $token): bool
    {
        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Obtener campo hidden de CSRF para formularios
     *
     * @return string HTML
     */
    public static function csrfField(): string
    {
        $token = self::generateCsrfToken();
        $name = CSRF_TOKEN_NAME;
        return "<input type=\"hidden\" name=\"{$name}\" value=\"{$token}\">";
    }

    /**
     * Verificar CSRF desde request
     *
     * @return bool True si el token es válido, False si no lo es
     */
    public static function checkCsrf(): bool
    {
        $tokenName = CSRF_TOKEN_NAME;

        // Buscar token en POST, GET o headers HTTP
        $token = $_POST[$tokenName]
            ?? $_GET[$tokenName]
            ?? $_SERVER['HTTP_X_CSRF_TOKEN']
            ?? (getallheaders()['X-CSRF-Token'] ?? '')
            ?? '';

        return self::verifyCsrfToken($token);
    }

    /**
     * Sanitizar entrada para prevenir XSS
     *
     * @param string $value
     * @return string
     */
    public static function sanitize(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Sanitizar array recursivamente
     *
     * @param array $data
     * @return array
     */
    public static function sanitizeArray(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = self::sanitizeArray($value);
            } elseif (is_string($value)) {
                $data[$key] = self::sanitize($value);
            }
        }
        return $data;
    }

    /**
     * Limpiar entrada (trim y sanitize)
     *
     * @param string $value
     * @return string
     */
    public static function clean(string $value): string
    {
        return self::sanitize(trim($value));
    }

    /**
     * Encriptar datos sensibles
     *
     * @param string $data
     * @param string|null $key
     * @return string
     */
    public static function encrypt(string $data, ?string $key = null): string
    {
        $key = $key ?? SECURITY_KEY;
        $iv = random_bytes(16);
        $encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);
        return base64_encode($iv . $encrypted);
    }

    /**
     * Desencriptar datos
     *
     * @param string $data
     * @param string|null $key
     * @return string|false
     */
    public static function decrypt(string $data, ?string $key = null)
    {
        $key = $key ?? SECURITY_KEY;
        $data = base64_decode($data);
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);
        return openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, $iv);
    }

    /**
     * Generar hash seguro
     *
     * @param string $data
     * @return string
     */
    public static function hash(string $data): string
    {
        return hash('sha256', $data);
    }

    /**
     * Generar token aleatorio
     *
     * @param int $length
     * @return string
     */
    public static function generateToken(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }

    /**
     * Validar contraseña segura
     *
     * @param string $password
     * @return array ['valid' => bool, 'errors' => array]
     */
    public static function validatePassword(string $password): array
    {
        $errors = [];
        $config = PASSWORD_CONFIG;

        if (strlen($password) < $config['min_length']) {
            $errors[] = "Debe tener al menos {$config['min_length']} caracteres";
        }

        if ($config['require_uppercase'] && !preg_match('/[A-Z]/', $password)) {
            $errors[] = "Debe contener al menos una mayúscula";
        }

        if ($config['require_lowercase'] && !preg_match('/[a-z]/', $password)) {
            $errors[] = "Debe contener al menos una minúscula";
        }

        if ($config['require_number'] && !preg_match('/[0-9]/', $password)) {
            $errors[] = "Debe contener al menos un número";
        }

        if ($config['require_special'] && !preg_match('/[@$!%*?&]/', $password)) {
            $errors[] = "Debe contener al menos un carácter especial (@$!%*?&)";
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Aplicar headers de seguridad
     */
    public static function setSecurityHeaders(): void
    {
        foreach (SECURITY_HEADERS as $header => $value) {
            header("$header: $value");
        }
    }

    /**
     * Prevenir clickjacking
     */
    public static function preventClickjacking(): void
    {
        header('X-Frame-Options: DENY');
        header('Content-Security-Policy: frame-ancestors \'none\'');
    }

    /**
     * Verificar si la petición es HTTPS
     *
     * @return bool
     */
    public static function isHttps(): bool
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || $_SERVER['SERVER_PORT'] == 443
            || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    }

    /**
     * Forzar HTTPS
     */
    public static function forceHttps(): void
    {
        if (!self::isHttps() && APP_ENV === 'production') {
            $redirect = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            header('HTTP/1.1 301 Moved Permanently');
            header('Location: ' . $redirect);
            exit();
        }
    }

    /**
     * Obtener IP del cliente
     *
     * @return string
     */
    public static function getClientIp(): string
    {
        $ip = '';

        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        }

        // Validar IP
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            return $ip;
        }

        return '0.0.0.0';
    }

    /**
     * Verificar rate limiting
     *
     * @param string $key Identificador único (ej: 'login_' . $ip)
     * @param int $maxAttempts
     * @param int $timeWindow Segundos
     * @return bool True si está dentro del límite
     */
    public static function checkRateLimit(string $key, int $maxAttempts, int $timeWindow): bool
    {
        $cacheFile = CACHE_PATH . '/rate_limit_' . md5($key) . '.json';

        if (file_exists($cacheFile)) {
            $data = json_decode(file_get_contents($cacheFile), true);
            $attempts = $data['attempts'] ?? 0;
            $firstAttempt = $data['first_attempt'] ?? time();

            // Resetear si pasó el tiempo
            if (time() - $firstAttempt > $timeWindow) {
                $attempts = 0;
                $firstAttempt = time();
            }

            // Verificar límite
            if ($attempts >= $maxAttempts) {
                return false;
            }

            $attempts++;
        } else {
            $attempts = 1;
            $firstAttempt = time();
        }

        // Guardar
        file_put_contents($cacheFile, json_encode([
            'attempts' => $attempts,
            'first_attempt' => $firstAttempt
        ]));

        return true;
    }

    /**
     * Limpiar rate limit
     *
     * @param string $key
     */
    public static function clearRateLimit(string $key): void
    {
        $cacheFile = CACHE_PATH . '/rate_limit_' . md5($key) . '.json';
        if (file_exists($cacheFile)) {
            unlink($cacheFile);
        }
    }

    /**
     * Generar código 2FA
     *
     * @param string $secret
     * @return string
     */
    public static function generate2FACode(string $secret): string
    {
        $time = floor(time() / 30);
        $hash = hash_hmac('sha1', pack('N*', 0) . pack('N*', $time), base64_decode($secret), true);
        $offset = ord($hash[strlen($hash) - 1]) & 0xf;
        $code = (
            ((ord($hash[$offset + 0]) & 0x7f) << 24) |
            ((ord($hash[$offset + 1]) & 0xff) << 16) |
            ((ord($hash[$offset + 2]) & 0xff) << 8) |
            (ord($hash[$offset + 3]) & 0xff)
        ) % 1000000;
        return str_pad($code, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Verificar código 2FA
     *
     * @param string $secret
     * @param string $code
     * @param int $window Ventana de tiempo (30s por defecto)
     * @return bool
     */
    public static function verify2FACode(string $secret, string $code, int $window = 1): bool
    {
        for ($i = -$window; $i <= $window; $i++) {
            $time = floor(time() / 30) + $i;
            $hash = hash_hmac('sha1', pack('N*', 0) . pack('N*', $time), base64_decode($secret), true);
            $offset = ord($hash[strlen($hash) - 1]) & 0xf;
            $testCode = (
                ((ord($hash[$offset + 0]) & 0x7f) << 24) |
                ((ord($hash[$offset + 1]) & 0xff) << 16) |
                ((ord($hash[$offset + 2]) & 0xff) << 8) |
                (ord($hash[$offset + 3]) & 0xff)
            ) % 1000000;
            $testCode = str_pad($testCode, 6, '0', STR_PAD_LEFT);

            if (hash_equals($testCode, $code)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Generar secret para 2FA
     *
     * @return string
     */
    public static function generate2FASecret(): string
    {
        return base64_encode(random_bytes(32));
    }

    /**
     * Generar QR code URL para 2FA
     *
     * @param string $email
     * @param string $secret
     * @param string|null $issuer
     * @return string
     */
    public static function get2FAQRCodeUrl(string $email, string $secret, ?string $issuer = null): string
    {
        $issuer = $issuer ?? TWO_FA_CONFIG['issuer'];
        $label = urlencode($issuer . ':' . $email);
        $secret = str_replace('=', '', base32_encode($secret));

        return "otpauth://totp/{$label}?secret={$secret}&issuer=" . urlencode($issuer);
    }

    /**
     * Sanitizar nombre de archivo
     *
     * @param string $filename
     * @return string
     */
    public static function sanitizeFilename(string $filename): string
    {
        // Remover caracteres peligrosos
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);

        // Remover múltiples puntos consecutivos
        $filename = preg_replace('/\.+/', '.', $filename);

        // Limitar longitud
        if (strlen($filename) > 255) {
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            $name = substr(pathinfo($filename, PATHINFO_FILENAME), 0, 255 - strlen($ext) - 1);
            $filename = $name . '.' . $ext;
        }

        return $filename;
    }

    /**
     * Validar tipo de archivo
     *
     * @param string $filename
     * @param array $allowedTypes
     * @return bool
     */
    public static function isAllowedFileType(string $filename, array $allowedTypes): bool
    {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return in_array($ext, $allowedTypes);
    }

    /**
     * Obtener tipo MIME real de un archivo
     *
     * @param string $filepath
     * @return string|false
     */
    public static function getRealMimeType(string $filepath)
    {
        if (!file_exists($filepath)) {
            return false;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $filepath);
        finfo_close($finfo);

        return $mimeType;
    }

    /**
     * Prevenir path traversal
     *
     * @param string $path
     * @param string $basePath
     * @return bool True si el path es seguro
     */
    public static function isPathSafe(string $path, string $basePath): bool
    {
        $realPath = realpath($path);
        $realBasePath = realpath($basePath);

        if ($realPath === false || $realBasePath === false) {
            return false;
        }

        return strpos($realPath, $realBasePath) === 0;
    }

    /**
     * Escapar para SQL (usar solo cuando no se puedan usar prepared statements)
     *
     * @deprecated Usar prepared statements en su lugar
     * @param string $value
     * @return string
     */
    public static function escapeSql(string $value): string
    {
        return addslashes($value);
    }

    /**
     * Generar password aleatorio seguro
     *
     * @param int $length
     * @return string
     */
    public static function generatePassword(int $length = 12): string
    {
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $numbers = '0123456789';
        $special = '@$!%*?&';
        $all = $uppercase . $lowercase . $numbers . $special;

        $password = '';
        $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
        $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        $password .= $special[random_int(0, strlen($special) - 1)];

        for ($i = 4; $i < $length; $i++) {
            $password .= $all[random_int(0, strlen($all) - 1)];
        }

        return str_shuffle($password);
    }
}

/**
 * Helper function para base32_encode
 */
if (!function_exists('base32_encode')) {
    function base32_encode($input)
    {
        $base32chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $output = '';
        $v = 0;
        $vbits = 0;

        for ($i = 0, $j = strlen($input); $i < $j; $i++) {
            $v <<= 8;
            $v += ord($input[$i]);
            $vbits += 8;

            while ($vbits >= 5) {
                $vbits -= 5;
                $output .= $base32chars[$v >> $vbits];
                $v &= ((1 << $vbits) - 1);
            }
        }

        if ($vbits > 0) {
            $v <<= (5 - $vbits);
            $output .= $base32chars[$v];
        }

        return $output;
    }
}
