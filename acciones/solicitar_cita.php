<?php
session_start();
if(!isset($_SESSION['usuario'])) { header("Location: ../login.php"); exit(); }
require_once("../config/conexion.php");

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $servicio_id       = (int)($_POST['servicio_id'] ?? 0);
    $horario_id        = (int)($_POST['horario_id'] ?? 0);
    $nombre_solicitante = trim($_POST['nombre_solicitante'] ?? '');
    $celular           = trim($_POST['celular'] ?? '');
    $cedula            = trim($_POST['cedula'] ?? '');
    $edad              = (int)($_POST['edad'] ?? 0);
    $mensaje           = trim($_POST['mensaje'] ?? '');
    $usuario_id        = $_SESSION['usuario_id'];

    if(!$servicio_id || !$horario_id || empty($nombre_solicitante) || empty($celular) || empty($cedula) || !$edad) {
        header("Location: ../asesorias.php?msg=error"); exit();
    }

    // Verificar si ya tiene solicitud pendiente
    $check = $pdo->prepare("SELECT id FROM solicitudes_cita WHERE servicio_id=? AND usuario_id=? AND estado='pendiente'");
    $check->execute([$servicio_id, $usuario_id]);
    if($check->fetch()) {
        header("Location: ../asesorias.php?msg=ya_solicitado"); exit();
    }

    // Insertar solicitud
    $stmt = $pdo->prepare("INSERT INTO solicitudes_cita (servicio_id, horario_id, nombre_solicitante, celular, cedula, edad, mensaje, usuario_id) VALUES (?,?,?,?,?,?,?,?)");
    $stmt->execute([$servicio_id, $horario_id, $nombre_solicitante, $celular, $cedula, $edad, $mensaje, $usuario_id]);

    // Marcar horario como no disponible
    $pdo->prepare("UPDATE horarios SET disponible=0 WHERE id=?")->execute([$horario_id]);

    header("Location: ../asesorias.php?msg=cita_solicitada"); exit();
}
header("Location: ../asesorias.php"); exit();
?>