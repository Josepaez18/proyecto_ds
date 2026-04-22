<?php
session_start();
require_once("../config/conexion.php");
header('Content-Type: application/json');

if(!isset($_SESSION['usuario_id'])){ echo json_encode(['error'=>'no_auth']); exit(); }

$pub_id     = (int)($_POST['publicacion_id'] ?? 0);
$usuario_id = $_SESSION['usuario_id'];

$check = $pdo->prepare("SELECT id FROM likes_publicacion WHERE publicacion_id=? AND usuario_id=?");
$check->execute([$pub_id, $usuario_id]);

if($check->fetch()) {
    $pdo->prepare("DELETE FROM likes_publicacion WHERE publicacion_id=? AND usuario_id=?")->execute([$pub_id, $usuario_id]);
    $liked = false;
} else {
    $pdo->prepare("INSERT INTO likes_publicacion (publicacion_id, usuario_id) VALUES (?,?)")->execute([$pub_id, $usuario_id]);
    $liked = true;
}

$total = $pdo->prepare("SELECT COUNT(*) FROM likes_publicacion WHERE publicacion_id=?");
$total->execute([$pub_id]);
echo json_encode(['liked'=>$liked, 'total_likes'=>(int)$total->fetchColumn()]);
?>