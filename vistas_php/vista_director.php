<?php
// vistas_php/vista_director.php
require_once '../auth_middleware.php';
require_once '../db_conect.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Proteger esta página: solo directores y administradores pueden acceder
protect_page(['director']);

$nombre_usuario = htmlspecialchars($_SESSION['nombre_usuario']);

$pageTitle = "Panel de Director";

// --- 1. Datos para gráfico pastel ---
$data_for_charts = ['depresion' => 0, 'ansiedad' => 0, 'estres' => 0];
$alumnos_alerta = [];
$sql = "SELECT rt.resultado_texto, t.nombre_test, u.nombre_usuario, u.id
        FROM resultados_tests rt
        JOIN tests t ON rt.id_test = t.id
        JOIN usuarios u ON rt.id_usuario = u.id
        WHERE u.rol = 'usuario'";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $texto = strtolower($row['resultado_texto']);
        $nombre_test = strtolower($row['nombre_test']);
        // Conteo para gráfico pastel
        if (strpos($nombre_test, 'depresión') !== false) $data_for_charts['depresion']++;
        elseif (strpos($nombre_test, 'ansiedad') !== false) $data_for_charts['ansiedad']++;
        elseif (strpos($nombre_test, 'estrés') !== false) $data_for_charts['estres']++;
        // Alertas específicas
        if (
            (strpos($nombre_test, 'ansiedad') !== false && strpos($texto, 'severa') !== false) ||
            (strpos($nombre_test, 'depresión') !== false && strpos($texto, 'severa') !== false) ||
            (strpos($nombre_test, 'estrés') !== false && strpos($texto, 'alto') !== false)
        ) {
            $alumnos_alerta[] = [
                'nombre' => $row['nombre_usuario'],
                'test' => ucfirst($nombre_test),
                'mensaje' => $row['resultado_texto']
            ];
        }
    }
}
$total_tests_registrados = array_sum($data_for_charts);

// --- 2. Porcentaje de cómo se sienten los alumnos ---
$animo_sql = "SELECT nivel_animo FROM estado_animo WHERE id_usuario IN (SELECT id FROM usuarios WHERE rol='usuario')";
$animo_result = $conn->query($animo_sql);
$animo_contador = [1=>0,2=>0,3=>0,4=>0,5=>0];
$total_animos = 0;
if ($animo_result) {
    while ($row = $animo_result->fetch_assoc()) {
        $nivel = intval($row['nivel_animo']);
        if ($nivel >= 1 && $nivel <= 5) {
            $animo_contador[$nivel]++;
            $total_animos++;
        }
    }
}

// --- 3. Fluctuaciones de ánimo (por día) ---
$rango = $_GET['rango'] ?? 'dia';
$fluctuaciones = [];
if ($rango == 'semana') {
    $fluctuacion_sql = "SELECT YEARWEEK(fecha_registro, 1) as semana, AVG(nivel_animo) as promedio
        FROM estado_animo WHERE id_usuario IN (SELECT id FROM usuarios WHERE rol='usuario')
        GROUP BY semana ORDER BY semana DESC LIMIT 12";
    $fluctuacion_result = $conn->query($fluctuacion_sql);
    if ($fluctuacion_result) {
        while ($row = $fluctuacion_result->fetch_assoc()) {
            $fluctuaciones['Semana '.$row['semana']] = round($row['promedio'],2);
        }
    }
} elseif ($rango == 'mes') {
    $fluctuacion_sql = "SELECT DATE_FORMAT(fecha_registro, '%Y-%m') as mes, AVG(nivel_animo) as promedio
        FROM estado_animo WHERE id_usuario IN (SELECT id FROM usuarios WHERE rol='usuario')
        GROUP BY mes ORDER BY mes DESC LIMIT 12";
    $fluctuacion_result = $conn->query($fluctuacion_sql);
    if ($fluctuacion_result) {
        while ($row = $fluctuacion_result->fetch_assoc()) {
            $fluctuaciones[$row['mes']] = round($row['promedio'],2);
        }
    }
} else {
    // Por día (default)
    $fluctuacion_sql = "SELECT DATE(fecha_registro) as fecha, AVG(nivel_animo) as promedio
        FROM estado_animo WHERE id_usuario IN (SELECT id FROM usuarios WHERE rol='usuario')
        GROUP BY fecha ORDER BY fecha DESC LIMIT 30";
    $fluctuacion_result = $conn->query($fluctuacion_sql);
    if ($fluctuacion_result) {
        while ($row = $fluctuacion_result->fetch_assoc()) {
            $fluctuaciones[$row['fecha']] = round($row['promedio'],2);
        }
    }
}

// --- 4. Top alumnos por problema ---
function top_alumnos($tipo, $conn) {
    $sql = "SELECT u.nombre_usuario, COUNT(*) as cantidad
            FROM resultados_tests rt
            JOIN tests t ON rt.id_test = t.id
            JOIN usuarios u ON rt.id_usuario = u.id
            WHERE u.rol='usuario' AND t.nombre_test LIKE '%$tipo%'
            GROUP BY u.id
            ORDER BY cantidad DESC
            LIMIT 5";
    $result = $conn->query($sql);
    $data = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $data[] = ['nombre' => $row['nombre_usuario'], 'cantidad' => $row['cantidad']];
        }
    }
    return $data;
}
$top_ansiedad = top_alumnos('Ansiedad', $conn);
$top_estres = top_alumnos('Estrés', $conn);
$top_depresion = top_alumnos('Depresión', $conn);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="../css/estilos_generales.css">
<title>Panel de Director</title>

<!-- Bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

<style>
body {
    font-family: 'Inter', sans-serif;
}

.profile-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: #4caf50;
    color: #fff;
    display: flex;
    justify-content: center;
    align-items: center;
    font-weight: bold;
    font-size: 1.2em;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.profile-icon:hover {
    background-color: #388e3c;
}

.navbar-brand.logo img {
    height: 40px;
    margin-right: 10px;
}

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
        <img src="../img/logo.png" alt="Logo">
        Confia+ Director
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdown"
                   role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="profile-icon me-2">
                        <?php echo strtoupper(substr($nombre_usuario, 0, 1)); ?>
                    </div>
                    <span><?php echo htmlspecialchars($nombre_usuario); ?></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="navbarDropdown">
                    <li>
                        <a class="dropdown-item" href="vista_perfil_director.php">
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
<!-- El resto de tu contenido -->
<div class="container mt-5">
    <h1 class="text-center text-primary-custom mb-4">Panel de Director</h1>
    <div class="row mb-4">
        <div class="col-md-6">
            <h4 class="mb-3">Resultados de Tests (Gráfico Pastel)</h4>
            <canvas id="testResultsChart"></canvas>
            <p class="mt-2">Total de tests analizados: <?php echo $total_tests_registrados; ?></p>
        </div>
        <div class="col-md-6">
            <h4 class="mb-3">Estado de Ánimo de los Alumnos</h4>
            <?php foreach ($animo_contador as $nivel => $cantidad):
                $porcentaje = $total_animos ? round(($cantidad/$total_animos)*100,1) : 0;
            ?>
                <div class="mb-1">
                    <strong>Nivel <?php echo $nivel; ?>:</strong> <?php echo $porcentaje; ?>%
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="mb-3">Alertas de Alumnos</h4>
            <?php if (!empty($alumnos_alerta)): ?>
                <?php foreach ($alumnos_alerta as $alumno): ?>
                    <div class="alert alert-danger">
                        <strong><?php echo htmlspecialchars($alumno['nombre']); ?></strong> - <?php echo htmlspecialchars($alumno['test']); ?>: <?php echo htmlspecialchars($alumno['mensaje']); ?>
                        <span class="fw-bold">→ Necesita atención especial.</span>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-success">No hay alertas críticas en este momento.</div>
            <?php endif; ?>
        </div>
    </div>
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h4 class="mb-3 text-center">Fluctuación de Ánimo</h4>
                    <form class="mb-3 text-center" id="rangoForm" onsubmit="return false;">
                        <label for="rango" class="form-label me-2">Ver por:</label>
                        <select name="rango" id="rango" class="form-select d-inline-block w-auto">
                            <option value="dia" <?php if(($rango ?? 'dia')=='dia') echo 'selected'; ?>>Día</option>
                            <option value="semana" <?php if(($rango ?? '')=='semana') echo 'selected'; ?>>Semana</option>
                            <option value="mes" <?php if(($rango ?? '')=='mes') echo 'selected'; ?>>Mes</option>
                        </select>
                    </form>
                    <canvas id="fluctuacionChart" style="min-height:300px;max-width:100%;"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h4 class="mb-3">Top Alumnos por Problema</h4>
                    <ul class="nav nav-tabs mb-3" id="problemaTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="ansiedad-tab" data-bs-toggle="tab" data-bs-target="#ansiedad" type="button" role="tab">Ansiedad</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="estres-tab" data-bs-toggle="tab" data-bs-target="#estres" type="button" role="tab">Estrés</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="depresion-tab" data-bs-toggle="tab" data-bs-target="#depresion" type="button" role="tab">Depresión</button>
                        </li>
                    </ul>
                    <div class="tab-content" id="problemaTabsContent">
                        <div class="tab-pane fade show active" id="ansiedad" role="tabpanel">
                            <canvas id="topAnsiedadChart"></canvas>
                        </div>
                        <div class="tab-pane fade" id="estres" role="tabpanel">
                            <canvas id="topEstresChart"></canvas>
                        </div>
                        <div class="tab-pane fade" id="depresion" role="tabpanel">
                            <canvas id="topDepresionChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Verifica si hay datos
    const testData = <?php echo json_encode(array_values($data_for_charts)); ?>;
    const totalTests = <?php echo $total_tests_registrados; ?>;
    if (totalTests > 0 && testData.some(v => v > 0)) {
        new Chart(document.getElementById('testResultsChart'), {
            type: 'doughnut',
            data: {
                labels: ['Depresión', 'Ansiedad', 'Estrés'],
                datasets: [{
                    data: testData,
                    backgroundColor: [
                        '#FF6384', // rosa fuerte
                        '#36A2EB', // azul
                        '#FFCE56'  // amarillo
                    ],
                    borderColor: ['#fff', '#fff', '#fff'],
                    borderWidth: 3
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: {
                                size: 16,
                                family: "'Montserrat', 'Open Sans', sans-serif"
                            },
                            color: '#333'
                        }
                    },
                    title: {
                        display: true,
                        text: 'Distribución de Tests por Problema',
                        font: { size: 18 }
                    }
                }
            }
        });
    } else {
        // Si no hay datos, muestra un mensaje en el canvas
        const canvas = document.getElementById('testResultsChart');
        const ctx = canvas.getContext('2d');
        ctx.font = "20px Montserrat, Arial";
        ctx.fillStyle = "#888";
        ctx.textAlign = "center";
        ctx.fillText("No hay datos suficientes para mostrar el gráfico", canvas.width / 2, canvas.height / 2);
    }

    // Fluctuación de ánimo dinámico
    let fluctChart;
    function renderFluctuacion(labels, data) {
        if (fluctChart) fluctChart.destroy();
        fluctChart = new Chart(document.getElementById('fluctuacionChart'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Ánimo promedio',
                    data: data,
                    fill: true,
                    borderColor: '#36A2EB',
                    backgroundColor: 'rgba(54,162,235,0.15)',
                    pointBackgroundColor: '#36A2EB',
                    pointBorderColor: '#fff',
                    pointRadius: 5,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: true, position: 'top' },
                    title: { display: true, text: 'Evolución del Ánimo Promedio', font: { size: 18 } },
                    tooltip: {
                        enabled: true,
                        callbacks: {
                            label: function(context) {
                                return 'Ánimo: ' + context.parsed.y;
                            }
                        }
                    }
                },
                scales: {
                    x: { title: { display: true, text: 'Fecha' }, ticks: { color: '#333', font: { size: 12 } } },
                    y: { title: { display: true, text: 'Ánimo promedio' }, min: 1, max: 5, ticks: { color: '#333', font: { size: 12 } } }
                }
            }
        });
    }

    function fetchFluctuacion(rango) {
        fetch(`/api_fluctuacion.php?rango=${rango}`)
            .then(res => res.json())
            .then(data => {
                renderFluctuacion(Object.keys(data), Object.values(data));
            });
    }

    // Inicializa con el rango actual
    fetchFluctuacion(document.getElementById('rango').value);

    // Actualiza al cambiar el selector
    document.getElementById('rango').addEventListener('change', function() {
        fetchFluctuacion(this.value);
    });

    // Top alumnos por problema
    function renderBarChart(canvasId, labels, data, color) {
        new Chart(document.getElementById(canvasId), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Cantidad de tests',
                    data: data,
                    backgroundColor: color
                }]
            },
            options: {
                responsive: true,
                indexAxis: 'y'
            }
        });
    }
    renderBarChart('topAnsiedadChart',
        <?php echo json_encode(array_column($top_ansiedad, 'nombre')); ?>,
        <?php echo json_encode(array_column($top_ansiedad, 'cantidad')); ?>,
        'rgba(54, 162, 235, 0.7)'
    );
    renderBarChart('topEstresChart',
        <?php echo json_encode(array_column($top_estres, 'nombre')); ?>,
        <?php echo json_encode(array_column($top_estres, 'cantidad')); ?>,
        'rgba(255, 206, 86, 0.7)'
    );
    renderBarChart('topDepresionChart',
        <?php echo json_encode(array_column($top_depresion, 'nombre')); ?>,
        <?php echo json_encode(array_column($top_depresion, 'cantidad')); ?>,
        'rgba(255, 99, 132, 0.7)'
    );
});
</script>
</body>
</html>
<?php
require_once '../_footer.php';
$conn->close();
?>
