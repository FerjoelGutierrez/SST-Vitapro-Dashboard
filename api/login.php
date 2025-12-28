<?php
require_once 'conexion.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuarioInput = trim($_POST['usuario']);
    $passwordInput = trim($_POST['password']);
    try {
        $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE usuario = ? AND password = ?');
        $stmt->execute([$usuarioInput, $passwordInput]);
        $user = $stmt->fetch();
        if ($user) {
            // En Vercel usamos COOKIES en lugar de SESSION
            // La cookie dura 1 día (86400 segundos)
            setcookie('auth_token', 'vitapro_admin_logged', time() + 86400, '/');
            header('Location: admin_nuevo.php');
            exit;
        } else {
            $error = 'Usuario o contraseña incorrectos';
        }
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>
<!-- El HTML del Login se mantiene igual, solo asegúrate de que el formulario siga enviando a este archivo -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acceso Panel SST</title>
</head>
<body style="background:#0f172a; display:flex; align-items:center; justify-content:center; height:100vh; font-family:sans-serif; color: white;">
    <div style="background:white; padding:40px; border-radius:20px; width:350px; color: #333;">
        <h2 style="text-align:center;">Acceso Panel SST</h2>
        <?php if(isset($error)): ?>
            <div style="background:#fee2e2; color:#b91c1c; padding:10px; border-radius:10px; margin-bottom:15px; text-align:center;"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST">
            <input type="text" name="usuario" placeholder="Usuario" style="width:90%; padding:12px; margin-bottom:15px; border:1px solid #ddd; border-radius:8px;" required>
            <input type="password" name="password" placeholder="Contraseña" style="width:90%; padding:12px; margin-bottom:15px; border:1px solid #ddd; border-radius:8px;" required>
            <button type="submit" style="width:100%; padding:12px; background:#2563eb; color:white; border:none; border-radius:10px; cursor:pointer; font-weight: bold;">ENTRAR AL DASHBOARD</button>
        </form>
    </div>
</body>
</html>
