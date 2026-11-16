<?php
session_start();
require_once __DIR__ . '/../includes/auth.php';
requerir_rol(['Estudiante']);
require_once __DIR__ . '/../config/conexion.php';

$id_usuario = $_SESSION['id_usuario'];

$stmt_u = $pdo->prepare("SELECT nombre_completo FROM usuarios WHERE id_usuario = ?");
$stmt_u->execute([$id_usuario]);
$datos_user = $stmt_u->fetch();
$nombre_estudiante = $datos_user['nombre_completo'] ?? 'Estudiante';

$stmt = $pdo->prepare("SELECT * FROM mascotas WHERE usuario_id = ?");
$stmt->execute([$id_usuario]);
$mascota = $stmt->fetch();

if (!$mascota) {
    $pdo->prepare("INSERT INTO mascotas (usuario_id, nombre_mascota, nivel_mascota, experiencia, vidas) VALUES (?, 'Sajito Bot', 1, 0, 3)")
        ->execute([$id_usuario]);
    header("Location: mascota.php");
    exit;
}

$exp_actual = intval($mascota['experiencia']);
// 10 Niveles en total (cada 100 EXP = 1 nivel, máximo Nivel 10)
$nivel_calculado = min(10, floor($exp_actual / 100) + 1);

if ($nivel_calculado != $mascota['nivel_mascota']) {
    $stmt_niv = $pdo->prepare("UPDATE mascotas SET nivel_mascota = ? WHERE usuario_id = ?");
    $stmt_niv->execute([$nivel_calculado, $id_usuario]);
    $mascota['nivel_mascota'] = $nivel_calculado;
}

$porcentaje_exp = ($mascota['nivel_mascota'] >= 10) ? 100 : ($exp_actual % 100);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ecosistema de 10 Niveles y Minijuego | Desperdicios de Comida AE</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg: #090a0f;
            --card-bg: rgba(22, 27, 39, 0.9);
            --border: rgba(255, 255, 255, 0.1);
            --accent: #f59e0b;
            --neon-green: #10b981;
            --neon-pink: #f43f5e;
            --text: #f3f4f6;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Outfit', sans-serif; }
        body { background: var(--bg); color: var(--text); min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: flex-start; padding: 20px; overflow-x: hidden; }

        .background-glow {
            position: fixed; width: 100vw; height: 100vh; z-index: -1;
            background: radial-gradient(circle at 50% 20%, #31104b 0%, #090a0f 80%);
        }

        .game-container {
            width: 100%; max-width: 800px;
            background: var(--card-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border);
            border-radius: 30px;
            padding: 25px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.6);
            text-align: center;
            position: relative;
            margin-bottom: 40px;
        }

        .header-game {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 15px; font-size: 0.95rem; font-weight: 600;
        }
        .header-game a { color: var(--accent); text-decoration: none; display: flex; align-items: center; gap: 6px; }

        .saludo-box {
            background: rgba(245, 158, 11, 0.1);
            border: 1px solid rgba(245, 158, 11, 0.3);
            padding: 10px 18px; border-radius: 16px;
            margin-bottom: 20px; font-size: 0.95rem; color: #fde047;
            font-weight: 600; text-align: left; display: flex; align-items: center; justify-content: space-between;
        }

        .stage-box {
            position: relative; width: 100%; height: 220px;
            background: radial-gradient(circle, rgba(124, 58, 237, 0.25) 0%, rgba(10, 10, 20, 0.95) 100%);
            border: 2px solid rgba(255, 255, 255, 0.15);
            border-radius: 24px; overflow: hidden;
            display: flex; justify-content: center; align-items: center;
            box-shadow: inset 0 0 35px rgba(0,0,0,0.6);
            margin-bottom: 20px;
        }

        .mascota-avatar {
            cursor: pointer;
            user-select: none;
            filter: drop-shadow(0 20px 20px rgba(0,0,0,0.7));
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            animation: flotarPro 3s ease-in-out infinite alternate;
        }
        @keyframes flotarPro {
            0% { transform: translateY(0px) rotate(-2deg); }
            100% { transform: translateY(-10px) rotate(2deg); }
        }

        .animar-accion { animation: saltarAccion 0.5s ease !important; }
        @keyframes saltarAccion {
            0% { transform: scale(1) translateY(0); }
            50% { transform: scale(1.3) translateY(-30px) rotate(15deg); }
            100% { transform: scale(1) translateY(0); }
        }

        /* Minijuego de Pelotitas Estilo Arcade (Oculto por defecto) */
        #modal-minijuego {
            position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
            background: rgba(9, 10, 15, 0.92); z-index: 9999;
            display: none; flex-direction: column; align-items: center; justify-content: center;
        }
        .canvas-container {
            width: 450px; height: 350px; background: #121624;
            border: 3px solid var(--accent); border-radius: 20px;
            position: relative; overflow: hidden; cursor: crosshair;
            box-shadow: 0 0 30px rgba(245, 158, 11, 0.4);
        }
        .pelotita {
            position: absolute; width: 32px; height: 32px;
            background: radial-gradient(circle, #fde047 0%, #f59e0b 100%);
            border-radius: 50%; box-shadow: 0 0 10px #f59e0b;
            cursor: pointer; display: flex; align-items: center; justify-content: center;
            font-size: 0.7rem; font-weight: bold; color: #121624;
        }

        .niveles-guia {
            background: rgba(124, 58, 237, 0.15);
            border: 1px solid rgba(124, 58, 237, 0.4);
            border-radius: 18px; padding: 15px; margin-bottom: 20px; text-align: left;
        }
        .niveles-guia h4 { color: #c084fc; font-size: 0.95rem; margin-bottom: 8px; display: flex; align-items: center; gap: 6px; }
        .niveles-guia p { font-size: 0.85rem; color: #e2e8f0; line-height: 1.4; }

        .stats-grid {
            display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 20px;
        }
        .stat-card {
            background: rgba(255,255,255,0.03); border: 1px solid var(--border);
            padding: 12px; border-radius: 14px; text-align: left;
        }
        .stat-card span { font-size: 0.75rem; color: #9ca3af; display: block; }
        .stat-card strong { font-size: 1rem; color: #fff; }

        .barra-exp { background: rgba(255,255,255,0.08); height: 10px; border-radius: 8px; overflow: hidden; margin-top: 6px; }
        .barra-exp-fill { background: linear-gradient(90deg, #f59e0b, #10b981); height: 100%; transition: width 0.4s ease; }

        .section-title { font-size: 0.95rem; color: var(--accent); text-align: left; margin: 15px 0 10px 0; font-weight: 700; display: flex; align-items: center; gap: 8px; }
        
        .grid-paneles {
            display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 15px;
        }
        .panel-opcion {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid var(--border);
            border-radius: 16px; padding: 15px 10px;
            display: flex; flex-direction: column; align-items: center; gap: 8px;
            cursor: pointer; transition: all 0.2s ease;
        }
        .panel-opcion:hover {
            background: rgba(245, 158, 11, 0.12);
            border-color: var(--accent);
            transform: translateY(-3px);
        }
        .panel-opcion i { font-size: 1.5rem; color: var(--accent); }
        .panel-opcion span { font-size: 0.85rem; font-weight: 600; color: #fff; }
        .panel-opcion p { font-size: 0.7rem; color: #9ca3af; }

        #floating-msg {
            position: absolute; top: 15px; left: 50%; transform: translateX(-50%);
            background: rgba(16, 185, 129, 0.95); color: white; padding: 6px 18px;
            border-radius: 20px; font-weight: 600; font-size: 0.85rem;
            opacity: 0; transition: opacity 0.3s ease; pointer-events: none; z-index: 10;
        }
        .show-msg { opacity: 1 !important; }
    </style>
</head>
<body>
    <div class="background-glow"></div>

    <!-- MODAL MINIJUEGO DE PELOTITAS (DIFÍCIL) -->
    <div id="modal-minijuego">
        <h2 style="color: var(--accent); margin-bottom: 10px;">🎮 Minijuego: Atrapa la Pelotita (Nivel <span id="lbl-nivel-juego">1</span>/10)</h2>
        <p style="color: #cbd5e1; margin-bottom: 15px; font-size: 0.9rem;">¡Atrapa 10 pelotitas antes de que se acabe el tiempo! Muy difícil.</p>
        <div style="font-size: 1.2rem; font-weight: bold; color: var(--neon-green); margin-bottom: 10px;">Atrapadas: <span id="contador-pelotas">0</span> / 10 | Tiempo: <span id="tiempo-juego">10</span>s</div>
        
        <div class="canvas-container" id="zona-pelotas"></div>

        <button onclick="cerrarMinijuego()" style="margin-top: 15px; background: #f43f5e; color: white; border: none; padding: 8px 20px; border-radius: 10px; font-weight: bold; cursor: pointer;">Cancelar</button>
    </div>

    <div class="game-container" id="panel-juego">
        <div id="floating-msg">¡Acción realizada!</div>

        <div class="header-game">
            <h3 style="color: var(--accent); font-size: 1.2rem;"><?= htmlspecialchars($mascota['nombre_mascota']) ?></h3>
            <a href="panel.php"><i class="fa-solid fa-arrow-left"></i> Volver a mi Panel Estudiantil</a>
        </div>

        <div class="saludo-box">
            <div>
                <span>👋 ¡Hola, <strong><?= htmlspecialchars($nombre_estudiante) ?></strong>!</span>
                <div style="font-size: 0.8rem; color: #cbd5e1; font-weight: 400;" id="txt-subtitulo">Sistema de 10 Niveles de Transformación Activo.</div>
            </div>
            <button onclick="iniciarMinijuego()" style="background: var(--accent); color: #090a0f; border: none; padding: 6px 12px; border-radius: 10px; font-weight: 700; cursor: pointer; font-size: 0.8rem;">
                <i class="fa-solid fa-gamepad"></i> Jugar Minijuego Difícil
            </button>
        </div>

        <div class="stage-box" id="escenario">
            <div class="mascota-avatar" id="avatar" onclick="interactuarMascota()" style="font-size: 4.5rem;">🐣</div>
        </div>

        <!-- GUÍA DE 10 NIVELES -->
        <div class="niveles-guia">
            <h4><i class="fa-solid fa-wand-magic-sparkles"></i> Centro de Evolución (10 Fases de Cuerpo)</h4>
            <p id="texto-ayuda-nivel">Cargando datos de nivel...</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <span>NIVEL DE CRIATURA</span>
                <strong id="txt-nivel"><?= $mascota['nivel_mascota'] ?> / 10</strong>
            </div>
            <div class="stat-card">
                <span>ENERGÍA VITAL</span>
                <strong id="txt-vidas" style="color: var(--neon-pink);">❤️ <?= $mascota['vidas'] ?> / 3</strong>
            </div>
            <div class="stat-card" style="grid-column: span 2;">
                <div style="display: flex; justify-content: space-between; font-size: 0.85rem;">
                    <span style="color:#9ca3af;">EXPERIENCIA (EXP)</span>
                    <strong id="txt-exp"><?= $mascota['experiencia'] ?> pts</strong>
                </div>
                <div class="barra-exp">
                    <div class="barra-exp-fill" id="barra-fill" style="width: <?= $porcentaje_exp ?>%;"></div>
                </div>
            </div>
        </div>

        <!-- ALIMENTOS CON LÍMITES -->
        <div class="section-title"><i class="fa-solid fa-bowl-food"></i> Nutrición (Límite: 3 veces al día c/u)</div>
        <div class="grid-paneles">
            <div class="panel-opcion" onclick="enviarAccion('alimentar_papas')">
                <i class="fa-solid fa-french-fries"></i>
                <span>Papas Sajo</span>
                <p>+15 EXP</p>
            </div>
            <div class="panel-opcion" onclick="enviarAccion('alimentar_ensalada')">
                <i class="fa-solid fa-carrot"></i>
                <span>Ensalada Verde</span>
                <p>+20 EXP</p>
            </div>
            <div class="panel-opcion" onclick="enviarAccion('alimentar_fruta')">
                <i class="fa-solid fa-apple-whole"></i>
                <span>Fruta Fresca</span>
                <p>+10 EXP</p>
            </div>
        </div>

        <!-- ENTRENAMIENTO -->
        <div class="section-title"><i class="fa-solid fa-dumbbell"></i> Entrenamiento Físico (Límite: 2 veces al día)</div>
        <div class="grid-paneles">
            <div class="panel-opcion" onclick="enviarAccion('entrenar_fuerza')">
                <i class="fa-solid fa-fire"></i>
                <span>Cardio Escolar</span>
                <p>+25 EXP</p>
            </div>
            <div class="panel-opcion" onclick="enviarAccion('entrenar_reciclaje')">
                <i class="fa-solid fa-recycle"></i>
                <span>Eco-Reciclaje</span>
                <p>+25 EXP</p>
            </div>
            <div class="panel-opcion" onclick="enviarAccion('entrenar_enfoque')">
                <i class="fa-solid fa-brain"></i>
                <span>Enfoque Mental</span>
                <p>+20 EXP</p>
            </div>
        </div>

        <!-- DESAFÍOS -->
        <div class="section-title"><i class="fa-solid fa-trophy"></i> Desafíos de Comedor (Límite: 1 vez al día)</div>
        <div class="grid-paneles">
            <div class="panel-opcion" onclick="enviarAccion('desafio_plato_limpio')">
                <i class="fa-solid fa-check-double"></i>
                <span>Plato 100% Limpio</span>
                <p>+40 EXP</p>
            </div>
            <div class="panel-opcion" onclick="enviarAccion('desafio_qr')">
                <i class="fa-solid fa-qrcode"></i>
                <span>Escaneo QR</span>
                <p>+30 EXP</p>
            </div>
            <div class="panel-opcion" onclick="enviarAccion('desafio_ayuda')">
                <i class="fa-solid fa-hands-holding-child"></i>
                <span>Ayuda en Cafetería</span>
                <p>+50 EXP</p>
            </div>
        </div>
    </div>

    <script>
    // 10 Niveles de Evolución Física Detallados
    const fasesEvolucion = {
        1: { icono: '🐣', tamano: '4.0rem', nombreFase: 'Huevo / Bebé (Nivel 1)', guia: 'Fase inicial. Aliméntate balanceadamente para abrir caparazón.' },
        2: { icono: '🐥', tamano: '4.6rem', nombreFase: 'Criatura Tierna (Nivel 2)', guia: 'Tus extremidades se empiezan a formar con los entrenamientos.' },
        3: { icono: '🦊', tamano: '5.2rem', nombreFase: 'Zorro Ágil (Nivel 3)', guia: 'Agilidad mejorada gracias al reciclaje escolar y la fruta.' },
        4: { icono: '🐺', tamano: '5.8rem', nombreFase: 'Lobo Joven (Nivel 4)', guia: 'Tu masa corporal aumenta de tamaño visiblemente.' },
        5: { icono: '🦁', tamano: '6.4rem', nombreFase: 'Bestia Robusta (Nivel 5)', guia: '¡Mitad de camino! Cuerpo fuerte y resistente al esfuerzo.' },
        6: { icono: '🐯', tamano: '7.0rem', nombreFase: 'Depredador Feroz (Nivel 6)', guia: 'Fuerza extrema alcanzada mediante los desafíos del comedor.' },
        7: { icono: '🦅', tamano: '7.6rem', nombreFase: 'Guardián Alado (Nivel 7)', guia: 'Capacidad de visión y reflejos nivelados.' },
        8: { icono: '🐲', tamano: '8.2rem', nombreFase: 'Dragón Menor (Nivel 8)', guia: 'Poder de transformación avanzado desbloqueado.' },
        9: { icono: '🐉', tamano: '8.8rem', nombreFase: 'Dragón Supremo (Nivel 9)', guia: 'A un solo nivel de la forma definitiva absoluta.' },
        10: { icono: '👑⚡', tamano: '9.5rem', nombreFase: 'Deidad Legendaria (Nivel 10)', guia: '¡Cima absoluta! Forma perfecta e imbatible del comedor.' }
    };

    function actualizarCuerpoMascota(nivel) {
        const fase = fasesEvolucion[nivel] || fasesEvolucion[1];
        const avatar = document.getElementById('avatar');
        avatar.innerText = fase.icono;
        avatar.style.fontSize = fase.tamano;
        
        document.getElementById('txt-subtitulo').innerText = "Fase: " + fase.nombreFase;
        document.getElementById('texto-ayuda-nivel').innerHTML = `<strong>Nivel ${nivel} / 10: ${fase.nombreFase}.</strong><br>${fase.guia}`;
    }

    actualizarCuerpoMascota(<?= $mascota['nivel_mascota'] ?>);

    function interactuarMascota() {
        const avatar = document.getElementById('avatar');
        avatar.classList.add('animar-accion');
        setTimeout(() => avatar.classList.remove('animar-accion'), 500);
        mostrarMensaje("¡Hola! Juega al minijuego de pelotitas para ganar EXP extra.");
    }

    function mostrarMensaje(texto) {
        const msg = document.getElementById('floating-msg');
        msg.innerText = texto;
        msg.classList.add('show-msg');
        setTimeout(() => msg.classList.remove('show-msg'), 2500);
    }

    // --- LÓGICA DEL MINIJUEGO DE PELOTITAS DIFÍCILES ---
    let intervaloPelotas = null;
    let tiempoRestante = 10;
    let pelotasAtrapadas = 0;
    let nivelJuego = 1;

    function iniciarMinijuego() {
        nivelJuego = parseInt(document.getElementById('txt-nivel').innerText) || 1;
        document.getElementById('lbl-nivel-juego').innerText = nivelJuego;
        document.getElementById('modal-minijuego').style.display = 'flex';
        pelotasAtrapadas = 0;
        tiempoRestante = Math.max(4, 11 - nivelJuego); // Más difícil en niveles altos
        document.getElementById('contador-pelotas').innerText = pelotasAtrapadas;
        document.getElementById('tiempo-juego').innerText = tiempoRestante;

        const zona = document.getElementById('zona-pelotas');
        zona.innerHTML = '';

        // Generar pelotitas difíciles con velocidad
        intervaloPelotas = setInterval(() => {
            tiempoRestante--;
            document.getElementById('tiempo-juego').innerText = tiempoRestante;

            if (tiempoRestante <= 0) {
                clearInterval(intervaloPelotas);
                detenerMinijuego(false);
            }
        }, 1000);

        generarPelota();
    }

    function generarPelota() {
        const zona = document.getElementById('zona-pelotas');
        if (pelotasAtrapadas >= 10 || document.getElementById('modal-minijuego').style.display === 'none') return;

        const pelota = document.createElement('div');
        pelota.className = 'pelotita';
        pelota.innerText = '•O•';
        
        let rx = Math.random() * (zona.clientWidth - 40);
        let ry = Math.random() * (zona.clientHeight - 40);
        pelota.style.left = rx + 'px';
        pelota.style.top = ry + 'px';

        pelota.onmousedown = () => {
            pelotasAtrapadas++;
            document.getElementById('contador-pelotas').innerText = pelotasAtrapadas;
            pelota.remove();

            if (pelotasAtrapadas >= 10) {
                clearInterval(intervaloPelotas);
                detenerMinijuego(true);
            } else {
                generarPelota();
            }
        };

        zona.appendChild(pelota);

        // Desaparecen rápido (Modo Difícil)
        setTimeout(() => {
            if (pelota.parentElement) pelota.remove();
        }, Math.max(800, 1800 - (nivelJuego * 100)));
    }

    function cerrarMinijuego() {
        clearInterval(intervaloPelotas);
        document.getElementById('modal-minijuego').style.display = 'none';
    }

    function detenerMinijuego(gano) {
        document.getElementById('modal-minijuego').style.display = 'none';
        if (gano) {
            enviarAccion('minijuego_ganado');
        } else {
            alert("¡Se acabó el tiempo! No lograste atrapar las 10 pelotitas.");
        }
    }

    function enviarAccion(tipo) {
        const avatar = document.getElementById('avatar');
        avatar.classList.add('animar-accion');
        setTimeout(() => avatar.classList.remove('animar-accion'), 500);

        fetch('./validador_mascota.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'accion=' + encodeURIComponent(tipo)
        })
        .then(async r => {
            const text = await r.text();
            try { return JSON.parse(text); } 
            catch (e) { throw new Error("Error de sintaxis en servidor."); }
        })
        .then(data => {
            if (data.exito) {
                document.getElementById('txt-exp').innerText = data.nueva_exp + ' pts';
                document.getElementById('txt-vidas').innerText = '❤️ ' + data.nuevas_vidas + ' / 3';
                document.getElementById('txt-nivel').innerText = data.nuevo_nivel + ' / 10';
                document.getElementById('barra-fill').style.width = data.porcentaje_exp + '%';
                
                actualizarCuerpoMascota(data.nuevo_nivel);
                
                if (data.subio_nivel) {
                    mostrarMensaje("🎉 ¡MUTACIÓN! Subiste al Nivel " + data.nuevo_nivel + " / 10");
                } else {
                    mostrarMensaje(data.mensaje_feedback);
                }
            } else {
                mostrarMensaje(data.mensaje || "Límite diario alcanzado.");
            }
        })
        .catch(err => {
            alert("Error de conexión: " + err.message);
        });
    }
    </script>
</body>
</html>