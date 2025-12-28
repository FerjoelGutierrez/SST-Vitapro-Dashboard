<?php
require_once 'conexion.php';
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=Matriz_SST_Vitapro_".date('Ymd').".xls");
$reportes = $pdo->query("SELECT * FROM reportes ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
<head><meta charset="UTF-8"></head>
<body>
    <table border="1">
        <tr style="background-color: #003366; color: white;">
            <th>ID</th>
            <th>FECHA</th>
            <th>REPORTANTE</th>
            <th>AREA</th>
            <th>HALLAZGO</th>
            <th>CAUSA ESPECIFICA</th>
            <th>RIESGO</th>
            <th>AVISO SAP</th>
            <th>DETUVO ACTIVIDAD</th>
            <th>DESCRIPCION</th>
            <th>LINK FOTO</th>
        </tr>
        <?php foreach($reportes as $r): ?>
        <tr>
            <td><?php echo $r['id']; ?></td>
            <td><?php echo date('d/m/Y', strtotime($r['fecha'])); ?></td>
            <td><?php echo utf8_decode($r['nombre']); ?></td>
            <td><?php echo utf8_decode($r['area']); ?></td>
            <td><?php echo utf8_decode($r['tipo_hallazgo']); ?></td>
            <td><?php echo utf8_decode($r['causa_especifica']); ?></td>
            <td style="color: <?php echo $r['nivel_riesgo']=='Alto' ? '#cc0000' : '#006600'; ?>;">
                <?php echo $r['nivel_riesgo']; ?>
            </td>
            <td><?php echo $r['aviso_sap'] ?: '-'; ?></td>
            <td><?php echo $r['detuvo_actividad']; ?></td>
            <td><?php echo utf8_decode($r['descripcion']); ?></td>
            <td><?php echo $r['foto_path']; ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
