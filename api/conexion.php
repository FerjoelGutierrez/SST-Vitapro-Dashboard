<?php
// api/conexion.php

// 1. Host (Tu proyecto Supabase)
$host = "db.piliozcqxjhcpeewpicx.supabase.co";

// 2. Datos por defecto de Supabase
$db = "postgres";
$user = "postgres";

// 3. TU CONTRASEÑA (La que me acabas de dar)
$pass = "nIcovita2025";

// 4. Puerto estándar
$port = "5432";

try {
    // Cadena de conexión para PostgreSQL
    $dsn = "pgsql:host=$host;port=$port;dbname=$db;options='--client_encoding=UTF8'";

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Si quieres verificar que conecta, descomenta la siguiente línea:
    // echo "¡Conectado exitosamente!";

} catch (PDOException $e) {
    // Si falla, mostramos el error
    die("Error de conexión: " . $e->getMessage());
}
?>
