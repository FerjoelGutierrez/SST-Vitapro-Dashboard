<?php
ob_start(); // Prevenir errores de headers
require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuarioInput = trim($_POST['usuario']);
    $passwordInput = trim($_POST['password']);

    try {
        // Verifica que la tabla en Supabase se llame 'usuarios'
        $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE usuario = ? AND password = ?');
        $stmt->execute([$usuarioInput, $passwordInput]);
        $user = $stmt->fetch();

        if ($user) {
            // CREAR LA COOKIE DE ACCESO (Dura 24 horas)
            // Importante: El path '/' asegura que funcione en todas las carpetas
            setcookie('auth_token', 'vitapro_admin_logged', time() + 86400, '/');
            
            header('Location: admin_nuevo.php');
            exit;
        } else {
            $error = 'Usuario o contraseña incorrectos';
        }
    } catch (PDOException $e) {
        $error = "Error DB: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso SST Vitapro</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;700&display=swap" rel="stylesheet">
</head>
<body style="background:#0f172a; display:flex; align-items:center; justify-content:center; height:100vh; font-family:'Outfit', sans-serif; margin:0;">
    <div style="background:white; padding:40px; border-radius:20px; width:100%; max-width:400px; box-shadow: 0 10px 25px rgba(0,0,0,0.5);">
        <div style="text-align:center; margin-bottom:30px;">
            <h2 style="color:#0f172a; margin:0;">VITAPRO SST</h2>
            <p style="color:#64748b; margin-top:5px;">Portal Administrativo</p>
        </div>

        <?php if(isset($error)): ?>
            <div style="background:#fee2e2; color:#b91c1c; padding:12px; border-radius:8px; margin-bottom:20px; text-align:center; font-size:14px;">
                ⚠️ <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div style="margin-bottom:20px;">
                <label style="display:block; color:#475569; font-weight:bold; margin-bottom:8px; font-size:14px;">Usuario</label>
                <input type="text" name="usuario" style="width:100%; padding:12px; border:1px solid #cbd5e1; border-radius:10px; box-sizing:border-box; font-size:16px;" required>
            </div>
            
            <div style="margin-bottom:30px;">
                <label style="display:block; color:#475569; font-weight:bold; margin-bottom:8px; font-size:14px;">Contraseña</label>
                <input type="password" name="password" style="width:100%; padding:12px; border:1px solid #cbd5e1; border-radius:10px; box-sizing:border-box; font-size:16px;" required>
            </div>

            <button type="submit" style="width:100%; padding:14px; background:#2563eb; color:white; border:none; border-radius:10px; cursor:pointer; font-weight:bold; font-size:16px; transition: background 0.3s;">
                INGRESAR AL SISTEMA
            </button>
        </form>
    </div>
</body>
</html>
