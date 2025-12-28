<?php
// Configuración Supabase Vitapro 🛡️ (Modo de Alta Ingeniería - Pooler)
$host = 'aws-0-us-west-2.pooler.supabase.com'; // <--- ESTE ES EL CAMBIO CLAVE
$port = '6543'; // <--- ESTE ES EL CAMBIO CLAVE
$dbname = 'postgres';
$user = 'postgres.piliozcqxjhcpeewpicx';
$password = 'nIcovita2025';
try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("Error de conexión Supabase: " . $e->getMessage());
}
?>
