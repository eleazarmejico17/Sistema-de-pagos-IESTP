-- =====================================================
-- SISTEMA DE PAGOS IESTP - TABLAS EN USO REAL
-- Solo las tablas que el sistema está utilizando actualmente
-- =====================================================

-- Crear base de datos (ajustar el nombre según necesidad)
CREATE DATABASE IF NOT EXISTS db_sistema CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE db_sistema;
SET sql_mode='';

-- =====================================================
-- 1. TABLAS DE UBIGEO (UBICACIONES)
-- =====================================================

CREATE TABLE ubdepartamento(
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    departamento VARCHAR(250)
);

CREATE TABLE ubprovincia(
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    provincia VARCHAR(250),
    ubdepartamento INT,
    FOREIGN KEY (ubdepartamento) REFERENCES ubdepartamento(id)
);

CREATE TABLE ubdistrito(
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    distrito VARCHAR(250),
    ubprovincia INT,
    FOREIGN KEY (ubprovincia) REFERENCES ubprovincia(id)
);

-- =====================================================
-- 2. TABLAS DE ESTUDIANTES Y MATRÍCULAS
-- =====================================================

CREATE TABLE prog_estudios(
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    nom_progest VARCHAR(40),
    perfilingre_progest TEXT,
    perfilegre_progest TEXT
);

CREATE TABLE estudiante(
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    ubdistrito INT,
    dni_est CHAR(8),
    ap_est VARCHAR(40),
    am_est VARCHAR(40),
    nom_est VARCHAR(40),
    sex_est CHAR(1),
    cel_est CHAR(9),
    ubigeodir_est CHAR(6),
    ubigeonac_est CHAR(6),
    dir_est VARCHAR(40),
    mailp_est VARCHAR(40),
    maili_est VARCHAR(40),
    fecnac_est DATE,
    foto_est VARCHAR(40),
    estado INT,
    FOREIGN KEY (ubdistrito) REFERENCES ubdistrito(id)
);

CREATE TABLE matricula(
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    estudiante INT,
    prog_estudios INT,
    id_matricula CHAR(9),
    per_lectivo VARCHAR(7),
    per_acad VARCHAR(3),
    per_acad2 INT(1),
    seccion CHAR(1),
    turno CHAR(1),
    fec_matricula DATE,
    cond_matricula CHAR(1),
    est_matricula CHAR(1),
    est_perlec CHAR(1),
    obs_matricula VARCHAR(50),
    FOREIGN KEY (estudiante) REFERENCES estudiante(id),
    FOREIGN KEY (prog_estudios) REFERENCES prog_estudios(id)
);

-- =====================================================
-- 3. TABLAS DE EMPLEADOS Y USUARIOS
-- =====================================================

CREATE TABLE empleado(
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    prog_estudios INT,
    dni_emp CHAR(8),
    apnom_emp VARCHAR(60),
    sex_emp CHAR(1),
    cel_emp CHAR(9),
    ubigeodir_emp CHAR(6),
    ubigeonac_emp CHAR(6),
    dir_emp VARCHAR(40),
    mailp_emp VARCHAR(40),
    maili_emp VARCHAR(40),
    fecnac_emp DATE,
    cargo_emp CHAR(1),
    cond_emp CHAR(1),
    id_progest CHAR(3),
    fecinc_emp DATE,
    foto_emp VARCHAR(40),
    estado INT,
    FOREIGN KEY (prog_estudios) REFERENCES prog_estudios(id)
);

CREATE TABLE usuarios(
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    usuario VARCHAR(200),
    password TEXT,
    tipo INT, -- 1 ES EMPLEADO, 2 ES ESTUDIANTE, 3 ES EMPRESA
    estuempleado INT,
    token TEXT
);

-- =====================================================
-- 4. SISTEMA DE PAGOS Y DESCUENTOS (EN USO)
-- =====================================================

CREATE TABLE resoluciones(
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    numero_resolucion VARCHAR(50) NOT NULL UNIQUE,
    titulo VARCHAR(255) NOT NULL,
    texto_respaldo TEXT,
    ruta_documento VARCHAR(255),
    fecha_inicio DATE,
    fecha_fin DATE,
    creado_por INT,
    creado_en DATETIME,
    FOREIGN KEY (creado_por) REFERENCES empleado(id)
);

-- Tabla SOLICITUD (singular) - Es la que se usa en el código
CREATE TABLE solicitud(
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(255),
    telefono VARCHAR(20),
    correo VARCHAR(100),
    tipo_solicitud VARCHAR(100) NOT NULL,
    descripcion TEXT,
    estado ENUM('Pendiente','En evaluación','Aprobado','Rechazado') DEFAULT 'Pendiente',
    archivos TEXT,
    fecha DATE,
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_respuesta DATETIME,
    motivo_respuesta TEXT,
    empleado_id INT,
    notificacion_enviada TINYINT(1) DEFAULT 0,
    FOREIGN KEY (empleado_id) REFERENCES empleado(id)
);

CREATE TABLE historial_solicitudes(
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    solicitud_id INT NOT NULL,
    estado VARCHAR(50) NOT NULL,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    empleado_id INT,
    comentarios TEXT,
    FOREIGN KEY (solicitud_id) REFERENCES solicitud(id),
    FOREIGN KEY (empleado_id) REFERENCES empleado(id)
);

CREATE TABLE beneficiarios(
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    estudiante INT NOT NULL,
    resoluciones INT NOT NULL,
    porcentaje_descuento DECIMAL(5,2) NOT NULL,
    fecha_inicio DATE,
    fecha_fin DATE,
    activo BOOLEAN DEFAULT TRUE,
    registrado_por INT,
    registrado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (estudiante) REFERENCES estudiante(id),
    FOREIGN KEY (resoluciones) REFERENCES resoluciones(id),
    FOREIGN KEY (registrado_por) REFERENCES empleado(id)
);

CREATE TABLE tipo_pago(
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    descripcion VARCHAR(150)
);

-- =====================================================
-- 5. SISTEMA DE NOTIFICACIONES (EN USO)
-- =====================================================

-- Tabla notificaciones - Usada en actualizarEstadoSolicitud.php
CREATE TABLE notificaciones(
    id INT AUTO_INCREMENT PRIMARY KEY,
    solicitud_id INT NOT NULL,
    usuario_id INT,
    mensaje TEXT NOT NULL,
    tipo ENUM('Aprobado','Rechazado','Aviso') DEFAULT 'Aviso',
    leido TINYINT(1) DEFAULT 0,
    creado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (solicitud_id) REFERENCES solicitud(id)
);

-- Tabla notificaciones_sistema - Usada en NotificacionModel.php
CREATE TABLE notificaciones_sistema(
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    usuario_nombre VARCHAR(255),
    tipo VARCHAR(50) NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    mensaje TEXT NOT NULL,
    modulo VARCHAR(100),
    accion VARCHAR(50),
    referencia_id INT,
    leida TINYINT(1) DEFAULT 0,
    creado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_usuario (usuario_id),
    INDEX idx_leida (leida),
    INDEX idx_creado (creado_en)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- FIN DEL SCRIPT
-- Total de tablas en uso: 14 tablas
-- =====================================================

/*
RESUMEN DE TABLAS EN USO:

1. Ubigeo (3 tablas):
   - ubdepartamento
   - ubprovincia
   - ubdistrito

2. Estudiantes (3 tablas):
   - prog_estudios
   - estudiante
   - matricula

3. Empleados y Usuarios (2 tablas):
   - empleado
   - usuarios

4. Sistema de Pagos y Descuentos (4 tablas):
   - resoluciones
   - solicitud (singular - esta es la que se usa)
   - historial_solicitudes
   - beneficiarios
   - tipo_pago

5. Notificaciones (2 tablas):
   - notificaciones
   - notificaciones_sistema

NOTAS IMPORTANTES:
- El sistema usa "solicitud" (singular), NO "solicitudes" (plural)
- El sistema usa "usuarios" (plural), NO "usuario" (singular)
- El sistema usa "estudiante" (singular), NO "estudiantes" (plural)
- Hay dos tablas de notificaciones: "notificaciones" y "notificaciones_sistema"
- La tabla "pagos" no se está usando actualmente en los controladores
*/

