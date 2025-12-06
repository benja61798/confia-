<?php
// Habilitar la visualización de errores para depuración (desactivar en producción)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Iniciar la sesión PHP. ¡Importante que esté al principio del script!
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Establecer el tipo de contenido a JSON para la respuesta
header('Content-Type: application/json');

// Incluir el archivo de conexión a la base de datos
require_once 'db_conect.php';

// Verificar la conexión a la base de datos
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Error de conexión a la base de datos: " . $conn->connect_error]);
    exit();
}

// Obtener los datos enviados por POST como un objeto
$data = json_decode(file_get_contents("php://input"));

// Verificar si se especificó una acción
if (!isset($data->action)) {
    echo json_encode(["success" => false, "message" => "Acción no especificada."]);
    exit();
}

$action = $data->action;

// Manejar la acción correspondiente
switch ($action) {
    case 'register':
        handleRegister($conn, $data);
        break;
    case 'login':
        handleLogin($conn, $data);
        break;
    default:
        echo json_encode(["success" => false, "message" => "Acción inválida."]);
        break;
}

// Cerrar la conexión a la base de datos al finalizar el script
$conn->close();

/**
 * Maneja el proceso de registro de un nuevo usuario.
 * @param mysqli $conn Objeto de conexión a la base de datos.
 * @param object $data Objeto con los datos de registro (username, email, password).
 */
function handleRegister($conn, $data) {
    // Escapar los datos para prevenir inyección SQL
    $username = $conn->real_escape_string($data->username ?? '');
    $email = $conn->real_escape_string($data->email ?? '');
    $password = $conn->real_escape_string($data->password ?? '');

    // Validaciones de campos obligatorios
    if (empty($username) || empty($email) || empty($password)) {
        echo json_encode(["success" => false, "message" => "Todos los campos son obligatorios."]);
        return;
    }
    // Validar formato de correo electrónico
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["success" => false, "message" => "Formato de correo electrónico inválido."]);
        return;
    }

    // Hashear la contraseña antes de guardarla para seguridad
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Verificar si el nombre de usuario o correo ya existen en la base de datos
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE nombre_usuario = ? OR correo = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo json_encode(["success" => false, "message" => "El nombre de usuario o correo electrónico ya están registrados."]);
        $stmt->close();
        return;
    }
    $stmt->close();

    // Insertar el nuevo usuario en la base de datos con el rol por defecto
    $default_rol = 'usuario'; // Rol predeterminado para nuevos registros
    $stmt = $conn->prepare("INSERT INTO usuarios (nombre_usuario, correo, passwd, rol) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $email, $hashed_password, $default_rol);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Registro exitoso. Ahora puedes seleccionar tu rol."]);
    } else {
        // En caso de error en la inserción, devolver un mensaje descriptivo
        echo json_encode(["success" => false, "message" => "Error al registrar: " . $stmt->error]);
    }
    $stmt->close();
}

/**
 * Maneja el proceso de inicio de sesión de un usuario.
 * @param mysqli $conn Objeto de conexión a la base de datos.
 * @param object $data Objeto con los datos de inicio de sesión (identifier, password).
 */
function handleLogin($conn, $data) {
    // Escapar los datos para prevenir inyección SQL
    $identifier = $conn->real_escape_string($data->identifier ?? '');
    $password = $conn->real_escape_string($data->password ?? '');

    // Validaciones de campos obligatorios
    if (empty($identifier) || empty($password)) {
        echo json_encode(["success" => false, "message" => "Usuario/Email y contraseña son obligatorios."]);
        return;
    }

    // Buscar usuario por nombre de usuario o correo electrónico, y obtener su ID, contraseña hasheada, nombre de usuario y rol
    $stmt = $conn->prepare("SELECT id, passwd, nombre_usuario, rol FROM usuarios WHERE nombre_usuario = ? OR correo = ?");
    $stmt->bind_param("ss", $identifier, $identifier);
    $stmt->execute();
    $stmt->store_result(); // Almacenar el resultado para poder usar num_rows

    // Verificar si se encontró un usuario
    if ($stmt->num_rows == 1) {
        // Vincular las columnas del resultado a variables PHP
        $stmt->bind_result($user_id, $hashed_password, $db_username, $user_rol);
        $stmt->fetch(); // Obtener los valores

        // Verificar si la contraseña proporcionada coincide con la hasheada
        if (password_verify($password, $hashed_password)) {
            // ¡Inicio de sesión exitoso!
            // Iniciar la sesión si aún no está iniciada (redundante aquí si ya está arriba, pero seguro)
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }

            // Establecer las variables de sesión necesarias
            $_SESSION['user_id'] = $user_id;
            $_SESSION['nombre_usuario'] = $db_username; // <-- ¡Esta es la corrección clave!
            $_SESSION['rol'] = $user_rol;

            // Enviar respuesta JSON de éxito con datos del usuario
            echo json_encode(["success" => true, "message" => "Inicio de sesión exitoso.", "username" => $db_username, "rol" => $user_rol]);
        } else {
            // Contraseña incorrecta
            echo json_encode(["success" => false, "message" => "Contraseña incorrecta."]);
        }
    } else {
        // Usuario no encontrado
        echo json_encode(["success" => false, "message" => "Usuario no encontrado."]);
    }
    $stmt->close(); // Cerrar el statement
}