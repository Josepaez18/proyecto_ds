<?php
session_start();
if(!isset($_SESSION['usuario'])){ header("Location: ../login.php"); exit(); }
require_once("../config/conexion.php");

$pub_id     = (int)($_POST['publicacion_id']??0);
$usuario_id = $_SESSION['usuario_id'];
$redirect   = $_POST['redirect'] ?? 'home.php';

// Validar que solo sean rutas seguras
$rutas_ok = ['home.php','perfil.php'];
if(!in_array($redirect,$rutas_ok)) $redirect='home.php';

$check=$pdo->prepare("SELECT id,imagen FROM publicaciones WHERE id=? AND usuario_id=?");
$check->execute([$pub_id,$usuario_id]);
$pub=$check->fetch();

if($pub){
    if(!empty($pub['imagen'])){$r="../uploads/".$pub['imagen'];if(file_exists($r))unlink($r);}
    $pdo->prepare("DELETE FROM publicaciones WHERE id=?")->execute([$pub_id]);
    header("Location: ../".$redirect."?msg=eliminado");
}else{
    header("Location: ../home.php");
}
exit();
?>