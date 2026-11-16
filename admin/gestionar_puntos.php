<?php
session_start();
require_once __DIR__ . '/../includes/auth.php';
requerir_rol(['Administrador']);
require_once __DIR__ . '/../config/conexion.php';

$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_usuario = $_POST['id_usuario'];
    $puntos = intval($_POST['puntos']);
    $tipo = $_POST['tipo']; // 'sumar' o 'restar'
    $motivo = trim($_POST['motivo']);

    $valor_final = ($tipo === 'restar') ? -$puntos : $puntos;

    // Actualizar puntos totales
    $stmt = $pdo->prepare("UPDATE usuarios SET puntos_totales = puntos_totales + ? WHERE id_usuario = ?");
    $stmt->execute([$valor_final, $id_usuario]);

    // Registrar en movimientos
    $stmt_mov = $pdo->prepare("INSERT INTO movimientos_puntos (id_usuario, puntos, motivo) VALUES (?, ?, ?)");
    $stmt_mov->execute([$id_usuario, $valor_final, $motivo]);

    $mensaje = "¡Puntos actualizados correctamente!";
}

// Obtener lista de estudiantes
$estudiantes = $pdo->query("SELECT u.id_usuario, u.nombre_completo, u.documento, g.nombre_grado, u.puntos_totales FROM usuarios u LEFT JOIN grados g ON u.id_grado = g.id_grado WHERE u.id_rol = 3")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestionar Puntos | Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --bg-main: #12141c; --bg-card: #1a1d29; --accent-gold: #f59e0b; --text-main: #f3f4f6; --text-muted: #9ca3af; --border-color: #2e3548; }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Inter', sans-serif; }
        body { background-color: var(--bg-main); color: var(--text-main); display: flex; min-height: 100vh; }
        aside { width: 280px; background-color: var(--bg-card); border-right: 1px solid var(--border-color); display: flex; flex-direction: column; position: fixed; height: 100vh; }
        .sidebar-brand { padding: 25px; font-size: 1.25rem; font-weight: 700; color: var(--accent-gold); display: flex; align-items: center; gap: 10px; border-bottom: 1px solid var(--border-color); }
        .sidebar-menu { list-style: none; padding: 20px 0; flex-grow: 1; }
        .sidebar-menu li a { display: flex; align-items: center; gap: 15px; padding: 15px 25px; color: var(--text-muted); text-decoration: none; font-weight: 500; }
        .sidebar-menu li a:hover, .sidebar-menu li a.active { background-color: rgba(245, 158, 11, 0.1); color: var(--accent-gold); border-left: 4px solid var(--accent-gold); }
        main { margin-left: 280px; flex-grow: 1; padding: 40px; }
        .card { background-color: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 30px; max-width: 600px; margin: 0 auto; }
        h2 { color: var(--accent-gold); margin-bottom: 20px; }
        label { display: block; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 6px; }
        select, input { width: 100%; padding: 12px; background-color: #12141c; border: 1px solid var(--border-color); border-radius: 8px; color: white; margin-bottom: 20px; outline: none; }
        .btn { background-color: var(--accent-gold); color: #12141c; font-weight: bold; padding: 12px; border: none; border-radius: 8px; cursor: pointer; width: 100%; }
        .alerta { background: rgba(52, 211, 153, 0.1); border: 1px solid #34d399; color: #34d399; padding: 12px; border-radius: 8px; margin-bottom: 20px; text-align: center; }
    </style>
</head>
<body>
    <aside>
        <div class="sidebar-brand"><i class="fa-solid fa-utensils"></i> Admin Pro</div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php"><i class="fa-solid fa-chart-pie"></i> Panel General</a></li>
            <li><a href="gestionar_puntos.php" class="active"><i class="fa-solid fa-star-half-stroke"></i> Asignar Puntos</a></li>
            <li><a href="ranking.php"><i class="fa-solid fa-trophy"></i> Ranking</a></li>
            <li><a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i> Salir</a></li>
        </ul>
    </aside>
    <main>
        <div class="card">
            <h2><i class="fa-solid fa-star"></i> Sumar o Restar Puntos a Estudiante</h2>
            <?php if($mensaje): ?><div class="alerta"><?= $mensaje ?></div><?php endif; ?>
            <form method="POST">
                <label>Seleccionar Estudiante</label>
                <select name="id_usuario" required>
                    <option value="">-- Seleccione un alumno ----</option>
                    <?php foreach($estudiantes as $e): ?>
                        <option value="<?= $e['id_usuario'] ?>"><?= htmlspecialchars($e['nombre_completo']) ?> (<?= $e['nombre_grado'] ?>) - Actual: <?= $e['puntos_totales'] ?> pts</option>
                    <?php endforeach; ?>
                </select>

                <label>Acción</label>
                <select name="tipo" required>
                    <option value="sumar">Sumar Puntos (+)</option>
                    <option value="restar">Restar Puntos (-)</option>
                </select>

                <label>Cantidad de Puntos</label>
                <input type="number" name="puntos" min="1" required placeholder="Ej: 5">

                <label>Motivo</label>
                <input type="text" name="motivo" required placeholder="Ej: Buen comportamiento / Desperdicio de comida">

                <button type="submit" class="btn">Aplicar Cambios en Puntos</button>
            </form>
        </div>
    </main>
</body>
</html>