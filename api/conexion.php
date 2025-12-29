<?php
// api/conexion.php

// 1. HOST: (Tu proyecto Supabase)
$host = "db.piliozcqxjhcpeewpicx.supabase.co";

// 2. Datos por defecto
$db = "postgres";
$user = "postgres";

// 3. TU CONTRASEÑA (La que definiste: nIcovita2025)
$pass = "nIcovita2025";

// --- IMPORTANTE: PUERTO 6543 ---
// Si dice 5432, fallará en Vercel. Tiene que ser 6543.
$port = "6543"; 

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$db;options='--client_encoding=UTF8'";
    
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        
        // --- IMPORTANTE: EMULATE_PREPARES en TRUE ---
        // Obligatorio para el puerto 6543.
        PDO::ATTR_EMULATE_PREPARES => true, 
    ];
    
    $pdo = new PDO($dsn, $user, $pass, $options);

} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>
