<?php
session_start();
require_once("../config/conexion.php");

header('Content-Type: application/json');

if(!isset($_SESSION['usuario_id'])){
    echo json_encode(['ok'=>false]); exit();
}

$donacion_id = (int)($_POST['donacion_id'] ?? 0);
$comentario  = trim($_POST['comentario'] ?? '');
$usuario_id  = $_SESSION['usuario_id'];

if(empty($comentario) || $donacion_id === 0){
    echo json_encode(['ok'=>false]); exit();
}

$stmt = $pdo->prepare("INSERT INTO comentarios_donacion (donacion_id, usuario_id, comentario) VALUES (?,?,?)");
$stmt->execute([$donacion_id, $usuario_id, $comentario]);

// Total solicitudes para actualizar stats
$sol = $pdo->prepare("SELECT COUNT(*) FROM solicitudes_donacion WHERE donacion_id=?");
$sol->execute([$donacion_id]);

echo json_encode([
    'ok'          => true,
    'nombre'      => htmlspecialchars($_SESSION['usuario']),
    'inicial'     => strtoupper(substr($_SESSION['usuario'], 0, 1)),
    'solicitudes' => (int)$sol->fetchColumn()
]);
?>