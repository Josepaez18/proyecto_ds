<?php
session_start();
require_once("../config/conexion.php");

header('Content-Type: application/json');

if(!isset($_SESSION['usuario_id'])){
    echo json_encode(['error'=>'no_auth']); exit();
}

$donacion_id = (int)($_POST['donacion_id'] ?? 0);
$usuario_id  = $_SESSION['usuario_id'];

// Verificar si ya dio like
$check = $pdo->prepare("SELECT id FROM likes_donacion WHERE donacion_id=? AND usuario_id=?");
$check->execute([$donacion_id, $usuario_id]);

if($check->fetch()) {
    // Quitar like
    $pdo->prepare("DELETE FROM likes_donacion WHERE donacion_id=? AND usuario_id=?")->execute([$donacion_id, $usuario_id]);
    $liked = false;
} else {
    // Dar like
    $pdo->prepare("INSERT INTO likes_donacion (donacion_id, usuario_id) VALUES (?,?)")->execute([$donacion_id, $usuario_id]);
    $liked = true;
}

$total = $pdo->prepare("SELECT COUNT(*) FROM likes_donacion WHERE donacion_id=?");
$total->execute([$donacion_id]);

echo json_encode(['liked'=>$liked, 'total_likes'=>(int)$total->fetchColumn()]);
?>