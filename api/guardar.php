<?php
require_once 'conexion.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitización básica de entrada
    $data = [
        $_POST['tipo_usuario'] ?? 'Anónimo',
        $_POST['nombre'] ?: 'Anónimo',
        $_POST['empresa_contratista'] ?? '',
        $_POST['area'] ?? '',
        $_POST['tipo_hallazgo'] ?? '',
        $_POST['causa_especifica'] ?? '',
        $_POST['nivel_riesgo'] ?? 'Bajo',
        $_POST['aviso_sap'] ?? '',
        $_POST['detuvo_actividad'] ?? 'NO',
        $_POST['descripcion'] ?? '',
        $_POST['foto_url'] ?? '' // URL pública de Supabase Storage
    ];
    try {
        // Query preparada para PostgreSQL
        $sql = "INSERT INTO reportes 
                (tipo_usuario, nombre, empresa_contratista, area, tipo_hallazgo, causa_especifica, nivel_riesgo, aviso_sap, detuvo_actividad, descripcion, foto_path) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($data);
        // Redirección exitosa usando la ruta raíz reescrita por vercel.json
        header('Location: /index?success=1');
        exit;
    } catch (PDOException $e) {
        error_log("Error al insertar reporte: " . $e->getMessage());
        die("Error al procesar el reporte. Intente de nuevo.");
    }
} else {
    header('Location: /');
    exit;
}
?>
