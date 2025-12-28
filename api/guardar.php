<?php
session_start();
// Seguridad: Si no hay sesión, regresamos al login
if (!isset($_SESSION['admin_logged_in'])) { 
    header('Location: login.php'); 
    exit; 
}
require_once 'conexion.php';
/** 
 * 1. OBTENER ESTADÍSTICAS PARA KPIs
 * Ajustado para PostgreSQL (Supabase)
 */
$sql_kpi = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN tipo_hallazgo = 'Acto Subestándar' THEN 1 ELSE 0 END) as actos,
    SUM(CASE WHEN tipo_hallazgo = 'Condición Subestándar' THEN 1 ELSE 0 END) as cond,
    SUM(CASE WHEN nivel_riesgo = 'Alto' THEN 1 ELSE 0 END) as alto
    FROM reportes";
$q = $pdo->query($sql_kpi)->fetch();
/** 
 * 2. DATOS PARA GRÁFICOS (Tendencias)
 */
$sql_actos = "SELECT causa_especifica, COUNT(*) as cantidad 
              FROM reportes WHERE tipo_hallazgo = 'Acto Subestándar' 
              GROUP BY causa_especifica ORDER BY cantidad DESC LIMIT 5";
$actos_chart = $pdo->query($sql_actos)->fetchAll(PDO::FETCH_ASSOC);
$sql_cond = "SELECT causa_especifica, COUNT(*) as cantidad 
             FROM reportes WHERE tipo_hallazgo = 'Condición Subestándar' 
             GROUP BY causa_especifica ORDER BY cantidad DESC LIMIT 5";
$cond_chart = $pdo->query($sql_cond)->fetchAll(PDO::FETCH_ASSOC);
/** 
 * 3. LISTADO DE REPORTES CON FILTRO SAP
 */
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
    <title>SST Dashboard Pro - Vitapro</title>
    
    <!-- Librerías de Clase Mundial -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary: #0f172a;
            --secondary: #2563eb;
            --accent: #f59e0b;
            --danger: #ef4444;
            --sidebar: #1e293b;
            --bg: #f8fafc;
        }
        body { font-family: 'Outfit', sans-serif; background: var(--bg); display: flex; min-height: 100vh; }
        
        /* Sidebar Estilizado */
        .sidebar { width: 260px; background: var(--sidebar); color: white; padding: 25px; position: sticky; top: 0; height: 100vh; }
        .nav-link { color: #94a3b8; padding: 12px; border-radius: 10px; margin-bottom: 5px; display: flex; align-items: center; text-decoration: none; transition: 0.3s; }
        .nav-link:hover, .nav-link.active { background: var(--secondary); color: white; }
        .nav-link i { width: 30px; }
        /* Dashboard Content */
        .content { flex: 1; padding: 40px; }
        .glass-card { background: white; border-radius: 20px; padding: 25px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); border: 1px solid rgba(0,0,0,0.02); }
        
        /* KPIs */
        .kpi-title { font-size: 0.8rem; font-weight: 700; color: #64748b; text-transform: uppercase; }
        .kpi-value { font-size: 2.2rem; font-weight: 800; margin: 10px 0; }
        
        /* Alerta Parpadeante */
        @keyframes parpadeo { 0% { box-shadow: 0 0 0px var(--danger); } 50% { box-shadow: 0 0 20px var(--danger); opacity: 0.8; } 100% { box-shadow: 0 0 0px var(--danger); } }
        .alerta-activa { animation: parpadeo 1.5s infinite; border: 2px solid var(--danger) !important; }
        /* Tabla y Thumbs */
        .table { border-radius: 15px; overflow: hidden; }
        .thumb-box { width: 45px; height: 45px; border-radius: 10px; overflow: hidden; background: #eee; cursor: pointer; }
        .thumb-box img { width: 100%; height: 100%; object-fit: cover; }
        .badge-riesgo { padding: 5px 12px; border-radius: 8px; font-weight: 700; font-size: 0.7rem; }
    </style>
</head>
<body>
    <aside class="sidebar">
        <h4 class="fw-bold mb-4 text-center">VITAPRO <span class="text-info">SST</span></h4>
        <nav>
            <a href="admin_nuevo.php" class="nav-link <?php echo !isset($_GET['filter_sap'])?'active':''; ?>"><i class="fas fa-chart-line"></i> Dashboard</a>
            <a href="admin_nuevo.php?filter_sap=1" class="nav-link <?php echo isset($_GET['filter_sap'])?'active':''; ?>"><i class="fas fa-file-invoice"></i> Solo Avisos SAP</a>
            <a href="#" class="nav-link" data-bs-toggle="modal" data-bs-target="#modalQR"><i class="fas fa-qrcode"></i> Generar QR</a>
            <a href="exportar_excel.php" class="nav-link"><i class="fas fa-file-excel"></i> Descargar Excel</a>
            <hr>
            <a href="logout.php" class="nav-link text-danger mt-5"><i class="fas fa-power-off"></i> Salir</a>
        </nav>
    </aside>
    <main class="content">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h2 class="fw-bold m-0">Consola de Mando SST</h2>
                <p class="text-muted">Análisis de Actos y Condiciones en Tiempo Real</p>
            </div>
            <div class="text-end">
                <span class="badge bg-success mb-2">● Sistema Online</span>
                <p class="small text-muted mb-0">Bienvenido, <strong><?php echo $_SESSION['admin_name']; ?></strong></p>
            </div>
        </div>
        <!-- KPIs Dinámicos -->
        <div class="row g-4 mb-5">
            <div class="col-md-3">
                <div class="glass-card">
                    <div class="kpi-title">Actos Reportados</div>
                    <div class="kpi-value text-primary"><?php echo $q['actos'] ?: 0; ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="glass-card">
                    <div class="kpi-title">Condiciones Críticas</div>
                    <div class="kpi-value text-warning"><?php echo $q['cond'] ?: 0; ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div id="kpiAlto" class="glass-card <?php echo $q['alto'] > 0 ? 'alerta-activa' : ''; ?>">
                    <div class="kpi-title">Riesgos ALTOS</div>
                    <div class="kpi-value text-danger"><?php echo $q['alto'] ?: 0; ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="glass-card bg-primary text-white">
                    <div class="kpi-title text-white opacity-75">Total Global</div>
                    <div class="kpi-value"><?php echo $q['total']; ?></div>
                </div>
            </div>
        </div>
        <div class="row g-4 mb-5">
            <div class="col-md-6">
                <div class="glass-card">
                    <h6 class="fw-bold mb-4">Top Actos por Subcategoría</h6>
                    <canvas id="chartActos" height="250"></canvas>
                </div>
            </div>
            <div class="col-md-6">
                <div class="glass-card">
                    <h6 class="fw-bold mb-4">Top Condiciones por Subcategoría</h6>
                    <canvas id="chartCondiciones" height="250"></canvas>
                </div>
            </div>
        </div>
        <!-- Tabla de Reportes -->
        <div class="glass-card p-0 overflow-hidden">
            <div class="p-4 border-bottom d-flex justify-content-between">
                <h5 class="fw-bold">Matriz de Gestión Reciente</h5>
                <small class="text-muted">Actualizado hace unos segundos</small>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Fecha</th>
                            <th>Hallazgo</th>
                            <th>Área</th>
                            <th>Persona / Empresa</th>
                            <th class="text-center">SAP</th>
                            <th class="text-center">Riesgo</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($reportes as $r): ?>
                        <tr>
                            <td class="ps-4">
                                <span class="fw-bold"><?php echo date('d/m/y', strtotime($r['fecha'])); ?></span><br>
                                <small class="text-muted"><?php echo date('H:i', strtotime($r['fecha'])); ?></small>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <?php if($r['foto_path']): ?>
                                        <div class="thumb-box me-3" onclick="window.open('<?php echo $r['foto_path']; ?>', '_blank')">
                                            <img src="<?php echo $r['foto_path']; ?>">
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <div class="fw-bold"><?php echo $r['tipo_hallazgo']; ?></div>
                                        <div class="small text-muted"><?php echo substr($r['causa_especifica'], 0, 30); ?>...</div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge bg-light text-dark border"><?php echo $r['area']; ?></span></td>
                            <td>
                                <div class="fw-bold"><?php echo $r['nombre']; ?></div>
                                <div class="small text-muted"><?php echo $r['empresa_contratista'] ?: 'Vitapro'; ?></div>
                            </td>
                            <td class="text-center">
                                <?php if($r['aviso_sap']): ?>
                                    <span class="text-primary fw-bold">#<?php echo $r['aviso_sap']; ?></span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php $color = $r['nivel_riesgo']=='Alto'?'danger':($r['nivel_riesgo']=='Medio'?'warning':'success'); ?>
                                <span class="badge-riesgo bg-<?php echo $color; ?> text-white"><?php echo $r['nivel_riesgo']; ?></span>
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group">
                                    <a href="generar_informe.php?id=<?php echo $r['id']; ?>" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="fas fa-file-pdf"></i></a>
                                    <?php if($r['aviso_sap']): ?>
                                        <button onclick="enviarOutlook('<?php echo $r['aviso_sap']; ?>', '<?php echo $r['nombre']; ?>', '<?php echo $r['area']; ?>', '<?php echo $r['id']; ?>')" class="btn btn-sm btn-primary ms-1"><i class="fab fa-microsoft"></i></button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
    <!-- Modal QR -->
    <div class="modal fade" id="modalQR" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4">
                <div class="modal-body text-center p-5">
                    <h5 class="fw-bold mb-4">Punto de Reporte Digital</h5>
                    <img src="https://chart.googleapis.com/chart?chs=250x250&cht=qr&chl=<?php echo urlencode("http://".$_SERVER['HTTP_HOST']."/index.php"); ?>" alt="QR" class="border p-2 rounded-4 mb-4">
                    <p class="text-muted">Imprima este QR y colóquelo por toda la planta de Vitapro para facilitar los reportes.</p>
                    <button class="btn btn-primary w-100 rounded-pill py-3 fw-bold" onclick="window.print()">IMPRIMIR CARTEL</button>
                </div>
            </div>
        </div>
    </div>
    <!-- SCRIPTS DE ALTA INGENIERÍA -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // 1. Gráficos Chart.js
        const chartOptions = { indexAxis: 'y', responsive: true, plugins: { legend: { display:false } }, scales: { x: { display:false } } };
        
        new Chart(document.getElementById('chartActos'), {
            type: 'bar', data: { labels: [<?php foreach($actos_chart as $a) echo "'".$a['causa_especifica']."',"; ?>], datasets: [{ data: [<?php foreach($actos_chart as $a) echo $a['cantidad'].","; ?>], backgroundColor: '#2563eb', borderRadius: 10 }] }, options: chartOptions
        });
        new Chart(document.getElementById('chartCondiciones'), {
            type: 'bar', data: { labels: [<?php foreach($cond_chart as $c) echo "'".$c['causa_especifica']."',"; ?>], datasets: [{ data: [<?php foreach($cond_chart as $c) echo $c['cantidad'].","; ?>], backgroundColor: '#f59e0b', borderRadius: 10 }] }, options: chartOptions
        });
        // 2. SISTEMA DE ALARMA SONORA 🚨
        const siren = new Audio('https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3');
        function activarAlarma() {
            const numAltos = <?php echo $q['alto'] ?: 0; ?>;
            if (numAltos > 0) {
                // Solo suena si el usuario ha interactuado con la página
                siren.play().catch(e => console.log("Haga clic en la página para activar sonido"));
            }
        }
        window.onload = activarAlarma;
        // 3. AUTO-ACTUALIZACIÓN (Cada 30 seg)
        setInterval(() => { location.reload(); }, 30000);
        function enviarOutlook(sap, nombre, area, id) {
            const url = window.location.origin + '/generar_informe.php?id=' + id;
            const subject = encodeURIComponent("Gestión SAP #" + sap + " - Reporte SST Vitapro");
            const body = encodeURIComponent("Se ha generado un nuevo reporte de seguridad.\n\nReportante: " + nombre + "\nÁrea: " + area + "\n\nVer Informe Completo: " + url + "\n\nFavor realizar las gestiones correspondientes.");
            window.location.href = "mailto:Fgutierrezv@ibalnor.com.ec?subject=" + subject + "&body=" + body;
        }
    </script>
</body>
</html>
