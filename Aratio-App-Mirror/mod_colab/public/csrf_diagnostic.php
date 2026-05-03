<?php
/**
 * Diagnostic script for CSRF token issue
 */

// Start session
session_start();

header('Content-Type: text/plain; charset=UTF-8');

echo "=== CSRF TOKEN DIAGNOSTIC ===\n\n";

// Check if session is working
echo "1. SESSION STATUS:\n";
echo "   Session ID: " . session_id() . "\n";
echo "   Session status: " . (session_status() === PHP_SESSION_ACTIVE ? 'ACTIVE' : 'INACTIVE') . "\n\n";

// Check CSRF token in session
echo "2. CSRF TOKEN IN SESSION:\n";
if (isset($_SESSION['csrf_token'])) {
    echo "   Token exists: YES\n";
    echo "   Token value: " . $_SESSION['csrf_token'] . "\n";
    echo "   Token length: " . strlen($_SESSION['csrf_token']) . "\n";
} else {
    echo "   Token exists: NO\n";
    echo "   Generating new token...\n";
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    echo "   New token: " . $_SESSION['csrf_token'] . "\n";
}

echo "\n3. SESSION CONTENTS:\n";
echo "   " . print_r($_SESSION, true) . "\n";

// Check Security class
echo "\n4. SECURITY CLASS CHECK:\n";
$securityFile = __DIR__ . '/../src/Utils/Security.php';
if (file_exists($securityFile)) {
    echo "   File exists: YES\n";
    echo "   Last modified: " . date("Y-m-d H:i:s", filemtime($securityFile)) . "\n";

    // Load the class
    require_once __DIR__ . '/../config/config.php';
    require_once $securityFile;

    // Test csrfField() method
    echo "\n5. TESTING csrfField() METHOD:\n";
    try {
        $csrfField = \App\Utils\Security::csrfField();
        echo "   Output: " . htmlspecialchars($csrfField) . "\n";
    } catch (Exception $e) {
        echo "   ERROR: " . $e->getMessage() . "\n";
    }

    // Test checkCsrf() method
    echo "\n6. TESTING checkCsrf() METHOD:\n";
    $_POST['csrf_token'] = $_SESSION['csrf_token'] ?? '';
    try {
        $result = \App\Utils\Security::checkCsrf();
        echo "   Result: " . ($result ? 'TRUE (valid)' : 'FALSE (invalid)') . "\n";
    } catch (Exception $e) {
        echo "   ERROR: " . $e->getMessage() . "\n";
    }

} else {
    echo "   File exists: NO\n";
    echo "   Expected path: $securityFile\n";
}

echo "\n=== END DIAGNOSTIC ===\n";
?>