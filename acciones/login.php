<?php
session_start();
require_once("../config/conexion.php");

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);

    if(empty($email) || empty($password)) {
        header("Location: ../login.php?error=Completa todos los campos");
        exit();
    }

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch();

    if($usuario && password_verify($password, $usuario['password'])) {
        $_SESSION['usuario']    = $usuario['nombre'];
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['rol']        = $usuario['rol'];
        header("Location: ../home.php");
        exit();
    } else {
        header("Location: ../login.php?error=Correo o contraseña incorrectos");
        exit();
    }
}
header("Location: ../login.php");
exit();
?>