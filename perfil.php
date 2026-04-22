<?php
session_start();
if(!isset($_SESSION['usuario'])){ header("Location: login.php"); exit(); }
require_once("config/conexion.php");

$mi_id     = $_SESSION['usuario_id'];
$perfil_id = (int)($_GET['id'] ?? $mi_id);
$es_mio    = ($perfil_id === $mi_id);
$msg       = $_GET['msg'] ?? '';
$tab       = $_GET['tab'] ?? 'publicaciones';

// Info del perfil
$up = $pdo->prepare("SELECT * FROM usuarios WHERE id=?");
$up->execute([$perfil_id]); $perfil = $up->fetch();
if(!$perfil){ header("Location: home.php"); exit(); }

// Publicaciones del feed
$sp = $pdo->prepare("
    SELECT p.*,
        (SELECT COUNT(*) FROM likes_publicacion WHERE publicacion_id=p.id) AS total_likes,
        (SELECT COUNT(*) FROM comentarios_publicacion WHERE publicacion_id=p.id) AS total_comentarios,
        (SELECT COUNT(*) FROM likes_publicacion WHERE publicacion_id=p.id AND usuario_id=$mi_id) AS yo_di_like
    FROM publicaciones p WHERE p.usuario_id=? ORDER BY p.fecha DESC
");
$sp->execute([$perfil_id]); $pubs = $sp->fetchAll();

// Donaciones del usuario
$sd = $pdo->prepare("SELECT * FROM donaciones WHERE usuario_id=? ORDER BY fecha DESC");
$sd->execute([$perfil_id]); $dons = $sd->fetchAll();

// Total likes recibidos
$tl_q = $pdo->prepare("SELECT COALESCE(COUNT(*),0) FROM likes_publicacion lp JOIN publicaciones p ON lp.publicacion_id=p.id WHERE p.usuario_id=?");
$tl_q->execute([$perfil_id]); $total_likes = (int)$tl_q->fetchColumn();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($perfil['nombre']) ?> - EquiRed</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .perfil-page { max-width:720px; margin:30px auto; padding:0 20px; }

        /* Banner y card */
        .perfil-banner { height:180px; border-radius:16px 16px 0 0; background:linear-gradient(135deg,#7b2ff7,#a855f7); }
        .perfil-card { background:white; border-radius:0 0 16px 16px; box-shadow:0 4px 20px rgba(0,0,0,0.08); padding:0 28px 24px; margin-bottom:24px; }
        .paw { position:relative; display:inline-block; margin-top:-50px; margin-bottom:12px; }
        .pa  { width:100px; height:100px; border-radius:50%; border:4px solid white; object-fit:cover; display:block; }
        .pap { width:100px; height:100px; border-radius:50%; border:4px solid white; background:linear-gradient(135deg,#7b2ff7,#a855f7); display:flex; align-items:center; justify-content:center; font-size:40px; color:white; font-weight:800; }
        .bcf { position:absolute; bottom:4px; right:4px; width:30px; height:30px; border-radius:50%; background:#7b2ff7; color:white; border:2px solid white; display:flex; align-items:center; justify-content:center; font-size:14px; cursor:pointer; }
        .pi  { display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:12px; }
        .pn  { font-size:22px; font-weight:900; color:#1a1a2e; }
        .pr  { font-size:14px; color:#a855f7; font-weight:700; text-transform:capitalize; margin-top:2px; }
        .pd  { font-size:14px; color:#666; margin-top:8px; line-height:1.6; max-width:500px; }
        .pdv { font-size:14px; color:#ccc; font-style:italic; margin-top:8px; }
        .ps  { display:flex; gap:24px; margin-top:14px; flex-wrap:wrap; }
        .pst { text-align:center; }
        .pst strong { display:block; font-size:20px; font-weight:900; color:#7b2ff7; }
        .pst span   { font-size:12px; color:#888; }
        .bep { padding:10px 20px; background:linear-gradient(135deg,#7b2ff7,#a855f7); color:white; border:none; border-radius:10px; font-weight:700; font-size:14px; cursor:pointer; font-family:inherit; }

        /* Tabs */
        .perfil-tabs { display:flex; background:white; border-radius:14px; overflow:hidden; box-shadow:0 2px 12px rgba(0,0,0,0.06); margin-bottom:20px; }
        .ptab { flex:1; padding:14px; text-align:center; font-weight:700; font-size:14px; color:#888; border:none; background:none; cursor:pointer; font-family:inherit; border-bottom:3px solid transparent; transition:all 0.2s; }
        .ptab.active { color:#7b2ff7; border-bottom-color:#7b2ff7; background:#faf5ff; }

        /* Cards publicaciones en perfil */
        .pub-card { background:white; border-radius:14px; margin-bottom:14px; box-shadow:0 2px 12px rgba(0,0,0,0.06); overflow:hidden; }
        .pub-header { display:flex; justify-content:space-between; align-items:center; padding:14px 18px 8px; }
        .pub-time   { font-size:12px; color:#aaa; }
        .pub-del-btn { background:none; border:none; color:#aaa; cursor:pointer; font-size:13px; font-weight:600; padding:4px 8px; border-radius:8px; font-family:inherit; transition:background 0.2s,color 0.2s; }
        .pub-del-btn:hover { background:#fee2e2; color:#dc2626; }
        .pub-body   { padding:0 18px 12px; font-size:15px; color:#333; line-height:1.6; }
        .pub-media  { background:#f8f8f8; display:flex; align-items:center; justify-content:center; max-height:400px; }
        .pub-media img  { max-width:100%; max-height:400px; object-fit:contain; display:block; }
        .pub-media video { width:100%; max-height:360px; display:block; background:#000; }
        .pub-footer { padding:10px 18px; display:flex; justify-content:space-between; align-items:center; font-size:13px; color:#aaa; border-top:1px solid #f0f0f0; }
        .pub-like-btn { background:none; border:none; padding:6px 10px; font-size:13px; font-weight:700; color:#666; cursor:pointer; border-radius:8px; font-family:inherit; display:flex; align-items:center; gap:4px; transition:background 0.2s; }
        .pub-like-btn:hover { background:#f4f4f8; color:#7b2ff7; }
        .pub-like-btn.liked { color:#ef4444; }

        /* Cards donaciones en perfil */
        .don-card-p  { background:white; border-radius:14px; margin-bottom:14px; box-shadow:0 2px 12px rgba(0,0,0,0.06); overflow:hidden; }
        .don-header-p { display:flex; justify-content:space-between; align-items:center; padding:14px 18px 4px; }
        .don-titulo-p { font-size:16px; font-weight:800; color:#1a1a2e; }
        .don-desc-p   { font-size:14px; color:#555; line-height:1.6; padding:0 18px 10px; }
        .don-media-p  { background:#f8f8f8; display:flex; align-items:center; justify-content:center; max-height:360px; }
        .don-media-p img  { max-width:100%; max-height:360px; object-fit:contain; display:block; }
        .don-media-p video { width:100%; max-height:320px; display:block; background:#000; }
        .don-footer-p { padding:10px 18px; font-size:13px; color:#aaa; border-top:1px solid #f0f0f0; display:flex; justify-content:space-between; }

        /* Modales */
        .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:200; align-items:center; justify-content:center; padding:20px; }
        .modal-overlay.active { display:flex; }
        .modal { background:white; border-radius:20px; padding:36px; width:100%; max-width:480px; max-height:90vh; overflow-y:auto; }
        .modal h3 { font-size:20px; font-weight:900; margin-bottom:20px; color:#1a1a2e; }
        .modal label { display:block; font-weight:700; font-size:14px; color:#333; margin-bottom:6px; }
        .modal input,.modal textarea { width:100%; padding:12px 14px; border:1.5px solid #e8e8e8; border-radius:10px; font-size:14px; font-family:inherit; margin-bottom:14px; outline:none; background:#f9f9f9; }
        .modal input:focus,.modal textarea:focus { border-color:#7b2ff7; background:white; }
        .modal textarea { height:100px; resize:none; }
        .modal-close { float:right; background:none; border:none; font-size:22px; cursor:pointer; color:#aaa; margin-top:-8px; }
        .modal-btns { display:flex; gap:10px; margin-top:8px; }
        .bmc { flex:1; padding:12px; background:#f4f4f8; color:#555; border:none; border-radius:10px; font-weight:700; font-family:inherit; cursor:pointer; }
        .bms { flex:2; padding:12px; background:linear-gradient(135deg,#7b2ff7,#a855f7); color:white; border:none; border-radius:10px; font-weight:800; font-family:inherit; cursor:pointer; }
        .bmd { flex:2; padding:12px; background:#dc2626; color:white; border:none; border-radius:10px; font-weight:800; font-family:inherit; cursor:pointer; }
        .fl { display:flex; align-items:center; gap:8px; padding:12px 14px; border:1.5px dashed #d8b4fe; border-radius:10px; cursor:pointer; font-size:14px; color:#7b2ff7; font-weight:700; margin-bottom:14px; background:#faf5ff; }
        .fl input { display:none; }
        .ap { width:80px; height:80px; border-radius:50%; object-fit:cover; margin-bottom:14px; display:none; }

        /* Toast */
        .toast { position:fixed; top:80px; right:24px; z-index:999; padding:14px 22px; border-radius:12px; font-size:15px; font-weight:700; box-shadow:0 4px 20px rgba(0,0,0,0.15); animation:slideIn 0.3s ease,fadeOut 0.4s ease 3s forwards; }
        .toast-exito { background:#d1fae5; color:#059669; border:1px solid #a7f3d0; }
        @keyframes slideIn { from{transform:translateX(120%);opacity:0} to{transform:translateX(0);opacity:1} }
        @keyframes fadeOut { from{opacity:1} to{opacity:0;visibility:hidden} }

        .vacio { text-align:center; padding:40px; color:#aaa; }
        .vacio .icon { font-size:40px; margin-bottom:10px; }
        .tab-content { display:none; }
        .tab-content.active { display:block; }
    </style>
</head>
<body>

<?php include("includes/navbar.php"); ?>

<?php if($msg==='actualizado'): ?><div class="toast toast-exito">✅ Perfil actualizado.</div>
<?php elseif($msg==='eliminado'): ?><div class="toast toast-exito">🗑️ Publicación eliminada.</div><?php endif; ?>

<div class="perfil-page">

    <!-- Banner + card de perfil -->
    <div class="perfil-banner"></div>
    <div class="perfil-card">
        <div style="display:flex;justify-content:space-between;align-items:flex-end;flex-wrap:wrap;gap:12px">
            <div class="paw">
                <?php if(!empty($perfil['foto_perfil'])): ?>
                    <img class="pa" src="uploads/<?= htmlspecialchars($perfil['foto_perfil']) ?>" alt="">
                <?php else: ?>
                    <div class="pap"><?= strtoupper(substr($perfil['nombre'],0,1)) ?></div>
                <?php endif; ?>
                <?php if($es_mio): ?>
                    <button class="bcf" onclick="document.getElementById('mEditar').classList.add('active')" title="Cambiar foto">📷</button>
                <?php endif; ?>
            </div>
            <?php if($es_mio): ?>
                <button class="bep" onclick="document.getElementById('mEditar').classList.add('active')">✏️ Editar perfil</button>
            <?php endif; ?>
        </div>

        <div class="pi">
            <div>
                <div class="pn"><?= htmlspecialchars($perfil['nombre']) ?></div>
                <div class="pr"><?= htmlspecialchars($perfil['rol']) ?></div>
                <?php if(!empty($perfil['descripcion'])): ?>
                    <div class="pd"><?= nl2br(htmlspecialchars($perfil['descripcion'])) ?></div>
                <?php else: ?>
                    <div class="pdv"><?= $es_mio?'Agrega una descripción sobre ti...':'Sin descripción.' ?></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="ps">
            <div class="pst"><strong><?= count($pubs) ?></strong><span>Publicaciones</span></div>
            <div class="pst"><strong><?= count($dons) ?></strong><span>Donaciones</span></div>
            <div class="pst"><strong><?= $total_likes ?></strong><span>Me gusta recibidos</span></div>
        </div>
    </div>

    <!-- Tabs publicaciones / donaciones -->
    <div class="perfil-tabs">
        <button class="ptab <?= $tab==='publicaciones'?'active':'' ?>" onclick="cambiarTab('publicaciones')">
            📝 Publicaciones (<?= count($pubs) ?>)
        </button>
        <button class="ptab <?= $tab==='donaciones'?'active':'' ?>" onclick="cambiarTab('donaciones')">
            📦 Donaciones (<?= count($dons) ?>)
        </button>
    </div>

    <!-- ── Tab Publicaciones ── -->
    <div class="tab-content <?= $tab==='publicaciones'?'active':'' ?>" id="tab-publicaciones">
        <?php if(empty($pubs)): ?>
            <div class="vacio"><div class="icon">📭</div><p><?= $es_mio?'Aún no has publicado nada.':'Sin publicaciones aún.' ?></p></div>
        <?php else: foreach($pubs as $p): ?>
        <div class="pub-card" id="pp-<?= $p['id'] ?>">
            <div class="pub-header">
                <span class="pub-time"><?= t2($p['fecha']) ?></span>
                <!-- Botón eliminar visible para el dueño del perfil (todos los roles) -->
                <?php if($es_mio): ?>
                <button class="pub-del-btn" onclick="confirmarEliminar(<?= $p['id'] ?>)">🗑️ Eliminar</button>
                <?php endif; ?>
            </div>

            <?php if(!empty($p['contenido'])): ?>
                <div class="pub-body"><?= nl2br(htmlspecialchars($p['contenido'])) ?></div>
            <?php endif; ?>

            <?php if(!empty($p['imagen'])):
                $ext = strtolower(pathinfo($p['imagen'], PATHINFO_EXTENSION));
                $ev  = in_array($ext,['mp4','webm','ogg','mov']); ?>
                <?php if($ev): ?>
                    <video style="width:100%;max-height:360px;display:block;background:#000" controls>
                        <source src="uploads/<?= htmlspecialchars($p['imagen']) ?>">
                    </video>
                <?php else: ?>
                    <div class="pub-media"><img src="uploads/<?= htmlspecialchars($p['imagen']) ?>" alt=""></div>
                <?php endif; ?>
            <?php endif; ?>

            <div class="pub-footer">
                <span>❤️ <?= $p['total_likes'] ?> · 💬 <?= $p['total_comentarios'] ?></span>
                <button class="pub-like-btn <?= $p['yo_di_like']?'liked':'' ?>"
                    id="pplike-<?= $p['id'] ?>" onclick="tLP(<?= $p['id'] ?>,this)">
                    <?= $p['yo_di_like']?'❤️':'♡' ?> Me gusta
                </button>
            </div>
        </div>
        <?php endforeach; endif; ?>
    </div>

    <!-- ── Tab Donaciones ── -->
    <div class="tab-content <?= $tab==='donaciones'?'active':'' ?>" id="tab-donaciones">
        <?php if(empty($dons)): ?>
            <div class="vacio"><div class="icon">📦</div><p><?= $es_mio?'No has publicado donaciones aún.':'Sin donaciones aún.' ?></p></div>
        <?php else: foreach($dons as $d): ?>
        <div class="don-card-p">
            <div class="don-header-p">
                <?php if(!empty($d['titulo'])): ?>
                    <div class="don-titulo-p"><?= htmlspecialchars($d['titulo']) ?></div>
                <?php else: ?>
                    <div class="don-titulo-p" style="color:#aaa">Sin título</div>
                <?php endif; ?>
                <span style="font-size:12px;color:#aaa"><?= t2($d['fecha']) ?></span>
            </div>

            <?php if(!empty($d['descripcion'])): ?>
                <div class="don-desc-p"><?= nl2br(htmlspecialchars($d['descripcion'])) ?></div>
            <?php endif; ?>

            <?php if(!empty($d['imagen'])):
                $ext = strtolower(pathinfo($d['imagen'], PATHINFO_EXTENSION));
                $ev  = in_array($ext,['mp4','webm','ogg','mov']); ?>
                <?php if($ev): ?>
                    <video style="width:100%;max-height:320px;display:block;background:#000" controls>
                        <source src="uploads/<?= htmlspecialchars($d['imagen']) ?>">
                    </video>
                <?php else: ?>
                    <div class="don-media-p"><img src="uploads/<?= htmlspecialchars($d['imagen']) ?>" alt=""></div>
                <?php endif; ?>
            <?php endif; ?>

            <div class="don-footer-p">
                <span>📍 <?= htmlspecialchars($d['ciudad'] ?? 'Sin ciudad') ?></span>
                <span>📦 Donación</span>
            </div>
        </div>
        <?php endforeach; endif; ?>
    </div>

</div><!-- fin perfil-page -->

<!-- Modal editar perfil (solo si es mi perfil) -->
<?php if($es_mio): ?>
<div class="modal-overlay" id="mEditar">
    <div class="modal">
        <button class="modal-close" onclick="document.getElementById('mEditar').classList.remove('active')">✕</button>
        <h3>✏️ Editar mi perfil</h3>
        <form action="acciones/actualizar_perfil.php" method="POST" enctype="multipart/form-data">
            <label>Foto de perfil</label>
            <label class="fl">
                📷 Seleccionar foto
                <input type="file" name="foto_perfil" accept="image/*" onchange="pAv(this)">
            </label>
            <img id="ap" class="ap" src="" alt="">
            <label>Nombre</label>
            <input type="text" name="nombre" value="<?= htmlspecialchars($perfil['nombre']) ?>" required>
            <label>Descripción (cuéntanos sobre ti)</label>
            <textarea name="descripcion" placeholder="Ej: Soy diseñador apasionado por la inclusión..."><?= htmlspecialchars($perfil['descripcion']??'') ?></textarea>
            <div class="modal-btns">
                <button type="button" class="bmc" onclick="document.getElementById('mEditar').classList.remove('active')">Cancelar</button>
                <button type="submit" class="bms">Guardar cambios</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal confirmar eliminar publicación -->
<div class="modal-overlay" id="mEliminar">
    <div class="modal">
        <h3>🗑️ Eliminar publicación</h3>
        <p>¿Estás seguro que quieres eliminar esta publicación? Esta acción no se puede deshacer.</p>
        <form action="acciones/eliminar_publicacion.php" method="POST">
            <input type="hidden" name="publicacion_id" id="del-pub-id">
            <input type="hidden" name="redirect" value="perfil.php">
            <div class="modal-btns">
                <button type="button" class="bmc" onclick="document.getElementById('mEliminar').classList.remove('active')">Cancelar</button>
                <button type="submit" class="bmd">Sí, eliminar</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<footer class="footer">© 2026 EquiRed. Conectando oportunidades, construyendo igualdad.</footer>

<script>
function pAv(i){ const p=document.getElementById('ap'); if(i.files&&i.files[0]){ p.src=URL.createObjectURL(i.files[0]); p.style.display='block'; } }

function cambiarTab(t){
    document.querySelectorAll('.tab-content').forEach(c=>c.classList.remove('active'));
    document.querySelectorAll('.ptab').forEach(b=>b.classList.remove('active'));
    document.getElementById('tab-'+t).classList.add('active');
    document.querySelector(`[onclick="cambiarTab('${t}')"]`).classList.add('active');
}

function confirmarEliminar(id){
    document.getElementById('del-pub-id').value = id;
    document.getElementById('mEliminar').classList.add('active');
}

function tLP(id,btn){
    fetch('acciones/like_publicacion.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'publicacion_id='+id})
    .then(r=>r.json()).then(d=>{
        btn.classList.toggle('liked',d.liked);
        btn.innerHTML=(d.liked?'❤️':'♡')+' Me gusta';
    });
}

setTimeout(()=>{ const t=document.querySelector('.toast'); if(t) t.style.display='none'; },3500);
</script>

<?php function t2($f){ $d=time()-strtotime($f); if($d<3600) return round($d/60)." min"; if($d<86400) return "Hace ".round($d/3600)."h"; return date('d/m/Y',strtotime($f)); } ?>
</body>
</html>