<?php
session_start();
// Seguridad: Si no hay sesión, regresamos al index
if (!isset($_SESSION['admin_logged_in'])) { header('Location: index.php'); exit; }
require_once 'conexion.php';
// 1. OBTENER ESTADÍSTICAS PARA KPIs
$q = $pdo->query("SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN tipo_hallazgo = 'Acto Subestándar' THEN 1 ELSE 0 END) as actos,
    SUM(CASE WHEN tipo_hallazgo = 'Condición Subestándar' THEN 1 ELSE 0 END) as cond,
    SUM(CASE WHEN nivel_riesgo = 'Alto' THEN 1 ELSE 0 END) as alto
    FROM reportes")->fetch();
// 2. DATOS PARA GRÁFICO DE ACTOS (Top 5 Causas)
$sql_actos = "SELECT causa_especifica, COUNT(*) as cantidad 
              FROM reportes WHERE tipo_hallazgo = 'Acto Subestándar' 
              GROUP BY causa_especifica ORDER BY cantidad DESC LIMIT 5";
$actos_chart = $pdo->query($sql_actos)->fetchAll(PDO::FETCH_ASSOC);
// 3. DATOS PARA GRÁFICO DE CONDICIONES (Top 5 Causas)
$sql_cond = "SELECT causa_especifica, COUNT(*) as cantidad 
             FROM reportes WHERE tipo_hallazgo = 'Condición Subestándar' 
             GROUP BY causa_especifica ORDER BY cantidad DESC LIMIT 5";
$cond_chart = $pdo->query($sql_cond)->fetchAll(PDO::FETCH_ASSOC);
// 4. FILTRADO (Botón Solo SAP)
$where = "";
if (isset($_GET['filter_sap'])) {
    $where = " WHERE aviso_sap IS NOT NULL AND aviso_sap <> '' ";
}
$stmt_reportes = $pdo->query("SELECT * FROM reportes $where ORDER BY id DESC LIMIT 50");
$reportes = $stmt_reportes->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SST Dashboard Premium - Vitapro</title>
    
    <!-- Librerías Externas -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary: #0f172a;
            --secondary: #2563eb;
            --accent: #f59e0b;
            --danger: #e11d48;
            --success: #10b981;
            --sidebar-bg: #1e293b;
            --card-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --bg-body: #f1f5f9;
        }
        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-body);
            color: #1e293b;
            margin: 0;
            display: flex;
            min-height: 100vh;
        }
        /* Sidebar Glassmorphism */
        .sidebar {
            width: 260px;
            background: var(--sidebar-bg);
            color: white;
            padding: 30px 15px;
            position: sticky;
            top: 0;
            height: 100vh;
            box-shadow: 4px 0 10px rgba(0,0,0,0.05);
            z-index: 1000;
        }
        .sidebar h2 {
            font-size: 1.4rem;
            font-weight: 800;
            margin-bottom: 40px;
            padding: 0 10px;
            background: linear-gradient(to right, #60a5fa, #2563eb);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .nav-link {
            color: #94a3b8;
            padding: 12px 18px;
            border-radius: 12px;
            margin-bottom: 5px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            text-decoration: none;
            font-weight: 500;
        }
        .nav-link:hover {
            color: white;
            background: rgba(255,255,255,0.05);
            transform: translateX(5px);
        }
        .nav-link.active {
            background: var(--secondary);
            color: white;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }
        .nav-link i { width: 26px; font-size: 1.1rem; }
        /* Contenido Principal */
        .content {
            flex-grow: 1;
            padding: 30px 40px;
            max-width: 1600px;
        }
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
        }
        /* Tarjetas Premium */
        .glass-card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            border: 1px solid rgba(255,255,255,0.8);
            box-shadow: var(--card-shadow);
            transition: transform 0.3s ease;
        }
        .glass-card:hover { transform: translateY(-5px); }
        .kpi-title { font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 1px; }
        .kpi-value { font-size: 2.4rem; font-weight: 800; color: var(--primary); margin: 8px 0; }
        
        /* Gráficos */
        .chart-card { min-height: 380px; }
        .chart-header h5 { font-weight: 800; color: var(--primary); font-size: 1rem; margin-bottom: 20px; }
        /* Tabla Estilizada */
        .table-container {
            background: white;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: var(--card-shadow);
            margin-top: 30px;
        }
        .table thead th {
            background: #f8fafc;
            color: #475569;
            font-weight: 800;
            font-size: 0.75rem;
            text-transform: uppercase;
            padding: 20px;
            border-bottom: 2px solid #f1f5f9;
        }
        .table tbody td {
            padding: 18px 20px;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.9rem;
        }
        .badge-riesgo {
            padding: 6px 14px;
            border-radius: 10px;
            font-weight: 800;
            font-size: 0.65rem;
            text-transform: uppercase;
        }
        /* Botones Outlook y PDF */
        .btn-outlook {
            background: #0078d4;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 0.8rem;
            display: inline-flex;
            align-items: center;
            transition: 0.3s;
            text-decoration: none;
        }
        .btn-outlook:hover { background: #005a9e; color: white; transform: scale(1.05); }
        .btn-pdf {
            background: #f1f5f9;
            color: #475569;
            width: 38px;
            height: 38px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            text-decoration: none;
            transition: 0.3s;
        }
        .btn-pdf:hover { background: #fee2e2; color: #e11d48; }
        .thumb-box {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            background: #f1f5f9;
            overflow: hidden;
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .thumb-box img { width: 100%; height: 100%; object-fit: cover; }
    </style>
</head>
<body>
    <!-- Sidebar Navegación -->
    <div class="sidebar">
        <h2>VITAPRO SST</h2>
        <nav>
            <a href="admin_nuevo.php" class="nav-link active"><i class="fas fa-th-large"></i> Dashboard</a>
            <a href="admin_nuevo.php?filter_sap=1" class="nav-link"><i class="fas fa-search-dollar"></i> Registros SAP</a>
            <a href="#" class="nav-link" data-bs-toggle="modal" data-bs-target="#modalQR"><i class="fas fa-qrcode"></i> Descargar QR</a>
            <a href="exportar_excel.php" class="nav-link"><i class="fas fa-file-excel"></i> Exportar Todo</a>
            <a href="index.php" class="nav-link"><i class="fas fa-plus-circle"></i> Nuevo Reporte</a>
            <div style="margin-top: 60px;">
                <a href="logout.php" class="nav-link text-danger"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
            </div>
        </nav>
    </div>
    <!-- Contenido Principal -->
    <div class="content">
        
        <!-- Top Bar -->
        <div class="top-bar">
            <div>
                <h3 class="fw-bold mb-0">Monitor de Seguridad</h3>
                <p class="text-muted small mb-0">Analítica SST - Vitapro Cero Accidentes</p>
            </div>
            <div class="d-flex align-items-center bg-white p-2 rounded-4 shadow-sm px-3">
                <div class="text-end me-3">
                    <div class="fw-bold fs-6"><?php echo $_SESSION['admin_name'] ?? 'Admin SST'; ?></div>
                    <div class="text-muted" style="font-size: 11px;">Administrador Central</div>
                </div>
                <img src="https://ui-avatars.com/api/?name=Admin&background=0f172a&color=fff" class="rounded-circle" width="40">
            </div>
        </div>
        <!-- KPIs Dinámicos -->
        <div class="row g-4 mb-5">
            <div class="col-md-3">
                <div class="glass-card">
                    <div class="kpi-title">Actos Subestándar</div>
                    <div class="kpi-value text-primary"><?php echo $q['actos'] ?: 0; ?></div>
                    <div class="progress" style="height: 5px;"><div class="progress-bar bg-primary" style="width: 70%"></div></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="glass-card">
                    <div class="kpi-title">Condiciones Subest.</div>
                    <div class="kpi-value text-warning"><?php echo $q['cond'] ?: 0; ?></div>
                    <div class="progress" style="height: 5px;"><div class="progress-bar bg-warning" style="width: 50%"></div></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="glass-card">
                    <div class="kpi-title">Alertas Riesgo Alto</div>
                    <div class="kpi-value text-danger"><?php echo $q['alto'] ?: 0; ?></div>
                    <div class="progress" style="height: 5px;"><div class="progress-bar bg-danger" style="width: 30%"></div></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="glass-card bg-primary text-white">
                    <div class="kpi-title text-white opacity-75">Base de Datos</div>
                    <div class="kpi-value text-white"><?php echo $q['total']; ?></div>
                    <div class="small opacity-50">Registros en Matriz</div>
                </div>
            </div>
        </div>
        <!-- Fila de Gráficos -->
        <div class="row g-4 mb-5">
            <div class="col-md-6">
                <div class="glass-card chart-card">
                    <div class="chart-header">
                        <h5>Tendencia: Top 5 Actos</h5>
                    </div>
                    <div style="height: 280px;"><canvas id="chartActos"></canvas></div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="glass-card chart-card">
                    <div class="chart-header">
                        <h5>Tendencia: Top 5 Condiciones</h5>
                    </div>
                    <div style="height: 280px;"><canvas id="chartCondiciones"></canvas></div>
                </div>
            </div>
        </div>
        <!-- Matriz de Gestión -->
        <div class="table-container">
            <div class="p-4 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0">Gestión de Hallazgos y Seguimiento</h5>
                <div class="btn-group">
                    <a href="admin_nuevo.php" class="btn btn-light btn-sm px-4">Todos</a>
                    <a href="admin_nuevo.php?filter_sap=1" class="btn btn-primary btn-sm px-4">Filtro SAP</a>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Hallazgo</th>
                            <th>Persona / Empresa</th>
                            <th>Área</th>
                            <th class="text-center">SAP</th>
                            <th class="text-center">Riesgo</th>
                            <th class="text-end">Herramientas</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($reportes as $r): ?>
                        <tr>
                            <td>
                                <div class="fw-bold"><?php echo date('d M, Y', strtotime($r['fecha'])); ?></div>
                                <div class="text-muted small"><?php echo date('H:i', strtotime($r['fecha'])); ?></div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <?php if($r['foto_path']): ?>
                                        <div class="thumb-box me-3" onclick="verImagen('<?php echo $r['foto_path']; ?>')">
                                            <img src="<?php echo $r['foto_path']; ?>">
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <div class="fw-bold"><?php echo $r['tipo_hallazgo']; ?></div>
                                        <div class="text-muted small"><?php echo substr($r['causa_especifica'], 0, 25); ?>...</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-bold"><?php echo $r['nombre']; ?></div>
                                <div class="text-muted small"><?php echo $r['empresa_contratista'] ?: 'Personal Interno'; ?></div>
                            </td>
                            <td><span class="badge bg-light text-dark border"><?php echo $r['area']; ?></span></td>
                            <td class="text-center">
                                <?php if($r['aviso_sap']): ?>
                                    <span class="fw-bold text-primary">#<?php echo $r['aviso_sap']; ?></span>
                                    <?php if($r['detuvo_actividad']=='SI'): ?>
                                        <div class="text-danger fw-bold" style="font-size: 9px;">PARO</div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php $c = $r['nivel_riesgo']=='Alto'?'danger':($r['nivel_riesgo']=='Medio'?'warning':'success'); ?>
                                <span class="badge-riesgo bg-<?php echo $c; ?> text-white"><?php echo $r['nivel_riesgo']; ?></span>
                            </td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="generar_informe.php?id=<?php echo $r['id']; ?>" class="btn-pdf" target="_blank" title="Reporte PDF"><i class="fas fa-file-pdf"></i></a>
                                    <?php if($r['aviso_sap']): ?>
                                        <button class="btn-outlook" onclick="enviarOutlook('<?php echo $r['aviso_sap']; ?>', '<?php echo $r['nombre']; ?>', '<?php echo $r['area']; ?>', '<?php echo $r['nivel_riesgo']; ?>', '<?php echo $r['id']; ?>')">
                                            <i class="fab fa-microsoft me-2"></i> Outlook
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- Modales -->
    <div class="modal fade" id="modalImagen" tabindex="-1"><div class="modal-dialog modal-dialog-centered modal-lg"><div class="modal-content"><div class="modal-body p-0"><img id="imgFull" src="" class="img-fluid w-100 rounded shadow"></div></div></div></div>
    <div class="modal fade" id="modalQR" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content overflow-hidden border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0"><h5 class="modal-title fw-bold">Acceso Digital Planta</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
                <div class="modal-body text-center p-5">
                    <p class="text-muted mb-4">Escanee para reportar un Acto o Condición</p>
                    <div class="p-4 bg-white d-inline-block border rounded-4">
                        <?php 
                            $actual_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]/index.php";
                            $qr_url = "https://chart.googleapis.com/chart?chs=250x250&cht=qr&chl=" . urlencode($actual_url);
                        ?>
                        <img src="<?php echo $qr_url; ?>" alt="QR Vitapro" width="220">
                    </div>
                    <div class="mt-4 fw-bold text-primary fs-5">VITAPRO SEGURIDAD</div>
                </div>
                <div class="modal-footer border-0 p-4"><button class="btn btn-primary w-100 py-3 rounded-pill fw-bold" onclick="window.print()"><i class="fas fa-print me-2"></i> IMPRIMIR CARTEL</button></div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Gráficos Trend
        const options = { indexAxis: 'y', responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { grid: { display:false }, beginAtZero:true } } };
        
        new Chart(document.getElementById('chartActos').getContext('2d'), {
            type: 'bar', data: { labels: [<?php foreach($actos_chart as $a) echo '"'.$a['causa_especifica'].'",'; ?>], datasets: [{ data: [<?php foreach($actos_chart as $a) echo $a['cantidad'].','; ?>], backgroundColor: '#2563eb', borderRadius: 8 }] }, options: options
        });
        new Chart(document.getElementById('chartCondiciones').getContext('2d'), {
            type: 'bar', data: { labels: [<?php foreach($cond_chart as $c) echo '"'.$c['causa_especifica'].'",'; ?>], datasets: [{ data: [<?php foreach($cond_chart as $c) echo $c['cantidad'].','; ?>], backgroundColor: '#f59e0b', borderRadius: 8 }] }, options: options
        });
        function verImagen(src) { document.getElementById('imgFull').src = src; new bootstrap.Modal(document.getElementById('modalImagen')).show(); }
        function enviarOutlook(sap, nombre, area, riesgo, id) {
            const email = "Fgutierrezv@ibalnor.com.ec";
            const link = window.location.origin + '/generar_informe.php?id=' + id;
            const body = encodeURIComponent(`Reportante: ${nombre}\nÁrea: ${area}\nRiesgo: ${riesgo}\n\nEnlace al Informe: ${link}\n\nPor favor adjunte el PDF descargado.`);
            window.location.href = `mailto:${email}?subject=Notificacion SST Vitapro - SAP #${sap}&body=${body}`;
        }
    </script>
</body>
</html>
