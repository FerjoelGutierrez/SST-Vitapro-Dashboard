<?php
// api/conexion.php

// 1. HOST: Lo saqué de tu URL de Supabase
$host = "db.piliozcqxjhcpeewpicx.supabase.co"; 

// 2. DATABASE y USER: Siempre son estos por defecto en Supabase
$db = "postgres";
$user = "postgres"; 

// 3. CONTRASEÑA: ¡AQUÍ PONES LA QUE ACABAS DE CREAR O RECORDAR!
// (NO es la 'sb_secret', es la contraseña que escribiste al crear el proyecto)
$pass = "ESCRIBE_AQUI_TU_CONTRASEÑA_DE_BASE_DE_DATOS"; 

// 4. PUERTO: 5432 es el estándar
$port = "5432"; 

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$db;options='--client_encoding=UTF8'";
    
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Si quieres probar si conecta, descomenta la linea de abajo:
    // echo "¡Conexión Exitosa!"; 

} catch (PDOException $e) {
    // Si falla, muestra el error
    die("Error de conexión: " . $e->getMessage());
}
?>
