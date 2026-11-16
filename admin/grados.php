<?php
session_start();
require_once __DIR__ . '/../includes/auth.php';
requerir_rol(['Administrador']);
require_once __DIR__ . '/../config/conexion.php';

$mensaje = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_grado'])) {
    $nombre_grado = trim($_POST['nombre_grado'] ?? '');
    if ($nombre_grado !== '') {
        try {
            $stmt = $pdo->prepare("INSERT INTO grados (nombre_grado) VALUES (?)");
            $stmt->execute([$nombre_grado]);
            $mensaje = "Grado agregado correctamente.";
        } catch (Exception $e) {
            $error = "El grado ya existe o hubo un error.";
        }
    } else {
        $error = "Escribe un nombre de grado válido.";
    }
}

$grados = $pdo->query("SELECT g.*, (SELECT COUNT(*) FROM usuarios u WHERE u.id_grado = g.id_grado) as total_estudiantes FROM grados g ORDER BY g.id_grado DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Grados | Restaurante Institucional</title>
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
        h2 { margin-bottom: 20px; font-size: 1.5rem; font-weight: 600; color: var(--accent-gold); }
        label { display: block; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 8px; }
        input { width: 100%; max-width: 400px; padding: 12px; background-color: #12141c; border: 1px solid var(--border-color); border-radius: 8px; color: white; font-size: 0.95rem; margin-bottom: 15px; }
        input:focus { border-color: var(--accent-gold); outline: none; }
        .btn-dorado { background-color: var(--accent-gold); color: #12141c; font-weight: 700; padding: 12px 24px; border: none; border-radius: 8px; cursor: pointer; transition: background 0.2s; }
        .btn-dorado:hover { background-color: #d97706; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; text-align: left; }
        th, td { padding: 14px; border-bottom: 1px solid var(--border-color); font-size: 0.9rem; }
        th { color: var(--accent-gold); font-weight: 600; background-color: rgba(245, 158, 11, 0.05); }
        .alerta-exito { background-color: rgba(16, 185, 129, 0.1); border: 1px solid #10b981; color: #34d399; padding: 12px; border-radius: 8px; margin-bottom: 20px; }
        .alerta-error { background-color: rgba(239, 68, 68, 0.1); border: 1px solid #ef4444; color: #f87171; padding: 12px; border-radius: 8px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <aside>
        <div class="sidebar-brand"><i class="fa-solid fa-utensils"></i> Restaurante Pro</div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php"><i class="fa-solid fa-chart-pie"></i> Panel General</a></li>
            <li><a href="usuarios.php"><i class="fa-solid fa-users"></i> Gestión de Usuarios</a></li>
            <li><a href="grados.php" class="active"><i class="fa-solid fa-graduation-cap"></i> Gestión de Grados</a></li>
            <li><a href="permisos.php"><i class="fa-solid fa-shield-halved"></i> Permisos y Roles</a></li>
            <li><a href="qr_generator.php"><i class="fa-solid fa-qrcode"></i> Generador QR</a></li>
            <li><a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i> Cerrar Sesión</a></li>
        </ul>
    </aside>

    <main>
        <header>
            <div style="font-weight: 600; color: var(--text-muted);">Módulo de Grados</div>
            <span><i class="fa-solid fa-user-shield"></i> <?= htmlspecialchars($_SESSION['nombre_completo'] ?? 'Admin') ?></span>
        </header>

        <div class="content">
            <h2>Gestión de Grados Escolares</h2>

            <?php if ($mensaje): ?><div class="alerta-exito"><?= $mensaje ?></div><?php endif; ?>
            <?php if ($error): ?><div class="alerta-error"><?= $error ?></div><?php endif; ?>

            <div class="card">
                <h3 style="margin-bottom: 15px; font-size: 1.1rem;"><i class="fa-solid fa-plus-circle"></i> Agregar Nuevo Grado</h3>
                <form method="POST">
                    <label>Nombre del Grado (Ej: 11-1, 11-2, Undécimo)</label>
                    <input type="text" name="nombre_grado" required placeholder="Ej. Undécimo Grado">
                    <br>
                    <button type="submit" name="crear_grado" class="btn-dorado"><i class="fa-solid save"></i> Guardar Grado</button>
                </form>
            </div>

            <div class="card">
                <h3 style="margin-bottom: 15px; font-size: 1.1rem;"><i class="fa-solid fa-list"></i> Grados Registrados</h3>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre del Grado</th>
                            <th>Estudiantes Asociados</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($grados as $g): ?>
                        <tr>
                            <td><?= $g['id_grado'] ?></td>
                            <td><strong><?= htmlspecialchars($g['nombre_grado']) ?></strong></td>
                            <td><?= $g['total_estudiantes'] ?> estudiantes</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>