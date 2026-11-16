<?php
session_start();
require_once __DIR__ . '/../includes/auth.php';
requerir_rol(['Estudiante']);
require_once __DIR__ . '/../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['foto_comida'])) {
    $id_usuario = $_SESSION['id_usuario'];
    $archivo = $_FILES['foto_comida'];
    
    $nombre_original = $archivo['name'];
    $nombre_guardado = uniqid() . '_' . $nombre_original;
    $ruta_destino = __DIR__ . '/../uploads/' . $nombre_guardado;

    // Crear carpeta uploads si no existe
    if (!is_dir(__DIR__ . '/../uploads/')) {
        mkdir(__DIR__ . '/../uploads/', 0777, true);
    }

    if (move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
        // Guardar en archivos_subidos
        $stmt = $pdo->prepare("INSERT INTO archivos_subidos (nombre_original, nombre_guardado, ruta, subido_por) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nombre_original, $nombre_guardado, 'uploads/' . $nombre_guardado, $id_usuario]);

        // Otorgar puntos automáticos por registrar la foto del plato
        $puntos_extra = 3;
        $pdo->prepare("UPDATE usuarios SET puntos_totales = puntos_totales + ? WHERE id_usuario = ?")->execute([$puntos_extra, $id_usuario]);
        
        // Registrar en movimientos
        $pdo->prepare("INSERT INTO movimientos_puntos (id_usuario, puntos, motivo) VALUES (?, ?, ?)")->execute([$id_usuario, $puntos_extra, 'Subir foto de evidencia de plato']);

        header("Location: panel.php?exito=foto_subida");
        exit;
    }
}
header("Location: panel.php?error=upload_fail");
exit;