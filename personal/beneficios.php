<?php
require_once __DIR__ . '/../includes/auth.php';
requerirRol(['Administrador', 'Personal']);
require_once __DIR__ . '/../includes/header.php';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idEstudiante = (int)$_POST['id_estudiante'];
    $costoPuntos  = (int)$_POST['costo_puntos'];
    $beneficio    = trim($_POST['nombre_beneficio']);

    // Verificar si el estudiante tiene suficientes puntos
    $stmtUser = $pdo->prepare("SELECT puntos_totales, nombre_completo FROM usuarios WHERE id_usuario = :id");
    $stmtUser->execute([':id' => $idEstudiante]);
    $est = $stmtUser->fetch();

    if ($est && $est['puntos_totales'] >= $costoPuntos) {
        try {
            $pdo->beginTransaction();

            // Descontar puntos
            $upd = $pdo->prepare("UPDATE usuarios SET puntos_totales = puntos_totales - :p WHERE id_usuario = :id");
            $upd->execute([':p' => $costoPuntos, ':id' => $idEstudiante]);

            // Registrar en historial como canje (puntos negativos)
            $hist = $pdo->prepare("INSERT INTO puntos_historial (id_usuario, puntos, motivo, otorgado_por) VALUES (:u, :p, :m, :o)");
            $hist->execute([
                ':u' => $idEstudiante,
                ':p' => -$costoPuntos,
                ':m' => 'Canje de beneficio: ' . $beneficio,
                ':o' => $_SESSION['id_usuario']
            ]);

            $pdo->commit();
            $msg = '<div class="alerta exito">🎁 Beneficio canjeado con éxito para <b>'.htmlspecialchars($est['nombre_completo']).'</b>. Se descontaron <b>'.$costoPuntos.' pts</b>.</div>';
        } catch (Exception $e) {
            $pdo->rollBack();
            $msg = '<div class="alerta error">Error al procesar el canje: '.htmlspecialchars($e->getMessage()).'</div>';
        }
    } else {
        $msg = '<div class="alerta error">⚠️ El estudiante no cuenta con suficientes puntos para este beneficio.</div>';
    }
}

$estudiantes = $pdo->query("SELECT id_usuario, nombre_completo, puntos_totales FROM usuarios WHERE id_rol = 3 AND activo = 1 ORDER BY nombre_completo")->fetchAll();
?>

<h2 class="titulo-seccion">Canje de Beneficios y Premios</h2>
<?= $msg ?>

<div class="card">
    <h3>Registrar Canje de Puntos por Premios</h3>
    <form method="POST">
        <label>Estudiante</label>
        <select name="id_estudiante" required>
            <option value="">— Seleccione estudiante —</option>
            <?php foreach ($estudiantes as $e): ?>
                <option value="<?= $e['id_usuario'] ?>">
                    <?= htmlspecialchars($e['nombre_completo']) ?> (Disponible: <?= $e['puntos_totales'] ?> pts)
                </option>
            <?php endforeach; ?>
        </select>

        <label>Descripción del Beneficio / Premio</label>
        <input type="text" name="nombre_beneficio" required placeholder="Ej: Postre gratis, pase preferencial, combo especial...">

        <label>Costo en Puntos</label>
        <input type="number" name="costo_puntos" required min="1" placeholder="Ej: 50">

        <button class="btn-dorado" type="submit">Procesar Canje</button>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>