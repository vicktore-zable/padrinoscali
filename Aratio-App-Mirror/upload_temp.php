<?php
/**
 * Upload temporal - borrar después de usar
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $target = __DIR__ . '/' . basename($_FILES['file']['name']);
    if (move_uploaded_file($_FILES['file']['tmp_name'], $target)) {
        echo json_encode(['success' => true, 'path' => $target]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Move failed']);
    }
    exit;
}
?>
<!DOCTYPE html>
<html><body>
<form method="POST" enctype="multipart/form-data">
    <input type="file" name="file">
    <button>Subir</button>
</form>
</body></html>