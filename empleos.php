<?php
session_start();
require_once("config/conexion.php");

// ── Stats dinámicos ──────────────────────────────────────
// Total empleos
$total_empleos = $pdo->query("SELECT COUNT(*) FROM empleos")->fetchColumn();
if($total_empleos == 0) $total_empleos = 6; // fallback ejemplos

// Total empresas registradas
$total_empresas = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE rol='empresa'")->fetchColumn();

// Postulaciones del usuario actual (o total si no está logueado)
$mis_postulaciones   = [];
$total_mis_post      = 0;
$rol                 = $_SESSION['rol'] ?? '';

if(isset($_SESSION['usuario_id'])) {
    $sp = $pdo->prepare("
        SELECT p.*, e.titulo, e.descripcion, u.nombre AS empresa_nombre
        FROM postulaciones p
        JOIN empleos e ON p.empleo_id = e.id
        JOIN usuarios u ON e.empresa_id = u.id
        WHERE p.usuario_id = ?
        ORDER BY p.fecha DESC
    ");
    $sp->execute([$_SESSION['usuario_id']]);
    $mis_postulaciones = $sp->fetchAll();
    $total_mis_post    = count($mis_postulaciones);
}

// Total postulaciones exitosas globales (para visitantes sin sesión)
$total_post_global = $pdo->query("SELECT COUNT(*) FROM postulaciones")->fetchColumn();

// Empleos de la BD
$stmt   = $pdo->query("SELECT e.*, u.nombre AS empresa_nombre FROM empleos e JOIN usuarios u ON e.empresa_id = u.id ORDER BY e.fecha DESC");
$empleos = $stmt->fetchAll();

$msg = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Empleos - EquiRed</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .empleos-page { max-width:1000px; margin:0 auto; padding:40px 20px; }

        /* Hero */
        .empleos-hero { text-align:center; margin-bottom:36px; }
        .empleos-hero h1 { font-size:36px; font-weight:900; color:#1a1a2e; }
        .empleos-hero h1 span { color:#7b2ff7; }
        .empleos-hero p { color:#777; font-size:15px; margin-top:10px; max-width:580px; margin-inline:auto; line-height:1.6; }

        /* Toast */
        .toast {
            position:fixed; top:80px; right:24px; z-index:999;
            padding:14px 22px; border-radius:12px;
            font-size:15px; font-weight:700; box-shadow:0 4px 20px rgba(0,0,0,0.15);
            animation: slideIn 0.3s ease, fadeOut 0.4s ease 3s forwards;
        }
        .toast-exito   { background:#d1fae5; color:#059669; border:1px solid #a7f3d0; }
        .toast-warning { background:#fef3c7; color:#d97706; border:1px solid #fde68a; }
        @keyframes slideIn { from{transform:translateX(120%);opacity:0} to{transform:translateX(0);opacity:1} }
        @keyframes fadeOut { from{opacity:1} to{opacity:0;visibility:hidden} }

        /* Buscador */
        .search-bar {
            display:flex; gap:12px; margin-bottom:28px;
            background:white; padding:14px 18px; border-radius:14px;
            box-shadow:0 2px 12px rgba(0,0,0,0.06);
        }
        .search-input-wrap { flex:1; position:relative; }
        .search-input-wrap input {
            width:100%; padding:10px 14px 10px 38px;
            border:1.5px solid #e8e8e8; border-radius:10px;
            font-size:14px; font-family:inherit; outline:none; background:#f9f9f9;
        }
        .search-input-wrap input:focus { border-color:#7b2ff7; background:white; }
        .search-icon { position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#aaa; }
        .filter-select {
            padding:10px 16px; border:1.5px solid #e8e8e8; border-radius:10px;
            font-size:14px; font-family:inherit; outline:none;
            background:#f9f9f9; color:#555; min-width:180px; cursor:pointer;
        }

        /* Stats */
        .stats-row { display:grid; grid-template-columns:repeat(3,1fr); gap:16px; margin-bottom:28px; }
        .stat-card {
            background:white; border-radius:14px; padding:20px; text-align:center;
            box-shadow:0 2px 12px rgba(0,0,0,0.06);
            transition:box-shadow 0.2s, transform 0.2s;
        }
        .stat-card.clickable {
            cursor:pointer; border:1.5px solid #f0f0f0;
        }
        .stat-card.clickable:hover {
            box-shadow:0 6px 20px rgba(123,47,247,0.15);
            transform:translateY(-2px);
            border-color:#7b2ff7;
        }
        .stat-num   { font-size:32px; font-weight:900; color:#7b2ff7; }
        .stat-label { font-size:13px; color:#888; margin-top:4px; }
        .stat-hint  { font-size:11px; color:#a855f7; margin-top:4px; font-weight:600; }

        /* Grid empleos */
        .empleos-grid { display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:40px; }
        .empleo-card {
            background:white; border-radius:16px; padding:22px;
            box-shadow:0 2px 12px rgba(0,0,0,0.06);
            display:flex; flex-direction:column; gap:12px;
            border:1.5px solid #f0f0f0; transition:border-color 0.2s, box-shadow 0.2s;
        }
        .empleo-card:hover { border-color:#7b2ff7; box-shadow:0 4px 20px rgba(123,47,247,0.1); }
        .empleo-header { display:flex; justify-content:space-between; align-items:flex-start; }
        .empleo-left   { display:flex; gap:12px; align-items:flex-start; }
        .empleo-icon   { width:46px; height:46px; border-radius:12px; background:#f3e8ff; display:flex; align-items:center; justify-content:center; font-size:22px; flex-shrink:0; }
        .empleo-titulo  { font-size:16px; font-weight:800; color:#1a1a2e; margin-bottom:2px; }
        .empleo-empresa { font-size:13px; font-weight:700; color:#7b2ff7; }
        .badge-tipo { font-size:11px; font-weight:700; padding:4px 10px; border-radius:50px; background:#f3e8ff; color:#7b2ff7; white-space:nowrap; flex-shrink:0; }
        .empleo-meta { display:flex; flex-direction:column; gap:5px; }
        .empleo-meta span { font-size:13px; color:#888; display:flex; align-items:center; gap:6px; }
        .empleo-desc { font-size:14px; color:#555; line-height:1.6; }
        .btn-postular {
            width:100%; padding:11px; background:linear-gradient(135deg,#7b2ff7,#a855f7);
            color:white; border:none; border-radius:10px;
            font-size:14px; font-weight:800; font-family:inherit; cursor:pointer; transition:opacity 0.2s;
        }
        .btn-postular:hover { opacity:0.9; }

        /* Botón publicar empleo */
        .btn-publicar-empleo {
            display:inline-flex; align-items:center; gap:8px;
            background:linear-gradient(135deg,#7b2ff7,#a855f7);
            color:white; padding:11px 24px; border-radius:10px;
            font-weight:800; font-size:14px; border:none; cursor:pointer;
            font-family:inherit; margin-bottom:28px; transition:opacity 0.2s;
        }
        .btn-publicar-empleo:hover { opacity:0.9; }

        /* ── Modal genérico ── */
        .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:200; align-items:center; justify-content:center; padding:20px; }
        .modal-overlay.active { display:flex; }
        .modal { background:white; border-radius:20px; padding:36px; width:100%; max-width:520px; max-height:90vh; overflow-y:auto; }
        .modal h3 { font-size:20px; font-weight:900; margin-bottom:20px; color:#1a1a2e; }
        .modal input, .modal textarea, .modal select {
            width:100%; padding:12px 14px; border:1.5px solid #e8e8e8;
            border-radius:10px; font-size:14px; font-family:inherit;
            margin-bottom:14px; outline:none; background:#f9f9f9;
        }
        .modal input:focus, .modal textarea:focus, .modal select:focus { border-color:#7b2ff7; background:white; }
        .modal textarea { height:90px; resize:none; }
        .modal-btns { display:flex; gap:10px; margin-top:4px; }
        .btn-modal-cancel { flex:1; padding:12px; background:#f4f4f8; color:#555; border:none; border-radius:10px; font-weight:700; font-family:inherit; cursor:pointer; }
        .btn-modal-send   { flex:2; padding:12px; background:linear-gradient(135deg,#7b2ff7,#a855f7); color:white; border:none; border-radius:10px; font-weight:800; font-family:inherit; cursor:pointer; }
        .modal-close { float:right; background:none; border:none; font-size:22px; cursor:pointer; color:#aaa; margin-top:-8px; }

        /* ── Modal mis postulaciones ── */
        .post-item {
            display:flex; justify-content:space-between; align-items:center;
            padding:14px 16px; border-radius:12px; margin-bottom:10px;
            border:1.5px solid #f0f0f0; background:#fafafa;
        }
        .post-item-info strong { display:block; font-size:14px; font-weight:800; color:#1a1a2e; }
        .post-item-info span   { font-size:12px; color:#888; }
        .estado-badge { padding:6px 14px; border-radius:50px; font-size:12px; font-weight:800; }
        .estado-pendiente  { background:#fef3c7; color:#d97706; }
        .estado-aceptado   { background:#d1fae5; color:#059669; }
        .estado-rechazado  { background:#fee2e2; color:#dc2626; }
        .no-postulaciones  { text-align:center; padding:30px; color:#aaa; font-size:15px; }
    </style>
</head>
<body>

<?php include("includes/navbar.php"); ?>

<!-- Toasts -->
<?php if($msg==='exito'): ?>
    <div class="toast toast-exito">✅ ¡Postulación enviada exitosamente!</div>
<?php elseif($msg==='ya_postulado'): ?>
    <div class="toast toast-warning">⚠️ Ya te postulaste a este empleo.</div>
<?php elseif($msg==='empleo_publicado'): ?>
    <div class="toast toast-exito">✅ ¡Empleo publicado exitosamente!</div>
<?php endif; ?>

<div class="empleos-page">

    <div class="empleos-hero">
        <h1>Empleos <span>Inclusivos</span></h1>
        <p>Lista extensa de oportunidades laborales en empresas comprometidas con la diversidad y la inclusión. Todos merecen una oportunidad.</p>
    </div>

    <!-- Botón publicar empleo SOLO para empresas -->
    <?php if($rol==='empresa'): ?>
    <div style="text-align:right; margin-bottom:10px;">
        <button class="btn-publicar-empleo" onclick="document.getElementById('modalEmpleo').classList.add('active')">
            ➕ Publicar empleo
        </button>
    </div>
    <?php endif; ?>

    <!-- Buscador -->
    <div class="search-bar">
        <div class="search-input-wrap">
            <span class="search-icon">🔍</span>
            <input type="text" id="buscar" placeholder="Buscar por puesto o empresa..." oninput="filtrarEmpleos()">
        </div>
        <select class="filter-select" id="filtroTipo" onchange="filtrarEmpleos()">
            <option value="">Todos los empleos</option>
            <option value="Tiempo completo">Tiempo completo</option>
            <option value="Media jornada">Media jornada</option>
            <option value="Freelance">Freelance</option>
        </select>
    </div>

    <!-- Stats dinámicos -->
    <div class="stats-row">

        <!-- Empleos disponibles -->
        <div class="stat-card">
            <div class="stat-num"><?= $total_empleos ?></div>
            <div class="stat-label">Empleos disponibles</div>
        </div>

        <!-- Empresas registradas (dinámico) -->
        <div class="stat-card">
            <div class="stat-num"><?= $total_empresas > 0 ? $total_empresas : '150+' ?></div>
            <div class="stat-label">Empresas</div>
        </div>

        <!-- Postulaciones: clickeable si está logueado -->
        <?php if(isset($_SESSION['usuario']) && $rol !== 'empresa'): ?>
        <div class="stat-card clickable" onclick="document.getElementById('modalPostulaciones').classList.add('active')" title="Ver mis postulaciones">
            <div class="stat-num"><?= $total_mis_post ?></div>
            <div class="stat-label">Mis postulaciones</div>
            <div class="stat-hint">👆 Ver detalle</div>
        </div>
        <?php else: ?>
        <div class="stat-card">
            <div class="stat-num"><?= $total_post_global > 0 ? $total_post_global : '500+' ?></div>
            <div class="stat-label">Postulaciones exitosas</div>
        </div>
        <?php endif; ?>

    </div>

    <!-- Grid de empleos -->
    <div class="empleos-grid" id="empleosGrid">
        <?php if(empty($empleos)):
            $ejemplos = [
                ['🎧','Operador(a) Telefónico(a)','TechCall Solutions','Bogotá, Colombia','$2,500,000 - $3,200,000/mes','Hace 2 días','Tiempo completo','Buscamos operadores telefónicos con o sin experiencia. Ofrecemos capacitación completa y un ambiente inclusivo.'],
                ['💻','Desarrollador(a) Web','Digital Inclusion','Medellín, Colombia (Remoto)','$4,000,000 - $6,000,000/mes','Hace 5 días','Tiempo completo','Únete a nuestro equipo diverso de desarrollo. Valoramos el talento sin importar tu origen.'],
                ['📋','Auxiliar Administrativo(a)','Empresa Inclusiva SA','Cali, Colombia','$1,800,000 - $2,200,000/mes','Hace 3 días','Media jornada','Empresa comprometida con la inclusión busca auxiliar con actitud positiva y ganas de crecer.'],
                ['🎨','Diseñador(a) Gráfico(a)','Creative Minds Studio','Barranquilla, Colombia','$3,000,000 - $4,500,000/mes','Hace 1 día','Freelance','Estudio creativo busca diseñadores con portafolio. Abiertos a personas con discapacidad.'],
                ['👥','Asistente de Recursos Humanos','EquiHR Consulting','Cartagena, Colombia','$2,800,000 - $3,800,000/mes','Hace 4 días','Tiempo completo','Consultora de RRHH especializada en diversidad busca asistente comprometido con la inclusión.'],
                ['📞','Atención al Cliente','ServiPlus','Bucaramanga, Colombia','$2,200,000 - $2,800,000/mes','Hace 1 día','Tiempo completo','Empresa líder en servicio al cliente busca personas con ganas de aprender y crecer.'],
            ];
            foreach($ejemplos as $e): ?>
            <div class="empleo-card" data-titulo="<?= $e[1] ?>" data-empresa="<?= $e[2] ?>" data-tipo="<?= $e[6] ?>">
                <div class="empleo-header">
                    <div class="empleo-left">
                        <div class="empleo-icon"><?= $e[0] ?></div>
                        <div>
                            <div class="empleo-titulo"><?= $e[1] ?></div>
                            <div class="empleo-empresa"><?= $e[2] ?></div>
                        </div>
                    </div>
                    <span class="badge-tipo"><?= $e[6] ?></span>
                </div>
                <div class="empleo-meta">
                    <span>📍 <?= $e[3] ?></span>
                    <span>💲 <?= $e[4] ?></span>
                    <span>🕐 <?= $e[5] ?></span>
                </div>
                <div class="empleo-desc"><?= $e[7] ?></div>
                <?php if(isset($_SESSION['usuario']) && $rol !== 'empresa'): ?>
                    <form action="acciones/postular.php" method="POST">
                        <input type="hidden" name="empleo_titulo" value="<?= htmlspecialchars($e[1]) ?>">
                        <button type="submit" class="btn-postular">Postularme</button>
                    </form>
                <?php elseif(!isset($_SESSION['usuario'])): ?>
                    <a href="login.php" class="btn-postular" style="display:block;text-align:center;padding:11px;text-decoration:none;">Postularme</a>
                <?php endif; ?>
            </div>
            <?php endforeach;
        else:
            $iconos=['💼','💻','🎧','📋','🎨','📞','👥','🔧']; $i=0;
            foreach($empleos as $emp): ?>
            <div class="empleo-card" data-titulo="<?= htmlspecialchars($emp['titulo']) ?>" data-empresa="<?= htmlspecialchars($emp['empresa_nombre']) ?>" data-tipo="Tiempo completo">
                <div class="empleo-header">
                    <div class="empleo-left">
                        <div class="empleo-icon"><?= $iconos[$i%count($iconos)] ?></div>
                        <div>
                            <div class="empleo-titulo"><?= htmlspecialchars($emp['titulo']) ?></div>
                            <div class="empleo-empresa"><?= htmlspecialchars($emp['empresa_nombre']) ?></div>
                        </div>
                    </div>
                    <span class="badge-tipo">Tiempo completo</span>
                </div>
                <div class="empleo-meta">
                    <span>🕐 Hace <?= t($emp['fecha']) ?></span>
                </div>
                <div class="empleo-desc"><?= htmlspecialchars(substr($emp['descripcion'],0,120)) ?>...</div>
                <?php if(isset($_SESSION['usuario']) && $rol !== 'empresa'): ?>
                    <form action="acciones/postular.php" method="POST">
                        <input type="hidden" name="empleo_id" value="<?= $emp['id'] ?>">
                        <button type="submit" class="btn-postular">Postularme</button>
                    </form>
                <?php elseif(!isset($_SESSION['usuario'])): ?>
                    <a href="login.php" class="btn-postular" style="display:block;text-align:center;padding:11px;text-decoration:none;">Postularme</a>
                <?php endif; ?>
            </div>
            <?php $i++; endforeach;
        endif; ?>
    </div>

</div><!-- fin empleos-page -->

<!-- ── Modal publicar empleo (solo empresa) ── -->
<?php if($rol==='empresa'): ?>
<div class="modal-overlay" id="modalEmpleo">
    <div class="modal">
        <button class="modal-close" onclick="document.getElementById('modalEmpleo').classList.remove('active')">✕</button>
        <h3>💼 Publicar empleo</h3>
        <form action="acciones/publicar_empleo.php" method="POST">
            <input type="text" name="titulo" placeholder="Título del puesto" required>
            <textarea name="descripcion" placeholder="Describe el empleo, requisitos y beneficios..." required></textarea>
            <select name="tipo">
                <option value="Tiempo completo">Tiempo completo</option>
                <option value="Media jornada">Media jornada</option>
                <option value="Freelance">Freelance</option>
            </select>
            <input type="text" name="ciudad" placeholder="📍 Ciudad (ej: Bogotá, Colombia)">
            <input type="text" name="salario" placeholder="💲 Salario (ej: $2,000,000 - $3,000,000/mes)">
            <div class="modal-btns">
                <button type="button" class="btn-modal-cancel" onclick="document.getElementById('modalEmpleo').classList.remove('active')">Cancelar</button>
                <button type="submit" class="btn-modal-send">Publicar empleo</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- ── Modal mis postulaciones ── -->
<?php if(isset($_SESSION['usuario']) && $rol !== 'empresa'): ?>
<div class="modal-overlay" id="modalPostulaciones">
    <div class="modal">
        <button class="modal-close" onclick="document.getElementById('modalPostulaciones').classList.remove('active')">✕</button>
        <h3>📋 Mis postulaciones (<?= $total_mis_post ?>)</h3>

        <?php if(empty($mis_postulaciones)): ?>
            <div class="no-postulaciones">
                <div style="font-size:40px;margin-bottom:12px;">📭</div>
                <p>Aún no te has postulado a ningún empleo.</p>
                <p style="font-size:13px;margin-top:6px;">¡Explora los empleos y postúlate!</p>
            </div>
        <?php else: ?>
            <?php foreach($mis_postulaciones as $mp): ?>
            <div class="post-item">
                <div class="post-item-info">
                    <strong><?= htmlspecialchars($mp['titulo']) ?></strong>
                    <span><?= htmlspecialchars($mp['empresa_nombre']) ?> · <?= date('d/m/Y', strtotime($mp['fecha'])) ?></span>
                </div>
                <span class="estado-badge estado-<?= $mp['estado'] ?>">
                    <?php
                    if($mp['estado']==='aceptado')    echo '✅ Aceptado';
                    elseif($mp['estado']==='rechazado') echo '❌ Rechazado';
                    else echo '⏳ Pendiente';
                    ?>
                </span>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <div class="modal-btns" style="margin-top:16px;">
            <button class="btn-modal-cancel" onclick="document.getElementById('modalPostulaciones').classList.remove('active')">Cerrar</button>
        </div>
    </div>
</div>
<?php endif; ?>

<footer class="footer">© 2026 EquiRed. Conectando oportunidades, construyendo igualdad.</footer>

<script>
function filtrarEmpleos() {
    const buscar = document.getElementById('buscar').value.toLowerCase();
    const tipo   = document.getElementById('filtroTipo').value.toLowerCase();
    document.querySelectorAll('.empleo-card').forEach(card => {
        const okBuscar = card.dataset.titulo.toLowerCase().includes(buscar) || card.dataset.empresa.toLowerCase().includes(buscar);
        const okTipo   = tipo === '' || card.dataset.tipo.toLowerCase().includes(tipo);
        card.style.display = (okBuscar && okTipo) ? '' : 'none';
    });
}
setTimeout(() => { const t = document.querySelector('.toast'); if(t) t.style.display='none'; }, 3500);
</script>

<?php function t($f){ $d=time()-strtotime($f); if($d<3600) return round($d/60)."min"; if($d<86400) return round($d/3600)."h"; return round($d/86400)." días"; } ?>
</body>
</html>