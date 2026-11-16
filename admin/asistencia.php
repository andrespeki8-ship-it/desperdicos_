<?php
session_start();
require_once __DIR__ . '/../includes/auth.php';
requerir_rol(['Administrador']);
require_once __DIR__ . '/../config/conexion.php';

$mensaje = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['codigo_qr'])) {
    $input = trim($_POST['codigo_qr'] ?? '');
    
    if ($input !== '') {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE documento = ? OR username = ? LIMIT 1");
        $stmt->execute([$input, $input]);
        $estudiante = $stmt->fetch();

        if ($estudiante) {
            // CORREGIDO: Se especifica fecha_registro con NOW() y puntos_otorgados
            $pdo->prepare("INSERT INTO asistencias (id_usuario, fecha_registro, puntos_otorgados) VALUES (?, NOW(), 5)")->execute([$estudiante['id_usuario']]);
            $pdo->prepare("UPDATE usuarios SET puntos_totales = puntos_totales + 5 WHERE id_usuario = ?")->execute([$estudiante['id_usuario']]);
            $mensaje = "¡Asistencia registrada para <strong>" . htmlspecialchars($estudiante['nombre_completo']) . "</strong> (+5 Puntos otorgados)!";
        } else {
            $error = "No se encontró ningún estudiante con el documento: " . htmlspecialchars($input);
        }
    }
}

// CORREGIDO: Se cambia fecha_asistencia por fecha_registro en el SELECT y en el ORDER BY
$historial_asistencias = $pdo->query("SELECT a.fecha_registro, u.nombre_completo, u.documento, g.nombre_grado, a.puntos_otorgados FROM asistencias a JOIN usuarios u ON a.id_usuario = u.id_usuario LEFT JOIN grados g ON u.id_grado = g.id_grado ORDER BY a.fecha_registro DESC LIMIT 30")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Control de Asistencia QR | Admin</title>
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
        input[type="text"] { width: 100%; padding: 16px; background-color: #12141c; border: 2px solid var(--accent-gold); border-radius: 8px; color: white; font-size: 1.2rem; text-align: center; margin-bottom: 15px; outline: none; }
        .btn-dorado { background-color: var(--accent-gold); color: #12141c; font-weight: 700; padding: 12px; border: none; border-radius: 8px; cursor: pointer; width: 100%; font-size: 1rem; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; text-align: left; }
        th, td { padding: 12px; border-bottom: 1px solid var(--border-color); font-size: 0.9rem; }
        th { color: var(--accent-gold); background-color: rgba(245, 158, 11, 0.05); }
        .alerta-exito { background-color: rgba(16, 185, 129, 0.1); border: 1px solid #10b981; color: #34d399; padding: 12px; border-radius: 8px; margin-bottom: 15px; }
        .alerta-error { background-color: rgba(239, 68, 68, 0.1); border: 1px solid #ef4444; color: #f87171; padding: 12px; border-radius: 8px; margin-bottom: 15px; }
    </style>
</head>
<body>
    <aside>
        <div class="sidebar-brand"><i class="fa-solid fa-utensils"></i> Admin Pro</div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php"><i class="fa-solid fa-chart-pie"></i> Panel General</a></li>
            <li><a href="asistencia.php" class="active"><i class="fa-solid fa-barcode"></i> Control Asistencia</a></li>
            <li><a href="usuarios.php"><i class="fa-solid fa-users"></i> Usuarios</a></li>
            <li><a href="menu.php"><i class="fa-solid fa-utensils"></i> Menú</a></li>
            <li><a href="permisos.php"><i class="fa-solid fa-shield-halved"></i> Permisos y Roles</a></li>
            <li><a href="qr_generator.php"><i class="fa-solid fa-qrcode"></i> Generador QR</a></li>
            <li><a href="ranking.php"><i class="fa-solid fa-trophy"></i> Ranking</a></li>
            <li><a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i> Salir</a></li>
        </ul>
    </aside>

    <main>
        <header>
            <div style="font-weight: 600; color: var(--text-muted);">Módulo de Asistencia Directa</div>
            <span><i class="fa-solid fa-user-shield"></i> <?= htmlspecialchars($_SESSION['nombre_completo'] ?? 'Admin') ?></span>
        </header>

        <div class="content">
            <div class="card">
                <h3><i class="fa-solid fa-qrcode"></i> Lector Rápido por QR (Admin)</h3>
                <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 15px;">Escanea la credencial para registrar la asistencia con fecha/hora y sumar +5 puntos.</p>
                <?php if ($mensaje): ?><div class="alerta-exito"><?= $mensaje ?></div><?php endif; ?>
                <?php if ($error): ?><div class="alerta-error"><?= $error ?></div><?php endif; ?>
                <form method="POST">
                    <input type="text" name="codigo_qr" id="codigo_qr" placeholder="Escanee o digite documento..." autofocus autocomplete="off" required>
                    <button type="submit" class="btn-dorado">Registrar Asistencia</button>
                </form>
            </div>

            <div class="card">
                <h3><i class="fa-solid fa-trophy"></i> Resumen Rápido</h3>
                <p style="color: var(--text-muted); font-size: 0.95rem; line-height: 1.6;">
                    Desde aquí el administrador puede supervisar en tiempo real los ingresos de los estudiantes al comedor escolar, manteniendo sincronizados los puntos y el historial general de asistencias de toda la institución.
                </p>
            </div>

            <div class="card card-full">
                <h3><i class="fa-solid fa-clipboard-user"></i> Historial Completo de Asistencias (Día, Hora y Estudiante)</h3>
                <table>
                    <thead><tr><th>Fecha y Hora</th><th>Estudiante</th><th>Documento</th><th>Grado</th><th>Puntos Otorgados</th></tr></thead>
                    <tbody>
                        <?php foreach ($historial_asistencias as $ha): ?>
                        <tr>
                            <td><span style="color: var(--accent-gold); font-weight: bold;"><?= htmlspecialchars($ha['fecha_registro']) ?></span></td>
                            <td><strong><?= htmlspecialchars($ha['nombre_completo']) ?></strong></td>
                            <td><?= htmlspecialchars($ha['documento']) ?></td>
                            <td><?= htmlspecialchars($ha['nombre_grado'] ?? 'N/A') ?></td>
                            <td><span style="color: #34d399; font-weight: bold;">+<?= $ha['puntos_otorgados'] ?> pts</span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        const input = document.getElementById('codigo_qr');
        setInterval(() => { if (document.activeElement !== input) { input.focus(); } }, 1000);
    </script>
</body>
</html>