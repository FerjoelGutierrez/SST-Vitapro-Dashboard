<?php
session_start();
require 'conexion.php';

// 1. RECIBIR DATOS DEL FORMULARIO
// (Asegúrate que los 'name' en tu HTML coincidan con estos $_POST)
$tipo_usuario = $_POST['tipo_usuario'] ?? 'Interno'; // Ej: Interno / Contratista
$nombre = $_POST['nombre'] ?? 'Anónimo';
$empresa = $_POST['empresa_contratista'] ?? 'Vitapro';
$area = $_POST['area'] ?? 'General';
$tipo_hallazgo = $_POST['tipo_hallazgo'] ?? 'No especificado'; // Acto / Condición
$nivel_riesgo = $_POST['nivel_riesgo'] ?? 'Bajo';
$causa = $_POST['causa_especifica'] ?? '';
$descripcion = $_POST['descripcion'] ?? '';
$aviso_sap = $_POST['aviso_sap'] ?? 'N/A';
$detuvo = $_POST['detuvo_actividad'] ?? 'NO';
// $foto_path = ... (La lógica de foto iría aquí, por ahora lo dejamos vacío o NULL)

// 2. VALIDACIÓN
if (empty($descripcion)) {
    die("Error: La descripción es obligatoria.");
}

try {
    // --- PASO 1: GUARDAR EN TU TABLA 'reportes' (La de la imagen) ---
    $sql = "INSERT INTO reportes 
        (tipo_usuario, nombre, empresa_contratista, area, tipo_hallazgo, nivel_riesgo, causa_especifica, descripcion, aviso_sap, detuvo_actividad) 
        VALUES 
        (:tipo, :nom, :emp, :area, :hallazgo, :riesgo, :causa, :desc, :sap, :detuvo) 
        RETURNING id";
        
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':tipo' => $tipo_usuario,
        ':nom' => $nombre,
        ':emp' => $empresa,
        ':area' => $area,
        ':hallazgo' => $tipo_hallazgo,
        ':riesgo' => $nivel_riesgo,
        ':causa' => $causa,
        ':desc' => $descripcion,
        ':sap' => $aviso_sap,
        ':detuvo' => $detuvo
    ]);

    $fila = $stmt->fetch();
    $id_nuevo = $fila['id'];

    // --- PASO 2: AUDITORIA (Igual que antes) ---
    $usuario_audit = $_SESSION['user_id'] ?? 0;
    $ip = $_SERVER['REMOTE_ADDR'];
    $accion = "NUEVO_REPORTE";
    $detalle = "Reporte #$id_nuevo ($tipo_hallazgo) creado por $nombre. SAP: $aviso_sap";

    $stmt_log = $pdo->prepare("INSERT INTO auditoria (usuario_id, accion, descripcion, ip_address) VALUES (?, ?, ?, ?)");
    $stmt_log->execute([$usuario_audit, $accion, $detalle, $ip]);

    // --- FIN ---
    header("Location: ../index.php?status=success");
    exit();

} catch (PDOException $e) {
    die("Error al guardar: " . $e->getMessage());
}
?>
