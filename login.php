<?php
session_start();
require_once __DIR__ . '/config/conexion.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!empty($usuario) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE username = ? OR documento = ?");
        $stmt->execute([$usuario, $usuario]);
        $user = $stmt->fetch();

        if ($user) {
            if (password_verify($password, $user['password_hash']) || $password === $user['documento'] || (isset($user['password_texto']) && $password === $user['password_texto'])) {
                
                $_SESSION['id_usuario'] = $user['id_usuario'];
                $_SESSION['nombre_completo'] = $user['nombre_completo'];
                $_SESSION['id_rol'] = intval($user['id_rol']);

                if ($user['id_rol'] == 1) {
                    header("Location: admin/dashboard.php");
                    exit;
                } elseif ($user['id_rol'] == 2) {
                    header("Location: personal/dashboard.php");
                    exit;
                } else {
                    header("Location: estudiante/panel.php");
                    exit;
                }
            } else {
                $error = "Contraseña incorrecta.";
            }
        } else {
            $error = "El usuario o documento no existe.";
        }
    } else {
        $error = "Por favor completa todos los campos.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión | Restaurante Escolar</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --bg-main: #12141c; --bg-card: #1a1d29; --accent-gold: #f59e0b; --text-main: #f3f4f6; --text-muted: #9ca3af; --border-color: #2e3548; }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Inter', sans-serif; }
        body { background-color: var(--bg-main); color: var(--text-main); display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .login-card { background-color: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; padding: 40px; width: 100%; max-width: 420px; box-shadow: 0 10px 25px rgba(0,0,0,0.5); }
        .login-brand { text-align: center; margin-bottom: 30px; }
        .login-brand i { font-size: 3rem; color: var(--accent-gold); margin-bottom: 10px; }
        .login-brand h1 { font-size: 1.5rem; color: white; font-weight: 700; }
        label { display: block; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 6px; font-weight: 500; }
        input { width: 100%; padding: 12px; background-color: #12141c; border: 1px solid var(--border-color); border-radius: 8px; color: white; margin-bottom: 20px; outline: none; font-size: 0.95rem; }
        input:focus { border-color: var(--accent-gold); }
        .btn-login { background-color: var(--accent-gold); color: #12141c; font-weight: 700; padding: 12px; border: none; border-radius: 8px; cursor: pointer; width: 100%; font-size: 1rem; transition: background 0.3s; }
        .btn-login:hover { background-color: #d97706; }
        .alerta-error { background-color: rgba(239, 68, 68, 0.1); border: 1px solid #ef4444; color: #f87171; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; text-align: center; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-brand">
            <i class="fa-solid fa-utensils"></i>
            <h1>Restaurante Escolar</h1>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alerta-error"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <label>Usuario o Documento</label>
            <input type="text" name="usuario" required placeholder="Ingresa tu usuario o documento...">
            
            <label>Contraseña</label>
            <input type="password" name="password" required placeholder="Ingresa tu contraseña...">

            <button type="submit" class="btn-login">Ingresar</button>
        </form>
    </div>
</body>
</html>