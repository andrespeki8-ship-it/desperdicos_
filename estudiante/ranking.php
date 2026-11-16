<?php
session_start();
require_once __DIR__ . '/../includes/auth.php';
requerir_login();
require_once __DIR__ . '/../config/conexion.php';

// Obtener ranking de usuarios ordenados por puntos de mayor a menor
$ranking = $pdo->query("SELECT u.nombre_completo, u.puntos_totales, g.nombre_grado, r.nombre_rol FROM usuarios u LEFT JOIN grados g ON u.id_grado = g.id_grado JOIN roles r ON u.id_rol = r.id_rol ORDER BY u.puntos_totales DESC LIMIT 20")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ranking Gamificado | Restaurante</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --bg-main: #12141c; --bg-card: #1a1d29; --accent-gold: #f59e0b; --text-main: #f3f4f6; --text-muted: #9ca3af; --border-color: #2e3548; }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Inter', sans-serif; }
        body { background-color: var(--bg-main); color: var(--text-main); padding: 40px; }
        .container { max-width: 800px; margin: 0 auto; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 30px; }
        h1 { color: var(--accent-gold); margin-bottom: 20px; font-size: 1.8rem; display: flex; align-items: center; gap: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; text-align: left; }
        th, td { padding: 14px; border-bottom: 1px solid var(--border-color); font-size: 0.95rem; }
        th { color: var(--accent-gold); background-color: rgba(245, 158, 11, 0.05); }
        .podium-1 { color: #f59e0b; font-weight: bold; }
        .btn-volver { display: inline-block; margin-bottom: 20px; color: var(--accent-gold); text-decoration: none; font-weight: 600; }
    </style>
</head>
<body>
    <div class="container">
        <a href="javascript:history.back()" class="btn-volver"><i class="fa-solid fa-arrow-left"></i> Volver al Panel</a>
        <h1><i class="fa-solid fa-trophy"></i> Tabla de Ranking Institucional</h1>
        <p style="color: var(--text-muted); margin-bottom: 20px;">Estudiantes y usuarios con mayor acumulación de puntos por hábitos sostenibles y cero desperdicio.</p>
        
        <table>
            <thead>
                <tr>
                    <th>Posición</th>
                    <th>Nombre Completo</th>
                    <th>Grado</th>
                    <th>Puntos Totales</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $pos = 1;
                foreach ($ranking as $r): 
                ?>
                <tr>
                    <td>
                        <?php if($pos == 1): ?>⭐ 1º<?php elseif($pos == 2): ?>🥈 2º<?php elseif($pos == 3): ?>🥉 3º<?php else: ?>#<?= $pos ?><?php endif; ?>
                    </td>
                    <td class="<?= $pos == 1 ? 'podium-1' : '' ?>"><?= htmlspecialchars($r['nombre_completo']) ?></td>
                    <td><?= htmlspecialchars($r['nombre_grado'] ?? 'Personal') ?></td>
                    <td><strong style="color: var(--accent-gold);"><?= $r['puntos_totales'] ?> pts</strong></td>
                </tr>
                <?php 
                $pos++;
                endforeach; 
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>