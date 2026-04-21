<?php
session_start();
require_once("config/conexion.php");

$tab = $_GET['tab'] ?? 'psicologica';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asesorías - EquiRed</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .asesorias-page { max-width:1000px; margin:0 auto; padding:40px 20px; }

        .asesorias-hero { text-align:center; margin-bottom:32px; }
        .asesorias-hero h1 { font-size:36px; font-weight:900; color:#1a1a2e; }
        .asesorias-hero h1 span { color:#7b2ff7; }
        .asesorias-hero p { color:#777; font-size:15px; margin-top:10px; max-width:560px; margin-inline:auto; line-height:1.6; }

        /* Tabs */
        .tabs-row { display:flex; justify-content:center; margin-bottom:36px; }
        .tabs-wrap { background:#f0e8ff; border-radius:50px; padding:5px; display:inline-flex; gap:4px; }
        .tab-btn {
            padding:10px 24px; border-radius:50px; border:none;
            font-size:14px; font-weight:700; cursor:pointer; font-family:inherit;
            background:transparent; color:#888; transition:all 0.2s;
            display:flex; align-items:center; gap:6px;
        }
        .tab-btn.active { background:linear-gradient(135deg,#7b2ff7,#a855f7); color:white; }

        /* Sección profesionales */
        .seccion-titulo { margin-bottom:20px; }
        .seccion-titulo h2 { font-size:22px; font-weight:900; color:#1a1a2e; }
        .seccion-titulo p { font-size:14px; color:#888; margin-top:4px; }

        /* Grid profesionales */
        .profesionales-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:20px; margin-bottom:36px; }

        .prof-card {
            background:white; border-radius:16px; padding:26px 20px;
            box-shadow:0 2px 12px rgba(0,0,0,0.06);
            text-align:center; border:1.5px solid #f0f0f0;
            transition:border-color 0.2s, box-shadow 0.2s;
        }
        .prof-card:hover { border-color:#7b2ff7; box-shadow:0 4px 20px rgba(123,47,247,0.1); }

        .prof-avatar {
            width:70px; height:70px; background:#f3e8ff;
            border-radius:50%; display:flex; align-items:center;
            justify-content:center; font-size:32px; margin:0 auto 14px;
        }
        .prof-nombre { font-size:17px; font-weight:900; color:#1a1a2e; margin-bottom:4px; }
        .prof-especialidad { font-size:13px; font-weight:700; color:#7b2ff7; margin-bottom:10px; }

        .prof-disponible {
            display:inline-block; padding:4px 12px; border-radius:50px;
            font-size:12px; font-weight:700; margin-bottom:10px;
        }
        .disponible { background:#d1fae5; color:#059669; }
        .proximamente { background:#fee2e2; color:#dc2626; }

        .prof-rating { font-size:13px; color:#555; margin-bottom:12px; }
        .prof-rating span { color:#f59e0b; }

        .prof-tags { display:flex; flex-wrap:wrap; gap:6px; justify-content:center; margin-bottom:16px; }
        .prof-tag {
            padding:4px 10px; background:#f4f4f8; color:#555;
            border-radius:50px; font-size:11px; font-weight:600;
        }

        .btn-cita {
            width:100%; padding:11px; background:linear-gradient(135deg,#7b2ff7,#a855f7);
            color:white; border:none; border-radius:10px;
            font-size:14px; font-weight:800; font-family:inherit; cursor:pointer;
            transition:opacity 0.2s;
        }
        .btn-cita:hover { opacity:0.9; }
        .btn-cita:disabled { background:#e8e8e8; color:#aaa; cursor:not-allowed; }

        /* Banner profesional */
        .prof-banner {
            background:#faf5ff; border-radius:14px;
            padding:22px 28px; display:flex;
            align-items:center; justify-content:space-between; gap:20px;
        }
        .prof-banner-left { display:flex; align-items:center; gap:14px; }
        .prof-banner-icon { font-size:28px; }
        .prof-banner-text strong { display:block; font-size:16px; font-weight:800; color:#1a1a2e; }
        .prof-banner-text span { font-size:13px; color:#888; }
        .btn-publicar-servicio {
            background:linear-gradient(135deg,#7b2ff7,#a855f7); color:white;
            border:none; padding:11px 22px; border-radius:10px;
            font-weight:800; font-size:14px; font-family:inherit;
            cursor:pointer; white-space:nowrap; transition:opacity 0.2s;
        }
        .btn-publicar-servicio:hover { opacity:0.9; }
    </style>
</head>
<body>

<?php include("includes/navbar.php"); ?>

<div class="asesorias-page">

    <div class="asesorias-hero">
        <h1>Asesorías <span>Profesionales</span></h1>
        <p>Encuentra apoyo con nuestros especialistas. Terapeutas y abogados comprometidos brindando asesoría profesional y apoyo orientado a personas en situación vulnerable.</p>
    </div>

    <!-- Tabs -->
    <div class="tabs-row">
        <div class="tabs-wrap">
            <button class="tab-btn <?= $tab==='psicologica' ? 'active':'' ?>"
                onclick="cambiarTab('psicologica')">
                🧠 Asesoría Psicológica
            </button>
            <button class="tab-btn <?= $tab==='juridica' ? 'active':'' ?>"
                onclick="cambiarTab('juridica')">
                ⚖️ Asesoría Jurídica
            </button>
        </div>
    </div>

    <!-- Tab Psicológica -->
    <div id="tab-psicologica" class="<?= $tab==='psicologica' ? '' : 'hidden' ?>">
        <div class="seccion-titulo">
            <h2>Psicólogos disponibles</h2>
            <p>Todos nuestros profesionales están certificados y tienen experiencia en casos de discriminación e inclusión.</p>
        </div>
        <div class="profesionales-grid">
            <?php
            $psicologos = [
                ['🧑‍⚕️','Dra. María González','Psicología Clínica','disponible','4.9','127',['Ansiedad','Depresión','Trauma','Discriminación']],
                ['🧑‍⚕️','Dr. Carlos Ruiz','Psicología Social','disponible','4.8','95',['Autoestima','Identidad','Inclusión social','LGBTIQ+']],
                ['🧑‍⚕️','Dra. Ana Martínez','Terapia de pareja y familia','proximamente','5','103',['Conflictos familiares','Diversidad','Comunicación']],
            ];
            foreach($psicologos as $p): ?>
            <div class="prof-card">
                <div class="prof-avatar"><?= $p[0] ?></div>
                <div class="prof-nombre"><?= $p[1] ?></div>
                <div class="prof-especialidad"><?= $p[2] ?></div>
                <span class="prof-disponible <?= $p[3] ?>"><?= $p[3]==='disponible' ? 'Disponible' : 'Próximamente' ?></span>
                <div class="prof-rating"><span>⭐</span> <?= $p[4] ?> (<?= $p[5] ?> reseñas)</div>
                <div class="prof-tags">
                    <?php foreach($p[6] as $tag): ?><span class="prof-tag"><?= $tag ?></span><?php endforeach; ?>
                </div>
                <?php if($p[3]==='disponible'): ?>
                    <?php if(isset($_SESSION['usuario'])): ?>
                        <button class="btn-cita" onclick="alert('Solicitud de cita enviada a <?= $p[1] ?>')">Solicitar cita</button>
                    <?php else: ?>
                        <a href="login.php" class="btn-cita" style="display:block;text-decoration:none;">Solicitar cita</a>
                    <?php endif; ?>
                <?php else: ?>
                    <button class="btn-cita" disabled>No disponible</button>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Tab Jurídica -->
    <div id="tab-juridica" class="<?= $tab==='juridica' ? '' : 'hidden' ?>">
        <div class="seccion-titulo">
            <h2>Abogados disponibles</h2>
            <p>Todos nuestros profesionales están certificados y tienen experiencia en casos de discriminación e inclusión.</p>
        </div>
        <div class="profesionales-grid">
            <?php
            $abogados = [
                ['👨‍⚖️','Lic. Roberto Sánchez','Derecho Laboral','disponible','4.9','156',['Discriminación laboral','Despidos injustificados','Acoso','Derechos laborales']],
                ['👩‍⚖️','Lic. Laura Fernández','Derechos Humanos','disponible','5','142',['Discriminación','Igualdad','Casos de DDHH','Violencia']],
                ['👨‍⚖️','Lic. David Torres','Derecho Civil','disponible','4.8','118',['Contratos','Denuncias civiles','Mediación','Asesoría general']],
            ];
            foreach($abogados as $a): ?>
            <div class="prof-card">
                <div class="prof-avatar"><?= $a[0] ?></div>
                <div class="prof-nombre"><?= $a[1] ?></div>
                <div class="prof-especialidad"><?= $a[2] ?></div>
                <span class="prof-disponible disponible">Disponible</span>
                <div class="prof-rating"><span>⭐</span> <?= $a[4] ?> (<?= $a[5] ?> reseñas)</div>
                <div class="prof-tags">
                    <?php foreach($a[6] as $tag): ?><span class="prof-tag"><?= $tag ?></span><?php endforeach; ?>
                </div>
                <?php if(isset($_SESSION['usuario'])): ?>
                    <button class="btn-cita" onclick="alert('Solicitud de cita enviada a <?= $a[1] ?>')">Solicitar cita</button>
                <?php else: ?>
                    <a href="login.php" class="btn-cita" style="display:block;text-decoration:none;">Solicitar cita</a>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Banner profesional -->
    <div class="prof-banner">
        <div class="prof-banner-left">
            <div class="prof-banner-icon">👥</div>
            <div class="prof-banner-text">
                <strong>¿Eres un profesional especializado?</strong>
                <span>Ofrece tus servicios de asesoría psicológica o jurídica en EquiRed</span>
            </div>
        </div>
        <?php if(isset($_SESSION['usuario'])): ?>
            <button class="btn-publicar-servicio">Publicar Servicio</button>
        <?php else: ?>
            <a href="login.php" class="btn-publicar-servicio" style="text-decoration:none;">Publicar Servicio</a>
        <?php endif; ?>
    </div>

</div>

<footer class="footer">© 2026 EquiRed. Conectando oportunidades, construyendo igualdad.</footer>

<style>
.hidden { display:none; }
</style>

<script>
function cambiarTab(tab) {
    document.getElementById('tab-psicologica').classList.toggle('hidden', tab !== 'psicologica');
    document.getElementById('tab-juridica').classList.toggle('hidden', tab !== 'juridica');
    document.querySelectorAll('.tab-btn').forEach((btn, i) => {
        btn.classList.toggle('active', (i===0 && tab==='psicologica') || (i===1 && tab==='juridica'));
    });
}
</script>

</body>
</html>