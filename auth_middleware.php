<?php


// Asegurarse de que la sesión esté iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Función para proteger una vista por rol
function protect_page($allowed_roles, $redirect_url = 'index.php') {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['rol'])) {
        // No hay sesión, redirigir al login
        header('Location: ' . $redirect_url);
        exit();
    }

    $user_rol = $_SESSION['rol'];

    // Si el rol del usuario no está en la lista de roles permitidos, redirigir
    if (!in_array($user_rol, $allowed_roles)) {
        // Podrías redirigir a una página de "Acceso Denegado" más específica si quieres
        header('Location: ' . $redirect_url . '?error=acceso_denegado');
        exit();
    }
}
