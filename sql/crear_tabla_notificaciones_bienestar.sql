-- Tabla para almacenar notificaciones específicas para el área de bienestar
CREATE TABLE IF NOT EXISTS `notificaciones_bienestar` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tipo` varchar(50) NOT NULL COMMENT 'Tipo de notificación: aprobacion, rechazo, info, etc.',
  `titulo` varchar(255) NOT NULL COMMENT 'Título de la notificación',
  `mensaje` text NOT NULL COMMENT 'Mensaje completo de la notificación',
  `id_resolucion` int(11) DEFAULT NULL COMMENT 'ID de la resolución relacionada (opcional)',
  `id_empleado_creador` int(11) DEFAULT NULL COMMENT 'ID del empleado que creó la resolución original',
  `id_empleado_revisor` int(11) DEFAULT NULL COMMENT 'ID del empleado de dirección que revisó',
  `estado_notificacion` varchar(20) NOT NULL DEFAULT 'no_leida' COMMENT 'Estado: no_leida, leida, archivada',
  `creado_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de creación de la notificación',
  `leido_en` datetime DEFAULT NULL COMMENT 'Fecha cuando fue leída',
  PRIMARY KEY (`id`),
  KEY `idx_tipo` (`tipo`),
  KEY `idx_estado` (`estado_notificacion`),
  KEY `idx_resolucion` (`id_resolucion`),
  KEY `idx_creador` (`id_empleado_creador`),
  KEY `idx_creado_en` (`creado_en`),
  CONSTRAINT `fk_notif_bien_resolucion` FOREIGN KEY (`id_resolucion`) REFERENCES `resoluciones` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_notif_bien_creador` FOREIGN KEY (`id_empleado_creador`) REFERENCES `empleado` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_notif_bien_revisor` FOREIGN KEY (`id_empleado_revisor`) REFERENCES `empleado` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci COMMENT='Notificaciones específicas para el área de bienestar';

-- Insertar algunos datos de ejemplo (opcional)
INSERT INTO `notificaciones_bienestar` (`tipo`, `titulo`, `mensaje`, `id_resolucion`, `id_empleado_creador`, `estado_notificacion`) VALUES
('info', 'Sistema de Notificaciones Activo', 'El sistema de notificaciones para bienestar ha sido activado correctamente.', NULL, NULL, 'leida');
