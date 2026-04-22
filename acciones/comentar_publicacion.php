<?php
session_start();
require_once("../config/conexion.php");
header('Content-Type: application/json');

if(!isset($_SESSION['usuario_id'])){ echo json_encode(['ok'=>false]); exit(); }

$pub_id     = (int)($_POST['publicacion_id'] ?? 0);
$comentario = trim($_POST['comentario'] ?? '');
$usuario_id = $_SESSION['usuario_id'];

if(empty($comentario) || $pub_id===0){ echo json_encode(['ok'=>false]); exit(); }

$stmt = $pdo->prepare("INSERT INTO comentarios_publicacion (publicacion_id, usuario_id, comentario) VALUES (?,?,?)");
$stmt->execute([$pub_id, $usuario_id, $comentario]);

echo json_encode([
    'ok'      => true,
    'nombre'  => htmlspecialchars($_SESSION['usuario']),
    'inicial' => strtoupper(substr($_SESSION['usuario'],0,1))
]);
?>