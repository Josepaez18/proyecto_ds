<?php
session_start();
if(!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'profesional') {
    header("Location: ../login.php"); exit();
}
require_once("../config/conexion.php");

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre      = trim($_POST['nombre'] ?? '');
    $especialidad = trim($_POST['especialidad'] ?? '');
    $tipo        = $_POST['tipo'] ?? 'psicologica';
    $descripcion = trim($_POST['descripcion'] ?? '');
    $horarios    = $_POST['horarios'] ?? [];
    $prof_id     = $_SESSION['usuario_id'];

    if(!empty($nombre) && !empty($especialidad)) {
        // Insertar servicio
        $stmt = $pdo->prepare("INSERT INTO servicios (profesional_id, nombre, especialidad, tipo, descripcion) VALUES (?,?,?,?,?)");
        $stmt->execute([$prof_id, $nombre, $especialidad, $tipo, $descripcion]);
        $servicio_id = $pdo->lastInsertId();

        // Insertar horarios
        foreach($horarios as $h) {
            $h = trim($h);
            if(!empty($h)) {
                $sh = $pdo->prepare("INSERT INTO horarios (servicio_id, dia_hora, disponible) VALUES (?,?,1)");
                $sh->execute([$servicio_id, $h]);
            }
        }
        header("Location: ../asesorias.php?msg=servicio_publicado");
        exit();
    }
}
header("Location: ../asesorias.php?msg=error");
exit();
?>