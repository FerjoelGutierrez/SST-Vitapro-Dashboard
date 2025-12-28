<?php
// 1. SEGURIDAD
if (!isset($_COOKIE['auth_token']) || $_COOKIE['auth_token'] !== 'vitapro_admin_logged') {
    header('Location: login.php');
    exit;
}
require_once 'conexion.php';

// 2. DATOS
try {
    // KPIs
    $sqlKPI = "SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN tipo_hallazgo LIKE '%Acto%' THEN 1 ELSE 0 END) as actos,
        SUM(CASE WHEN tipo_hallazgo LIKE '%Condición%' THEN 1 ELSE 0 END) as cond,
        SUM(CASE WHEN nivel_riesgo = 'Alto' THEN 1 ELSE 0 END) as alto
        FROM reportes";
    $q = $pdo->query($sqlKPI)->fetch(PDO::FETCH_ASSOC);

    // Gráficos
    $sql_actos = "SELECT descripcion, COUNT(*) as cantidad FROM reportes WHERE tipo_hallazgo LIKE '%Acto%' GROUP BY descripcion ORDER BY cantidad DESC LIMIT 5";
    $actos_chart = $pdo->query($sql_actos)->fetchAll(PDO::FETCH_ASSOC);

    $sql_cond = "SELECT descripcion, COUNT(*) as cantidad FROM reportes WHERE tipo_hallazgo LIKE '%Condición%' GROUP BY descripcion ORDER BY cantidad DESC LIMIT 5";
    $cond_chart = $pdo->query($sql_cond)->fetchAll(PDO::FETCH_ASSOC);

    // Tabla (Últimos 100)
    $stmt_reportes = $pdo->query("SELECT * FROM reportes ORDER BY id DESC LIMIT 100");
    $reportes = $stmt_reportes->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) { die("Error: " . $e->getMessage()); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SST Executive Dashboard | Vitapro</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    
    <style>
        :root {
            --sidebar-width: 260px;
            --brand-navy: #0f172a;
            --brand-blue: #2563eb;
            --bg-light: #f1f5f9;
            --text-dark: #334155;
        }

        body { font-family: 'Inter', sans-serif; background: var(--bg-light); color: var(--text-dark); display: flex; }

        /* 1. SIDEBAR FIJO */
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--brand-navy);
            color: white;
            position: fixed;
            top: 0; left: 0;
            padding: 25px;
            display: flex;
            flex-direction: column;
            z-index: 1000;
        }
        .brand { font-size: 1.25rem; font-weight: 800; letter-spacing: -0.5px; margin-bottom: 40px; color: white; text-decoration: none; display: flex; align-items: center; gap: 10px; }
        .nav-link { color: #94a3b8; padding: 12px 15px; border-radius: 8px; margin-bottom: 5px; font-weight: 500; display: flex; align-items: center; transition: 0.2s; }
        .nav-link:hover, .nav-link.active { background: rgba(255,255,255,0.1); color: white; }
        .nav-link i { width: 25px; text-align: center; margin-right: 10px; }

        /* 2. AREA PRINCIPAL */
        .main {
            margin-left: var(--sidebar-width);
            width: calc(100% - var(--sidebar-width));
            padding: 40px;
            min-height: 100vh;
        }

        /* 3. TARJETAS KPI (ESTILO MINIMALISTA) */
        .card-kpi {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: start;
            height: 100%;
        }
        .kpi-label { font-size: 0.85rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 5px; }
        .kpi-number { font-size: 2rem; font-weight: 700; color: var(--brand-navy); line-height: 1; }
        .kpi-icon { padding: 12px; border-radius: 10px; background: #f8fafc; color: var(--brand-blue); font-size: 1.2rem; }

        /* 4. CONTENEDOR DE GRÁFICOS (FIX PARA QUE NO SE DERRITAN) */
        .chart-box {
            background: white;
            border-radius: 12px;
            padding: 25px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            height: 100%;
        }
        .chart-header { font-size: 1rem; font-weight: 700; margin-bottom: 20px; color: var(--brand-navy); }
        
        /* ESTA CLASE ES LA SOLUCIÓN: Altura fija y relativa */
        .chart-canvas-container {
            position: relative;
            height: 300px; /* Altura obligatoria */
            width: 100%;
        }

        /* 5. TABLA DATATABLES */
        .table-box { background: white; border-radius: 12px; border: 1px solid #e2e8f0; padding: 20px; margin-top: 30px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        table.dataTable thead th { background: #f8fafc; font-size: 0.75rem; text-transform: uppercase; color: #475569; font-weight: 700; border-bottom: 2px solid #e2e8f0; }
        .badge-status { padding: 5px 10px; border-radius: 50px; font-size: 0.75rem; font-weight: 600; }
        .thumb-img { width: 36px; height: 36px; border-radius: 6px; object-fit: cover; border: 1px solid #e2e8f0; cursor: pointer; }
        
        /* UTILIDADES */
        .btn-action { width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; border: none; transition: 0.2s; }
        .btn-pdf { background: #f1f5f9; color: #64748b; } .btn-pdf:hover { background: #fee2e2; color: #ef4444; }
        .btn-email { background: #eff6ff; color: #2563eb; } .btn-email:hover { background: #dbeafe; color: #1e40af; }

    </style>
</head>
<body>

    <div class="sidebar">
        <a href="#" class="brand"><i class="fas fa-shield-halved"></i> VITAPRO SST</a>
        
        <div class="mt-4">
            <span style="font-size: 10px; text-transform:uppercase; color: #64748b; font-weight:bold; padding-left:15px;">Gestión</span>
            <a href="#" class="nav-link active mt-2"><i class="fas fa-chart-line"></i> Dashboard</a>
            <a href="index.php" target="_blank" class="nav-link"><i class="fas fa-plus-circle"></i> Nuevo Reporte</a>
            
            <span style="font-size: 10px; text-transform:uppercase; color: #64748b; font-weight:bold; padding-left:15px; display:block; margin-top:20px;">Utilidades</span>
            <a href="#" class="nav-link" data-bs-toggle="modal" data-bs-target="#modalQR"><i class="fas fa-qrcode"></i> Código QR</a>
            <a href="exportar_excel.php" class="nav-link"><i class="fas fa-file-excel"></i> Exportar Data</a>
        </div>

        <div style="margin-top: auto;">
            <a href="logout.php" class="nav-link text-danger"><i class="fas fa-power-off"></i> Cerrar Sesión</a>
        </div>
    </div>

    <div class="main">
        
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h3 class="fw-bold m-0 text-dark">Panel General</h3>
                <p class="text-muted small m-0">Monitoreo de indicadores de seguridad</p>
            </div>
            <div>
                <button class="btn btn-outline-secondary btn-sm bg-white" onclick="window.location.reload()"><i class="fas fa-sync-alt me-2"></i>Actualizar</button>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-xl-3 col-md-6">
                <div class="card-kpi">
                    <div>
                        <div class="kpi-label">Reportes Totales</div>
                        <div class="kpi-number"><?php echo $q['total']; ?></div>
                    </div>
                    <div class="kpi-icon"><i class="fas fa-folder-open"></i></div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card-kpi">
                    <div>
                        <div class="kpi-label">Actos Inseguros</div>
                        <div class="kpi-number"><?php echo $q['actos']; ?></div>
                    </div>
                    <div class="kpi-icon text-warning bg-warning-subtle"><i class="fas fa-user-injured"></i></div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card-kpi">
                    <div>
                        <div class="kpi-label">Condiciones Sub.</div>
                        <div class="kpi-number"><?php echo $q['cond']; ?></div>
                    </div>
                    <div class="kpi-icon text-info bg-info-subtle"><i class="fas fa-tools"></i></div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card-kpi" style="border-left: 4px solid #ef4444;">
                    <div>
                        <div class="kpi-label text-danger">Riesgo Alto</div>
                        <div class="kpi-number text-danger"><?php echo $q['alto']; ?></div>
                    </div>
                    <div class="kpi-icon text-danger bg-danger-subtle"><i class="fas fa-siren-on"></i></div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-lg-6">
                <div class="chart-box">
                    <div class="chart-header">Top 5 Actos Subestándar</div>
                    <div class="chart-canvas-container">
                        <canvas id="chartActos"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="chart-box">
                    <div class="chart-header">Top 5 Condiciones Inseguras</div>
                    <div class="chart-canvas-container">
                        <canvas id="chartCondiciones"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-box">
            <div class="d-flex justify-content-between mb-3">
                <h5 class="fw-bold m-0">Matriz de Hallazgos</h5>
                <button class="btn btn-sm btn-primary" onclick="filtrarSAP()">Solo con SAP</button>
            </div>
            <table id="tablaSST" class="table table-hover w-100">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Evidencia</th>
                        <th>Hallazgo</th>
                        <th>Reportante</th>
                        <th>Ubicación</th>
                        <th>SAP</th>
                        <th>Riesgo</th>
                        <th class="text-end">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($reportes as $r): ?>
                    <tr>
                        <td data-sort="<?php echo strtotime($r['fecha']); ?>">
                            <div class="fw-bold"><?php echo date('d/m/y', strtotime($r['fecha'])); ?></div>
                            <small class="text-muted"><?php echo date('H:i', strtotime($r['fecha'])); ?></small>
                        </td>
                        <td>
                            <?php if($r['foto_path']): ?>
                                <img src="<?php echo $r['foto_path']; ?>" class="thumb-img" onclick="verFoto('<?php echo $r['id']; ?>')">
                                <img src="<?php echo $r['foto_path']; ?>" id="full_<?php echo $r['id']; ?>" style="display:none;">
                            <?php else: ?>
                                <span class="text-muted small">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="fw-bold"><?php echo $r['tipo_hallazgo']; ?></div>
                            <small class="text-muted d-block text-truncate" style="max-width:150px;"><?php echo $r['descripcion']; ?></small>
                        </td>
                        <td>
                            <div class="fw-bold text-dark"><?php echo $r['nombre']; ?></div>
                            <small class="text-muted"><?php echo $r['empresa_contratista']?:'Interno'; ?></small>
                        </td>
                        <td><span class="badge bg-light text-secondary border"><?php echo $r['area']; ?></span></td>
                        <td><?php echo $r['aviso_sap'] ? '<span class="fw-bold text-primary">#'.$r['aviso_sap'].'</span>' : '-'; ?></td>
                        <td>
                            <?php 
                                $bg = 'bg-success-subtle text-success';
                                if($r['nivel_riesgo']=='Medio') $bg = 'bg-warning-subtle text-warning';
                                if($r['nivel_riesgo']=='Alto') $bg = 'bg-danger-subtle text-danger';
                            ?>
                            <span class="badge <?php echo $bg; ?>"><?php echo $r['nivel_riesgo']; ?></span>
                        </td>
                        <td class="text-end">
                            <a href="generar_informe.php?id=<?php echo $r['id']; ?>" target="_blank" class="btn-action btn-pdf"><i class="fas fa-file-pdf"></i></a>
                            <?php if($r['aviso_sap']): ?>
                            <button class="btn-action btn-email" onclick="mail('<?php echo $r['aviso_sap']; ?>','<?php echo $r['nombre']; ?>','<?php echo $r['id']; ?>')"><i class="fas fa-envelope"></i></button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>

    <div class="modal fade" id="modalFoto" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content bg-transparent border-0">
                <div class="modal-body text-center position-relative p-0">
                    <button class="btn-close btn-close-white position-absolute top-0 end-0 m-3" data-bs-dismiss="modal"></button>
                    <img id="imgModal" src="" class="img-fluid rounded shadow-lg" style="max-height:80vh;">
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalQR" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered text-center">
            <div class="modal-content p-5">
                <h4>QR Planta</h4>
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=<?php echo urlencode('https://'.$_SERVER['HTTP_HOST'].'/index.php'); ?>" class="my-3 mx-auto d-block">
                <button class="btn btn-dark w-100" onclick="window.print()">Imprimir</button>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
        // 1. TABLA (CERO LAG)
        let table = $('#tablaSST').DataTable({
            language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
            order: [[0, 'desc']],
            dom: 'frtip',
            pageLength: 8
        });

        function filtrarSAP() {
            let col = table.column(5);
            col.search(col.search() ? '' : '#').draw();
        }

        // 2. GRÁFICOS (FIX ALTURA)
        const common = {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false, // CLAVE PARA QUE NO SE DERRITA
            plugins: { legend: { display: false } }
        };

        new Chart(document.getElementById('chartActos'), {
            type: 'bar',
            data: {
                labels: [<?php foreach($actos_chart as $a) echo '"'.($a['descripcion']??'').'",'; ?>],
                datasets: [{ data: [<?php foreach($actos_chart as $a) echo $a['cantidad'].','; ?>], backgroundColor: '#0f172a', borderRadius: 4, barThickness: 25 }]
            },
            options: common
        });

        new Chart(document.getElementById('chartCondiciones'), {
            type: 'bar',
            data: {
                labels: [<?php foreach($cond_chart as $c) echo '"'.($c['descripcion']??'').'",'; ?>],
                datasets: [{ data: [<?php foreach($cond_chart as $c) echo $c['cantidad'].','; ?>], backgroundColor: '#2563eb', borderRadius: 4, barThickness: 25 }]
            },
            options: common
        });

        // 3. UTILIDADES
        function verFoto(id) {
            document.getElementById('imgModal').src = document.getElementById('full_'+id).src;
            new bootstrap.Modal(document.getElementById('modalFoto')).show();
        }
        function mail(sap, nom, id) {
            window.location.href = `mailto:?subject=SAP ${sap}&body=Reporte de ${nom}: ${window.location.origin}/generar_informe.php?id=${id}`;
        }
    </script>
</body>
</html>
