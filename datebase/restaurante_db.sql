-- ========================================================
-- RESPALDO COMPLETO: Gamified Institutional Restaurant System
-- Base de Datos: restaurante_db
-- ========================================================

CREATE DATABASE IF NOT EXISTS restaurante_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE restaurante_db;

-- 1. Tabla de Roles (Permisos del sistema)
DROP TABLE IF EXISTS roles;
CREATE TABLE roles (
    id_rol INT AUTO_INCREMENT PRIMARY KEY,
    nombre_rol VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

INSERT INTO roles (id_rol, nombre_rol) VALUES 
(1, 'Administrador'),
(2, 'Personal'),
(3, 'Estudiante')
ON DUPLICATE KEY UPDATE nombre_rol=VALUES(nombre_rol);

-- 2. Tabla de Grados Escolares
DROP TABLE IF EXISTS grados;
CREATE TABLE grados (
    id_grado INT AUTO_INCREMENT PRIMARY KEY,
    nombre_grado VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

INSERT INTO grados (id_grado, nombre_grado) VALUES 
(1, '11-1'),
(2, '11-2')
ON DUPLICATE KEY UPDATE nombre_grado=VALUES(nombre_grado);

-- 3. Tabla de Usuarios (Administradores, Personal y Estudiantes)
DROP TABLE IF EXISTS usuarios;
CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre_completo VARCHAR(150) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    password_texto VARCHAR(255) DEFAULT 'Estudiante2026*',
    documento VARCHAR(50) UNIQUE,
    id_rol INT NOT NULL,
    id_grado INT DEFAULT NULL,
    puntos_totales INT DEFAULT 0,
    activo TINYINT DEFAULT 1,
    FOREIGN KEY (id_rol) REFERENCES roles(id_rol) ON DELETE CASCADE,
    FOREIGN KEY (id_grado) REFERENCES grados(id_grado) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Usuario Administrador y Personal por defecto (Contraseña: 123456)
INSERT INTO usuarios (nombre_completo, username, password_hash, password_texto, documento, id_rol, id_grado, puntos_totales, activo) VALUES 
('Administrador General', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '123456', '1000000001', 1, NULL, 500, 1),
('Personal de Servicio', 'personal', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '123456', '1000000002', 2, NULL, 100, 1)
ON DUPLICATE KEY UPDATE documento=VALUES(documento);

-- Estudiantes de prueba con sus documentos
INSERT INTO usuarios (nombre_completo, username, password_hash, password_texto, documento, id_rol, id_grado, puntos_totales, activo) VALUES 
('Luisa Fernanda Agudelo Rodriguez', 'lagudelo', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Estudiante2026*', '1111548041', 3, 1, 50, 1),
('Juan Pablo Angulo Viafara', 'jangulov', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Estudiante2026*', '1105373321', 3, 1, 75, 1),
('Leis Marian Ballesteros Rodallega', 'lballesteros', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Estudiante2026*', '107869919', 3, 1, 30, 1),
('Juan Camilo Bolaños Vargas', 'jbolanos', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Estudiante2026*', '1111677567', 3, 1, 100, 1),
('Gil Gabriela Bueno', 'gbueno', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Estudiante2026*', '1109545382', 3, 1, 45, 1),
('Alex David Anchico Micolta', 'aanchico', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Estudiante2026*', '1086194926', 3, 2, 90, 1),
('Andrés David Arango Pequi', 'aarango', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Estudiante2026*', '1110370268', 3, 2, 150, 1),
('Mina Sebastian Arce', 'sarce', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Estudiante2026*', '1107853489', 3, 2, 60, 1)
ON DUPLICATE KEY UPDATE documento=VALUES(documento), puntos_totales=VALUES(puntos_totales);

-- 4. Tabla de Asistencias (Registro rápido por QR por el Personal)
DROP TABLE IF EXISTS asistencias;
CREATE TABLE asistencias (
    id_asistencia INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    fecha_asistencia TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    puntos_otorgados INT DEFAULT 5,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 5. Tabla de Menú Institucional
DROP TABLE IF EXISTS menu;
CREATE TABLE menu (
    id_menu INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT,
    fecha DATE DEFAULT (CURRENT_DATE)
) ENGINE=InnoDB;

INSERT INTO menu (nombre, descripcion, fecha) VALUES 
('Almuerzo Balanceado con Pollo', 'Arroz, pollo a la plancha, ensalada fresca y jugo natural.', CURDATE()),
('Menú Saludable Vegetariano', 'Quinua, verduras al vapor, sopa de verduras y bebida.', CURDATE());

-- 6. Tabla de Beneficios / Recompensas Gamificadas
DROP TABLE IF EXISTS beneficios;
CREATE TABLE beneficios (
    id_beneficio INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(120) NOT NULL,
    descripcion TEXT,
    costo_puntos INT NOT NULL,
    stock INT DEFAULT 50,
    activo TINYINT DEFAULT 1
) ENGINE=InnoDB;

INSERT INTO beneficios (nombre, descripcion, costo_puntos, stock, activo) VALUES 
('Prioridad en la Fila', 'Acceso directo a la fila principal del restaurante institucional.', 100, 50, 1),
('Postre Especial', 'Reclamación de postre o bebida en la barra de atención.', 250, 30, 1),
('Almuerzo Gratis', 'Un menú completo totalmente bonificado por cero desperdicio.', 500, 10, 1);