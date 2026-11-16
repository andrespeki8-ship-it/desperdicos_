<?php
session_start();
require_once __DIR__ . '/../includes/auth.php';
requerir_rol(['Administrador']);
require_once __DIR__ . '/../config/conexion.php';

$mensaje = '';

if (isset($_GET['borrar'])) {
    $id_borrar = intval($_GET['borrar']);
    if ($id_borrar > 1) {
        $pdo->prepare("DELETE FROM usuarios WHERE id_usuario = ?")->execute([$id_borrar]);
        $mensaje = "¡Estudiante y credencial eliminados correctamente!";
    }
}

$filtro_grado = isset($_GET['grado']) ? intval($_GET['grado']) : 0;
$grados = $pdo->query("SELECT * FROM grados ORDER BY nombre_grado ASC")->fetchAll();

if ($filtro_grado > 0) {
    $stmt = $pdo->prepare("SELECT u.*, g.nombre_grado, r.nombre_rol FROM usuarios u LEFT JOIN grados g ON u.id_grado = g.id_grado JOIN roles r ON u.id_rol = r.id_rol WHERE u.id_rol = 3 AND u.id_grado = ? ORDER BY u.nombre_completo ASC");
    $stmt->execute([$filtro_grado]);
    $usuarios = $stmt->fetchAll();
} else {
    $usuarios = $pdo->query("SELECT u.*, g.nombre_grado, r.nombre_rol FROM usuarios u LEFT JOIN grados g ON u.id_grado = g.id_grado JOIN roles r ON u.id_rol = r.id_rol WHERE u.id_rol = 3 ORDER BY u.nombre_completo ASC")->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Generador QR por Salón | Admin</title>
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
        .content { padding: 40px; }
        .card { background-color: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 25px; margin-bottom: 30px; }
        h2, h3 { color: var(--accent-gold); margin-bottom: 15px; }
        .filtro-box { display: flex; gap: 15px; align-items: center; margin-bottom: 25px; }
        select { padding: 12px; background-color: #12141c; border: 1px solid var(--border-color); border-radius: 8px; color: white; width: 280px; }
        .btn-filtrar { background-color: var(--accent-gold); color: #12141c; font-weight: 700; padding: 12px 20px; border: none; border-radius: 8px; cursor: pointer; }
        .grid-qr { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; }
        .card-qr { background: #12141c; border: 1px solid var(--border-color); border-radius: 10px; padding: 20px; text-align: center; }
        .qr-img { background: white; padding: 10px; border-radius: 8px; display: inline-block; margin: 15px 0; }
        .qr-img img { width: 140px; height: 140px; display: block; }
        .btn-borrar { background: rgba(239, 68, 68, 0.2); color: #f87171; border: 1px solid #ef4444; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-weight: bold; font-size: 0.85rem; display: inline-block; margin-top: 10px; cursor: pointer; }
        .alerta-exito { background-color: rgba(16, 185, 129, 0.1); border: 1px solid #10b981; color: #34d399; padding: 12px; border-radius: 8px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <aside>
        <div class="sidebar-brand"><i class="fa-solid fa-utensils"></i> Admin Pro</div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php"><i class="fa-solid fa-chart-pie"></i> Panel General</a></li>
            <li><a href="asistencia.php"><i class="fa-solid fa-barcode"></i> Control Asistencia</a></li>
            <li><a href="usuarios.php"><i class="fa-solid fa-users"></i> Usuarios</a></li>
            <li><a href="menu.php"><i class="fa-solid fa-utensils"></i> Menú</a></li>
            <li><a href="qr_generator.php" class="active"><i class="fa-solid fa-qrcode"></i> Generador QR</a></li>
            <li><a href="ranking.php"><i class="fa-solid fa-trophy"></i> Ranking</a></li>
            <li><a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i> Salir</a></li>
        </ul>
    </aside>

    <main>
        <header>
            <div style="font-weight: 600; color: var(--text-muted);">Generador de Credenciales QR por Salón</div>
            <span><i class="fa-solid fa-user-shield"></i> <?= htmlspecialchars($_SESSION['nombre_completo'] ?? 'Admin') ?></span>
        </header>

        <div class="content">
            <h2>Seleccionar Grado / Salón</h2>
            <?php if ($mensaje): ?><div class="alerta-exito"><?= $mensaje ?></div><?php endif; ?>

            <form method="GET" class="filtro-box">
                <select name="grado">
                    <option value="0">-- Todos los Salones / Grados --</option>
                    <?php foreach ($grados as $g): ?>
                        <option value="<?= $g['id_grado'] ?>" <?= $filtro_grado == $g['id_grado'] ? 'selected' : '' ?>><?= htmlspecialchars($g['nombre_grado']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn-filtrar"><i class="fa-solid fa-filter"></i> Filtrar Salón</button>
            </form>

            <div class="grid-qr">
                <?php if (empty($usuarios)): ?>
                    <p style="color: var(--text-muted);">No hay estudiantes registrados en este salón.</p>
                <?php else: ?>
                    <?php foreach ($usuarios as $u): 
                        $doc = !empty($u['documento']) ? $u['documento'] : $u['username'];
                        $qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=160x160&data=" . urlencode($doc);
                    ?>
                    <div class="card-qr">
                        <strong style="color: white; display: block; font-size: 1.05rem;"><?= htmlspecialchars($u['nombre_completo']) ?></strong>
                        <span style="color: var(--accent-gold); font-size: 0.85rem; font-weight: bold;"><?= htmlspecialchars($u['nombre_grado'] ?? 'Sin Grado') ?></span>
                        <p style="color: var(--text-muted); font-size: 0.8rem; margin-top: 4px;">Documento: <?= htmlspecialchars($doc) ?></p>
                        
                        <div class="qr-img">
                            <img src="<?= $qr_url ?>" alt="QR Code">
                        </div>

                        <div>
                            <a href="qr_generator.php?grado=<?= $filtro_grado ?>&borrar=<?= $u['id_usuario'] ?>" class="btn-borrar" onclick="return confirm('¿Seguro que deseas eliminar este estudiante?');">
                                <i class="fa-solid fa-trash"></i> Eliminar
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>
</body>
</html>