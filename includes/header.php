<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Restaurante Gamificado</title>
    <link rel="stylesheet" href="/desperdicios_/assets/css/estilos.css">
</head>
<body>
    <header class="main-header">
        <div class="nav-brand">🍽️ Sistema de Restaurante</div>
        <div class="nav-user">
            <span>Hola, <strong><?= htmlspecialchars($_SESSION['nombre_completo'] ?? 'Usuario') ?></strong></span>
            <a href="/desperdicios_/logout.php" class="btn-logout">Cerrar Sesión</a>
        </div>
    </header>
    <div class="main-wrapper">