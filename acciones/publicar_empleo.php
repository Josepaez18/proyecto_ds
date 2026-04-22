<?php
session_start();
if(!isset($_SESSION['usuario']) || $_SESSION['rol']!=='empresa'){ header("Location: ../login.php"); exit(); }
require_once("../config/conexion.php");

if($_SERVER['REQUEST_METHOD']==='POST'){
    $titulo      = trim($_POST['titulo']??'');
    $descripcion = trim($_POST['descripcion']??'');
    $tipo        = $_POST['tipo'] ?? 'Tiempo completo';
    $ciudad      = trim($_POST['ciudad']??'');
    $salario     = trim($_POST['salario']??'');
    $empresa_id  = $_SESSION['usuario_id'];

    if(!empty($titulo)&&!empty($descripcion)){
        $stmt = $pdo->prepare("INSERT INTO empleos (empresa_id,titulo,descripcion,tipo,ciudad,salario) VALUES (?,?,?,?,?,?)");
        $stmt->execute([$empresa_id,$titulo,$descripcion,$tipo,$ciudad,$salario]);
    }
}
header("Location: ../empleos.php?msg=empleo_publicado"); exit();
?>