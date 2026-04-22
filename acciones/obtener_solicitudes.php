<?php
session_start();
require_once("../config/conexion.php");
header('Content-Type: application/json');

if(!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'profesional') {
    echo json_encode([]); exit();
}

$prof_id = $_SESSION['usuario_id'];

$stmt = $pdo->prepare("
    SELECT sc.*, s.nombre AS servicio_nombre, s.especialidad,
        DATE_FORMAT(h.dia_hora, '%W %d de %M %Y, %h:%i %p') AS dia_hora
    FROM solicitudes_cita sc
    JOIN servicios s ON sc.servicio_id = s.id
    JOIN horarios h ON sc.horario_id = h.id
    WHERE s.profesional_id = ?
    ORDER BY sc.fecha DESC
");
$stmt->execute([$prof_id]);
$solicitudes = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($solicitudes);
?>