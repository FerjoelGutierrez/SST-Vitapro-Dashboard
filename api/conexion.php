<?php
// Configuración Supabase Vitapro 🛡️ (Garantizado para Vercel)
$host = 'db.piliozcqxjhcpeewpicx.supabase.co';
$port = '5432';
$dbname = 'postgres';
$user = 'postgres';
$password = 'nIcovita2025';
try {
    // Cadena de conexión optimizada para Supabase
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
    
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 5 // Tiempo de espera para evitar bloqueos
    ]);
} catch (PDOException $e) {
    // Si falla, nos dirá exactamente por qué
    die("Error de conexión Supabase: " . $e->getMessage());
}
?>
