<?php
session_start();
require 'conexion.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_POST['usuario'];
    $pass = $_POST['password'];

    // Buscar usuario en la base de datos
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = :u");
    $stmt->execute([':u' => $user]);
    $usuario = $stmt->fetch();

    // Verificar si existe y si la contraseña coincide
    // NOTA: Para producción real deberías usar password_verify() con hash
    if ($usuario && $usuario['password'] === $pass) {
        $_SESSION['user_id'] = $usuario['id'];
        $_SESSION['user_nombre'] = $usuario['nombre'];
        
        // Redirigir al panel de administración (asegúrate de crear este archivo después)
        header("Location: admin_nuevo.php"); 
        exit();
    } else {
        $error = "Usuario o contraseña incorrectos";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | SST Vitapro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #0f172a; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-card { background: white; padding: 40px; border-radius: 10px; width: 100%; max-width: 400px; box-shadow: 0 10px 25px rgba(0,0,0,0.5); }
        .btn-login { background: #2563eb; color: white; width: 100%; padding: 12px; font-weight: bold; border: none; border-radius: 5px; }
        .btn-login:hover { background: #1d4ed8; }
        .back-link { display: block; text-align: center; margin-top: 15px; color: #64748b; text-decoration: none; font-size: 0.9rem; }
    </style>
</head>
<body>
    <div class="login-card">
        <h3 class="text-center fw-bold mb-4" style="color:#0f172a">Acceso Admin</h3>
        
        <?php if($error): ?>
            <div class="alert alert-danger p-2 text-center small"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label fw-bold">Usuario</label>
                <input type="text" name="usuario" class="form-control" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Contraseña</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn-login">INGRESAR</button>
        </form>
        
        <a href="index.php" class="back-link">← Volver al formulario</a>
    </div>
</body>
</html>
