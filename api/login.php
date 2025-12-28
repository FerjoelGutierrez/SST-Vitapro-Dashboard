<?php
session_start();
require_once 'conexion.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario = $_POST['usuario'] ?? '';
    $password = $_POST['password'] ?? '';
    if (!empty($usuario) && !empty($password)) {
        // En un entorno profesional usaríamos password_verify, 
        // pero para asegurar que entres hoy mismo usaremos comparación directa
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = ? AND password = ?");
        $stmt->execute([$usuario, $password]);
        $user = $stmt->fetch();
        if ($user) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_name'] = $user['nombre'];
            header('Location: admin_nuevo.php');
            exit;
        } else {
            $error = 'Usuario o contraseña incorrectos';
        }
    } else {
        $error = 'Por favor rellene todos los campos';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema SST</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', sans-serif;
        }
        .login-card {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
        }
        .btn-primary {
            background: #1e3a8a;
            border: none;
            padding: 12px;
        }
        .logo-sst {
            text-align: center;
            margin-bottom: 25px;
            color: #1e3a8a;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="logo-sst">
            <h3><i class="fas fa-shield-alt"></i> Panel SST</h3>
            <p class="text-muted">Acceso Administrativo</p>
        </div>
        
        <?php if($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Usuario</label>
                <input type="text" name="usuario" class="form-control" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label">Contraseña</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Iniciar Sesión</button>
        </form>
    </div>
</body>
</html>
