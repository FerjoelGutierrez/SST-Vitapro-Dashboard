<?php
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
    <title>Informe SST - #<?php echo $id; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; background: #555; padding: 40px; display: flex; justify-content: center; }
        .page { background: white; width: 21cm; min-height: 29.7cm; padding: 50px; position: relative; }
        .header { border-bottom: 4px solid #1e3a8a; padding-bottom: 20px; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center; }
        .logo h1 { color: #1e3a8a; margin: 0; font-size: 28px; }
        .item { background: #f8fafc; padding: 10px; border: 1px solid #e2e8f0; margin-bottom: 10px; border-radius: 6px; }
        .label { font-size: 10px; color: #64748b; font-weight: bold; text-transform: uppercase; }
        .value { font-size: 14px; font-weight: 600; color: #0f172a; }
        .img-box { text-align: center; margin-top: 20px; border: 2px dashed #cbd5e1; padding: 10px; }
        .img-box img { max-width: 100%; max-height: 500px; border-radius: 8px; }
        @media print { body { background: white; padding: 0; } .btn-print { display: none; } }
        .btn-print { position: fixed; top: 20px; right: 20px; background: #2563eb; color: white; padding: 15px 30px; border: none; border-radius: 50px; font-weight: bold; cursor: pointer; }
    </style>
</head>
<body>
    <button class="btn-print" onclick="window.print()">IMPRIMIR PDF</button>
    <div class="page">
        <div class="header">
            <div class="logo"><h1>VITAPRO</h1><small>SEGURIDAD INDUSTRIAL</small></div>
            <div style="text-align: right;"><strong>REPORTE #<?php echo str_pad($r['id'], 5, '0', STR_PAD_LEFT); ?></strong><br><?php echo $r['fecha']; ?></div>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
            <div class="item"><div class="label">Reportante</div><div class="value"><?php echo $r['nombre']; ?></div></div>
            <div class="item"><div class="label">Área</div><div class="value"><?php echo $r['area']; ?></div></div>
            <div class="item"><div class="label">Hallazgo</div><div class="value"><?php echo $r['tipo_hallazgo']; ?></div></div>
            <div class="item"><div class="label">Riesgo</div><div class="value" style="color: <?php echo $r['nivel_riesgo']=='Alto'?'red':'green'; ?>"><?php echo $r['nivel_riesgo']; ?></div></div>
        </div>

        <div class="item" style="min-height: 100px;">
            <div class="label">Descripción</div>
            <div class="value"><?php echo nl2br($r['descripcion']); ?></div>
        </div>

        <?php if(!empty($r['foto_path'])): ?>
            <div class="img-box">
                <div class="label" style="margin-bottom: 10px;">EVIDENCIA FOTOGRÁFICA</div>
                <img src="<?php echo $r['foto_path']; ?>">
            </div>
        <?php endif; ?>

        <div style="margin-top: 80px; text-align: center;">
            <div style="border-top: 2px solid #0f172a; width: 200px; margin: 0 auto; padding-top: 10px; font-weight: bold;">Firma Responsable SST</div>
        </div>
    </div>
</body>
</html>
