<?php
session_start();
require_once __DIR__ . '/../includes/auth.php';
requerir_rol(['Administrador']); // Función correcta de autenticación
require_once __DIR__ . '/../config/conexion.php';

// Consulta del ranking de estudiantes ordenados por puntos
$ranking = $pdo->query("SELECT u.nombre_completo, u.puntos_totales, g.nombre_grado FROM usuarios u LEFT JOIN grados g ON u.id_grado = g.id_grado WHERE u.id_rol = 3 ORDER BY u.puntos_totales DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ranking de Estudiantes | Admin</title>
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
        .card { background-color: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 25px; }
        h2 { color: var(--accent-gold); margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; text-align: left; }
        th, td { padding: 12px; border-bottom: 1px solid var(--border-color); font-size: 0.9rem; }
        th { color: var(--accent-gold); background-color: rgba(245, 158, 11, 0.05); }
    </style>
</head>
<body>
    <aside>
        <div class="sidebar-brand"><i class="fa-solid fa-utensils"></i> Admin Pro</div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php"><i class="fa-solid fa-chart-pie"></i> Panel General</a></li>
            <li><a href="asistencia.php"><i class="fa-solid fa-barcode"></i> Control Asistencia</a></li>
            <li><a href="usuarios.php"><i class="fa-solid fa-users"></i> Usuarios</a></li>
            <li><a href="ranking.php" class="active"><i class="fa-solid fa-trophy"></i> Ranking</a></li>
            <li><a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i> Salir</a></li>
        </ul>
    </aside>

    <main>
        <header>
            <div style="font-weight: 600; color: var(--text-muted);">Módulo de Ranking Institucional</div>
            <span><i class="fa-solid fa-user-shield"></i> Admin</span>
        </header>

        <div class="content">
            <div class="card">
                <h2><i class="fa-solid fa-trophy"></i> Tabla de Posiciones y Puntos de Estudiantes</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Posición</th>
                            <th>Estudiante</th>
                            <th>Grado</th>
                            <th>Puntos Acumulados</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $p = 1; foreach ($ranking as $r): ?>
                        <tr>
                            <td><?= $p == 1 ? '⭐ 1º' : ($p == 2 ? '🥈 2º' : ($p == 3 ? '🥉 3º' : '#' . $p)) ?></td>
                            <td><strong><?= htmlspecialchars($r['nombre_completo']) ?></strong></td>
                            <td><?= htmlspecialchars($r['nombre_grado'] ?? 'N/A') ?></td>
                            <td><strong style="color: #34d399;"><?= $r['puntos_totales'] ?> pts</strong></td>
                        </tr>
                        <?php $p++; endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>