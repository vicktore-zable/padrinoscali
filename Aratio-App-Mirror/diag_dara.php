<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $pdo = new PDO(
        'mysql:host=auth-db690.hstgr.io;dbname=u156469157_aratio_v1;charset=utf8mb4',
        'u156469157_aratio_v1',
        '15zxCeBbvgsR',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
        );
}
catch (Exception $e) {
    die("ERROR PDO: " . $e->getMessage());
}

// Corregir el campo 'usuario' de 1006054676 → 1006054675 para el user id=18
$upd = $pdo->prepare("UPDATE usuarios SET usuario = '1006054675' WHERE id = 18 AND usuario = '1006054676'");
$upd->execute();
$rows = $upd->rowCount();

echo "<pre>";
if ($rows > 0) {
    echo "✅ CORRECCIÓN APLICADA: usuario actualizado de 1006054676 → 1006054675\n\n";
}
else {
    echo "⚠️ No se actualizó ningún registro (quizás ya fue corregido)\n\n";
}

// Verificar resultado
$s = $pdo->prepare("SELECT id, usuario, nombre, email, activo, tipo_usuario, colaborador_id FROM usuarios WHERE id = 18");
$s->execute();
echo "ESTADO ACTUAL DEL USUARIO:\n";
print_r($s->fetch());

echo "\n✅ Dara ya puede ingresar con:\n";
echo "   Documento: 1006054675\n";
echo "   Contraseña (teléfono): 3205561975\n";
echo "</pre>";
