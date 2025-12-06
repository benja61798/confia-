<?php
// api_admin.php
session_start();
header('Content-Type: application/json');
require_once 'db_conect.php';

// --- Manejo de errores para mostrar JSON en caso de error PHP ---
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        "success" => false,
        "message" => "PHP Error: $errstr en $errfile:$errline"
    ]);
    exit;
});
set_exception_handler(function($exception) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        "success" => false,
        "message" => "Excepción: " . $exception->getMessage()
    ]);
    exit;
});

// 1. Proteger la API: solo administradores pueden acceder
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'administrador') {
    echo json_encode(["success" => false, "message" => "Acceso no autorizado."]);
    exit();
}

// Obtener los datos enviados por POST
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['action'])) {
    echo json_encode(["success" => false, "message" => "Acción no especificada."]);
    exit();
}

$action = $data['action'];

switch ($action) {
    case 'add_user':
        handleAddUser($conn, $data);
        break;
    case 'edit_user':
        handleEditUser($conn, $data);
        break;
    case 'delete_user':
        handleDeleteUser($conn, $data);
        break;
    case 'add_director':
        handleAddDirector($conn, $data);
        break;
    case 'edit_director':
        handleEditDirector($conn, $data);
        break;
    case 'delete_director':
        handleDeleteDirector($conn, $data);
        break;
    case 'add_institution':
        handleAddInstitution($conn, $data);
        break;
    case 'edit_institution':
        handleEditInstitution($conn, $data);
        break;
    case 'delete_institution':
        handleDeleteInstitution($conn, $data);
        break;
    default:
        echo json_encode(["success" => false, "message" => "Acción inválida."]);
        break;
}

// --- Funciones para USUARIOS ---
function handleAddUser($conn, $data) {
    $username = $data['username'] ?? '';
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';
    $rol = $data['rol'] ?? 'usuario';

    if (empty($username) || empty($email) || empty($password)) {
        echo json_encode(["success" => false, "message" => "Todos los campos son obligatorios (excepto el rol por defecto)."]);
        return;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["success" => false, "message" => "Formato de correo electrónico inválido."]);
        return;
    }
    if (!in_array($rol, ['usuario', 'director', 'administrador'])) {
        echo json_encode(["success" => false, "message" => "Rol inválido."]);
        return;
    }

    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE nombre_usuario = ? OR correo = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $result->free();
        $stmt->close();
        echo json_encode(["success" => false, "message" => "El nombre de usuario o correo electrónico ya están registrados."]);
        return;
    }
    $result->free();
    $stmt->close();

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO usuarios (nombre_usuario, correo, passwd, rol) VALUES (?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("ssss", $username, $email, $hashed_password, $rol);
        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Usuario añadido exitosamente."]);
        } else {
            echo json_encode(["success" => false, "message" => "Error al añadir usuario: " . $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(["success" => false, "message" => "Error de preparación de la consulta de inserción: " . $conn->error]);
    }
}

function handleEditUser($conn, $data) {
    $id = $data['id'] ?? null;
    $username = $data['username'] ?? '';
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';
    $rol = $data['rol'] ?? '';

    if (empty($id) || empty($username) || empty($email) || empty($rol)) {
        echo json_encode(["success" => false, "message" => "ID, nombre de usuario, correo y rol son obligatorios."]);
        return;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["success" => false, "message" => "Formato de correo electrónico inválido."]);
        return;
    }
    if (!in_array($rol, ['usuario', 'director', 'administrador'])) {
        echo json_encode(["success" => false, "message" => "Rol inválido."]);
        return;
    }

    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE (nombre_usuario = ? OR correo = ?) AND id != ?");
    if (!$stmt) {
        echo json_encode(["success" => false, "message" => "Error de preparación de la consulta de verificación de existencia (edit): " . $conn->error]);
        return;
    }
    $stmt->bind_param("ssi", $username, $email, $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $result->free();
        $stmt->close();
        echo json_encode(["success" => false, "message" => "El nombre de usuario o correo electrónico ya están registrados por otro usuario."]);
        return;
    }
    $result->free();
    $stmt->close();

    $query = "UPDATE usuarios SET nombre_usuario = ?, correo = ?, rol = ?";
    $params = [$username, $email, $rol];
    $types = "sssi";
    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $query .= ", passwd = ?";
        $params[] = $hashed_password;
        $types = "ssss";
    }
    $query .= " WHERE id = ?";
    $params[] = $id;

    $stmt = $conn->prepare($query);
    if ($stmt) {
        $bind_names = [];
        $bind_names[] = $types;
        foreach ($params as $key => $value) {
            $bind_names[] = &$params[$key];
        }
        call_user_func_array([$stmt, 'bind_param'], $bind_names);

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Usuario actualizado exitosamente."]);
        } else {
            echo json_encode(["success" => false, "message" => "Error al actualizar usuario: " . $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(["success" => false, "message" => "Error de preparación de la consulta de actualización: " . $conn->error]);
    }
}

function handleDeleteUser($conn, $data) {
    $id = $data['id'] ?? null;

    if (empty($id)) {
        echo json_encode(["success" => false, "message" => "ID de usuario no especificado."]);
        return;
    }

    if ($id == $_SESSION['user_id']) {
        echo json_encode(["success" => false, "message" => "No puedes eliminar tu propia cuenta de administrador."]);
        return;
    }

    $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo json_encode(["success" => true, "message" => "Usuario eliminado exitosamente."]);
            } else {
                echo json_encode(["success" => false, "message" => "Usuario no encontrado."]);
            }
        } else {
            echo json_encode(["success" => false, "message" => "Error al eliminar usuario: " . $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(["success" => false, "message" => "Error de preparación de la consulta de eliminación: " . $conn->error]);
    }
}

// --- Funciones para DIRECTORES ---
function handleAddDirector($conn, $data) {
    $username = $data['username'] ?? '';
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';
    $rol = 'director';

    if (empty($username) || empty($email) || empty($password)) {
        echo json_encode(["success" => false, "message" => "Todos los campos de director son obligatorios."]);
        return;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["success" => false, "message" => "Formato de correo electrónico inválido para director."]);
        return;
    }

    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE nombre_usuario = ? OR correo = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $result->free();
        $stmt->close();
        echo json_encode(["success" => false, "message" => "El nombre de usuario o correo electrónico ya están registrados."]);
        return;
    }
    $result->free();
    $stmt->close();

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO usuarios (nombre_usuario, correo, passwd, rol) VALUES (?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("ssss", $username, $email, $hashed_password, $rol);
        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Director añadido exitosamente."]);
        } else {
            echo json_encode(["success" => false, "message" => "Error al añadir director: " . $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(["success" => false, "message" => "Error de preparación de la consulta de inserción de director: " . $conn->error]);
    }
}

function handleEditDirector($conn, $data) {
    $id = $data['id'] ?? null;
    $username = $data['username'] ?? '';
    $email = $data['email'] ?? '';

    if (empty($id) || empty($username) || empty($email)) {
        echo json_encode(["success" => false, "message" => "ID, nombre de usuario y correo son obligatorios para el director."]);
        return;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["success" => false, "message" => "Formato de correo electrónico inválido para director."]);
        return;
    }

    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE (nombre_usuario = ? OR correo = ?) AND id != ?");
    if (!$stmt) {
        echo json_encode(["success" => false, "message" => "Error de preparación de la consulta de verificación de existencia (edit director): " . $conn->error]);
        return;
    }
    $stmt->bind_param("ssi", $username, $email, $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $result->free();
        $stmt->close();
        echo json_encode(["success" => false, "message" => "El nombre de usuario o correo electrónico ya están registrados por otro usuario."]);
        return;
    }
    $result->free();
    $stmt->close();

    $query = "UPDATE usuarios SET nombre_usuario = ?, correo = ? WHERE id = ? AND rol = 'director'";
    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param("ssi", $username, $email, $id);
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo json_encode(["success" => true, "message" => "Director actualizado exitosamente."]);
            } else {
                echo json_encode(["success" => false, "message" => "Director no encontrado o no se realizaron cambios."]);
            }
        } else {
            echo json_encode(["success" => false, "message" => "Error al actualizar director: " . $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(["success" => false, "message" => "Error de preparación de la consulta de actualización de director: " . $conn->error]);
    }
}

function handleDeleteDirector($conn, $data) {
    $id = $data['id'] ?? null;

    if (empty($id)) {
        echo json_encode(["success" => false, "message" => "ID de director no especificado."]);
        return;
    }

    $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ? AND rol = 'director'");
    if ($stmt) {
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo json_encode(["success" => true, "message" => "Director eliminado exitosamente."]);
            } else {
                echo json_encode(["success" => false, "message" => "Director no encontrado."]);
            }
        } else {
            echo json_encode(["success" => false, "message" => "Error al eliminar director: " . $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(["success" => false, "message" => "Error de preparación de la consulta de eliminación de director: " . $conn->error]);
    }
}

// --- Funciones para INSTITUCIONES ---
function handleAddInstitution($conn, $data) {
    $name = $data['name'] ?? '';
    $region = $data['region'] ?? '';
    $ciudad = $data['ciudad'] ?? '';
    $contact_email = $data['contact_email'] ?? '';

    if (empty($name)) {
        echo json_encode(["success" => false, "message" => "El nombre de la institución es obligatorio."]);
        return;
    }
    if (!empty($contact_email) && !filter_var($contact_email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["success" => false, "message" => "Formato de correo electrónico de contacto inválido."]);
        return;
    }

    $stmt = $conn->prepare("SELECT id FROM instituciones WHERE nombre_institucion = ?");
    if (!$stmt) {
        echo json_encode(["success" => false, "message" => "Error de preparación de la consulta de verificación de institución existente: " . $conn->error]);
        return;
    }
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $result->free();
        $stmt->close();
        echo json_encode(["success" => false, "message" => "Ya existe una institución con ese nombre."]);
        return;
    }
    $result->free();
    $stmt->close();

    $stmt = $conn->prepare("INSERT INTO instituciones (nombre_institucion, region, ciudad, contacto_email) VALUES (?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("ssss", $name, $region, $ciudad, $contact_email);
        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Institución añadida exitosamente."]);
        } else {
            echo json_encode(["success" => false, "message" => "Error al añadir institución: " . $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(["success" => false, "message" => "Error de preparación de la consulta de inserción de institución: " . $conn->error]);
    }
}

function handleEditInstitution($conn, $data) {
    $id = $data['id'] ?? null;
    $name = $data['name'] ?? '';
    $region = $data['region'] ?? '';
    $ciudad = $data['ciudad'] ?? '';
    $contact_email = $data['contact_email'] ?? '';

    if (empty($id) || empty($name)) {
        echo json_encode(["success" => false, "message" => "ID y nombre de institución son obligatorios."]);
        return;
    }
    if (!empty($contact_email) && !filter_var($contact_email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["success" => false, "message" => "Formato de correo electrónico de contacto inválido."]);
        return;
    }

    $stmt = $conn->prepare("SELECT id FROM instituciones WHERE nombre_institucion = ? AND id != ?");
    if (!$stmt) {
        echo json_encode(["success" => false, "message" => "Error de preparación de la consulta de verificación de existencia (edit institution): " . $conn->error]);
        return;
    }
    $stmt->bind_param("si", $name, $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $result->free();
        $stmt->close();
        echo json_encode(["success" => false, "message" => "Ya existe otra institución con ese nombre."]);
        return;
    }
    $result->free();
    $stmt->close();

    $query = "UPDATE instituciones SET nombre_institucion = ?, region = ?, ciudad = ?, contacto_email = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param("ssssi", $name, $region, $ciudad, $contact_email, $id);
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo json_encode(["success" => true, "message" => "Institución actualizada exitosamente."]);
            } else {
                echo json_encode(["success" => false, "message" => "Institución no encontrada o no se realizaron cambios."]);
            }
        } else {
            echo json_encode(["success" => false, "message" => "Error al actualizar institución: " . $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(["success" => false, "message" => "Error de preparación de la consulta de actualización de institución: " . $conn->error]);
    }
}

function handleDeleteInstitution($conn, $data) {
    $id = $data['id'] ?? null;

    if (empty($id)) {
        echo json_encode(["success" => false, "message" => "ID de institución no especificado."]);
        return;
    }

    $stmt = $conn->prepare("DELETE FROM instituciones WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo json_encode(["success" => true, "message" => "Institución eliminada exitosamente."]);
            } else {
                echo json_encode(["success" => false, "message" => "Institución no encontrada."]);
            }
        } else {
            echo json_encode(["success" => false, "message" => "Error al eliminar institución: " . $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(["success" => false, "message" => "Error de preparación de la consulta de eliminación de institución: " . $conn->error]);
    }
}
?>