<?php
// test_php/guardar_resultado_test.php
session_start();
header('Content-Type: application/json');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// RUTA CORREGIDA: Ajusta esta ruta si db_conect.php está en la raíz o en otra ubicación
require_once '../db_conect.php'; 

// Verify DB connection
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Error de conexión a la base de datos."]);
    exit();
}

// 1. Verify user session
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Usuario no autenticado. Por favor, inicia sesión."]);
    $conn->close();
    exit();
}

$user_id = $_SESSION['user_id'];

// 2. Get data sent by client (JSON)
$input_data = file_get_contents("php://input");
$data = json_decode($input_data, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(["success" => false, "message" => "Datos JSON inválidos: " . json_last_error_msg()]);
    $conn->close();
    exit();
}

$test_id = $data['test_id'] ?? null;
$answers = $data['answers'] ?? []; // Receive individual answers
$test_name = $data['test_name'] ?? 'Test Desconocido'; 

// 3. Validate basic data
if (empty($test_id) || !filter_var($test_id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) {
    echo json_encode(["success" => false, "message" => "ID del test inválido."]);
    $conn->close();
    exit();
}

$test_id = intval($test_id);

// --- CRUCIAL LOGIC: Calculate score and result_text in the backend ---

$puntaje = 0;
$resultado_texto = "";

// Use a switch or if-else if for different tests to apply correct scoring logic
switch ($test_name) {
    case 'Test de Depresión':
        // PHQ-9 has 9 questions, all direct scoring (0-3)
        for ($i = 1; $i <= 9; $i++) {
            $q_name = "q" . $i;
            // Validate each answer is present and within expected range
            if (!isset($answers[$q_name]) || !is_numeric($answers[$q_name]) || $answers[$q_name] < 0 || $answers[$q_name] > 3) {
                echo json_encode(["success" => false, "message" => "Respuesta inválida o faltante para la pregunta " . $i . " del Test de Depresión."]);
                $conn->close();
                exit();
            }
            $puntaje += intval($answers[$q_name]);
        }
        // PHQ-9 interpretation (0-27)
        if ($puntaje >= 0 && $puntaje <= 4) {
            $resultado_texto = "Depresión mínima o ausente.";
        } elseif ($puntaje >= 5 && $puntaje <= 9) {
            $resultado_texto = "Depresión leve.";
        } elseif ($puntaje >= 10 && $puntaje <= 14) {
            $resultado_texto = "Depresión moderada.";
        } elseif ($puntaje >= 15 && $puntaje <= 19) {
            $resultado_texto = "Depresión moderadamente severa.";
        } else { // $puntaje >= 20 && $puntaje <= 27
            $resultado_texto = "Depresión severa. Se recomienda encarecidamente buscar apoyo profesional.";
        }
        break;

    case 'Test de Ansiedad':
        // GAD-7 has 7 questions, all direct scoring (0-3)
        for ($i = 1; $i <= 7; $i++) {
            $q_name = "q" . $i;
            if (!isset($answers[$q_name]) || !is_numeric($answers[$q_name]) || $answers[$q_name] < 0 || $answers[$q_name] > 3) {
                echo json_encode(["success" => false, "message" => "Respuesta inválida o faltante para la pregunta " . $i . " del Test de Ansiedad."]);
                $conn->close();
                exit();
            }
            $puntaje += intval($answers[$q_name]);
        }
        // GAD-7 interpretation (0-21)
        if ($puntaje >= 0 && $puntaje <= 4) {
            $resultado_texto = "Ansiedad mínima o ausente.";
        } elseif ($puntaje >= 5 && $puntaje <= 9) {
            $resultado_texto = "Ansiedad leve.";
        } elseif ($puntaje >= 10 && $puntaje <= 14) {
            $resultado_texto = "Ansiedad moderada.";
        } else { // $puntaje >= 15 && $puntaje <= 21
            $resultado_texto = "Ansiedad severa. Se recomienda encarecidamente buscar apoyo profesional.";
        }
        break;

    case 'Test de Estrés':
        
        for ($i = 1; $i <= 10; $i++) {
            $q_name = "q" . $i;
            if (!isset($answers[$q_name]) || !is_numeric($answers[$q_name]) || $answers[$q_name] < 0 || $answers[$q_name] > 4) {
                echo json_encode(["success" => false, "message" => "Respuesta inválida o faltante para la pregunta " . $i . " del Test de Estrés."]);
                $conn->close();
                exit();
            }
            $puntaje += intval($answers[$q_name]);
        }
        
        // PSS-10 interpretation (0-40)
        if ($puntaje >= 0 && $puntaje <= 13) {
            $resultado_texto = "Nivel de estrés bajo. Generalmente, has manejado bien las situaciones.";
        } elseif ($puntaje >= 14 && $puntaje <= 26) {
            $resultado_texto = "Nivel de estrés moderado. Podrías beneficiarte de técnicas de manejo del estrés.";
        } else { // $puntaje >= 27 && $puntaje <= 40
            $resultado_texto = "Nivel de estrés alto. Considera buscar apoyo profesional para el manejo del estrés.";
        }
        break;

    default:
        echo json_encode(["success" => false, "message" => "Tipo de test no reconocido para el cálculo de puntaje."]);
        $conn->close();
        exit();
}


// 4. Insert result into database
$stmt = $conn->prepare("INSERT INTO resultados_tests (id_usuario, id_test, puntaje, resultado_texto) VALUES (?, ?, ?, ?)");

if ($stmt) {
    $stmt->bind_param("iiis", $user_id, $test_id, $puntaje, $resultado_texto);

    if ($stmt->execute()) {
        // Return the result_text calculated by the backend
        echo json_encode(["success" => true, "message" => "Resultado del test guardado exitosamente.", "resultado_texto" => $resultado_texto]);
    } else {
        error_log("Error al guardar resultado del test (id_usuario: $user_id, test_id: $test_id): " . $stmt->error);
        echo json_encode(["success" => false, "message" => "Error al guardar el resultado del test. Por favor, inténtalo de nuevo más tarde."]);
    }
    $stmt->close();
} else {
    error_log("Error en la preparación de la consulta SQL: " . $conn->error);
    echo json_encode(["success" => false, "message" => "Error interno del servidor al procesar el test."]);
}

$conn->close();
?>