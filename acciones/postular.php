<?php
session_start();
if(!isset($_SESSION['usuario'])){ header("Location: ../login.php"); exit(); }
require_once("../config/conexion.php");

if($_SERVER['REQUEST_METHOD']==='POST'){
    $usuario_id = $_SESSION['usuario_id'];
    $empleo_id  = (int)($_POST['empleo_id']??0);
    $hoja_vida  = null;

    // Subir PDF
    if(!empty($_FILES['hoja_vida']['name'])){
        $ext = strtolower(pathinfo($_FILES['hoja_vida']['name'],PATHINFO_EXTENSION));
        if($ext==='pdf'){
            $carpeta = "../uploads/";
            if(!is_dir($carpeta)) mkdir($carpeta,0755,true);
            $nombre = uniqid('hv_').'.'.$ext;
            if(move_uploaded_file($_FILES['hoja_vida']['tmp_name'],$carpeta.$nombre)){
                $hoja_vida=$nombre;
            }
        }
    }

    if($empleo_id>0){
        // Verificar si ya se postuló
        $check=$pdo->prepare("SELECT id FROM postulaciones WHERE usuario_id=? AND empleo_id=?");
        $check->execute([$usuario_id,$empleo_id]);
        if($check->fetch()){ header("Location: ../empleos.php?msg=ya_postulado"); exit(); }
        $stmt=$pdo->prepare("INSERT INTO postulaciones (usuario_id,empleo_id,hoja_vida,estado) VALUES (?,?,?,'pendiente')");
        $stmt->execute([$usuario_id,$empleo_id,$hoja_vida]);
    }
    header("Location: ../empleos.php?msg=exito"); exit();
}
header("Location: ../empleos.php"); exit();
?>