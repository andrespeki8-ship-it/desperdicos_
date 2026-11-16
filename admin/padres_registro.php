<?php
session_start();
require_once __DIR__ . '/../includes/auth.php';
requerir_rol(['Administrador']);
require_once __DIR__ . '/../config/conexion.php';

$mensaje = '';
$error = '';

// Registrar nuevo padre
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_padre'])) {
    $nombre = trim($_POST['nombre_completo'] ?? '');
    $documento = trim($_POST['documento'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $id_estudiante = intval($_POST['id_estudiante'] ?? 0);

    if ($nombre !== '' && $documento !== '' && $password !== '') {
        try {
            // Asumimos que el rol de Padre tiene id_rol = 4 (o ajusta según tu base de datos)
            $stmt = $pdo->prepare("INSERT INTO usuarios (nombre_completo, documento, password, id_rol, id_grado) VALUES (?, ?, ?, 4, 0)");
            $stmt->execute([$nombre, $documento, $password]);
            $id_nuevo_padre = $pdo->lastInsertId();

            // Si asociaste un estudiante, puedes crear una relación si tienes tabla de vínculos (opcional o informativo)
            $mensaje = "¡Padre registrado exitosamente en el sistema!";
        } catch (Exception $e) {
            $error = "Error: El documento ya está registrado.";
        }
    } else {
        $error = "Por favor completa todos los campos obligatorios.";
    }
}

// Obtener lista de padres registrados (filtrando por rol de padre, ej: id_rol = 4 o nombre rol 'Padre')
$padres = $pdo->query("SELECT u.*, r.nombre_rol FROM usuarios u JOIN roles r ON u.id_rol = r.id_rol WHERE r.nombre_rol LIKE '%Padre%' ORDER BY u.nombre_completo ASC")->fetchAll();
$estudiantes = $pdo->query("SELECT id_usuario, nombre_completo FROM usuarios WHERE id_rol = 3 ORDER BY nombre_completo ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Monitoreo y Registro de Padres | Admin Pro</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Inter', sans-serif; }
        body { background-color: #0b0f19; color: #f3f4f6; display: flex; min-height: 100vh; }
        main { margin-left: 270px; flex-grow: 1; display: flex; flex-direction: column; }
        header { background-color: #111827; border-bottom: 1px solid #1f2937; padding: 20px 40px; display: flex; justify-content: space-between; align-items: center; }
        .content { padding: 40px; }
        .card { background-color: #111827; border: 1px solid #1f2937; border-radius: 12px; padding: 25px; margin-bottom: 30px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.3); }
        h2, h3 { color: #f97316; margin-bottom: 15px; }
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 20px; }
        label { display: block; font-size: 0.85rem; color: #9ca3af; margin-bottom: 8px; font-weight: 500; }
        input, select { width: 100%; padding: 12px; background-color: #0b0f19; border: 1px solid #1f2937; border-radius: 8px; color: white; outline: none; }
        input:focus, select:focus { border-color: #f97316; }
        .btn-dorado { background-color: #f97316; color: #0b0f19; font-weight: 700; padding: 12px 24px; border: none; border-radius: 8px; cursor: pointer; transition: 0.2s; }
        .btn-dorado:hover { background-color: #ea580c; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; text-align: left; }
        th, td { padding: 14px; border-bottom: 1px solid #1f2937; font-size: 0.9rem; }
        th { color: #f97316; background-color: rgba(249, 115, 22, 0.05); }
        .alerta-exito { background-color: rgba(16, 185, 129, 0.1); border: 1px solid #10b981; color: #34d399; padding: 12px; border-radius: 8px; margin-bottom: 20px; }
        .alerta-error { background-color: rgba(239, 68, 68, 0.1); border: 1px solid #ef4444; color: #f87171; padding: 12px; border-radius: 8px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <main>
        <header>
            <div style="font-weight: 600; color: #9ca3af;">Módulo de Control de Padres</div>
            <span><i class="fa-solid fa-user-shield"></i> <?= htmlspecialchars($_SESSION['nombre_completo'] ?? 'Admin') ?></span>
        </header>

        <div class="content">
            <h2>Gestión y Registro de Padres de Familia</h2>
            
            <?php if ($mensaje): ?><div class="alerta-exito"><?= $mensaje ?></div><?php endif; ?>
            <?php if ($error): ?><div class="alerta-error"><?= $error ?></div><?php endif; ?>

            <div class="card">
                <h3><i class="fa-solid fa-user-plus"></i> Registrar Nuevo Padre en el Sistema</h3>
                <form method="POST">
                    <div class="form-grid">
                        <div>
                            <label>Nombre Completo del Padre</label>
                            <input type="text" name="nombre_completo" required placeholder="Ej. Carlos Rodríguez">
                        </div>
                        <div>
                            <label>Documento de Identidad (Usuario)</label>
                            <input type="text" name="documento" required placeholder="Ej. 10203040">
                        </div>
                        <div>
                            <label>Contraseña de Acceso</label>
                            <input type="password" name="password" required placeholder="Contraseña temporal">
                        </div>
                    </div>
                    <button type="submit" name="crear_padre" class="btn-dorado">Guardar y Registrar Padre</button>
                </form>
            </div>

            <div class="card">
                <h3><i class="fa-solid fa-users-viewfinder"></i> Listado de Padres Registrados y Actividad</h3>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre Completo</th>
                            <th>Documento</th>
                            <th>Rol en Sistema</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($padres)): ?>
                            <tr><td colspan="4" style="text-align: center; color: #9ca3af;">No hay padres registrados todavía.</td></tr>
                        <?php else: ?>
                            <?php foreach ($padres as $p): ?>
                            <tr>
                                <td>#<?= $p['id_usuario'] ?></td>
                                <td><strong><?= htmlspecialchars($p['nombre_completo']) ?></strong></td>
                                <td><?= htmlspecialchars($p['documento']) ?></td>
                                <td><span style="color: #38bdf8; font-weight: 600;"><?= htmlspecialchars($p['nombre_rol']) ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>