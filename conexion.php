<?php
/**
 * Archivo de conexión a la base de datos
 * Utiliza PDO para una conexión segura y preparada contra SQL Injection
 */
// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'sst_reportes');
define('DB_USER', 'root');  // Cambiar según tu configuración
define('DB_PASS', '');      // Cambiar según tu configuración
define('DB_CHARSET', 'utf8mb4');
// Opciones de PDO para mayor seguridad
$opciones = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];
try {
    // Crear conexión PDO
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $opciones);
    
} catch (PDOException $e) {
    // En producción, no mostrar detalles del error
    error_log("Error de conexión: " . $e->getMessage());
    die("Error al conectar con la base de datos. Por favor, contacte al administrador.");
}
?>
