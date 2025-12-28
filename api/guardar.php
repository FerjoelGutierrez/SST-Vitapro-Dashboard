<?php
require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. Recibir datos del formulario con validación básica
    $nombre = $_POST['nombre'] ?? 'Anónimo';
    $area = $_POST['area'] ?? 'Sin área';
    $hallazgo = $_POST['hallazgo'] ?? 'No definido'; // Tipo de Hallazgo (Acto/Condición)
    $riesgo = $_POST['riesgo'] ?? 'Bajo';
    $descripcion = $_POST['descripcion'] ?? ''; 
    $sap = $_POST['sap'] ?? '';
    $detuvo = $_POST['detuvo'] ?? 'NO';
    $tipo_usuario = $_POST['tipo_usuario'] ?? 'Interno';
    $empresa = $_POST['empresa'] ?? '';
    
    // CORRECCIÓN DEL ERROR SQL: Capturamos la causa específica
    // Intentamos varios nombres comunes por si acaso, si no, ponemos "General"
    $causa = $_POST['clasificacion_especifica'] ?? $_POST['causa_especifica'] ?? $_POST['causa'] ?? 'General';
    
    // Si llegó vacía, forzamos un texto para evitar el error "Not null violation"
    if (trim($causa) === '') {
        $causa = 'No especificado';
    }

    // 2. PROCESAR IMAGEN A BASE64 (Para que se vea en Vercel)
    $foto_path = null;
    
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['foto']['tmp_name'];
        $type = $_FILES['foto']['type'];
        
        // Leemos el archivo y lo convertimos a texto Base64
        $data = file_get_contents($tmp_name);
        $foto_path = 'data:' . $type . ';base64,' . base64_encode($data);
    }

    try {
        // 3. INSERTAR EN LA BASE DE DATOS
        // Agregamos 'causa_especifica' a la consulta
        $sql = "INSERT INTO reportes (
                    fecha, nombre, area, tipo_hallazgo, causa_especifica, 
                    nivel_riesgo, descripcion, aviso_sap, detuvo_actividad, 
                    tipo_usuario, empresa_contratista, foto_path
                ) VALUES (
                    NOW(), ?, ?, ?, ?, 
                    ?, ?, ?, ?, 
                    ?, ?, ?
                )";
        
        $stmt = $pdo->prepare($sql);
        // El orden de las variables debe coincidir EXACTAMENTE con los ? de arriba
        $stmt->execute([
            $nombre, 
            $area, 
            $hallazgo, 
            $causa,         // Aquí va la variable que faltaba
            $riesgo, 
            $descripcion, 
            $sap, 
            $detuvo, 
            $tipo_usuario, 
            $empresa, 
            $foto_path      // La imagen convertida
        ]);

        // Redirigir al éxito
        header("Location: index.php?status=success");
        exit;

    } catch (PDOException $e) {
        // Mensaje de error más amigable
        echo "<div style='font-family:sans-serif; padding:20px; color:red;'>";
        echo "<h3>Error al guardar el reporte</h3>";
        echo "<p>Detalle técnico: " . $e->getMessage() . "</p>";
        echo "<p>Verifica que todas las columnas en la base de datos coincidan con el código.</p>";
        echo "<a href='index.php'>Volver al formulario</a>";
        echo "</div>";
        exit;
    }
} else {
    // Si intentan entrar directo a guardar.php sin enviar datos
    header("Location: index.php");
    exit;
}
?>
