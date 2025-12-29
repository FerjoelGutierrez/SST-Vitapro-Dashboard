<?php
// api/conexion.php

// 1. HOST REGIONAL (Este fuerza IPv4 y evita el error de Vercel)
// Nota: Asumo que tu proyecto está en US East (es lo estándar).
$host = "aws-0-us-east-1.pooler.supabase.com";

// 2. ID DE TU PROYECTO SUPABASE
$project_id = "piliozcqxjhcpeewpicx";

// 3. BASE DE DATOS Y USUARIO
$db = "postgres";
// ¡OJO AQUÍ! El usuario ahora es "usuario.proyecto" para que el pooler sepa dónde ir
$user = "postgres.$project_id"; 

// 4. TU CONTRASEÑA
$pass = "nIcovita2025";

// 5. PUERTO DEL POOLER
$port = "6543"; 

try {
    // String de conexión
    $dsn = "pgsql:host=$host;port=$port;dbname=$db;options='--client_encoding=UTF8'";
    
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => true, // Obligatorio para puerto 6543
    ];
    
    $pdo = new PDO($dsn, $user, $pass, $options);

} catch (PDOException $e) {
    // Si sigue fallando, es probable que tu región no sea US-East-1.
    die("Error de conexión: " . $e->getMessage());
}
?>
