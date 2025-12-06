<?php

require_once '../auth_middleware.php'; 

if (!isset($_SESSION['user_id']) || !isset($_SESSION['rol'])) {
    header('Location: ../confia.php'); 
    exit();
}

$user_rol = $_SESSION['rol'];

switch ($user_rol) {
    case 'usuario':
        header('Location: vista_usuario.php');
        break;
    case 'director':
        header('Location: vista_director.php');
        break;
    case 'administrador':
        header('Location: vista_administrador.php');
        break;
    default:
        header('Location: logout.php?error=rol_invalido');
        break;
}
exit();
?>