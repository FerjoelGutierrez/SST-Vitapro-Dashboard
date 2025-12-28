<?php
/**
 * Archivo para procesar y guardar los reportes SST
 * Maneja la subida de imágenes, almacenamiento en base de datos y envío de alarmas
 */
require_once 'conexion.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}
function limpiarDato($dato) {
    return htmlspecialchars(strip_tags(trim($dato)));
}
// Datos básicos
$tipo_usuario = limpiarDato($_POST['tipo_usuario'] ?? '');
$nombre = !empty($_POST['nombre']) ? limpiarDato($_POST['nombre']) : 'Anónimo';
$empresa_contratista = limpiarDato($_POST['empresa_contratista'] ?? '');
$area = limpiarDato($_POST['area'] ?? '');
$tipo_hallazgo = limpiarDato($_POST['tipo_hallazgo'] ?? '');
$descripcion = limpiarDato($_POST['descripcion'] ?? '');
$nivel_riesgo = limpiarDato($_POST['nivel_riesgo'] ?? '');
$causa_especifica = limpiarDato($_POST['causa_especifica'] ?? '');
// Nuevos campos de seguimiento
$aviso_sap = limpiarDato($_POST['aviso_sap'] ?? '');
$detuvo_actividad = limpiarDato($_POST['detuvo_actividad'] ?? 'NO');
// Procesar imagen
$foto_path = null;
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $dir = 'uploads/';
    if (!file_exists($dir)) mkdir($dir, 0755, true);
    
    $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
    $nombre_archivo = 'reporte_' . date('Ymd_His') . '_' . uniqid() . '.' . $ext;
    $ruta_completa = $dir . $nombre_archivo;
    
    if (move_uploaded_file($_FILES['foto']['tmp_name'], $ruta_completa)) {
        $foto_path = $ruta_completa;
    }
}
try {
    // Insertar en Base de Datos
    $sql = "INSERT INTO reportes (tipo_usuario, nombre, empresa_contratista, area, tipo_hallazgo, descripcion, nivel_riesgo, causa_especifica, foto_path, aviso_sap, detuvo_actividad) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $tipo_usuario, 
        $nombre, 
        $empresa_contratista, 
        $area, 
        $tipo_hallazgo, 
        $descripcion, 
        $nivel_riesgo, 
        $causa_especifica, 
        $foto_path,
        $aviso_sap,
        $detuvo_actividad
    ]);
    
    $ultimo_id = $pdo->lastInsertId();
    // --- LÓGICA DE ALARMA POR CORREO ---
    // Se envía si se detuvo la actividad O si hay un aviso SAP crítico
    if ($detuvo_actividad === 'SI' || !empty($aviso_sap)) {
        $destinatario = "Fgutierrezv@ibalnor.com.ec";
        $asunto = "⚠️ ALARMA SST: " . ($detuvo_actividad === 'SI' ? "PARADA DE ACTIVIDAD" : "Registro SAP #" . $aviso_sap);
        
        $url_informe = "http://" . $_SERVER['HTTP_HOST'] . "/generar_informe.php?id=$ultimo_id";
        $mensaje = "
        <html>
        <body style='font-family: Arial, sans-serif; border: 4px solid #1e3a8a; padding: 25px; max-width: 600px;'>
            <h2 style='color: #1e3a8a; text-align: center;'>⚠️ NOTIFICACIÓN SST VITAPRO</h2>
            <p>Se ha registrado un evento que requiere su atención inmediata.</p>
            <div style='background: #f8fafc; padding: 20px; border-radius: 10px; border: 1px solid #e2e8f0;'>
                <p><b>🏢 Área:</b> $area<br>
                <b>👤 Reportante:</b> $nombre<br>
                <b>🔢 Aviso SAP:</b> " . ($aviso_sap ?: 'N/A') . "<br>
                <b>🛑 ¿Paró actividad?:</b> $detuvo_actividad<br>
                <b>🔥 Riesgo:</b> $nivel_riesgo</p>
            </div>
            <p><b>Descripción:</b><br>$descripcion</p>
            <div style='text-align: center; margin-top: 30px;'>
                <a href='$url_informe' style='background: #1e3a8a; color: white; padding: 15px 25px; text-decoration: none; border-radius: 50px; font-weight: bold; display: inline-block;'>VER INFORME PDF</a>
            </div>
            <p style='color: #94a3b8; font-size: 11px; margin-top: 40px; text-align: center;'>Generado automáticamente por el Sistema SST Vitapro</p>
        </body>
        </html>";
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: Alarma SST Vitapro <no-reply@" . $_SERVER['HTTP_HOST'] . ">" . "\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();
        mail($destinatario, $asunto, $mensaje, $headers);
    }
    header('Location: index.php?success=1');
    exit;
} catch (PDOException $e) {
    error_log("Error al guardar reporte: " . $e->getMessage());
    die("Error: No se pudo guardar el reporte.");
}
?>
