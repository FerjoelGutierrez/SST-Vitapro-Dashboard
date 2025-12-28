<?php
/**
 * Exportar Reportes a Excel
 * Genera un archivo Excel con todos los reportes filtrados
 */
require_once 'conexion.php';
// Obtener filtros de la sesión o parámetros GET
$filtro_area = $_GET['area'] ?? '';
$filtro_riesgo = $_GET['riesgo'] ?? '';
$filtro_tipo = $_GET['tipo'] ?? '';
$filtro_usuario = $_GET['usuario'] ?? '';
$busqueda = $_GET['busqueda'] ?? '';
// Construir consulta SQL con filtros
$where_conditions = [];
$params = [];
if (!empty($filtro_area)) {
    $where_conditions[] = "area = :area";
    $params[':area'] = $filtro_area;
}
if (!empty($filtro_riesgo)) {
    $where_conditions[] = "nivel_riesgo = :riesgo";
    $params[':riesgo'] = $filtro_riesgo;
}
if (!empty($filtro_tipo)) {
    $where_conditions[] = "tipo_hallazgo = :tipo";
    $params[':tipo'] = $filtro_tipo;
}
if (!empty($filtro_usuario)) {
    $where_conditions[] = "tipo_usuario = :usuario";
    $params[':usuario'] = $filtro_usuario;
}
if (!empty($busqueda)) {
    $where_conditions[] = "(descripcion LIKE :busqueda OR nombre LIKE :busqueda OR empresa_contratista LIKE :busqueda)";
    $params[':busqueda'] = "%$busqueda%";
}
$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";
// Obtener reportes
try {
    $sql = "SELECT * FROM reportes $where_clause ORDER BY fecha DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $reportes = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error al obtener reportes: " . $e->getMessage());
}
// Configurar headers para descarga de Excel
header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
header('Content-Disposition: attachment; filename="Reportes_SST_' . date('Y-m-d_His') . '.xls"');
header('Pragma: no-cache');
header('Expires: 0');
// Iniciar tabla HTML (Excel puede leer HTML)
echo "\xEF\xBB\xBF"; // BOM para UTF-8
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        table { border-collapse: collapse; width: 100%; }
        th { background-color: #1e3a8a; color: white; font-weight: bold; padding: 10px; border: 1px solid #000; }
        td { padding: 8px; border: 1px solid #000; }
        .alto { background-color: #fee2e2; color: #991b1b; font-weight: bold; }
        .medio { background-color: #fef3c7; color: #92400e; font-weight: bold; }
        .bajo { background-color: #d1fae5; color: #065f46; font-weight: bold; }
    </style>
</head>
<body>
    <h1 style="color: #1e3a8a; font-family: sans-serif;">Matriz de Seguimiento SST - Vitapro</h1>
    <p style="font-family: sans-serif;">Fecha de exportación: <?php echo date('d/m/Y H:i:s'); ?></p>
    <p style="font-family: sans-serif;">Total de registros en matriz: <?php echo count($reportes); ?></p>
    
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Fecha</th>
                <th>Tipo de Usuario</th>
                <th>Nombre</th>
                <th>Empresa Contratista</th>
                <th>Área</th>
                <th>Tipo de Hallazgo</th>
                <th>Nivel de Riesgo</th>
                <th>Descripción</th>
                <th>Tiene Foto</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($reportes as $reporte): ?>
            <tr>
                <td><?php echo $reporte['id']; ?></td>
                <td><?php echo date('d/m/Y H:i', strtotime($reporte['fecha'])); ?></td>
                <td><?php echo $reporte['tipo_usuario']; ?></td>
                <td><?php echo $reporte['nombre'] ?: 'Anónimo'; ?></td>
                <td><?php echo $reporte['empresa_contratista'] ?: '-'; ?></td>
                <td><?php echo $reporte['area']; ?></td>
                <td><?php echo $reporte['tipo_hallazgo']; ?></td>
                <td class="<?php echo strtolower($reporte['nivel_riesgo']); ?>">
                    <?php echo $reporte['nivel_riesgo']; ?>
                </td>
                <td><?php echo htmlspecialchars($reporte['descripcion']); ?></td>
                <td><?php echo $reporte['foto_path'] ? 'Sí' : 'No'; ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <br><br>
    <p><strong>Estadísticas:</strong></p>
    <table style="width: 50%;">
        <tr>
            <th>Nivel de Riesgo</th>
            <th>Cantidad</th>
        </tr>
        <tr>
            <td class="alto">Alto</td>
            <td><?php echo count(array_filter($reportes, fn($r) => $r['nivel_riesgo'] === 'Alto')); ?></td>
        </tr>
        <tr>
            <td class="medio">Medio</td>
            <td><?php echo count(array_filter($reportes, fn($r) => $r['nivel_riesgo'] === 'Medio')); ?></td>
        </tr>
        <tr>
            <td class="bajo">Bajo</td>
            <td><?php echo count(array_filter($reportes, fn($r) => $r['nivel_riesgo'] === 'Bajo')); ?></td>
        </tr>
    </table>
</body>
</html>
