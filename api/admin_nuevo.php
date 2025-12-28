<?php
// 1. SEGURIDAD Y CONEXIÓN
if (!isset($_COOKIE['auth_token']) || $_COOKIE['auth_token'] !== 'vitapro_admin_logged') {
    header('Location: login.php');
    exit;
}
require_once 'conexion.php';

try {
    // 2. OBTENCIÓN DE DATOS (Consulta única optimizada)
    
    // KPIs
    $sqlKPI = "SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN tipo_hallazgo LIKE '%Acto%' THEN 1 ELSE 0 END) as actos,
        SUM(CASE WHEN tipo_hallazgo LIKE '%Condición%' THEN 1 ELSE 0 END) as cond,
        SUM(CASE WHEN nivel_riesgo = 'Alto' THEN 1 ELSE 0 END) as alto,
        SUM(CASE WHEN aviso_sap IS NOT NULL AND aviso_sap != '' THEN 1 ELSE 0 END) as con_sap
        FROM reportes";
    $q = $pdo->query($sqlKPI)->fetch(PDO::FETCH_ASSOC);

    // Gráficos (Agrupados por Descripción)
    $sql_actos = "SELECT descripcion, COUNT(*) as cantidad FROM reportes WHERE tipo_hallazgo LIKE '%Acto%' GROUP BY descripcion ORDER BY cantidad DESC LIMIT 5";
    $actos_chart = $pdo->query($sql_actos)->fetchAll(PDO::FETCH_ASSOC);

    $sql_cond = "SELECT descripcion, COUNT(*) as cantidad FROM reportes WHERE tipo_hallazgo LIKE '%Condición%' GROUP BY descripcion ORDER BY cantidad DESC LIMIT 5";
    $cond_chart = $pdo->query($sql_cond)->fetchAll(PDO::FETCH_ASSOC);

    // Tabla Completa (Traemos los últimos 100 para que la tabla sea rápida)
    $stmt_reportes = $pdo->query("SELECT * FROM reportes ORDER BY id DESC LIMIT 100");
    $reportes = $stmt_reportes->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SST Management | Vitapro</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    
    <style>
        :root {
            --brand-dark: #0f172a;  /* Azul oscuro corporativo */
            --brand-blue: #2563eb;  /* Azul acento */
            --bg-body: #f8fafc;     /* Gris muy claro para fondo */
            --text-main: #334155;
            --text-light: #64748b;
        }

        body { font-family: 'Inter', sans-serif; background-color: var(--bg-body); color: var(--text-main); overflow-x: hidden; }

        /* SIDEBAR PRO */
        .sidebar {
            width: 260px;
            background: var(--brand-dark);
            height: 100vh;
            position: fixed;
            top: 0; left: 0;
            padding: 25px;
            display: flex;
            flex-direction: column;
            z-index: 1000;
            box-shadow: 4px 0 15px rgba(0,0,0,0.1);
        }

        .brand-logo { color: white; font-weight: 800; font-size: 1.5rem; letter-spacing: -0.5px; margin-bottom: 40px; display: flex; align-items: center; gap: 10px; }
        .brand-logo span { color: var(--brand-blue); }

        .nav-item { color: #94a3b8; text-decoration: none; padding: 14px 16px; border-radius: 8px; margin-bottom: 4px; font-size: 0.9rem; font-weight: 500; display: flex; align-items: center; transition: all 0.2s ease; cursor: pointer;}
        .nav-item:hover, .nav-item.active { background: rgba(255,255,255,0.08); color: white; }
        .nav-item i { width: 24px; font-size: 1.1rem; opacity: 0.8; }
        .nav-section { font-size: 0.75rem; text-transform: uppercase; color: #475569; font-weight: 700; margin: 20px 0 10px 10px; letter-spacing: 0.5px; }

        /* CONTENIDO PRINCIPAL */
        .main-content { margin-left: 260px; padding: 40px; }
        
        /* TARJETAS GERENCIALES (Minimalistas) */
        .kpi-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
            transition: transform 0.2s;
            height: 100%;
        }
        .kpi-card:hover { transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05); }
        .kpi-label { font-size: 0.8rem; font-weight: 600; color: var(--text-light); text-transform: uppercase; margin-bottom: 8px; }
        .kpi-number { font-size: 2.2rem; font-weight: 700; color: var(--brand-dark); line-height: 1; }
        .kpi-icon { float: right; color: #e2e8f0; font-size: 2rem; }

        /* GRÁFICOS */
        .chart-container { background: white; padding: 25px; border-radius: 12px; border: 1px solid #e2e8f0; height: 100%; }
        .chart-title { font-size: 1rem; font-weight: 700; margin-bottom: 20px; color: var(--brand-dark); }

        /* TABLA CON DATATABLES */
        .table-card { background: white; border-radius: 12px; border: 1px solid #e2e8f0; padding: 0; overflow: hidden; margin-top: 30px; }
        .table-header { padding: 20px 25px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: white; }
        .table-title { font-weight: 700; color: var(--brand-dark); font-size: 1.1rem; margin: 0; }
        
        table.dataTable thead th { background-color: #f8fafc !important; color: #475569; font-weight: 600; text-transform: uppercase; font-size: 0.75rem; padding: 15px !important; border-bottom: 1px solid #e2e8f0 !important; }
        table.dataTable tbody td { padding: 15px !important; vertical-align: middle; font-size: 0.9rem; color: #334155; border-bottom: 1px solid #f1f5f9; }
        
        /* Status Badges (Sutiles) */
        .badge-status { padding: 5px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 600; }
        .bg-risk-high { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
        .bg-risk-med { background: #fffbeb; color: #92400e; border: 1px solid #fde68a; }
        .bg-risk-low { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
        
        .thumb-img { width: 40px; height: 40px; border-radius: 8px; object-fit: cover; cursor: pointer; border: 1px solid #e2e8f0; transition: transform 0.2s; }
        .thumb-img:hover { transform: scale(1.5); }

        /* Botones Acción */
        .btn-icon { width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; border: none; transition: 0.2s; margin-left: 5px; }
        .btn-pdf { background: #f1f5f9; color: #64748b; } .btn-pdf:hover { background: #e2e8f0; color: #ef4444; }
        .btn-mail { background: #eff6ff; color: #2563eb; } .btn-mail:hover { background: #dbeafe; color: #1d4ed8; }

        /* DataTables Customization */
        .dataTables_wrapper .dataTables_filter input { border: 1px solid #cbd5e1; border-radius: 6px; padding: 5px 10px; }
        .dataTables_wrapper .dataTables_length select { border: 1px solid #cbd5e1; border-radius: 6px; padding: 5px; }
        .page-item.active .page-link { background-color: var(--brand-dark); border-color: var(--brand-dark); }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="brand-logo">
            <i class="fas fa-shield-halved"></i> VITAPRO <span>SST</span>
        </div>

        <div class="nav-section">Principal</div>
        <a href="#" class="nav-item active"><i class="fas fa-chart-pie me-3"></i> Dashboard</a>
        <a href="index.php" target="_blank" class="nav-item"><i class="fas fa-plus-circle me-3"></i> Nuevo Reporte</a>
        
        <div class="nav-section">Herramientas</div>
        <a href="#" class="nav-item" data-bs-toggle="modal" data-bs-target="#modalQR"><i class="fas fa-qrcode me-3"></i> Código QR Planta</a>
        <a href="exportar_excel.php" class="nav-item"><i class="fas fa-file-excel me-3"></i> Exportar Base</a>
        
        <div style="margin-top: auto;">
            <a href="logout.php" class="nav-item text-danger"><i class="fas fa-arrow-right-from-bracket me-3"></i> Cerrar Sesión</a>
        </div>
    </div>

    <div class="main-content">
        
        <div class="d-flex justify-content-between align-items-end mb-5">
            <div>
                <h2 class="fw-bold text-dark m-0">Resumen Ejecutivo</h2>
                <p class="text-muted m-0 small mt-1">Gestión de Seguridad y Salud en el Trabajo</p>
            </div>
            <div class="text-end">
                <span class="badge bg-light text-dark border px-3 py-2 fw-normal">
                    <i class="far fa-calendar me-2"></i> <?php echo date('d M, Y'); ?>
                </span>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-xl-3 col-md-6">
                <div class="kpi-card">
                    <i class="fas fa-clipboard-check kpi-icon"></i>
                    <div class="kpi-label">Total Reportes</div>
                    <div class="kpi-number"><?php echo $q['total']; ?></div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="kpi-card">
                    <i class="fas fa-exclamation-circle kpi-icon"></i>
                    <div class="kpi-label">Actos Inseguros</div>
                    <div class="kpi-number text-warning"><?php echo $q['actos']; ?></div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="kpi-card">
                    <i class="fas fa-bolt kpi-icon"></i>
                    <div class="kpi-label">Condiciones Sub.</div>
                    <div class="kpi-number text-primary"><?php echo $q['cond']; ?></div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="kpi-card" style="border-left: 4px solid #ef4444;">
                    <i class="fas fa-triangle-exclamation kpi-icon text-danger"></i>
                    <div class="kpi-label text-danger">Riesgo Crítico</div>
                    <div class="kpi-number text-danger"><?php echo $q['alto']; ?></div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-lg-6">
                <div class="chart-container">
                    <div class="chart-title">Tendencia de Actos Subestándar</div>
                    <canvas id="chartActos" height="200"></canvas>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="chart-container">
                    <div class="chart-title">Tendencia de Condiciones Inseguras</div>
                    <canvas id="chartCondiciones" height="200"></canvas>
                </div>
            </div>
        </div>

        <div class="table-card">
            <div class="table-header">
                <h5 class="table-title">Matriz de Seguimiento</h5>
                <button class="btn btn-sm btn-outline-primary" onclick="filtrarSAP()">
                    <i class="fas fa-filter me-2"></i> Solo con SAP
                </button>
            </div>
            <div class="p-3">
                <table id="tablaReportes" class="table table-hover w-100">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Evidencia</th>
                            <th>Hallazgo</th>
                            <th>Reportante</th>
                            <th>Ubicación</th>
                            <th>SAP</th>
                            <th>Estado</th>
                            <th class="text-end">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($reportes as $r): ?>
                        <tr>
                            <td data-sort="<?php echo strtotime($r['fecha']); ?>">
                                <div class="fw-bold"><?php echo date('d/m/y', strtotime($r['fecha'])); ?></div>
                                <div class="small text-muted"><?php echo date('H:i', strtotime($r['fecha'])); ?></div>
                            </td>
                            <td>
                                <?php if(!empty($r['foto_path'])): ?>
                                    <img src="<?php echo $r['foto_path']; ?>" class="thumb-img" onclick="verImagen('<?php echo $r['id']; ?>')">
                                    <img src="<?php echo $r['foto_path']; ?>" id="full_<?php echo $r['id']; ?>" style="display:none;">
                                <?php else: ?>
                                    <span class="text-muted small px-2">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="fw-bold text-dark"><?php echo $r['tipo_hallazgo']; ?></div>
                                <div class="small text-muted text-truncate" style="max-width: 150px;"><?php echo $r['descripcion']; ?></div>
                            </td>
                            <td>
                                <div class="small fw-bold"><?php echo $r['nombre']; ?></div>
                                <div class="small text-muted" style="font-size: 0.75rem;"><?php echo $r['empresa_contratista']?:'Interno'; ?></div>
                            </td>
                            <td><span class="badge bg-light text-dark border"><?php echo $r['area']; ?></span></td>
                            <td>
                                <?php if($r['aviso_sap']): ?>
                                    <span class="fw-bold text-primary small">#<?php echo $r['aviso_sap']; ?></span>
                                <?php else: ?>
                                    <span class="text-muted small">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                    $clase = 'bg-risk-low'; 
                                    if($r['nivel_riesgo']=='Medio') $clase = 'bg-risk-med';
                                    if($r['nivel_riesgo']=='Alto') $clase = 'bg-risk-high';
                                ?>
                                <span class="badge-status <?php echo $clase; ?>"><?php echo $r['nivel_riesgo']; ?></span>
                            </td>
                            <td class="text-end">
                                <a href="generar_informe.php?id=<?php echo $r['id']; ?>" target="_blank" class="btn-icon btn-pdf" title="PDF">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                                <?php if($r['aviso_sap']): ?>
                                    <button class="btn-icon btn-mail" onclick="enviarOutlook('<?php echo $r['aviso_sap']; ?>', '<?php echo $r['nombre']; ?>', '<?php echo $r['id']; ?>')" title="Correo">
                                        <i class="fas fa-envelope"></i>
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div> <div class="modal fade" id="modalImagen" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 bg-transparent">
                <div class="modal-body p-0 text-center position-relative">
                    <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" data-bs-dismiss="modal"></button>
                    <img id="imgModalSrc" src="" class="img-fluid rounded shadow-lg" style="max-height: 80vh;">
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalQR" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered text-center">
            <div class="modal-content p-5 border-0 shadow">
                <h4 class="fw-bold text-dark mb-4">Punto de Reporte Digital</h4>
                <div class="bg-light p-3 rounded d-inline-block mx-auto mb-4 border">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=<?php echo urlencode('https://'.$_SERVER['HTTP_HOST'].'/index.php'); ?>" class="img-fluid">
                </div>
                <button class="btn btn-primary w-100 py-2 fw-bold" onclick="window.print()">Imprimir Cartel</button>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
        // 1. INICIALIZAR DATATABLES (Cero Lag)
        let table;
        $(document).ready(function() {
            table = $('#tablaReportes').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                },
                order: [[ 0, "desc" ]], // Ordenar por fecha reciente
                pageLength: 10,
                lengthChange: false, // Ocultar selector de cantidad
                dom: 'frtip' // Layout limpio
            });
        });

        // Función para filtrar SAP sin recargar la página
        function filtrarSAP() {
            let currentSearch = table.column(5).search(); // Columna 5 es SAP
            if(currentSearch) {
                table.column(5).search('').draw(); // Limpiar filtro
            } else {
                table.column(5).search('#').draw(); // Buscar cualquier cosa que tenga '#'
            }
        }

        // 2. CONFIGURACIÓN DE GRÁFICOS (Minimalistas)
        Chart.defaults.font.family = "'Inter', sans-serif";
        Chart.defaults.color = '#64748b';
        
        const commonOptions = {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false } },
                y: { grid: { display: false } }
            }
        };

        new Chart(document.getElementById('chartActos'), {
            type: 'bar',
            data: {
                labels: [<?php foreach($actos_chart as $a) echo '"'.($a['descripcion']??'').'",'; ?>],
                datasets: [{ 
                    data: [<?php foreach($actos_chart as $a) echo $a['cantidad'].','; ?>], 
                    backgroundColor: '#1e293b', 
                    borderRadius: 4,
                    barThickness: 20
                }]
            },
            options: commonOptions
        });

        new Chart(document.getElementById('chartCondiciones'), {
            type: 'bar',
            data: {
                labels: [<?php foreach($cond_chart as $c) echo '"'.($c['descripcion']??'').'",'; ?>],
                datasets: [{ 
                    data: [<?php foreach($cond_chart as $c) echo $c['cantidad'].','; ?>], 
                    backgroundColor: '#2563eb', 
                    borderRadius: 4,
                    barThickness: 20
                }]
            },
            options: commonOptions
        });

        // 3. UTILIDADES
        function verImagen(id) {
            let src = document.getElementById('full_' + id).src;
            document.getElementById('imgModalSrc').src = src;
            new bootstrap.Modal(document.getElementById('modalImagen')).show();
        }

        function enviarOutlook(sap, nombre, id) {
            let link = window.location.origin + '/generar_informe.php?id=' + id;
            window.location.href = `mailto:?subject=Reporte SST SAP ${sap}&body=Reportante: ${nombre}%0D%0AInforme: ${link}`;
        }
    </script>
</body>
</html>
