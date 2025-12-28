<?php
require_once 'conexion.php';

// Nombre del archivo
$filename = "Matriz_SST_Vitapro_" . date('Ymd_His') . ".xls";

// Headers para que el navegador lo descargue como Excel
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

// Obtener datos
try {
    // Ajusta los nombres de columna (ej. 'descripcion' vs 'descripcion_breve') según tu BD real
    $sql = "SELECT id, fecha, nombre, tipo_hallazgo, nivel_riesgo, descripcion, aviso_sap, foto_path FROM reportes ORDER BY id DESC";
    $stmt = $pdo->query($sql);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Contadores para estadísticas
    $total = count($rows);
    $altos = 0; $medios = 0; $bajos = 0;

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit;
}
?>

<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style>
        .header { background-color: #003366; color: #ffffff; font-weight: bold; font-size: 14pt; }
        .sub-header { background-color: #f2f2f2; font-weight: bold; }
        .th-col { background-color: #ffffff; border: 1px solid #000; font-weight: bold; text-align: center; }
        .risk-alto { background-color: #ffcccc; color: #cc0000; }
        .risk-medio { background-color: #fff4cc; color: #996600; }
        .risk-bajo { background-color: #ccffcc; color: #006600; }
        td { border: 1px solid #cccccc; vertical-align: middle; }
    </style>
</head>
<body>

<table>
    <tr>
        <td colspan="8" class="header" height="40">Matriz de Seguimiento SST - Vitapro</td>
    </tr>
    <tr>
        <td colspan="8">Fecha de exportación: <?php echo date('d/m/Y H:i:s'); ?></td>
    </tr>
    <tr>
        <td colspan="8">Total de registros en matriz: <?php echo $total; ?></td>
    </tr>
    <tr><td></td></tr>

    <tr style="text-align:center;">
        <th class="th-col" style="border: 2px solid black;">ID</th>
        <th class="th-col" style="border: 2px solid black;">Fecha</th>
        <th class="th-col" style="border: 2px solid black;">Nombre</th>
        <th class="th-col" style="border: 2px solid black;">Tipo de Hallazgo</th>
        <th class="th-col" style="border: 2px solid black;">Nivel de Riesgo</th>
        <th class="th-col" style="border: 2px solid black;">Descripción / Hallazgo</th>
        <th class="th-col" style="border: 2px solid black;">Aviso SAP</th>
        <th class="th-col" style="border: 2px solid black;">Tiene Foto</th>
    </tr>

    <?php foreach($rows as $r): 
        // Lógica de conteo
        if($r['nivel_riesgo'] == 'Alto') $altos++;
        elseif($r['nivel_riesgo'] == 'Medio') $medios++;
        else $bajos++;
        
        // Color según riesgo
        $bg_riesgo = '';
        if($r['nivel_riesgo'] == 'Alto') $bg_riesgo = 'background-color:#FFC7CE; color:#9C0006;';
        if($r['nivel_riesgo'] == 'Medio') $bg_riesgo = 'background-color:#FFEB9C; color:#9C5700;';
        if($r['nivel_riesgo'] == 'Bajo') $bg_riesgo = 'background-color:#C6EFCE; color:#006100;';
    ?>
    <tr>
        <td style="text-align:center;"><?php echo $r['id']; ?></td>
        <td style="text-align:center;"><?php echo date('d/m/Y H:i', strtotime($r['fecha'])); ?></td>
        <td><?php echo mb_convert_encoding($r['nombre'], 'HTML-ENTITIES', 'UTF-8'); ?></td>
        <td><?php echo mb_convert_encoding($r['tipo_hallazgo'], 'HTML-ENTITIES', 'UTF-8'); ?></td>
        <td style="text-align:center; font-weight:bold; <?php echo $bg_riesgo; ?>">
            <?php echo $r['nivel_riesgo']; ?>
        </td>
        <td><?php echo mb_convert_encoding($r['descripcion'], 'HTML-ENTITIES', 'UTF-8'); ?></td>
        <td style="text-align:center;"><?php echo $r['aviso_sap'] ? $r['aviso_sap'] : '-'; ?></td>
        <td style="text-align:center;"><?php echo ($r['foto_path'] && $r['foto_path'] != '') ? 'Sí' : 'No'; ?></td>
    </tr>
    <?php endforeach; ?>

    <tr><td></td></tr>
    <tr><td></td></tr>

    <tr>
        <td colspan="2" style="font-weight:bold; border: 1px solid black; background:#efefef;">Estadísticas:</td>
    </tr>
    <tr>
        <td style="background:#C6EFCE; border: 1px solid black;">Bajo</td>
        <td style="border: 1px solid black; text-align:center;"><?php echo $bajos; ?></td>
    </tr>
    <tr>
        <td style="background:#FFEB9C; border: 1px solid black;">Medio</td>
        <td style="border: 1px solid black; text-align:center;"><?php echo $medios; ?></td>
    </tr>
    <tr>
        <td style="background:#FFC7CE; border: 1px solid black;">Alto</td>
        <td style="border: 1px solid black; text-align:center;"><?php echo $altos; ?></td>
    </tr>
</table>
</body>
</html>
