<?php
// Ya no iniciamos sesión aquí, lo hace conexion.php solo si hace falta
require_once 'conexion.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuarioInput = trim($_POST['usuario']);
    $passwordInput = trim($_POST['password']);
    try {
        // Buscamos al usuario (Sin quotes dobles para evitar errores de sintaxis)
        $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE usuario = ? AND password = ?');
        $stmt->execute([$usuarioInput, $passwordInput]);
        $user = $stmt->fetch();
        if ($user) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_name'] = $user['nombre'];
            header('Location: admin_nuevo.php'); // Te manda al Dashboard
            exit;
        } else {
            $error = 'Usuario o contraseña incorrectos';
        }
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>
<!-- El resto de tu HTML de Login se mantiene igual -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acceso Panel SST</title>
    <!-- Tus estilos aquí... -->
</head>
<body style="background:#0f172a; display:flex; align-items:center; justify-content:center; height:100vh; font-family:sans-serif;">
    <div style="background:white; padding:40px; border-radius:20px; width:350px;">
        <h2 style="text-align:center;">Acceso Panel SST</h2>
        <?php if(isset($error)): ?>
            <div style="background:#fee2e2; color:#b91c1c; padding:10px; border-radius:10px; margin-bottom:15px; text-align:center; font-size:14px;"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST">
            <input type="text" name="usuario" placeholder="Usuario" style="width:100%; padding:12px; margin-bottom:15px; border:1px solid #ddd; border-radius:8px;" required>
            <input type="password" name="password" placeholder="Contraseña" style="width:100%; padding:12px; margin-bottom:15px; border:1px solid #ddd; border-radius:8px;" required>
            <button type="submit" style="width:100%; padding:12px; background:#2563eb; color:white; border:none; border-radius:10px; cursor:pointer;">ENTRAR AL DASHBOARD</button>
        </form>
    </div>
</body>
</html>
