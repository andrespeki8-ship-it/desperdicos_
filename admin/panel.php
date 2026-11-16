<?php
session_start();
require_once __DIR__ . '/../includes/auth.php';
requerir_rol(['Administrador']);
require_once __DIR__ . '/../config/conexion.php';

// Obtener estadísticas generales para el dashboard
$total_estudiantes = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE id_rol = 3")->fetchColumn();
$total_mascotas = $pdo->query("SELECT COUNT(*) FROM mascotas")->fetchColumn();
$promedio_nivel = $pdo->query("SELECT AVG(nivel_mascota) FROM mascotas")->fetchColumn();
$total_menus = $pdo->query("SELECT COUNT(*) FROM menu")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administración | Desperdicios de Comida AE</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --bg: #090a0f; --card: #1a1d29; --border: #2e3548; --accent: #f59e0b; --text: #f3f4f6; }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Outfit', sans-serif; }
        body { background-color: var(--bg); color: var(--text); display: flex; min-height: 100vh; }
        
        aside { width: 280px; background-color: var(--card); border-right: 1px solid var(--border); display: flex; flex-direction: column; position: fixed; height: 100vh; }
        .sidebar-brand { padding: 25px; font-size: 1.25rem; font-weight: 800; color: var(--accent); display: flex; align-items: center; gap: 10px; border-bottom: 1px solid var(--border); }
        .sidebar-menu { list-style: none; padding: 20px 0; flex-grow: 1; }
        .sidebar-menu li a { display: flex; align-items: center; gap: 15px; padding: 15px 25px; color: #9ca3af; text-decoration: none; font-weight: 600; transition: all 0.3s ease; }
        .sidebar-menu li a:hover, .sidebar-menu li a.active { background-color: rgba(245, 158, 11, 0.1); color: var(--accent); border-left: 4px solid var(--accent); }

        main { margin-left: 280px; flex-grow: 1; padding: 40px; }
        header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        
        .grid-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: var(--card); border: 1px solid var(--border); border-radius: 16px; padding: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.3); }
        .stat-card span { font-size: 0.8rem; color: #9ca3af; display: block; margin-bottom: 5px; }
        .stat-card strong { font-size: 1.8rem; color: var(--accent); }

        .card { background: var(--card); border: 1px solid var(--border); border-radius: 16px; padding: 25px; margin-bottom: 25px; }
        .card h3 { color: var(--accent); margin-bottom: 15px; display: flex; align-items: center; gap: 10px; }
        
        .btn-accion { background: var(--accent); color: #090a0f; padding: 12px 20px; border-radius: 10px; text-decoration: none; font-weight: bold; display: inline-flex; align-items: center; gap: 8px; }
    </style>
</head>
<body>
    <aside>
        <div class="sidebar-brand"><i class="fa-solid fa-shield-halved"></i> Admin Pro</div>
        <ul class="sidebar-menu">
            <li><a href="panel.php" class="active"><i class="fa-solid fa-chart-line"></i> Dashboard General</a></li>
            <li><a href="ver_mascotas.php"><i class="fa-solid fa-paw"></i> Mascotas Escolares</a></li>
            <li><a href="gestionar_menu.php"><i class="fa-solid fa-utensils"></i> Gestionar Menú</a></li>
            <li><a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i> Salir</a></li>
        </ul>
    </aside>

    <main>
        <header>
            <div>
                <h1>Panel de Control Administrativo</h1>
                <p style="color: #9ca3af; font-size: 0.9rem;">Bienvenido al sistema de control de Desperdicios de Comida AE.</p>
            </div>
            <span style="background: rgba(16,185,129,0.15); color: #34d399; padding: 8px 16px; border-radius: 20px; font-weight: bold;">Sesión Segura</span>
        </header>

        <!-- TARJETAS DE ESTADÍSTICAS -->
        <div class="grid-stats">
            <div class="stat-card">
                <span>ESTUDIANTES REGISTRADOS</span>
                <strong><?= $total_estudiantes ?></strong>
            </div>
            <div class="stat-card">
                <span>MASCOTAS ACTIVAS</span>
                <strong><?= $total_mascotas ?></strong>
            </div>
            <div class="stat-card">
                <span>NIVEL PROMEDIO MASCOTAS</span>
                <strong><?= round($promedio_nivel, 1) ?> / 10</strong>
            </div>
            <div class="stat-card">
                <span>MENÚS PUBLICADOS</span>
                <strong><?= $total_menus ?></strong>
            </div>
        </div>

        <div class="card">
            <h3><i class="fa-solid fa-bolt"></i> Accesos Rápidos de Gestión</h3>
            <p style="color: #9ca3af; font-size: 0.9rem; margin-bottom: 20px;">Administra los menús institucionales del comedor o supervisa las criaturas virtuales de los estudiantes.</p>
            <div style="display: flex; gap: 15px;">
                <a href="ver_mascotas.php" class="btn-accion"><i class="fa-solid fa-paw"></i> Ver Mascotas y Niveles</a>
                <a href="gestionar_menu.php" class="btn-accion" style="background: #10b981; color: #090a0f;"><i class="fa-solid fa-utensils"></i> Administrar Menú Escolar</a>
            </div>
        </div>
    </main>
</body>
</html>