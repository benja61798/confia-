<?php

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confía+: <?php echo $pageTitle ?? 'Plataforma'; ?></title>
    <link rel="icon" type="image/png" href="img/logo.png"> 
    <link rel="stylesheet" href="css/estilos_generales.css">
    <link rel="stylesheet" href="css/login-page.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <?php if (isset($customCss)): ?>
    <?php endif; ?>
</head>
<body>
    <header class="bg-primary-custom text-white p-3 shadow-sm">
        <div class="container d-flex justify-content-between align-items-center">
            <h1 class="h4 mb-0"><a href="seleccionar_rol.php" class="text-white text-decoration-none">Confía+</a></h1>
            <nav>
                <ul class="list-unstyled d-flex mb-0">
                    <li class="me-3"><a href="vistas_php/vista_usuario.php" class="text-white text-decoration-none">Mi Perfil</a></li>
                    <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'administrador'): ?>
                        <li class="me-3"><a href="vistas_php/vista_administrador.php" class="text-white text-decoration-none">Admin</a></li>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['rol']) && ($_SESSION['rol'] === 'director' || $_SESSION['rol'] === 'administrador')): ?>
                        <li class="me-3"><a href="vistas_php/vista_director.php" class="text-white text-decoration-none">Director</a></li>
                    <?php endif; ?>
                    <li><a href="logout.php" class="btn btn-sm btn-outline-light">Cerrar Sesión</a></li>
                </ul>
            </nav>
        </div>
    </header>
    <main class="container my-4">