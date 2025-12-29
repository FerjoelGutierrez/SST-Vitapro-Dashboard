<?php
$host = "aws-0-us-east-1.pooler.supabase.com"; // Tu host de Supabase (búscalo en Settings > Database > Connection params)
$db = "postgres";
$user = "postgres.tu_usuario";
$pass = "tu_contraseña";
$port = "5432"; // Puerto estándar de Postgres (o 6543 si usas el pooler)

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$db;";
    // Opciones para manejo de errores
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    $pdo = new PDO($dsn, $user, $pass, $options);
    
} catch (PDOException $e) {
    // En producción no muestres el error exacto, pero para debug sirve
    die("Error de conexión: " . $e->getMessage());
}
?>
