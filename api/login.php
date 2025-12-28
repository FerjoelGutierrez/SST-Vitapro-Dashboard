<?php
// Configuración obligatoria para sesiones en Vercel
ini_set('session.save_path', '/tmp');
session_start();
require_once 'conexion.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'];
    $password = $_POST['password'];
    try {
        // Consulta exacta para PostgreSQL (Supabase)
        $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE "usuario" = ? AND "password" = ?');
        $stmt->execute([$usuario, $password]);
        $user = $stmt->fetch();
        if ($user) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_name'] = $user['nombre'];
            header('Location: admin_nuevo.php'); // Redirige al Dashboard
            exit;
        } else {
            $error = 'Usuario o contraseña incorrectos';
        }
    } catch (PDOException $e) {
        $error = "Error de base de datos: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login SST - Vitapro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); height: 100vh; display: flex; align-items: center; justify-content: center; color: white; }
        .login-card { background: white; color: #1e293b; padding: 40px; border-radius: 20px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        .btn-primary { background: #2563eb; border: none; padding: 12px; font-weight: 700; border-radius: 12px; width: 100%; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="login-card text-center">
        <h3 class="fw-bold mb-4">Acceso Panel SST</h3>
        <?php if(isset($error)): ?>
            <div class="alert alert-danger py-2 small"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3 text-start">
                <label class="form-label small fw-bold text-muted">Usuario</label>
                <input type="text" name="usuario" class="form-control" required placeholder="admin">
            </div>
            <div class="mb-3 text-start">
                <label class="form-label small fw-bold text-muted">Contraseña</label>
                <input type="password" name="password" class="form-control" required placeholder="••••••••">
            </div>
            <button type="submit" class="btn btn-primary">ENTRAR AL DASHBOARD</button>
        </form>
        <p class="mt-4 small text-muted">Vitapro - Cero Accidentes</p>
    </div>
</body>
</html>
