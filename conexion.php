<?php
// Configuración de la conexión a MySQL usando PDO
$host     = 'mysql_database';
$port     = 3306;
$dbName   = 'muelitas';
$username = 'root';
$password = 'root_password'; // Vacío por defecto en XAMPP o bien contraseña segun sea el caso.
$charset  = 'utf8mb4';

// Configurar DSN (Data Source Name) con el puerto específico
$dsn = "mysql:host=$host;port=$port;dbname=$dbName;charset=$charset";

// Opciones de configuración de PDO para mayor seguridad y control de errores
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Lanzar excepciones en caso de error
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Resultados devueltos como arreglo asociativo
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Desactivar emulación de consultas preparadas
];

try {
    // Establecer la conexión
    $pdo = new PDO($dsn, $username, $password, $options);
    
    // Opcional: Descomentar la siguiente línea durante pruebas de depuración
    // echo "Conexión a la base de datos establecida con éxito.";
} catch (\PDOException $e) {   
    die("Error de conexión en la base de datos: " . $e->getMessage());
}
?>
