<?php
// ---------------------------------------------------------
// 1. AUTENTICACIÓN COMPATIBLE CON VERCEL (COOKIES)
// ---------------------------------------------------------
// En lugar de session_start(), verificamos si existe la cookie del navegador.
if (!isset($_COOKIE['auth_token']) || $_COOKIE['auth_token'] !== 'vitapro_admin_logged') {
    // Si no tiene la cookie, lo mandamos fuera
    header('Location: login.php');
    exit;
}

// ---------------------------------------------------------
// 2. CONEXIÓN Y DATOS
// ---------------------------------------------------------
require_once 'conexion.php';

try {
    // A. Obtener estadísticas para KPIs (Total y Riesgos Altos)
    // Usamos PDO (compatible con PostgreSQL/Supabase)
    $stmt_stats = $pdo->query("SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN nivel_riesgo = 'Alto' THEN 1 ELSE 0 END) as alto
        FROM reportes");
    $q = $stmt_stats->fetch(PDO::FETCH_ASSOC);

    // B. Obtener los últimos 50 reportes para la tabla
    $stmt_lista = $pdo->query("SELECT * FROM reportes ORDER BY id DESC LIMIT 50");
    $reportes = $stmt_lista->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error al cargar datos: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SST Dashboard Premium - Vitapro</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/font-awesome.min.css">
    
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;700&display=swap" rel="stylesheet">

    <style>
        body { background: #f8fafc; font-family: 'Outfit', sans-serif; }
        
        /* Sidebar Estilo Pro */
        .sidebar { width: 260px; background: #0f172a; color: white; min-height: 100vh; position: fixed; padding: 25px; transition: all 0.3s; z-index: 1000; }
        .sidebar h4 { letter-spacing: 1px; }
        .nav-link { color: #94a3b8; padding: 12px 15px; border-radius: 10px; transition: 0.3s; }
        .nav-link:hover, .nav-link.active { background: #334155; color: #fff; transform: translateX(5px); }
        .nav-link i { width: 25px; }

        /* Contenido Principal */
        .content { margin-left: 260px; padding: 40px; }
        
        /* Tarjetas de Cristal */
        .glass-card { background: white; border-radius: 16px; padding: 25px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); border: 1px solid #e2e8f0; }
        
        /* Animación de Alerta Roja */
        .riesgo-alto { border: 2px solid #ef4444; animation: pulse 2s infinite; }
        @keyframes pulse { 
            0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4); } 
            70% { box-shadow: 0 0 0 10px rgba(239, 68, 68, 0); } 
            100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); } 
        }

        /* Tabla Estilizada */
        .table thead th { background: #f1f5f9; color: #475569; font-weight: 600; border: none; }
        .badge { padding: 8px 12px; font-weight: 500; }

        /* Responsivo para móviles */
        @media (max-width: 768px) {
            .sidebar { margin-left: -260px; }
            .content { margin-left: 0; }
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="d-flex align-items-center mb-5">
            <i class="fa fa-shield-halved fa-2x text-primary me-2"></i>
            <h4 class="fw-bold m-0">VITAPRO <span class="text-primary">SST</span></h4>
        </div>

        <nav>
            <small class="text-uppercase text-muted fw-bold mb-2 d-block px-2">Menu Principal</small>
            <a href="admin_nuevo.php" class="nav-link active mb-2"><i class="fa fa-chart-line"></i> Dashboard</a>
            <a href="#" class="nav-link mb-2" data-bs-toggle="modal" data-bs-target="#modalQR"><i class="fa fa-qrcode"></i> Generar QR</a>
            <a href="index.php" class="nav-link mb-2" target="_blank"><i class="fa fa-plus-circle"></i> Nuevo Reporte</a>
            
            <hr class="border-secondary my-4">
            
            <a href="logout.php" class="nav-link text-danger"><i class="fa fa-right-from-bracket"></i> Cerrar Sesión</a>
        </nav>
    </div>

    <div class="content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold text-dark">Monitor de Control</h2>
                <p class="text-muted">Resumen de actos y condiciones subestándar.</p>
            </div>
            <div class="d-flex gap-2">
                <button onclick="window.print()" class="btn btn-outline-dark"><i class="fa fa-print"></i></button>
                <a href="exportar_excel.php" class="btn btn-success"><i class="fa fa-file-excel me-2"></i> Exportar Todo</a>
            </div>
        </div>
        
        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="glass-card d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-muted fw-bold text-uppercase mb-1">Total Reportes</h6>
                        <h2 class="fw-bold m-0"><?php echo $q['total']; ?></h2>
                    </div>
                    <div class="icon-box bg-light p-3 rounded-circle text-primary">
                        <i class="fa fa-folder-open fa-2x"></i>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="glass-card <?php echo ($q['alto'] > 0) ? 'riesgo-alto' : ''; ?> d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-danger fw-bold text-uppercase mb-1">Riesgos Críticos</h6>
                        <h2 class="fw-bold text-danger m-0"><?php echo $q['alto'] ?: 0; ?></h2>
                    </div>
                    <div class="icon-box bg-danger bg-opacity-10 p-3 rounded-circle text-danger">
                        <i class="fa fa-triangle-exclamation fa-2x"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="glass-card d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-success fw-bold text-uppercase mb-1">Estado del Sistema</h6>
                        <h5 class="fw-bold text-success m-0">En Línea <i class="fa fa-check-circle"></i></h5>
                    </div>
                </div>
            </div>
        </div>

        <div class="glass-card">
            <h5 class="fw-bold mb-4">Últimos Registros Ingresados</h5>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Reportante</th>
                            <th>Hallazgo</th>
                            <th>Ubicación</th>
                            <th>Nivel</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($reportes) > 0): ?>
                            <?php foreach($reportes as $r): ?>
                            <tr>
                                <td><?php echo date('d/m/y H:i', strtotime($r['fecha'])); ?></td>
                                <td>
                                    <div class="fw-bold"><?php echo htmlspecialchars($r['nombre']); ?></div>
                                    <small class="text-muted">SAP: <?php echo htmlspecialchars($r['aviso_sap']); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($r['tipo_hallazgo']); ?></td>
                                <td><?php echo htmlspecialchars($r['ubicacion']); ?></td>
                                <td>
                                    <?php 
                                        $badgeClass = match($r['nivel_riesgo']) {
                                            'Alto' => 'bg-danger',
                                            'Medio' => 'bg-warning text-dark',
                                            default => 'bg-success'
                                        };
                                    ?>
                                    <span class="badge <?php echo $badgeClass; ?>"><?php echo $r['nivel_riesgo']; ?></span>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group">
                                        <a href="generar_informe.php?id=<?php echo $r['id']; ?>" class="btn btn-sm btn-outline-secondary" target="_blank" title="Ver PDF">
                                            <i class="fa fa-file-pdf"></i>
                                        </a>
                                        <button onclick="enviarOutlook('<?php echo $r['aviso_sap']; ?>', '<?php echo $r['nombre']; ?>', '<?php echo $r['id']; ?>')" class="btn btn-sm btn-primary" title="Enviar Correo">
                                            <i class="fa fa-envelope"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center py-4">No hay reportes aún.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalQR" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-center p-5 border-0 shadow">
                <h3 class="fw-bold mb-2">Escanea para Reportar</h3>
                <p class="text-muted mb-4">Usa este código en planta para acceso rápido</p>
                
                <img src="https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=<?php echo urlencode('https://'.$_SERVER['HTTP_HOST'].'/index.php'); ?>" class="img-fluid mx-auto border rounded p-2 mb-4">
                
                <div class="d-grid">
                    <button class="btn btn-dark btn-lg" onclick="window.print()">
                        <i class="fa fa-print me-2"></i> IMPRIMIR CÓDIGO
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // 1. Alarma Sonora para Riesgos Altos 🚨
        const siren = new Audio('https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3');
        <?php if($q['alto'] > 0): ?>
            // Nota: Los navegadores bloquean audio automático si no hay interacción previa.
            // Se intentará reproducir, si falla, se imprimirá en consola.
            siren.play().catch(e => console.log("Audio bloqueado por navegador hasta interacción"));
        <?php endif; ?>

        // 2. Función para abrir Outlook con plantilla
        function enviarOutlook(sap, nombre, id) {
            const subject = encodeURIComponent("Notificación SST Vitapro - SAP #" + sap);
            const link = window.location.origin + "/generar_informe.php?id=" + id;
            const body = encodeURIComponent(`Estimados,\n\nSe ha generado un nuevo reporte de seguridad.\n\nReportante: ${nombre}\nID Sistema: ${id}\n\nVer detalle aquí: ${link}\n\nSaludos,\nSST Vitapro`);
            
            // Reemplaza con tu correo real si deseas que llegue a ti por defecto
            window.location.href = `mailto:?subject=${subject}&body=${body}`;
        }
    </script>
</body>
</html>
