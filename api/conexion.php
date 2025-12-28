<?php
// Configuración Supabase Vitapro 🛡️ (Repositiorio Privado - Seguro)
$host = 'db.piliozcqxjhcpeewpicx.supabase.co'; 
$port = '5432';
$dbname = 'postgres';
$user = 'postgres'; 
$password = 'nIcovita2025'; 
try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Error crítico de conexión.");
}
?>
