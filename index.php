<?php
require_once 'config/conexion.php';
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT u.*, r.nombre_rol FROM usuarios u JOIN roles r ON u.id_rol = r.id_rol WHERE u.username = :username AND u.activo = 1");
        $stmt->execute(['username' => $username]);
        $usuario = $stmt->fetch();

        if ($usuario && password_verify($password, $usuario['password_hash'])) {
            $_SESSION['id_usuario'] = $usuario['id_usuario'];
            $_SESSION['nombre_completo'] = $usuario['nombre_completo'];
            $_SESSION['username'] = $usuario['username'];
            $_SESSION['nombre_rol'] = $usuario['nombre_rol'];

            if ($usuario['nombre_rol'] === 'Administrador') {
                header("Location: admin/dashboard.php");
                exit;
            } elseif ($usuario['nombre_rol'] === 'Personal') {
                header("Location: personal/dashboard.php");
                exit;
            } else {
                header("Location: estudiante/panel.php");
                exit;
            }
        } else {
            $error = "Credenciales inválidas.";
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
    <title>Login | Restaurante Gamificado</title>
    <link rel="stylesheet" href="assets/css/estilos.css">
</head>
<body class="login-body">
    <div class="login-container">
        <div class="login-card">
            <h2>🍽️ Restaurante</h2>
            <p>SISTEMA DE GESTIÓN GAMIFICADA</p>
            
            <?php if (!empty($error)): ?>
                <div class="alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <label for="username">USUARIO</label>
                <input type="text" id="username" name="username" required autocomplete="off">

                <label for="password">CONTRASEÑA</label>
                <input type="password" id="password" name="password" required>

                <button type="submit">INGRESAR</button>
            </form>
        </div>
    </div>
</body>
</html>