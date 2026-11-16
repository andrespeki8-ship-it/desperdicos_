<?php
session_start();
require_once __DIR__ . '/../includes/auth.php';
requerir_rol(['Administrador']);
require_once __DIR__ . '/../config/conexion.php';

$mensaje = '';
$error = '';

// Procesar formulario de guardado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_menu'])) {
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $fecha = $_POST['fecha'] ?? date('Y-m-d');

    if ($nombre !== '') {
        try {
            // Intentamos insertar usando la columna 'fecha'
            $stmt = $pdo->prepare("INSERT INTO menu (nombre, descripcion, fecha) VALUES (?, ?, ?)");
            $stmt->execute([$nombre, $descripcion, $fecha]);
            $mensaje = "¡Menú guardado con éxito!";
        } catch (Exception $e) {
            // Si la columna en tu BD se llamara fecha_publicacion, intentamos con esa por respaldo
            try {
                $stmt = $pdo->prepare("INSERT INTO menu (nombre, descripcion, fecha_publicacion) VALUES (?, ?, ?)");
                $stmt->execute([$nombre, $descripcion, $fecha]);
                $mensaje = "¡Menú guardado con éxito!";
            } catch (Exception $ex) {
                $error = "Error al guardar en la base de datos: " . $ex->getMessage();
            }
        }
    } else {
        $error = "El nombre del plato es obligatorio.";
    }
}

// Eliminar plato
if (isset($_GET['eliminar'])) {
    $id_menu = intval($_GET['eliminar']);
    try {
        $pdo->prepare("DELETE FROM menu WHERE id_menu = ?")->execute([$id_menu]);
    } catch (Exception $e) {
        // Por si tu llave primaria se llama 'id' en lugar de 'id_menu'
        $pdo->prepare("DELETE FROM menu WHERE id = ?")->execute([$id_menu]);
    }
    header("Location: menu.php");
    exit;
}

// Consultar menús ordenados de forma segura (compatible con cualquier nombre de columna de fecha)
try {
    $menus = $pdo->query("SELECT * FROM menu ORDER BY fecha DESC, id_menu DESC")->fetchAll();
} catch (Exception $e) {
    try {
        $menus = $pdo->query("SELECT * FROM menu ORDER BY fecha_publicacion DESC")->fetchAll();
    } catch (Exception $ex) {
        $menus = $pdo->query("SELECT * FROM menu")->fetchAll();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Menú | Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --bg-main: #12141c; --bg-card: #1a1d29; --accent-gold: #f59e0b; --text-main: #f3f4f6; --text-muted: #9ca3af; --border-color: #2e3548; }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Inter', sans-serif; }
        body { background-color: var(--bg-main); color: var(--text-main); display: flex; min-height: 100vh; }
        aside { width: 280px; background-color: var(--bg-card); border-right: 1px solid var(--border-color); display: flex; flex-direction: column; position: fixed; height: 100vh; }
        .sidebar-brand { padding: 25px; font-size: 1.25rem; font-weight: 700; color: var(--accent-gold); display: flex; align-items: center; gap: 10px; border-bottom: 1px solid var(--border-color); }
        .sidebar-menu { list-style: none; padding: 20px 0; flex-grow: 1; overflow-y: auto; }
        .sidebar-menu li a { display: flex; align-items: center; gap: 15px; padding: 14px 25px; color: var(--text-muted); text-decoration: none; font-weight: 500; transition: all 0.3s ease; }
        .sidebar-menu li a:hover, .sidebar-menu li a.active { background-color: rgba(245, 158, 11, 0.1); color: var(--accent-gold); border-left: 4px solid var(--accent-gold); }
        main { margin-left: 280px; flex-grow: 1; display: flex; flex-direction: column; }
        header { background-color: var(--bg-card); border-bottom: 1px solid var(--border-color); padding: 20px 40px; display: flex; justify-content: space-between; align-items: center; }
        .content { padding: 40px; }
        .card { background-color: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 25px; margin-bottom: 30px; }
        h2, h3 { color: var(--accent-gold); margin-bottom: 15px; }
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 20px; }
        label { display: block; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 8px; }
        input { width: 100%; padding: 12px; background-color: #12141c; border: 1px solid var(--border-color); border-radius: 8px; color: white; }
        .btn-dorado { background-color: var(--accent-gold); color: #12141c; font-weight: 700; padding: 12px 24px; border: none; border-radius: 8px; cursor: pointer; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; text-align: left; }
        th, td { padding: 14px; border-bottom: 1px solid var(--border-color); font-size: 0.9rem; }
        th { color: var(--accent-gold); background-color: rgba(245, 158, 11, 0.05); }
        .alerta-exito { background-color: rgba(16, 185, 129, 0.1); border: 1px solid #10b981; color: #34d399; padding: 12px; border-radius: 8px; margin-bottom: 20px; }
        .alerta-error { background-color: rgba(239, 68, 68, 0.1); border: 1px solid #ef4444; color: #f87171; padding: 12px; border-radius: 8px; margin-bottom: 20px; }
        .btn-eliminar { background: rgba(239, 68, 68, 0.2); color: #f87171; border: 1px solid #ef4444; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-weight: bold; font-size: 0.85rem; }
    </style>
</head>
<body>
    <aside>
        <div class="sidebar-brand"><i class="fa-solid fa-utensils"></i> Admin Pro</div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php"><i class="fa-solid fa-chart-pie"></i> Panel General</a></li>
            <li><a href="asistencia.php"><i class="fa-solid fa-barcode"></i> Control Asistencia</a></li>
            <li><a href="gestionar_puntos.php"><i class="fa-solid fa-star-half-stroke"></i> Asignar Puntos</a></li>
            <li><a href="usuarios.php"><i class="fa-solid fa-users"></i> Usuarios</a></li>
            <li><a href="menu.php" class="active"><i class="fa-solid fa-bowl-food"></i> Menú del Día</a></li>
            <li><a href="permisos.php"><i class="fa-solid fa-shield-halved"></i> Permisos y Roles</a></li>
            <li><a href="ranking.php"><i class="fa-solid fa-trophy"></i> Ranking</a></li>
            <li><a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i> Salir</a></li>
        </ul>
    </aside>

    <main>
        <header>
            <div style="font-weight: 600; color: var(--text-muted);">Módulo de Menú</div>
            <span><i class="fa-solid fa-user-shield"></i> <?= htmlspecialchars($_SESSION['nombre_completo'] ?? 'Admin') ?></span>
        </header>

        <div class="content">
            <h2>Publicar Plato en el Menú</h2>
            <?php if ($mensaje): ?><div class="alerta-exito"><?= $mensaje ?></div><?php endif; ?>
            <?php if ($error): ?><div class="alerta-error"><?= $error ?></div><?php endif; ?>

            <div class="card">
                <h3><i class="fa-solid fa-plus-circle"></i> Registrar Nuevo Menú</h3>
                <form method="POST">
                    <div class="form-grid">
                        <div>
                            <label>Fecha</label>
                            <input type="date" name="fecha" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div>
                            <label>Nombre del Plato</label>
                            <input type="text" name="nombre" required placeholder="Ej. Almuerzo institucional">
                        </div>
                        <div>
                            <label>Descripción</label>
                            <input type="text" name="descripcion" placeholder="Ej. Seco, sopa y jugo">
                        </div>
                    </div>
                    <button type="submit" name="guardar_menu" class="btn-dorado">Guardar Plato</button>
                </form>
            </div>

            <div class="card">
                <h3><i class="fa-solid fa-list"></i> Platos Registrados</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Plato</th>
                            <th>Descripción</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($menus) > 0): ?>
                            <?php foreach ($menus as $m): ?>
                            <tr>
                                <td><strong style="color: var(--accent-gold);"><?= htmlspecialchars($m['fecha'] ?? ($m['fecha_publicacion'] ?? 'N/A')) ?></strong></td>
                                <td><strong><?= htmlspecialchars($m['nombre'] ?? ($m['titulo_plato'] ?? 'Sin nombre')) ?></strong></td>
                                <td><?= htmlspecialchars($m['descripcion'] ?? '') ?></td>
                                <td><a href="menu.php?eliminar=<?= $m['id_menu'] ?? ($m['id'] ?? 0) ?>" class="btn-eliminar" onclick="return confirm('¿Eliminar plato?');">Eliminar</a></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4" style="text-align: center; color: var(--text-muted); padding: 20px;">No hay platos registrados.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>