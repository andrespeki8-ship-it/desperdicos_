<?php
session_start();
require_once __DIR__ . '/../includes/auth.php';
requerir_rol(['Administrador']);
require_once __DIR__ . '/../config/conexion.php';

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_rol'])) {
    $id_usuario = intval($_POST['id_usuario'] ?? 0);
    $nuevo_rol = intval($_POST['id_rol'] ?? 0);
    if ($id_usuario > 0 && $nuevo_rol > 0) {
        $stmt = $pdo->prepare("UPDATE usuarios SET id_rol = ? WHERE id_usuario = ?");
        $stmt->execute([$nuevo_rol, $id_usuario]);
        $mensaje = "¡Rol y permisos actualizados con éxito!";
    }
}

$usuarios = $pdo->query("SELECT u.id_usuario, u.nombre_completo, u.username, r.nombre_rol, u.id_rol FROM usuarios u JOIN roles r ON u.id_rol = r.id_rol ORDER BY u.nombre_completo ASC")->fetchAll();
$roles = $pdo->query("SELECT * FROM roles")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Permisos y Roles | Administrador</title>
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
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 20px; }
        label { display: block; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 8px; }
        select { width: 100%; padding: 12px; background-color: #12141c; border: 1px solid var(--border-color); border-radius: 8px; color: white; }
        .btn-dorado { background-color: var(--accent-gold); color: #12141c; font-weight: 700; padding: 12px 24px; border: none; border-radius: 8px; cursor: pointer; }
        .alerta-exito { background-color: rgba(16, 185, 129, 0.1); border: 1px solid #10b981; color: #34d399; padding: 12px; border-radius: 8px; margin-bottom: 20px; }
        .permisos-box { background: rgba(245, 158, 11, 0.05); border: 1px solid var(--border-color); padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .permisos-box ul { padding-left: 20px; color: var(--text-muted); font-size: 0.9rem; }
    </style>
</head>
<body>
    <aside>
        <div class="sidebar-brand"><i class="fa-solid fa-utensils"></i> Admin Pro</div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php"><i class="fa-solid fa-chart-pie"></i> Panel General</a></li>
            <li><a href="usuarios.php"><i class="fa-solid fa-users"></i> Usuarios</a></li>
            <li><a href="menu.php"><i class="fa-solid fa-utensils"></i> Menú</a></li>
            <li><a href="permisos.php" class="active"><i class="fa-solid fa-shield-halved"></i> Permisos y Roles</a></li>
            <li><a href="qr_generator.php"><i class="fa-solid fa-qrcode"></i> Generador QR</a></li>
            <li><a href="ranking.php"><i class="fa-solid fa-trophy"></i> Ranking</a></li>
            <li><a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i> Salir</a></li>
        </ul>
    </aside>

    <main>
        <header>
            <div style="font-weight: 600; color: var(--text-muted);">Módulo de Permisos y Roles</div>
            <span><i class="fa-solid fa-user-shield"></i> <?= htmlspecialchars($_SESSION['nombre_completo'] ?? 'Admin') ?></span>
        </header>

        <div class="content">
            <h2>Gestión de Permisos por Rol de Usuario</h2>
            <?php if ($mensaje): ?><div class="alerta-exito"><?= $mensaje ?></div><?php endif; ?>

            <div class="permisos-box">
                <h3 style="font-size: 1rem; margin-bottom: 8px;"><i class="fa-solid fa-circle-info"></i> Detalle de Permisos por Rol:</h3>
                <ul>
                    <li><strong>Administrador:</strong> Acceso total al sistema, gestión de usuarios, grados, menús, permisos, generación de QR y edición de puntajes.</li>
                    <li><strong>Personal:</strong> Acceso al panel operativo de escáner rápido de asistencia (suma de 5 puntos), visualización del ranking y consulta del menú del día.</li>
                    <li><div><strong>Estudiante:</strong> Acceso a su panel personal, credencial QR, escáner de plato por cámara (IA) para ganar puntos y visualización del ranking institucional.</div></li>
                </ul>
            </div>

            <div class="card">
                <h3><i class="fa-solid fa-user-gear"></i> Asignar Rol y Permisos a un Usuario</h3>
                <form method="POST">
                    <div class="form-grid">
                        <div>
                            <label>Seleccionar Usuario</label>
                            <select name="id_usuario" required>
                                <option value="">-- Seleccione Usuario --</option>
                                <?php foreach ($usuarios as $u): ?>
                                    <option value="<?= $u['id_usuario'] ?>"><?= htmlspecialchars($u['nombre_completo']) ?> (Rol Actual: <?= $u['nombre_rol'] ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label>Nuevo Rol a Asignar</label>
                            <select name="id_rol" required>
                                <?php foreach ($roles as $r): ?>
                                    <option value="<?= $r['id_rol'] ?>"><?= htmlspecialchars($r['nombre_rol']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <button type="submit" name="actualizar_rol" class="btn-dorado">Actualizar Permisos del Usuario</button>
                </form>
            </div>
        </div>
    </main>
</body>
</html>