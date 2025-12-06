<?php
// vistas_php/test_estres.php
require_once '../auth_middleware.php';

// Habilitar reportes de errores de MySQLi para una mejor depuración
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Conexión a la base de datos
// Asegúrate de que el nombre del archivo de conexión sea correcto (db_conect.php o db_connect.php)
require_once '../db_conect.php'; // Cambia a '../db_connect.php' si ese es el nombre de tu archivo

// Verificar la conexión a la base de datos
if ($conn->connect_error) {
    die("Error de conexión a la base de datos: " . $conn->connect_error);
}

// Proteger esta página: solo usuarios y administradores pueden acceder
protect_page(['usuario', 'administrador']);

$pageTitle = "Test de Estrés";

$test_id = $_GET['test_id'] ?? null;
if (!$test_id) {
    echo "<div class='alert alert-danger text-center'>ID de test no especificado.</div>";
    require_once '../_footer.php';
    exit();
}

$stmt = $conn->prepare("SELECT nombre_test FROM tests WHERE id = ?");
if ($stmt === false) {
    die("Error al preparar la consulta de test: " . $conn->error);
}
$stmt->bind_param("i", $test_id);
$stmt->execute();
$result = $stmt->get_result();
$test_info = $result->fetch_assoc();
$stmt->close();

if (!$test_info || $test_info['nombre_test'] !== 'Test de Estrés') {
    echo "<div class='alert alert-danger text-center'>Este no es el Test de Estrés o el ID es inválido.</div>";
    require_once '../_footer.php';
    exit();
}

// Asegúrate de que $_SESSION['user_id'] esté configurado al iniciar sesión
if (!isset($_SESSION['user_id'])) {
    echo "<div class='alert alert-danger text-center'>Debes iniciar sesión para realizar este test.</div>";
    require_once '../_footer.php';
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
    <title>Test de Estres</title>
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-10 offset-md-1">
                <h1 class="text-center text-primary-custom">Test de Estrés PSS10</h1>
                <p class="lead text-center">Indica con qué frecuencia te has sentido de determinada manera durante el último mes.</p>

                <form id="stressTestForm" action="guardar_resultado_test.php" method="POST" class="bg-light p-4 rounded shadow-lg">
                    <input type="hidden" name="test_id" value="<?php echo htmlspecialchars($test_id); ?>">
                    <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">
                    <input type="hidden" name="test_name" value="Test de Estrés">

                    <div class="mb-4 p-3 border rounded bg-white">
                        <label class="form-label mb-2">1. ¿Con qué frecuencia te has sentido alterado/a por algo que ha ocurrido inesperadamente?</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q1" value="0" id="q1_0" required>
                            <label class="form-check-label" for="q1_0">0 - Nunca</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q1" value="1" id="q1_1">
                            <label class="form-check-label" for="q1_1">1 - Casi nunca</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q1" value="2" id="q1_2">
                            <label class="form-check-label" for="q1_2">2 - De vez en cuando</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q1" value="3" id="q1_3">
                            <label class="form-check-label" for="q1_3">3 - A menudo</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q1" value="4" id="q1_4">
                            <label class="form-check-label" for="q1_4">4 - Muy a menudo</label>
                        </div>
                    </div>

                    <div class="mb-4 p-3 border rounded bg-white">
                        <label class="form-label mb-2">2. ¿Con qué frecuencia te has sentido incapaz de controlar las cosas importantes de tu vida?</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q2" value="0" id="q2_0" required>
                            <label class="form-check-label" for="q2_0">0 - Nunca</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q2" value="1" id="q2_1">
                            <label class="form-check-label" for="q2_1">1 - Casi nunca</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q2" value="2" id="q2_2">
                            <label class="form-check-label" for="q2_2">2 - De vez en cuando</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q2" value="3" id="q2_3">
                            <label class="form-check-label" for="q2_3">3 - A menudo</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q2" value="4" id="q2_4">
                            <label class="form-check-label" for="q2_4">4 - Muy a menudo</label>
                        </div>
                    </div>

                    <div class="mb-4 p-3 border rounded bg-white">
                        <label class="form-label mb-2">3. ¿Con qué frecuencia te has sentido nervioso/a o estresado/a?</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q3" value="0" id="q3_0" required>
                            <label class="form-check-label" for="q3_0">0 - Nunca</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q3" value="1" id="q3_1">
                            <label class="form-check-label" for="q3_1">1 - Casi nunca</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q3" value="2" id="q3_2">
                            <label class="form-check-label" for="q3_2">2 - De vez en cuando</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q3" value="3" id="q3_3">
                            <label class="form-check-label" for="q3_3">3 - A menudo</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q3" value="4" id="q3_4">
                            <label class="form-check-label" for="q3_4">4 - Muy a menudo</label>
                        </div>
                    </div>

                    <div class="mb-4 p-3 border rounded bg-white">
                        <label class="form-label mb-2">4. ¿Con qué frecuencia has afrontado con éxito los pequeños problemas irritantes de la vida?</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q4" value="4" id="q4_0" required>
                            <label class="form-check-label" for="q4_0">0 - Nunca</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q4" value="3" id="q4_1">
                            <label class="form-check-label" for="q4_1">1 - Casi nunca</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q4" value="2" id="q4_2">
                            <label class="form-check-label" for="q4_2">2 - De vez en cuando</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q4" value="1" id="q4_3">
                            <label class="form-check-label" for="q4_3">3 - A menudo</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q4" value="0" id="q4_4">
                            <label class="form-check-label" for="q4_4">4 - Muy a menudo</label>
                        </div>
                    </div>

                    <div class="mb-4 p-3 border rounded bg-white">
                        <label class="form-label mb-2">5. ¿Con qué frecuencia has sentido que las cosas te iban bien?</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q5" value="4" id="q5_0" required>
                            <label class="form-check-label" for="q5_0">0 - Nunca</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q5" value="3" id="q5_1">
                            <label class="form-check-label" for="q5_1">1 - Casi nunca</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q5" value="2" id="q5_2">
                            <label class="form-check-label" for="q5_2">2 - De vez en cuando</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q5" value="1" id="q5_3">
                            <label class="form-check-label" for="q5_3">3 - A menudo</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q5" value="0" id="q5_4">
                            <label class="form-check-label" for="q5_4">4 - Muy a menudo</label>
                        </div>
                    </div>

                    <div class="mb-4 p-3 border rounded bg-white">
                        <label class="form-label mb-2">6. ¿Con qué frecuencia has sentido que no podías con todo lo que tenías que hacer?</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q6" value="0" id="q6_0" required>
                            <label class="form-check-label" for="q6_0">0 - Nunca</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q6" value="1" id="q6_1">
                            <label class="form-check-label" for="q6_1">1 - Casi nunca</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q6" value="2" id="q6_2">
                            <label class="form-check-label" for="q6_2">2 - De vez en cuando</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q6" value="3" id="q6_3">
                            <label class="form-check-label" for="q6_3">3 - A menudo</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q6" value="4" id="q6_4">
                            <label class="form-check-label" for="q6_4">4 - Muy a menudo</label>
                        </div>
                    </div>

                    <div class="mb-4 p-3 border rounded bg-white">
                        <label class="form-label mb-2">7. ¿Con qué frecuencia has pensado en las cosas que te quedan por hacer?</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q7" value="0" id="q7_0" required>
                            <label class="form-check-label" for="q7_0">0 - Nunca</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q7" value="1" id="q7_1">
                            <label class="form-check-label" for="q7_1">1 - Casi nunca</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q7" value="2" id="q7_2">
                            <label class="form-check-label" for="q7_2">2 - De vez en cuando</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q7" value="3" id="q7_3">
                            <label class="form-check-label" for="q7_3">3 - A menudo</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q7" value="4" id="q7_4">
                            <label class="form-check-label" for="q7_4">4 - Muy a menudo</label>
                        </div>
                    </div>

                    <div class="mb-4 p-3 border rounded bg-white">
                        <label class="form-label mb-2">8. ¿Con qué frecuencia has recordado que te gustaba hacer las cosas?</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q8" value="4" id="q8_0" required>
                            <label class="form-check-label" for="q8_0">0 - Nunca</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q8" value="3" id="q8_1">
                            <label class="form-check-label" for="q8_1">1 - Casi nunca</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q8" value="2" id="q8_2">
                            <label class="form-check-label" for="q8_2">2 - De vez en cuando</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q8" value="1" id="q8_3">
                            <label class="form-check-label" for="q8_3">3 - A menudo</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q8" value="0" id="q8_4">
                            <label class="form-check-label" for="q8_4">4 - Muy a menudo</label>
                        </div>
                    </div>

                    <div class="mb-4 p-3 border rounded bg-white">
                        <label class="form-label mb-2">9. ¿Con qué frecuencia has sentido que las dificultades se acumulaban tanto que no podías superarlas?</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q9" value="0" id="q9_0" required>
                            <label class="form-check-label" for="q9_0">0 - Nunca</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q9" value="1" id="q9_1">
                            <label class="form-check-label" for="q9_1">1 - Casi nunca</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q9" value="2" id="q9_2">
                            <label class="form-check-label" for="q9_2">2 - De vez en cuando</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q9" value="3" id="q9_3">
                            <label class="form-check-label" for="q9_3">3 - A menudo</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q9" value="4" id="q9_4">
                            <label class="form-check-label" for="q9_4">4 - Muy a menudo</label>
                        </div>
                    </div>

                    <div class="mb-4 p-3 border rounded bg-white">
                        <label class="form-label mb-2">10. ¿Con qué frecuencia has sido capaz de controlar los irritantes de tu vida?</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q10" value="4" id="q10_0" required>
                            <label class="form-check-label" for="q10_0">0 - Nunca</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q10" value="3" id="q10_1">
                            <label class="form-check-label" for="q10_1">1 - Casi nunca</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q10" value="2" id="q10_2">
                            <label class="form-check-label" for="q10_2">2 - De vez en cuando</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q10" value="1" id="q10_3">
                            <label class="form-check-label" for="q10_3">3 - A menudo</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q10" value="0" id="q10_4">
                            <label class="form-check-label" for="q10_4">4 - Muy a menudo</label>
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
document.getElementById('stressTestForm').addEventListener('submit', async function(event) {
    event.preventDefault();

    const formData = new FormData(this);
    let score = 0;
    // Las preguntas 4, 5, 8 y 10 tienen puntuación invertida en la escala PSS
    // Sus valores en el HTML ya están configurados para la inversión (4=0, 3=1, etc.)
    // Por lo tanto, solo necesitamos sumar los valores directamente.
    for (let i = 1; i <= 10; i++) {
        const qName = `q${i}`;
        const value = formData.get(qName);
        if (value !== null && !isNaN(parseInt(value))) {
            score += parseInt(value);
        }
    }

    const testId = formData.get('test_id');
    const userId = formData.get('user_id');
    const testName = formData.get('test_name');

    let resultadoTexto = "";
    // Lógica de interpretación de puntaje para PSS-10 (rango 0-40)
    // Estos umbrales son generales y pueden ajustarse según la referencia específica.
    if (score >= 0 && score <= 13) {
        resultadoTexto = "Nivel de estrés bajo. Generalmente, has manejado bien las situaciones.";
    } else if (score >= 14 && score <= 26) {
        resultadoTexto = "Nivel de estrés moderado. Podrías beneficiarte de técnicas de manejo del estrés.";
    } else { // score >= 27 && score <= 40
        resultadoTexto = "Nivel de estrés alto. Considera buscar apoyo profesional para el manejo del estrés.";
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
                puntaje: score,
                resultado_texto: resultadoTexto,
                test_name: testName
            })
        });

        const result = await response.json();
        const messageDiv = document.getElementById('testResultMessage');

        if (result.success) {
            messageDiv.className = 'alert alert-success';
            messageDiv.innerHTML = `<strong>¡Test Completado!</strong> Tu resultado para el ${testName} es: ${resultadoTexto}. <a href="vista_usuario.php" class="alert-link">Volver al Dashboard</a>`;
        } else {
            messageDiv.className = 'alert alert-danger';
            messageDiv.textContent = `Error al guardar el resultado: ${result.message}`;
        }
        messageDiv.style.display = 'block';
        document.getElementById('stressTestForm').style.display = 'none'; // Oculta el formulario al finalizar
    } catch (error) {
        console.error('Error al enviar el test:', error);
        const messageDiv = document.getElementById('testResultMessage');
        messageDiv.className = 'alert alert-danger';
        messageDiv.textContent = 'Error de conexión al servidor al guardar el test.';
        messageDiv.style.display = 'block';
    }
});
</script>

<?php
require_once '../_footer.php';
$conn->close();
?>