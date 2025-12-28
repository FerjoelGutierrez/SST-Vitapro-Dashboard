<?php
// Configuración para que las sesiones funcionen en el entorno sin estado de Vercel
ini_set('session.save_path', '/tmp');
// Credenciales desde variables de entorno (Configúralas en el Dashboard de Vercel)
$db_host = getenv('DB_HOST'); 
$db_port = getenv('DB_PORT') ?: '5432';
$db_name = getenv('DB_NAME') ?: 'postgres';
$db_user = getenv('DB_USER');
$db_pass = getenv('DB_PASS');
try {
    // Cadena de conexión PostgreSQL (DSN)
    $dsn = "pgsql:host=$db_host;port=$db_port;dbname=$db_name;sslmode=require";
    
    $pdo = new PDO($dsn, $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    // Error limpio para logs de Vercel
    error_log("Fallo de conexión: " . $e->getMessage());
    die("Error de infraestructura. Por favor, contacte al administrador.");
}
?>
