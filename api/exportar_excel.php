<?php
// SEGURIDAD
if (!isset($_COOKIE['auth_token'])) exit;
require_once 'conexion.php';

// HEADERS DE EXCEL
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=Matriz_SST_Vitapro.xls");
header("Pragma: no-cache");
header("Expires: 0");

// BOM para que Excel reconozca tildes automáticamente
echo "\xEF\xBB\xBF"; 

// DATOS
$stmt = $pdo->query("SELECT * FROM reportes ORDER BY id DESC");
$reportes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ESTADISTICAS PARA EL PIE
$total = count($reportes);
$alto = 0; $medio = 0; $bajo = 0;
foreach($reportes as $r) {
    if ($r['nivel_riesgo'] === 'Alto') $alto++;
    elseif ($r['nivel_riesgo'] === 'Medio') $medio++;
    else $bajo++;
}
?>
<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<style>
    .titulo { color: #1e3a8a; font-size: 24px; font-weight: bold; font-family: Arial; }
    .header { background-color: white; color: black; font-weight: bold; text-align: center; border: 1px solid black; }
    .cell { border: 1px solid black; font-family: Arial; font-size: 12px; vertical-align: middle;}
    .alto { background-color: #fee2e2; color: #991b1b; font-weight: bold; }
    .medio { background-color: #fef3c7; color: #92400e; font-weight: bold; }
    .bajo { background-color: #d1fae5; color: #065f46; font-weight: bold; }
</style>
</head>
<body>
    <div class="titulo">Matriz de Seguimiento SST - Vitapro</div>
    <br>
    <div>Fecha de exportación: <?php echo date('d/m/Y H:i:s'); ?></div>
    <div>Total de registros en matriz: <?php echo $total; ?></div>
    <br>
    <table cellspacing="0" cellpadding="5">
        <thead>
            <tr>
                <td class="header">ID</td>
                <td class="header">Fecha</td>
                <td class="header">Nombre</td>
                <td class="header">Empresa Contratista</td>
                <td class="header">Área</td>
                <td class="header">Tipo de Hallazgo</td>
                <td class="header">Nivel de Riesgo</td>
                <td class="header">Descripción</td>
                <td class="header">Aviso SAP</td>
                <td class="header">Tiene Foto</td>
            </tr>
        </thead>
        <tbody>
            <?php foreach($reportes as $r): ?>
            <tr>
                <td class="cell" align="center"><?php echo $r['id']; ?></td>
                <td class="cell"><?php echo date('d/m/Y H:i', strtotime($r['fecha'])); ?></td>
                <td class="cell"><?php echo $r['nombre']; ?></td>
                <td class="cell"><?php echo $r['empresa_contratista'] ?: '-'; ?></td>
                <td class="cell"><?php echo $r['area']; ?></td>
                <td class="cell"><?php echo $r['tipo_hallazgo']; ?></td>
                <?php 
                    $class = 'bajo';
                    if($r['nivel_riesgo'] == 'Alto') $class = 'alto';
                    if($r['nivel_riesgo'] == 'Medio') $class = 'medio';
                ?>
                <td class="cell <?php echo $class; ?>" align="center"><?php echo $r['nivel_riesgo']; ?></td>
                <td class="cell"><?php echo $r['descripcion']; ?></td>
                <td class="cell"><?php echo $r['aviso_sap'] ?: '-'; ?></td>
                <td class="cell" align="center"><?php echo $r['foto_path'] ? 'Sí' : 'No'; ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <br><br>
    <b>Estadísticas:</b>
    <br><br>
    <table cellspacing="0" cellpadding="5" style="width: 200px; border: 1px solid black;">
        <tr><td class="header" style="background:#d1fae5">Nivel de Riesgo</td><td class="header" style="background:#d1fae5">Cantidad</td></tr>
        <tr><td class="cell alto">Alto</td><td class="cell" align="center"><?php echo $alto; ?></td></tr>
        <tr><td class="cell medio">Medio</td><td class="cell" align="center"><?php echo $medio; ?></td></tr>
        <tr><td class="cell bajo">Bajo</td><td class="cell" align="center"><?php echo $bajo; ?></td></tr>
    </table>
</body>
</html>
