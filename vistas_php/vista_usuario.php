<?php
// vistas_php/vista_usuario.php
require_once '../auth_middleware.php'; // Asegúrate de que esta ruta sea correcta desde vistas_php
require_once '../db_conect.php';     // Asegúrate de que esta ruta sea correcta desde vistas_php


$pageTitle = "Vista de Usuario";


$user_id = $_SESSION['user_id'];

// Lógica para la escala "Cómo me siento hoy"
$animo_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'guardar_animo') {
    $nivel_animo = intval($_POST['nivel_animo']);
    // Usa null coalescing operator para manejar si 'observaciones' no está seteado
    $observaciones = $conn->real_escape_string($_POST['observaciones'] ?? ''); 

    if ($nivel_animo >= 1 && $nivel_animo <= 5) { 
        $stmt = $conn->prepare("INSERT INTO estado_animo (id_usuario, nivel_animo, observaciones) VALUES (?, ?, ?)");
        if ($stmt) { // Verifica si la preparación de la consulta fue exitosa
            $stmt->bind_param("iis", $user_id, $nivel_animo, $observaciones);
            if ($stmt->execute()) {
                $animo_message = "<div class='alert alert-success'>¡Tu estado de ánimo ha sido registrado!</div>";
            } else {
                $animo_message = "<div class='alert alert-danger'>Error al registrar tu ánimo: " . $stmt->error . "</div>";
            }
            $stmt->close();
        } else {
            $animo_message = "<div class='alert alert-danger'>Error al preparar la consulta: " . $conn->error . "</div>";
        }
    } else {
        $animo_message = "<div class='alert alert-danger'>Nivel de ánimo inválido.</div>";
    }
}

// Lógica para el mensaje de mejora en el estado de ánimo
$mejora_animo_mensaje = '';
$stmt_animo_historial = $conn->prepare("SELECT nivel_animo FROM estado_animo WHERE id_usuario = ? ORDER BY fecha_registro DESC LIMIT 3");
if ($stmt_animo_historial) {
    $stmt_animo_historial->bind_param("i", $user_id);
    $stmt_animo_historial->execute();
    $result_animo_historial = $stmt_animo_historial->get_result();

    if ($result_animo_historial->num_rows >= 3) {
        $animos = [];
        while ($row = $result_animo_historial->fetch_assoc()) {
            $animos[] = $row['nivel_animo'];
        }
        // Comprueba si los últimos 3 registros muestran una mejora constante
        // anímo más reciente > anímo anterior > anímo más antiguo
        if ($animos[0] > $animos[1] && $animos[1] > $animos[2]) { 
            $mejora_animo_mensaje = "<div class='alert alert-info mt-3'>¡Genial! Parece que tu estado de ánimo está mejorando. ¡Sigue así, tú puedes!</div>";
        }
    }
    $stmt_animo_historial->close();
} else {
    // Manejar error si la preparación de la consulta falla
    // $mejora_animo_mensaje = "<div class='alert alert-danger'>Error al preparar consulta de historial de ánimo: " . $conn->error . "</div>";
}


// Obtener los tests disponibles 
$tests = [];
$stmt_tests = $conn->prepare("SELECT id, nombre_test, descripcion FROM tests ORDER BY id ASC");
if ($stmt_tests) {
    $stmt_tests->execute();
    $result_tests = $stmt_tests->get_result();
    while ($row = $result_tests->fetch_assoc()) {
        $tests[] = $row;
    }
    $stmt_tests->close();
} else {
    // Manejar error si la preparación de la consulta falla
    // error_log("Error al preparar consulta de tests: " . $conn->error);
}


// Obtener los últimos resultados de tests del usuario para mensajes de motivación 
$ultimos_resultados = [];
$stmt_ultimos_resultados = $conn->prepare("SELECT rt.resultado_texto, rt.fecha_realizacion, t.nombre_test FROM resultados_tests rt JOIN tests t ON rt.id_test = t.id WHERE rt.id_usuario = ? ORDER BY rt.fecha_realizacion DESC LIMIT 3");
if ($stmt_ultimos_resultados) {
    $stmt_ultimos_resultados->bind_param("i", $user_id);
    $stmt_ultimos_resultados->execute();
    $result_ultimos_resultados = $stmt_ultimos_resultados->get_result();
    while ($row = $result_ultimos_resultados->fetch_assoc()) {
        $ultimos_resultados[] = $row;
    }
    $stmt_ultimos_resultados->close();
} else {
    // Manejar error si la preparación de la consulta falla
    // error_log("Error al preparar consulta de últimos resultados: " . $conn->error);
}

?>
<!DOCTYPE html>
<html lang="es"> <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <link rel="stylesheet" href="/css/estilos_generales.css">
    <link rel="icon" type="image/png" href="/img/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
</head>
<body class="bg-light">
    <header class="header mb-4">
        <div class="container">
            <div class="logo">
                <a href="vista_usuario.php" style="text-decoration: none; color: inherit;">
                    <img src="/img/logo.png" alt="Logo" style="height: 30px; vertical-align: middle; margin-right: 10px;">
                    BienestarApp
                </a>
            </div>
            <nav class="nav">
                <ul>
                    <li><a href="vista_usuario.php">Inicio</a></li>
                    <li><a href="#contenedor-tests-seccion">Tests</a></li> <li><a href="#estado-animo">Estado de Ánimo</a></li>
                    <li><a href="/logout.php" style="color: #d9534f;">Cerrar sesión</a></li>
                </ul>
            </nav>
        </div>
    </header>


    <div class="container text-center my-5">
        <div class="row">
            <div class="col-12">
                <h1 class="text-center text-primary-custom">Bienvenido</h1>
                <p class="lead text-center">Aquí puedes acceder a herramientas y recursos para tu bienestar.</p>
            </div>
        </div>

        <hr>

        <div class="row my-4">
            <div class="col-12">
                <h2 class="text-secondary-custom">Mensajes Personalizados y Resultados Recientes</h2>
                <?php if (!empty($ultimos_resultados)): ?>
                    <?php foreach ($ultimos_resultados as $resultado): ?>
                        <div class="alert alert-info" role="alert">
                            <h5 class="alert-heading">Resultado de "<?php echo htmlspecialchars($resultado['nombre_test']); ?>" (<?php echo date('d/m/Y', strtotime($resultado['fecha_realizacion'])); ?>)</h5>
                            <p><?php echo htmlspecialchars($resultado['resultado_texto']); ?></p>
                            <?php
                            // Lógica muy simple de motivación: puedes expandirla mucho
                            if (strpos(strtolower($resultado['resultado_texto']), 'depresión') !== false && strpos(strtolower($resultado['resultado_texto']), 'severa') !== false) {
                                echo "<p><strong>¡Recuerda que no estás solo! Buscar ayuda profesional es un paso valiente y fundamental. Hay recursos disponibles para apoyarte en este camino.</strong></p>";
                            } elseif (strpos(strtolower($resultado['resultado_texto']), 'ansiedad') !== false && strpos(strtolower($resultado['resultado_texto']), 'alta') !== false) {
                                echo "<p><strong>Es normal sentirse ansioso. Practicar técnicas de relajación como la respiración profunda o la meditación puede ayudar. Considera hablar con un profesional.</strong></p>";
                            } else {
                                echo "<p><strong>Mantenerse informado y practicar el autocuidado es clave para tu bienestar.</strong></p>";
                            }
                            ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Realiza algunos tests para obtener mensajes personalizados y ver tus resultados aquí.</p>
                <?php endif; ?>
            </div>
        </div>

        <hr id="contenedor-tests-seccion"> <div class="row my-4">
            <div class="col-12">
                <h2 class="text-secondary-custom">Realiza un Test</h2>
                <p>Evalúa tu estado emocional con nuestros test diseñados.</p>
                <div class="container my-4">
                    <h4>Tests de Bienestar</h4>


                    <div id="contenedor-iframe-test" class="border rounded p-3" style="display:none;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h5 class="mb-0">Test en curso</h5>
                            <button class="btn btn-sm btn-danger" onclick="cerrarTest()">Cerrar</button>
                        </div>
                        <iframe id="iframe-test" src="" width="100%" height="500px" style="border: none;"></iframe>
                    </div>
                </div>
                
                <?php if (!empty($tests)): ?>
                    <div class="row">
                        <?php foreach ($tests as $test): ?>
                            <div class="col-md-4 mb-4">
                                <div class="card h-100 shadow-sm" data-aos="fade-up">
                                    <div class="card-body">
                                        <h5 class="card-title text-primary-custom"><?php echo htmlspecialchars($test['nombre_test']); ?></h5>
                                        <p class="card-text"><?php echo htmlspecialchars($test['descripcion']); ?></p>
                                        <?php
                                        // Mapeo simple de nombre de test a tipo para la función JavaScript
                                        $test_type = '';
                                        switch ($test['nombre_test']) {
                                            case 'Test de Depresión': $test_type = 'depresion'; break;
                                            case 'Test de Ansiedad': $test_type = 'ansiedad'; break;
                                            case 'Test de Estrés': $test_type = 'estres'; break;
                                            default: $test_type = ''; break; // Fallback
                                        }
                                        ?>
                                        <a href="#" onclick="event.preventDefault(); mostrarTest('<?php echo htmlspecialchars($test_type); ?>', <?php echo $test['id']; ?>);" class="btn btn-primary-custom">Iniciar Test</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>No hay tests disponibles en este momento.</p>
                <?php endif; ?>
            </div>
        </div>

        <hr id="estado-animo">

        <div class="row my-4">
            <div class="col-md-8 offset-md-2" >
                <h2 class="text-secondary-custom text-center">¿Cómo te sientes hoy?</h2>
                <p class="text-center">Registra tu estado de ánimo para que podamos ver tu progreso.</p>
                <?php echo $animo_message; ?>
                <form action="vista_usuario.php" method="POST" class="bg-light p-4 rounded shadow-sm">
                    <input type="hidden" name="action" value="guardar_animo">
                    <div class="mb-3">
                        <label for="nivel_animo" class="form-label">Mi estado de ánimo es (1-5, donde 1 es muy mal, 5 es excelente):</label>
                        <input type="range" class="form-range" id="nivel_animo" name="nivel_animo" min="1" max="5" value="3" oninput="this.nextElementSibling.value = this.value">
                        <output class="d-block text-center mt-2">3</output>
                    </div>
                    <div class="mb-3">
                        <label for="observaciones" class="form-label">Notas rápidas (opcional):</label>
                        <textarea class="form-control" id="observaciones" name="observaciones" rows="3" placeholder="Ej: Me sentí cansado por la mañana pero mejoré en la tarde."></textarea>
                    </div>
                    <button type="submit" class="btn btn-success w-100">Registrar mi Ánimo</button>
                </form>
                <?php echo $mejora_animo_mensaje; ?>
            </div>
        </div>
    </div>
    
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init(); // Inicializa AOS después de que se cargue la biblioteca
    </script>
    <script src="/js/main.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

    <script>
    // La función mostrarTest ahora acepta el tipo de test Y el ID del test
    function mostrarTest(tipo, id) {
        const rutas = {
            depresion: `../test_php/test_depresion.php?test_id=${id}`,
            ansiedad: `../test_php/test_ansiedad.php?test_id=${id}`,
            estres: `../test_php/test_estres.php?test_id=${id}`
        };

        const iframe = document.getElementById('iframe-test');
        
        // Asegurarse de que el tipo de test es válido
        if (rutas[tipo]) {
            iframe.src = rutas[tipo]; // Se asigna la ruta con el ID
            // Mostrar el contenedor del iframe
            document.getElementById('contenedor-iframe-test').style.display = 'block';
            // Opcional: Desplazar a la sección del test
            document.getElementById('contenedor-tests-seccion').scrollIntoView({ behavior: 'smooth' });
        } else {
            console.error('Tipo de test no reconocido:', tipo);
        }
    }

    function cerrarTest() {
        const iframe = document.getElementById('iframe-test');
        iframe.src = ''; // Limpiar el src del iframe
        document.getElementById('contenedor-iframe-test').style.display = 'none'; // Ocultar el contenedor
    }

    // Comunicación desde el iframe para cerrar el test
    // Esto es crucial para que los tests dentro del iframe puedan notificar a la página padre
    window.addEventListener('message', function(event) {
        // Asegurarse de que el origen del mensaje es el esperado por seguridad
        // En un entorno de producción, deberías verificar event.origin
        // if (event.origin === "http://tu-dominio.com") {
        if (event.data === 'cerrarTest') {
            cerrarTest();
        }
        // }
    });
    </script>
</body>
</html>

<?php
// Incluir el footer, asegurándonos de que la ruta es correcta.
// Si _footer.php está en el mismo nivel que auth_middleware.php y db_conect.php, entonces es '../_footer.php'
require_once '../_footer.php'; 

// Cerrar la conexión a la base de datos al final del script
$conn->close();
?>