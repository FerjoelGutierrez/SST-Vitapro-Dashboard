<?php
session_start();
require 'conexion.php'; // Tu archivo de conexión PDO

// Recibir datos del formulario (ejemplo)
$reportante = $_POST['reportante'] ?? 'Anónimo';
$incidente = $_POST['descripcion_incidente'] ?? '';

// Validaciones básicas (Importante para que sea profesional)
if (empty($incidente)) {
    die("Error: La descripción es obligatoria.");
}

try {
    // --- PASO 1: GUARDAR EL REPORTE ---
    // En PostgreSQL, usa "RETURNING id" para obtener el ID creado
    $sql_reporte = "INSERT INTO reportes (reportante, descripcion) VALUES (:rep, :desc) RETURNING id";
    $stmt = $pdo->prepare($sql_reporte);
    $stmt->execute([':rep' => $reportante, ':desc' => $incidente]);
    
    // Obtener el ID del reporte recién creado
    $resultado = $stmt->fetch();
    $id_nuevo_reporte = $resultado['id'];

    // --- PASO 2: GUARDAR EN AUDITORIA ---
    // Datos automáticos
    $usuario_id = $_SESSION['user_id'] ?? 0; // Si no hay login, pon 0 o 1 (usuario sistema)
    $ip = $_SERVER['REMOTE_ADDR'];
    $accion = "NUEVO_REPORTE";
    $detalle = "Se creó el reporte ID #$id_nuevo_reporte por $reportante";

    $sql_audit = "INSERT INTO auditoria (usuario_id, accion, descripcion, ip_address) VALUES (:uid, :accion, :desc, :ip)";
    $stmt_audit = $pdo->prepare($sql_audit);
    $stmt_audit->execute([
        ':uid' => $usuario_id,
        ':accion' => $accion,
        ':desc' => $detalle,
        ':ip' => $ip
    ]);

    // --- ÉXITO ---
    // Redirigir con éxito
    header("Location: ../index.php?status=success");
    exit();

} catch (PDOException $e) {
    // Si falla, registrar el error (opcional) y mostrar mensaje
    die("Error al guardar: " . $e->getMessage());
}
?>
