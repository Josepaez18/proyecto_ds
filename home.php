<?php
session_start();
if(!isset($_SESSION['usuario'])){ header("Location: login.php"); exit(); }
require_once("config/conexion.php");

$stmt = $pdo->query("SELECT p.*, u.nombre, u.rol FROM publicaciones p JOIN usuarios u ON p.usuario_id = u.id ORDER BY p.fecha DESC LIMIT 20");
$publicaciones = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio - EquiRed</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>

<?php include("includes/navbar.php"); ?>

<div class="feed-container">

    <div class="home-banner">
        <h2>Construyendo Igualdad Juntos</h2>
        <p>Buscamos construir una sociedad más justa donde todas las personas tengan las mismas oportunidades, sin importar su origen, condición económica, género o cultura. Cada mensaje es una invitación a creer en un futuro más inclusivo.</p>
    </div>

    <!-- Caja publicar -->
    <div class="post-box">
        <div class="post-avatar-sm">😊</div>
        <div class="post-box-right">
            <form action="acciones/publicar.php" method="POST">
                <textarea name="contenido" placeholder="¿Qué quieres compartir hoy?"></textarea>
                <div class="post-box-actions">
                    <div class="post-box-media">
                        <button type="button">📷 Foto</button>
                        <button type="button">🎥 Video</button>
                    </div>
                    <button type="submit" class="btn-publicar">Publicar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Publicaciones -->
    <?php if(empty($publicaciones)): ?>
        <div class="empty-state">
            <div class="empty-icon">📝</div>
            <p>Aún no hay publicaciones. ¡Sé el primero en compartir algo!</p>
        </div>
    <?php else: ?>
        <?php foreach($publicaciones as $pub): ?>
        <div class="post-card">
            <div class="post-card-header">
                <div class="post-card-user">
                    <div class="post-avatar">😊</div>
                    <div>
                        <strong><?= htmlspecialchars($pub['nombre']) ?></strong>
                        <span>Hace <?= tiempo_transcurrido($pub['fecha']) ?></span>
                    </div>
                </div>
                <span class="post-menu">⋮</span>
            </div>
            <div class="post-card-content">
                <p><?= nl2br(htmlspecialchars($pub['contenido'])) ?></p>
            </div>
            <?php if(!empty($pub['imagen'])): ?>
            <div class="post-card-image">
                <img src="<?= htmlspecialchars($pub['imagen']) ?>" alt="Imagen publicación">
            </div>
            <?php endif; ?>
            <div class="post-card-stats">
                <span>0 me gusta</span>
                <span>0 comentarios</span>
            </div>
            <div class="post-card-actions">
                <button class="post-action-btn">♡ Me gusta</button>
                <button class="post-action-btn">💬 Comentar</button>
                <button class="post-action-btn">↗ Compartir</button>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <button class="btn-cargar-mas">Cargar más publicaciones</button>
</div>

<footer class="footer">© 2026 EquiRed. Conectando oportunidades, construyendo igualdad.</footer>

<?php
function tiempo_transcurrido($fecha) {
    $diff = time() - strtotime($fecha);
    if($diff < 60) return "unos segundos";
    if($diff < 3600) return round($diff/60) . " minutos";
    if($diff < 86400) return round($diff/3600) . " horas";
    return round($diff/86400) . " días";
}
?>
</body>
</html>