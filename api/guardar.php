php
// Incluir la conexión a Supabase
require_once 'conexion.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Recoger los datos del formulario
    $tipo_u   = $_POST['tipo_usuario'] ?? '';
    $nombre   = $_POST['nombre'] ?: 'Anónimo';
    $empresa  = $_POST['empresa_contratista'] ?? '';
    $area     = $_POST['area'] ?? '';
    $tipo_h   = $_POST['tipo_hallazgo'] ?? '';
    $causa    = $_POST['causa_especifica'] ?? '';
    $nivel    = $_POST['nivel_riesgo'] ?? 'Bajo';
    $sap      = $_POST['aviso_sap'] ?? '';
    $paro     = $_POST['detuvo_actividad'] ?? 'NO';
    $desc     = $_POST['descripcion'] ?? '';
    $foto_url = $_POST['foto_url'] ?? ''; // URL pública de Supabase enviada por JS
    try {
        // 2. Preparar el INSERT (Sintaxis exacta para PostgreSQL/Supabase)
        $sql = "INSERT INTO reportes 
                (tipo_usuario, nombre, empresa_contratista, area, tipo_hallazgo, causa_especifica, nivel_riesgo, aviso_sap, detuvo_actividad, descripcion, foto_path) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $tipo_u, 
            $nombre, 
            $empresa, 
            $area, 
            $tipo_h, 
            $causa, 
            $nivel, 
            $sap, 
            $paro, 
            $desc, 
            $foto_url
        ]);
        // 3. REDIRECCIÓN DE ÉXITO (Vuelve al formulario)
        // Esto evita que te mande al login involuntariamente
        header('Location: index.php?success=1');
        exit;
    } catch (PDOException $e) {
        // En caso de error, nos dirá exactamente qué pasó
        die("Error crítico al guardar en la nube: " . $e->getMessage());
    }
} else {
    // Si alguien intenta entrar a guardar.php sin enviar datos, lo mandamos al inicio
    header('Location: index.php');
    exit;
}
?>
