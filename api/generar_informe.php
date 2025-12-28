<?php
// Evitar caché
header("Cache-Control: no-cache, must-revalidate");

if (!isset($_COOKIE['auth_token'])) die("Acceso denegado.");
require_once 'conexion.php';

$id = $_GET['id'] ?? 0;
// Seleccionamos todo (*) para traer la nueva columna también
$stmt = $pdo->prepare("SELECT * FROM reportes WHERE id = ?");
$stmt->execute([$id]);
$r = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$r) die("Reporte no encontrado.");

// Colores según riesgo
$colorRiesgo = '#16a34a'; // Verde
if ($r['nivel_riesgo'] == 'Medio') $colorRiesgo = '#ca8a04'; // Amarillo
if ($r['nivel_riesgo'] == 'Alto') $colorRiesgo = '#dc2626'; // Rojo

// DATOS LIMPIOS
$descripcion = $r['descripcion'];
// Si el reporte es antiguo y no tiene columna, usa el default
$accion = $r['accion_inmediata'] ?? 'Dato no disponible en este reporte histórico.';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Técnico #<?php echo $id; ?></title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #555; padding: 30px; display: flex; justify-content: center; }
        .page { background: white; width: 21cm; min-height: 29.7cm; padding: 0; box-shadow: 0 0 20px rgba(0,0,0,0.5); position: relative; display: flex; flex-direction: column; }
        
        /* HEADER AZUL OSCURO */
        .header { background: #0f172a; color: white; padding: 25px 40px; display: flex; justify-content: space-between; align-items: center; border-bottom: 5px solid #2563eb; }
        .logo h1 { margin: 0; font-size: 26px; text-transform: uppercase; letter-spacing: 1px; }
        .logo span { color: #3b82f6; }
        .meta { text-align: right; font-size: 12px; }
        .meta strong { font-size: 18px; color: #fff; }

        .content { padding: 40px; flex-grow: 1; }

        /* SECCIONES */
        .section-title { font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 800; border-bottom: 2px solid #e2e8f0; margin-top: 25px; margin-bottom: 15px; padding-bottom: 5px; }

        /* TABLAS DE DATOS */
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 10px; }
        .info-box { background: #f8fafc; border: 1px solid #cbd5e1; padding: 10px 15px; border-radius: 6px; }
        .label { display: block; font-size: 9px; text-transform: uppercase; color: #64748b; font-weight: 700; margin-bottom: 3px; }
        .value { font-size: 13px; font-weight: 600; color: #0f172a; }

        /* BADGE RIESGO */
        .risk-badge { background: <?php echo $colorRiesgo; ?>; color: white; padding: 10px; text-align: center; border-radius: 6px; font-weight: bold; text-transform: uppercase; font-size: 14px; border: 1px solid rgba(0,0,0,0.1); }

        /* AREAS DE TEXTO */
        .text-area { background: #fff; border: 1px solid #cbd5e1; padding: 15px; border-radius: 6px; font-size: 13px; line-height: 1.5; color: #334155; text-align: justify; margin-bottom: 15px; }
        
        /* CAJA DE ACCIÓN (Highlight) */
        .action-area { background: #eff6ff; border: 1px solid #bfdbfe; border-left: 5px solid #2563eb; padding: 15px; border-radius: 6px; }
        .action-title { color: #1e40af; font-size: 11px; font-weight: 800; text-transform: uppercase; margin-bottom: 5px; }
        .action-text { color: #1e3a8a; font-size: 13px; font-weight: 600; }

        /* FOTO */
        .evidence-container { background: #f1f5f9; padding: 15px; border: 2px dashed #cbd5e1; border-radius: 8px; text-align: center; margin-top: 10px; }
        .evidence-img { max-width: 100%; max-height: 320px; border-radius: 4px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }

        /* FIRMAS */
        .signatures { margin-top: 50px; display: grid; grid-template-columns: 1fr 1fr; gap: 40px; padding: 0 20px; }
        .sign-box { border-top: 1px solid #334155; text-align: center; padding-top: 10px; }
        .sign-title { font-size: 11px; font-weight: 700; color: #0f172a; text-transform: uppercase; }

        /* FOOTER */
        .footer { background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 15px 40px; font-size: 10px; color: #94a3b8; display: flex; justify-content: space-between; }

        @media print { body { background: white; padding: 0; } .page { box-shadow: none; } .btn-print { display: none; } }
        .btn-print { position: fixed; top: 20px; right: 20px; background: #2563eb; color: white; padding: 12px 25px; border-radius: 50px; border: none; font-weight: 700; cursor: pointer; box-shadow: 0 5px 15px rgba(37,99,235,0.4); }
    </style>
</head>
<body>
    <button class="btn-print" onclick="window.print()">🖨️ IMPRIMIR REPORTE</button>

    <div class="page">
        <div class="header">
            <div>
                <h1 class="logo">VITAPRO <span>SST</span></h1>
                <small style="opacity:0.8; font-size:11px; letter-spacing:1px; text-transform:uppercase;">Sistema de Gestión de Seguridad</small>
            </div>
            <div class="meta">
                FOLIO DE REPORTE<br>
                <strong>#<?php echo str_pad($r['id'], 6, '0', STR_PAD_LEFT); ?></strong><br>
                <?php echo date('d/m/Y H:i', strtotime($r['fecha'])); ?>
            </div>
        </div>

        <div class="content">
            
            <div class="section-title">01. Información General</div>
            <div class="info-grid">
                <div class="info-box">
                    <span class="label">Reportado Por</span>
                    <div class="value"><?php echo $r['nombre']; ?></div>
                    <div style="font-size:10px; color:#64748b; margin-top:2px;">
                        <?php echo $r['tipo_usuario']; ?> <?php echo $r['empresa_contratista'] ? ' - '.$r['empresa_contratista'] : ''; ?>
                    </div>
                </div>
                <div class="info-box">
                    <span class="label">Ubicación / Área</span>
                    <div class="value"><?php echo $r['area']; ?></div>
                </div>
            </div>

            <div class="section-title">02. Clasificación del Hallazgo</div>
            <div style="display: grid; grid-template-columns: 1fr 1fr 0.6fr; gap: 15px;">
                <div class="info-box">
                    <span class="label">Tipo de Evento</span>
                    <div class="value" style="color: #2563eb;"><?php echo $r['tipo_hallazgo']; ?></div>
                </div>
                <div class="info-box">
                    <span class="label">Causa Específica</span>
                    <div class="value"><?php echo $r['causa_especifica'] ?: 'General'; ?></div>
                </div>
                <div class="risk-badge">
                    <span style="display:block; font-size:8px; font-weight:normal; opacity:0.9; margin-bottom:2px;">NIVEL DE RIESGO</span>
                    <?php echo strtoupper($r['nivel_riesgo']); ?>
                </div>
            </div>

            <div class="section-title">03. Detalle y Gestión</div>
            
            <span class="label" style="margin-left: 5px;">Descripción del Hallazgo</span>
            <div class="text-area">
                <?php echo nl2br($descripcion); ?>
            </div>

            <div class="action-area">
                <div class="action-title">✅ Acción Correctiva Inmediata</div>
                <div class="action-text">
                    <?php echo nl2br($accion); ?>
                </div>
            </div>

            <div class="info-grid" style="margin-top: 15px;">
                <div class="info-box">
                    <span class="label">Aviso SAP Relacionado</span>
                    <div class="value"><?php echo $r['aviso_sap'] ?: 'N/A'; ?></div>
                </div>
                <div class="info-box">
                    <span class="label">¿Parada de Actividad?</span>
                    <div class="value" style="color: <?php echo $r['detuvo_actividad']=='SI'?'red':'inherit'; ?>">
                        <?php echo $r['detuvo_actividad']; ?>
                    </div>
                </div>
            </div>

            <?php if(!empty($r['foto_path'])): ?>
            <div class="section-title">04. Evidencia Fotográfica</div>
            <div class="evidence-container">
                <img src="<?php echo $r['foto_path']; ?>" class="evidence-img" alt="Evidencia">
            </div>
            <?php endif; ?>

            <div class="signatures">
                <div class="sign-box">
                    <div class="sign-title">Firma del Reportante</div>
                    <small style="color:#64748b; font-size:9px;">Responsable del reporte</small>
                </div>
                <div class="sign-box">
                    <div class="sign-title">Visto Bueno SST</div>
                    <small style="color:#64748b; font-size:9px;">Validación de seguridad</small>
                </div>
            </div>

        </div>

        <div class="footer">
            <div>Documento Oficial SST - Vitapro</div>
            <div>Impreso el <?php echo date('d/m/Y H:i'); ?></div>
        </div>
    </div>
</body>
</html>
