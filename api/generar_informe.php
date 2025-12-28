<?php
if (!isset($_COOKIE['auth_token'])) die("Acceso denegado. Inicie sesión.");
require_once 'conexion.php';

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM reportes WHERE id = ?");
$stmt->execute([$id]);
$r = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$r) die("Reporte no encontrado");

// Lógica para separar Descripción de Acción Inmediata
$full_desc = $r['descripcion'];
$descripcion_texto = $full_desc;
$accion_tomada = "No se reportó ninguna acción inmediata.";

if (strpos($full_desc, '|| ACCION_TOMADA:') !== false) {
    $parts = explode('|| ACCION_TOMADA:', $full_desc);
    $descripcion_texto = trim($parts[0]);
    $accion_tomada = trim($parts[1]);
}

// Colores dinámicos
$colorHeader = '#0f172a'; // Azul oscuro Vitapro
$colorRiesgo = '#16a34a'; // Verde por defecto
if ($r['nivel_riesgo'] == 'Medio') $colorRiesgo = '#ca8a04'; // Amarillo oscuro
if ($r['nivel_riesgo'] == 'Alto') $colorRiesgo = '#dc2626'; // Rojo
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Técnico SST - #<?php echo $id; ?></title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #555; padding: 20px; display: flex; justify-content: center; }
        
        .sheet { 
            background: white; 
            width: 210mm; 
            min-height: 297mm; 
            padding: 0; 
            box-shadow: 0 0 15px rgba(0,0,0,0.3); 
            position: relative;
        }

        /* HEADER */
        .header { background: <?php echo $colorHeader; ?>; color: white; padding: 25px 40px; display: flex; justify-content: space-between; align-items: center; border-bottom: 5px solid #2563eb; }
        .brand h1 { margin: 0; font-size: 24px; text-transform: uppercase; letter-spacing: 2px; }
        .brand span { color: #60a5fa; }
        .meta { text-align: right; font-size: 12px; opacity: 0.9; }
        .meta strong { font-size: 16px; color: #fff; }

        .content { padding: 40px; }

        /* SECTIONS */
        .section-title { 
            font-size: 11px; 
            text-transform: uppercase; 
            letter-spacing: 1px; 
            color: #64748b; 
            font-weight: bold; 
            border-bottom: 2px solid #e2e8f0; 
            padding-bottom: 5px; 
            margin-top: 30px; 
            margin-bottom: 15px; 
        }

        /* GRIDS */
        .row { display: flex; margin-bottom: 15px; gap: 20px; }
        .col { flex: 1; }
        
        .field-box {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            padding: 10px 15px;
            border-radius: 4px;
        }
        .field-label { font-size: 9px; text-transform: uppercase; color: #64748b; font-weight: 700; display: block; margin-bottom: 4px; }
        .field-value { font-size: 13px; font-weight: 600; color: #0f172a; }

        /* RIESGO BADGE */
        .risk-badge { 
            background: <?php echo $colorRiesgo; ?>; 
            color: white; 
            padding: 5px 10px; 
            border-radius: 4px; 
            font-size: 12px; 
            font-weight: bold; 
            display: inline-block;
        }

        /* TEXT AREAS */
        .text-block { background: #fff; border: 1px solid #e2e8f0; padding: 15px; border-radius: 4px; font-size: 13px; line-height: 1.5; text-align: justify; }

        /* PHOTO */
        .photo-container { text-align: center; background: #f1f5f9; padding: 20px; border: 2px dashed #cbd5e1; border-radius: 8px; margin-top: 10px; }
        .photo-container img { max-width: 100%; max-height: 350px; border-radius: 4px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }

        /* FOOTER */
        .footer { position: absolute; bottom: 0; left: 0; width: 100%; background: #f8fafc; padding: 20px 40px; border-top: 1px solid #e2e8f0; font-size: 10px; color: #94a3b8; display: flex; justify-content: space-between; }

        @media print {
            body { background: white; padding: 0; }
            .sheet { box-shadow: none; width: 100%; }
            .no-print { display: none; }
        }
        
        .btn-print { position: fixed; top: 20px; right: 20px; padding: 12px 25px; background: #2563eb; color: white; border: none; border-radius: 30px; font-weight: bold; cursor: pointer; box-shadow: 0 4px 10px rgba(37,99,235,0.4); }
    </style>
</head>
<body>

    <button class="btn-print no-print" onclick="window.print()">🖨️ Imprimir / PDF</button>

    <div class="sheet">
        <div class="header">
            <div class="brand">
                <h1>VITAPRO <span>SST</span></h1>
                <small>Gestión de Seguridad Industrial</small>
            </div>
            <div class="meta">
                FOLIO ÚNICO<br>
                <strong>#<?php echo str_pad($r['id'], 6, '0', STR_PAD_LEFT); ?></strong><br>
                <?php echo date('d/m/Y H:i', strtotime($r['fecha'])); ?>
            </div>
        </div>

        <div class="content">
            
            <div class="section-title">1. Datos del Reportante y Ubicación</div>
            <div class="row">
                <div class="col field-box">
                    <span class="field-label">Reportado Por</span>
                    <div class="field-value"><?php echo $r['nombre']; ?></div>
                    <small style="color:#64748b; font-size:10px;"><?php echo $r['tipo_usuario']; ?> <?php echo $r['empresa_contratista'] ? '- ' . $r['empresa_contratista'] : ''; ?></small>
                </div>
                <div class="col field-box">
                    <span class="field-label">Área / Ubicación</span>
                    <div class="field-value"><?php echo $r['area']; ?></div>
                </div>
                <div class="col field-box" style="flex: 0.5;">
                    <span class="field-label">Aviso SAP</span>
                    <div class="field-value"><?php echo $r['aviso_sap'] ?: 'N/A'; ?></div>
                </div>
            </div>

            <div class="section-title">2. Clasificación del Hallazgo</div>
            <div class="row">
                <div class="col field-box">
                    <span class="field-label">Tipo de Evento</span>
                    <div class="field-value" style="color: #2563eb;"><?php echo $r['tipo_hallazgo']; ?></div>
                </div>
                <div class="col field-box">
                    <span class="field-label">Clasificación Específica</span>
                    <div class="field-value"><?php echo $r['causa_especifica'] ?: 'No especificado'; ?></div>
                </div>
                <div class="col field-box" style="flex: 0.5; display:flex; align-items:center; justify-content:center; flex-direction:column;">
                    <span class="field-label">Nivel de Riesgo</span>
                    <div class="risk-badge"><?php echo strtoupper($r['nivel_riesgo']); ?></div>
                </div>
            </div>

            <div class="section-title">3. Detalle Técnico y Control</div>
            
            <div style="margin-bottom: 15px;">
                <span class="field-label" style="margin-left: 5px;">Descripción del Hallazgo</span>
                <div class="text-block">
                    <?php echo nl2br($descripcion_texto); ?>
                </div>
            </div>

            <div style="margin-bottom: 15px;">
                <span class="field-label" style="margin-left: 5px; color: #059669;">Acción Inmediata / Medida Correctiva</span>
                <div class="text-block" style="border-left: 4px solid #059669;">
                    <?php echo nl2br($accion_tomada); ?>
                </div>
            </div>

            <div class="row">
                <div class="col field-box">
                    <span class="field-label">¿Se detuvo la actividad?</span>
                    <div class="field-value"><?php echo $r['detuvo_actividad']; ?></div>
                </div>
            </div>

            <?php if($r['foto_path']): ?>
            <div class="section-title">4. Evidencia Fotográfica</div>
            <div class="photo-container">
                <img src="<?php echo $r['foto_path']; ?>" alt="Evidencia del reporte">
            </div>
            <?php endif; ?>

            <div style="margin-top: 60px; display: flex; justify-content: space-between; padding: 0 50px;">
                <div style="text-align: center;">
                    <div style="border-bottom: 1px solid #333; width: 200px; margin-bottom: 5px;"></div>
                    <small style="font-weight:bold; color:#475569;">Firma Reportante</small>
                </div>
                <div style="text-align: center;">
                    <div style="border-bottom: 1px solid #333; width: 200px; margin-bottom: 5px;"></div>
                    <small style="font-weight:bold; color:#475569;">Visto Bueno SST</small>
                </div>
            </div>

        </div>

        <div class="footer">
            <div>Generado por Sistema SST Vitapro v2.0</div>
            <div>Fecha de impresión: <?php echo date('d/m/Y H:i:s'); ?></div>
        </div>
    </div>

</body>
</html>
