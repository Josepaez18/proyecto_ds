<?php
session_start();
if(!isset($_SESSION['usuario'])){ header("Location: ../login.php"); exit(); }
require_once("../config/conexion.php");

if($_SERVER['REQUEST_METHOD']==='POST') {
    $contenido  = trim($_POST['contenido'] ?? '');
    $usuario_id = $_SESSION['usuario_id'];
    $imagen     = null;

    if(!empty($_FILES['media']['name'])) {
        $ext = strtolower(pathinfo($_FILES['media']['name'], PATHINFO_EXTENSION));
        $permitidos = ['jpg','jpeg','png','gif','webp','mp4','webm','ogg','mov'];
        if(in_array($ext, $permitidos)) {
            $carpeta = "../uploads/";
            if(!is_dir($carpeta)) mkdir($carpeta, 0755, true);
            $nombre = uniqid('pub_').'.'.$ext;
            if(move_uploaded_file($_FILES['media']['tmp_name'], $carpeta.$nombre)) {
                $imagen = $nombre;
            }
        }
    }

    if(!empty($contenido) || !empty($imagen)) {
        $stmt = $pdo->prepare("INSERT INTO publicaciones (usuario_id, contenido, imagen) VALUES (?,?,?)");
        $stmt->execute([$usuario_id, $contenido, $imagen]);
    }
}
header("Location: ../home.php?msg=publicado");
exit();
?>

