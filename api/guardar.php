<?php
require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // DATOS OBLIGATORIOS
    $nombre = $_POST['nombre'];
    $area = $_POST['area'];
    $hallazgo = $_POST['hallazgo'];
    $causa = $_POST['causa_especifica'];
    $descripcion = $_POST['descripcion'];
    // NUEVO DATO
    $accion = $_POST['accion_inmediata'] ?? 'No se reportó acción.';

    // DATOS OPCIONALES
    $riesgo = $_POST['riesgo'] ?? 'Bajo';
    $sap = $_POST['sap'] ?? '';
    $detuvo = $_POST['detuvo'] ?? 'NO';
    $tipo_usuario = $_POST['tipo_usuario'] ?? 'Interno';
    $empresa = $_POST['empresa'] ?? '';

    // IMAGEN A BASE64
    $foto_path = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['foto']['tmp_name'];
        $type = $_FILES['foto']['type'];
        $data = file_get_contents($tmp_name);
        $foto_path = 'data:' . $type . ';base64,' . base64_encode($data);
    }

    try {
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
            $nombre, $area, $hallazgo, $causa, 
            $descripcion, $accion, $riesgo, $sap, 
            $detuvo, $tipo_usuario, $empresa, $foto_path
        ]);

        header("Location: ../index.php?status=success");
        exit;

    } catch (PDOException $e) {
        // SI FALLA, MUESTRA EL ERROR EN PANTALLA
        die("Error SQL: " . $e->getMessage());
    }
}
?>
