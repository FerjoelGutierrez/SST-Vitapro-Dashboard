<?php
require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. Recibir datos
    $nombre = $_POST['nombre'] ?? 'Anónimo';
    $area = $_POST['area'] ?? 'General';
    $hallazgo = $_POST['hallazgo'] ?? 'No definido';
    // Si no seleccionan causa específica, ponemos 'General' para evitar error
    $causa = $_POST['causa_especifica'] ?? 'General'; 
    if(trim($causa) == '') $causa = 'General';

    $riesgo = $_POST['riesgo'] ?? 'Bajo';
    $descripcion = $_POST['descripcion'] ?? ''; 
    
    // AQUÍ ESTÁ EL CAMBIO: Recibimos la acción limpia
    $accion = $_POST['accion_inmediata'] ?? 'No se reportó acción inmediata.';
    if(trim($accion) == '') $accion = 'No se reportó acción inmediata.';

    $sap = $_POST['sap'] ?? '';
    $detuvo = $_POST['detuvo'] ?? 'NO';
    $tipo_usuario = $_POST['tipo_usuario'] ?? 'Interno';
    $empresa = $_POST['empresa'] ?? '';

    // 2. Imagen a Base64
    $foto_path = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['foto']['tmp_name'];
        $type = $_FILES['foto']['type'];
        $data = file_get_contents($tmp_name);
        $foto_path = 'data:' . $type . ';base64,' . base64_encode($data);
    }

    try {
        // 3. INSERTAR DATOS (Incluyendo la nueva columna accion_inmediata)
        $sql = "INSERT INTO reportes (
            fecha, nombre, area, tipo_hallazgo, causa_especifica, 
            nivel_riesgo, descripcion, accion_inmediata, aviso_sap, 
            detuvo_actividad, tipo_usuario, empresa_contratista, foto_path
        ) VALUES (
            NOW(), ?, ?, ?, ?, 
            ?, ?, ?, ?, 
            ?, ?, ?, ?
        )";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $nombre, $area, $hallazgo, $causa, 
            $riesgo, $descripcion, $accion, $sap, 
            $detuvo, $tipo_usuario, $empresa, $foto_path
        ]);

        header("Location: ../index.php?status=success");
        exit;

    } catch (PDOException $e) {
        die("Error al guardar en BD: " . $e->getMessage());
    }
}
?>
