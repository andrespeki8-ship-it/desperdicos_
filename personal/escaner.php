<?php
session_start();
require_once __DIR__ . '/../includes/auth.php';
requerir_rol(['Personal', 'Administrador']);
require_once __DIR__ . '/../config/conexion.php';

$mensaje = '';
$error = '';
$estudiante_registrado = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['codigo_qr'])) {
    $input = trim($_POST['codigo_qr'] ?? '');
    
    // El QR trae formato: "DOCUMENTO:1111548041|USER:lagudelo" o solo el documento
    $documento = $input;
    if (strpos($input, 'DOCUMENTO:') !== false) {
        $partes = explode('|', $input);
        foreach ($partes as $p) {
            if (strpos($p, 'DOCUMENTO:') === 0) {
                $documento = trim(str_replace('DOCUMENTO:', '', $p));
            }
        }
    }

    if ($documento !== '') {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE documento = ? OR username = ? LIMIT 1");
        $stmt->execute([$documento, $documento]);
        $estudiante = $stmt->fetch();

        if ($estudiante) {
            // Registrar asistencia y otorgar 5 puntos
            $pdo->prepare("INSERT INTO asistencias (id_usuario, puntos_otorgados) VALUES (?, 5)")->execute([$estudiante['id_usuario']]);
            $pdo->prepare("UPDATE usuarios SET puntos_totales = puntos_totales + 5 WHERE id_usuario = ?")->execute([$estudiante['id_usuario']]);

            $estudiante_registrado = $estudiante['nombre_completo'];
            $mensaje = "¡Asistencia registrada con éxito para <strong>" . htmlspecialchars($estudiante_registrado) . "</strong> (+5 Puntos otorgados)!";
        } else {
            $error = "Estudiante no encontrado con el código: " . htmlspecialchars($input);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Escáner de Asistencia QR | Personal</title>
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
        .content { padding: 40px; display: flex; justify-content: center; }
        .card { background-color: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 35px; width: 100%; max-width: 600px; text-align: center; }
        h2 { color: var(--accent-gold); margin-bottom: 15px; font-size: 1.6rem; }
        input[type="text"] { width: 100%; padding: 16px; background-color: #12141c; border: 2px solid var(--accent-gold); border-radius: 8px; color: white; font-size: 1.2rem; text-align: center; margin-bottom: 20px; outline: none; }
        .alerta-exito { background-color: rgba(16, 185, 129, 0.1); border: 1px solid #10b981; color: #34d399; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-size: 1.1rem; }
        .alerta-error { background-color: rgba(239, 68, 68, 0.1); border: 1px solid #ef4444; color: #f87171; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-size: 1.1rem; }
        .btn-dorado { background-color: var(--accent-gold); color: #12141c; font-weight: 700; padding: 14px 28px; border: none; border-radius: 8px; cursor: pointer; font-size: 1.1rem; width: 100%; }
    </style>
</head>
<body>
    <aside>
        <div class="sidebar-brand"><i class="fa-solid fa-utensils"></i> Restaurante Pro</div>
        <ul class="sidebar-menu">
            <li><a href="escaner.php" class="active"><i class="fa-solid fa-qrcode"></i> Escáner de Asistencia</a></li>
            <li><a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i> Cerrar Sesión</a></li>
        </ul>
    </aside>

    <main>
        <header>
            <div style="font-weight: 600; color: var(--text-muted);">Panel Operativo de Asistencia</div>
            <span><i class="fa-solid fa-user"></i> <?= htmlspecialchars($_SESSION['nombre_completo'] ?? 'Personal') ?></span>
        </header>

        <div class="content">
            <div class="card">
                <h2><i class="fa-solid fa-barcode"></i> Lector de Credenciales QR</h2>
                <p style="color: var(--text-muted); margin-bottom: 25px;">Escanee el código QR del estudiante. El sistema registrará la asistencia y sumará automáticamente +5 puntos.</p>

                <?php if ($mensaje): ?><div class="alerta-exito"><i class="fa-solid fa-check-circle"></i> <?= $mensaje ?></div><?php endif; ?>
                <?php if ($error): ?><div class="alerta-error"><i class="fa-solid fa-triangle-exclamation"></i> <?= $error ?></div><?php endif; ?>

                <form method="POST" id="formScanner">
                    <input type="text" name="codigo_qr" id="codigo_qr" placeholder="Esperando escaneo QR..." autofocus autocomplete="off" required>
                    <button type="submit" class="btn-dorado"><i class="fa-solid fa-check"></i> Registrar Asistencia</button>
                </form>
            </div>
        </div>
    </main>

    <script>
        // Mantener siempre el foco en el input para lectura consecutiva con pistola láser o cámara
        const input = document.getElementById('codigo_qr');
        setInterval(() => {
            if (document.activeElement !== input) {
                input.focus();
            }
        }, 1000);
    </script>
</body>
</html>