<?php
session_start();
require_once("../config/conexion.php");

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre   = trim($_POST['nombre']);
    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirmar = trim($_POST['confirmar']);
    $rol      = $_POST['rol'] ?? 'beneficiario';

    if(empty($nombre) || empty($email) || empty($password)) {
        header("Location: ../registro.php?error=Completa todos los campos");
        exit();
    }
    if($password !== $confirmar) {
        header("Location: ../registro.php?error=Las contraseñas no coinciden");
        exit();
    }
    if(strlen($password) < 6) {
        header("Location: ../registro.php?error=La contraseña debe tener al menos 6 caracteres");
        exit();
    }

    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    if($stmt->fetch()) {
        header("Location: ../registro.php?error=El correo ya está registrado");
        exit();
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password, rol) VALUES (?,?,?,?)");
    $stmt->execute([$nombre, $email, $hash, $rol]);

    $_SESSION['usuario']    = $nombre;
    $_SESSION['usuario_id'] = $pdo->lastInsertId();
    $_SESSION['rol']        = $rol;
    header("Location: ../home.php");
    exit();
}
header("Location: ../registro.php");
exit();
?>