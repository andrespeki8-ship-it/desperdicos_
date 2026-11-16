<?php
session_start();
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/conexion.php';

// Obtener estadísticas generales de la institución de forma segura
try {
    $total_estudiantes = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE id_rol = 3")->fetchColumn();
} catch (Exception $e) { $total_estudiantes = 0; }

try {
    $total_mascotas = $pdo->query("SELECT COUNT(*) FROM mascotas")->fetchColumn();
    $promedio_nivel = $pdo->query("SELECT AVG(nivel_mascota) FROM mascotas")->fetchColumn() ?: 1;
} catch (Exception $e) { $total_mascotas = 0; $promedio_nivel = 1; }

// Obtener menús registrados
try {
    $menus = $pdo->query("SELECT *, COALESCE(nombre, titulo_plato) AS nombre, COALESCE(fecha, fecha_publicacion) AS fecha FROM menu ORDER BY fecha DESC LIMIT 5")->fetchAll();
} catch (Exception $e) { $menus = []; }

// Obtener ranking de estudiantes
try {
    $ranking = $pdo->query("SELECT u.nombre_completo, u.puntos_totales, g.nombre_grado FROM usuarios u LEFT JOIN grados g ON u.id_grado = g.id_grado WHERE u.id_rol = 3 ORDER BY u.puntos_totales DESC LIMIT 5")->fetchAll();
} catch (Exception $e) { $ranking = []; }

// Obtener mascotas con sus niveles y grados
$iconos_nivel = [1 => '🐣', 2 => '🐥', 3 => '🦊', 4 => '🐺', 5 => '🦁', 6 => '🐯', 7 => '🦅', 8 => '🐲', 9 => '🐉', 10 => '👑⚡'];
try {
    $mascotas = $pdo->query("
        SELECT m.*, u.nombre_completo, g.nombre_grado 
        FROM mascotas m
        JOIN usuarios u ON m.usuario_id = u.id_usuario
        LEFT JOIN grados g ON u.id_grado = g.id_grado
        ORDER BY m.nivel_mascota DESC LIMIT 5
    ")->fetchAll();
} catch (Exception $e) { $mascotas = []; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Completo | Personal</title>
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
        .content { padding: 40px; display: grid; grid-template-columns: 2fr 1fr; gap: 30px; }
        
        .grid-stats { grid-column: span 2; display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 10px; }
        .stat-card { background-color: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 20px; }
        .stat-card span { font-size: 0.8rem; color: var(--text-muted); display: block; margin-bottom: 5px; }
        .stat-card strong { font-size: 1.8rem; color: var(--accent-gold); }

        .card { background-color: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 25px; margin-bottom: 25px; }
        h3 { color: var(--accent-gold); margin-bottom: 15px; display: flex; align-items: center; gap: 10px; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; text-align: left; }
        th, td { padding: 12px; border-bottom: 1px solid var(--border-color); font-size: 0.88rem; }
        th { color: var(--accent-gold); background-color: rgba(245, 158, 11, 0.05); }
        .badge-nivel { background: rgba(245, 158, 11, 0.15); color: var(--accent-gold); padding: 3px 8px; border-radius: 10px; font-weight: bold; font-size: 0.78rem; }
    </style>
</head>
<body>
    <aside>
        <div class="sidebar-brand"><i class="fa-solid fa-utensils"></i> Personal Pro</div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php" class="active"><i class="fa-solid fa-chart-pie"></i> Dashboard</a></li>
            <li><a href="escaner.php"><i class="fa-solid fa-barcode"></i> Escáner Asistencia</a></li>
            <li><a href="menu.php"><i class="fa-solid fa-bowl-food"></i> Menú y Edición</a></li>
            <li><a href="puntos.php"><i class="fa-solid fa-star-half-stroke"></i> Puntos</a></li>
            <li><a href="beneficios.php"><i class="fa-solid fa-gift"></i> Beneficios</a></li>
            <li><a href="panel.php"><i class="fa-solid fa-house"></i> Mi Panel</a></li>
            <li><a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i> Salir</a></li>
        </ul>
    </aside>

    <main>
        <header>
            <div style="font-weight: 600; color: var(--text-muted);">Panel General / Dashboard Institucional</div>
            <span><i class="fa-solid fa-user-shield"></i> <?= htmlspecialchars($_SESSION['nombre_completo'] ?? 'Personal') ?></span>
        </header>

        <div class="content">
            <!-- COLUMNA PRINCIPAL -->
            <div>
                <!-- TARJETAS DE ESTADÍSTICAS -->
                <div class="grid-stats">
                    <div class="stat-card">
                        <span>ESTUDIANTES</span>
                        <strong><?= $total_estudiantes ?></strong>
                    </div>
                    <div class="stat-card">
                        <span>MASCOTAS ACTIVAS</span>
                        <strong><?= $total_mascotas ?></strong>
                    </div>
                    <div class="stat-card">
                        <span>NIVEL PROMEDIO</span>
                        <strong><?= round($promedio_nivel, 1) ?> / 10</strong>
                    </div>
                </div>

                <!-- MENÚ DEL COMEDOR -->
                <div class="card">
                    <h3><i class="fa-solid fa-utensils"></i> Menús Recientes Publicados</h3>
                    <table>
                        <thead><tr><th>Fecha</th><th>Plato</th><th>Descripción</th></tr></thead>
                        <tbody>
                            <?php if (count($menus) > 0): ?>
                                <?php foreach ($menus as $m): ?>
                                <tr>
                                    <td><strong style="color: var(--accent-gold);"><?= htmlspecialchars($m['fecha'] ?? 'N/A') ?></strong></td>
                                    <td><strong><?= htmlspecialchars($m['nombre'] ?? 'Sin nombre') ?></strong></td>
                                    <td><?= htmlspecialchars($m['descripcion'] ?? '') ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="3" style="text-align: center; color: var(--text-muted);">No hay menús registrados.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- MASCOTAS, NIVELES Y GRADOS -->
                <div class="card">
                    <h3><i class="fa-solid fa-paw"></i> Monitoreo de Mascotas y Grados</h3>
                    <table>
                        <thead><tr><th>Avatar</th><th>Mascota</th><th>Estudiante</th><th>Grado</th><th>Nivel</th></tr></thead>
                        <tbody>
                            <?php if (count($mascotas) > 0): ?>
                                <?php foreach ($mascotas as $mas): ?>
                                <?php $ico = $iconos_nivel[$mas['nivel_mascota']] ?? '🐣'; ?>
                                <tr>
                                    <td style="font-size: 1.5rem; text-align: center;"><?= $ico ?></td>
                                    <td><strong><?= htmlspecialchars($mas['nombre_mascota']) ?></strong></td>
                                    <td><?= htmlspecialchars($mas['nombre_completo']) ?></td>
                                    <td><span style="color: #34d399; font-weight: 600;"><?= htmlspecialchars($mas['nombre_grado'] ?? 'N/A') ?></span></td>
                                    <td><span class="badge-nivel">Nivel <?= $mas['nivel_mascota'] ?>/10</span></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="5" style="text-align: center; color: var(--text-muted);">No hay mascotas registradas.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- COLUMNA LATERAL (RANKING) -->
            <div>
                <div class="card">
                    <h3><i class="fa-solid fa-trophy"></i> Ranking Institucional</h3>
                    <table>
                        <thead><tr><th>Estudiante</th><th>Grado</th><th>Pts</th></tr></thead>
                        <tbody>
                            <?php if (count($ranking) > 0): ?>
                                <?php foreach ($ranking as $rk): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($rk['nombre_completo']) ?></strong></td>
                                    <td><span style="font-size: 0.8rem; color: #34d399;"><?= htmlspecialchars($rk['nombre_grado'] ?? 'N/A') ?></span></td>
                                    <td><strong style="color: var(--accent-gold);"><?= $rk['puntos_totales'] ?></strong></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="3" style="text-align: center; color: var(--text-muted);">Sin registros de ranking.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</body>
</html>