-- Crear base de datos
CREATE DATABASE IF NOT EXISTS pago_descuentos;
USE pago_descuentos;

-- Tabla Roles
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL
);

-- Tabla Usuarios
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(100) UNIQUE NOT NULL,
    nombre_completo VARCHAR(200) NOT NULL,
    correo VARCHAR(150) UNIQUE NOT NULL,
    contrasena_hash VARCHAR(255) NOT NULL,
    rol_id INT,
    creado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
    actualizado_en DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (rol_id) REFERENCES roles(id)
);

-- Tabla Estudiantes
CREATE TABLE estudiantes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNIQUE,
    codigo_estudiante VARCHAR(50) UNIQUE NOT NULL,
    dni VARCHAR(15) UNIQUE NOT NULL,
    carrera VARCHAR(150) NOT NULL,
    ciclo INT NOT NULL,
    fecha_ingreso DATE NOT NULL,
    creado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
    actualizado_en DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- Tabla Resoluciones
CREATE TABLE resoluciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_resolucion VARCHAR(50) UNIQUE NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    texto_respaldo TEXT,
    ruta_documento VARCHAR(255),
    fecha_inicio DATE,
    fecha_fin DATE,
    creado_por INT,
    creado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (creado_por) REFERENCES usuarios(id)
);

-- Tabla Solicitudes
CREATE TABLE solicitudes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    estudiante_id INT NOT NULL,
    resolucion_id INT,
    tipo_solicitud VARCHAR(100) NOT NULL,
    descripcion TEXT,
    estado ENUM('pendiente', 'en_evaluacion', 'aprobado', 'rechazado') DEFAULT 'pendiente',
    fecha_solicitud DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_revision DATETIME,
    revisado_por INT,
    observaciones TEXT,
    foto TEXT,
    FOREIGN KEY (estudiante_id) REFERENCES estudiantes(id),
    FOREIGN KEY (resolucion_id) REFERENCES resoluciones(id),
    FOREIGN KEY (revisado_por) REFERENCES usuarios(id)
);

-- Tabla Historial Solicitudes
CREATE TABLE historial_solicitudes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    solicitud_id INT NOT NULL,
    estado VARCHAR(50) NOT NULL,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    usuario_id INT,
    comentarios TEXT,
    FOREIGN KEY (solicitud_id) REFERENCES solicitudes(id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- Tabla Beneficiarios
CREATE TABLE beneficiarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    estudiante_id INT NOT NULL,
    resolucion_id INT NOT NULL,
    porcentaje_descuento DECIMAL(5,2) NOT NULL,
    fecha_inicio DATE,
    fecha_fin DATE,
    activo BOOLEAN DEFAULT TRUE,
    registrado_por INT,
    registrado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (estudiante_id) REFERENCES estudiantes(id),
    FOREIGN KEY (resolucion_id) REFERENCES resoluciones(id),
    FOREIGN KEY (registrado_por) REFERENCES usuarios(id)
);

-- Tabla Historial Descuentos
CREATE TABLE historial_descuentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    beneficiario_id INT NOT NULL,
    monto_original DECIMAL(10,2) NOT NULL,
    porcentaje_descuento DECIMAL(5,2) NOT NULL,
    monto_descuento DECIMAL(10,2) NOT NULL,
    monto_final DECIMAL(10,2) NOT NULL,
    aplicado_por INT,
    aplicado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
    observaciones VARCHAR(255),
    FOREIGN KEY (beneficiario_id) REFERENCES beneficiarios(id),
    FOREIGN KEY (aplicado_por) REFERENCES usuarios(id)
);

-- Tabla Tipo Pago
CREATE TABLE tipo_pago (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) UNIQUE NOT NULL, -- Yape, Tarjeta, Otros
    descripcion VARCHAR(150)
);

-- Tabla Pagos
CREATE TABLE pagos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    estudiante_id INT NOT NULL,
    solicitud_id INT,
    tipo_pago_id INT NOT NULL,
    monto_original DECIMAL(10,2) NOT NULL,
    monto_descuento DECIMAL(10,2) DEFAULT 0.00,
    monto_final DECIMAL(10,2) NOT NULL,
    fecha_pago DATETIME DEFAULT CURRENT_TIMESTAMP,
    comprobante VARCHAR(255),
    registrado_por INT,
    registrado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (estudiante_id) REFERENCES estudiantes(id),
    FOREIGN KEY (solicitud_id) REFERENCES solicitudes(id),
    FOREIGN KEY (tipo_pago_id) REFERENCES tipo_pago(id),
    FOREIGN KEY (registrado_por) REFERENCES usuarios(id)
);

-- Tabla Auditoría
CREATE TABLE auditoria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    accion VARCHAR(100) NOT NULL,
    entidad VARCHAR(100) NOT NULL,
    entidad_id VARCHAR(50),
    detalles TEXT,
    creado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);
