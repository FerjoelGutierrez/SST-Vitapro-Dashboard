<?php
session_start();
require_once 'conexion.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'];
    $password = $_POST['password'];
    try {
        // Usamos comillas dobles para las columnas, así PostgreSQL no se confunde
        $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE "usuario" = ? AND "password" = ?');
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
    } catch (PDOException $e) {
        $error = "Err: " . $e->getMessage();
    }
}
?>
<!-- Aquí sigue tu HTML de login de siempre... -->
