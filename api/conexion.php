<?php
// Configuración robusta de sesiones para Vercel
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.save_path', '/tmp');
    session_start();
}
// Credenciales desde variables de entorno (Vercel)
$host = getenv('DB_HOST');
$port = getenv('DB_PORT') ?: '6543';
$dbname = getenv('DB_NAME') ?: 'postgres';
$user = getenv('DB_USER');
$password = getenv('DB_PASS');
try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>
