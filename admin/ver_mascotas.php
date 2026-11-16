<?php
session_start();
require_once __DIR__ . '/../includes/auth.php';
requerir_rol(['Administrador']); // Solo accesible para administradores
require_once __DIR__ . '/../config/conexion.php';

// Consultar todas las mascotas con datos del estudiante y su grado
$stmt = $pdo->query("
    SELECT m.*, u.nombre_completo, g.nombre_grado 
    FROM mascotas m
    JOIN usuarios u ON m.usuario_id = u.id_usuario
    LEFT JOIN grados g ON u.id_grado = g.id_grado
    ORDER BY m.nivel_mascota DESC, m.experiencia DESC
");
$mascotas = $stmt->fetchAll();

// Iconos correspondientes a cada nivel de evolución
$iconos_nivel = [
    1 => '🐣', 2 => '🐥', 3 => '🦊', 4 => '🐺', 5 => '🦁',
    6 => '🐯', 7 => '🦅', 8 => '🐲', 9 => '🐉', 10 => '👑⚡'
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Mascotas | Panel Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --bg: #090a0f; --card: #1a1d29; --border: #2e3548; --accent: #f59e0b; --text: #f3f4f6; }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Outfit', sans-serif; }
        body { background-color: var(--bg); color: var(--text); padding: 30px; }
        .container { max-width: 1000px; margin: 0 auto; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .btn-volver { background: var(--accent); color: #12141c; padding: 10px 18px; border-radius: 8px; text-decoration: none; font-weight: bold; }
        .card { background: var(--card); border: 1px solid var(--border); border-radius: 14px; padding: 25px; box-shadow: 0 4px 12px rgba(0,0,0,0.4); }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; text-align: left; }
        th, td { padding: 14px; border-bottom: 1px solid var(--border); font-size: 0.95rem; }
        th { color: var(--accent); background-color: rgba(245, 158, 11, 0.05); }
        .badge-nivel { background: rgba(245, 158, 11, 0.2); color: var(--accent); padding: 4px 10px; border-radius: 12px; font-weight: bold; font-size: 0.85rem; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1 style="color: var(--accent);"><i class="fa-solid fa-paw"></i> Control de Mascotas Escolares</h1>
                <p style="color: #9ca3af; font-size: 0.9rem;">Monitorea los niveles, evolución corporal y grados de las mascotas de los estudiantes.</p>
            </div>
            <a href="panel.php" class="btn-volver"><i class="fa-solid fa-arrow-left"></i> Volver al Panel Admin</a>
        </div>

        <div class="card">
            <h3><i class="fa-solid fa-list"></i> Listado General de Criaturas</h3>
            <table>
                <thead>
                    <tr>
                        <th>Avatar</th>
                        <th>Nombre Criatura</th>
                        <th>Estudiante Dueño</th>
                        <th>Grado</th>
                        <th>Nivel</th>
                        <th>Experiencia (EXP)</th>
                        <th>Vidas</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($mascotas) > 0): ?>
                        <?php foreach ($mascotas as $m): ?>
                            <?php $icono = $iconos_nivel[$m['nivel_mascota']] ?? '🐣'; ?>
                            <tr>
                                <td style="font-size: 1.8rem; text-align: center;"><?= $icono ?></td>
                                <td><strong><?= htmlspecialchars($m['nombre_mascota']) ?></strong></td>
                                <td><?= htmlspecialchars($m['nombre_completo']) ?></td>
                                <td><span style="color: #34d399; font-weight: 600;"><?= htmlspecialchars($m['nombre_grado'] ?? 'Sin Grado') ?></span></td>
                                <td><span class="badge-nivel">Nivel <?= $m['nivel_mascota'] ?> / 10</span></td>
                                <td><strong><?= $m['experiencia'] ?> pts</strong></td>
                                <td style="color: #f43f5e;">❤️ <?= $m['vidas'] ?> / 3</td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" style="text-align: center; color: #9ca3af; padding: 20px;">No hay mascotas registradas todavía.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>