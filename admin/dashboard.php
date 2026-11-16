<?php
// admin/dashboard.php
session_start();
require_once __DIR__ . '/../includes/auth.php';
requerir_rol(['Administrador']);
require_once __DIR__ . '/../config/conexion.php';

$mensaje = '';

// Lógica para el reinicio semanal de puntos positivos (conservando negativos)
if (isset($_POST['reiniciar_semana'])) {
    $pdo->query("UPDATE usuarios SET puntos_totales = IF(puntos_totales > 0, 0, puntos_totales) WHERE id_rol = 3");
    $mensaje = "¡Cierre de semana exitoso! Se han reiniciado los puntos positivos a 0. Los saldos negativos se han conservado.";
}

// Métricas generales
$total_estudiantes = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE id_rol = 3")->fetchColumn();
$total_asistencias_hoy = $pdo->query("SELECT COUNT(DISTINCT id_usuario) FROM asistencias WHERE DATE(fecha_registro) = CURDATE()")->fetchColumn();

// Estadísticas y porcentajes por grado
$stats_grados = $pdo->query("
    SELECT g.nombre_grado, 
           COUNT(DISTINCT u.id_usuario) as total_matriculados,
           COUNT(DISTINCT a.id_usuario) as asistentes_hoy
    FROM grados g
    LEFT JOIN usuarios u ON g.id_grado = u.id_grado AND u.id_rol = 3
    LEFT JOIN asistencias a ON u.id_usuario = a.id_usuario AND DATE(a.fecha_registro) = CURDATE()
    GROUP BY g.id_grado, g.nombre_grado
    ORDER BY g.nombre_grado ASC
")->fetchAll();

// Estudiantes con más asistencias al comedor
$top_comelones = $pdo->query("
    SELECT u.nombre_completo, g.nombre_grado, COUNT(a.id_asistencia) as total_comidas, u.puntos_totales
    FROM usuarios u
    LEFT JOIN grados g ON u.id_grado = g.id_grado
    JOIN asistencias a ON u.id_usuario = a.id_usuario
    WHERE u.id_rol = 3
    GROUP BY u.id_usuario
    ORDER BY total_comidas DESC
    LIMIT 5
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel General | Administrador</title>
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
        .content { padding: 40px; display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
        .card { background-color: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 25px; }
        .card-full { grid-column: span 2; }
        h3 { color: var(--accent-gold); margin-bottom: 15px; }
        .metric-value { font-size: 2.2rem; font-weight: 700; color: white; margin-top: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; text-align: left; }
        th, td { padding: 12px; border-bottom: 1px solid var(--border-color); font-size: 0.9rem; }
        th { color: var(--accent-gold); background-color: rgba(245, 158, 11, 0.05); }
        .barra-progreso { background: #2e3548; border-radius: 6px; overflow: hidden; height: 10px; width: 100%; margin-top: 6px; }
        .progreso-fill { background: var(--accent-gold); height: 100%; }
        .btn-semana { background-color: #ef4444; color: white; font-weight: bold; padding: 12px 20px; border: none; border-radius: 8px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; }
        .alerta-exito { background-color: rgba(16, 185, 129, 0.1); border: 1px solid #10b981; color: #34d399; padding: 12px; border-radius: 8px; margin-bottom: 20px; grid-column: span 2; }
    </style>
</head>
<body>
    <aside>
        <div class="sidebar-brand"><i class="fa-solid fa-utensils"></i> Admin Pro</div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php" class="active"><i class="fa-solid fa-chart-pie"></i> Panel General</a></li>
            <li><a href="asistencia.php"><i class="fa-solid fa-barcode"></i> Control Asistencia</a></li>
            <li><a href="usuarios.php"><i class="fa-solid fa-users"></i> Usuarios y Carga</a></li>
            <li><a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i> Salir</a></li>
        </ul>
    </aside>

    <main>
        <header>
            <div style="font-weight: 600; color: var(--text-muted);">Panel de Control Principal</div>
            <span><i class="fa-solid fa-user-shield"></i> <?= htmlspecialchars($_SESSION['nombre_completo'] ?? 'Admin') ?></span>
        </header>

        <div class="content">
            <?php if ($mensaje): ?><div class="alerta-exito"><i class="fa-solid fa-check-circle"></i> <?= $mensaje ?></div><?php endif; ?>

            <div class="card card-full" style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h3 style="margin-bottom: 5px;"><i class="fa-solid fa-calendar-week"></i> Cierre Semanal de Puntos</h3>
                    <p style="color: var(--text-muted); font-size: 0.9rem;">Reinicia los puntos positivos a 0. Los saldos negativos se mantienen.</p>
                </div>
                <form method="POST" onsubmit="return confirm('¿Estás seguro de realizar el cierre semanal?');">
                    <button type="submit" name="reiniciar_semana" class="btn-semana"><i class="fa-solid fa-rotate"></i> Ejecutar Cierre</button>
                </form>
            </div>

            <div class="card">
                <span style="color: var(--text-muted);">Estudiantes Matriculados</span>
                <div class="metric-value"><?= $total_estudiantes ?></div>
            </div>
            <div class="card">
                <span style="color: var(--text-muted);">Asistencias Hoy</span>
                <div class="metric-value" style="color: #34d399;"><?= $total_asistencias_hoy ?></div>
            </div>

            <div class="card card-full">
                <h3><i class="fa-solid fa-chart-bar"></i> Porcentaje de Asistencia por Grado</h3>
                <table>
                    <thead>
                        <tr><th>Grado</th><th>Matriculados</th><th>Asistieron Hoy</th><th>% Asistencia</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stats_grados as $sg): 
                            $mat = $sg['total_matriculados'] > 0 ? $sg['total_matriculados'] : 1;
                            $asist = $sg['asistentes_hoy'];
                            $porcentaje = round(($asist / $mat) * 100, 1);
                        ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($sg['nombre_grado']) ?></strong></td>
                            <td><?= $sg['total_matriculados'] ?></td>
                            <td><strong style="color: #34d399;"><?= $asist ?></strong></td>
                            <td style="width: 40%;">
                                <span><?= $porcentaje ?>%</span>
                                <div class="barra-progreso">
                                    <div class="progreso-fill" style="width: <?= min($porcentaje, 100) ?>%;"></div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>