<?php
// 1. SEGURIDAD: Verificar cookie
if (!isset($_COOKIE['auth_token'])) {
    die("<div style='padding:50px;text-align:center;font-family:sans-serif;'>⚠️ Acceso Denegado. Por favor inicie sesión en el Dashboard.</div>");
}

require_once 'conexion.php';

// 2. OBTENER DATOS
$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM reportes WHERE id = ?");
$stmt->execute([$id]);
$r = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$r) die("El reporte #$id no existe.");

// 3. LÓGICA DE COLORES Y DATOS
$bg_riesgo = '#22c55e'; // Verde
if ($r['nivel_riesgo'] == 'Medio') $bg_riesgo = '#eab308'; // Amarillo
if ($r['nivel_riesgo'] == 'Alto') $bg_riesgo = '#ef4444'; // Rojo

// Separar descripción de acción inmediata (si existe el separador)
$desc_real = $r['descripcion'];
$accion = "No registrada en el momento del reporte.";

if (strpos($r['descripcion'], '|| ACCION_TOMADA:') !== false) {
    $parts = explode('|| ACCION_TOMADA:', $r['descripcion']);
    $desc_real = trim($parts[0]);
    $accion = trim($parts[1]);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte #<?php echo str_pad($r['id'], 5, '0', STR_PAD_LEFT); ?> - SST</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        /* ESTILOS DE IMPRESIÓN EXACTOS */
        body { 
            background: #525659; 
            font-family: 'Inter', sans-serif; 
            margin: 0; 
            padding: 40px; 
            display: flex; 
            justify-content: center; 
        }
        
        /* La Hoja de Papel A4 */
        .sheet { 
            background: white; 
            width: 210mm; 
            min-height: 297mm; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.5); 
            position: relative;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
        }

        /* Encabezado Corporativo */
        .header {
            background-color: #0f172a; /* Azul Vitapro Oscuro */
            color: white;
            padding: 30px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 6px solid #2563eb; /* Línea azul brillante */
        }
        
        .logo-text { font-size: 28px; font-weight: 800; letter-spacing: -1px; margin: 0; }
        .logo-sub { font-size: 10px; text-transform: uppercase; letter-spacing: 2px; opacity: 0.8; }
        
        .report-meta { text-align: right; }
        .report-id { font-size: 20px; font-weight: 700; color: #60a5fa; }
        .report-date { font-size: 12px; opacity: 0.8; margin-top: 4px; }

        /* Cuerpo del Reporte */
        .content { padding: 40px; flex-grow: 1; }

        /* Títulos de Sección */
        .section-title {
            font-size: 11px;
            text-transform: uppercase;
            color: #64748b;
            font-weight: 800;
            border-bottom: 2px solid #e2e8f0;
            margin-top: 30px;
            margin-bottom: 15px;
            padding-bottom: 5px;
            letter-spacing: 0.5px;
        }

        /* Grillas de Datos */
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 15px; }
        .grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 15px; }

        /* Cajas de Datos */
        .data-box {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 10px 15px;
        }
        .label { font-size: 9px; text-transform: uppercase; color: #64748b; font-weight: 700; display: block; margin-bottom: 4px; }
        .value { font-size: 14px; font-weight: 600; color: #1e293b; }

        /* Badge de Riesgo */
        .risk-badge {
            background-color: <?php echo $bg_riesgo; ?>;
            color: white;
            padding: 8px 0;
            text-align: center;
            border-radius: 6px;
            font-weight: 800;
            text-transform: uppercase;
            border: 2px solid rgba(0,0,0,0.1);
        }

        /* Cajas de Texto Largo */
        .text-area {
            background: white;
            border: 1px solid #cbd5e1;
            border-left: 4px solid #0f172a;
            padding: 15px;
            border-radius: 4px;
            font-size: 13px;
            line-height: 1.5;
            color: #334155;
            text-align: justify;
        }

        .action-area {
            background: #eff6ff; /* Azul muy claro */
            border: 1px solid #bfdbfe;
            border-left: 4px solid #2563eb;
            padding: 15px;
            border-radius: 4px;
            font-size: 13px;
            color: #1e40af;
        }

        /* Imagen */
        .evidence-box {
            margin-top: 10px;
            text-align: center;
            border: 2px dashed #cbd5e1;
            padding: 10px;
            border-radius: 8px;
            background: #f1f5f9;
        }
        .evidence-img {
            max-width: 100%;
            max-height: 300px;
            border-radius: 4px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        /* Firmas */
        .signatures {
            margin-top: 60px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            padding: 0 20px;
        }
        .sign-line {
            border-top: 1px solid #334155;
            text-align: center;
            padding-top: 10px;
        }
        .sign-title { font-weight: 700; font-size: 12px; color: #0f172a; text-transform: uppercase; }
        .sign-desc { font-size: 10px; color: #64748b; }

        /* Pie de Página */
        .footer {
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 15px 40px;
            font-size: 10px;
            color: #94a3b8;
            display: flex;
            justify-content: space-between;
            margin-top: auto; /* Empujar al fondo */
        }

        /* Botón Flotante para Imprimir */
        .btn-print {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: #2563eb;
            color: white;
            padding: 15px 30px;
            border-radius: 50px;
            font-weight: 700;
            border: none;
            box-shadow: 0 10px 20px rgba(37, 99, 235, 0.4);
            cursor: pointer;
            z-index: 1000;
            transition: transform 0.2s;
        }
        .btn-print:hover { transform: scale(1.05); background: #1d4ed8; }

        /* Ocultar cosas al imprimir */
        @media print {
            body { background: white; padding: 0; }
            .sheet { box-shadow: none; width: 100%; height: auto; }
            .btn-print { display: none; }
        }
    </style>
</head>
<body>

    <button class="btn-print" onclick="window.print()">🖨️ IMPRIMIR / GUARDAR PDF</button>

    <div class="sheet">
        <div class="header">
            <div>
                <h1 class="logo-text">VITAPRO <span style="font-weight:300">SST</span></h1>
                <div class="logo-sub">Gestión de Seguridad Industrial</div>
            </div>
            <div class="report-meta">
                <div class="report-id">REPORTE #<?php echo str_pad($r['id'], 6, '0', STR_PAD_LEFT); ?></div>
                <div class="report-date"><?php echo date('d/m/Y H:i', strtotime($r['fecha'])); ?></div>
            </div>
        </div>

        <div class="content">
            
            <div class="section-title">01. Información General</div>
            <div class="grid-2">
                <div class="data-box">
                    <span class="label">Reportado Por</span>
                    <div class="value"><?php echo $r['nombre']; ?></div>
                    <div style="font-size:10px; color:#64748b; margin-top:2px;">
                        <?php echo $r['tipo_usuario']; ?> <?php echo $r['empresa_contratista'] ? ' - '.$r['empresa_contratista'] : ''; ?>
                    </div>
                </div>
                <div class="data-box">
                    <span class="label">Área / Ubicación</span>
                    <div class="value"><?php echo $r['area']; ?></div>
                </div>
            </div>
            
            <div class="section-title">02. Clasificación del Hallazgo</div>
            <div class="grid-3">
                <div class="data-box">
                    <span class="label">Tipo</span>
                    <div class="value"><?php echo $r['tipo_hallazgo']; ?></div>
                </div>
                <div class="data-box">
                    <span class="label">Clasificación</span>
                    <div class="value"><?php echo $r['causa_especifica'] ?: 'General'; ?></div>
                </div>
                <div class="risk-badge">
                    <span style="display:block; font-size:9px; opacity:0.8; font-weight:normal;">NIVEL DE RIESGO</span>
                    <?php echo strtoupper($r['nivel_riesgo']); ?>
                </div>
            </div>

            <div class="section-title">03. Análisis del Evento</div>
            
            <div style="margin-bottom: 20px;">
                <span class="label" style="margin-bottom:5px;">Descripción del Hallazgo</span>
                <div class="text-area">
                    <?php echo nl2br($desc_real); ?>
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <span class="label" style="margin-bottom:5px; color:#2563eb;">Acción Inmediata / Control</span>
                <div class="action-area">
                    <strong>ACCIÓN TOMADA:</strong> <?php echo nl2br($accion); ?>
                </div>
            </div>

            <div class="grid-2">
                <div class="data-box">
                    <span class="label">Aviso SAP Relacionado</span>
                    <div class="value"><?php echo $r['aviso_sap'] ?: 'N/A'; ?></div>
                </div>
                <div class="data-box">
                    <span class="label">¿Parada de Actividad?</span>
                    <div class="value" style="color: <?php echo $r['detuvo_actividad']=='SI'?'red':'inherit'; ?>">
                        <?php echo $r['detuvo_actividad']; ?>
                    </div>
                </div>
            </div>

            <?php if(!empty($r['foto_path'])): ?>
            <div class="section-title">04. Evidencia Fotográfica</div>
            <div class="evidence-box">
                <img src="<?php echo $r['foto_path']; ?>" class="evidence-img" alt="Evidencia del reporte">
            </div>
            <?php endif; ?>

            <div class="signatures">
                <div class="sign-line">
                    <div class="sign-title">Firma del Reportante</div>
                    <div class="sign-desc">Declaro que la información es verídica</div>
                </div>
                <div class="sign-line">
                    <div class="sign-title">Supervisor / SST</div>
                    <div class="sign-desc">Revisión y Validación</div>
                </div>
            </div>

        </div>

        <div class="footer">
            <div>Generado por Sistema de Gestión SST - Vitapro</div>
            <div>Documento Interno Confidencial</div>
            <div>Pag. 1/1</div>
        </div>
    </div>

</body>
</html>
