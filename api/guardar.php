<?php
// guardar.php
require_once 'conexion.php'; // Debe definir $pdo (PDO)

/**
 * Nota: Asegúrate de que 'conexion.php' crea $pdo y establece
 * PDO::ERRMODE_EXCEPTION. Más abajo incluyo un ejemplo.
 */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php?status=invalid_method');
    exit;
}

// Helper para obtener POST seguros (trim)
function post($key, $default = null) {
    return isset($_POST[$key]) ? trim($_POST[$key]) : $default;
}

// Campos obligatorios
$nombre = post('nombre');
$area = post('area');
$hallazgo = post('hallazgo');
$causa = post('causa_especifica');
$descripcion = post('descripcion');

// Validación básica de obligatorios
$errors = [];
if ($nombre === '' || $nombre === null) $errors[] = 'Nombre es obligatorio.';
if ($area === '' || $area === null) $errors[] = 'Área es obligatoria.';
if ($hallazgo === '' || $hallazgo === null) $errors[] = 'Tipo de hallazgo es obligatorio.';
if ($causa === '' || $causa === null) $errors[] = 'Causa específica es obligatoria.';
if ($descripcion === '' || $descripcion === null) $errors[] = 'Descripción es obligatoria.';

if (!empty($errors)) {
    // En producción redirige con error; en desarrollo puedes debuggear.
    $msg = urlencode(implode(' | ', $errors));
    header("Location: ../index.php?status=validation_error&msg={$msg}");
    exit;
}

// Campos opcionales con valores por defecto
$accion = post('accion_inmediata', 'No se reportó acción.');
$riesgo = post('riesgo', 'Bajo');
$sap = post('sap', '');
$detuvo = post('detuvo', 'NO');
$tipo_usuario = post('tipo_usuario', 'Interno');
$empresa = post('empresa', '');

// Procesamiento de la imagen (opcional)
// Recomiendo guardar en filesystem y almacenar solo la ruta en la BD.
// Aquí mantengo la opción base64 pero valida tipo y tamaño.
$foto_path = null;
if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
    $file = $_FILES['foto'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        // Puedes mapear errores más detalladamente
        header("Location: ../index.php?status=file_error&code={$file['error']}");
        exit;
    }

    // Límites y tipos permitidos
    $maxBytes = 2 * 1024 * 1024; // 2 MB
    $allowed = ['image/jpeg', 'image/png', 'image/webp'];

    if ($file['size'] > $maxBytes) {
        header('Location: ../index.php?status=file_too_large');
        exit;
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, $allowed, true)) {
        header('Location: ../index.php?status=invalid_mime');
        exit;
    }

    // Verificar que realmente sea imagen
    $imgInfo = @getimagesize($file['tmp_name']);
    if ($imgInfo === false) {
        header('Location: ../index.php?status=not_image');
        exit;
    }

    // Leer y convertir a base64 (si quieres guardar en BD)
    $data = file_get_contents($file['tmp_name']);
    if ($data === false) {
        header('Location: ../index.php?status=file_read_error');
        exit;
    }
    $foto_path = 'data:' . $mime . ';base64,' . base64_encode($data);
    // Alternativa recomendada: mover el archivo a una carpeta y guardar la ruta:
    // $dest = __DIR__ . '/../uploads/' . uniqid('', true) . image_type_to_extension($imgInfo[2]);
    // move_uploaded_file($file['tmp_name'], $dest);
    // $foto_path = '/uploads/' . basename($dest);
}

try {
    // Asegúrate que $pdo está definido en conexion.php
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        throw new Exception('No hay conexión PDO válida. Revisa conexion.php');
    }

    $sql = "INSERT INTO reportes (
        fecha, nombre, area, tipo_hallazgo, causa_especifica, 
        descripcion, accion_inmediata, nivel_riesgo, aviso_sap, 
        detuvo_actividad, tipo_usuario, empresa_contratista, foto_path
    ) VALUES (
        NOW(), ?, ?, ?, ?, 
        ?, ?, ?, ?, 
        ?, ?, ?, ?
    )";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $nombre,
        $area,
        $hallazgo,
        $causa,
        $descripcion,
        $accion,
        $riesgo,
        $sap,
        $detuvo,
        $tipo_usuario,
        $empresa,
        $foto_path
    ]);

    header("Location: ../index.php?status=success");
    exit;
} catch (PDOException $e) {
    // En desarrollo muestra el error; en producción loguéalo y muestra mensaje genérico.
    // error_log($e->getMessage());
    header('Location: ../index.php?status=db_error&msg=' . urlencode($e->getMessage()));
    exit;
} catch (Exception $e) {
    header('Location: ../index.php?status=error&msg=' . urlencode($e->getMessage()));
    exit;
}
?>
