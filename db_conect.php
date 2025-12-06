<?php
// Configuración de errores (para desarrollo, quitar o modificar en producción)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Credenciales de la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "confia+_db"; 
// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión a la base de datos: " . $conn->connect_error);
}
