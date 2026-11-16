<?php
session_start();
require_once __DIR__ . '/../includes/auth.php';
requerir_rol(['Estudiante']);
require_once __DIR__ . '/../config/conexion.php';

$id_usuario = $_SESSION['id_usuario'];

// Obtener todas las mascotas activas del colegio junto con el nombre de su dueño
$stmt_all = $pdo->query("
    SELECT m.*, u.nombre_completo, g.nombre_grado
    FROM mascotas m
    JOIN usuarios u ON m.usuario_id = u.id_usuario
    LEFT JOIN grados g ON u.id_grado = g.id_grado
    ORDER BY m.nivel_mascota DESC, m.experiencia DESC
");
$todas_mascotas = $stmt_all->fetchAll();

$iconos_nivel = [
    1 => '🐣', 2 => '🐥', 3 => '🦊', 4 => '🐺', 5 => '🦁',
    6 => '🐯', 7 => '🦅', 8 => '🐲', 9 => '🐉', 10 => '👑⚡'
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Plaza Social de Mascotas | Desperdicios de Comida AE</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --bg: #090a0f; --card: rgba(22, 27, 39, 0.9); --border: rgba(255,255,255,0.1); --accent: #f59e0b; --green: #10b981; }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Outfit', sans-serif; }
        body { background: radial-gradient(circle at 50% 10%, #1e1b4b 0%, #090a0f 85%); color: #f3f4f6; min-height: 100vh; padding: 25px; }
        .container { max-width: 1100px; margin: 0 auto; }
        .top-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; border-bottom: 1px solid var(--border); padding-bottom: 15px; }
        .btn-volver { background: var(--accent); color: #090a0f; padding: 8px 16px; border-radius: 10px; text-decoration: none; font-weight: 700; }
        
        .layout-plaza { display: grid; grid-template-columns: 1.6fr 1fr; gap: 25px; }
        .parque-escenario {
            background: rgba(16, 185, 129, 0.08);
            border: 2px solid rgba(16, 185, 129, 0.3);
            border-radius: 24px;
            padding: 25px;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 18px;
            align-content: start;
            min-height: 450px;
        }
        .mascota-vecina {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 18px;
            padding: 15px;
            text-align: center;
            transition: transform 0.2s;
            position: relative;
        }
        .mascota-vecina:hover { transform: translateY(-5px); border-color: var(--accent); }
        .avatar-vecino { font-size: 3.5rem; display: block; margin-bottom: 6px; animation: brincar 2s infinite alternate; }
        @keyframes brincar { from { transform: translateY(0); } to { transform: translateY(-8px); } }

        .chat-box {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            height: 500px;
        }
        .mensajes-lista {
            flex-grow: 1;
            overflow-y: auto;
            padding-right: 8px;
            margin: 15px 0;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .burbuja { background: rgba(255,255,255,0.06); padding: 10px 14px; border-radius: 14px; font-size: 0.88rem; }
        .burbuja.mia { background: rgba(245, 158, 11, 0.18); border: 1px solid rgba(245, 158, 11, 0.4); align-self: flex-end; }
        .chat-form { display: flex; gap: 8px; }
        .chat-form input { flex-grow: 1; background: #090a0f; border: 1px solid var(--border); color: white; padding: 10px 14px; border-radius: 12px; }
        .chat-form button { background: var(--green); color: #090a0f; border: none; padding: 0 18px; border-radius: 12px; font-weight: 800; cursor: pointer; }
    </style>
</head>
<body>
    <div class="container">
        <div class="top-bar">
            <div>
                <h1 style="color: var(--accent); font-size: 1.5rem;"><i class="fa-solid fa-tree"></i> Plaza Central de Mascotas</h1>
                <p style="color: #9ca3af; font-size: 0.9rem;">Conoce a las criaturas de tus compañeros y charla sobre hábitos saludables.</p>
            </div>
            <div style="display: flex; gap: 10px;">
                <a href="mascota.php" class="btn-volver"><i class="fa-solid fa-gamepad"></i> Volver a mi Mascota</a>
                <a href="panel.php" class="btn-volver" style="background: #334155; color: white;"><i class="fa-solid fa-house"></i> Mi Panel</a>
            </div>
        </div>

        <div class="layout-plaza">
            <!-- ESCENARIO CON TODAS LAS MASCOTAS DEL COLEGIO -->
            <div class="parque-escenario">
                <?php foreach ($todas_mascotas as $m): ?>
                    <?php $icono = $iconos_nivel[$m['nivel_mascota']] ?? '🐣'; ?>
                    <div class="mascota-vecina">
                        <span style="background: rgba(245,158,11,0.2); color: var(--accent); font-size: 0.72rem; padding: 3px 10px; border-radius: 10px; font-weight: bold;">
                            Nivel <?= $m['nivel_mascota'] ?>
                        </span>
                        <span class="avatar-vecino"><?= $icono ?></span>
                        <strong style="display: block; font-size: 0.95rem;"><?= htmlspecialchars($m['nombre_mascota']) ?></strong>
                        <span style="font-size: 0.78rem; color: #9ca3af; display: block;">Dueño: <?= htmlspecialchars(explode(' ', $m['nombre_completo'])[0]) ?></span>
                        <span style="font-size: 0.72rem; color: #34d399;"><?= htmlspecialchars($m['nombre_grado'] ?? '') ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- CHAT EN VIVO DE LAS MASCOTAS -->
            <div class="chat-box">
                <h3 style="color: var(--accent);"><i class="fa-solid fa-comments"></i> Chat de la Plaza</h3>
                <div class="mensajes-lista" id="lista-mensajes"></div>
                <form class="chat-form" onsubmit="enviarMensajeChat(event)">
                    <input type="text" id="input-chat" placeholder="Saluda a las demás mascotas..." maxlength="180" required>
                    <button type="submit"><i class="fa-solid fa-paper-plane"></i></button>
                </form>
            </div>
        </div>
    </div>

    <script>
    const miId = <?= $id_usuario ?>;

    function cargarMensajes() {
        fetch('api_chat_mascotas.php')
        .then(r => r.json())
        .then(data => {
            const lista = document.getElementById('lista-mensajes');
            lista.innerHTML = '';
            data.forEach(msg => {
                const div = document.createElement('div');
                div.className = 'burbuja' + (msg.usuario_id == miId ? ' mia' : '');
                div.innerHTML = `<strong style="color:#fde047; font-size:0.8rem;">${msg.icono} ${msg.nombre_mascota} (${msg.dueno}):</strong><br>${msg.mensaje}`;
                lista.appendChild(div);
            });
            lista.scrollTop = lista.scrollHeight;
        });
    }

    function enviarMensajeChat(e) {
        e.preventDefault();
        const input = document.getElementById('input-chat');
        const texto = input.value.trim();
        if (!texto) return;

        fetch('api_chat_mascotas.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'mensaje=' + encodeURIComponent(texto)
        })
        .then(r => r.json())
        .then(res => {
            if (res.exito) {
                input.value = '';
                cargarMensajes();
            } else {
                alert(res.mensaje);
            }
        });
    }

    cargarMensajes();
    setInterval(cargarMensajes, 3500);
    </script>
</body>
</html>