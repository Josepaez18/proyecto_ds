<?php
session_start();
if(!isset($_SESSION['usuario'])){ header("Location: ../login.php"); exit(); }
require_once("../config/conexion.php");

$donacion_id = (int)($_POST['donacion_id'] ?? 0);
$usuario_id  = $_SESSION['usuario_id'];

// Verificar que la donación pertenece al usuario
$check = $pdo->prepare("SELECT id, imagen FROM donaciones WHERE id=? AND usuario_id=?");
$check->execute([$donacion_id, $usuario_id]);
$donacion = $check->fetch();

if($donacion) {
    // Eliminar archivo de imagen/video si existe
    if(!empty($donacion['imagen'])) {
        $ruta = "../uploads/" . $donacion['imagen'];
        if(file_exists($ruta)) unlink($ruta);
    }
    // Eliminar donación (las FK CASCADE eliminan likes, comentarios y solicitudes)
    $pdo->prepare("DELETE FROM donaciones WHERE id=?")->execute([$donacion_id]);
    header("Location: ../donar.php?msg=eliminada");
} else {
    header("Location: ../donar.php?msg=error");
}
exit();
?>