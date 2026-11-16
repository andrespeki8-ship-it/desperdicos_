<?php
require_once __DIR__ . '/../includes/auth.php';
requerirRol(['Administrador', 'Personal']);
require_once __DIR__ . '/../includes/header.php';

$mensaje = '';
$tipoAlerta = 'error';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigoQr = trim($_POST['codigo_qr'] ?? '');

    if ($codigoQr !== '') {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE codigo_qr = :qr AND id_rol = 3 AND activo = 1 LIMIT 1");
        $stmt->execute([':qr' => $codigoQr]);
        $estudiante = $stmt->fetch();

        if ($estudiante) {
            $idEstudiante = $estudiante['id_usuario'];
            
            // Verificar si ya registró asistencia hoy
            $stmtCheck = $pdo->prepare("SELECT id_asistencia FROM asistencias WHERE id_usuario = :u AND fecha = CURDATE()");
            $stmtCheck->execute([':u' => $idEstudiante]);
            
            if ($stmtCheck->fetch()) {
                $mensaje = '⚠️ El estudiante <b>' . htmlspecialchars($estudiante['nombre_completo']) . '</b> ya registró su asistencia el día de hoy.';
                $tipoAlerta = 'error';
            } else {
                // Registrar asistencia
                $stmtInsert = $pdo->prepare("INSERT INTO asistencias (id_usuario, fecha, hora) VALUES (:u, CURDATE(), CURTIME())");
                $stmtInsert->execute([':u' => $idEstudiante]);

                // Opcional: Otorgar puntos automáticos por almorzar/asistir
                $stmtPuntos = $pdo->prepare("INSERT INTO puntos_historial (id_usuario, puntos, motivo, otorgado_por) VALUES (:u, 10, 'Asistencia al restaurante escolar', :o)");
                $stmtPuntos->execute([':u' => $idEstudiante, ':o' => $_SESSION['id_usuario']]);

                $stmtUpd = $pdo->prepare("UPDATE usuarios SET puntos_totales = puntos_totales + 10 WHERE id_usuario = :u");
                $stmtUpd->execute([':u' => $idEstudiante]);

                $mensaje = '✅ Asistencia registrada con éxito para: <b>' . htmlspecialchars($estudiante['nombre_completo']) . '</b> (+10 puntos bonificados).';
                $tipoAlerta = 'exito';
            }
        } else {
            $mensaje = '❌ Código QR no válido o estudiante inactivo.';
            $tipoAlerta = 'error';
        }
    }
}
?>

<h2 class="titulo-seccion">Resultado del Escaneo</h2>

<div class="card">
    <div class="alerta <?= $tipoAlerta ?>">
        <?= $mensaje ?>
    </div>
    <div style="margin-top:20px;">
        <a href="/restaurante_gamificado/escaner/index.php" class="btn-dorado" style="display:inline-block; text-align:center; text-decoration:none; max-width:250px;">Scanear Siguiente QR</a>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>