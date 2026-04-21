<?php
session_start();
if(!isset($_SESSION['usuario'])){ header("Location: ../login.php"); exit(); }
require_once("../config/conexion.php");

if($_SERVER['REQUEST_METHOD']==='POST') {
    $titulo      = trim($_POST['titulo'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $ciudad      = trim($_POST['ciudad'] ?? '');
    $usuario_id  = $_SESSION['usuario_id'];
    $imagen      = null;

    // Subir archivo
    if(!empty($_FILES['media']['name'])) {
        $ext      = strtolower(pathinfo($_FILES['media']['name'], PATHINFO_EXTENSION));
        $permitidos = ['jpg','jpeg','png','gif','webp','mp4','webm','ogg','mov'];

        if(in_array($ext, $permitidos)) {
            $carpeta = "../uploads/";
            if(!is_dir($carpeta)) mkdir($carpeta, 0755, true);
            $nombre  = uniqid('don_') . '.' . $ext;
            if(move_uploaded_file($_FILES['media']['tmp_name'], $carpeta . $nombre)) {
                $imagen = $nombre;
            }
        }
    }

    if(!empty($titulo)) {
        $stmt = $pdo->prepare("INSERT INTO donaciones (usuario_id, titulo, descripcion, ciudad, imagen, monto) VALUES (?,?,?,?,?,0)");
        $stmt->execute([$usuario_id, $titulo, $descripcion, $ciudad, $imagen]);
    }
}
header("Location: ../donar.php?msg=donacion_publicada");
exit();
?>