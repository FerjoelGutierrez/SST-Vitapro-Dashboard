<?php
// Asegurar que no se cachee
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

if (!isset($_COOKIE['auth_token'])) die("Debe iniciar sesión.");
require_once 'conexion.php';

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM reportes WHERE id = ?");
$stmt->execute([$id]);
$r = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$r) die("Reporte no encontrado");

// Preparar textos (Separando acción de descripción)
$descripcion = $r['descripcion'];
$accion = "No se registró acción inmediata.";
// Buscamos si guardamos la acción con el separador especial
if (strpos($r['descripcion'], '|| ACCION_TOMADA:') !== false) {
    $parts = explode('|| ACCION_TOMADA:', $r['descripcion']);
    $descripcion = trim($parts[0]);
    $accion = trim($parts[1]);
}

// Colores
$color = '#22c55e'; // Verde
if($r['nivel_riesgo'] == 'Medio') $color = '#eab308'; // Amarillo
if($r['nivel_riesgo'] == 'Alto') $color = '#ef4444'; // Rojo

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Técnico #<?php echo $id; ?></title>
    <style>
        body { font-family: Arial, sans-serif; background: #555; padding: 30px; display: flex; justify-content: center; }
        .page { background: white; width: 21cm; min-height: 29.7cm; box-shadow: 0 0 20px rgba(0,0,0,0.5); position: relative; }
        
        /* HEADER PRO */
        .header { background: #0f172a; color: white; padding: 30px 40px; border-bottom: 5px solid #2563eb; display: flex; justify-content: space-between; align-items: center; }
        .logo { font-size: 28px; font-weight: bold; letter-spacing: -1px; }
        .logo span { color: #3b82f6; }
        .meta { text-align: right; font-size: 12px; line-height: 1.4; }
        .meta strong { font-size: 18px; color: #fff; }

        .body-content { padding: 40px; }

        /* TABLA DE DATOS */
        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
        .data-table th, .data-table td { border: 1px solid #e2e8f0; padding: 12px; text-align: left; }
        .data-table th { background: #f1f5f9; color: #64748b; font-size: 10px; text-transform: uppercase; width: 30%; }
        .data-table td { font-size: 14px; font-weight: 600; color: #1e293b; }

        /* SECCIONES */
        .section-title { color: #0f172a; font-size: 14px; font-weight: bold; border-bottom: 2px solid #0f172a; padding-bottom: 5px; margin-bottom: 15px; margin-top: 30px; text-transform: uppercase; }

        /* TEXTO LARGO */
        .text-box { background: #f8fafc; border: 1px solid #e2e8f0; padding: 15px; border-radius: 5px; font-size: 13px; line-height: 1.6; color: #334155; text-align: justify; }
        
        /* ACCION INMEDIATA (Highlight) */
        .action-box { background: #eff6ff; border: 1px solid #bfdbfe; border-left: 4px solid #2563eb; padding: 15px; border-radius: 5px; font-size: 13px; color: #1e3a8a; }

        /* EVIDENCIA */
        .img-container { text-align: center; margin-top: 15px; padding: 10px; border: 2px dashed #cbd5e1; border-radius: 8px; }
        .img-container img { max-width: 100%; max-height: 300px; border-radius: 5px; }

        /* FOOTER */
        .footer { position: absolute; bottom: 0; left: 0; width: 100%; background: #f1f5f9; border-top: 1px solid #e2e8f0; padding: 15px 40px; display: flex; justify-content: space-between; font-size: 10px; color: #64748b; box-sizing: border-box;}

        @media print { body { background: white; padding: 0; } .page { box-shadow: none; } button { display: none; } }
        .btn-print { position: fixed; top: 20px; right: 20px; padding: 10px 20px; background: #2563eb; color: white; border: none; cursor: pointer; border-radius: 5px; font-weight: bold; }
    </style>
</head>
<body>
    <button class="btn-print" onclick="window.print()">IMPRIMIR / PDF</button>

    <div class="page">
        <div class="header">
            <div class="logo">VITAPRO <span>SST</span></div>
            <div class="meta">
                REPORTE DE INCIDENTE<br>
                <strong>#<?php echo str_pad($r['id'], 6, '0', STR_PAD_LEFT); ?></strong><br>
                <?php echo date('d/m/Y H:i', strtotime($r['fecha'])); ?>
            </div>
        </div>

        <div class="body-content">
            
            <div class="section-title">1. Información del Evento</div>
            <table class="data-table">
                <tr>
                    <th>Reportado Por</th>
                    <td><?php echo $r['nombre']; ?> <span style="font-size:11px; color:#64748b; font-weight:normal;">(<?php echo $r['tipo_usuario']; ?>)</span></td>
                </tr>
                <tr>
                    <th>Área / Ubicación</th>
                    <td><?php echo $r['area']; ?></td>
                </tr>
                <tr>
                    <th>Empresa Contratista</th>
                    <td><?php echo $r['empresa_contratista'] ?: 'N/A'; ?></td>
                </tr>
            </table>

            <div class="section-title">2. Clasificación y Riesgo</div>
            <table class="data-table">
                <tr>
                    <th>Tipo de Hallazgo</th>
                    <td><?php echo $r['tipo_hallazgo']; ?></td>
                    <th>Clasificación</th>
                    <td><?php echo $r['causa_especifica'] ?: 'General'; ?></td>
                </tr>
                <tr>
                    <th>Nivel de Riesgo</th>
                    <td style="color:<?php echo $color; ?>; text-transform:uppercase;"><?php echo $r['nivel_riesgo']; ?></td>
                    <th>Aviso SAP</th>
                    <td><?php echo $r['aviso_sap'] ?: '-'; ?></td>
                </tr>
            </table>

            <div class="section-title">3. Detalle y Gestión</div>
            
            <p style="font-size:11px; font-weight:bold; color:#64748b; margin-bottom:5px;">DESCRIPCIÓN DEL HALLAZGO</p>
            <div class="text-box">
                <?php echo nl2br($descripcion); ?>
            </div>
            
            <br>

            <p style="font-size:11px; font-weight:bold; color:#2563eb; margin-bottom:5px;">ACCIÓN INMEDIATA TOMADA</p>
            <div class="action-box">
                <?php echo nl2br($accion); ?>
            </div>

            <?php if($r['foto_path']): ?>
            <div class="section-title">4. Evidencia Fotográfica</div>
            <div class="img-container">
                <img src="<?php echo $r['foto_path']; ?>">
            </div>
            <?php endif; ?>

            <div style="margin-top: 60px; display: flex; justify-content: space-around;">
                <div style="text-align: center; width: 200px;">
                    <div style="border-bottom: 1px solid #333; margin-bottom: 5px;"></div>
                    <div style="font-size: 11px; font-weight: bold;">Firma Reportante</div>
                </div>
                <div style="text-align: center; width: 200px;">
                    <div style="border-bottom: 1px solid #333; margin-bottom: 5px;"></div>
                    <div style="font-size: 11px; font-weight: bold;">Visto Bueno SST</div>
                </div>
            </div>

        </div>

        <div class="footer">
            <div>Sistema de Gestión SST - Vitapro</div>
            <div>Documento Interno</div>
            <div>Pag. 1/1</div>
        </div>
    </div>
</body>
</html>
