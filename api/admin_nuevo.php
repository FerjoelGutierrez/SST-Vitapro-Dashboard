<?php
// 1. SEGURIDAD
if (!isset($_COOKIE['auth_token']) || $_COOKIE['auth_token'] !== 'vitapro_admin_logged') {
    header('Location: login.php');
    exit;
}

require_once 'conexion.php';

// 2. LOGICA DE DATOS
try {
    // KPIs Generales
    $sqlKPI = "SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN tipo_hallazgo LIKE '%Acto%' THEN 1 ELSE 0 END) as actos,
        SUM(CASE WHEN tipo_hallazgo LIKE '%Condición%' THEN 1 ELSE 0 END) as cond,
        SUM(CASE WHEN nivel_riesgo = 'Alto' THEN 1 ELSE 0 END) as alto
        FROM reportes";
    $q = $pdo->query($sqlKPI)->fetch(PDO::FETCH_ASSOC);

    // Gráficos (Usando 'descripcion' para agrupar si 'causa_especifica' no existe)
    $sql_actos = "SELECT descripcion, COUNT(*) as cantidad FROM reportes WHERE tipo_hallazgo LIKE '%Acto%' GROUP BY descripcion ORDER BY cantidad DESC LIMIT 5";
    $actos_chart = $pdo->query($sql_actos)->fetchAll(PDO::FETCH_ASSOC);

    $sql_cond = "SELECT descripcion, COUNT(*) as cantidad FROM reportes WHERE tipo_hallazgo LIKE '%Condición%' GROUP BY descripcion ORDER BY cantidad DESC LIMIT 5";
    $cond_chart = $pdo->query($sql_cond)->fetchAll(PDO::FETCH_ASSOC);

    // Filtros
    $where = "";
    if (isset($_GET['filter_sap'])) { $where = " WHERE aviso_sap IS NOT NULL AND aviso_sap <> '' "; }
    
    // Lista
    $stmt_reportes = $pdo->query("SELECT * FROM reportes $where ORDER BY id DESC LIMIT 50");
    $reportes = $stmt_reportes->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) { die("Error DB: " . $e->getMessage()); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dash - Gestión SST Vitapro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root { --primary: #0f172a; --secondary: #2563eb; --accent: #f59e0b; --danger: #e11d48; --bg-body: #f1f5f9; --sidebar-bg: #1e293b; }
        body { font-family: 'Outfit', sans-serif; background-color: var(--bg-body); color: #1e293b; margin: 0; display: flex; }
        .sidebar { width: 260px; background: var(--sidebar-bg); min-height: 100vh; color: white; padding: 30px 15px; position: fixed; top: 0; bottom:0; z-index: 1000; overflow-y: auto;}
        .sidebar h2 { font-size: 1.4rem; font-weight: 800; margin-bottom: 40px; padding: 0 10px; background: linear-gradient(to right, #60a5fa, #2563eb); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .nav-link { color: #94a3b8; padding: 12px 18px; border-radius: 12px; margin-bottom: 5px; transition: all 0.3s; display: flex; align-items: center; text-decoration: none; font-weight: 500; }
        .nav-link:hover { color: white; background: rgba(255,255,255,0.05); }
        .nav-link.active { background: var(--secondary); color: white; }
        .content { margin-left: 260px; padding: 30px 50px; width: 100%; }
        .glass-card { background: white; border-radius: 20px; border: 1px solid rgba(255,255,255,0.8); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        .kpi-card { padding: 25px; } .kpi-value { font-size: 2.4rem; font-weight: 800; color: var(--primary); }
        .table-container { background: white; border-radius: 24px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); margin-top: 30px; }
        .table thead th { background: #f8fafc; color: #475569; padding: 20px; border-bottom: 2px solid #f1f5f9; }
        .table tbody td { padding: 20px; vertical-align: middle; }
        .badge-riesgo { padding: 6px 16px; border-radius: 10px; font-weight: 800; font-size: 0.65rem; }
        .thumb-box { width: 50px; height: 50px; border-radius: 12px; background: #f1f5f9; overflow: hidden; cursor: pointer; border: 1px solid #e2e8f0; }
        .thumb-box img { width: 100%; height: 100%; object-fit: cover; }
        .btn-action { text-decoration: none; padding: 8px 12px; border-radius: 8px; font-size: 0.8rem; font-weight: 600; display: inline-flex; align-items: center; transition: 0.2s; border: none; }
        .btn-pdf { background: #f1f5f9; color: #64748b; } .btn-pdf:hover { background: #fee2e2; color: #ef4444; }
        .btn-outlook { background: #0078d4; color: white; } .btn-outlook:hover { background: #005a9e; color: white; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2><i class="fas fa-shield-alt"></i> VITAPRO</h2>
        <nav>
            <a href="admin_nuevo.php" class="nav-link active"><i class="fas fa-th-large me-2"></i> Dashboard</a>
            <a href="admin_nuevo.php?filter_sap=1" class="nav-link"><i class="fas fa-search-dollar me-2"></i> Registros SAP</a>
            <a href="#" class="nav-link" data-bs-toggle="modal" data-bs-target="#modalQR"><i class="fas fa-qrcode me-2"></i> Descargar QR</a>
            <a href="exportar_excel.php" class="nav-link"><i class="fas fa-file-excel me-2"></i> Exportar Todo</a>
            <a href="index.php" class="nav-link" target="_blank"><i class="fas fa-plus-circle me-2"></i> Nuevo Reporte</a>
            <div style="margin-top: 50px;">
                <a href="logout.php" class="nav-link text-danger"><i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión</a>
            </div>
        </nav>
    </div>

    <div class="content">
        <h3 class="fw-bold mb-4">Portal de Seguridad Industrial</h3>
        
        <div class="row g-4 mb-5">
            <div class="col-md-3"><div class="glass-card kpi-card"><div class="small fw-bold text-muted">ACTOS SUBESTÁNDAR</div><div class="kpi-value text-primary"><?php echo $q['actos']?:0; ?></div></div></div>
            <div class="col-md-3"><div class="glass-card kpi-card"><div class="small fw-bold text-muted">CONDICIONES SUB.</div><div class="kpi-value text-warning"><?php echo $q['cond']?:0; ?></div></div></div>
            <div class="col-md-3"><div class="glass-card kpi-card"><div class="small fw-bold text-muted">RIESGO ALTO</div><div class="kpi-value text-danger"><?php echo $q['alto']?:0; ?></div></div></div>
            <div class="col-md-3"><div class="glass-card kpi-card bg-primary text-white"><div class="small fw-bold opacity-75">TOTAL REGISTROS</div><div class="kpi-value text-white"><?php echo $q['total']; ?></div></div></div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-md-6"><div class="glass-card p-4"><h5>Tendencia de Actos</h5><div style="height:250px"><canvas id="chartActos"></canvas></div></div></div>
            <div class="col-md-6"><div class="glass-card p-4"><h5>Tendencia de Condiciones</h5><div style="height:250px"><canvas id="chartCondiciones"></canvas></div></div></div>
        </div>

        <div class="table-container">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Evidencia</th>
                            <th>Hallazgo</th>
                            <th>Reportante</th>
                            <th>Área</th>
                            <th class="text-center">SAP</th>
                            <th class="text-center">Riesgo</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($reportes as $r): ?>
                        <tr>
                            <td>
                                <div class="fw-bold"><?php echo date('d/m/y', strtotime($r['fecha'])); ?></div>
                                <div class="small text-muted"><?php echo date('H:i', strtotime($r['fecha'])); ?></div>
                            </td>
                            <td>
                                <?php if(!empty($r['foto_path'])): ?>
                                    <div class="thumb-box" onclick="verImagen('<?php echo $r['id']; ?>')">
                                        <img src="<?php echo $r['foto_path']; ?>" id="thumb_<?php echo $r['id']; ?>">
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted small">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="fw-bold"><?php echo $r['tipo_hallazgo']; ?></div>
                                <div class="small text-muted"><?php echo substr($r['descripcion'], 0, 30); ?>...</div>
                            </td>
                            <td>
                                <div><?php echo $r['nombre']; ?></div>
                                <div class="small text-muted"><?php echo $r['empresa_contratista']?:'Interno'; ?></div>
                            </td>
                            <td><span class="badge bg-light text-dark border"><?php echo $r['area']; ?></span></td>
                            <td class="text-center"><?php echo $r['aviso_sap'] ? '<span class="fw-bold text-primary">#'.$r['aviso_sap'].'</span>' : '-'; ?></td>
                            <td class="text-center">
                                <?php $c = ($r['nivel_riesgo']=='Alto')?'danger':(($r['nivel_riesgo']=='Medio')?'warning':'success'); ?>
                                <span class="badge-riesgo bg-<?php echo $c; ?> text-white"><?php echo $r['nivel_riesgo']; ?></span>
                            </td>
                            <td class="text-end">
                                <a href="generar_informe.php?id=<?php echo $r['id']; ?>" class="btn-action btn-pdf" target="_blank"><i class="fas fa-file-pdf"></i></a>
                                <?php if($r['aviso_sap']): ?>
                                    <button class="btn-action btn-outlook ms-1" onclick="enviarOutlook('<?php echo $r['aviso_sap']; ?>', '<?php echo $r['nombre']; ?>', '<?php echo $r['id']; ?>')"><i class="fab fa-microsoft"></i></button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalImagen" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-body p-0 position-relative">
                    <button type="button" class="btn-close position-absolute top-0 end-0 m-3 bg-white" data-bs-dismiss="modal"></button>
                    <img id="imgFull" src="" class="img-fluid w-100 rounded">
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalQR" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered text-center">
            <div class="modal-content p-4">
                <h4>Código QR Planta</h4>
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=<?php echo urlencode('https://'.$_SERVER['HTTP_HOST'].'/index.php'); ?>" class="img-fluid my-3">
                <button class="btn btn-primary rounded-pill w-100" onclick="window.print()">Imprimir</button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function verImagen(id) {
            // Buscamos la imagen pequeña por ID y usamos su fuente para la grande
            var src = document.getElementById('thumb_' + id).src;
            document.getElementById('imgFull').src = src;
            new bootstrap.Modal(document.getElementById('modalImagen')).show();
        }
        
        // Gráficos
        const confActos = { type: 'bar', data: { labels: [<?php foreach($actos_chart as $a) echo '"'.($a['descripcion']??'').'",'; ?>], datasets: [{ data: [<?php foreach($actos_chart as $a) echo $a['cantidad'].','; ?>], backgroundColor: '#1e3a8a', borderRadius: 5 }] }, options: { indexAxis: 'y', plugins: { legend: { display: false } } } };
        new Chart(document.getElementById('chartActos'), confActos);

        const confCond = { type: 'bar', data: { labels: [<?php foreach($cond_chart as $c) echo '"'.($c['descripcion']??'').'",'; ?>], datasets: [{ data: [<?php foreach($cond_chart as $c) echo $c['cantidad'].','; ?>], backgroundColor: '#f59e0b', borderRadius: 5 }] }, options: { indexAxis: 'y', plugins: { legend: { display: false } } } };
        new Chart(document.getElementById('chartCondiciones'), confCond);

        function enviarOutlook(sap, nombre, id) {
            window.location.href = `mailto:?subject=Reporte SST SAP ${sap}&body=Revisar reporte de ${nombre}: ${window.location.origin}/generar_informe.php?id=${id}`;
        }
    </script>
</body>
</html>
