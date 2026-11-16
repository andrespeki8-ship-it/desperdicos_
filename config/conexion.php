<?php
// config/conexion.php
$host = 'localhost';
$dbname = 'restaurante_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("<div style='background:#12141c; color:#ef4444; padding:30px; font-family:sans-serif; text-align:center;'>
        <h3>Error de Conexión a la Base de Datos</h3>
        <p>" . $e->getMessage() . "</p>
    </div>");
}
?>