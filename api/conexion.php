<?php
// Configuración Supabase Vitapro 🛡️ (Modo de Alta Ingeniería - Pooler)
$host = 'aws-0-us-west-2.pooler.supabase.com'; // El host de tu imagen
$port = '6543'; // El puerto del pooler
$dbname = 'postgres';
$user = 'postgres.piliozcqxjhcpeewpicx'; // El usuario largo de tu imagen
$password = 'nIcovita2025';
try {
    // Cadena de conexión con SSL obligatorio para Supabase
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
    
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    // Si falla, nos dirá el error exacto
    die("Error de conexión: " . $e->getMessage());
}
?>
