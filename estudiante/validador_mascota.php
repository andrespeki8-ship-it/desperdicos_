<?php
if (ob_get_length()) ob_clean();
header('Content-Type: application/json; charset=utf-8');

session_start();
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/conexion.php';

if (!isset($_SESSION['id_usuario']) || $_SESSION['id_rol'] != 3) {
    echo json_encode(['exito' => false, 'mensaje' => 'No autorizado.']);
    exit;
}

$id_usuario = $_SESSION['id_usuario'];
$accion = $_POST['accion'] ?? '';
$hoy = date('Y-m-d');

// Verificar límites diarios para evitar trampa / abuso
$stmt_l = $pdo->prepare("SELECT * FROM limites_acciones WHERE usuario_id = ? AND fecha = ? AND tipo_accion = ?");
$stmt_l->execute([$id_usuario, $hoy, $accion]);
$limite = $stmt_l->fetch();

$max_usos = 3; // Límite estándar para comida
if (strpos($accion, 'entrenar_') !== false) $max_usos = 2;
if (strpos($accion, 'desafio_') !== false) $max_usos = 1;
if ($accion === 'minijuego_ganado') $max_usos = 5; // El minijuego se puede ganar hasta 5 veces al día

if ($limite && $limite['usos'] >= $max_usos) {
    echo json_encode(['exito' => false, 'mensaje' => '⚠️ ¡Límite diario alcanzado para esta acción! Prueba otra actividad.']);
    exit;
}

// Registrar o actualizar uso diario
if ($limite) {
    $pdo->prepare("UPDATE limites_acciones SET usos = usos + 1 WHERE id = ?")->execute([$limite['id']]);
} else {
    // Si la tabla limites_acciones no existe, se ignorará o se creará en BD, manejamos con control de errores por seguridad
    try {
        $pdo->prepare("INSERT INTO limites_acciones (usuario_id, fecha, tipo_accion, usos) VALUES (?, ?, ?, 1)")
            ->execute([$id_usuario, $hoy, $accion]);
    } catch (Exception $e) { /* Evita bloqueo si la tabla opcional no está creada */ }
}

$stmt = $pdo->prepare("SELECT * FROM mascotas WHERE usuario_id = ?");
$stmt->execute([$id_usuario]);
$mascota = $stmt->fetch();

$exp_ganada = 0;
$feedback = "";

switch ($accion) {
    case 'alimentar_papas':     $exp_ganada = 15; $feedback = "¡Papas Sajo consumidas! (+15 EXP)"; break;
    case 'alimentar_ensalada':  $exp_ganada = 20; $feedback = "¡Ensalada verde procesada! (+20 EXP)"; break;
    case 'alimentar_fruta':     $exp_ganada = 10; $feedback = "¡Fruta fresca añadida! (+10 EXP)"; break;
    case 'entrenar_fuerza':     $exp_ganada = 25; $feedback = "¡Cardio escolar completado! (+25 EXP)"; break;
    case 'entrenar_reciclaje':  $exp_ganada = 25; $feedback = "¡Eco-reciclaje realizado! (+25 EXP)"; break;
    case 'entrenar_enfoque':    $exp_ganada = 20; $feedback = "¡Enfoque mental fortalecido! (+20 EXP)"; break;
    case 'desafio_plato_limpio':$exp_ganada = 40; $feedback = "¡Plato 100% limpio superado! (+40 EXP)"; break;
    case 'desafio_qr':          $exp_ganada = 30; $feedback = "¡Escaneo QR validado! (+30 EXP)"; break;
    case 'desafio_ayuda':       $exp_ganada = 50; $feedback = "¡Ayuda en cafetería recompensada! (+50 EXP)"; break;
    case 'minijuego_ganado':    $exp_ganada = 60; $feedback = "🏆 ¡Minijuego difícil superado! (+60 EXP)"; break;
    default:
        echo json_encode(['exito' => false, 'mensaje' => 'Acción no válida.']);
        exit;
}

$nueva_exp = intval($mascota['experiencia']) + $exp_ganada;
$nivel_actual = intval($mascota['nivel_mascota']);

// Escala de 10 Niveles (cada 100 puntos = 1 nivel, tope máximo 10)
$nuevo_nivel = min(10, floor($nueva_exp / 100) + 1);
$nuevas_vidas = min(3, intval($mascota['vidas']) + 1);

$subio_nivel = ($nuevo_nivel > $nivel_actual);

$stmt_up = $pdo->prepare("UPDATE mascotas SET experiencia = ?, nivel_mascota = ?, vidas = ? WHERE usuario_id = ?");
$stmt_up->execute([$nueva_exp, $nuevo_nivel, $nuevas_vidas, $id_usuario]);

$porcentaje_exp = ($nuevo_nivel >= 10) ? 100 : ($nueva_exp % 100);

echo json_encode([
    'exito' => true,
    'nueva_exp' => $nueva_exp,
    'nuevas_vidas' => $nuevas_vidas,
    'nuevo_nivel' => $nuevo_nivel,
    'porcentaje_exp' => $porcentaje_exp,
    'subio_nivel' => $subio_nivel,
    'mensaje_feedback' => $feedback
]);
exit;