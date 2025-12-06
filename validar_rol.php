<?php
session_start(); // Inicia la sesión PHP
header('Content-Type: application/json'); // La respuesta será JSON

// Verificar si hay una sesión activa y si el rol del usuario está establecido
if (!isset($_SESSION['user_id']) || !isset($_SESSION['rol'])) {
    echo json_encode(["success" => false, "message" => "No hay sesión iniciada o rol no definido. Por favor, inicia sesión de nuevo."]);
    exit();
}

// Obtener el rol seleccionado por el usuario desde el POST (JSON)
$data = json_decode(file_get_contents("php://input"));
$selectedRole = $data->role ?? null; 

$userRoleInSession = $_SESSION['rol']; 

$redirectPath = ''; 
$success = false;
$message = '';

if ($selectedRole === null) {
    echo json_encode(["success" => false, "message" => "Rol no especificado."]);
    exit();
}

// Lógica de validación de roles y redirección
switch ($selectedRole) {
    case 'usuario':
        if ($userRoleInSession === 'usuario' || $userRoleInSession === 'administrador') {
            $success = true;
            $_SESSION['rol'] = 'usuario'; 
            $redirectPath = 'vista_php/vista_dashboard.php'; 
        } else {
            $message = "Acceso denegado. No tienes permisos para acceder como Usuario.";
        }
        break;
    case 'director':
        if ($userRoleInSession === 'director' || $userRoleInSession === 'administrador') {
            $success = true;
            $_SESSION['rol'] = 'director'; 
            $redirectPath = 'vista_php/vista_dashboard.php';
        } else {
            $message = "Acceso denegado. No tienes permisos para acceder como Director.";
        }
        break;
    case 'administrador':
        if ($userRoleInSession === 'administrador') {
            $success = true;
            $_SESSION['rol'] = 'administrador'; 
            $redirectPath = 'vista_php/vista_dashboard.php'; 
        } else {
            $message = "Acceso denegado. No tienes permisos para acceder como Administrador.";
        }
        break;
    default:
        $message = "Rol seleccionado inválido.";
        break;
}
// Devuelve la respuesta JSON al cliente
echo json_encode(["success" => $success, "message" => $message, "redirect" => $redirectPath]);
