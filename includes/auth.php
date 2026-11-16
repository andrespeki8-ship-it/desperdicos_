<?php
// includes/auth.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Conexión a la base de datos (subiendo un nivel desde includes/ hacia config/)
require_once __DIR__ . '/../config/conexion.php';

function verificar_sesion() {
    if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['id_rol'])) {
        header("Location: ../login.php");
        exit;
    }
}

function requerir_rol($roles_permitidos = []) {
    verificar_sesion();

    $rol_actual = $_SESSION['id_rol'];
    $autorizado = false;

    foreach ($roles_permitidos as $rol) {
        if (is_numeric($rol) && intval($rol) === intval($rol_actual)) {
            $autorizado = true;
            break;
        }
        if (strcasecmp($rol, 'Administrador') === 0 && (intval($rol_actual) === 1 || strcasecmp($rol_actual, 'Administrador') === 0)) { $autorizado = true; break; }
        if (strcasecmp($rol, 'Personal') === 0 && (intval($rol_actual) === 2 || strcasecmp($rol_actual, 'Personal') === 0)) { $autorizado = true; break; }
        if (strcasecmp($rol, 'Estudiante') === 0 && (intval($rol_actual) === 3 || strcasecmp($rol_actual, 'Estudiante') === 0)) { $autorizado = true; break; }
    }

    if (!$autorizado) {
        header("Location: ../login.php?error=no_autorizado");
        exit;
    }
}
?>