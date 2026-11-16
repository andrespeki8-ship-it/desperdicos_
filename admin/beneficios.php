<?php
// admin/beneficios.php
session_start();
require_once __DIR__ . '/../includes/auth.php';
requerir_rol(['Administrador']);
require_once __DIR__ . '/../config/conexion.php';

$mensaje = '';
$tipo_alerta = '';
$beneficio_a_editar = null;

// 1. CREAR O EDITAR BENEFICIO
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_beneficio = $_POST['id_beneficio'] ?? '';
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $puntos = intval($_POST['puntos_requeridos']);

    if (!empty($nombre) && $puntos > 0) {
        if (!empty($id_beneficio)) {
            // Actualizar beneficio existente
            $stmt = $pdo->prepare("UPDATE beneficios SET nombre_beneficio = ?, descripcion = ?, puntos_requeridos = ? WHERE id_beneficio = ?");
            $stmt->execute([$nombre, $descripcion, $puntos, $id_beneficio]);
            $mensaje = "¡Beneficio actualizado correctamente!";
        } else {
            // Crear nuevo beneficio
            $stmt = $pdo->prepare("INSERT INTO beneficios (nombre_beneficio, descripcion, puntos_requeridos) VALUES (?, ?, ?)");
            $stmt->execute([$nombre, $descripcion, $puntos]);
            $mensaje = "¡Beneficio creado correctamente!";
        }
        $tipo_alerta = "exito";
    } else {
        $mensaje = "Por favor completa los campos obligatorios y asegúrate de colocar puntos válidos.";
        $tipo_alerta = "error";
    }
}

// 2. ELIMINAR BENEFICIO
if (isset($_GET['eliminar'])) {
    $id_eliminar = intval($_GET['eliminar']);
    $stmt = $pdo->prepare("DELETE FROM beneficios WHERE id_beneficio = ?");
    $stmt->execute([$id_eliminar]);
    header("Location: beneficios.php?msg=eliminado");
    exit;
}

// 3. CARGAR DATOS SI SE VA A EDITAR
if (isset($_GET['editar'])) {
    $id_editar = intval($_GET['editar']);
    $stmt_ed = $pdo->prepare("SELECT * FROM beneficios WHERE id_beneficio = ?");
    $stmt_ed->execute([$id_editar]);
    $beneficio_a_editar = $stmt_ed->fetch();
}

// Obtener todos los beneficios
$beneficios = $pdo->query("SELECT * FROM beneficios ORDER BY puntos_requeridos ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Beneficios | Admin Pro</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --bg-main: #12141c; --bg-card: #1a1d29; --accent-gold: #f59e0b; --text-main: #f3f4f6; --text-muted: #9ca3af; --border-color: #2e3548; }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Inter', sans-serif; }
        body { background-color: var(--bg-main); color: var(--text-main); display: flex; min-height: 100vh; }
        aside { width: 280px; background-color: var(--bg-card); border-right: 1px solid var(--border-color); display: flex; flex-direction: column; position: fixed; height: 100vh; }
        .sidebar-brand { padding: 25px; font-size: 1.25rem; font-weight: 700; color: var(--accent-gold); display: flex; align-items: center; gap: 10px; border-bottom: 1px solid var(--border-color); }
        .sidebar-menu { list-style: none; padding: 20px 0; flex-grow: 1; }
        .sidebar-menu li a { display: flex; align-items: center; gap: 15px; padding: 15px 25px; color: var(--text-muted); text-decoration: none; font-weight: 500; transition: all 0.3s; }
        .sidebar-menu li a:hover, .sidebar-menu li a.active { background-color: rgba(245, 158, 11, 0.1); color: var(--accent-gold); border-left: 4px solid var(--accent-gold); }
        main { margin-left: 280px; flex-grow: 1; display: flex; flex-direction: column; }
        header { background-color: var(--bg-card); border-bottom: 1px solid var(--border-color); padding: 20px 40px; display: flex; justify-content: space-between; align-items: center; }
        .content { padding: 40px; }
        .card { background-color: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 25px; margin-bottom: 30px; }
        h2 { color: var(--accent-gold); margin-bottom: 20px; font-size: 1.25rem; display: flex; align-items: center; gap: 10px; }
        label { display: block; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 6px; font-weight: 500; }
        input, textarea { width: 100%; padding: 12px; background-color: #12141c; border: 1px solid var(--border-color); border-radius: 8px; color: white; margin-bottom: 15px; outline: none; }
        input:focus, textarea:focus { border-color: var(--accent-gold); }
        .btn { background-color: var(--accent-gold); color: #12141c; font-weight: bold; padding: 12px 20px; border: none; border-radius: 8px; cursor: pointer; text-decoration: none; display: inline-block; transition: background 0.3s; }
        .btn:hover { background-color: #d97706; }
        .btn-danger { background-color: #ef4444; color: white; padding: 6px 12px; font-size: 0.85rem; }
        .btn-danger:hover { background-color: #dc2626; }
        .btn-edit { background-color: #3b82f6; color: white; padding: 6px 12px; font-size: 0.85rem; }
        .btn-edit:hover { background-color: #2563eb; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; text-align: left; }
        th, td { padding: 12px; border-bottom: 1px solid var(--border-color); font-size: 0.9rem; }
        th { color: var(--accent-gold); background-color: rgba(245, 158, 11, 0.05); }
        .alerta { padding: 12px; border-radius: 8px; margin-bottom: 20px; text-align: center; font-weight: 500; }
        .alerta.exito { background: rgba(52, 211, 153, 0.1); border: 1px solid #34d399; color: #34d399; }
        .alerta.error { background: rgba(239, 68, 68, 0.1); border: 1px solid #ef4444; color: #f87171; }
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
            <li><a href="menu.php"><i class="fa-solid fa-bowl-food"></i> Menú</a></li>
            <li><a href="beneficios.php" class="active"><i class="fa-solid fa-gift"></i> Beneficios</a></li>
            <li><a href="ranking.php"><i class="fa-solid fa-trophy"></i> Ranking</a></li>
            <li><a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i> Salir</a></li>
        </ul>
    </aside>

    <main>
        <header>
            <div style="font-weight: 600; color: var(--text-muted);">Módulo de Beneficios y Premios</div>
            <span><i class="fa-solid fa-user-shield"></i> Administrador</span>
        </header>

        <div class="content">
            <?php if (!empty($mensaje)): ?>
                <div class="alerta <?= $tipo_alerta ?>"><?= $mensaje ?></div>
            <?php endif; ?>

            <!-- FORMULARIO DE CREACIÓN / EDICIÓN -->
            <div class="card" style="max-width: 650px;">
                <h2>
                    <i class="fa-solid <?= $beneficio_a_editar ? 'fa-pen-to-square' : 'fa-plus-circle' ?>"></i> 
                    <?= $beneficio_a_editar ? 'Editar Beneficio' : 'Crear Nuevo Beneficio' ?>
                </h2>
                <form method="POST">
                    <input type="hidden" name="id_beneficio" value="<?= $beneficio_a_editar['id_beneficio'] ?? '' ?>">
                    
                    <label>Nombre del Beneficio</label>
                    <input type="text" name="nombre" required value="<?= htmlspecialchars($beneficio_a_editar['nombre_beneficio'] ?? '') ?>" placeholder="Ej: Postre especial / Almuerzo gratis">
                    
                    <label>Descripción</label>
                    <textarea name="descripcion" rows="3" placeholder="Detalles de lo que incluye el premio..."><?= htmlspecialchars($beneficio_a_editar['descripcion'] ?? '') ?></textarea>
                    
                    <label>Puntos Requeridos</label>
                    <input type="number" name="puntos_requeridos" min="1" required value="<?= $beneficio_a_editar['puntos_requeridos'] ?? '' ?>" placeholder="Ej: 25">
                    
                    <div style="display: flex; gap: 10px;">
                        <button type="submit" class="btn"><?= $beneficio_a_editar ? 'Actualizar Beneficio' : 'Guardar Beneficio' ?></button>
                        <?php if ($beneficio_a_editar): ?>
                            <a href="beneficios.php" class="btn" style="background-color: #374151; color: white;">Cancelar Edición</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- TABLA DE BENEFICIOS REGISTRADOS -->
            <div class="card">
                <h2><i class="fa-solid fa-list"></i> Listado de Beneficios Activos</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre del Beneficio</th>
                            <th>Descripción</th>
                            <th>Puntos Requeridos</th>
                            <th style="text-align: center;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($beneficios)): ?>
                            <tr><td colspan="5" style="text-align: center; color: var(--text-muted);">No hay beneficios registrados.</td></tr>
                        <?php else: ?>
                            <?php foreach ($beneficios as $b): ?>
                                <tr>
                                    <td>#<?= $b['id_beneficio'] ?></td>
                                    <td><strong><?= htmlspecialchars($b['nombre_beneficio']) ?></strong></td>
                                    <td style="color: var(--text-muted);"><?= htmlspecialchars($b['descripcion']) ?></td>
                                    <td><strong style="color: var(--accent-gold);"><i class="fa-solid fa-star"></i> <?= $b['puntos_requeridos'] ?> pts</strong></td>
                                    <td style="text-align: center; display: flex; gap: 8px; justify-content: center;">
                                        <a href="beneficios.php?editar=<?= $b['id_beneficio'] ?>" class="btn btn-edit"><i class="fa-solid fa-pen"></i> Editar</a>
                                        <a href="beneficios.php?eliminar=<?= $b['id_beneficio'] ?>" class="btn btn-danger" onclick="return confirm('¿Estás seguro de eliminar este beneficio?')"><i class="fa-solid fa-trash"></i> Borrar</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>