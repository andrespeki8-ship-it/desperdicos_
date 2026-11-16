<?php
session_start();
require_once __DIR__ . '/../includes/auth.php';
requerir_rol(['Estudiante', 'Administrador']);
require_once __DIR__ . '/../config/conexion.php';

$mensaje = '';
$puntos_ganados = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['escanear_plato'])) {
    $id_usuario = $_SESSION['id_usuario'];
    
    // Detección simulada de 3 alimentos en el plato (10 puntos cada uno = 30 puntos)
    $puntos_ganados = 30; 

    $pdo->prepare("UPDATE usuarios SET puntos_totales = puntos_totales + ? WHERE id_usuario = ?")->execute([$puntos_ganados, $id_usuario]);
    $mensaje = "¡Excelente hábito! La IA ha detectado 3 grupos de alimentos consumidos en tu plato. ¡Has ganado +" . $puntos_ganados . " puntos!";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Escáner de Plato por IA | Estudiante</title>
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
        .content { padding: 40px; display: flex; justify-content: center; }
        .card { background-color: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 30px; width: 100%; max-width: 600px; text-align: center; }
        h2 { color: var(--accent-gold); margin-bottom: 15px; }
        .camera-box { border: 2px dashed var(--border-color); border-radius: 10px; padding: 20px; margin-bottom: 20px; background: #12141c; }
        .camera-box input[type="file"] { display: none; }
        .btn-camara { background-color: #3b82f6; color: white; padding: 12px 20px; border-radius: 8px; cursor: pointer; display: inline-block; font-weight: bold; margin-bottom: 15px; }
        .btn-dorado { background-color: var(--accent-gold); color: #12141c; font-weight: 700; padding: 12px 24px; border: none; border-radius: 8px; cursor: pointer; width: 100%; font-size: 1rem; }
        .alerta-exito { background-color: rgba(16, 185, 129, 0.1); border: 1px solid #10b981; color: #34d399; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-size: 1.05rem; }
        #preview { max-width: 100%; max-height: 250px; border-radius: 8px; display: none; margin: 0 auto 15px auto; }
    </style>
</head>
<body>
    <aside>
        <div class="sidebar-brand"><i class="fa-solid fa-utensils"></i> Panel Estudiantil</div>
        <ul class="sidebar-menu">
            <li><a href="panel.php"><i class="fa-solid fa-house"></i> Mi Panel</a></li>
            <li><a href="escaner_comida.php" class="active"><i class="fa-solid fa-camera"></i> Escáner de Plato IA</a></li>
            <li><a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i> Cerrar Sesión</a></li>
        </ul>
    </aside>

    <main>
        <header>
            <div style="font-weight: 600; color: var(--text-muted);">Módulo de Sostenibilidad por Cámara</div>
            <span><i class="fa-solid fa-user"></i> <?= htmlspecialchars($_SESSION['nombre_completo'] ?? 'Estudiante') ?></span>
        </header>

        <div class="content">
            <div class="card">
                <h2><i class="fa-solid fa-camera-retro"></i> Verificación de Cero Desperdicio</h2>
                <p style="color: var(--text-muted); margin-bottom: 20px;">Toma una foto de tu plato terminado. La inteligencia artificial identificará los 3 alimentos consumidos y te acreditará <strong>+10 puntos por cada alimento</strong>.</p>

                <?php if ($mensaje): ?>
                    <div class="alerta-exito"><i class="fa-solid fa-award"></i> <?= $mensaje ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="camera-box">
                        <img id="preview" alt="Vista previa del plato">
                        <label for="fotoPlato" class="btn-camara"><i class="fa-solid fa-camera"></i> Tomar / Subir Foto del Plato</label>
                        <input type="file" id="fotoPlato" accept="image/*" capture="environment" onchange="mostrarVistaPrevia(event)">
                        <p style="font-size: 0.8rem; color: var(--text-muted);">Asegúrate de que se aprecien los alimentos consumidos.</p>
                    </div>

                    <button type="submit" name="escaner_plato" class="btn-dorado"><i class="fa-solid fa-bolt"></i> Analizar Plato y Reclamar Puntos</button>
                </form>
            </div>
        </div>
    </main>

    <script>
        function mostrarVistaPrevia(event) {
            let reader = new FileReader();
            reader.onload = function(){
                let output = document.getElementById('preview');
                output.src = reader.result;
                output.style.display = 'block';
            };
            reader.readAsDataURL(event.target.files[0]);
        }
    </script>
</body>
</html>