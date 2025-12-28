<?php
require_once 'conexion.php';

if (!isset($_GET['id'])) die("ID no especificado");
$id = $_GET['id'];

// Obtener datos del reporte
$stmt = $pdo->prepare("SELECT * FROM reportes WHERE id = ?");
$stmt->execute([$id]);
$r = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$r) die("Reporte no encontrado");

// Definir colores según riesgo
$colorRiesgo = '#28a745'; // Verde
$textoRiesgo = 'BAJO';
if($r['nivel_riesgo'] == 'Medio') { $colorRiesgo = '#ffc107'; $textoRiesgo = 'MEDIO'; }
if($r['nivel_riesgo'] == 'Alto') { $colorRiesgo = '#dc3545'; $textoRiesgo = 'ALTO'; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte SST #<?php echo $id; ?></title>
    <style>
        body { font-family: Arial, sans-serif; background: #555; margin: 0; padding: 20px; }
        .page { background: white; width: 21cm; min-height: 29.7cm; margin: 0 auto; padding: 2cm; box-shadow: 0 0 10px rgba(0,0,0,0.5); position: relative; }
        .header { border-bottom: 2px solid #003366; padding-bottom: 20px; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center; }
        .logo { color: #003366; font-size: 24px; font-weight: bold; }
        .title { text-align: right; }
        .box { border: 1px solid #ddd; padding: 15px; margin-bottom: 20px; border-radius: 5px; }
        .label { color: #666; font-size: 12px; text-transform: uppercase; font-weight: bold; margin-bottom: 5px; }
        .value { font-size: 16px; color: #000; font-weight: 500; }
        .risk-badge { background: <?php echo $colorRiesgo; ?>; color: white; padding: 5px 15px; border-radius: 4px; font-weight: bold; display: inline-block; }
        .photo-container { text-align: center; margin-top: 20px; border: 2px dashed #ccc; padding: 10px; }
        .photo-container img { max-width: 100%; max-height: 400px; }
        .footer { position: absolute; bottom: 2cm; left: 2cm; right: 2cm; text-align: center; font-size: 11px; color: #999; border-top: 1px solid #eee; padding-top: 10px; }
        
        /* Botón de imprimir que no sale en el papel */
        .btn-print { position: fixed; top: 20px; right: 20px; background: #007bff; color: white; padding: 15px 30px; border: none; border-radius: 50px; font-weight: bold; cursor: pointer; box-shadow: 0 4px 10px rgba(0,0,0,0.3); z-index: 1000; }
        .btn-print:hover { background: #0056b3; }
        
        @media print {
            body { background: white; padding: 0; }
            .page { box-shadow: none; margin: 0; width: 100%; }
            .btn-print { display: none; }
        }
    </style>
</head>
<body>

    <button class="btn-print" onclick="window.print()">🖨️ IMPRIMIR / GUARDAR PDF</button>

    <div class="page">
        <div class="header">
            <div class="logo">VITAPRO <span style="color:#28a745">SST</span></div>
            <div class="title">
                <div style="font-size: 18px; font-weight: bold;">REPORTE DE INCIDENTE</div>
                <div style="font-size: 14px; color: #666;">Folio #<?php echo str_pad($id, 5, "0", STR_PAD_LEFT); ?></div>
            </div>
        </div>

        <div style="display: flex; gap: 20px;">
            <div class="box" style="flex: 1;">
                <div class="label">Fecha y Hora</div>
                <div class="value"><?php echo date('d/m/Y H:i', strtotime($r['fecha'])); ?></div>
            </div>
            <div class="box" style="flex: 1;">
                <div class="label">Nivel de Riesgo</div>
                <div class="risk-badge"><?php echo $r['nivel_riesgo']; ?></div>
            </div>
        </div>

        <div class="box">
            <div class="label">Reportante</div>
            <div class="value"><?php echo htmlspecialchars($r['nombre']); ?></div>
        </div>

        <div style="display: flex; gap: 20px;">
            <div class="box" style="flex: 1;">
                <div class="label">Tipo de Hallazgo</div>
                <div class="value"><?php echo htmlspecialchars($r['tipo_hallazgo']); ?></div>
            </div>
            <div class="box" style="flex: 1;">
                <div class="label">Aviso SAP (Opcional)</div>
                <div class="value"><?php echo $r['aviso_sap'] ? $r['aviso_sap'] : 'N/A'; ?></div>
            </div>
        </div>

        <div class="box">
            <div class="label">Descripción Detallada</div>
            <div class="value" style="line-height: 1.5;">
                <?php echo nl2br(htmlspecialchars($r['descripcion'])); ?>
            </div>
        </div>

        <?php if ($r['foto_path']): ?>
        <div class="label">Evidencia Fotográfica</div>
        <div class="photo-container">
            <img src="../<?php echo $r['foto_path']; ?>" alt="Evidencia">
        </div>
        <?php endif; ?>

        <div class="footer">
            Reporte generado automáticamente por el Sistema de Gestión SST - Vitapro.<br>
            Fecha de impresión: <?php echo date('d/m/Y H:i:s'); ?>
        </div>
    </div>

</body>
</html>
