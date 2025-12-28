<?php
/**
 * Conexión a Base de Datos Supabase (PostgreSQL)
 * Usamos PDO para máxima seguridad.
 */

// Si usas variables de entorno en Vercel (Recomendado):
$host = getenv('DB_HOST') ?: 'tu-host-de-supabase.supabase.co';
$db   = getenv('DB_NAME') ?: 'postgres';
$user = getenv('DB_USER') ?: 'postgres';
$pass = getenv('DB_PASS') ?: 'tu-contraseña-segura';
$port = getenv('DB_PORT') ?: '5432';

$dsn = "pgsql:host=$host;port=$port;dbname=$db";

try {
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    // Si falla, mostramos mensaje (en producción idealmente no mostrar detalles)
    die("Error de conexión a Base de Datos: " . $e->getMessage());
}
?>
