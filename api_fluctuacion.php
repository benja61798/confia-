<?php
require_once 'db_conect.php';
$rango = $_GET['rango'] ?? 'dia';
$fluctuaciones = [];
if ($rango == 'semana') {
    $sql = "SELECT YEARWEEK(fecha_registro, 1) as semana, AVG(nivel_animo) as promedio
        FROM estado_animo WHERE id_usuario IN (SELECT id FROM usuarios WHERE rol='usuario')
        GROUP BY semana ORDER BY semana DESC LIMIT 12";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $fluctuaciones['Semana '.$row['semana']] = round($row['promedio'],2);
    }
} elseif ($rango == 'mes') {
    $sql = "SELECT DATE_FORMAT(fecha_registro, '%Y-%m') as mes, AVG(nivel_animo) as promedio
        FROM estado_animo WHERE id_usuario IN (SELECT id FROM usuarios WHERE rol='usuario')
        GROUP BY mes ORDER BY mes DESC LIMIT 12";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $fluctuaciones[$row['mes']] = round($row['promedio'],2);
    }
} else {
    $sql = "SELECT DATE(fecha_registro) as fecha, AVG(nivel_animo) as promedio
        FROM estado_animo WHERE id_usuario IN (SELECT id FROM usuarios WHERE rol='usuario')
        GROUP BY fecha ORDER BY fecha DESC LIMIT 30";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $fluctuaciones[$row['fecha']] = round($row['promedio'],2);
    }
}
header('Content-Type: application/json');
echo json_encode($fluctuaciones);
?>