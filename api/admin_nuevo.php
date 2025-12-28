<?php
// Verificación de Cookie (Seguridad Vercel)
if (!isset($_COOKIE['auth_token']) || $_COOKIE['auth_token'] !== 'vitapro_admin_logged') {
    header('Location: login.php');
    exit;
}

require_once 'conexion.php';

try {
    // 1. CONSULTAS PARA TARJETAS (KPIs)
    $sqlKPI = "SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN nivel_riesgo = 'Alto' THEN 1 ELSE 0 END) as criticos,
        SUM(CASE WHEN tipo_hallazgo LIKE '%Acto%' THEN 1 ELSE 0 END) as actos,
        SUM(CASE WHEN tipo_hallazgo LIKE '%Condición%' THEN 1 ELSE 0 END) as condiciones,
        SUM(CASE WHEN aviso_sap IS NOT NULL AND aviso_sap != '' THEN 1 ELSE 0 END) as con_sap
        FROM reportes";
    $kpi = $pdo->query($sqlKPI)->fetch(PDO::FETCH_ASSOC);

    // 2. CONSULTAS PARA GRÁFICOS (TOP 5 REPETIDOS)
    // CORRECCIÓN AQUÍ: Cambié 'descripcion_breve' por 'descripcion'
    // Top Actos
    $sqlActos = "SELECT descripcion, COUNT(*) as cantidad 
                 FROM reportes WHERE tipo_hallazgo LIKE '%Acto%' 
                 GROUP BY descripcion ORDER BY cantidad DESC LIMIT 5";
    $topActos = $pdo->query($sqlActos)->fetchAll(PDO::FETCH_ASSOC);

    // Top Condiciones
    $sqlCond = "SELECT descripcion, COUNT(*) as cantidad 
                FROM reportes WHERE tipo_hallazgo LIKE '%Condición%' 
                GROUP BY descripcion ORDER BY cantidad DESC LIMIT 5";
    $topCond = $pdo->query($sqlCond)->fetchAll(PDO::FETCH_ASSOC);

    // 3. TABLA GENERAL
    $reportes = $pdo->query("SELECT * FROM reportes ORDER BY id DESC LIMIT 50")->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error cargando datos: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Gerencial SST</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        :root { --primary: #0f172a; --accent: #2563eb; --bg: #f1f5f9; }
        body { background: var(--bg); font-family: 'Outfit', sans-serif; }
        
        /* Sidebar */
        .sidebar { width: 250px; background: var(--primary); min-height: 100vh; position: fixed; padding: 20px; color: white; }
        .nav-link { color: #94a3b8; padding: 12px; margin-bottom: 5px; border-radius: 8px; transition: 0.3s; }
        .nav-link:hover, .nav-link.active { background: rgba(255,255,255,0.1); color: white; }
        
        /* Contenido */
        .content { margin-left: 250px; padding: 30px; }
        
        /* Tarjetas KPI */
        .kpi-card { background: white; border-radius: 12px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); border-left: 5px solid var(--accent); height: 100%; }
        .kpi-title { font-size: 0.85rem; text-transform: uppercase; color: #64748b; font-weight: 700; letter-spacing: 0.5px; }
        .kpi-value { font-size: 2rem; font-weight: 700; color: var(--primary); margin: 5px 0 0 0; }
        
        /* Gráficos y Tablas */
        .dashboard-panel { background: white; border-radius: 16px; padding: 25px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); margin-bottom: 25px; }
        
        /* Colores Específicos */
        .border-danger { border-left-color: #ef4444 !important; } /* Críticos */
        .border-success { border-left-color: #10b981 !important; } /* SAP */
        .border-warning { border-left-color: #f59e0b !important; } /* Actos */
        .border-info { border-left-color: #06b6d4 !important; } /* Condiciones */
    </style>
</head>
<body>

    <div class="sidebar">
        <h4 class="fw-bold mb-5"><i class="fas fa-shield-alt me-2"></i>VITAPRO SST</h4>
        <nav class="nav flex-column">
            <a href="#" class="nav-link active"><i class="fas fa-chart-pie me-3"></i>Dashboard</a>
            <a href="index.php" class="nav-link" target="_blank"><i class="fas fa-plus me-3"></i>Nuevo Reporte</a>
            <a href="#" class="nav-link" data-bs-toggle="modal" data-bs-target="#modalQR"><i class="fas fa-qrcode me-3"></i>Ver QR</a>
            <hr class="text-secondary">
            <a href="logout.php" class="nav-link text-danger"><i class="fas fa-sign-out-alt me-3"></i>Salir</a>
        </nav>
    </div>

    <div class="content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold text-dark">Panel de Control Gerencial</h2>
            <div class="d-flex gap-2">
                <a href="exportar_excel.php" class="btn btn-success"><i class="fas fa-file-excel me-2"></i>Descargar Excel</a>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md"> <div class="kpi-card">
                    <div class="kpi-title">Total Reportes</div>
                    <div class="kpi-value"><?php echo $kpi['total']; ?></div>
                </div>
            </div>
            <div class="col-md"> <div class="kpi-card border-warning">
                    <div class="kpi-title">Actos Subestándar</div>
                    <div class="kpi-value"><?php echo $kpi['actos']; ?></div>
                </div>
            </div>
            <div class="col-md"> <div class="kpi-card border-info">
                    <div class="kpi-title">Condiciones Sub.</div>
                    <div class="kpi-value"><?php echo $kpi['condiciones']; ?></div>
                </div>
            </div>
            <div class="col-md"> <div class="kpi-card border-danger">
                    <div class="kpi-title text-danger">Nivel Crítico</div>
                    <div class="kpi-value text-danger"><?php echo $kpi['criticos']; ?></div>
                </div>
            </div>
            <div class="col-md"> <div class="kpi-card border-success">
                    <div class="kpi-title text-success">Con Aviso SAP</div>
                    <div class="kpi-value text-success"><?php echo $kpi['con_sap']; ?></div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6">
                <div class="dashboard-panel">
                    <h5 class="fw-bold mb-4">Top Actos Inseguros (Pareto)</h5>
                    <canvas id="chartActos"></canvas>
                </div>
            </div>
            <div class="col-md-6">
                <div class="dashboard-panel">
                    <h5 class="fw-bold mb-4">Top Condiciones Inseguras (Pareto)</h5>
                    <canvas id="chartCondiciones"></canvas>
                </div>
            </div>
        </div>

        <div class="dashboard-panel">
            <h5 class="fw-bold mb-3">Matriz de Seguimiento</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Fecha</th>
                            <th>Hallazgo</th>
                            <th>Descripción</th>
                            <th>Riesgo</th>
                            <th>SAP</th>
                            <th class="text-end">Informe</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($reportes as $r): ?>
                        <tr>
                            <td class="fw-bold">#<?php echo $r['id']; ?></td>
                            <td><?php echo date('d/m/Y', strtotime($r['fecha'])); ?></td>
                            <td>
                                <span class="badge <?php echo strpos($r['tipo_hallazgo'], 'Acto') !== false ? 'bg-warning text-dark' : 'bg-info text-dark'; ?>">
                                    <?php echo $r['tipo_hallazgo']; ?>
                                </span>
                            </td>
                            <td class="small text-muted">
                                <?php echo substr($r['descripcion'] ?? $r['detalle'] ?? 'Sin descripción', 0, 40) . '...'; ?>
                            </td>
                            <td>
                                <span class="badge <?php echo $r['nivel_riesgo'] == 'Alto' ? 'bg-danger' : 'bg-success'; ?>">
                                    <?php echo $r['nivel_riesgo']; ?>
                                </span>
                            </td>
                            <td>
                                <?php if($r['aviso_sap']): ?>
                                    <span class="badge bg-primary"><?php echo $r['aviso_sap']; ?></span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="generar_informe.php?id=<?php echo $r['id']; ?>" target="_blank" class="btn btn-sm btn-outline-danger">
                                    <i class="fas fa-file-pdf"></i> PDF
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalQR" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered text-center">
            <div class="modal-content p-4">
                <h4>Código QR Planta</h4>
                <img src="https://chart.googleapis.com/chart?chs=250x250&cht=qr&chl=<?php echo urlencode('https://'.$_SERVER['HTTP_HOST'].'/index.php'); ?>" class="img-fluid mx-auto my-3">
                <button class="btn btn-dark" onclick="window.print()">Imprimir</button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // CORRECCIÓN AQUÍ: Cambiamos 'descripcion_breve' por 'descripcion' en los labels
        const dataActos = {
            labels: <?php echo json_encode(array_column($topActos, 'descripcion')); ?>,
            datasets: [{
                label: 'Frecuencia',
                data: <?php echo json_encode(array_column($topActos, 'cantidad')); ?>,
                backgroundColor: '#f59e0b',
                borderRadius: 5
            }]
        };

        const dataCond = {
            labels: <?php echo json_encode(array_column($topCond, 'descripcion')); ?>,
            datasets: [{
                label: 'Frecuencia',
                data: <?php echo json_encode(array_column($topCond, 'cantidad')); ?>,
                backgroundColor: '#06b6d4',
                borderRadius: 5
            }]
        };

        new Chart(document.getElementById('chartActos'), {
            type: 'bar',
            data: dataActos,
            options: { indexAxis: 'y', responsive: true, plugins: { legend: { display: false } } }
        });

        new Chart(document.getElementById('chartCondiciones'), {
            type: 'bar',
            data: dataCond,
            options: { indexAxis: 'y', responsive: true, plugins: { legend: { display: false } } }
        });
    </script>
</body>
</html>
