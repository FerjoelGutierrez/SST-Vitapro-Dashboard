<?php
// SEGURIDAD
if (!isset($_COOKIE['auth_token'])) die("Acceso denegado");
require_once 'conexion.php';

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM reportes WHERE id = ?");
$stmt->execute([$id]);
$r = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$r) die("Reporte no encontrado");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Informe SST - Vitapro</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; color: #1e293b; padding: 40px; background: #555; display: flex; justify-content: center; }
        
        .page { background: white; width: 21cm; min-height: 29.7cm; padding: 60px; box-shadow: 0 5px 20px rgba(0,0,0,0.5); position: relative; box-sizing: border-box; }
        
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 4px solid #1e3a8a; padding-bottom: 20px; margin-bottom: 40px; }
        .logo h1 { margin: 0; color: #1e3a8a; font-weight: 800; font-size: 32px; letter-spacing: 1px; }
        .meta { text-align: right; font-size: 14px; }
        
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
        .item { background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0; }
        .label { font-size: 10px; text-transform: uppercase; color: #64748b; font-weight: 800; letter-spacing: 0.5px; margin-bottom: 5px; }
        .value { font-size: 16px; font-weight: 600; color: #0f172a; }
        
        .hallazgo-box { background: white; border: 1px solid #cbd5e1; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        
        .img-box { text-align: center; margin-top: 20px; border: 2px dashed #cbd5e1; padding: 10px; border-radius: 12px; }
        .img-box img { max-width: 100%; max-height: 400px; border-radius: 8px; }
        
        .sign { margin-top: 80px; text-align: center; border-top: 2px solid #1e293b; width: 300px; margin-left: auto; margin-right: auto; padding-top: 10px; font-weight: 700; color: #1e293b; }
        
        .btn-print { position: fixed; top: 30px; right: 30px; background: #2563eb; color: white; padding: 15px 30px; border-radius: 50px; border: none; cursor: pointer; font-weight: 700; box-shadow: 0 4px 15px rgba(37,99,235,0.4); z-index: 999; }
        .btn-print:hover { transform: scale(1.05); }

        @media print { 
            body { background: white; padding: 0; display: block; }
            .page { box-shadow: none; width: 100%; padding: 40px; margin: 0; } 
            .btn-print { display: none; }
        }
    </style>
</head>
<body>
    <button class="btn-print" onclick="window.print()">DESCARGAR / IMPRIMIR PDF</button>
    
    <div class="page">
        <div class="header">
            <div class="logo">
                <h1>VITAPRO</h1>
                <p style="margin:0; font-size:12px; font-weight:700; color:#64748b;">SEGURIDAD Y SALUD EN EL TRABAJO</p>
            </div>
            <div class="meta">
                <strong style="font-size: 18px; color: #1e3a8a;">REPORTE #<?php echo str_pad($r['id'], 5, '0', STR_PAD_LEFT); ?></strong>
                <br><?php echo $r['fecha']; ?>
            </div>
        </div>

        <div class="grid">
            <div class="item"><div class="label">Reportante</div><div class="value"><?php echo $r['nombre']; ?></div></div>
            <div class="item"><div class="label">Área</div><div class="value"><?php echo $r['area']; ?></div></div>
            <div class="item"><div class="label">Aviso SAP</div><div class="value"><?php echo $r['aviso_sap'] ?: 'N/A'; ?></div></div>
            <div class="item">
                <div class="label">Parada de Actividad</div>
                <div class="value" style="color:<?php echo $r['detuvo_actividad']=='SI'?'#dc2626':'#16a34a'; ?>">
                    <?php echo strtoupper($r['detuvo_actividad']); ?>
                </div>
            </div>
        </div>

        <div class="hallazgo-box">
            <div class="label">Hallazgo Identificado</div>
            <div class="value" style="font-size: 18px; color: #1e3a8a;">
                <?php echo $r['tipo_hallazgo']; ?>
            </div>
        </div>

        <div class="hallazgo-box" style="min-height: 100px;">
            <div class="label">Descripción Detallada</div>
            <div class="value" style="font-weight: 400; line-height: 1.6;">
                <?php echo nl2br($r['descripcion']); ?>
            </div>
        </div>
        
        <?php if($r['foto_path']): ?>
        <div class="label" style="text-align: center; margin-top: 10px;">EVIDENCIA FOTOGRÁFICA</div>
        <div class="img-box">
            <img src="<?php echo $r['foto_path']; ?>">
        </div>
        <?php endif; ?>

        <div class="sign">Firma Responsable SST</div>
        
        <div style="position: absolute; bottom: 40px; left: 0; right: 0; text-align: center;">
            <p style="font-size:11px; color:#94a3b8; border-top:1px solid #f1f5f9; padding-top:10px; display: inline-block;">
                Documento Oficial generado por Sistema SST Vitapro
            </p>
        </div>
    </div>
</body>
</html>
