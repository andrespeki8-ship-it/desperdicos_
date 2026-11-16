<?php
session_start();
require_once 'config/conexion.php';

$estudiantes = [
    // Grado 11-1
    ["nombre" => "Angulo Rodriguez Luisa Fernanda", "doc" => "1111548041", "grado" => "11-1"],
    ["nombre" => "Angulo Viafara Juan Pablo", "doc" => "1105373321", "grado" => "11-1"],
    ["nombre" => "Ballesteros Rodallega Leinis Marian", "doc" => "1078688919", "grado" => "11-1"],
    ["nombre" => "Bolaños Vargas Juan Camilo", "doc" => "1111677567", "grado" => "11-1"],
    ["nombre" => "Bueno Gil Gabriela", "doc" => "1109549382", "grado" => "11-1"],
    ["nombre" => "Cardenas Murillo Heidy Vanessa", "doc" => "1110297601", "grado" => "11-1"],
    ["nombre" => "Escobar Valencia Laura Marcela", "doc" => "1096035846", "grado" => "11-1"],
    ["nombre" => "Grisales Rosero Manuela", "doc" => "1105931017", "grado" => "11-1"],
    ["nombre" => "Gutierrez Prieto Daury Yuleidy", "doc" => "1117521029", "grado" => "11-1"],
    ["nombre" => "Hurtado Velasco Mariana Liseth", "doc" => "1085549569", "grado" => "11-1"],
    ["nombre" => "Imbajoa Aviles Melany Andrea", "doc" => "1110370145", "grado" => "11-1"],
    ["nombre" => "Lozano Cordoba Roland David", "doc" => "1077711218", "grado" => "11-1"],
    ["nombre" => "Marquez Mendoza Wuilmarys Daniela", "doc" => "6623979", "grado" => "11-1"],
    ["nombre" => "Morillo Pulgar Jhosimar Alexandra", "doc" => "6422643", "grado" => "11-1"],
    ["nombre" => "Mosquera Valencia Heidy Sofia", "doc" => "1109922777", "grado" => "11-1"],
    ["nombre" => "Murillo Posso Orlennys", "doc" => "1150939480", "grado" => "11-1"],
    ["nombre" => "Ordoñez Herrera Helen Sofia", "doc" => "1111548554", "grado" => "11-1"],
    ["nombre" => "Otalvaro Lopez Juan Jose", "doc" => "1105376803", "grado" => "11-1"],
    ["nombre" => "Panameño Sinisterra Eiling Violeta", "doc" => "1061116773", "grado" => "11-1"],
    ["nombre" => "Renteria Suarez Brenda Michel", "doc" => "1089800386", "grado" => "11-1"],
    ["nombre" => "Rocha Villa Marlen Yajaira", "doc" => "5475786", "grado" => "11-1"],
    ["nombre" => "Sanchez Davila Dylan Andres", "doc" => "1111548575", "grado" => "11-1"],
    ["nombre" => "Segura Caicedo Crismar Andrea", "doc" => "1085550506", "grado" => "11-1"],
    ["nombre" => "Sinisterra Carabali Maria Daniela", "doc" => "1085550141", "grado" => "11-1"],
    ["nombre" => "Sinisterra Moreno Keidy Nicol", "doc" => "1111808890", "grado" => "11-1"],
    
    // Grado 11-2
    ["nombre" => "Anchico Micolta Alex David", "doc" => "1086194926", "grado" => "11-2"],
    ["nombre" => "Arango Pequi Andres David", "doc" => "1110370268", "grado" => "11-2"],
    ["nombre" => "Arce Mina Sebastian", "doc" => "1107853489", "grado" => "11-2"],
    ["nombre" => "Bellorin Mariño Nerymar De Los Angeles", "doc" => "5213526", "grado" => "11-2"],
    ["nombre" => "Caicedo Renteria Lisbeth Liceth", "doc" => "1151439788", "grado" => "11-2"],
    ["nombre" => "Cifuentes Reina Juan Sebastian", "doc" => "1089003014", "grado" => "11-2"],
    ["nombre" => "Cortes Martinez Juan Sebastian", "doc" => "1111677675", "grado" => "11-2"],
    ["nombre" => "Cortes Valencia Dana Sofia", "doc" => "1107067029", "grado" => "11-2"],
    ["nombre" => "Diaz Garcia Albanis", "doc" => "5440020", "grado" => "11-2"],
    ["nombre" => "Diaz Martinez Cristhian Camilo", "doc" => "1104822368", "grado" => "11-2"],
    ["nombre" => "Espinosa Buila Luis Santiago", "doc" => "1191213052", "grado" => "11-2"],
    ["nombre" => "Fernandez Vergara Yareth Tatiana", "doc" => "1060103991", "grado" => "11-2"],
    ["nombre" => "Garcia Saa Shary Germayuri", "doc" => "1064488935", "grado" => "11-2"],
    ["nombre" => "Jordan Carrion Karjelys Carolina", "doc" => "7923437", "grado" => "11-2"],
    ["nombre" => "Lozano Rivas Laura Yiceth", "doc" => "1111548455", "grado" => "11-2"],
    ["nombre" => "Obando Salcedo Oscar Ivan", "doc" => "1085549315", "grado" => "11-2"],
    ["nombre" => "Potes Sanches Jhonatan Alcides", "doc" => "1110296351", "grado" => "11-2"],
    ["nombre" => "Quiñones Perlaza Hillary Taliana", "doc" => "1111676928", "grado" => "11-2"],
    ["nombre" => "Riascos Alomia Jose David", "doc" => "1148690650", "grado" => "11-2"],
    ["nombre" => "Silva Benitez Karoll Paulina", "doc" => "1111549203", "grado" => "11-2"],
    ["nombre" => "Tapasco Vergara Maria Del Mar", "doc" => "1114312085", "grado" => "11-2"],
    ["nombre" => "Torres Largacha Hellen Yineth", "doc" => "1111677860", "grado" => "11-2"],
    ["nombre" => "Torres Velasquez Alejandro", "doc" => "1105933613", "grado" => "11-2"],
    ["nombre" => "Valencia Pineda Luisa Fernanda", "doc" => "1028887689", "grado" => "11-2"]
];

echo "<h2>Forzando Registro de Estudiantes en Base de Datos y Admin</h2>";
echo "<ul style='font-family: monospace;'>";

foreach ($estudiantes as $est) {
    $nombre = $est['nombre'];
    $documento = $est['doc'];
    $grado_nombre = $est['grado'];
    $username = $documento;
    $password = $documento;

    try {
        // 1. Obtener o crear el grado
        $stmtGrado = $pdo->prepare("SELECT id_grado FROM grados WHERE nombre_grado = ? LIMIT 1");
        $stmtGrado->execute([$grado_nombre]);
        $gradoRow = $stmtGrado->fetch(PDO::FETCH_ASSOC);

        if ($gradoRow) {
            $id_grado = $gradoRow['id_grado'];
        } else {
            $stmtInsertGrado = $pdo->prepare("INSERT INTO grados (nombre_grado) VALUES (?)");
            $stmtInsertGrado->execute([$grado_nombre]);
            $id_grado = $pdo->lastInsertId();
        }

        // 2. Verificar si ya existe en la tabla usuarios
        $stmtCheck = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE documento = ? OR username = ? LIMIT 1");
        $stmtCheck->execute([$documento, $documento]);
        $existe = $stmtCheck->fetch();

        if ($existe) {
            // Forzar actualización completa de los campos que lee el panel admin (id_rol = 3 asegurado)
            $stmtUpdate = $pdo->prepare("
                UPDATE usuarios 
                SET nombre_completo = ?, username = ?, documento = ?, password = ?, id_grado = ?, id_rol = 3, rol = 'Estudiante', puntos_totales = COALESCE(puntos_totales, 0) 
                WHERE documento = ? OR username = ?
            ");
            $stmtUpdate->execute([$nombre, $username, $documento, $password, $id_grado, $documento, $documento]);
            echo "<li style='color: #2563eb;'>Actualizado: <strong>$nombre</strong> ($documento)</li>";
        } else {
            // Inserción directa con id_rol = 3
            $stmtInsert = $pdo->prepare("
                INSERT INTO usuarios (nombre_completo, username, documento, password, id_grado, id_rol, rol, puntos_totales) 
                VALUES (?, ?, ?, ?, ?, 3, 'Estudiante', 0)
            ");
            $stmtInsert->execute([$nombre, $username, $documento, $password, $id_grado]);
            echo "<li style='color: #16a34a;'>Insertado: <strong>$nombre</strong> ($documento)</li>";
        }

    } catch (Exception $e) {
        echo "<li style='color: #dc2626;'>Error en $nombre: " . $e->getMessage() . "</li>";
    }
}

echo "</ul>";
echo "<h3>¡Listo! Ve a tu panel de administración a refrescar la página.</h3>";
echo "<a href='admin/usuarios.php' style='padding: 10px 20px; background: #f97316; color: #fff; text-decoration: none; border-radius: 5px; font-weight: bold;'>Ir al Panel de Usuarios</a>";
?>