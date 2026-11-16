<?php
session_start();
require_once __DIR__ . '/../includes/auth.php';
requerir_rol(['Padre']);
require_once __DIR__ . '/../config/conexion.php';

$id_padre = $_SESSION['id_usuario'];
$alerta = '';
$tipo_alerta = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion_vincular'])) {
    $codigo_estudiante = trim($_POST['codigo_estudiante'] ?? '');
    if (!empty($codigo_estudiante)) {
        $stmt_e = $pdo->prepare("SELECT id_usuario, nombre_completo FROM usuarios WHERE (documento = ? OR username = ?) AND id_rol = 3");
        $stmt_e->execute([$codigo_estudiante, $codigo_estudiante]);
        $est = $stmt_e->fetch();

        if ($est) {
            $stmt_vin = $pdo->prepare("INSERT IGNORE INTO padre_estudiante (padre_id, estudiante_id) VALUES (?, ?)");
            $stmt_vin->execute([$id_padre, $est['id_usuario']]);
            $alerta = "Estudiante <strong>" . htmlspecialchars($est['nombre_completo']) . "</strong> vinculado correctamente.";
            $tipo_alerta = "exito";
        } else {
            $alerta = "No se encontró ningún estudiante con ese documento.";
            $tipo_alerta = "error";
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion_sugerencia'])) {
    $sugerencia = trim($_POST['mensaje_sugerencia'] ?? '');
    $palabras_prohibidas = ['basura', 'porqueria', 'estupido', 'idiota', 'mierda', 'asco'];
    $tiene_groserias = false;
    foreach ($palabras_prohibidas as $mala) {
        if (stripos($sugerencia, $mala) !== false) { $tiene_groserias = true; break; }
    }

    if (empty($sugerencia)) {
        $alerta = "El mensaje no puede estar vacío.";
        $tipo_alerta = "error";
    } elseif (mb_strlen($sugerencia) < 20) {
        $alerta = "Filtro de Calidad: Escribe al menos 20 caracteres detallando tu aporte.";
        $tipo_alerta = "error";
    } elseif ($tiene_groserias) {
        $alerta = "Filtro de Calidad: Tu mensaje contiene lenguaje inapropiado.";
        $tipo_alerta = "error";
    } else {
        $stmt_sug = $pdo->prepare("INSERT INTO buzon_sugerencias (padre_id, mensaje, cumple_requisitos) VALUES (?, ?, 1)");
        $stmt_sug->execute([$id_padre, $sugerencia]);
        $alerta = "¡Sugerencia enviada con éxito al administrador!";
        $tipo_alerta = "exito";
    }
}

$stmt_hijos = $pdo->prepare("
    SELECT u.id_usuario, u.nombre_completo, u.documento, u.puntos_totales, g.nombre_grado
    FROM padre_estudiante pe
    JOIN usuarios u ON pe.estudiante_id = u.id_usuario
    LEFT JOIN grados g ON u.id_grado = g.id_grado
    WHERE pe.padre_id = ?
");
$stmt_hijos->execute([$id_padre]);
$estudiantes_vinculados = $stmt_hijos->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Portal de Padres | Restaurante Escolar</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --bg: #12141c; --card: #1a1d29; --gold: #f59e0b; --border: #2e3548; --text: #f3f4f6; }
        body { background: var(--bg); color: var(--text); font-family: 'Inter', sans-serif; padding: 30px; margin: 0; }
        .container { max-width: 960px; margin: 0 auto; }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border); padding-bottom: 20px; margin-bottom: 30px; }
        .card { background: var(--card); border: 1px solid var(--border); border-radius: 12px; padding: 25px; margin-bottom: 25px; }
        h2, h3 { color: var(--gold); margin-top: 0; }
        input, textarea { width: 100%; padding: 12px; background: var(--bg); border: 1px solid var(--border); border-radius: 8px; color: white; margin: 8px 0 16px; box-sizing: border-box; }
        .btn { background: var(--gold); color: #12141c; font-weight: 700; padding: 12px 22px; border: none; border-radius: 8px; cursor: pointer; }
        .alerta-exito { background: rgba(16, 185, 129, 0.12); border: 1px solid #10b981; color: #34d399; padding: 14px; border-radius: 8px; margin-bottom: 20px; }
        .alerta-error { background: rgba(239, 68, 68, 0.12); border: 1px solid #ef4444; color: #f87171; padding: 14px; border-radius: 8px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 10px; border-bottom: 1px solid var(--border); text-align: left; font-size: 0.9rem; }
        th { color: var(--gold); }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1 style="margin:0; font-size:1.5rem;">Portal de Padres y Acudientes</h1>
                <span style="color:#9ca3af; font-size:0.9rem;">Bienvenido, <?= htmlspecialchars($_SESSION['nombre_completo']) ?></span>
            </div>
            <a href="../logout.php" style="color:#ef4444; text-decoration:none; font-weight:600;"><i class="fa-solid fa-right-from-bracket"></i> Salir</a>
        </div>

        <?php if (!empty($alerta)): ?>
            <div class="<?= ($tipo_alerta === 'exito') ? 'alerta-exito' : 'alerta-error' ?>"><?= $alerta ?></div>
        <?php endif; ?>

        <div class="card">
            <h3><i class="fa-solid fa-link"></i> Vincular Estudiante</h3>
            <form method="POST" style="display:flex; gap:12px; align-items:center;">
                <input type="hidden" name="accion_vincular" value="1">
                <input type="text" name="codigo_estudiante" required placeholder="Número de documento del estudiante..." style="margin:0;">
                <button type="submit" class="btn" style="white-space:nowrap;">Vincular</button>
            </form>
        </div>

        <div class="card">
            <h3><i class="fa-solid fa-clipboard-list"></i> Historial del Estudiante en el Comedor</h3>
            <?php if (empty($estudiantes_vinculados)): ?>
                <p style="color:#9ca3af;">Aún no tienes estudiantes vinculados.</p>
            <?php else: ?>
                <?php foreach ($estudiantes_vinculados as $hijo): ?>
                    <div style="background: rgba(0,0,0,0.2); padding: 18px; border-radius: 10px; margin-bottom: 18px; border: 1px solid var(--border);">
                        <div style="display:flex; justify-content:space-between; align-items:center;">
                            <div>
                                <strong style="font-size:1.1rem;"><?= htmlspecialchars($hijo['nombre_completo']) ?></strong>
                                <span style="color:#9ca3af; font-size:0.85rem;"> • Grado: <?= htmlspecialchars($hijo['nombre_grado'] ?? 'N/A') ?></span>
                            </div>
                            <span style="background:rgba(52,211,153,0.15); color:#34d399; padding:5px 12px; border-radius:20px; font-weight:bold;">
                                <?= $hijo['puntos_totales'] ?> Puntos Eco
                            </span>
                        </div>

                        <?php
                            $stmt_hist = $pdo->prepare("SELECT fecha_registro, puntos_otorgados, motivo FROM asistencias WHERE id_usuario = ? ORDER BY fecha_registro DESC LIMIT 5");
                            $stmt_hist->execute([$hijo['id_usuario']]);
                            $registros = $stmt_hist->fetchAll();
                        ?>

                        <?php if (!empty($registros)): ?>
                            <table>
                                <thead><tr><th>Fecha y Hora</th><th>Motivo</th><th>Impacto</th></tr></thead>
                                <tbody>
                                    <?php foreach ($registros as $reg): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($reg['fecha_registro']) ?></td>
                                            <td><?= htmlspecialchars($reg['motivo'] ?? 'Asistencia al comedor') ?></td>
                                            <td style="color:#34d399; font-weight:bold;">+<?= $reg['puntos_otorgados'] ?> pts</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="card">
            <h3><i class="fa-solid fa-envelope-open-text"></i> Buzón de Sugerencias Inteligente</h3>
            <form method="POST">
                <input type="hidden" name="accion_sugerencia" value="1">
                <textarea name="mensaje_sugerencia" rows="4" required placeholder="Escribe tu sugerencia constructiva (mínimo 20 caracteres)..."></textarea>
                <button type="submit" class="btn"><i class="fa-solid fa-paper-plane"></i> Enviar Sugerencia</button>
            </form>
        </div>
    </div>
</body>
</html>