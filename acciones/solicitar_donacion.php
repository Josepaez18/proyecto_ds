<?php
session_start();
if(!isset($_SESSION['usuario'])){ header("Location: ../login.php"); exit(); }
require_once("../config/conexion.php");

if($_SERVER['REQUEST_METHOD']==='POST') {
    $donacion_id  = (int)($_POST['donacion_id'] ?? 0);
    $solicitante  = $_SESSION['usuario_id'];
    $mensaje      = trim($_POST['mensaje'] ?? '');

    // Verificar si ya solicitó
    $check = $pdo->prepare("SELECT id FROM solicitudes_donacion WHERE donacion_id=? AND solicitante_id=?");
    $check->execute([$donacion_id, $solicitante]);

    if($check->fetch()) {
        header("Location: ../donar.php?msg=ya_solicitado"); exit();
    }

    $stmt = $pdo->prepare("INSERT INTO solicitudes_donacion (donacion_id, solicitante_id, mensaje) VALUES (?,?,?)");
    $stmt->execute([$donacion_id, $solicitante, $mensaje]);

    header("Location: ../donar.php?msg=solicitud_enviada"); exit();
}
header("Location: ../donar.php"); exit();
?>