<?php
require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Recibir datos del formulario
    $nombre = $_POST['nombre'] ?? '';
    $area = $_POST['area'] ?? '';
    $hallazgo = $_POST['hallazgo'] ?? '';
    $riesgo = $_POST['riesgo'] ?? '';
    $descripcion = $_POST['descripcion'] ?? ''; // Asegúrate que tu BD tenga esta columna (o detalle)
    $sap = $_POST['sap'] ?? '';
    $detuvo = $_POST['detuvo'] ?? 'NO';
    $tipo_usuario = $_POST['tipo_usuario'] ?? 'Interno';
    $empresa = $_POST['empresa'] ?? '';

    // PROCESAR IMAGEN A BASE64 (Solución Vercel)
    $foto_path = null;
    
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['foto']['tmp_name'];
        $type = $_FILES['foto']['type'];
        
        // Leemos el archivo binario
        $data = file_get_contents($tmp_name);
        
        // Lo convertimos a base64
        // Esto crea un string largo tipo: "data:image/jpeg;base64,/9j/4AAQSkZJRg..."
        $foto_path = 'data:' . $type . ';base64,' . base64_encode($data);
    }

    try {
        // Insertar en Base de Datos
        // NOTA: Asegúrate que la columna 'foto_path' en Supabase sea de tipo TEXT (no varchar limitado)
        $sql = "INSERT INTO reportes (fecha, nombre, area, tipo_hallazgo, nivel_riesgo, descripcion, aviso_sap, detuvo_actividad, tipo_usuario, empresa_contratista, foto_path) 
                VALUES (NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nombre, $area, $hallazgo, $riesgo, $descripcion, $sap, $detuvo, $tipo_usuario, $empresa, $foto_path]);

        // Redirigir al éxito o volver al form
        header("Location: index.php?status=success");
        exit;

    } catch (PDOException $e) {
        die("Error al guardar: " . $e->getMessage());
    }
}
?>
