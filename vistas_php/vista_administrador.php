<?php
// vistas_php/vista_administrador.php
require_once '../auth_middleware.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
require_once '../db_conect.php'; // Asegúrate de que este path sea correcto

// Asegúrate de que la sesión esté iniciada para acceder a $_SESSION
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Depuración: Estado de la conexión al inicio de vista_administrador.php
echo '<script>console.log("PHP DEBUG: Conexión abierta en vista_administrador.php al inicio. Estado: ' . ($conn && $conn->ping() ? 'Activa' : 'Inactiva/Cerrada') . '");</script>';


if ($conn->connect_error) {
    die("Error de conexión a la base de datos: " . $conn->connect_error);
}

// --- CÓDIGO PARA OBTENER LA URL BASE DE FORMA DINÁMICA ---
// Esta función SÓLO debe estar aquí y en ningún otro archivo.
function getBaseUrl() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'];
    // Para tu estructura, si vistas_php está en la raíz de htdocs, la base debe ser la raíz del dominio
    return $protocol . $host . '/'; 
}

define('BASE_URL', getBaseUrl());

// Si ya no necesitas ver esta depuración de BASE_URL, puedes comentarla o eliminarla.
// echo '<script>console.log("BASE_URL calculada: ' . BASE_URL . '");</script>';
// -------------------------------------------------------------------

// Solo administradores pueden acceder
protect_page(['administrador']);

$pageTitle = "Panel de Administración";

// Iniciar buffering de salida para capturar los mensajes
ob_start();

$console_messages = []; // Array para almacenar mensajes de consola

// Verificación y creación de tabla/columna si faltan (usuarios)
try {
    $result = $conn->query("SHOW TABLES LIKE 'usuarios'");
    if ($result->num_rows === 0) {
        $console_messages[] = "⚠️ La tabla usuarios no existe. Creándola…";
        $sql_create_table = "
            CREATE TABLE usuarios (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nombre_usuario VARCHAR(255) NOT NULL,
                correo VARCHAR(255) NOT NULL UNIQUE,
                passwd VARCHAR(255) NOT NULL,
                rol ENUM('usuario','director','administrador') DEFAULT 'usuario',
                fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";
        $conn->query($sql_create_table);
        $console_messages[] = "✅ Tabla usuarios creada correctamente.";
    } else {
        $console_messages[] = "✅ Tabla usuarios ya existe.";
    }
    $result->free(); // <-- AGREGA ESTA LÍNEA

    $result = $conn->query("SHOW COLUMNS FROM usuarios LIKE 'fecha_registro'");
    if ($result->num_rows === 0) {
        $console_messages[] = "⚠️ La columna fecha_registro no existe. Creándola…";
        $sql_add_column = "
            ALTER TABLE usuarios
            ADD COLUMN fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP;
        ";
        $conn->query($sql_add_column);

        $sql_update = "
            UPDATE usuarios
            SET fecha_registro = NOW()
            WHERE fecha_registro IS NULL;
        ";
        $conn->query($sql_update);

        $console_messages[] = "✅ Columna fecha_registro agregada y registros actualizados.";
    } else {
        $console_messages[] = "✅ Columna fecha_registro ya existe.";
    }
    $result->free(); // <-- AGREGA ESTA LÍNEA

} catch (Exception $e) {
    // Es mejor usar die() en la configuración de desarrollo si un error de DB es crítico
    die("Error en la verificación/creación de la tabla o columna 'usuarios': " . $e->getMessage());
}

// Verificación y creación de tabla si falta (instituciones)
// Y asegurar que las columnas 'region', 'ciudad', 'contacto_email' existan
try {
    $result_check_table = $conn->query("SHOW TABLES LIKE 'instituciones'");
    if ($result_check_table->num_rows === 0) {
        $console_messages[] = "⚠️ La tabla 'instituciones' no existe. Creándola…";
        $sql_create_instituciones_table = "
            CREATE TABLE instituciones (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nombre_institucion VARCHAR(255) NOT NULL UNIQUE,
                region VARCHAR(100),
                ciudad VARCHAR(100),
                contacto_email VARCHAR(255),
                fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";
        $conn->query($sql_create_instituciones_table);
        $console_messages[] = "✅ Tabla 'instituciones' creada correctamente con columnas 'region', 'ciudad', 'contacto_email'.";
    } else {
        $console_messages[] = "✅ Tabla 'instituciones' ya existe.";
        
        // Verificar y añadir columnas si la tabla ya existía sin ellas
        $columns_to_check = ['region' => 'VARCHAR(100)', 'ciudad' => 'VARCHAR(100)', 'contacto_email' => 'VARCHAR(255)'];
        foreach ($columns_to_check as $col_name => $col_type) {
            $result_check_column = $conn->query("SHOW COLUMNS FROM instituciones LIKE '$col_name'");
            if ($result_check_column->num_rows === 0) {
                $console_messages[] = "⚠️ La columna '$col_name' no existe en 'instituciones'. Añadiéndola…";
                $sql_add_column = "ALTER TABLE instituciones ADD COLUMN $col_name $col_type;";
                $conn->query($sql_add_column);
                $console_messages[] = "✅ Columna '$col_name' añadida a 'instituciones'.";
            } else {
                $console_messages[] = "✅ Columna '$col_name' ya existe en 'instituciones'.";
            }
            $result_check_column->free(); // <-- AGREGA ESTA LÍNEA
        }
    }
    $result_check_table->free(); // <-- AGREGA ESTA LÍNEA
} catch (Exception $e) {
    die("Error en la verificación/creación de la tabla 'instituciones' o sus columnas: " . $e->getMessage());
}

// Lógica para estadísticas
$total_users = 0;
$new_users_today = 0;
$total_directors = 0;
$total_instituciones = 0;

try {
    $stmt = $conn->prepare("SELECT COUNT(id) AS total FROM usuarios");
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($total_users);
    $stmt->fetch();
    $stmt->close();

    $stmt = $conn->prepare("SELECT COUNT(id) AS total FROM usuarios WHERE DATE(fecha_registro) = CURDATE()");
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($new_users_today);
    $stmt->fetch();
    $stmt->close();

    $stmt = $conn->prepare("SELECT COUNT(id) AS total FROM usuarios WHERE rol = 'director'");
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($total_directors);
    $stmt->fetch();
    $stmt->close();

    $stmt = $conn->prepare("SELECT COUNT(id) AS total FROM instituciones");
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($total_instituciones);
    $stmt->fetch();
    $stmt->close();

    // Obtener la lista de usuarios
    $users_list = [];
    $stmt = $conn->prepare("SELECT id, nombre_usuario, correo, rol, fecha_registro FROM usuarios ORDER BY id DESC");
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $users_list[] = $row;
    }
    $stmt->close();

    // Obtener la lista de directores (usuarios con rol 'director')
    $directors_list = [];
    $stmt = $conn->prepare("SELECT id, nombre_usuario, correo, fecha_registro FROM usuarios WHERE rol = 'director' ORDER BY id DESC");
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $directors_list[] = $row;
    }
    $stmt->close();

    // Obtener la lista de instituciones
    $instituciones_list = [];
    // Seleccionamos las columnas que realmente existen en tu tabla instituciones
    $stmt = $conn->prepare("SELECT id, nombre_institucion, region, ciudad, contacto_email, fecha_registro FROM instituciones ORDER BY id DESC");
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $instituciones_list[] = $row;
    }
    $stmt->close();

} catch (Exception $e) {
    die("Error en la base de datos (estadísticas/listas): " . $e->getMessage());
}

// Capturar el contenido del buffer y limpiar
$header_output = ob_get_clean(); // Captura y limpia el buffer.


?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <link rel="stylesheet" href="../../css/estilos_generales.css">
    <link rel="stylesheet" href="../../css/login-page.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="icon" type="image/png" href="../img/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <style>
        /* Estilos para el logo en la barra de navegación */
        .navbar-brand.logo img {
            height: 40px; /* Ajusta este valor para el tamaño deseado del logo */
            width: auto; /* Mantener la proporción de la imagen */
            margin-right: 10px; /* Espacio a la derecha del logo si hay texto al lado */
            vertical-align: middle; /* Para alinear con el texto si lo hay */
        }
        /* Opcional: Si quieres que el logo sea aún más pequeño en pantallas muy pequeñas */
        @media (max-width: 768px) {
            .navbar-brand.logo img {
                height: 30px; /* Un tamaño más pequeño para móviles */
            }
        }
        /* Estilos para el Dropdown de Perfil */
        .profile-dropdown .profile-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--primary-color, #28a745); /* Usar tu color primario o un verde por defecto */
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            font-weight: bold;
            font-size: 1.2em;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .profile-dropdown .profile-icon:hover {
            background-color: var(--primary-dark, #218838); /* Un tono más oscuro del primario */
        }
        .profile-dropdown .dropdown-menu {
            min-width: 200px;
        }
        .profile-dropdown .dropdown-item-text {
            white-space: normal;
        }
        /* Custom colors for sections for better visual distinction */
        .section-heading {
            color: #007bff; /* Bootstrap primary blue */
            margin-bottom: 1.5rem;
            font-weight: 600;
        }
        /* Ajustar el logo y el brand */
        .navbar-brand.logo {
            font-weight: bold;
            color: #007bff;
        }
    </style>
</head>
<body>
<header class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
    <div class="container-fluid container">
        <a class="navbar-brand logo" href="../index.php">
            <img src="../img/logo.png" alt="logo">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="#users-section">Usuarios</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#directors-section">Directores</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#institutions-section">Instituciones</a>
                </li>
                <li class="nav-item dropdown profile-dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdown"
                       role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="profile-icon me-2"
                             style="width: 40px; height: 40px; background: #4caf50; color: #fff; border-radius: 50%;
                             display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 1.2em;">
                            <?php echo strtoupper(substr($_SESSION['nombre_usuario'], 0, 1)); ?>
                        </div>
                        <span><?php echo htmlspecialchars($_SESSION['nombre_usuario']); ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="navbarDropdown">
                        <li>
                            <a class="dropdown-item" href="vista_perfil_admin.php">
                                <i class="fas fa-user-circle me-2"></i>Mis Datos
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger fw-bold" href="../logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</header>

<!-- Incluye Bootstrap JS (solo una vez, antes de </body>) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


    <div class="container mt-5">
        <div class="row">
            <div class="col-12">
                <h1 class="text-center text-primary-custom">Panel de Administración</h1>
                <p class="lead text-center">Gestión y estadísticas de la plataforma.</p>
            </div>
        </div>

        <hr>

        <div class="row my-4">
            <div class="col-12">
                <h2 class="text-secondary-custom section-heading">Estadísticas Rápidas</h2>
                <div class="row text-center">
                    <div class="col-md-3 mb-3" data-aos="fade-up">
                        <div class="card p-3 shadow-sm feature-item">
                            <h5>Total de Usuarios</h5>
                            <p class="h3 text-primary-custom"><?php echo $total_users; ?></p>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3" data-aos="fade-up" data-aos-delay="100">
                        <div class="card p-3 shadow-sm feature-item">
                            <h5>Nuevos Usuarios Hoy</h5>
                            <p class="h3 text-success"><?php echo $new_users_today; ?></p>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3" data-aos="fade-up" data-aos-delay="200">
                        <div class="card p-3 shadow-sm feature-item">
                            <h5>Cuentas de Directores</h5>
                            <p class="h3 text-info"><?php echo $total_directors; ?></p>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3" data-aos="fade-up" data-aos-delay="300">
                        <div class="card p-3 shadow-sm feature-item">
                            <h5>Instituciones (CL)</h5>
                            <p class="h3 text-warning"><?php echo $total_instituciones; ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <hr>

        <div class="row my-4" id="users-section">
            <div class="col-12">
                <h2 class="text-secondary-custom section-heading">Gestión de Usuarios y Roles</h2>
                <button class="btn btn-success mb-3 btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="fas fa-plus"></i> Añadir Nuevo Usuario
                </button>

                <div class="table-responsive">
                    <table class="table table-striped table-hover shadow-sm">
                        <thead class="bg-primary-custom text-white">
                            <tr>
                                <th>ID</th>
                                <th>Nombre Usuario</th>
                                <th>Correo</th>
                                <th>Rol</th>
                                <th>Fecha Registro</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="usersTableBody">
                            <?php if (!empty($users_list)): ?>
                                <?php foreach ($users_list as $user): ?>
                                    <tr data-id="<?php echo $user['id']; ?>">
                                        <td><?php echo $user['id']; ?></td>
                                        <td data-field="nombre_usuario"><?php echo htmlspecialchars($user['nombre_usuario']); ?></td>
                                        <td data-field="correo"><?php echo htmlspecialchars($user['correo']); ?></td>
                                        <td data-field="rol"><?php echo htmlspecialchars($user['rol']); ?></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($user['fecha_registro'])); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-info edit-user-btn"
                                                    data-bs-toggle="modal" data-bs-target="#editUserModal"
                                                    data-id="<?php echo $user['id']; ?>"
                                                    data-username="<?php echo htmlspecialchars($user['nombre_usuario']); ?>"
                                                    data-email="<?php echo htmlspecialchars($user['correo']); ?>"
                                                    data-rol="<?php echo htmlspecialchars($user['rol']); ?>">
                                                    <i class="fas fa-edit"></i> Editar
                                            </button>
                                            <button class="btn btn-sm btn-danger delete-user-btn" data-id="<?php echo $user['id']; ?>">
                                                    <i class="fas fa-trash-alt"></i> Eliminar
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">No hay usuarios registrados.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <hr>

        <div class="row my-4" id="directors-section">
            <div class="col-12">
                <h2 class="text-secondary-custom section-heading">Gestión de Cuentas de Directores</h2>
                <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addDirectorModal">
                    <i class="fas fa-user-plus"></i> Añadir Nuevo Director
                </button>

                <div class="table-responsive">
                    <table class="table table-striped table-hover shadow-sm">
                        <thead class="bg-primary-custom text-white">
                            <tr>
                                <th>ID</th>
                                <th>Nombre Usuario</th>
                                <th>Correo</th>
                                <th>Fecha Registro</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="directorsTableBody">
                            <?php if (!empty($directors_list)): ?>
                                <?php foreach ($directors_list as $director): ?>
                                    <tr data-id="<?php echo $director['id']; ?>">
                                        <td><?php echo $director['id']; ?></td>
                                        <td data-field="nombre_usuario"><?php echo htmlspecialchars($director['nombre_usuario']); ?></td>
                                        <td data-field="correo"><?php echo htmlspecialchars($director['correo']); ?></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($director['fecha_registro'])); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-info edit-director-btn"
                                                    data-bs-toggle="modal" data-bs-target="#editDirectorModal"
                                                    data-id="<?php echo $director['id']; ?>"
                                                    data-username="<?php echo htmlspecialchars($director['nombre_usuario']); ?>"
                                                    data-email="<?php echo htmlspecialchars($director['correo']); ?>">
                                                    <i class="fas fa-edit"></i> Editar
                                            </button>
                                            <button class="btn btn-sm btn-danger delete-director-btn" data-id="<?php echo $director['id']; ?>">
                                                    <i class="fas fa-trash-alt"></i> Eliminar
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center">No hay directores registrados.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <hr>

        <div class="row my-4" id="institutions-section">
            <div class="col-12">
                <h2 class="text-secondary-custom section-heading">Gestión de Instituciones</h2>
                <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addInstitutionModal">
                    <i class="fas fa-building"></i> Añadir Nueva Institución
                </button>

                <div class="table-responsive">
                    <table class="table table-striped table-hover shadow-sm">
                        <thead class="bg-primary-custom text-white">
                            <tr>
                                <th>ID</th>
                                <th>Nombre Institución</th>
                                <th>Región</th>
                                <th>Ciudad</th>
                                <th>Email Contacto</th>
                                <th>Fecha Registro</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="institutionsTableBody">
                            <?php if (!empty($instituciones_list)): ?>
                                <?php foreach ($instituciones_list as $institucion): ?>
                                    <tr data-id="<?php echo $institucion['id']; ?>">
                                        <td><?php echo $institucion['id']; ?></td>
                                        <td data-field="nombre_institucion"><?php echo htmlspecialchars($institucion['nombre_institucion']); ?></td>
                                        <td data-field="region"><?php echo htmlspecialchars($institucion['region']); ?></td>
                                        <td data-field="ciudad"><?php echo htmlspecialchars($institucion['ciudad']); ?></td>
                                        <td data-field="contacto_email"><?php echo htmlspecialchars($institucion['contacto_email']); ?></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($institucion['fecha_registro'])); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-info edit-institution-btn"
                                                    data-bs-toggle="modal" data-bs-target="#editInstitutionModal"
                                                    data-id="<?php echo $institucion['id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($institucion['nombre_institucion']); ?>"
                                                    data-region="<?php echo htmlspecialchars($institucion['region']); ?>"
                                                    data-ciudad="<?php echo htmlspecialchars($institucion['ciudad']); ?>"
                                                    data-contactemail="<?php echo htmlspecialchars($institucion['contacto_email']); ?>">
                                                    <i class="fas fa-edit"></i> Editar
                                            </button>
                                            <button class="btn btn-sm btn-danger delete-institution-btn" data-id="<?php echo $institucion['id']; ?>">
                                                    <i class="fas fa-trash-alt"></i> Eliminar
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">No hay instituciones registradas.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addUserModalLabel">Añadir Nuevo Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addUserForm">
                        <div class="mb-3">
                            <label for="username" class="form-label">Nombre de Usuario</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Correo Electrónico</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Contraseña</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="rol" class="form-label">Rol</label>
                            <select class="form-select" id="rol" name="rol" required>
                                <option value="usuario">Usuario</option>
                                <option value="director">Director</option>
                                <option value="administrador">Administrador</option>
                            </select>
                        </div>
                        <div id="addFormMessage" class="alert d-none"></div>
                        <button type="submit" class="btn btn-primary">Guardar Usuario</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editUserModalLabel">Editar Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editUserForm">
                        <input type="hidden" id="editUserId" name="id">
                        <div class="mb-3">
                            <label for="editUsername" class="form-label">Nombre de Usuario</label>
                            <input type="text" class="form-control" id="editUsername" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="editEmail" class="form-label">Correo Electrónico</label>
                            <input type="email" class="form-control" id="editEmail" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="editRole" class="form-label">Rol</label>
                            <select class="form-select" id="editRole" name="rol" required>
                                <option value="usuario">Usuario</option>
                                <option value="director">Director</option>
                                <option value="administrador">Administrador</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Actualizar Usuario</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addDirectorModal" tabindex="-1" aria-labelledby="addDirectorModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addDirectorModalLabel">Añadir Nuevo Director</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addDirectorForm">
                        <div class="mb-3">
                            <label for="directorUsername" class="form-label">Nombre de Usuario</label>
                            <input type="text" class="form-control" id="directorUsername" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="directorEmail" class="form-label">Correo Electrónico</label>
                            <input type="email" class="form-control" id="directorEmail" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="directorPassword" class="form-label">Contraseña</label>
                            <input type="password" class="form-control" id="directorPassword" name="password" required>
                        </div>
                        <div id="addDirectorMessage" class="alert d-none"></div>
                        <button type="submit" class="btn btn-primary">Guardar Director</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editDirectorModal" tabindex="-1" aria-labelledby="editDirectorModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editDirectorModalLabel">Editar Director</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editDirectorForm">
                        <input type="hidden" id="editDirectorId" name="id">
                        <div class="mb-3">
                            <label for="editDirectorUsername" class="form-label">Nombre de Usuario</label>
                            <input type="text" class="form-control" id="editDirectorUsername" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="editDirectorEmail" class="form-label">Correo Electrónico</label>
                            <input type="email" class="form-control" id="editDirectorEmail" name="email" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Actualizar Director</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addInstitutionModal" tabindex="-1" aria-labelledby="addInstitutionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addInstitutionModalLabel">Añadir Nueva Institución</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addInstitutionForm">
                        <div class="mb-3">
                            <label for="institutionName" class="form-label">Nombre de la Institución</label>
                            <input type="text" class="form-control" id="institutionName" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="institutionRegion" class="form-label">Región</label>
                            <input type="text" class="form-control" id="institutionRegion" name="region">
                        </div>
                        <div class="mb-3">
                            <label for="institutionCiudad" class="form-label">Ciudad</label>
                            <input type="text" class="form-control" id="institutionCiudad" name="ciudad">
                        </div>
                        <div class="mb-3">
                            <label for="institutionContactEmail" class="form-label">Email de Contacto</label>
                            <input type="email" class="form-control" id="institutionContactEmail" name="contact_email">
                        </div>
                        <button type="submit" class="btn btn-primary">Guardar Institución</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editInstitutionModal" tabindex="-1" aria-labelledby="editInstitutionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editInstitutionModalLabel">Editar Institución</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editInstitutionForm">
                        <input type="hidden" id="editInstitutionId" name="id">
                        <div class="mb-3">
                            <label for="editInstitutionName" class="form-label">Nombre de la Institución</label>
                            <input type="text" class="form-control" id="editInstitutionName" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="editInstitutionRegion" class="form-label">Región</label>
                            <input type="text" class="form-control" id="editInstitutionRegion" name="region">
                        </div>
                        <div class="mb-3">
                            <label for="editInstitutionCiudad" class="form-label">Ciudad</label>
                            <input type="text" class="form-control" id="editInstitutionCiudad" name="ciudad">
                        </div>
                        <div class="mb-3">
                            <label for="editInstitutionContactEmail" class="form-label">Email de Contacto</label>
                            <input type="email" class="form-control" id="editInstitutionContactEmail" name="contact_email">
                        </div>
                        <button type="submit" class="btn btn-primary">Actualizar Institución</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init();
        // Mostrar los mensajes de verificación en la consola
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/main.js"></script>
    <script src="../js/admin_crud.js"></script>
</body>
</html>

<?php
// Incluir el footer al final
require_once '../_footer.php'; 

// Esta es la línea 747 que te da error
$conn->close(); 

echo '<script>console.log("PHP DEBUG: Conexión cerrada en vista_administrador.php.");</script>';
?>