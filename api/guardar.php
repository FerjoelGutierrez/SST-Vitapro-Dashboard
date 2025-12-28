<?php
require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Recibir datos
    $nombre = $_POST['nombre'] ?? 'Anónimo';
    $area = $_POST['area'] ?? 'General';
    $hallazgo = $_POST['hallazgo'] ?? 'No definido';
    $causa = $_POST['causa_especifica'] ?? 'General';
    $riesgo = $_POST['riesgo'] ?? 'Bajo';
    
    // TRUCO: Concatenamos la Acción Inmediata dentro de la descripción para que salga en el reporte
    $desc_raw = $_POST['descripcion'] ?? '';
    $accion = $_POST['accion_inmediata'] ?? '';
    
    // Guardamos un JSON o Texto estructurado si prefieres, pero texto plano es más seguro por ahora
    $descripcion_final = $desc_raw;
    if (!empty($accion)) {
        $descripcion_final .= " || ACCION_TOMADA: " . $accion;
    }

    $sap = $_POST['sap'] ?? '';
    $detuvo = $_POST['detuvo'] ?? 'NO';
    $tipo_usuario = $_POST['tipo_usuario'] ?? 'Interno';
    $empresa = $_POST['empresa'] ?? '';

    // Imagen Base64
    $foto_path = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['foto']['tmp_name'];
        $type = $_FILES['foto']['type'];
        $data = file_get_contents($tmp_name);
        $foto_path = 'data:' . $type . ';base64,' . base64_encode($data);
    }

    try {
        $sql = "INSERT INTO reportes (fecha, nombre, area, tipo_hallazgo, causa_especifica, nivel_riesgo, descripcion, aviso_sap, detuvo_actividad, tipo_usuario, empresa_contratista, foto_path) 
                VALUES (NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nombre, $area, $hallazgo, $causa, $riesgo, $descripcion_final, $sap, $detuvo, $tipo_usuario, $empresa, $foto_path]);

        header("Location: ../index.php?status=success"); // Regresa a la carpeta raíz
        exit;

    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}
?>
