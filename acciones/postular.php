<?php
session_start();
if(!isset($_SESSION['usuario'])){ header("Location: ../login.php"); exit(); }
require_once("../config/conexion.php");

if($_SERVER['REQUEST_METHOD']==='POST') {

    $usuario_id = $_SESSION['usuario_id'];

    // Postulación a empleo real de BD
    if(!empty($_POST['empleo_id'])) {
        $empleo_id = (int)$_POST['empleo_id'];
        $check = $pdo->prepare("SELECT id FROM postulaciones WHERE usuario_id=? AND empleo_id=?");
        $check->execute([$usuario_id, $empleo_id]);
        if($check->fetch()) {
            header("Location: ../empleos.php?msg=ya_postulado");
        } else {
            $stmt = $pdo->prepare("INSERT INTO postulaciones (usuario_id, empleo_id, estado) VALUES (?,?,'pendiente')");
            $stmt->execute([$usuario_id, $empleo_id]);
            header("Location: ../empleos.php?msg=exito");
        }
        exit();
    }

    // Postulación a empleo de ejemplo (sin ID real en BD)
    if(!empty($_POST['empleo_titulo'])) {
        header("Location: ../empleos.php?msg=exito");
        exit();
    }
}

header("Location: ../empleos.php");
exit();
?>