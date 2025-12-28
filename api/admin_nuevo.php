<?php
/**
 * VITAPRO SST - DASHBOARD GERENCIAL PRO
 * Sistema de Seguridad Industrial con Verificación por Cookies
 */
// 1. Verificación de Seguridad para Vercel
if (!isset($_COOKIE['auth_token']) || $_COOKIE['auth_token'] !== 'vitapro_admin_logged') {
    header('Location: login.php');
    exit;
}
require_once 'conexion.php';
try {
    // 2. Consultas de Datos (PostgreSQL/Supabase)
    $sqlKPI = "SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN nivel_riesgo = 'Alto' THEN 1 ELSE 0 END) as criticos,
        SUM(CASE WHEN tipo_hallazgo LIKE '%Acto%' THEN 1 ELSE 0 END) as actos,
        SUM(CASE WHEN tipo_hallazgo LIKE '%Condición%' THEN 1 ELSE 0 END) as condiciones
        FROM reportes";
    $kpi = $pdo->query($sqlKPI)->fetch(PDO::FETCH_ASSOC);
    // Consulta de los últimos 50 reportes para la matriz
    $reportes = $pdo->query("SELECT * FROM reportes ORDER BY id DESC LIMIT 50")->fetchAll(PDO::FETCH_ASSOC);
    
    // Datos para Gráficos
    $topActos = $pdo->query("SELECT causa_especifica as descrip, COUNT(*) as cant FROM reportes WHERE tipo_hallazgo LIKE '%Acto%' GROUP BY causa_especifica ORDER BY cant DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    $topCond = $pdo->query("SELECT causa_especifica as descrip, COUNT(*) as cant FROM reportes WHERE tipo_hallazgo LIKE '%Condición%' GROUP BY causa_especifica ORDER BY cant DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) { 
    die("Error de conexión: " . $e->getMessage()); 
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SST Dashboard Premium - Vitapro</title>
    <!-- Librerías Premium -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root { --primary: #0f172a; --accent: #2563eb; --sidebar: #1e293b; --bg: #f1f5f9; }
        body { background: var(--bg); font-family: 'Outfit', sans-serif; color: #1e293b; }
        
        /* Sidebar */
        .sidebar { width: 260px; background: var(--sidebar); min-height: 100vh; position: fixed; padding: 25px; color: white; box-shadow: 4px 0 10px rgba(0,0,0,0.1); }
        .nav-link { color: #94a3b8; padding: 12px 15px; margin-bottom: 8px; border-radius: 12px; transition: 0.3s; display: flex; align-items: center; text-decoration: none; }
        .nav-link i { width: 25px; font-size: 1.1rem; }
        .nav-link:hover, .nav-link.active { background: rgba(255,255,255,0.1); color: white; }
        
        /* Layout */
        .main-content { margin-left: 260px; padding: 40px; }
        
        /* Cards */
        .glass-card { background: white; border-radius: 20px; padding: 25px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); border: 1px solid rgba(255,255,255,0.8); margin-bottom: 25px; }
        
        /* KPI Stats */
        .stat-card { border-left: 6px solid var(--accent); }
        .stat-label { font-size: 0.75rem; text-transform: uppercase; font-weight: 800; color: #64748b; letter-spacing: 1px; }
        .stat-value { font-size: 2.2rem; font-weight: 700; margin: 0; line-height: 1; }
        
        /* Table */
        .table { margin: 0; }
        .table thead th { background: #f8fafc; border: none; font-size: 0.75rem; text-transform: uppercase; padding: 15px; color: #64748b; }
        .table tbody td { padding: 15px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; }
        .img-thumb { width: 45px; height: 45px; object-fit: cover; border-radius: 10px; cursor: pointer; transition: 0.3s; }
        .img-thumb:hover { transform: scale(1.1); }
        
        /* Badges */
        .badge-riesgo { padding: 6px 12px; border-radius: 20px; font-weight: 700; font-size: 10px; text-transform: uppercase; }
        
        @media (max-width: 991px) {
            .sidebar { width: 80px; padding: 15px 10px; text-align: center; }
            .sidebar h4, .nav-link span { display: none; }
            .main-content { margin-left: 80px; padding: 20px; }
        }
    </style>
</head>
<body>
    <!-- Menú Lateral -->
    <div class="sidebar">
        <h4 class="fw-bold mb-5 text-center text-info"><i class="fas fa-shield-alt"></i> <span>DASHBOARD</span></h4>
        <nav class="nav flex-column">
            <a href="admin_nuevo.php" class="nav-link active"><i class="fas fa-chart-pie"></i> <span>Métricas</span></a>
            <a href="index.php" class="nav-link"><i class="fas fa-plus-circle"></i> <span>Nuevo Registro</span></a>
            <a href="#" class="nav-link" data-bs-toggle="modal" data-bs-target="#modalQR"><i class="fas fa-qrcode"></i> <span>Generar QR</span></a>
            <hr class="text-secondary">
            <a href="exportar_excel.php" class="nav-link text-success"><i class="fas fa-file-excel"></i> <span>Excel Matriz</span></a>
            <a href="logout.php" class="nav-link text-danger mt-auto"><i class="fas fa-sign-out-alt"></i> <span>Cerrar Sesión</span></a>
        </nav>
    </div>
    <!-- Contenido Principal -->
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h2 class="fw-bold m-0">SST Monitor Vitapro</h2>
                <p class="text-muted small m-0">Consola de Seguridad y Salud en el Trabajo</p>
            </div>
            <div class="text-end">
                <span class="badge bg-white text-dark shadow-sm p-2 px-3 border rounded-pill">
                    <i class="fas fa-calendar-alt me-2 text-primary"></i> <?php echo date('d M, Y'); ?>
                </span>
            </div>
        </div>
        <!-- Fila de Indicadores (KPIs) -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="glass-card stat-card">
                    <p class="stat-label">Total Reportes</p>
                    <h3 class="stat-value"><?php echo $kpi['total'] ?: 0; ?></h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="glass-card stat-card" style="border-left-color: #f59e0b;">
                    <p class="stat-label">Actos Registrados</p>
                    <h3 class="stat-value"><?php echo $kpi['actos'] ?: 0; ?></h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="glass-card stat-card" style="border-left-color: #06b6d4;">
                    <p class="stat-label">Condiciones Sub.</p>
                    <h3 class="stat-value"><?php echo $kpi['condiciones'] ?: 0; ?></h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="glass-card stat-card" style="border-left-color: #ef4444;">
                    <p class="stat-label text-danger">Riesgos Críticos</p>
                    <h3 class="stat-value text-danger"><?php echo $kpi['criticos'] ?: 0; ?></h3>
                </div>
            </div>
        </div>
        <!-- Fila de Gráficos de Pareto -->
        <div class="row g-4 mb-4">
            <div class="col-lg-6">
                <div class="glass-card">
                    <h6 class="fw-bold mb-4"><i class="fas fa-chart-bar text-warning me-2"></i>Top 5 Actos Subestándar</h6>
                    <canvas id="chartActos" height="200"></canvas>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="glass-card">
                    <h6 class="fw-bold mb-4"><i class="fas fa-chart-bar text-info me-2"></i>Top 5 Condiciones Inseguras</h6>
                    <canvas id="chartCondiciones" height="200"></canvas>
                </div>
            </div>
        </div>
        <!-- Matriz de Hallazgos con Fotos -->
        <div class="glass-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold m-0 text-primary">Matriz de Seguimiento SST</h5>
                <button onclick="window.location.reload()" class="btn btn-sm btn-light border rounded-pill"><i class="fas fa-sync-alt"></i></button>
            </div>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Foto</th>
                            <th>Hallazgo / Causa</th>
                            <th>Riesgo</th>
                            <th>Descripción</th>
                            <th class="text-end">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($reportes as $r): ?>
                        <tr>
                            <td class="fw-bold text-muted" style="font-size: 11px;">#<?php echo $r['id']; ?></td>
                            <td>
                                <?php if($r['foto_path']): ?>
                                    <img src="<?php echo $r['foto_path']; ?>" class="img-thumb" data-bs-toggle="modal" data-bs-target="#imgModal<?php echo $r['id']; ?>">
                                    <!-- Modal Mini-Visor -->
                                    <div class="modal fade" id="imgModal<?php echo $r['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered"><div class="modal-content border-0 bg-transparent"><img src="<?php echo $r['foto_path']; ?>" class="img-fluid rounded-4 shadow"></div></div>
                                    </div>
                                <?php else: ?>
                                    <div class="bg-light rounded text-muted d-flex align-items-center justify-content-center" style="width:45px; height:45px; font-size:10px;">N/A</div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="d-block fw-bold small"><?php echo $r['tipo_hallazgo']; ?></span>
                                <span class="text-muted" style="font-size: 11px;"><?php echo $r['causa_especifica']; ?></span>
                            </td>
                            <td>
                                <span class="badge-riesgo <?php echo $r['nivel_riesgo'] == 'Alto' ? 'bg-danger text-white' : 'bg-success text-white'; ?>">
                                    <?php echo $r['nivel_riesgo']; ?>
                                </span>
                            </td>
                            <td class="small text-muted" style="max-width:300px;"><?php echo htmlspecialchars(substr($r['descripcion'], 0, 80)) . '...'; ?></td>
                            <td class="text-end">
                                <a href="generar_informe.php?id=<?php echo $r['id']; ?>" target="_blank" class="btn btn-sm btn-outline-danger border-0 rounded-pill"><i class="fas fa-file-pdf"></i> PDF</a>
                                <button onclick="enviarOutlook('<?php echo $r['aviso_sap']; ?>', '<?php echo htmlspecialchars($r['nombre']); ?>', '<?php echo $r['id']; ?>')" class="btn btn-sm btn-outline-primary border-0 rounded-pill"><i class="fab fa-microsoft text-primary"></i></button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- Modal QR Fijo -->
    <div class="modal fade" id="modalQR" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg border-0" style="border-radius: 30px;">
                <div class="modal-body p-5 text-center">
                    <i class="fas fa-qrcode fa-3x text-primary mb-4"></i>
                    <h3 class="fw-bold">Código QR Vitapro</h3>
                    <p class="text-muted mb-4 small">Imprima este código para que los reportes lleguen directo a su celular desde la planta.</p>
                    <div class="bg-light p-4 rounded-4 d-inline-block border mb-4">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=<?php echo urlencode('https://'.$_SERVER['HTTP_HOST'].'/'); ?>" class="img-fluid" style="width:250px;">
                    </div>
                    <div class="d-grid"><button class="btn btn-primary btn-lg rounded-pill fw-bold" onclick="window.print()">IMPRIMIR AHORA</button></div>
                </div>
            </div>
        </div>
    </div>
    <!-- Scripts Finales -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Configuración de Gráficos de Pareto
        const cfgActos = {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($topActos, 'descrip')); ?>,
                datasets: [{ label: 'Frecuencia', data: <?php echo json_encode(array_column($topActos, 'cant')); ?>, backgroundColor: '#f59e0b', borderRadius: 8 }]
            },
            options: { indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { display: false } } }
        };
        const cfgCond = {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($topCond, 'descrip')); ?>,
                datasets: [{ label: 'Frecuencia', data: <?php echo json_encode(array_column($topCond, 'cant')); ?>, backgroundColor: '#06b6d4', borderRadius: 8 }]
            },
            options: { indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { display: false } } }
        };
        new Chart(document.getElementById('chartActos'), cfgActos);
        new Chart(document.getElementById('chartCondiciones'), cfgCond);
        function enviarOutlook(sap, nombre, id) {
            const subject = encodeURIComponent("Gestión SST Vitapro - Notificación #" + id);
            const link = window.location.origin + "/generar_informe.php?id=" + id;
            const body = encodeURIComponent("Hola,\nSe ha generado un nuevo reporte de seguridad por: " + nombre + ".\nVer aquí: " + link);
            window.location.href = `mailto:Fgutierrezv@ibalnor.com.ec?subject=${subject}&body=${body}`;
        }
    </script>
</body>
</html>
