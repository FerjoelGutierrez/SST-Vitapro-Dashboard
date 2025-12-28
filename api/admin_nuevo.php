<?php
/**
 * SST Dashboard Premium - Vitapro
 * Sistema de Verificación mediante Cookies (Optimizado para Vercel Serverless)
 */
// 1. Verificación de Seguridad (Cookie de 24h)
if (!isset($_COOKIE['auth_token']) || $_COOKIE['auth_token'] !== 'vitapro_admin_logged') {
    header('Location: login.php');
    exit;
}
require_once 'conexion.php';
try {
    // 2. Obtener estadísticas para KPIs (PostgreSQL)
    $q = $pdo->query("SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN nivel_riesgo = 'Alto' THEN 1 ELSE 0 END) as alto
        FROM reportes")->fetch();
    // 3. Obtener los últimos 50 reportes
    $reportes = $pdo->query("SELECT * FROM reportes ORDER BY id DESC LIMIT 50")->fetchAll();
} catch (PDOException $e) {
    die("Error de base de datos: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SST Dashboard Premium - Vitapro</title>
    <!-- Bootstrap 5 y Google Fonts -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/font-awesome.min.css">
    
    <style>
        :root { --primary: #2563eb; --sidebar: #1e293b; --bg: #f8fafc; }
        body { background: var(--bg); font-family: 'Outfit', sans-serif; overflow-x: hidden; }
        
        /* Sidebar */
        .sidebar { width: 260px; background: var(--sidebar); color: white; min-height: 100vh; position: fixed; padding: 25px; transition: all 0.3s; z-index: 1000; }
        .nav-link { color: rgba(255,255,255,0.7); border-radius: 12px; padding: 12px 15px; margin-bottom: 5px; transition: 0.3s; }
        .nav-link:hover, .nav-link.active { background: rgba(255,255,255,0.1); color: white; }
        
        /* Contenido */
        .content { margin-left: 260px; padding: 40px; transition: all 0.3s; }
        .glass-card { background: white; border-radius: 20px; padding: 25px; border: 1px solid rgba(0,0,0,0.05); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05); height: 100%; }
        
        /* Alerta de Riesgo Alto */
        .riesgo-alto-card { border: 2px solid #ef4444; position: relative; overflow: hidden; }
        .riesgo-alto-card::after { content: ''; position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(239, 68, 68, 0.05); animation: pulse-bg 2s infinite; }
        
        @keyframes pulse-bg {
            0% { opacity: 0.1; }
            50% { opacity: 0.3; }
            100% { opacity: 0.1; }
        }
        .table thead { background: #f1f5f9; }
        .table th { border: none; font-weight: 600; text-transform: uppercase; font-size: 12px; letter-spacing: 0.5px; }
        
        @media (max-width: 992px) {
            .sidebar { margin-left: -260px; }
            .content { margin-left: 0; padding: 20px; }
        }
    </style>
</head>
<body>
    <!-- Sidebar Lateral -->
    <div class="sidebar">
        <div class="text-center mb-5">
            <h4 class="fw-bold text-info"><i class="fas fa-shield-alt me-2"></i>VITAPRO SST</h4>
            <p class="small text-muted mb-0">Portal Administrativo</p>
        </div>
        
        <nav class="nav flex-column">
            <a href="admin_nuevo.php" class="nav-link active"><i class="fas fa-chart-line me-3"></i>Dashboard</a>
            <a href="#" class="nav-link" data-bs-toggle="modal" data-bs-target="#modalQR"><i class="fas fa-qrcode me-3"></i>Generar QR</a>
            <a href="index.php" class="nav-link"><i class="fas fa-plus-circle me-3"></i>Nuevo Reporte</a>
            <hr class="my-4 text-white-50">
            <a href="logout.php" class="nav-link text-danger"><i class="fas fa-power-off me-3"></i>Cerrar Sesión</a>
        </nav>
    </div>
    <!-- Contenido Principal -->
    <div class="content">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h2 class="fw-bold text-dark mb-1">Monitor de Seguridad</h2>
                <p class="text-muted">Estado actual de la planta en tiempo real</p>
            </div>
            <div class="bg-white p-2 rounded-3 shadow-sm px-4 border">
                <span class="small text-muted d-block">Fecha Actual</span>
                <span class="fw-bold"><?php echo date('d M, Y'); ?></span>
            </div>
        </div>
        
        <!-- KPIs Principales -->
        <div class="row g-4 mb-5">
            <div class="col-md-6 col-lg-4">
                <div class="glass-card">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 p-3 rounded-4 me-3">
                            <i class="fas fa-clipboard-list text-primary fa-2x"></i>
                        </div>
                        <div>
                            <h6 class="text-muted small fw-bold mb-1">TOTAL REPORTES</h6>
                            <h2 class="fw-bold mb-0"><?php echo $q['total'] ?: 0; ?></h2>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-4">
                <div class="glass-card <?php echo $q['alto'] > 0 ? 'riesgo-alto-card' : ''; ?>">
                    <div class="d-flex align-items-center">
                        <div class="bg-danger bg-opacity-10 p-3 rounded-4 me-3">
                            <i class="fas fa-exclamation-triangle text-danger fa-2x"></i>
                        </div>
                        <div>
                            <h6 class="text-danger small fw-bold mb-1">RIESGOS ALTOS</h6>
                            <h2 class="fw-bold mb-0 text-danger"><?php echo $q['alto'] ?: 0; ?></h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Tabla de Reportes Recientes -->
        <div class="glass-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold m-0 text-dark">Últimos Eventos Registrados</h5>
                <button onclick="window.location.reload()" class="btn btn-sm btn-light border"><i class="fas fa-sync-alt me-2"></i>Actualizar</button>
            </div>
            
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Fecha</th>
                            <th>Colaborador</th>
                            <th>Hallazgo</th>
                            <th>Riesgo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($reportes as $r): ?>
                        <tr>
                            <td class="text-muted small">#<?php echo $r['id']; ?></td>
                            <td class="small fw-bold"><?php echo date('d/m/Y', strtotime($r['fecha'])); ?></td>
                            <td>
                                <span class="d-block fw-bold"><?php echo htmlspecialchars($r['nombre']); ?></span>
                                <span class="text-muted x-small" style="font-size: 11px;"><?php echo htmlspecialchars($r['area']); ?></span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border"><?php echo $r['tipo_hallazgo']; ?></span>
                            </td>
                            <td>
                                <?php if($r['nivel_riesgo'] == 'Alto'): ?>
                                    <span class="badge bg-danger text-white px-3">Alto</span>
                                <?php else: ?>
                                    <span class="badge bg-success text-white px-3">Bajo / Medio</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="generar_informe.php?id=<?php echo $r['id']; ?>" class="btn btn-sm btn-outline-dark" target="_blank">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                    <button onclick="enviarOutlook('<?php echo $r['aviso_sap']; ?>', '<?php echo htmlspecialchars($r['nombre']); ?>', '<?php echo $r['id']; ?>')" class="btn btn-sm btn-primary">
                                        <i class="fab fa-microsoft me-1"></i> Outlook
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($reportes)): ?>
                        <tr><td colspan="6" class="text-center py-5 text-muted fst-italic">No hay reportes registrados aún.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- Modal QR (Generador Dinámico) -->
    <div class="modal fade" id="modalQR" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0" style="border-radius: 25px;">
                <div class="modal-body text-center p-5">
                    <div class="mb-4">
                        <i class="fas fa-qrcode fa-3x text-primary"></i>
                    </div>
                    <h4 class="fw-bold mb-2">Código QR de Planta</h4>
                    <p class="text-muted mb-4 small">Escanee para acceder directamente al sistema de reportes SST desde dispositivos móviles.</p>
                    
                    <div class="bg-light p-4 rounded-4 d-inline-block border mb-4">
                        <img src="https://chart.googleapis.com/chart?chs=250x250&cht=qr&chl=<?php echo urlencode('https://'.$_SERVER['HTTP_HOST'].'/index.php'); ?>" class="img-fluid" width="220" alt="QR SST">
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button class="btn btn-primary btn-lg rounded-pill" onclick="window.print()"><i class="fas fa-print me-2"></i>Imprimir Cartel Oficial</button>
                        <button class="btn btn-link text-muted mt-2" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Alarma Sonora para Riesgos Críticos 🚨
        const siren = new Audio('https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3');
        
        function activarAlarma() {
            const numAltos = <?php echo $q['alto'] ?: 0; ?>;
            if (numAltos > 0) {
                // Notifica al usuario antes de sonar (política de navegadores)
                console.log("ALERTA: Se han detectado riesgos activos.");
                siren.play().catch(e => {
                    console.log("Sonido bloqueado por el navegador. Haga clic en la página para habilitar alarmas.");
                });
            }
        }
        window.onload = activarAlarma;
        // Auto-actualización automática cada 60 segundos
        setTimeout(() => { window.location.reload(); }, 60000);
        function enviarOutlook(sap, nombre, id) {
            const subject = encodeURIComponent("Gestión SST Vitapro - Notificación SAP #" + (sap ? sap : 'SST-' + id));
            const portalUrl = window.location.origin + "/generar_informe.php?id=" + id;
            const body = encodeURIComponent("Saludos Equipo,\n\nSe ha generado un nuevo reporte de seguridad por: " + nombre + ".\n\nPueden ver el informe completo aquí: " + portalUrl + "\n\nSaludos Cordiales,\nSistema de Gestión Vitapro.");
            
            window.location.href = `mailto:Fgutierrezv@ibalnor.com.ec?subject=${subject}&body=${body}`;
        }
    </script>
</body>
</html>
