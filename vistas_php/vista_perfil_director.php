<?php
// vistas_php/vista_perfil_director.php
require_once '../auth_middleware.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
require_once '../db_conect.php';

if ($conn->connect_error) {
    die("Error de conexión a la base de datos: " . $conn->connect_error);
}

// Solo directores pueden acceder
protect_page(['director']);

$pageTitle = "Perfil de Director";

// Obtener datos del usuario desde la sesión o la base de datos si es necesario
$user_id = $_SESSION['user_id'] ?? null;
$nombre_usuario = $_SESSION['nombre_usuario'] ?? 'N/A';
$correo_usuario = $_SESSION['correo'] ?? 'N/A';

// Procesar edición de perfil
$edit_success = null;
$edit_error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_profile'])) {
    $nuevo_nombre = trim($_POST['edit_nombre']);
    $nuevo_correo = trim($_POST['edit_correo']);
    $nueva_contrasena = $_POST['edit_contrasena'];

    // Validaciones básicas
    if ($nuevo_nombre === '' || $nuevo_correo === '') {
        $edit_error = "El nombre y el correo no pueden estar vacíos.";
    } else {
        // Actualizar en la base de datos
        if ($nueva_contrasena !== '') {
            // Si se cambia la contraseña
            $hash = password_hash($nueva_contrasena, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE usuarios SET nombre_usuario=?, correo=?, contrasena=? WHERE id=?");
            $stmt->bind_param("sssi", $nuevo_nombre, $nuevo_correo, $hash, $user_id);
        } else {
            // Sin cambiar la contraseña
            $stmt = $conn->prepare("UPDATE usuarios SET nombre_usuario=?, correo=? WHERE id=?");
            $stmt->bind_param("ssi", $nuevo_nombre, $nuevo_correo, $user_id);
        }
        if ($stmt->execute()) {
            $edit_success = "Perfil actualizado correctamente.";
            // Actualizar sesión
            $_SESSION['nombre_usuario'] = $nuevo_nombre;
            $_SESSION['correo'] = $nuevo_correo;
            $nombre_usuario = $nuevo_nombre;
            $correo_usuario = $nuevo_correo;
        } else {
            $edit_error = "Error al actualizar el perfil: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo htmlspecialchars($pageTitle); ?></title>
<link rel="stylesheet" href="/css/estilos_generales.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<link rel="icon" type="image/png" href="/img/logo.png">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
.profile-card {
    max-width: 500px;
    margin: 50px auto;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    background-color: #fff;
    text-align: center;
}
.profile-card .profile-avatar {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background-color: var(--primary-color);
    color: white;
    display: flex;
    justify-content: center;
    align-items: center;
    font-weight: bold;
    font-size: 4em;
    margin: 0 auto 20px auto;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}
.profile-card h2 {
    color: var(--secondary-color);
    margin-bottom: 20px;
}
.profile-info p {
    font-size: 1.1em;
    margin-bottom: 10px;
    color: var(--text-dark);
}
.profile-info p strong {
    color: var(--primary-color);
}
.profile-card .btn {
    margin-top: 20px;
}
</style>
</head>
<body>
<header class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
<div class="container-fluid container">
    <a class="navbar-brand logo" href="#">Confia+ Director</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
            <li class="nav-item">
                <a class="nav-link" href="vista_director.php">Volver</a>
            </li>
            <li class="nav-item dropdown profile-dropdown">
                <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="profile-icon me-2">
                        <?php echo strtoupper(substr($_SESSION['nombre_usuario'], 0, 1)); ?>
                    </div>
                    <span><?php echo htmlspecialchars($_SESSION['nombre_usuario']); ?></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                    <li><a class="dropdown-item" href="vista_perfil_director.php"><i class="fas fa-user-circle me-2"></i>Mis Datos</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="/confia.php"><i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión</a></li>
                </ul>
            </li>
        </ul>
    </div>
</div>
</header>

<div class="container">
<div class="profile-card">
    <div class="profile-avatar">
        <?php echo strtoupper(substr($nombre_usuario, 0, 1)); ?>
    </div>
    <h2><?php echo htmlspecialchars($nombre_usuario); ?></h2>
    <div class="profile-info">
        <p><strong>Correo Electrónico:</strong> <?php echo htmlspecialchars($correo_usuario); ?></p>
        <p><strong>Contraseña:</strong> ********</p>
        <p><strong>Rol:</strong> Director</p>
    </div>
    <?php if ($edit_success): ?>
        <div class="alert alert-success"><?php echo $edit_success; ?></div>
    <?php elseif ($edit_error): ?>
        <div class="alert alert-danger"><?php echo $edit_error; ?></div>
    <?php endif; ?>
    <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editProfileModal">Editar Perfil</a>
</div>
</div>

<!-- Modal Editar Perfil -->
<div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
<div class="modal-dialog">
<div class="modal-content">
<form method="post" autocomplete="off">
    <div class="modal-header">
    <h5 class="modal-title" id="editProfileModalLabel">Editar Perfil</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
    </div>
    <div class="modal-body">
    <div class="mb-3">
        <label for="edit_nombre" class="form-label">Nombre de Usuario</label>
        <input type="text" class="form-control" id="edit_nombre" name="edit_nombre" value="<?php echo htmlspecialchars($nombre_usuario); ?>" required>
    </div>
    <div class="mb-3">
        <label for="edit_correo" class="form-label">Correo Electrónico</label>
        <input type="email" class="form-control" id="edit_correo" name="edit_correo" value="<?php echo htmlspecialchars($correo_usuario); ?>" required>
    </div>
    <div class="mb-3">
        <label for="edit_contrasena" class="form-label">Nueva Contraseña</label>
        <input type="password" class="form-control" id="edit_contrasena" name="edit_contrasena" placeholder="Dejar vacío para no cambiar">
    </div>
    <div class="mb-3">
        <label class="form-label">Rol</label>
        <input type="text" class="form-control" value="Director" disabled>
    </div>
    </div>
    <div class="modal-footer">
    <button type="submit" name="edit_profile" class="btn btn-primary">Guardar Cambios</button>
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
    </div>
</form>
</div>
</div>
</div>

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>AOS.init();</script>
<script src="/js/main.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
require_once '../_footer.php';
$conn->close();
?>
