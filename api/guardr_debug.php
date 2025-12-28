<?php
// guardar_debug.php - versión de depuración temporal
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once 'conexion.php';

// Respuesta helper
function respond($data) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(['ok' => false, 'error' => 'Método inválido, use POST', 'method' => $_SERVER['REQUEST_METHOD']]);
}

// helper POST
function post($k, $def = null) {
    return isset($_POST[$k]) ? trim($_POST[$k]) : $def;
}

// Validación mínima
$nombre = post('nombre');
$area = post('area');
$hallazgo = post('hallazgo');
$causa = post('causa_especifica');
$descripcion = post('descripcion');

$errors = [];
if ($nombre === '' || $nombre === null) $errors[] = 'nombre vacío';
if ($area === '' || $area === null) $errors[] = 'area vacío';
if ($hallazgo === '' || $hallazgo === null) $errors[] = 'hallazgo vacío';
if ($causa === '' || $causa === null) $errors[] = 'causa_especifica vacío';
if ($descripcion === '' || $descripcion === null) $errors[] = 'descripcion vacío';

if ($errors) respond(['ok' => false, 'validation_errors' => $errors, 'post_keys' => array_keys($_POST)]);

// Optionals
$accion = post('accion_inmediata', 'No se reportó acción.');
$riesgo = post('riesgo', 'Bajo');
$sap = post('sap', '');
$detuvo = post('detuvo', 'NO');
$tipo_usuario = post('tipo_usuario', 'Interno');
$empresa = post('empresa', '');

// Imagen
$foto_path = null;
if (isset($_FILES['foto'])) {
    $file = $_FILES['foto'];
    // reporta info del archivo
    if ($file['error'] !== UPLOAD_ERR_OK) {
        respond(['ok' => false, 'file_error' => $file['error'], 'file' => $file]);
    }
    // limita tamaño en debug
    $allowed = ['image/jpeg','image/png','image/webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, $allowed, true)) {
        respond(['ok' => false, 'error' => 'mime no permitido', 'mime' => $mime]);
    }

    $data = file_get_contents($file['tmp_name']);
    if ($data === false) respond(['ok' => false, 'error' => 'no se pudo leer archivo tmp']);
    $foto_path = 'data:' . $mime . ';base64,' . base64_encode($data);
}

// Verificar $pdo
if (!isset($pdo) || !($pdo instanceof PDO)) {
    respond(['ok' => false, 'error' => 'No hay conexión PDO válida. Revisa conexion.php', 'pdo_defined' => isset($pdo)]);
}

try {
    $sql = "INSERT INTO reportes (
        fecha, nombre, area, tipo_hallazgo, causa_especifica,
        descripcion, accion_inmediata, nivel_riesgo, aviso_sap,
        detuvo_actividad, tipo_usuario, empresa_contratista, foto_path
    ) VALUES (NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $res = $stmt->execute([
        $nombre, $area, $hallazgo, $causa,
        $descripcion, $accion, $riesgo, $sap,
        $detuvo, $tipo_usuario, $empresa, $foto_path
    ]);
    respond(['ok' => true, 'inserted' => $res, 'rowCount' => $stmt->rowCount()]);
} catch (PDOException $e) {
    // guarda error en un log accesible
    $msg = date('c') . " PDOException: " . $e->getMessage() . "\n";
    file_put_contents(__DIR__ . '/../guardar_debug.log', $msg, FILE_APPEND | LOCK_EX);
    respond(['ok' => false, 'exception' => $e->getMessage()]);
} catch (Exception $e) {
    $msg = date('c') . " Exception: " . $e->getMessage() . "\n";
    file_put_contents(__DIR__ . '/../guardar_debug.log', $msg, FILE_APPEND | LOCK_EX);
    respond(['ok' => false, 'exception' => $e->getMessage()]);
}
?>
