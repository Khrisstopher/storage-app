-- phpMyAdmin SQL Dump
-- Base de datos: `storage_app`

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `blocked_extensions`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `blocked_extensions`;
CREATE TABLE `blocked_extensions` (
  `id` bigint(20) NOT NULL,
  `extension` varchar(10) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `quotas`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `quotas`;
CREATE TABLE `quotas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL COMMENT 'Ej: Free, Premium, Admin, Sistema Global',
  `quota_bytes` bigint(20) UNSIGNED NOT NULL COMMENT 'Límite máximo en bytes',
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `settings`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `quota_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `roles`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` bigint(20) NOT NULL,
  `name` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `groups`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `groups`;
CREATE TABLE `groups` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `quota_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `users`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint(20) NOT NULL,
  `external_id` char(36) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `group_id` bigint(20) UNSIGNED DEFAULT NULL,
  `quota_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `files`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `files`;
CREATE TABLE `files` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `filename` varchar(255) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `file_size` int(11) NOT NULL,
  `file_type` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ==============================================================================
-- LLAVES PRIMARIAS, ÍNDICES Y AUTO_INCREMENTS
-- ==============================================================================

ALTER TABLE `blocked_extensions` ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `extension` (`extension`);
ALTER TABLE `files` ADD PRIMARY KEY (`id`);
ALTER TABLE `groups` ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `name` (`name`);
ALTER TABLE `quotas` ADD PRIMARY KEY (`id`);
ALTER TABLE `roles` ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `name` (`name`);
ALTER TABLE `settings` ADD PRIMARY KEY (`id`);
ALTER TABLE `users` ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `email` (`email`), ADD UNIQUE KEY `external_id_unique` (`external_id`), ADD KEY `fk_users_groups` (`group_id`);

ALTER TABLE `blocked_extensions` MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;
ALTER TABLE `files` MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `groups` MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `quotas` MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `roles` MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;
ALTER TABLE `settings` MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `users` MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

ALTER TABLE `users` ADD CONSTRAINT `fk_users_groups` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;


-- ==============================================================================
-- INSERCIÓN DE DATOS INICIALES DEL SISTEMA
-- ==============================================================================

-- 1. Insertar Roles del sistema (Garantiza Admin = ID 1, User = ID 2)
INSERT INTO `roles` (`id`, `name`) VALUES
(1, 'admin'),
(2, 'user');

-- 2. Crear la cuota global inicial por defecto (ej: 50 MB = 52428800 bytes)
INSERT INTO `quotas` (`id`, `name`, `quota_bytes`, `description`) 
VALUES (1, 'Sistema Global Inicial', 52428800, 'Cuota por defecto autogenerada en la instalacion');

-- 3. Crear la fila única de configuraciones apuntando a esa cuota
INSERT INTO `settings` (`id`, `quota_id`) 
VALUES (1, 1);

-- 4. Crear el usuario Administrador Maestro para poder iniciar sesión por primera vez
-- Correo: admin@test.com  |  Contraseña: password
INSERT INTO `users` (`id`, `external_id`, `name`, `email`, `password`, `role_id`, `group_id`, `quota_id`) 
VALUES (
  1, 
  '0ca08814c9214b33110fc5e13c60dc7f', 
  'Administrador', 
  'admin@test.com', 
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
  1, -- ID del rol admin
  NULL, 
  NULL
);

COMMIT;