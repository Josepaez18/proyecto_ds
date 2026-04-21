<?php
session_start();
if(!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'empresa'){
    header("Location: ../login.php"); exit();
}
require_once("../config/conexion.php");

if($_SERVER['REQUEST_METHOD']==='POST') {
    $titulo      = trim($_POST['titulo']);
    $descripcion = trim($_POST['descripcion']);
    $empresa_id  = $_SESSION['usuario_id'];

    if(!empty($titulo) && !empty($descripcion)) {
        $stmt = $pdo->prepare("INSERT INTO empleos (empresa_id, titulo, descripcion) VALUES (?,?,?)");
        $stmt->execute([$empresa_id, $titulo, $descripcion]);
    }
}
header("Location: ../empleos.php?msg=empleo_publicado");
exit();
?>