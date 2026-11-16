<?php
session_start();
require_once 'config/conexion.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    die("Acceso no autorizado.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Calcular métricas actuales de la semana
    $stmtMetrics = $pdo->query("SELECT 
        COUNT(*) as total_asistencias,
        SUM(CASE WHEN comio_todo = 0 THEN 1 ELSE 0 END) as total_desperdicios,
        SUM(puntos_otorgados) as total_puntos
        FROM asistencias");
    $metrics = $stmtMetrics->fetch();

    $semanaActual = date('W');
    $fechaInicio = date('Y-m-d', strtotime('-7 days'));
    $fechaFin = date('Y-m-d');

    // 2. Guardar en el histórico de reportes
    $stmtBackup = $pdo->prepare("INSERT INTO reportes_semanales 
        (semana_num, fecha_inicio, fecha_fin, total_asistencias, total_desperdicios, total_puntos_otorgados) 
        VALUES (?, ?, ?, ?, ?, ?)");
    $stmtBackup->execute([
        $semanaActual,
        $fechaInicio,
        $fechaFin,
        $metrics['total_asistencias'] ?? 0,
        $metrics['total_desperdicios'] ?? 0,
        $metrics['total_puntos'] ?? 0
    ]);

    // 3. Limpiar asistencias actuales para la nueva semana
    $pdo->query("TRUNCATE TABLE asistencias");

    header("Location: admin/dashboard.php?msg=Semana+reiniciada+y+reporte+guardado");
    exit();
}
?>