<?php
// vistas_php/test_depresion.php
require_once '../auth_middleware.php'; // Ruta corregida
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // Habilitar reportes de errores de MySQLi

// Conexión a la base de datos
// Asegúrate de que el nombre del archivo de conexión sea correcto (db_conect.php o db_connect.php)
require_once '../db_conect.php'; // Ruta corregida. Cambia a '../db_connect.php' si ese es el nombre de tu archivo

// Verificar la conexión a la base de datos
if ($conn->connect_error) {
    die("Error de conexión a la base de datos: " . $conn->connect_error);
}

// Proteger esta página: solo usuarios y administradores
protect_page(['usuario', 'administrador']);

$pageTitle = "Test de Depresión";

$test_id = $_GET['test_id'] ?? null; // Obtener el ID del test de la URL
if (!$test_id) {
    echo "<div class='alert alert-danger text-center'>ID de test no especificado.</div>";
    require_once '../_footer.php'; // Ruta corregida
    exit();
}

// Validar que el test_id corresponda al "Test de Depresión"
$stmt = $conn->prepare("SELECT nombre_test FROM tests WHERE id = ?");
if ($stmt === false) {
    die("Error al preparar la consulta de test: " . $conn->error);
}
$stmt->bind_param("i", $test_id);
$stmt->execute();
$result = $stmt->get_result();
$test_info = $result->fetch_assoc();
$stmt->close();

if (!$test_info || $test_info['nombre_test'] !== 'Test de Depresión') {
    echo "<div class='alert alert-danger text-center'>Este no es el Test de Depresión o el ID es inválido.</div>";
    require_once '../_footer.php'; // Ruta corregida
    exit();
}

// Asegúrate de que $_SESSION['user_id'] esté configurado al iniciar sesión
if (!isset($_SESSION['user_id'])) {
    echo "<div class='alert alert-danger text-center'>Debes iniciar sesión para realizar este test.</div>";
    require_once '../_footer.php'; // Ruta corregida
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/estilos_generales.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="icon" type="image/png" href="/img/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <title>Test de Depresión</title>
</head>
<body>
    <div class="container mt-5">

        <div class="row">
            <div class="col-md-10 offset-md-1">
                <h1 class="text-center text-primary-custom">Test de Depresión (PHQ-9)</h1>
                <p class="lead text-center">Durante las últimas 2 semanas, ¿con qué frecuencia te han molestado los siguientes problemas?</p>

                <form id="depressionTestForm" action="guardar_resultado_test.php" method="POST" class="bg-light p-4 rounded shadow-lg">
                    <input type="hidden" name="test_id" value="<?php echo htmlspecialchars($test_id); ?>">
                    <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">
                    <input type="hidden" name="test_name" value="Test de Depresión">

                    <div class="mb-4 p-3 border rounded bg-white">
                        <label class="form-label mb-2">1. Poco interés o placer en hacer las cosas:</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q1" value="0" id="q1_0" required>
                            <label class="form-check-label" for="q1_0">0 - En absoluto</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q1" value="1" id="q1_1">
                            <label class="form-check-label" for="q1_1">1 - Varios días</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q1" value="2" id="q1_2">
                            <label class="form-check-label" for="q1_2">2 - Más de la mitad de los días</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q1" value="3" id="q1_3">
                            <label class="form-check-label" for="q1_3">3 - Casi todos los días</label>
                        </div>
                    </div>

                    <div class="mb-4 p-3 border rounded bg-white">
                        <label class="form-label mb-2">2. Sentirse desanimado/a, deprimido/a o sin esperanzas:</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q2" value="0" id="q2_0" required>
                            <label class="form-check-label" for="q2_0">0 - En absoluto</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q2" value="1" id="q2_1">
                            <label class="form-check-label" for="q2_1">1 - Varios días</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q2" value="2" id="q2_2">
                            <label class="form-check-label" for="q2_2">2 - Más de la mitad de los días</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q2" value="3" id="q2_3">
                            <label class="form-check-label" for="q2_3">3 - Casi todos los días</label>
                        </div>
                    </div>

                    <div class="mb-4 p-3 border rounded bg-white">
                        <label class="form-label mb-2">3. Dificultad para conciliar el sueño o permanecer dormido/a, o dormir demasiado:</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q3" value="0" id="q3_0" required>
                            <label class="form-check-label" for="q3_0">0 - En absoluto</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q3" value="1" id="q3_1">
                            <label class="form-check-label" for="q3_1">1 - Varios días</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q3" value="2" id="q3_2">
                            <label class="form-check-label" for="q3_2">2 - Más de la mitad de los días</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q3" value="3" id="q3_3">
                            <label class="form-check-label" for="q3_3">3 - Casi todos los días</label>
                        </div>
                    </div>

                    <div class="mb-4 p-3 border rounded bg-white">
                        <label class="form-label mb-2">4. Sentirse cansado/a o con poca energía:</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q4" value="0" id="q4_0" required>
                            <label class="form-check-label" for="q4_0">0 - En absoluto</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q4" value="1" id="q4_1">
                            <label class="form-check-label" for="q4_1">1 - Varios días</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q4" value="2" id="q4_2">
                            <label class="form-check-label" for="q4_2">2 - Más de la mitad de los días</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q4" value="3" id="q4_3">
                            <label class="form-check-label" for="q4_3">3 - Casi todos los días</label>
                        </div>
                    </div>

                    <div class="mb-4 p-3 border rounded bg-white">
                        <label class="form-label mb-2">5. Poco apetito o comer en exceso:</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q5" value="0" id="q5_0" required>
                            <label class="form-check-label" for="q5_0">0 - En absoluto</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q5" value="1" id="q5_1">
                            <label class="form-check-label" for="q5_1">1 - Varios días</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q5" value="2" id="q5_2">
                            <label class="form-check-label" for="q5_2">2 - Más de la mitad de los días</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q5" value="3" id="q5_3">
                            <label class="form-check-label" for="q5_3">3 - Casi todos los días</label>
                        </div>
                    </div>

                    <div class="mb-4 p-3 border rounded bg-white">
                        <label class="form-label mb-2">6. Sentirse mal consigo mismo/a, o que es un fracaso, o que ha defraudado a su familia o a sí mismo/a:</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q6" value="0" id="q6_0" required>
                            <label class="form-check-label" for="q6_0">0 - En absoluto</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q6" value="1" id="q6_1">
                            <label class="form-check-label" for="q6_1">1 - Varios días</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q6" value="2" id="q6_2">
                            <label class="form-check-label" for="q6_2">2 - Más de la mitad de los días</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q6" value="3" id="q6_3">
                            <label class="form-check-label" for="q6_3">3 - Casi todos los días</label>
                        </div>
                    </div>

                    <div class="mb-4 p-3 border rounded bg-white">
                        <label class="form-label mb-2">7. Dificultad para concentrarse en cosas, como leer el periódico o ver la televisión:</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q7" value="0" id="q7_0" required>
                            <label class="form-check-label" for="q7_0">0 - En absoluto</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q7" value="1" id="q7_1">
                            <label class="form-check-label" for="q7_1">1 - Varios días</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q7" value="2" id="q7_2">
                            <label class="form-check-label" for="q7_2">2 - Más de la mitad de los días</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q7" value="3" id="q7_3">
                            <label class="form-check-label" for="q7_3">3 - Casi todos los días</label>
                        </div>
                    </div>

                    <div class="mb-4 p-3 border rounded bg-white">
                        <label class="form-label mb-2">8. Moverse o hablar tan lento/a que otras personas lo han notado. O, por el contrario, estar tan inquieto/a o nervioso/a que se mueve mucho más de lo normal:</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q8" value="0" id="q8_0" required>
                            <label class="form-check-label" for="q8_0">0 - En absoluto</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q8" value="1" id="q8_1">
                            <label class="form-check-label" for="q8_1">1 - Varios días</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q8" value="2" id="q8_2">
                            <label class="form-check-label" for="q8_2">2 - Más de la mitad de los días</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q8" value="3" id="q8_3">
                            <label class="form-check-label" for="q8_3">3 - Casi todos los días</label>
                        </div>
                    </div>

                    <div class="mb-4 p-3 border rounded bg-white">
                        <label class="form-label mb-2">9. Pensamientos de que estaría mejor muerto/a o de lastimarse de alguna manera:</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q9" value="0" id="q9_0" required>
                            <label class="form-check-label" for="q9_0">0 - En absoluto</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q9" value="1" id="q9_1">
                            <label class="form-check-label" for="q9_1">1 - Varios días</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q9" value="2" id="q9_2">
                            <label class="form-check-label" for="q9_2">2 - Más de la mitad de los días</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q9" value="3" id="q9_3">
                            <label class="form-check-label" for="q9_3">3 - Casi todos los días</label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary-custom w-100 mt-4">Finalizar Test</button>
                </form>

                <div id="testResultMessage" class="mt-4" style="display: none;"></div>

            </div>
        </div>
    </div>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init();
        // Mostrar los mensajes de verificación en la consola
    </script>
    <script src="../js/main.js"></script>
    <script src="../js/admin_crud.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</body>
</html>


<script>
document.getElementById('depressionTestForm').addEventListener('submit', async function(event) {
    event.preventDefault();

    const formData = new FormData(this);

    const testId = formData.get('test_id');
    const userId = formData.get('user_id');
    const testName = formData.get('test_name');

    // Collect individual answers to send to the backend
    const answers = {};
    for (let i = 1; i <= 10; i++) {
        const qName = `q${i}`;
        const value = formData.get(qName);
        if (value !== null) { // We don't need to parseInt here, backend will handle it
            answers[qName] = value;
        }
    }

    try {
        const response = await fetch('guardar_resultado_test.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                test_id: testId,
                user_id: userId,
                test_name: testName,
                answers: answers // Sending individual answers
            })
        });

        const result = await response.json();
        const messageDiv = document.getElementById('testResultMessage');

        if (result.success) {
            messageDiv.className = 'alert alert-success';
            // The 'resultado_texto' now comes from the backend's response
            messageDiv.innerHTML = `<strong>¡Test Completado!</strong> Tu resultado para el ${testName} es: ${result.resultado_texto}. <a href="vista_usuario.php" class="alert-link">Volver al Dashboard</a>`;
        } else {
            messageDiv.className = 'alert alert-danger';
            messageDiv.textContent = `Error al guardar el resultado: ${result.message}`;
        }
        messageDiv.style.display = 'block';
        document.getElementById('stressTestForm').style.display = 'none';
    } catch (error) {
        console.error('Error al enviar el test:', error);
        const messageDiv = document.getElementById('testResultMessage');
        messageDiv.className = 'alert alert-danger';
        messageDiv.textContent = 'Error de conexión al servidor al guardar el test.';
        messageDiv.style.display = 'block';
    }
</script>

<?php
require_once '../_footer.php'; // Ruta corregida
$conn->close();
?>