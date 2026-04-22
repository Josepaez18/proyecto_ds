<?php
session_start();
require_once("../config/conexion.php");
header('Content-Type: application/json');

if(!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'profesional') {
    echo json_encode(['ok'=>false]); exit();
}

$solicitud_id = (int)($_POST['solicitud_id'] ?? 0);
$estado       = $_POST['estado'] ?? '';
$prof_id      = $_SESSION['usuario_id'];

if(!in_array($estado, ['aceptada','rechazada']) || !$solicitud_id) {
    echo json_encode(['ok'=>false]); exit();
}

// Verificar que la solicitud pertenece a un servicio de este profesional
$check = $pdo->prepare("
    SELECT sc.id, sc.horario_id FROM solicitudes_cita sc
    JOIN servicios s ON sc.servicio_id = s.id
    WHERE sc.id = ? AND s.profesional_id = ?
");
$check->execute([$solicitud_id, $prof_id]);
$sol = $check->fetch();

if(!$sol) { echo json_encode(['ok'=>false]); exit(); }

// Actualizar estado
$pdo->prepare("UPDATE solicitudes_cita SET estado=? WHERE id=?")->execute([$estado, $solicitud_id]);

// Si se rechaza, liberar el horario
if($estado === 'rechazada') {
    $pdo->prepare("UPDATE horarios SET disponible=1 WHERE id=?")->execute([$sol['horario_id']]);
}

echo json_encode(['ok'=>true, 'estado'=>$estado]);
?>