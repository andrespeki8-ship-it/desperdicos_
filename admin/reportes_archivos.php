<?php
session_start();
require_once __DIR__ . '/../includes/auth.php';
requerir_rol(['Administrador']);
require_once __DIR__ . '/../config/conexion.php';

$mensaje = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['documento_subido'])) {
    $archivo = $_FILES['documento_subido'];
    if ($archivo['error'] === UPLOAD_ERR_OK) {
        $nombre_original = basename($archivo['name']);
        $nombre_servidor = time() . "_" . preg_replace("/[^a-zA-Z0-9\._-]/", "", $nombre_original);
        $ruta_destino = __DIR__ . '/../uploads/';

        if (!is_dir($ruta_destino)) {
            mkdir($ruta_destino, 0777, true);
        }

        if (move_uploaded_file($archivo['tmp_name'], $ruta_destino . $nombre_servidor)) {
            $stmt = $pdo->prepare("INSERT INTO archivos_subidos (nombre_original, nombre_servidor) VALUES (?, ?)");
            $stmt->execute([$nombre_original, $nombre_servidor]);
            $mensaje = "¡Archivo guardado con éxito en el sistema!";
        } else {
            $error = "Error al mover el archivo al servidor.";
        }
    } else {
        $error = "Por favor selecciona un archivo válido.";
    }
}

$archivos = $pdo->query("SELECT * FROM archivos_subidos ORDER BY fecha_subida DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Archivos | Admin</title>
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
        main { margin-left: 280px; flex-grow: 1; display: flex; flex-direction: column; }
        header { background-color: var(--bg-card); border-bottom: 1px solid var(--border-color); padding: 20px 40px; display: flex; justify-content: space-between; align-items: center; }
        .content { padding: 40px; }
        .card { background-color: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 25px; margin-bottom: 30px; }
        h2, h3 { color: var(--accent-gold); margin-bottom: 15px; }
        input[type="file"] { width: 100%; padding: 12px; background-color: #12141c; border: 1px solid var(--border-color); border-radius: 8px; color: white; margin-bottom: 15px; }
        .btn-dorado { background-color: var(--accent-gold); color: #12141c; font-weight: 700; padding: 12px 24px; border: none; border-radius: 8px; cursor: pointer; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; text-align: left; }
        th, td { padding: 14px; border-bottom: 1px solid var(--border-color); font-size: 0.9rem; }
        th { color: var(--accent-gold); background-color: rgba(245, 158, 11, 0.05); }
        .alerta-exito { background-color: rgba(16, 185, 129, 0.1); border: 1px solid #10b981; color: #34d399; padding: 12px; border-radius: 8px; margin-bottom: 20px; }
        .alerta-error { background-color: rgba(239, 68, 68, 0.1); border: 1px solid #ef4444; color: #f87171; padding: 12px; border-radius: 8px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <aside>
        <div class="sidebar-brand"><i class="fa-solid fa-utensils"></i> Admin Pro</div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php"><i class="fa-solid fa-chart-pie"></i> Panel General</a></li>
            <li><a href="asistencia.php"><i class="fa-solid fa-barcode"></i> Control Asistencia</a></li>
            <li><a href="usuarios.php"><i class="fa-solid fa-users"></i> Usuarios y Carga Masiva</a></li>
            <li><a href="reportes_archivos.php" class="active"><i class="fa-solid fa-folder-open"></i> Archivos y Reportes</a></li>
            <li><a href="qr_generator.php"><i class="fa-solid fa-qrcode"></i> Generador QR</a></li>
            <li><a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i> Salir</a></li>
        </ul>
    </aside>

    <main>
        <header>
            <div style="font-weight: 600; color: var(--text-muted);">Repositorio y Carga de Archivos</div>
            <span><i class="fa-solid fa-user-shield"></i> Administrador</span>
        </header>

        <div class="content">
            <h2>Repositorio de Archivos y Listas</h2>
            <?php if ($mensaje): ?><div class="alerta-exito"><?= $mensaje ?></div><?php endif; ?>
            <?php if ($error): ?><div class="alerta-error"><?= $error ?></div><?php endif; ?>

            <div class="card">
                <h3><i class="fa-solid fa-cloud-arrow-up"></i> Subir y Almacenar Archivo (Excel / CSV / Reportes)</h3>
                <form method="POST" enctype="multipart/form-data">
                    <label style="color: var(--text-muted); display:block; margin-bottom:8px;">Seleccionar archivo desde tu equipo:</label>
                    <input type="file" name="documento_subido" required>
                    <button type="submit" class="btn-dorado">Subir y Guardar en el Sistema</button>
                </form>
            </div>

            <div class="card">
                <h3><i class="fa-solid fa-file-lines"></i> Archivos Guardados</h3>
                <table>
                    <thead><tr><th>Nombre del Archivo</th><th>Fecha de Subida</th><th>Acción</th></tr></thead>
                    <tbody>
                        <?php foreach ($archivos as $a): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($a['nombre_original']) ?></strong></td>
                            <td><span style="color: var(--accent-gold);"><?= htmlspecialchars($a['fecha_subida']) ?></span></td>
                            <td>
                                <a href="../uploads/<?= htmlspecialchars($a['nombre_servidor']) ?>" target="_blank" style="color: #38bdf8; text-decoration: none; font-weight: bold;"><i class="fa-solid fa-download"></i> Descargar / Ver</a>
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