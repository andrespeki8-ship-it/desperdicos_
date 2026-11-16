<?php
session_start();
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/conexion.php';

$mensaje = '';
$error = '';$plato_editar = null;

// Procesar Edición o Creación de Menú de forma segura
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_menu = intval($_POST['id_menu'] ?? 0);
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $fecha =$_POST['fecha'] ?? date('Y-m-d');

    if ($nombre !== '') {
        if ($id_menu > 0) {
            // Actualizar plato existente (compatible con cualquier nombre de columna)
            try {
                $pdo->prepare("UPDATE menu SET nombre = ?, descripcion = ?, fecha = ? WHERE id_menu = ?")->execute([$nombre,$descripcion, $fecha,$id_menu]);
            } catch (Exception $e) {
                try {
                    $pdo->prepare("UPDATE menu SET titulo_plato = ?, descripcion = ?, fecha_publicacion = ? WHERE id = ?")->execute([$nombre,$descripcion, $fecha,$id_menu]);
                } catch (Exception $ex) {}
            }
            $mensaje = "¡Menú actualizado con éxito!";
        } else {
            // Insertar nuevo plato
            try {
                $pdo->prepare("INSERT INTO menu (nombre, descripcion, fecha) VALUES (?, ?, ?)")->execute([$nombre, $descripcion,$fecha]);
            } catch (Exception $e) {
                try {
                    $pdo->prepare("INSERT INTO menu (titulo_plato, descripcion, fecha_publicacion) VALUES (?, ?, ?)")->execute([$nombre, $descripcion,$fecha]);
                } catch (Exception $ex) {$error = "Error al guardar en la base de datos: " . $ex->getMessage();
                }
            }
            $mensaje = "¡Menú publicado con éxito!";
        }
    } else {
        $error = "El nombre del plato es obligatorio.";
    }
}

// Cargar datos para editar si se pasa ID por GET
if (isset($_GET['editar'])) {
    $id_edit = intval($_GET['editar']);
    try {
        $stmt =$pdo->prepare("SELECT *, COALESCE(nombre, titulo_plato) AS nombre, COALESCE(fecha, fecha_publicacion) AS fecha, COALESCE(id_menu, id) AS id_menu FROM menu WHERE id_menu = ? OR id = ?");
        $stmt->execute([$id_edit,$id_edit]);
        $plato_editar =$stmt->fetch();
    } catch (Exception $e) {}
}

// Eliminar plato
if (isset($_GET['eliminar'])) {
    $id_del = intval($_GET['eliminar']);
    try {
        $pdo->prepare("DELETE FROM menu WHERE id_menu = ? OR id = ?")->execute([$id_del,$id_del]);
    } catch (Exception $e) {}
    header("Location: menu.php");
    exit;
}

// Obtener lista segura de menús (compatible con cualquier nombre de columna)
try {
    $menus =$pdo->query("SELECT *, COALESCE(nombre, titulo_plato) AS nombre, COALESCE(fecha, fecha_publicacion) AS fecha, COALESCE(id_menu, id) AS id_menu FROM menu ORDER BY fecha DESC")->fetchAll();
} catch (Exception $e) {
    try {
        $menus =$pdo->query("SELECT * FROM menu")->fetchAll();
    } catch (Exception $ex) {$menus = [];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Menú y Edición | Personal</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --bg-main: #12141c; --bg-card: #1a1d29; --accent-gold: #f59e0b; --text-main: #f3f4f6; --text-muted: #9ca3af; --border-color: #2e3548; }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Inter', sans-serif; }
        body { background-color: var(--bg-main); color: var(--text-main); display: flex; min-height: 100vh; }
        
        /* Menú lateral unificado */
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
        input, textarea { width: 100%; padding: 12px; background-color: #12141c; border: 1px solid var(--border-color); border-radius: 8px; color: white; }
        .btn-dorado { background-color: var(--accent-gold); color: #12141c; font-weight: 700; padding: 12px 24px; border: none; border-radius: 8px; cursor: pointer; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; text-align: left; }
        th, td { padding: 14px; border-bottom: 1px solid var(--border-color); font-size: 0.9rem; }
        th { color: var(--accent-gold); background-color: rgba(245, 158, 11, 0.05); }
        .alerta-exito { background-color: rgba(16, 185, 129, 0.1); border: 1px solid #10b981; color: #34d399; padding: 12px; border-radius: 8px; margin-bottom: 20px; }
        .alerta-error { background-color: rgba(239, 68, 68, 0.1); border: 1px solid #ef4444; color: #f87171; padding: 12px; border-radius: 8px; margin-bottom: 20px; }
        .btn-editar { background: rgba(59, 130, 246, 0.2); color: #60a5fa; border: 1px solid #3b82f6; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-weight: bold; font-size: 0.85rem; margin-right: 5px; }
        .btn-eliminar { background: rgba(239, 68, 68, 0.2); color: #f87171; border: 1px solid #ef4444; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-weight: bold; font-size: 0.85rem; }
    </style>
</head>
<body>
    <aside>
        <div class="sidebar-brand"><i class="fa-solid fa-utensils"></i> Personal Pro</div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php"><i class="fa-solid fa-chart-pie"></i> Dashboard</a></li>
            <li><a href="escaner.php"><i class="fa-solid fa-barcode"></i> Escáner Asistencia</a></li>
            <li><a href="menu.php" class="active"><i class="fa-solid fa-bowl-food"></i> Menú y Edición</a></li>
            <li><a href="puntos.php"><i class="fa-solid fa-star-half-stroke"></i> Puntos</a></li>
            <li><a href="beneficios.php"><i class="fa-solid fa-gift"></i> Beneficios</a></li>
            <li><a href="panel.php"><i class="fa-solid fa-house"></i> Mi Panel</a></li>
            <li><a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i> Salir</a></li>
        </ul>
    </aside>

    <main>
        <header>
            <div style="font-weight: 600; color: var(--text-muted);">Módulo de Menú Institucional</div>
            <span><i class="fa-solid fa-user-shield"></i> <?= htmlspecialchars($_SESSION['nombre_completo'] ?? 'Personal') ?></span>
        </header>

        <div class="content">
            <h2><?= $plato_editar ? 'Editar Plato del Menú' : 'Publicar Plato en el Menú' ?></h2>
            <?php if ($mensaje): ?><div class="alerta-exito"><?= $mensaje ?></div><?php endif; ?>
            <?php if ($error): ?><div class="alerta-error"><?= $error ?></div><?php endif; ?>

            <div class="card">
                <h3><i class="fa-solid fa-<?= $plato_editar ? 'pen-to-square' : 'plus-circle' ?>"></i> <?= $plato_editar ? 'Modificar Información del Plato' : 'Registrar Nuevo Menú' ?></h3>
                <form method="POST">
                    <input type="hidden" name="id_menu" value="<?= $plato_editar['id_menu'] ?? 0 ?>">
                    <div class="form-grid">
                        <div>
                            <label>Fecha</label>
                            <input type="date" name="fecha" value="<?= htmlspecialchars($plato_editar['fecha'] ?? date('Y-m-d')) ?>" required>
                        </div>
                        <div>
                            <label>Nombre del Plato</label>
                            <input type="text" name="nombre" value="<?= htmlspecialchars($plato_editar['nombre'] ?? '') ?>" required placeholder="Ej. Almuerzo institucional">
                        </div>
                        <div>
                            <label>Descripción</label>
                            <input type="text" name="descripcion" value="<?= htmlspecialchars($plato_editar['descripcion'] ?? '') ?>" placeholder="Ej. Seco, sopa y jugo">
                        </div>
                    </div>
                    <button type="submit" class="btn-dorado"><?= $plato_editar ? 'Actualizar Plato' : 'Guardar Plato' ?></button>
                    <?php if ($plato_editar): ?>
                        <a href="menu.php" style="margin-left: 10px; color: var(--text-muted); text-decoration: none; font-weight: bold;">Cancelar Edición</a>
                    <?php endif; ?>
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
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($menus) > 0): ?>
                            <?php foreach ($menus as$m): ?>
                            <?php $id_m =$m['id_menu'] ?? 0; ?>
                            <tr>
                                <td><strong style="color: var(--accent-gold);"><?= htmlspecialchars($m['fecha'] ?? 'N/A') ?></strong></td>
                                <td><strong><?= htmlspecialchars($m['nombre'] ?? 'Sin nombre') ?></strong></td>
                                <td><?= htmlspecialchars($m['descripcion'] ?? '') ?></td>
                                <td>
                                    <a href="menu.php?editar=<?= $id_m ?>" class="btn-editar"><i class="fa-solid fa-pen"></i> Editar</a>
                                    <a href="menu.php?eliminar=<?= $id_m ?>" class="btn-eliminar" onclick="return confirm('¿Estás seguro de eliminar este plato?');"><i class="fa-solid fa-trash"></i> Eliminar</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4" style="text-align: center; color: var(--text-muted); padding: 20px;">No hay platos registrados en el sistema.</td></tr>
                        <?php endif; ?>