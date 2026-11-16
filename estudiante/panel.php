<?php
session_start();
require_once __DIR__ . '/../includes/auth.php';
requerir_rol(['Estudiante']);
require_once __DIR__ . '/../config/conexion.php';

$id_usuario = $_SESSION['id_usuario'];
$hoy = date('Y-m-d');
$alerta_porcion = '';

// Guardar preferencia de porción para reducir desperdicio
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['porcion_hoy'])) {
    $porcion = $_POST['porcion_hoy'];
    $stmt_p = $pdo->prepare("INSERT INTO preferencias_porcion (usuario_id, fecha, porcion) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE porcion = ?");
    $stmt_p->execute([$id_usuario, $hoy, $porcion, $porcion]);
    $alerta_porcion = "¡Preferencia registrada! La cocina ajustará tu ración de hoy.";
}

// Obtener preferencia actual
$stmt_pref = $pdo->prepare("SELECT porcion FROM preferencias_porcion WHERE usuario_id = ? AND fecha = ?");
$stmt_pref->execute([$id_usuario, $hoy]);
$mi_porcion = $stmt_pref->fetchColumn() ?: 'Normal';

$stmt = $pdo->prepare("SELECT u.*, g.nombre_grado FROM usuarios u LEFT JOIN grados g ON u.id_grado = g.id_grado WHERE u.id_usuario = ?");
$stmt->execute([$id_usuario]);
$estudiante = $stmt->fetch();

if (!$estudiante) {
    header("Location: ../logout.php");
    exit;
}

$ranking = $pdo->query("SELECT u.nombre_completo, u.puntos_totales, g.nombre_grado FROM usuarios u LEFT JOIN grados g ON u.id_grado = g.id_grado WHERE u.id_rol = 3 ORDER BY u.puntos_totales DESC LIMIT 10")->fetchAll();
$menus = $pdo->query("SELECT * FROM menu ORDER BY fecha_publicacion DESC LIMIT 5")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Panel Personal | Estudiante</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --bg-main: #12141c; --bg-card: #1a1d29; --accent-gold: #f59e0b; --text-main: #f3f4f6; --text-muted: #9ca3af; --border-color: #2e3548; }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Inter', sans-serif; }
        body { background-color: var(--bg-main); color: var(--text-main); display: flex; min-height: 100vh; }
        aside { width: 280px; background-color: var(--bg-card); border-right: 1px solid var(--border-color); display: flex; flex-direction: column; position: fixed; height: 100vh; }
        .sidebar-brand { padding: 25px; font-size: 1.25rem; font-weight: 700; color: var(--accent-gold); display: flex; align-items: center; gap: 10px; border-bottom: 1px solid var(--border-color); }
        .sidebar-menu { list-style: none; padding: 20px 0; flex-grow: 1; }
        .sidebar-menu li a { display: flex; align-items: center; gap: 15px; padding: 15px 25px; color: var(--text-muted); text-decoration: none; font-weight: 500; transition: all 0.3s ease; }
        .sidebar-menu li a:hover, .sidebar-menu li a.active { background-color: rgba(245, 158, 11, 0.1); color: var(--accent-gold); border-left: 4px solid var(--accent-gold); }
        main { margin-left: 280px; flex-grow: 1; display: flex; flex-direction: column; }
        header { background-color: var(--bg-card); border-bottom: 1px solid var(--border-color); padding: 20px 40px; display: flex; justify-content: space-between; align-items: center; }
        .content { padding: 40px; display: grid; grid-template-columns: 2fr 1fr; gap: 30px; }
        .card { background-color: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 25px; margin-bottom: 25px; }
        h3 { color: var(--accent-gold); margin-bottom: 15px; }
        .puntos-badge { font-size: 2.2rem; font-weight: 700; color: #34d399; margin-top: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; text-align: left; }
        th, td { padding: 12px; border-bottom: 1px solid var(--border-color); font-size: 0.9rem; }
        th { color: var(--accent-gold); background-color: rgba(245, 158, 11, 0.05); }
        .qr-box { text-align: center; background: #12141c; padding: 15px; border-radius: 10px; border: 1px solid var(--border-color); }
        .qr-box img { width: 140px; height: 140px; background: white; padding: 8px; border-radius: 8px; margin-top: 10px; display: inline-block; }
        .btn-porcion { padding: 10px 16px; border-radius: 8px; border: 1px solid var(--border-color); background: #12141c; color: white; cursor: pointer; font-weight: 600; }
        .btn-porcion.activo { background: #10b981; color: #12141c; border-color: #10b981; }
    </style>
</head>
<body>
    <aside>
        <div class="sidebar-brand"><i class="fa-solid fa-utensils"></i> Estudiante Pro</div>
        <ul class="sidebar-menu">
            <li><a href="panel.php" class="active"><i class="fa-solid fa-house"></i> Mi Panel Personal</a></li>
            <li><a href="mascota.php"><i class="fa-solid fa-gamepad"></i> Mi Mascota Virtual</a></li>
            <li><a href="parque_mascotas.php"><i class="fa-solid fa-users"></i> Plaza de Mascotas</a></li>
            <li><a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i> Salir</a></li>
        </ul>
    </aside>

    <main>
        <header>
            <div style="font-weight: 600; color: var(--text-muted);">Bienvenido, <span style="color: white; font-size: 1.1rem;"><?= htmlspecialchars($estudiante['nombre_completo']) ?></span></div>
            <span><i class="fa-solid fa-graduation-cap"></i> Grado: <strong><?= htmlspecialchars($estudiante['nombre_grado'] ?? 'N/A') ?></strong></span>
        </header>

        <div class="content">
            <div>
                <!-- TARJETA DE PUNTOS Y CARNET QR PERSONAL -->
                <div class="card" style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h3 style="margin-bottom: 5px;"><i class="fa-solid fa-star"></i> Mis Puntos Acumulados</h3>
                        <p style="color: var(--text-muted); font-size: 0.9rem;">Gana +5 puntos al registrar tu asistencia en el comedor.</p>
                        <div class="puntos-badge"><?= $estudiante['puntos_totales'] ?? 0 ?> pts</div>
                    </div>
                    <div class="qr-box">
                        <span style="font-size: 0.8rem; color: var(--text-muted); display: block;">Mi Carnet QR Personal</span>
                        <?php 
                            $doc = !empty($estudiante['documento']) ? $estudiante['documento'] : $estudiante['username'];
                            $qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=140x140&data=" . urlencode($doc);
                        ?>
                        <img src="<?= $qr_url ?>" alt="QR Carnet">
                        <span style="font-size: 0.75rem; display: block; color: var(--accent-gold); margin-top: 4px;">Doc: <?= htmlspecialchars($doc) ?></span>
                    </div>
                </div>

                <!-- MÓDULO ANTI-DESPERDICIO: PREDICCIÓN DE PORCIÓN -->
                <div class="card">
                    <h3><i class="fa-solid fa-scale-balanced"></i> Ayúdanos a Reducir el Desperdicio Hoy</h3>
                    <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 15px;">Avisa a la cocina cuánta comida deseas hoy para evitar que sobre alimento:</p>
                    
                    <?php if ($alerta_porcion): ?>
                        <div style="background: rgba(16,185,129,0.15); color: #34d399; padding: 10px; border-radius: 8px; margin-bottom: 12px; font-size: 0.85rem;"><?= $alerta_porcion ?></div>
                    <?php endif; ?>

                    <form method="POST" style="display: flex; gap: 10px;">
                        <button type="submit" name="porcion_hoy" value="Normal" class="btn-porcion <?= $mi_porcion === 'Normal' ? 'activo' : '' ?>">🍽️ Porción Normal</button>
                        <button type="submit" name="porcion_hoy" value="Pequeña" class="btn-porcion <?= $mi_porcion === 'Pequeña' ? 'activo' : '' ?>">🥗 Porción Pequeña</button>
                        <button type="submit" name="porcion_hoy" value="No asistiré" class="btn-porcion <?= $mi_porcion === 'No asistiré' ? 'activo' : '' ?>">🚫 Hoy no almuerzo</button>
                    </form>
                </div>

                <!-- MENÚ INSTITUCIONAL -->
                <div class="card">
                    <h3><i class="fa-solid fa-utensils"></i> Menú Institucional del Comedor</h3>
                    <table>
                        <thead><tr><th>Fecha</th><th>Plato</th><th>Descripción</th></tr></thead>
                        <tbody>
                            <?php foreach ($menus as $m): ?>
                            <tr>
                                <td><strong style="color: var(--accent-gold);"><?= htmlspecialchars($m['fecha_publicacion']) ?></strong></td>
                                <td><strong><?= htmlspecialchars($m['titulo_plato'] ?? 'Menú del día') ?></strong></td>
                                <td><?= htmlspecialchars($m['descripcion']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- SECCIÓN DE REGISTRO FOTOGRÁFICO DE COMIDA -->
                <div class="card">
                    <h3><i class="fa-solid fa-camera"></i> Subir Foto de Plato Limpio</h3>
                    <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 15px;">Sube una foto de tu plato terminado para verificar cero desperdicio y ganar puntos.</p>
                    
                    <form action="subir_foto.php" method="POST" enctype="multipart/form-data" style="display: flex; gap: 10px; align-items: center;">
                        <input type="file" name="foto_comida" accept="image/*" capture="environment" required style="background: #12141c; padding: 10px; border: 1px solid var(--border-color); border-radius: 8px; color: white; flex-grow: 1;">
                        <button type="submit" style="background-color: var(--accent-gold); color: #12141c; font-weight: bold; padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer;">
                            <i class="fa-solid fa-upload"></i> Subir Plato
                        </button>
                    </form>
                </div>
            </div>

            <div>
                <!-- TABLA DE RANKING INSTITUCIONAL -->
                <div class="card">
                    <h3><i class="fa-solid fa-trophy"></i> Tabla de Ranking</h3>
                    <table>
                        <thead><tr><th>Pos</th><th>Estudiante</th><th>Puntos</th></tr></thead>
                        <tbody>
                            <?php $p = 1; foreach ($ranking as $r): ?>
                            <tr>
                                <td><?= $p == 1 ? '⭐ 1º' : ($p == 2 ? '🥈 2º' : ($p == 3 ? '🥉 3º' : '#' . $p)) ?></td>
                                <td><strong><?= htmlspecialchars($r['nombre_completo']) ?></strong></td>
                                <td><strong style="color: var(--accent-gold);"><?= $r['puntos_totales'] ?> pts</strong></td>
                            </tr>
                            <?php $p++; endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</body>
</html>