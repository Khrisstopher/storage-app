-- 1. Creamos la cuota global inicial con un límite por defecto (ej: 500 MB)
INSERT INTO `quotas` (`id`, `name`, `quota_bytes`, `description`) 
VALUES (1, 'Sistema Global Inicial', 524288000, 'Cuota por defecto autogenerada en la instalacion');

-- 2. Creamos la fila de configuración apuntando a esa cuota
INSERT INTO `settings` (`id`, `quota_id`) 
VALUES (1, 1);