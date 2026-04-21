<?php
$pagina_actual = basename($_SERVER['PHP_SELF']);
$logueado = isset($_SESSION['usuario']);
?>
<nav class="navbar">
    <a href="index.php" class="navbar-brand">
        <div class="brand-icon">
            <img src="img/Logo.png" alt="EquiRed" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'">
            <span style="display:none">👥</span>
        </div>
        <span class="brand-name">Equi<span>Red</span></span>
    </a>

    <div class="menu">
        <a href="home.php" class="<?= $pagina_actual==='home.php' ? 'active' : '' ?>">Inicio</a>
        <a href="empleos.php" class="<?= $pagina_actual==='empleos.php' ? 'active' : '' ?>">Empleos</a>
        <a href="donar.php" class="<?= $pagina_actual==='donar.php' ? 'active' : '' ?>">Donar</a>
        <a href="asesorias.php" class="<?= $pagina_actual==='asesorias.php' ? 'active' : '' ?>">Asesorías</a>
        <?php if($logueado): ?>
            <span class="nav-user">Hola, <?= htmlspecialchars($_SESSION['usuario']) ?> 👋</span>
            <a href="acciones/logout.php" class="btn-nav-logout">Cerrar sesión</a>
        <?php else: ?>
            <a href="login.php" class="btn-nav-login">Ingresar</a>
        <?php endif; ?>
    </div>
</nav>