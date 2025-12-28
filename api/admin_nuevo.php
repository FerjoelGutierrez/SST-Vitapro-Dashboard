<?php
// ... (Mantén tu lógica de verificación de cookies y conexión al principio igual)
require_once 'conexion.php';
try {
    // KPIs (Optimizado)
    $sqlKPI = "SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN nivel_riesgo = 'Alto' THEN 1 ELSE 0 END) as criticos,
        SUM(CASE WHEN tipo_hallazgo LIKE '%Acto%' THEN 1 ELSE 0 END) as actos,
        SUM(CASE WHEN tipo_hallazgo LIKE '%Condición%' THEN 1 ELSE 0 END) as condiciones
        FROM reportes";
    $kpi = $pdo->query($sqlKPI)->fetch(PDO::FETCH_ASSOC);
    // Consulta para la matriz
    $reportes = $pdo->query("SELECT * FROM reportes ORDER BY id DESC LIMIT 50")->fetchAll(PDO::FETCH_ASSOC);
    
    // Gráficos (Pareto)
    $topActos = $pdo->query("SELECT causa_especifica as descripcion, COUNT(*) as cantidad FROM reportes WHERE tipo_hallazgo LIKE '%Acto%' GROUP BY causa_especifica ORDER BY cantidad DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    $topCond = $pdo->query("SELECT causa_especifica as descripcion, COUNT(*) as cantidad FROM reportes WHERE tipo_hallazgo LIKE '%Condición%' GROUP BY causa_especifica ORDER BY cantidad DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) { die("Error: " . $e->getMessage()); }
?>
<!-- ... (Estilos iguales, pero añade este para la tabla) ... -->
<style>
    .img-thumb { width: 50px; height: 50px; object-fit: cover; border-radius: 8px; cursor: pointer; border: 2px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
    .badge-riesgo { padding: 8px 12px; font-weight: 700; border-radius: 20px; text-transform: uppercase; font-size: 10px; }
</style>
<!-- DENTRO DEL TBODY DE TU TABLA, REEMPLAZA LAS FILAS: -->
<?php foreach($reportes as $r): ?>
<tr>
    <td class="fw-bold">#<?php echo $r['id']; ?></td>
    <td class="small"><?php echo date('d/m/Y', strtotime($r['fecha'])); ?></td>
    <td>
        <!-- MINIATURA DE FOTO -->
        <?php if($r['foto_path']): ?>
            <img src="<?php echo $r['foto_path']; ?>" class="img-thumb" data-bs-toggle="modal" data-bs-target="#imgModal<?php echo $r['id']; ?>">
            <!-- Modal para ver foto grande -->
            <div class="modal fade" id="imgModal<?php echo $r['id']; ?>" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered"><div class="modal-content"><img src="<?php echo $r['foto_path']; ?>" class="img-fluid rounded"></div></div>
            </div>
        <?php else: ?>
            <div class="bg-light text-muted small rounded d-flex align-items-center justify-content-center" style="width:50px; height:50px;">N/A</div>
        <?php endif; ?>
    </td>
    <td><span class="badge <?php echo strpos($r['tipo_hallazgo'], 'Acto') !== false ? 'bg-warning text-dark' : 'bg-info text-dark'; ?>"><?php echo $r['tipo_hallazgo']; ?></span></td>
    <td class="small"><?php echo htmlspecialchars($r['causa_especifica'] ?? 'Sin detalle'); ?></td>
    <td><span class="badge-riesgo <?php echo $r['nivel_riesgo'] == 'Alto' ? 'bg-danger text-white' : 'bg-success text-white'; ?>"><?php echo $r['nivel_riesgo']; ?></span></td>
    <td class="text-end">
        <a href="generar_informe.php?id=<?php echo $r['id']; ?>" target="_blank" class="btn btn-sm btn-outline-danger"><i class="fas fa-file-pdf"></i> Informe</a>
    </td>
</tr>
<?php endforeach; ?>
<!-- MODAL QR CORREGIDO (Google Chart API es más estable) -->
<div class="modal fade" id="modalQR" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered text-center">
        <div class="modal-content p-5 border-0" style="border-radius:30px;">
            <h4 class="fw-bold">Habilitar Reportes SST</h4>
            <p class="text-muted small">Muestre este código a los colaboradores</p>
            <div class="bg-light p-4 rounded-4 d-inline-block border mx-auto">
                <img src="https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=<?php echo urlencode('https://'.$_SERVER['HTTP_HOST'].'/'); ?>&choe=UTF-8" class="img-fluid" style="width:250px;">
            </div>
            <button class="btn btn-primary mt-4 py-3 rounded-pill fw-bold" onclick="window.print()">IMPRIMIR CARTEL</button>
        </div>
    </div>
</div>
