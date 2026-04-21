<?php
session_start();
if(!isset($_SESSION['usuario'])){ header("Location: ../login.php"); exit(); }
require_once("../config/conexion.php");

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contenido  = trim($_POST['contenido']);
    $usuario_id = $_SESSION['usuario_id'];

    if(!empty($contenido)) {
        $stmt = $pdo->prepare("INSERT INTO publicaciones (usuario_id, contenido) VALUES (?,?)");
        $stmt->execute([$usuario_id, $contenido]);
    }
}
header("Location: ../home.php");
exit();
?>

