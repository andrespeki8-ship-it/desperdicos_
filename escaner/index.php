<?php
require_once __DIR__ . '/../includes/auth.php';
requerirRol(['Administrador', 'Personal']);
require_once __DIR__ . '/../includes/header.php';
?>

<h2 class="titulo-seccion">Escáner de Código QR - Asistencia y Comedor</h2>

<div class="card" style="text-align:center;">
    <h3>Escaner Carnet del Estudiante</h3>
    <p style="color:var(--gris-200); margin-bottom:20px;">Coloque el código QR frente a la cámara o ingrese el código manualmente.</p>
    
    <form action="procesar_qr.php" method="POST" style="max-width:400px; margin:0 auto;">
        <label>Código QR / Token del Estudiante</label>
        <input type="text" name="codigo_qr" required autofocus placeholder="Escanee o escriba el código...">
        <button class="btn-dorado" type="submit">Validar Asistencia</button>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>