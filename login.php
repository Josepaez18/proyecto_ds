<?php
session_start();
if(isset($_SESSION['usuario'])){ header("Location: home.php"); exit(); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ingresar - EquiRed</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>

<?php include("includes/navbar.php"); ?>

<div class="auth-page">
    <div class="auth-card">
        <div class="auth-icon">
            <img src="img/Logo.png" alt="Logo" onerror="this.parentElement.innerHTML='👥'">
        </div>
        <h2>Ingresar a EquiRed</h2>
        <p class="auth-subtitle">Bienvenido de nuevo. Ingresa tus credenciales para continuar.</p>

        <?php if(isset($_GET['error'])): ?>
            <div class="alert alert-error"><?= htmlspecialchars($_GET['error']) ?></div>
        <?php endif; ?>

        <form action="acciones/login.php" method="POST">
            <div class="form-group">
                <label>Correo electrónico</label>
                <div class="input-wrap">
                    <span class="input-icon">✉</span>
                    <input type="email" name="email" placeholder="tu@email.com" required>
                </div>
            </div>
            <div class="form-group">
                <label>Contraseña</label>
                <div class="input-wrap">
                    <span class="input-icon">🔒</span>
                    <input type="password" name="password" placeholder="••••••••" required>
                </div>
            </div>
            <a href="#" class="forgot-link">¿Olvidaste tu contraseña?</a>
            <button type="submit" class="btn-auth">Ingresar</button>
        </form>

        <div class="divider"><span>¿No tienes cuenta?</span></div>
        <a href="registro.php" class="btn-auth-outline">Crear cuenta gratis</a>
    </div>
</div>

</body>
</html>