<?php
// Configuración obligatoria para sesiones en Vercel
ini_set('session.save_path', '/tmp');
session_start();
// Si no está logueado, lo mandamos al login
if (!isset($_SESSION['admin_logged_in'])) { 
    header('Location: login.php'); 
    exit; 
}
require_once 'conexion.php';
// Obtener estadísticas para KPIs (PostgreSQL)
$q = $pdo->query("SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN nivel_riesgo = 'Alto' THEN 1 ELSE 0 END) as alto
    FROM reportes")->fetch();
// Obtener los últimos 50 reportes
$reportes = $pdo->query("SELECT * FROM reportes ORDER BY id DESC LIMIT 50")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>SST Dashboard Premium - Vitapro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/font-awesome.min.css">
    <style>
        body { background: #f8fafc; font-family: 'Outfit', sans-serif; }
        .sidebar { width: 260px; background: #1e293b; color: white; min-height: 100vh; position: fixed; padding: 25px; }
        .content { margin-left: 260px; padding: 40px; }
        .glass-card { background: white; border-radius: 20px; padding: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .riesgo-alto { border: 2px solid #ef4444; animation: pulse 2s infinite; }
        @keyframes pulse { 0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4); } 70% { box-shadow: 0 0 0 10px rgba(239, 68, 68, 0); } 100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); } }
    </style>
</head>
<body>
    <div class="sidebar">
        <h4 class="fw-bold text-info">VITAPRO SST</h4>
        <nav class="mt-5">
            <a href="admin_nuevo.php" class="nav-link text-white mb-3"><i class="fa fa-dashboard me-2"></i> Dashboard</a>
            <a href="#" class="nav-link text-white mb-3" data-bs-toggle="modal" data-bs-target="#modalQR"><i class="fa fa-qrcode me-2"></i> Generar QR</a>
            <a href="index.php" class="nav-link text-white"><i class="fa fa-plus-circle me-2"></i> Formulario</a>
            <hr>
            <a href="logout.php" class="nav-link text-danger"><i class="fa fa-sign-out me-2"></i> Salir</a>
        </nav>
    </div>
    <div class="content">
        <h2 class="fw-bold mb-4">Monitor de Seguridad Industrial</h2>
        
        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="glass-card">
                    <h6 class="text-muted fw-bold">TOTAL REPORTES</h6>
                    <h2 class="fw-bold"><?php echo $q['total']; ?></h2>
                </div>
            </div>
            <div class="col-md-4">
                <div class="glass-card <?php echo $q['alto'] > 0 ? 'riesgo-alto' : ''; ?>">
                    <h6 class="text-danger fw-bold">riesgos ALTOS</h6>
                    <h2 class="fw-bold text-danger"><?php echo $q['alto'] ?: 0; ?></h2>
                </div>
            </div>
        </div>
        <div class="glass-card">
            <table class="table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Hallazgo</th>
                        <th>Nivel</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($reportes as $r): ?>
                    <tr>
                        <td><?php echo date('d/m/y', strtotime($r['fecha'])); ?></td>
                        <td><strong><?php echo $r['tipo_hallazgo']; ?></strong></td>
                        <td><span class="badge bg-<?php echo $r['nivel_riesgo']=='Alto'?'danger':'success'; ?>"><?php echo $r['nivel_riesgo']; ?></span></td>
                        <td>
                            <a href="generar_informe.php?id=<?php echo $r['id']; ?>" class="btn btn-sm btn-outline-secondary" target="_blank">PDF</a>
                            <button onclick="enviarOutlook('<?php echo $r['aviso_sap']; ?>', '<?php echo $r['nombre']; ?>', '<?php echo $r['id']; ?>')" class="btn btn-sm btn-primary">Outlook</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <!-- Modal QR -->
    <div class="modal fade" id="modalQR" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-center p-5">
                <h5 class="fw-bold mb-4">Código QR para Planta</h5>
                <img src="https://chart.googleapis.com/chart?chs=250x250&cht=qr&chl=<?php echo urlencode('https://'.$_SERVER['HTTP_HOST'].'/index.php'); ?>" class="img-fluid mx-auto" width="200">
                <button class="btn btn-primary mt-4" onclick="window.print()">IMPRIMIR PARA PLANTA</button>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Alarma Sonora 🚨
        const siren = new Audio('https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3');
        <?php if($q['alto'] > 0): ?>
            siren.play().catch(e => console.log("Haga clic para activar sonido"));
        <?php endif; ?>
        function enviarOutlook(sap, nombre, id) {
            const subject = "Notificación SST Vitapro SAP #" + sap;
            const link = window.location.origin + "/generar_informe.php?id=" + id;
            window.location.href = `mailto:Fgutierrezv@ibalnor.com.ec?subject=${subject}&body=Reporte de: ${nombre}\nLink: ${link}`;
        }
    </script>
</body>
</html>
