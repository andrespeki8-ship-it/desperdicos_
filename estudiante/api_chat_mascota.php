<?php
if (ob_get_length()) ob_clean();
header('Content-Type: application/json; charset=utf-8');

session_start();
require_once __DIR__ . '/../config/conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['exito' => false, 'mensaje' => 'Sesión no iniciada']);
    exit;
}

$id_usuario = $_SESSION['id_usuario'];

// Enviar mensaje nuevo
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mensaje = trim($_POST['mensaje'] ?? '');
    
    // Filtro básico de palabras ofensivas
    $groserias = ['idiota', 'estupido', 'tonto', 'mierda', 'basura', 'puta', 'gonorrea', 'malparido'];
    foreach ($groserias as $palabra) {
        if (stripos($mensaje, $palabra) !== false) {
            echo json_encode(['exito' => false, 'mensaje' => '⚠️ Usa un lenguaje respetuoso en la Plaza de Mascotas.']);
            exit;
        }
    }

    if (mb_strlen($mensaje) > 0) {
        $stmt = $pdo->prepare("INSERT INTO chat_mascotas (usuario_id, mensaje) VALUES (?, ?)");
        $stmt->execute([$id_usuario, htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8')]);
        echo json_encode(['exito' => true]);
        exit;
    }
}

// Listar últimos 25 mensajes
$iconos_nivel = [
    1 => '🐣', 2 => '🐥', 3 => '🦊', 4 => '🐺', 5 => '🦁',
    6 => '🐯', 7 => '🦅', 8 => '🐲', 9 => '🐉', 10 => '👑⚡'
];

$stmt_q = $pdo->query("
    SELECT c.usuario_id, c.mensaje, u.nombre_completo, m.nombre_mascota, m.nivel_mascota
    FROM chat_mascotas c
    JOIN usuarios u ON c.usuario_id = u.id_usuario
    LEFT JOIN mascotas m ON c.usuario_id = m.usuario_id
    ORDER BY c.id DESC LIMIT 25
");
$filas = array_reverse($stmt_q->fetchAll());

$resultado = [];
foreach ($filas as $f) {
    $nivel = $f['nivel_mascota'] ?? 1;
    $resultado[] = [
        'usuario_id' => $f['usuario_id'],
        'mensaje' => $f['mensaje'],
        'dueno' => explode(' ', $f['nombre_completo'])[0],
        'nombre_mascota' => $f['nombre_mascota'] ?? 'Mascota',
        'icono' => $iconos_nivel[$nivel] ?? '🐣'
    ];
}

echo json_encode($resultado);
exit;