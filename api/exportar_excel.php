<?php
require_once 'conexion.php';

// Nombre del archivo
$filename = "Reporte_SST_Completo_" . date('Ymd') . ".xls";

// Headers para forzar descarga
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=\"$filename\"");

// Obtener datos
$sql = "SELECT * FROM reportes ORDER BY id DESC";
$stmt = $pdo->query($sql);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Imprimir encabezados de columna
$flag = false;
foreach($rows as $row) {
    if(!$flag) {
        // Títulos de columnas (keys del array)
        echo implode("\t", array_keys($row)) . "\n";
        $flag = true;
    }
    // Limpiar datos para evitar saltos de línea que rompan el excel
    array_walk($row, function(&$str) {
        $str = preg_replace("/\t/", "\\t", $str);
        $str = preg_replace("/\r?\n/", " ", $str);
        if(strstr($str, '"')) $str = '"' . str_replace('"', '""', $str) . '"';
    });
    
    echo implode("\t", array_values($row)) . "\n";
}
exit;
?>
