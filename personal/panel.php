<?php
session_start();
require_once __DIR__ . '/../includes/auth.php';
requerir_rol(['Administrador']);
require_once __DIR__ . '/../config/conexion.php';

$mensaje = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_usuario'])) {
    $id_usuario = intval($_POST['id_usuario'] ?? 0);
    $nombre_completo = trim($_POST['nombre_completo'] ?? '');
    $documento = trim($_POST['documento'] ?? '');
    $id_grado = !empty($_POST['id_grado']) ? intval($_POST['id_grado']) : null;
    $puntos_totales = intval($_POST['puntos_totales'] ?? 0);
    $password_texto = trim($_POST['password_texto'] ?? '');

    if ($id_usuario > 0 && $nombre_completo !== '') {
        $stmt = $pdo->prepare("UPDATE usuarios SET nombre_completo = ?, documento = ?, id_grado = ?, puntos_totales = ?, password_texto = ? WHERE id_usuario = ?");
        $stmt->execute([$nombre_completo, $documento, $id_grado, $puntos_totales, $password_texto, $id_usuario]);
        $mensaje = "¡Estudiante actualizado con éxito!";
    }
}

$usuarios = $pdo->query("SELECT u.*, r.nombre_rol, g.nombre_grado FROM usuarios u JOIN roles r ON u.id_rol = r.id_rol LEFT JOIN grados g ON u.id_grado = g.id_grado ORDER BY u.id_usuario DESC")->fetchAll();
$grados = $pdo->query("SELECT * FROM grados")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Usuarios | Administrador</title>
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
        .content { padding: 40px; }
        .card { background-color: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 25px; margin-bottom: 30px; }
        h2, h3 { color: var(--accent-gold); margin-bottom: 15px; }
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 20px; }
        label { display: block; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 8px; }
        input, select { width: 100%; padding: 12px; background-color: #12141c; border: 1px solid var(--border-color); border-radius: 8px; color: white; }
        .btn-dorado { background-color: var(--accent-gold); color: #12141c; font-weight: 700; padding: 12px 24px; border: none; border-radius: 8px; cursor: pointer; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; text-align: left; }
        th, td { padding: 14px; border-bottom: 1px solid var(--border-color); font-size: 0.9rem; vertical-align: middle; }
        th { color: var(--accent-gold); background-color: rgba(245, 158, 11, 0.05); }
        .alerta-exito { background-color: rgba(16, 185, 129, 0.1); border: 1px solid #10b981; color: #34d399; padding: 12px; border-radius: 8px; margin-bottom: 20px; }
        .btn-editar { background: rgba(59, 130, 246, 0.2); color: #60a5fa; border: 1px solid #3b82f6; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-weight: bold; cursor: pointer; font-size: 0.85rem; }
    </style>
</head>
<body>
    <aside>
        <div class="sidebar-brand"><i class="fa-solid fa-utensils"></i> Restaurante Pro</div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php"><i class="fa-solid fa-chart-pie"></i> Panel General</a></li>
            <li><a href="usuarios.php" class="active"><i class="fa-solid fa-users"></i> Gestión de Usuarios</a></li>
            <li><a href="grados.php"><i class="fa-solid fa-graduation-cap"></i> Grados</a></li>
            <li><a href="menu.php"><i class="fa-solid fa-utensils"></i> Menú</a></li>
            <li><a href="permisos.php"><i class="fa-solid fa-shield-halved"></i> Permisos y Beneficios</a></li>
            <li><a href="qr_generator.php"><i class="fa-solid fa-qrcode"></i> Generador QR</a></li>
            <li><a href="ranking.php"><i class="fa-solid fa-trophy"></i> Ranking Global</a></li>
            <li><a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i> Cerrar Sesión</a></li>
        </ul>
    </aside>

    <main>
        <header>
            <div style="font-weight: 600; color: var(--text-muted);">Gestión de Usuarios y Credenciales</div>
            <span><i class="fa-solid fa-user-shield"></i> <?= htmlspecialchars($_SESSION['nombre_completo'] ?? 'Admin') ?></span>
        </header>

        <div class="content">
            <h2>Lista de Usuarios con Contraseñas Visibles</h2>

            <?php if ($mensaje): ?><div class="alerta-exito"><?= $mensaje ?></div><?php endif; ?>

            <div class="card" id="formEdicionCard" style="display:none; border-color: var(--accent-gold);">
                <h3><i class="fa-solid fa-user-pen"></i> Editar Estudiante</h3>
                <form method="POST">
                    <input type="hidden" name="id_usuario" id="edit_id">
                    <div class="form-grid">
                        <div>
                            <label>Nombre Completo</label>
                            <input type="text" name="nombre_completo" id="edit_nombre" required>
                        </div>
                        <div>
                            <label>Documento</label>
                            <input type="text" name="documento" id="edit_documento">
                        </div>
                        <div>
                            <label>Contraseña Visible</label>
                            <input type="text" name="password_texto" id="edit_pass" required>
                        </div>
                        <div>
                            <label>Grado Escolar</label>
                            <select name="id_grado" id="edit_grado">
                                <option value="">-- Ninguno --</option>
                                <?php foreach ($grados as $g): ?>
                                    <option value="<?= $g['id_grado'] ?>"><?= $g['nombre_grado'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label>Puntos Totales</label>
                            <input type="number" name="puntos_totales" id="edit_puntos" required>
                        </div>
                    </div>
                    <button type="submit" name="editar_usuario" class="btn-dorado">Guardar Cambios</button>
                    <button type="button" onclick="document.getElementById('formEdicionCard').style.display='none'" style="background:transparent; border:1px solid var(--border-color); color:white; padding:12px 20px; border-radius:8px; cursor:pointer; margin-left:10px;">Cancelar</button>
                </form>
            </div>

            <div class="card">
                <table>
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Usuario (Login)</th>
                            <th>Contraseña</th>
                            <th>Documento</th>
                            <th>Rol</th>
                            <th>Grado</th>
                            <th>Puntos</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $u): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($u['nombre_completo']) ?></strong></td>
                            <td><?= htmlspecialchars($u['username']) ?></td>
                            <td><span style="background: rgba(245, 158, 11, 0.1); color: var(--accent-gold); padding: 4px 8px; border-radius: 4px; font-family: monospace;"><?= htmlspecialchars($u['password_texto'] ?? 'N/A') ?></span></td>
                            <td><?= htmlspecialchars($u['documento'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($u['nombre_rol']) ?></td>
                            <td><?= htmlspecialchars($u['nombre_grado'] ?? 'N/A') ?></td>
                            <td><strong style="color: var(--accent-gold);"><?= $u['puntos_totales'] ?> pts</strong></td>
                            <td>
                                <button type="button" class="btn-editar" onclick="cargarEdicion(<?= $u['id_usuario'] ?>, '<?= htmlspecialchars($u['nombre_completo'], ENT_QUOTES) ?>', '<?= htmlspecialchars($u['documento'], ENT_QUOTES) ?>', '<?= htmlspecialchars($u['password_texto'], ENT_QUOTES) ?>', '<?= $u['id_grado'] ?>', <?= $u['puntos_totales'] ?>)">
                                    Editar
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        function cargarEdicion(id, nombre, documento, pass, grado, puntos) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_nombre').value = nombre;
            document.getElementById('edit_documento').value = documento;
            document.getElementById('edit_pass').value = pass;
            document.getElementById('edit_grado').value = grado;
            document.getElementById('edit_puntos').value = puntos;
            document.getElementById('formEdicionCard').style.display = 'block';
            window.scrollTo({top: 0, behavior: 'smooth'});
        }
    </script>
</body>
</html>