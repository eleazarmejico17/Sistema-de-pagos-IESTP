-- =====================================================
-- INSERTAR CONCEPTOS DE PAGO CON VALORES UIT
-- =====================================================

USE db_sistema;

-- Verificar y agregar campo UIT si no existe
-- NOTA: Si el campo ya existe, esta línea dará error, pero puedes comentarla o ejecutarla manualmente
-- ALTER TABLE tipo_pago ADD COLUMN uit DECIMAL(10,2) DEFAULT 0.00 AFTER descripcion;

-- Limpiar datos existentes (opcional - comentar si no deseas eliminar)
-- TRUNCATE TABLE tipo_pago;

-- Insertar todos los conceptos de pago con sus valores UIT
INSERT INTO tipo_pago (nombre, descripcion, uit) VALUES
('1.1', 'Carnet de Medio Pasaje', 18.00),
('1.2', 'Duplicado de carnet', 18.00),
('3.1', 'Inscripción del postulante modalidad ordinario', 205.00),
('3.2', 'Inscripción del postulante modalidad exonerados', 205.00),
('3.3', 'Inscripción del postulante modalidad por convenio de Transitabilidad', 100.00),
('4.1', 'Trámite de Traslado Interno', 8.00),
('4.2', 'Trámite de Traslado de Turno', 8.00),
('4.3', 'Trámite de Traslado Externo', 8.00),
('5.1', 'Ratificación de matrícula', 172.00),
('5.2', 'Matrícula Ingresantes', 220.00),
('5.3', 'Matrícula de ingresantes por exoneración', 220.00),
('5.4', 'Matrícula Traslado de Turno', 288.00),
('5.5', 'Matrícula Traslado Interno', 288.00),
('5.6', 'Matrícula Traslado Externo', 515.00),
('6.1', 'Trámite de matrícula extemporánea', 8.00),
('6.2', 'Matrícula extemporánea', 233.00),
('6.3', 'Reserva de matrícula por procesos', 110.00),
('7.1', 'Convalidación interna por semestre', 61.00),
('7.2', 'Convalidación externa por semestre', 61.00),
('8.1', 'Trámite de repitencia de semestre', 8.00),
('8.2', 'Matrícula de repitencia de semestre', 343.00),
('9.1', 'Trámite de Reingreso', 8.00),
('9.2', 'Matrícula de Reingreso', 282.00)
ON DUPLICATE KEY UPDATE 
    descripcion = VALUES(descripcion),
    uit = VALUES(uit);

