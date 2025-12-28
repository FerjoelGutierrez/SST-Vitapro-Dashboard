<?php
require_once 'conexion.php';
$id = $_GET['id'] ?? die("Acceso denegado");
$r = $pdo->prepare("SELECT * FROM reportes WHERE id = ?");
$r->execute([$id]);
$data = $r->fetch(PDO::FETCH_ASSOC);
$color = ($data['nivel_riesgo'] == 'Alto') ? '#ef4444' : '#10b981';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Informe SST - Vitapro</title>
    <style>
        @page { size: A4; margin: 0; }
        body { font-family: 'Segoe UI', sans-serif; background: #eee; margin: 0; padding: 20px; }
        .page { background: white; width: 210mm; min-height: 297mm; margin: 0 auto; padding: 40px; box-sizing: border-box; position: relative; }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 4px solid #003366; padding-bottom: 20px; margin-bottom: 30px; }
        .logo-text { font-size: 28px; font-weight: 800; color: #003366; letter-spacing: -1px; }
        .status-badge { background: <?php echo $color; ?>; color: white; padding: 10px 20px; border-radius: 50px; font-weight: bold; text-transform: uppercase; font-size: 12px; }
        .section-title { background: #f1f5f9; padding: 8px 15px; font-weight: 700; color: #475569; border-radius: 6px; margin-top: 25px; margin-bottom: 15px; font-size: 11px; text-transform: uppercase; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 15px; }
        .info-box { border-bottom: 1px solid #e2e8f0; padding-bottom: 5px; }
        .label { font-size: 10px; color: #94a3b8; font-weight: bold; }
        .value { font-size: 14px; color: #1e293b; font-weight: 600; margin-top: 2px; }
        .photo-frame { border: 2px solid #e2e8f0; border-radius: 12px; padding: 10px; text-align: center; background: #fafafa; margin-top: 20px; }
        .photo-frame img { max-width: 100%; max-height: 450px; border-radius: 8px; }
        .btn-print { position: fixed; right: 30px; top: 30px; background: #2563eb; color: white; border: none; padding: 15px 25px; border-radius: 50px; font-weight: bold; cursor: pointer; box-shadow: 0 5px 15px rgba(0,0,0,0.2); z-index: 100; }
        @media print { .btn-print { display: none; } body { padding: 0; } }
    </style>
</head>
<body>
    <button class="btn-print" onclick="window.print()"><i class="fas fa-print"></i> GUARDAR / IMPRIMIR PDF</button>
    <div class="page">
        <div class="header">
            <div class="logo-text">VITAPRO <span style="color:#10b981">SST</span></div>
            <div style="text-align: right;">
                <div style="font-size: 18px; font-weight: 800; color: #1e293b;">REPORTE DE HALLAZGO</div>
                <div style="color: #64748b; font-size: 12px;">Nº Seguimiento: <strong>#<?php echo str_pad($data['id'], 6, "0", STR_PAD_LEFT); ?></strong></div>
            </div>
        </div>
        <div class="status-badge" style="float: right;">Riesgo <?php echo $data['nivel_riesgo']; ?></div>
        <div style="clear: both;"></div>
        <div class="section-title">Información General</div>
        <div class="grid">
            <div class="info-box"><div class="label">Fecha y Hora de Registro</div><div class="value"><?php echo date('d/m/Y H:i', strtotime($data['fecha'])); ?></div></div>
            <div class="info-box"><div class="label">Colaborador Reportante</div><div class="value"><?php echo htmlspecialchars($data['nombre']); ?></div></div>
        </div>
        <div class="grid">
            <div class="info-box"><div class="label">Área del Evento</div><div class="value"><?php echo htmlspecialchars($data['area']); ?></div></div>
            <div class="info-box"><div class="label">Clasificación Técnica</div><div class="value"><?php echo htmlspecialchars($data['tipo_hallazgo']); ?></div></div>
        </div>
        <div class="section-title">Detalles del Hallazgo</div>
        <div class="info-box" style="margin-bottom: 15px;"><div class="label">Causa Específica Citada</div><div class="value"><?php echo htmlspecialchars($data['causa_especifica']); ?></div></div>
        <div class="info-box"><div class="label">Descripción Detallada</div><div class="value" style="font-weight: 400; line-height: 1.6; text-align: justify;"><?php echo nl2br(htmlspecialchars($data['descripcion'])); ?></div></div>
        <?php if($data['foto_path']): ?>
        <div class="section-title">Evidencia Fotográfica de Campo</div>
        <div class="photo-frame">
            <!-- NOTA: Eliminamos el ../ porque la URL ya es absoluta desde Supabase -->
            <img src="<?php echo $data['foto_path']; ?>" alt="Evidencia SST">
        </div>
        <?php endif; ?>
        <div style="position: absolute; bottom: 40px; left: 40px; right: 40px; border-top: 1px solid #eee; padding-top: 15px; font-size: 10px; color: #94a3b8; text-align: center;">
            Documento generado por el Portal de Gestión SST Vitapro. Verificado por el Sistema de Seguridad y Salud en el Trabajo.
        </div>
    </div>
</body>
</html>
