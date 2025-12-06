<?php
require_once 'auth_middleware.php';

if (isset($_SESSION['rol']) && $_SESSION['rol'] !== 'sin_rol_asignar') { 
     header('Location: vistas_php/vista_dashboard.php'); 
     exit();
}
if (!isset($_SESSION['user_id'])) {
    header('Location: confia.php'); 
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confía+: Seleccionar Rol</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/estilos_generales.css">
    <link rel="stylesheet" href="css/login-page.css">
    <style>
        /* Estilos adicionales para los botones si no los cubre style.css o login-page.css */
        .btn-primary-custom {
            background-color: #007bff; /* Un color azul, ajusta a tu tema */
            border-color: #007bff;
            color: white;
        }
        .btn-primary-custom:hover {
            background-color: #0056b3;
            border-color: #004085;
        }
        .btn-secondary { /* Por si quieres un estilo diferente para director */
            background-color: #6c757d;
            border-color: #6c757d;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
            border-color: #545b62;
        }
        .btn-dark { /* Por si quieres un estilo diferente para administrador */
            background-color: #343a40;
            border-color: #343a40;
        }
        .btn-dark:hover {
            background-color: #23272b;
            border-color: #1d2124;
        }
        .auth-message-custom {
            padding: 10px;
            border-radius: 5px;
            margin-top: 15px;
            text-align: center;
        }
        .auth-message-custom.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100 bg-light">
    <div class="container text-center p-4 rounded shadow-lg bg-white" style="max-width: 600px;">
        <h2 class="mb-4 text-primary-custom">Selecciona tu Rol</h2>
        <p class="text-muted mb-4">Por favor, elige el rol con el que deseas acceder a la plataforma.</p>
        <div class="d-grid gap-3">
            <button class="btn btn-lg btn-primary-custom" data-role="usuario">Usuario / Alumno</button>
            <button class="btn btn-lg btn-secondary" data-role="director">Director / Profesor</button>
            <button class="btn btn-lg btn-dark" data-role="administrador">Administrador</button>
        </div>
        <div id="roleErrorMessage" class="auth-message-custom mt-4" style="display: none;"></div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const buttons = document.querySelectorAll('[data-role]');
            const errorMessageDiv = document.getElementById('roleErrorMessage');

            buttons.forEach(button => {
                button.addEventListener('click', async () => {
                    const selectedRole = button.dataset.role;

                    try {
                        const res = await fetch('validar_rol.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ role: selectedRole })
                        });
                        const result = await res.json();

                        if (result.success) {
                            window.location.href = result.redirect;
                        } else {
                            errorMessageDiv.textContent = result.message;
                            errorMessageDiv.className = 'auth-message-custom error';
                            errorMessageDiv.style.display = 'block';
                        }
                    } catch (err) {
                        console.error('Error al validar rol:', err);
                        errorMessageDiv.textContent = 'Error de conexión al servidor.';
                        errorMessageDiv.className = 'auth-message-custom error';
                        errorMessageDiv.style.display = 'block';
                    }
                });
            });
        });
    </script>
</body>
</html>