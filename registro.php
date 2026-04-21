<?php
session_start();
if(isset($_SESSION['usuario'])){ header("Location: home.php"); exit(); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - EquiRed</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>

<?php include("includes/navbar.php"); ?>

<div class="auth-page">
    <div class="auth-card wide">
        <div class="auth-icon">
            <img src="img/Logo.png" alt="Logo" onerror="this.parentElement.innerHTML='👥'">
        </div>
        <h2>Regístrate en EquiRed</h2>
        <p class="auth-subtitle">Únete a nuestra comunidad y comienza a generar un cambio positivo.</p>

        <?php if(isset($_GET['error'])): ?>
            <div class="alert alert-error"><?= htmlspecialchars($_GET['error']) ?></div>
        <?php endif; ?>

        <form action="acciones/registro.php" method="POST">
            <div class="form-group">
                <label>Nombre completo</label>
                <div class="input-wrap">
                    <span class="input-icon">👤</span>
                    <input type="text" name="nombre" placeholder="Juan Pérez" required>
                </div>
            </div>

            <div class="form-group">
                <label>Correo electrónico</label>
                <div class="input-wrap">
                    <span class="input-icon">✉</span>
                    <input type="email" name="email" placeholder="tu@email.com" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Contraseña</label>
                    <div class="input-wrap">
                        <span class="input-icon">🔒</span>
                        <input type="password" name="password" placeholder="••••••••" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Confirmar contraseña</label>
                    <div class="input-wrap">
                        <span class="input-icon">🔒</span>
                        <input type="password" name="confirmar" placeholder="••••••••" required>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <span class="tipo-label">Tipo de usuario</span>
                <label class="tipo-option">
                    <input type="radio" name="rol" value="beneficiario" checked>
                    <div class="tipo-info">
                        <strong>Beneficiario</strong>
                        <span>Busco oportunidades y apoyo</span>
                    </div>
                </label>
                <label class="tipo-option">
                    <input type="radio" name="rol" value="empresa">
                    <div class="tipo-info">
                        <strong>Empresa</strong>
                        <span>Quiero ofrecer empleos inclusivos</span>
                    </div>
                </label>
                <label class="tipo-option">
                    <input type="radio" name="rol" value="profesional">
                    <div class="tipo-info">
                        <strong>Profesional</strong>
                        <span>Ofrezco asesoría psicológica o jurídica</span>
                    </div>
                </label>
            </div>

            <div class="beneficios-box">
                <h4>Al unirte a EquiRed obtendrás:</h4>
                <ul>
                    <li>Acceso a historias de superación y contenido educativo</li>
                    <li>Oportunidades laborales de empresas inclusivas</li>
                    <li>Conexión con profesionales especializados</li>
                    <li>Posibilidad de ayudar a través de donaciones</li>
                </ul>
            </div>

            <button type="submit" class="btn-auth">Crear cuenta</button>
        </form>

        <div class="auth-footer">¿Ya tienes cuenta? <a href="login.php">Ingresar</a></div>
    </div>
</div>

</body>
</html>