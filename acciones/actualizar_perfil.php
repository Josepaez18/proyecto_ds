<?php
session_start();
if(!isset($_SESSION['usuario'])){ header("Location: ../login.php"); exit(); }
require_once("../config/conexion.php");

$usuario_id  = $_SESSION['usuario_id'];
$nombre      = trim($_POST['nombre'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$foto        = null;

// Subir nueva foto de perfil
if(!empty($_FILES['foto_perfil']['name'])) {
    $ext = strtolower(pathinfo($_FILES['foto_perfil']['name'], PATHINFO_EXTENSION));
    $permitidos = ['jpg','jpeg','png','gif','webp'];
    if(in_array($ext, $permitidos)) {
        $carpeta = "../uploads/";
        if(!is_dir($carpeta)) mkdir($carpeta, 0755, true);

        // Eliminar foto anterior
        $old = $pdo->prepare("SELECT foto_perfil FROM usuarios WHERE id=?");
        $old->execute([$usuario_id]);
        $oldFoto = $old->fetchColumn();
        if($oldFoto && file_exists($carpeta.$oldFoto)) unlink($carpeta.$oldFoto);

        $nombre_archivo = uniqid('avatar_').'.'.$ext;
        if(move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $carpeta.$nombre_archivo)) {
            $foto = $nombre_archivo;
        }
    }
}

if($foto) {
    $stmt = $pdo->prepare("UPDATE usuarios SET nombre=?, descripcion=?, foto_perfil=? WHERE id=?");
    $stmt->execute([$nombre, $descripcion, $foto, $usuario_id]);
} else {
    $stmt = $pdo->prepare("UPDATE usuarios SET nombre=?, descripcion=? WHERE id=?");
    $stmt->execute([$nombre, $descripcion, $usuario_id]);
}

// Actualizar nombre en sesión
$_SESSION['usuario'] = $nombre;

header("Location: ../perfil.php?msg=actualizado");
exit();
?>