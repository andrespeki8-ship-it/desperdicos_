<?php
require_once __DIR__ . '/../includes/auth.php';
requerirRol(['Administrador', 'Personal']);
require_once __DIR__ . '/../includes/header.php';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idEstudiante = (int)$_POST['id_estudiante'];
    $puntos       = (int)$_POST['puntos'];
    $motivo       = trim($_POST['motivo']);

    if ($idEstudiante > 0 && $puntos !== 0) {
        try {
            $pdo->beginTransaction();
            
            // Insertar en historial
            $stmt = $pdo->prepare("INSERT INTO puntos_historial (id_usuario, puntos, motivo, otorgado_por) VALUES (:u, :p, :m, :o)");
            $stmt->execute([
                ':u' => $idEstudiante,
                ':p' => $puntos,
                ':m' => $motivo,
                ':o' => $_SESSION['id_usuario']
            ]);

            // Actualizar puntos totales en usuarios
            $stmtUpdate = $pdo->prepare("UPDATE usuarios SET puntos_totales = puntos_totales + :p WHERE id_usuario = :u");
            $stmtUpdate->execute([':p' => $puntos, ':u' => $idEstudiante]);

            $pdo->commit();
            $msg = '<div class="alerta exito">✅ Puntos actualizados correctamente al estudiante.</div>';
        } catch (Exception $e) {
            $pdo->rollBack();
            $msg = '<div class="alerta error">Error al registrar puntos: '.htmlspecialchars($e->getMessage()).'</div>';
        }
    }
}

$estudiantes = $pdo->query("SELECT id_usuario, nombre_completo, puntos_totales FROM usuarios WHERE id_rol = 3 AND activo = 1 ORDER BY nombre_completo")->fetchAll();
?>

<h2 class="titulo-seccion">Asignación de Puntos Gamificados</h2>
<?= $msg ?>

<div class="card">
    <h3>Otorgar o Descontar Puntos</h3>
    <form method="POST">
        <label>Estudiante</label>
        <select name="id_estudiante" required>
            <option value="">— Seleccione estudiante —</option>
            <?php foreach ($estudiantes as $est): ?>
                <option value="<?= $est['id_usuario'] ?>">
                    <?= htmlspecialchars($est['nombre_completo']) ?> (Actual: <?= $est['puntos_totales'] ?> pts)
                </option>
            <?php endforeach; ?>
        </select>

        <label>Cantidad de Puntos (Use negativo para descontar, ej: -10)</label>
        <input type="number" name="puntos" required placeholder="Ej: 50 o -10">

        <label>Motivo</label>
        <textarea name="motivo" rows="3" required placeholder="Ej: Participación destacada, buen comportamiento, reciclaje..."></textarea>

        <button class="btn-dorado" type="submit">Registrar Puntos</button>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>