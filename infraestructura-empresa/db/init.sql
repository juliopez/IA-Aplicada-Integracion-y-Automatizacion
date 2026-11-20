CREATE DATABASE mesa_ayuda CHARACTER SET utf8mb4;
CREATE DATABASE sistema_academico CHARACTER SET utf8mb4;
CREATE USER 'mesa_app'@'%' IDENTIFIED BY 'REEMPLAZAR_MESA';
CREATE USER 'academico_app'@'%' IDENTIFIED BY 'REEMPLAZAR_ACADEMICO';
GRANT SELECT,INSERT,UPDATE ON mesa_ayuda.* TO 'mesa_app'@'%';
GRANT SELECT,INSERT,UPDATE ON sistema_academico.* TO 'academico_app'@'%';
USE mesa_ayuda;
CREATE TABLE tickets (id INT AUTO_INCREMENT PRIMARY KEY, asunto VARCHAR(180) NOT NULL, descripcion TEXT NOT NULL, solicitante VARCHAR(120) NOT NULL, categoria ENUM('acceso','conectividad','aplicacion','otro') NOT NULL DEFAULT 'otro', prioridad ENUM('baja','media','alta','critica') NOT NULL DEFAULT 'media', estado ENUM('abierto','en_proceso','resuelto','cerrado') NOT NULL DEFAULT 'abierto', creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP, actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP);
CREATE TABLE auditoria (id BIGINT AUTO_INCREMENT PRIMARY KEY, operacion VARCHAR(50) NOT NULL, entidad_id INT, detalle TEXT NOT NULL, creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
INSERT INTO tickets (asunto,descripcion,solicitante,categoria,prioridad,estado) VALUES
('Acceso bloqueado','No puedo ingresar al portal academico','ana@ejemplo.test','acceso','alta','abierto'),
('WiFi intermitente','Conexion inestable en sala 12','luis@ejemplo.test','conectividad','media','en_proceso'),
('Error en reportes','El sistema no exporta reportes','maria@ejemplo.test','aplicacion','media','abierto');
USE sistema_academico;
CREATE TABLE usuarios (id INT AUTO_INCREMENT PRIMARY KEY, nombre VARCHAR(120) NOT NULL, correo VARCHAR(150) NOT NULL UNIQUE, cuenta_estado ENUM('activa','bloqueada') NOT NULL DEFAULT 'activa', rol ENUM('estudiante','docente','administrativo') NOT NULL DEFAULT 'estudiante', actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP);
CREATE TABLE servicios (id INT AUTO_INCREMENT PRIMARY KEY, nombre VARCHAR(120) NOT NULL, estado ENUM('operativo','degradado','caido') NOT NULL DEFAULT 'operativo');
CREATE TABLE auditoria (id BIGINT AUTO_INCREMENT PRIMARY KEY, operacion VARCHAR(50) NOT NULL, entidad_id INT, detalle TEXT NOT NULL, creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
INSERT INTO usuarios (nombre,correo,cuenta_estado,rol) VALUES ('Ana Perez','ana@ejemplo.test','bloqueada','estudiante'),('Luis Soto','luis@ejemplo.test','activa','docente'),('Maria Diaz','maria@ejemplo.test','activa','administrativo');
INSERT INTO servicios (nombre,estado) VALUES ('Portal academico','operativo'),('WiFi campus','degradado'),('Reportes','operativo');
