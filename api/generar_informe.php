<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    die("Acceso denegado");
}
require_once 'conexion.php';
$id = $_GET['id'] ?? 0;
// Incluimos todas las columnas necesarias
$stmt = $pdo->prepare("SELECT * FROM reportes WHERE id = ?");
$stmt->execute([$id]);
$r = $stmt->fetch();
if (!$r) die("Reporte no encontrado");
function getBadgeColor($nivel) {
    switch($nivel) {
        case 'Alto': return '#ef4444';
        case 'Medio': return '#f59e0b';
        case 'Bajo': return '#10b981';
        default: return '#6b7280';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Informe de Hallazgo SST - Vitapro</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; color: #1e293b; line-height: 1.6; padding: 40px; background: #f8fafc; }
        .document-page { background: white; max-width: 850px; margin: 0 auto; padding: 60px; box-shadow: 0 0 40px rgba(0,0,0,0.05); border-radius: 4px; }
        
        .header { display: flex; justify-content: space-between; align-items: start; border-bottom: 4px solid #1e3a8a; padding-bottom: 25px; margin-bottom: 40px; }
        .logo-box h1 { font-size: 26px; font-weight: 800; color: #1e3a8a; margin: 0; letter-spacing: -0.5px; }
        .logo-box p { font-size: 14px; margin: 0; color: #64748b; font-weight: 600; }
        
        .report-meta { text-align: right; }
        .id-badge { background: #f1f5f9; color: #1e3a8a; padding: 8px 15px; border-radius: 8px; font-weight: 700; font-size: 18px; display: inline-block; }
        .date-text { color: #94a3b8; font-size: 13px; margin-top: 5px; font-weight: 500; }
        
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 40px; }
        .info-item { background: #f8fafc; padding: 18px; border-radius: 12px; border: 1px solid #f1f5f9; }
        .label { font-size: 11px; color: #94a3b8; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 6px; }
        .value { font-size: 16px; font-weight: 600; color: #1e293b; }
        
        .riesgo-tag { display: inline-block; padding: 5px 15px; border-radius: 50px; color: white; font-weight: 700; font-size: 13px; text-transform: uppercase; }
        
        .section-header { font-size: 14px; font-weight: 700; color: #1e3a8a; border-left: 4px solid #1e3a8a; padding-left: 12px; margin: 40px 0 20px; text-transform: uppercase; letter-spacing: 0.5px; }
        
        .text-box { background: #fff; padding: 25px; border: 1px solid #e2e8f0; border-radius: 12px; font-size: 15px; min-height: 120px; }
        
        .photo-section { text-align: center; margin-top: 30px; }
        .photo-section img { max-width: 100%; border-radius: 16px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); border: 8px solid white; }
        
        .btn-print { position: fixed; bottom: 30px; right: 30px; background: #1e3a8a; color: white; border: none; padding: 15px 30px; border-radius: 50px; font-weight: 700; cursor: pointer; box-shadow: 0 10px 15px rgba(30,58,138,0.3); transition: 0.3s; z-index: 1000; }
        .btn-print:hover { transform: translateY(-5px); box-shadow: 0 15px 25px rgba(30,58,138,0.4); }
        
        @media print {
            body { padding: 0; background: white; }
            .document-page { box-shadow: none; padding: 0; max-width: 100%; }
            .btn-print { display: none; }
        }
    </style>
</head>
<body>
    <button class="btn-print" onclick="window.print()">
        <i class="fas fa-print"></i> DESCARGAR INFORME EN PDF
    </button>
    <div class="document-page">
        <div class="header">
            <div class="logo-box">
                <h1>VITAPRO</h1>
                <p>GESTIÓN DE SEGURIDAD Y SALUD EN EL TRABAJO</p>
            </div>
            <div class="report-meta">
                <div class="id-badge">INFORME SST #<?php echo str_pad($r['id'], 5, '0', STR_PAD_LEFT); ?></div>
                <div class="date-text">Emitido el <?php echo date('d/m/Y', strtotime($r['fecha'])); ?> a las <?php echo date('H:i', strtotime($r['fecha'])); ?></div>
            </div>
        </div>
        <div class="info-grid">
            <div class="info-item">
                <div class="label">Reportado por</div>
                <div class="value"><?php echo $r['nombre'] ?: 'Anónimo'; ?></div>
                <div style="font-size: 12px; color: #64748b; font-weight: 500;"><?php echo $r['tipo_usuario']; ?> <?php echo $r['empresa_contratista'] ? '/ ' . $r['empresa_contratista'] : ''; ?></div>
            </div>
            <div class="info-item">
                <div class="label">Ubicación / Área</div>
                <div class="value"><?php echo $r['area']; ?></div>
            </div>
            <div class="info-item">
                <div class="label">Clasificación del Hallazgo</div>
                <div class="value"><?php echo $r['tipo_hallazgo']; ?></div>
            </div>
            <div class="info-item">
                <div class="label">Nivel de Riesgo Evaluado</div>
                <div class="value">
                    <span class="riesgo-tag" style="background: <?php echo getBadgeColor($r['nivel_riesgo']); ?>">
                        <?php echo $r['nivel_riesgo']; ?>
                    </span>
                </div>
            </div>
        </div>
        <div class="info-item" style="margin-bottom: 20px;">
            <div class="label">Causa Específica Identificada</div>
            <div class="value"><?php echo $r['causa_especifica']; ?></div>
        </div>
        <div class="section-header">Descripción Detallada</div>
        <div class="text-box">
            <?php echo nl2br(htmlspecialchars($r['descripcion'])); ?>
        </div>
        <?php if ($r['foto_path']): ?>
        <div class="section-header">Evidencia Fotográfica</div>
        <div class="photo-section">
            <img src="<?php echo $r['foto_path']; ?>" alt="Evidencia SST">
        </div>
        <?php endif; ?>
        <div style="margin-top: 80px; text-align: center;">
            <div style="width: 200px; border-top: 1px solid #1e293b; margin: 0 auto; padding-top: 10px; font-weight: 700; font-size: 12px; text-transform: uppercase;">
                Firma Responsable SST
            </div>
        </div>
        <div style="margin-top: 60px; border-top: 1px solid #f1f5f9; padding-top: 20px; font-size: 11px; color: #94a3b8; text-align: center; font-weight: 500;">
            Este es un documento oficial generado por la plataforma de Gestión SST Vitapro. <br>
            Cualquier alteración parcial o total de este documento invalida su legitimidad.
        </div>
    </div>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</body>
</html>
