<?php
// Configuración Supabase Vitapro 🛡️
$host = 'db.piliozcqxjhcpeewpicx.supabase.co'; 
$port = '5432';
$dbname = 'postgres';
$user = 'postgres'; 
$password = 'TU_CONTRASEÑA_DE_SUPABASE'; // La que elegiste al crear el proyecto
try {
    // Usamos el driver pgsql para PostgreSQL
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Error crítico de conexión: " . $e->getMessage());
}
?>
