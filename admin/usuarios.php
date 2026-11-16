<?php
session_start();
require_once __DIR__ . '/../includes/auth.php';
requerir_rol(['Administrador']);
require_once __DIR__ . '/../config/conexion.php';

$mensaje = '';
$error = '';

// 1. REGISTRO INDIVIDUAL DE ESTUDIANTE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_estudiante'])) {
    $nombre_completo = trim($_POST['nombre_completo'] ?? '');
    $documento = trim($_POST['documento'] ?? '');
    $id_grado = intval($_POST['id_grado'] ?? 0);
    $username = trim($_POST['username'] ?? '');
    
    // La contraseña por defecto es el mismo número de documento
    $password_texto = $documento;
    $password_hash = password_hash($password_texto, PASSWORD_DEFAULT);

    if ($nombre_completo !== '' && $documento !== '') {
        try {
            $stmt = $pdo->prepare("INSERT INTO usuarios (nombre_completo, username, password_hash, password_texto, documento, id_rol, id_grado, puntos_totales, activo) VALUES (?, ?, ?, ?, ?, 3, ?, 0, 1)");
            $stmt->execute([$nombre_completo, $username ?: $documento, $password_hash, $password_texto, $documento, $id_grado > 0 ? $id_grado : null]);
            $mensaje = "¡Estudiante registrado con éxito!";
        } catch (Exception $e) {
            $error = "Error: El usuario o el documento ya existen en la base de datos.";
        }
    } else {
        $error = "Por favor completa los campos obligatorios.";
    }
}

// 2. EDITAR ESTUDIANTE EXISTENTE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_estudiante'])) {
    $id_usuario = intval($_POST['id_usuario']);
    $nombre_completo = trim($_POST['nombre_completo'] ?? '');
    $documento = trim($_POST['documento'] ?? '');
    $id_grado = intval($_POST['id_grado'] ?? 0);
    $username = trim($_POST['username'] ?? '');

    if ($id_usuario > 0 && $nombre_completo !== '' && $documento !== '') {
        try {
            // Al actualizar el documento, actualizamos también la contraseña para que siga siendo igual al documento
            $stmt = $pdo->prepare("UPDATE usuarios SET nombre_completo = ?, documento = ?, username = ?, password_texto = ?, id_grado = ? WHERE id_usuario = ?");
            $stmt->execute([$nombre_completo, $documento, $username ?: $documento, $documento, $id_grado > 0 ? $id_grado : null, $id_usuario]);
            $mensaje = "¡Estudiante actualizado correctamente!";
        } catch (Exception $e) {
            $error = "Error al actualizar: El documento o usuario ya están en uso.";
        }
    } else {
        $error = "Completa los campos requeridos para editar.";
    }
}

// 3. CARGA MASIVA MEDIANTE ARCHIVO CSV
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cargar_csv'])) {
    if (isset($_FILES['archivo_csv']) && $_FILES['archivo_csv']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['archivo_csv']['tmp_name'];
        $handle = fopen($fileTmpPath, "r");
        
        $insertados = 0;
        $linea = 0;
        
        $gradosDB = $pdo->query("SELECT id_grado, LOWER(nombre_grado) as nombre FROM grados")->fetchAll(PDO::FETCH_KEY_PAIR);

        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $linea++;
            if ($linea === 1 && (strtolower($data[0]) === 'nombre' || strtolower($data[0]) === 'nombre_completo')) {
                continue; 
            }
            
            $nombre = trim($data[0] ?? '');
            $doc = trim($data[1] ?? '');
            $nombre_grado_input = strtolower(trim($data[2] ?? ''));

            if (!empty($nombre) && !empty($doc)) {
                $id_grado_encontrado = null;
                foreach ($gradosDB as $idg => $nomg) {
                    if (strpos($nomg, $nombre_grado_input) !== false || $nomg == $nombre_grado_input) {
                        $id_grado_encontrado = $idg;
                        break;
                    }
                }

                $pass_default = $doc; // La contraseña es el documento
                $pass_hash = password_hash($pass_default, PASSWORD_DEFAULT);

                try {
                    $stmt = $pdo->prepare("INSERT INTO usuarios (nombre_completo, username, password_hash, password_texto, documento, id_rol, id_grado, puntos_totales, activo) VALUES (?, ?, ?, ?, ?, 3, ?, 0, 1)");
                    $stmt->execute([$nombre, $doc, $pass_hash, $pass_default, $doc, $id_grado_encontrado]);
                    $insertados++;
                } catch (Exception $e) {
                    // Ignora duplicados
                }
            }
        }
        fclose($handle);
        $mensaje = "¡Carga masiva completada! Se registraron <strong>$insertados</strong> estudiantes.";
    } else {
        $error = "Por favor selecciona un archivo CSV válido.";
    }
}

// 4. ELIMINACIÓN MASIVA O INDIVIDUAL
if (isset($_POST['eliminar_seleccionados']) && isset($_POST['estudiantes_ids'])) {
    $ids = $_POST['estudiantes_ids'];
    if (!empty($ids)) {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id_usuario IN ($placeholders) AND id_rol != 1");
        $stmt->execute($ids);
        $mensaje = "¡Los estudiantes seleccionados fueron eliminados!";
    }
}

if (isset($_GET['eliminar'])) {
    $id_del = intval($_GET['eliminar']);
    if ($id_del > 1) {
        $pdo->prepare("DELETE FROM usuarios WHERE id_usuario = ?")->execute([$id_del]);
        header("Location: usuarios.php");
        exit;
    }
}

$grados = $pdo->query("SELECT * FROM grados ORDER BY nombre_grado ASC")->fetchAll();
$usuarios = $pdo->query("SELECT u.*, g.nombre_grado, r.nombre_rol FROM usuarios u LEFT JOIN grados g ON u.id_grado = g.id_grado JOIN roles r ON u.id_rol = r.id_rol ORDER BY u.nombre_completo ASC")->fetchAll();

$editando = null;
if (isset($_GET['editar'])) {
    $id_edit = intval($_GET['editar']);
    $stmt_edit = $pdo->prepare("SELECT * FROM usuarios WHERE id_usuario = ?");
    $stmt_edit->execute([$id_edit]);
    $editando = $stmt_edit->fetch();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Usuarios y Contraseñas | Admin</title>
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
        .grid-forms { display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-bottom: 30px; }
        .card { background-color: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 25px; }
        h2, h3 { color: var(--accent-gold); margin-bottom: 15px; }
        label { display: block; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 6px; }
        input, select { width: 100%; padding: 11px; background-color: #12141c; border: 1px solid var(--border-color); border-radius: 8px; color: white; margin-bottom: 12px; outline: none; }
        .btn-dorado { background-color: var(--accent-gold); color: #12141c; font-weight: 700; padding: 12px; border: none; border-radius: 8px; cursor: pointer; width: 100%; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; text-align: left; }
        th, td { padding: 12px; border-bottom: 1px solid var(--border-color); font-size: 0.9rem; }
        th { color: var(--accent-gold); background-color: rgba(245, 158, 11, 0.05); }
        .alerta-exito { background-color: rgba(16, 185, 129, 0.1); border: 1px solid #10b981; color: #34d399; padding: 12px; border-radius: 8px; margin-bottom: 20px; }
        .alerta-error { background-color: rgba(239, 68, 68, 0.1); border: 1px solid #ef4444; color: #f87171; padding: 12px; border-radius: 8px; margin-bottom: 20px; }
        .btn-editar { background: rgba(56, 189, 248, 0.2); color: #38bdf8; border: 1px solid #38bdf8; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-weight: bold; font-size: 0.85rem; margin-right: 5px; display: inline-block; }
        .btn-eliminar { background: rgba(239, 68, 68, 0.2); color: #f87171; border: 1px solid #ef4444; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-weight: bold; font-size: 0.85rem; display: inline-block; }
        .btn-eliminar-masivo { background-color: #ef4444; color: white; border: none; padding: 10px 18px; border-radius: 8px; font-weight: bold; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; font-size: 0.9rem; margin-bottom: 15px; }
    </style>
</head>
<body>
    <aside>
        <div class="sidebar-brand"><i class="fa-solid fa-utensils"></i> Admin Pro</div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php"><i class="fa-solid fa-chart-pie"></i> Panel General</a></li>
            <li><a href="asistencia.php"><i class="fa-solid fa-barcode"></i> Control Asistencia</a></li>
            <li><a href="usuarios.php" class="active"><i class="fa-solid fa-users"></i> Usuarios</a></li>
            <li><a href="qr_generator.php"><i class="fa-solid fa-qrcode"></i> Generador QR</a></li>
            <li><a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i> Salir</a></li>
        </ul>
    </aside>

    <main>
        <header>
            <div style="font-weight: 600; color: var(--text-muted);">Módulo de Gestión de Usuarios</div>
            <span><i class="fa-solid fa-user-shield"></i> Admin</span>
        </header>

        <div class="content">
            <h2>Gestión, Matrícula y Contraseñas</h2>
            <?php if ($mensaje): ?><div class="alerta-exito"><?= $mensaje ?></div><?php endif; ?>
            <?php if ($error): ?><div class="alerta-error"><?= $error ?></div><?php endif; ?>

            <div class="grid-forms">
                <!-- FORMULARIO DE EDICIÓN O REGISTRO -->
                <?php if ($editando): ?>
                <div class="card" style="border: 2px solid #38bdf8;">
                    <h3><i class="fa-solid fa-user-pen"></i> Editar Estudiante</h3>
                    <form method="POST">
                        <input type="hidden" name="id_usuario" value="<?= $editando['id_usuario'] ?>">
                        <label>Nombre Completo</label>
                        <input type="text" name="nombre_completo" value="<?= htmlspecialchars($editando['nombre_completo']) ?>" required>
                        <label>Documento de Identidad (Será también su contraseña)</label>
                        <input type="text" name="documento" value="<?= htmlspecialchars($editando['documento']) ?>" required>
                        <label>Usuario (Login)</label>
                        <input type="text" name="username" value="<?= htmlspecialchars($editando['username']) ?>">
                        <label>Grado Escolar</label>
                        <select name="id_grado" required>
                            <option value="">-- Seleccionar Grado --</option>
                            <?php foreach ($grados as $g): ?>
                                <option value="<?= $g['id_grado'] ?>" <?= $editando['id_grado'] == $g['id_grado'] ? 'selected' : '' ?>><?= htmlspecialchars($g['nombre_grado']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" name="editar_estudiante" class="btn-dorado" style="background-color: #38bdf8; color: #12141c;">Actualizar Estudiante</button>
                        <a href="usuarios.php" style="display: block; text-align: center; margin-top: 10px; color: var(--text-muted); font-size: 0.85rem; text-decoration: none;">Cancelar Edición</a>
                    </form>
                </div>
                <?php else: ?>
                <div class="card">
                    <h3><i class="fa-solid fa-user-plus"></i> Registrar Estudiante</h3>
                    <form method="POST">
                        <label>Nombre Completo</label>
                        <input type="text" name="nombre_completo" required placeholder="Ej. Juan Pérez">
                        <label>Documento de Identidad (Será su contraseña)</label>
                        <input type="text" name="documento" required placeholder="Ej. 1107853489">
                        <label>Usuario (Login)</label>
                        <input type="text" name="username" placeholder="Opcional">
                        <label>Grado Escolar</label>
                        <select name="id_grado" required>
                            <option value="">-- Seleccionar Grado --</option>
                            <?php foreach ($grados as $g): ?>
                                <option value="<?= $g['id_grado'] ?>"><?= htmlspecialchars($g['nombre_grado']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" name="crear_estudiante" class="btn-dorado">Guardar Estudiante</button>
                    </form>
                </div>
                <?php endif; ?>

                <!-- CARGA MASIVA CSV -->
                <div class="card">
                    <h3><i class="fa-solid fa-file-csv"></i> Carga Masiva (CSV)</h3>
                    <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 15px;">Sube un archivo <code>.csv</code> con el formato: <br><strong>Nombre, Documento, Grado</strong></p>
                    <form method="POST" enctype="multipart/form-data">
                        <label>Archivo CSV de Estudiantes</label>
                        <input type="file" name="archivo_csv" accept=".csv" required style="padding: 8px;">
                        <button type="submit" name="cargar_csv" class="btn-dorado" style="background-color: #10b981; color: white; margin-top: 10px;">Subir y Procesar Masivo</button>
                    </form>
                </div>
            </div>

            <!-- TABLA GENERAL -->
            <div class="card">
                <h3><i class="fa-solid fa-users"></i> Lista General de Usuarios Registrados</h3>
                
                <form method="POST" id="form-masivo" onsubmit="return confirm('¿Estás seguro de eliminar a los estudiantes seleccionados?');">
                    <button type="submit" name="eliminar_seleccionados" class="btn-eliminar-masivo">
                        <i class="fa-solid fa-trash-can"></i> Eliminar Seleccionados
                    </button>

                    <table>
                        <thead>
                            <tr>
                                <th style="width: 40px;"><input type="checkbox" id="seleccionar-todos"></th>
                                <th>Nombre</th>
                                <th>Usuario</th>
                                <th>Documento</th>
                                <th>Contraseña</th>
                                <th>Grado</th>
                                <th>Rol</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $u): ?>
                            <tr>
                                <td>
                                    <?php if ($u['id_rol'] != 1): ?>
                                        <input type="checkbox" name="estudiantes_ids[]" value="<?= $u['id_usuario'] ?>" class="check-estudiante">
                                    <?php else: ?>
                                        <input type="checkbox" disabled>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?= htmlspecialchars($u['nombre_completo']) ?></strong></td>
                                <td><?= htmlspecialchars($u['username']) ?></td>
                                <td><strong style="color: var(--accent-gold);"><?= htmlspecialchars($u['documento'] ?? 'N/A') ?></strong></td>
                                <td><code style="background: #12141c; padding: 4px 8px; border-radius: 4px; color: #34d399;"><?= htmlspecialchars($u['password_texto'] ?? $u['documento'] ?? 'N/A') ?></code></td>
                                <td><?= htmlspecialchars($u['nombre_grado'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($u['nombre_rol']) ?></td>
                                <td>
                                    <?php if ($u['id_rol'] != 1): ?>
                                        <a href="usuarios.php?editar=<?= $u['id_usuario'] ?>" class="btn-editar">Editar</a>
                                        <a href="usuarios.php?eliminar=<?= $u['id_usuario'] ?>" class="btn-eliminar" onclick="return confirm('¿Seguro que deseas eliminar este usuario?');">Eliminar</a>
                                    <?php else: ?>
                                        <span style="color: var(--text-muted); font-size: 0.8rem;">Protegido</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </form>
            </div>
        </div>
    </main>

    <script>
        const selectAllCheckbox = document.getElementById('seleccionar-todos');
        const checkboxes = document.querySelectorAll('.check-estudiante');

        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                checkboxes.forEach(cb => {
                    cb.checked = selectAllCheckbox.checked;
                });
            });
        }
    </script>
</body>
</html>