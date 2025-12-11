-- =====================================================
-- SISTEMA DE PAGOS IESTP - TODAS LAS TABLAS
-- Base de datos completa con todas las tablas del sistema
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

CREATE TABLE unidades_didacticas(
    id INT AUTO_INCREMENT PRIMARY KEY NOT NULL,
    nombre_unidad VARCHAR(100) NOT NULL,
    descripcion TEXT,
    creditos INT NOT NULL CHECK (creditos > 0)
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
-- 4. SISTEMA DE CONVENIOS
-- =====================================================

CREATE TABLE instituto(
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    ruc VARCHAR(11),
    razon_social VARCHAR(255),
    nombre_comercial VARCHAR(255),
    direccion TEXT,
    telefono VARCHAR(20),
    email VARCHAR(100),
    logo VARCHAR(500),
    activa INT
);

CREATE TABLE empresa(
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    ruc VARCHAR(11),
    razon_social VARCHAR(255),
    nombre_comercial VARCHAR(255),
    direccion_fiscal TEXT,
    telefono VARCHAR(20),
    email VARCHAR(100),
    sector VARCHAR(100),
    validado INT,
    registro_manual INT,
    estado VARCHAR(50),
    condicion_sunat VARCHAR(20),
    ubigeo VARCHAR(10),
    departamento VARCHAR(100),
    provincia VARCHAR(100),
    distrito VARCHAR(100),
    fecha_creacion DATETIME,
    fecha_actualizacion DATETIME
);

CREATE TABLE cache_sunat(
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    ruc VARCHAR(11),
    razon_social VARCHAR(255),
    nombre_comercial VARCHAR(255),
    direccion_fiscal TEXT,
    departamento VARCHAR(100),
    provincia VARCHAR(100),
    distrito VARCHAR(100),
    ubigeo VARCHAR(6),
    estado VARCHAR(50),
    condicion VARCHAR(50),
    representante_legal VARCHAR(255),
    representante_cargo VARCHAR(150),
    representante_desde DATE,
    telefono VARCHAR(20),
    fecha_inscripcion DATE,
    fecha_inicio_actividades DATE,
    fecha_consulta DATETIME,
    fecha_expiracion DATETIME
);

CREATE TABLE representante(
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    instituto INT,
    empresa INT,
    nombre_completo VARCHAR(255),
    cargo VARCHAR(100),
    documento VARCHAR(20),
    tipo_documento VARCHAR(20),
    email VARCHAR(100),
    telefono VARCHAR(20),
    puede_firmar INT,
    fecha_desde DATE,
    fecha_hasta DATE,
    activo INT,
    FOREIGN KEY (instituto) REFERENCES instituto(id),
    FOREIGN KEY (empresa) REFERENCES empresa(id)
);

CREATE TABLE tipo_convenio(
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(100),
    descripcion TEXT,
    plantilla VARCHAR(500),
    requiere_coord_academica INT,
    activa INT
);

CREATE TABLE estado_convenio(
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(100),
    descripcion TEXT,
    color VARCHAR(20),
    orden INT
);

CREATE TABLE convenio(
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    instituto INT,
    empresa INT,
    tipo INT,
    estado INT,
    codigo VARCHAR(50),
    iniciado_por VARCHAR(20),
    objetivo TEXT,
    beneficios_instituto TEXT,
    beneficios_empresa TEXT,
    duracion_meses INT,
    fecha_inicio DATE,
    fecha_vencimiento DATE,
    renovacion_automatica INT,
    condiciones_especiales TEXT,
    archivo_fisico VARCHAR(255),
    qr VARCHAR(500),
    fecha_creacion DATETIME,
    fecha_activacion DATETIME,
    FOREIGN KEY (instituto) REFERENCES instituto(id),
    FOREIGN KEY (empresa) REFERENCES empresa(id),
    FOREIGN KEY (tipo) REFERENCES tipo_convenio(id),
    FOREIGN KEY (estado) REFERENCES estado_convenio(id)
);

CREATE TABLE comunicacion(
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    convenio INT,
    tipo VARCHAR(50),
    asunto VARCHAR(255),
    contenido TEXT,
    enviado_por INT,
    fecha_envio DATETIME,
    fecha_respuesta DATETIME,
    respondido INT,
    archivo VARCHAR(500),
    tipouser INT, /* 1 es institución, 2 es empresa */
    FOREIGN KEY (convenio) REFERENCES convenio(id)
);

CREATE TABLE reunion(
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    convenio INT,
    fecha DATETIME,
    duracion INT,
    modalidad VARCHAR(20),
    link_virtual VARCHAR(500),
    lugar VARCHAR(255),
    estado VARCHAR(20),
    acta VARCHAR(500),
    propuesta_por INT,
    fecha_propuesta DATETIME,
    fecha_confirmacion DATETIME,
    tipouser INT, /* 1 es institución, 2 es empresa */
    FOREIGN KEY (convenio) REFERENCES convenio(id)
);

CREATE TABLE participante_reunion(
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    reunion INT,
    usuario INT,
    rol VARCHAR(20),
    confirmo INT,
    asistio INT,
    tipouser INT, /* 1 es institución, 2 es empresa */
    FOREIGN KEY (reunion) REFERENCES reunion(id)
);

CREATE TABLE invitado_reunion(
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    reunion INT,
    nombre_completo VARCHAR(255),
    cargo VARCHAR(100),
    entidad VARCHAR(255),
    email VARCHAR(100),
    telefono VARCHAR(20),
    asistio INT,
    FOREIGN KEY (reunion) REFERENCES reunion(id)
);

CREATE TABLE tipo_documento(
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(100),
    descripcion TEXT,
    plantilla VARCHAR(500),
    requiere_firma INT
);

CREATE TABLE documento(
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    convenio INT,
    reunion INT,
    tipo INT,
    nombre VARCHAR(255),
    descripcion TEXT,
    archivo VARCHAR(500),
    version INT,
    tamano BIGINT,
    hash VARCHAR(64),
    firmado_instituto INT,
    firmado_empresa INT,
    FOREIGN KEY (convenio) REFERENCES convenio(id),
    FOREIGN KEY (reunion) REFERENCES reunion(id),
    FOREIGN KEY (tipo) REFERENCES tipo_documento(id)
);

CREATE TABLE firma(
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    documento INT,
    usuario INT,
    representante INT,
    tipo VARCHAR(20),
    fecha DATETIME,
    ip VARCHAR(45),
    metodo VARCHAR(30),
    hash_documento VARCHAR(64),
    observaciones TEXT,
    tipouser INT, /* 1 es institución, 2 es empresa */
    FOREIGN KEY (documento) REFERENCES documento(id),
    FOREIGN KEY (representante) REFERENCES representante(id)
);

CREATE TABLE nivel_alerta(
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(20),
    descripcion TEXT,
    color VARCHAR(10),
    bloquea INT,
    prioridad INT
);

CREATE TABLE alerta_ia(
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    documento INT,
    convenio INT,
    nivel INT,
    titulo VARCHAR(255),
    descripcion TEXT,
    clausula TEXT,
    sugerencia TEXT,
    fecha DATETIME,
    resuelta INT,
    resuelto_por INT,
    tipouser INT, /* 1 es institución, 2 es empresa */
    FOREIGN KEY (documento) REFERENCES documento(id),
    FOREIGN KEY (convenio) REFERENCES convenio(id),
    FOREIGN KEY (nivel) REFERENCES nivel_alerta(id)
);

CREATE TABLE tipo_renovacion(
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(50),
    descripcion TEXT,
    requiere_firma INT,
    duracion_dias INT
);

CREATE TABLE estado_renovacion(
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(50),
    descripcion TEXT
);

CREATE TABLE renovacion(
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    convenio INT,
    tipo INT,
    estado INT,
    iniciado_por VARCHAR(20),
    fecha_inicio DATETIME,
    fecha_aprobacion DATETIME,
    meses_extension INT,
    requiere_reunion INT,
    observaciones TEXT,
    FOREIGN KEY (convenio) REFERENCES convenio(id),
    FOREIGN KEY (tipo) REFERENCES tipo_renovacion(id),
    FOREIGN KEY (estado) REFERENCES estado_renovacion(id)
);

CREATE TABLE notificacion(
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    usuario INT,
    convenio INT,
    comunicacion INT,
    tipo VARCHAR(20),
    prioridad VARCHAR(20),
    titulo VARCHAR(255),
    mensaje TEXT,
    leida INT,
    fecha_envio DATETIME,
    email_enviado INT,
    tipouser INT, /* 1 es institución, 2 es empresa */
    FOREIGN KEY (convenio) REFERENCES convenio(id),
    FOREIGN KEY (comunicacion) REFERENCES comunicacion(id)
);

CREATE TABLE tipo_recordatorio(
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(50),
    descripcion TEXT
);

CREATE TABLE recordatorio(
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    notificacion INT,
    tipo INT,
    evento VARCHAR(50),
    dias_antes INT,
    fecha_programada DATETIME,
    enviado INT,
    FOREIGN KEY (notificacion) REFERENCES notificacion(id),
    FOREIGN KEY (tipo) REFERENCES tipo_recordatorio(id)
);

CREATE TABLE auditoria(
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    usuario INT,
    convenio INT,
    accion VARCHAR(20),
    tabla VARCHAR(100),
    registro_id INT,
    datos_anteriores TEXT,
    datos_nuevos TEXT,
    ip VARCHAR(45),
    user_agent VARCHAR(255),
    fecha DATETIME,
    descripcion TEXT,
    tipouser INT, /* 1 es institución, 2 es empresa */
    FOREIGN KEY (convenio) REFERENCES convenio(id)
);

-- =====================================================
-- 5. SISTEMA DE SEGUIMIENTOS DE EGRESADO
-- =====================================================

CREATE TABLE condicion_academica(
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    nombre_condicion VARCHAR(50)
);

CREATE TABLE condicion_laboral(
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    nombre_condicion VARCHAR(100)
);

CREATE TABLE sector(
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    nombre_sector VARCHAR(100)
);

CREATE TABLE medio_consecucion(
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    nombre_medio VARCHAR(100)
);

CREATE TABLE situacion_laboral(
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    estudiante INT,
    empresa INT,
    trabaja INT,
    labora_programa_estudios INT,
    cargo_actual VARCHAR(200),
    condicion_laboral INT,
    ingreso_bruto_mensual DECIMAL(10,2),
    satisfaccion_trabajo VARCHAR(50),
    fecha_inicio DATE,
    FOREIGN KEY (estudiante) REFERENCES estudiante(id),
    FOREIGN KEY (empresa) REFERENCES empresa(id),
    FOREIGN KEY (condicion_laboral) REFERENCES condicion_laboral(id)
);

CREATE TABLE historial_laboral(
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    situacion INT,
    tiempo_primer_empleo VARCHAR(30),
    razon_desempleo VARCHAR(30),
    fecha_inicio DATE,
    fecha_fin DATE,
    fecha_registro DATETIME,
    FOREIGN KEY (situacion) REFERENCES situacion_laboral(id)
);

CREATE TABLE situacion_medio(
    situacion INT,
    medio INT,
    PRIMARY KEY (situacion, medio),
    FOREIGN KEY (situacion) REFERENCES situacion_laboral(id),
    FOREIGN KEY (medio) REFERENCES medio_consecucion(id)
);

CREATE TABLE tipo_seguimiento(
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    nombre_tipo VARCHAR(100),
    descripcion VARCHAR(200)
);

CREATE TABLE seguimiento(
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    estudiante INT,
    tipo INT,
    observaciones TEXT,
    fecha DATE,
    FOREIGN KEY (estudiante) REFERENCES estudiante(id),
    FOREIGN KEY (tipo) REFERENCES tipo_seguimiento(id)
);

-- =====================================================
-- 6. SISTEMA DE EXPERIENCIAS FORMATIVAS
-- =====================================================

CREATE TABLE practicas(
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    estudiante INT NOT NULL,
    empleado INT,
    empresa INT,
    modulo VARCHAR(100),
    periodo_academico VARCHAR(50),
    fecha_inicio DATE,
    fecha_fin DATE,
    total_horas INT DEFAULT 0,
    estado ENUM('En curso','Finalizado','Pendiente') DEFAULT 'Pendiente',
    FOREIGN KEY (estudiante) REFERENCES estudiante(id) ON DELETE CASCADE,
    FOREIGN KEY (empleado) REFERENCES empleado(id) ON DELETE SET NULL,
    FOREIGN KEY (empresa) REFERENCES empresa(id) ON DELETE SET NULL
);

CREATE TABLE asistencias(
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    practicas INT NOT NULL,
    fecha DATE NOT NULL,
    hora_entrada TIME,
    hora_salida TIME,
    horas_acumuladas INT,
    actividad TEXT,
    visto_bueno_empresa VARCHAR(150),
    visto_bueno_docente VARCHAR(150),
    FOREIGN KEY (practicas) REFERENCES practicas(id) ON DELETE CASCADE
);

CREATE TABLE evaluaciones(
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    practicas INT NOT NULL,
    puntaje_total INT,
    escala CHAR(1),
    apreciacion ENUM('Muy Buena','Buena','Aceptable','Deficiente'),
    observaciones TEXT,
    FOREIGN KEY (practicas) REFERENCES practicas(id) ON DELETE CASCADE
);

CREATE TABLE evidencias(
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    practicas INT NOT NULL,
    descripcion VARCHAR(255),
    archivo_url VARCHAR(255),
    fecha_subida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (practicas) REFERENCES practicas(id) ON DELETE CASCADE
);

-- =====================================================
-- 7. SISTEMA DE PAGOS Y DESCUENTOS
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

CREATE TABLE solicitudes(
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    estudiante INT NOT NULL,
    resoluciones INT,
    tipo_solicitud VARCHAR(100) NOT NULL,
    descripcion TEXT,
    estado ENUM('pendiente','en_evaluacion','aprobado','rechazado') DEFAULT 'pendiente',
    fecha_solicitud DATETIME,
    fecha_revision DATETIME,
    empleado INT,
    observaciones TEXT,
    foto TEXT,
    FOREIGN KEY (estudiante) REFERENCES estudiante(id),
    FOREIGN KEY (resoluciones) REFERENCES resoluciones(id),
    FOREIGN KEY (empleado) REFERENCES empleado(id)
);

CREATE TABLE historial_solicitudes(
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    solicitud_id INT NOT NULL,
    estado VARCHAR(50) NOT NULL,
    fecha DATETIME,
    empleado INT,
    comentarios TEXT,
    FOREIGN KEY (solicitud_id) REFERENCES solicitudes(id),
    FOREIGN KEY (empleado) REFERENCES empleado(id)
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
    registrado_en DATETIME,
    FOREIGN KEY (estudiante) REFERENCES estudiante(id),
    FOREIGN KEY (resoluciones) REFERENCES resoluciones(id),
    FOREIGN KEY (registrado_por) REFERENCES empleado(id)
);

CREATE TABLE historial_descuentos(
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    beneficiario_id INT NOT NULL,
    monto_original DECIMAL(10,2) NOT NULL,
    porcentaje_descuento DECIMAL(5,2) NOT NULL,
    monto_descuento DECIMAL(10,2) NOT NULL,
    monto_final DECIMAL(10,2) NOT NULL,
    aplicado_por INT,
    aplicado_en DATETIME,
    observaciones VARCHAR(255),
    FOREIGN KEY (beneficiario_id) REFERENCES beneficiarios(id),
    FOREIGN KEY (aplicado_por) REFERENCES empleado(id)
);

CREATE TABLE tipo_pago(
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    descripcion VARCHAR(150)
);

CREATE TABLE pagos(
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    estudiante INT NOT NULL,
    solicitudes INT,
    tipo_pago INT NOT NULL,
    monto_original DECIMAL(10,2) NOT NULL,
    monto_descuento DECIMAL(10,2) DEFAULT 0.00,
    monto_final DECIMAL(10,2) NOT NULL,
    fecha_pago DATETIME,
    comprobante VARCHAR(255),
    registrado_por INT,
    registrado_en DATETIME,
    FOREIGN KEY (estudiante) REFERENCES estudiante(id),
    FOREIGN KEY (solicitudes) REFERENCES solicitudes(id),
    FOREIGN KEY (tipo_pago) REFERENCES tipo_pago(id),
    FOREIGN KEY (registrado_por) REFERENCES empleado(id)
);

-- =====================================================
-- 8. SISTEMA DE CAPACITACIÓN
-- =====================================================

CREATE TABLE tipo_curso(
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom_tipocurso VARCHAR(50)
);

CREATE TABLE asistencia_cap(
    id INT PRIMARY KEY AUTO_INCREMENT,
    asistencia BOOLEAN,
    certificacion BOOLEAN,
    certificado_archivo VARCHAR(100)
);

CREATE TABLE curso(
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom_curso VARCHAR(60) NOT NULL,
    modalidad_curso ENUM('presencial', 'virtual') NOT NULL,
    descripcion_curso TEXT,
    fechini_curso DATE,
    fechfin_curso DATE,
    hora_curso VARCHAR(50),
    lugar_curso VARCHAR(100),
    estado_curso ENUM('vigente', 'finalizado') DEFAULT 'vigente',
    organizador_curso VARCHAR(100),
    certificacion_curso BOOLEAN DEFAULT FALSE,
    duracion_curso VARCHAR(50),
    costo_curso DECIMAL(5,2),
    creditos_curso CHAR(3),
    foto_curso VARCHAR(255),
    tipo_curso INT,
    creado_por INT,
    fecha_creacion DATETIME,
    fecha_modificacion DATETIME,
    FOREIGN KEY (tipo_curso) REFERENCES tipo_curso(id),
    FOREIGN KEY (creado_por) REFERENCES usuarios(id)
);

CREATE TABLE inscripcion(
    id INT PRIMARY KEY AUTO_INCREMENT,
    noms_ins VARCHAR(50),
    apaterno_ins VARCHAR(50),
    amaterno_ins VARCHAR(50),
    dni_ins CHAR(8),
    telefono_ins CHAR(9),
    correo_ins VARCHAR(50),
    institucion_ins VARCHAR(100),
    lugar_ins VARCHAR(100),
    asistencia_cap INT,
    curso INT,
    tipo INT,
    FOREIGN KEY (asistencia_cap) REFERENCES asistencia_cap(id),
    FOREIGN KEY (curso) REFERENCES curso(id)
);

CREATE TABLE pago_cap(
    id INT PRIMARY KEY AUTO_INCREMENT,
    monto_pago DECIMAL(5,2),
    fecha_pago DATE,
    estado_pago ENUM('porconfirmar', 'confirmado'),
    inscripcion INT,
    FOREIGN KEY (inscripcion) REFERENCES inscripcion(id)
);

CREATE TABLE auditoria_cap(
    id INT PRIMARY KEY AUTO_INCREMENT,
    tabla_afectada VARCHAR(50),
    id_registro_afectado INT,
    accion ENUM('insert', 'update', 'delete'),
    fecha_accion DATETIME,
    id_user INT,
    descripcion TEXT,
    tipo INT,
    FOREIGN KEY (id_user) REFERENCES usuarios(id)
);

-- =====================================================
-- FIN DEL SCRIPT
-- Total de tablas creadas: 58 tablas
-- =====================================================

