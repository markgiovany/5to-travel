-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 04-04-2026 a las 21:43:24
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `travel_marketing`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `catalogo`
--

CREATE TABLE `catalogo` (
  `id_catalogo` int(11) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `precio` decimal(10,2) DEFAULT NULL,
  `disponibilidad` int(11) DEFAULT NULL,
  `id_tipo` int(11) DEFAULT NULL,
  `id_grupo` int(11) DEFAULT NULL,
  `id_ubicacion` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cat_catalogo_habitacion`
--

CREATE TABLE `cat_catalogo_habitacion` (
  `id_catalogo` int(11) NOT NULL,
  `nombre_tipo` varchar(100) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `capacidad` int(11) DEFAULT NULL,
  `nombre` varchar(150) DEFAULT NULL,
  `precio` decimal(10,2) DEFAULT NULL,
  `disponibilidad` int(11) DEFAULT NULL,
  `id_tipo` int(11) DEFAULT NULL,
  `id_grupo` int(11) DEFAULT NULL,
  `id_ubicacion` int(11) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cat_ciudad`
--

CREATE TABLE `cat_ciudad` (
  `id_ciudad` int(11) NOT NULL,
  `nombre_ciudad` varchar(100) DEFAULT NULL,
  `id_estado` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cat_estado`
--

CREATE TABLE `cat_estado` (
  `id_estado` int(11) NOT NULL,
  `nombre_estado` varchar(100) DEFAULT NULL,
  `id_pais` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cat_grupo`
--

CREATE TABLE `cat_grupo` (
  `id_grupo` int(11) NOT NULL,
  `nombre_grupo` varchar(100) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cat_imagen`
--

CREATE TABLE `cat_imagen` (
  `id_imagen` int(11) NOT NULL,
  `id_catalogo` int(11) DEFAULT NULL,
  `url_imagen` varchar(255) DEFAULT NULL,
  `url_thumbnail` varchar(255) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cat_pais`
--

CREATE TABLE `cat_pais` (
  `id_pais` int(11) NOT NULL,
  `nombre_pais` varchar(100) DEFAULT NULL,
  `codigo_pais` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cat_tipo`
--

CREATE TABLE `cat_tipo` (
  `id_tipo` int(11) NOT NULL,
  `nombre_tipo` varchar(100) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cat_ubicacion`
--

CREATE TABLE `cat_ubicacion` (
  `id_ubicacion` int(11) NOT NULL,
  `direccion` varchar(200) DEFAULT NULL,
  `id_ciudad` int(11) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `metodos_pago`
--

CREATE TABLE `metodos_pago` (
  `id` int(11) NOT NULL,
  `user_id` varchar(100) DEFAULT NULL,
  `tipo` varchar(50) DEFAULT NULL,
  `numero` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reservas`
--

CREATE TABLE `reservas` (
  `id` int(11) NOT NULL,
  `user_id` varchar(100) DEFAULT NULL,
  `id_catalogo` int(11) DEFAULT NULL,
  `fecha` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `res_cancelacion`
--

CREATE TABLE `res_cancelacion` (
  `id_cancelacion` int(11) NOT NULL,
  `id_reserva` int(11) DEFAULT NULL,
  `motivo` text DEFAULT NULL,
  `fecha_cancelacion` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `res_control_estadia`
--

CREATE TABLE `res_control_estadia` (
  `id_estadia` int(11) NOT NULL,
  `id_reserva` int(11) DEFAULT NULL,
  `fecha_checkin` datetime DEFAULT NULL,
  `fecha_checkout` datetime DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `res_habitacion`
--

CREATE TABLE `res_habitacion` (
  `id_habitacion` int(11) NOT NULL,
  `numero` int(11) DEFAULT NULL,
  `id_catalogo` int(11) DEFAULT NULL,
  `precio` decimal(10,2) DEFAULT NULL,
  `estado` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `res_metodo_pago`
--

CREATE TABLE `res_metodo_pago` (
  `id_metodo_pago` int(11) NOT NULL,
  `nombre_metodo` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `res_registro_pago`
--

CREATE TABLE `res_registro_pago` (
  `id_pago` int(11) NOT NULL,
  `id_reserva` int(11) DEFAULT NULL,
  `id_metodo_pago` int(11) DEFAULT NULL,
  `monto` decimal(10,2) DEFAULT NULL,
  `estado_pago` varchar(20) DEFAULT NULL,
  `fecha_pago` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `res_reserva`
--

CREATE TABLE `res_reserva` (
  `id_reserva` int(11) NOT NULL,
  `user_uuid` char(36) DEFAULT NULL,
  `id_habitacion` int(11) DEFAULT NULL,
  `fecha_entrada` date DEFAULT NULL,
  `fecha_salida` date DEFAULT NULL,
  `estado` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usr_emails`
--

CREATE TABLE `usr_emails` (
  `id_email` bigint(20) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `user_uuid` char(36) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usr_emails`
--

INSERT INTO `usr_emails` (`id_email`, `email`, `user_uuid`) VALUES
(9, 'ingjuliosd@gmail.com', '676f80e6-305d-11f1-94cc-e89c25808c74'),
(10, 'ingjuliosd2@gmail.com', '9fa8197b-305d-11f1-94cc-e89c25808c74');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usr_roles`
--

CREATE TABLE `usr_roles` (
  `id_rol` bigint(20) NOT NULL,
  `rol` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usr_roles`
--

INSERT INTO `usr_roles` (`id_rol`, `rol`) VALUES
(1, 'usuario'),
(2, 'propietario'),
(3, 'admin');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usr_telefonos`
--

CREATE TABLE `usr_telefonos` (
  `id_telefono` bigint(20) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `user_uuid` char(36) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usr_telefonos`
--

INSERT INTO `usr_telefonos` (`id_telefono`, `telefono`, `user_uuid`) VALUES
(8, '9983380954', '676f80e6-305d-11f1-94cc-e89c25808c74'),
(9, '9983380954', '9fa8197b-305d-11f1-94cc-e89c25808c74');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usr_users`
--

CREATE TABLE `usr_users` (
  `uuid` char(36) NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usr_users`
--

INSERT INTO `usr_users` (`uuid`, `first_name`, `last_name`, `created_at`, `updated_at`) VALUES
('676f80e6-305d-11f1-94cc-e89c25808c74', 'julio ', 'serrano', '2026-04-04 14:35:15', NULL),
('9fa8197b-305d-11f1-94cc-e89c25808c74', 'Javier', 'Montana', '2026-04-04 14:36:49', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usr_users_login`
--

CREATE TABLE `usr_users_login` (
  `user_uuid` char(36) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `role` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usr_users_login`
--

INSERT INTO `usr_users_login` (`user_uuid`, `password`, `status`, `role`, `created_at`, `updated_at`) VALUES
('676f80e6-305d-11f1-94cc-e89c25808c74', 'db5d888a0480461f4fb978746d1baf34', 'active', 'propietario', '2026-04-04 14:35:15', NULL),
('9fa8197b-305d-11f1-94cc-e89c25808c74', 'db5d888a0480461f4fb978746d1baf34', 'active', 'user', '2026-04-04 14:36:49', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usr_usuarios_has_roles`
--

CREATE TABLE `usr_usuarios_has_roles` (
  `id_usuario_rol` bigint(20) NOT NULL,
  `id_rol` bigint(20) DEFAULT NULL,
  `user_uuid` char(36) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `lada` varchar(10) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `apellido`, `lada`, `telefono`, `email`, `password`, `fecha_registro`) VALUES
(1, 'Jjajajaj', 'Lopez', '+52', '9983380954', 'jija@gmail.com', '$2y$10$yoSHli0yJCpsg56nrFKz4e1OZ9wxsVyHTIykz5i8n3AHGAGiwQ0im', '2026-03-09 18:35:34');

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vista_marketplace`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vista_marketplace` (
`id_reserva` int(11)
,`first_name` varchar(100)
,`last_name` varchar(100)
,`numero_habitacion` int(11)
,`nombre_catalogo` varchar(150)
,`fecha_entrada` date
,`fecha_salida` date
,`estado` varchar(20)
);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vistos_recientes`
--

CREATE TABLE `vistos_recientes` (
  `id` int(11) NOT NULL,
  `user_id` varchar(100) DEFAULT NULL,
  `id_catalogo` int(11) DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_marketplace`
--
DROP TABLE IF EXISTS `vista_marketplace`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vista_marketplace`  AS SELECT `r`.`id_reserva` AS `id_reserva`, `u`.`first_name` AS `first_name`, `u`.`last_name` AS `last_name`, `h`.`numero` AS `numero_habitacion`, `c`.`nombre` AS `nombre_catalogo`, `r`.`fecha_entrada` AS `fecha_entrada`, `r`.`fecha_salida` AS `fecha_salida`, `r`.`estado` AS `estado` FROM (((`res_reserva` `r` join `usr_users` `u` on(`r`.`user_uuid` = `u`.`uuid`)) join `res_habitacion` `h` on(`r`.`id_habitacion` = `h`.`id_habitacion`)) join `cat_catalogo_habitacion` `c` on(`h`.`id_catalogo` = `c`.`id_catalogo`)) ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `catalogo`
--
ALTER TABLE `catalogo`
  ADD PRIMARY KEY (`id_catalogo`),
  ADD KEY `id_tipo` (`id_tipo`),
  ADD KEY `id_grupo` (`id_grupo`),
  ADD KEY `id_ubicacion` (`id_ubicacion`);

--
-- Indices de la tabla `cat_catalogo_habitacion`
--
ALTER TABLE `cat_catalogo_habitacion`
  ADD PRIMARY KEY (`id_catalogo`),
  ADD KEY `id_tipo` (`id_tipo`),
  ADD KEY `id_grupo` (`id_grupo`),
  ADD KEY `id_ubicacion` (`id_ubicacion`);

--
-- Indices de la tabla `cat_ciudad`
--
ALTER TABLE `cat_ciudad`
  ADD PRIMARY KEY (`id_ciudad`),
  ADD KEY `id_estado` (`id_estado`);

--
-- Indices de la tabla `cat_estado`
--
ALTER TABLE `cat_estado`
  ADD PRIMARY KEY (`id_estado`),
  ADD KEY `id_pais` (`id_pais`);

--
-- Indices de la tabla `cat_grupo`
--
ALTER TABLE `cat_grupo`
  ADD PRIMARY KEY (`id_grupo`);

--
-- Indices de la tabla `cat_imagen`
--
ALTER TABLE `cat_imagen`
  ADD PRIMARY KEY (`id_imagen`),
  ADD KEY `id_catalogo` (`id_catalogo`);

--
-- Indices de la tabla `cat_pais`
--
ALTER TABLE `cat_pais`
  ADD PRIMARY KEY (`id_pais`);

--
-- Indices de la tabla `cat_tipo`
--
ALTER TABLE `cat_tipo`
  ADD PRIMARY KEY (`id_tipo`);

--
-- Indices de la tabla `cat_ubicacion`
--
ALTER TABLE `cat_ubicacion`
  ADD PRIMARY KEY (`id_ubicacion`),
  ADD KEY `id_ciudad` (`id_ciudad`);

--
-- Indices de la tabla `metodos_pago`
--
ALTER TABLE `metodos_pago`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_pago_user` (`user_id`);

--
-- Indices de la tabla `reservas`
--
ALTER TABLE `reservas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `res_cancelacion`
--
ALTER TABLE `res_cancelacion`
  ADD PRIMARY KEY (`id_cancelacion`),
  ADD KEY `id_reserva` (`id_reserva`);

--
-- Indices de la tabla `res_control_estadia`
--
ALTER TABLE `res_control_estadia`
  ADD PRIMARY KEY (`id_estadia`),
  ADD KEY `id_reserva` (`id_reserva`);

--
-- Indices de la tabla `res_habitacion`
--
ALTER TABLE `res_habitacion`
  ADD PRIMARY KEY (`id_habitacion`),
  ADD KEY `fk_habitacion_catalogo` (`id_catalogo`);

--
-- Indices de la tabla `res_metodo_pago`
--
ALTER TABLE `res_metodo_pago`
  ADD PRIMARY KEY (`id_metodo_pago`);

--
-- Indices de la tabla `res_registro_pago`
--
ALTER TABLE `res_registro_pago`
  ADD PRIMARY KEY (`id_pago`),
  ADD KEY `id_reserva` (`id_reserva`),
  ADD KEY `id_metodo_pago` (`id_metodo_pago`);

--
-- Indices de la tabla `res_reserva`
--
ALTER TABLE `res_reserva`
  ADD PRIMARY KEY (`id_reserva`),
  ADD KEY `fk_reserva_user` (`user_uuid`),
  ADD KEY `fk_reserva_habitacion` (`id_habitacion`);

--
-- Indices de la tabla `usr_emails`
--
ALTER TABLE `usr_emails`
  ADD PRIMARY KEY (`id_email`),
  ADD KEY `fk_email_user` (`user_uuid`);

--
-- Indices de la tabla `usr_roles`
--
ALTER TABLE `usr_roles`
  ADD PRIMARY KEY (`id_rol`);

--
-- Indices de la tabla `usr_telefonos`
--
ALTER TABLE `usr_telefonos`
  ADD PRIMARY KEY (`id_telefono`),
  ADD KEY `fk_tel_user` (`user_uuid`);

--
-- Indices de la tabla `usr_users`
--
ALTER TABLE `usr_users`
  ADD PRIMARY KEY (`uuid`);

--
-- Indices de la tabla `usr_users_login`
--
ALTER TABLE `usr_users_login`
  ADD PRIMARY KEY (`user_uuid`);

--
-- Indices de la tabla `usr_usuarios_has_roles`
--
ALTER TABLE `usr_usuarios_has_roles`
  ADD PRIMARY KEY (`id_usuario_rol`),
  ADD KEY `id_rol` (`id_rol`),
  ADD KEY `user_uuid` (`user_uuid`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indices de la tabla `vistos_recientes`
--
ALTER TABLE `vistos_recientes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_vistos_user` (`user_id`),
  ADD KEY `fk_vistos_catalogo` (`id_catalogo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `catalogo`
--
ALTER TABLE `catalogo`
  MODIFY `id_catalogo` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cat_catalogo_habitacion`
--
ALTER TABLE `cat_catalogo_habitacion`
  MODIFY `id_catalogo` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cat_ciudad`
--
ALTER TABLE `cat_ciudad`
  MODIFY `id_ciudad` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cat_estado`
--
ALTER TABLE `cat_estado`
  MODIFY `id_estado` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cat_grupo`
--
ALTER TABLE `cat_grupo`
  MODIFY `id_grupo` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cat_imagen`
--
ALTER TABLE `cat_imagen`
  MODIFY `id_imagen` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cat_pais`
--
ALTER TABLE `cat_pais`
  MODIFY `id_pais` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cat_tipo`
--
ALTER TABLE `cat_tipo`
  MODIFY `id_tipo` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cat_ubicacion`
--
ALTER TABLE `cat_ubicacion`
  MODIFY `id_ubicacion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `metodos_pago`
--
ALTER TABLE `metodos_pago`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `reservas`
--
ALTER TABLE `reservas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `res_cancelacion`
--
ALTER TABLE `res_cancelacion`
  MODIFY `id_cancelacion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `res_control_estadia`
--
ALTER TABLE `res_control_estadia`
  MODIFY `id_estadia` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `res_habitacion`
--
ALTER TABLE `res_habitacion`
  MODIFY `id_habitacion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `res_metodo_pago`
--
ALTER TABLE `res_metodo_pago`
  MODIFY `id_metodo_pago` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `res_registro_pago`
--
ALTER TABLE `res_registro_pago`
  MODIFY `id_pago` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `res_reserva`
--
ALTER TABLE `res_reserva`
  MODIFY `id_reserva` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usr_emails`
--
ALTER TABLE `usr_emails`
  MODIFY `id_email` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `usr_roles`
--
ALTER TABLE `usr_roles`
  MODIFY `id_rol` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `usr_telefonos`
--
ALTER TABLE `usr_telefonos`
  MODIFY `id_telefono` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `usr_usuarios_has_roles`
--
ALTER TABLE `usr_usuarios_has_roles`
  MODIFY `id_usuario_rol` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `vistos_recientes`
--
ALTER TABLE `vistos_recientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `catalogo`
--
ALTER TABLE `catalogo`
  ADD CONSTRAINT `catalogo_ibfk_1` FOREIGN KEY (`id_tipo`) REFERENCES `cat_tipo` (`id_tipo`),
  ADD CONSTRAINT `catalogo_ibfk_2` FOREIGN KEY (`id_grupo`) REFERENCES `cat_grupo` (`id_grupo`),
  ADD CONSTRAINT `catalogo_ibfk_3` FOREIGN KEY (`id_ubicacion`) REFERENCES `cat_ubicacion` (`id_ubicacion`);

--
-- Filtros para la tabla `cat_catalogo_habitacion`
--
ALTER TABLE `cat_catalogo_habitacion`
  ADD CONSTRAINT `cat_catalogo_habitacion_ibfk_1` FOREIGN KEY (`id_tipo`) REFERENCES `cat_tipo` (`id_tipo`),
  ADD CONSTRAINT `cat_catalogo_habitacion_ibfk_2` FOREIGN KEY (`id_grupo`) REFERENCES `cat_grupo` (`id_grupo`),
  ADD CONSTRAINT `cat_catalogo_habitacion_ibfk_3` FOREIGN KEY (`id_ubicacion`) REFERENCES `cat_ubicacion` (`id_ubicacion`);

--
-- Filtros para la tabla `cat_ciudad`
--
ALTER TABLE `cat_ciudad`
  ADD CONSTRAINT `cat_ciudad_ibfk_1` FOREIGN KEY (`id_estado`) REFERENCES `cat_estado` (`id_estado`);

--
-- Filtros para la tabla `cat_estado`
--
ALTER TABLE `cat_estado`
  ADD CONSTRAINT `cat_estado_ibfk_1` FOREIGN KEY (`id_pais`) REFERENCES `cat_pais` (`id_pais`);

--
-- Filtros para la tabla `cat_imagen`
--
ALTER TABLE `cat_imagen`
  ADD CONSTRAINT `cat_imagen_ibfk_1` FOREIGN KEY (`id_catalogo`) REFERENCES `catalogo` (`id_catalogo`);

--
-- Filtros para la tabla `cat_ubicacion`
--
ALTER TABLE `cat_ubicacion`
  ADD CONSTRAINT `cat_ubicacion_ibfk_1` FOREIGN KEY (`id_ciudad`) REFERENCES `cat_ciudad` (`id_ciudad`);

--
-- Filtros para la tabla `metodos_pago`
--
ALTER TABLE `metodos_pago`
  ADD CONSTRAINT `fk_pago_user` FOREIGN KEY (`user_id`) REFERENCES `usr_users` (`uuid`) ON DELETE CASCADE;

--
-- Filtros para la tabla `res_cancelacion`
--
ALTER TABLE `res_cancelacion`
  ADD CONSTRAINT `res_cancelacion_ibfk_1` FOREIGN KEY (`id_reserva`) REFERENCES `res_reserva` (`id_reserva`);

--
-- Filtros para la tabla `res_control_estadia`
--
ALTER TABLE `res_control_estadia`
  ADD CONSTRAINT `res_control_estadia_ibfk_1` FOREIGN KEY (`id_reserva`) REFERENCES `res_reserva` (`id_reserva`);

--
-- Filtros para la tabla `res_habitacion`
--
ALTER TABLE `res_habitacion`
  ADD CONSTRAINT `fk_habitacion_catalogo` FOREIGN KEY (`id_catalogo`) REFERENCES `catalogo` (`id_catalogo`),
  ADD CONSTRAINT `res_habitacion_ibfk_1` FOREIGN KEY (`id_catalogo`) REFERENCES `cat_catalogo_habitacion` (`id_catalogo`);

--
-- Filtros para la tabla `res_registro_pago`
--
ALTER TABLE `res_registro_pago`
  ADD CONSTRAINT `res_registro_pago_ibfk_1` FOREIGN KEY (`id_reserva`) REFERENCES `res_reserva` (`id_reserva`),
  ADD CONSTRAINT `res_registro_pago_ibfk_2` FOREIGN KEY (`id_metodo_pago`) REFERENCES `res_metodo_pago` (`id_metodo_pago`);

--
-- Filtros para la tabla `res_reserva`
--
ALTER TABLE `res_reserva`
  ADD CONSTRAINT `fk_reserva_habitacion` FOREIGN KEY (`id_habitacion`) REFERENCES `res_habitacion` (`id_habitacion`),
  ADD CONSTRAINT `fk_reserva_user` FOREIGN KEY (`user_uuid`) REFERENCES `usr_users` (`uuid`),
  ADD CONSTRAINT `res_reserva_ibfk_1` FOREIGN KEY (`user_uuid`) REFERENCES `usr_users` (`uuid`),
  ADD CONSTRAINT `res_reserva_ibfk_2` FOREIGN KEY (`id_habitacion`) REFERENCES `res_habitacion` (`id_habitacion`);

--
-- Filtros para la tabla `usr_emails`
--
ALTER TABLE `usr_emails`
  ADD CONSTRAINT `fk_email_user` FOREIGN KEY (`user_uuid`) REFERENCES `usr_users` (`uuid`) ON DELETE CASCADE,
  ADD CONSTRAINT `usr_emails_ibfk_1` FOREIGN KEY (`user_uuid`) REFERENCES `usr_users` (`uuid`);

--
-- Filtros para la tabla `usr_telefonos`
--
ALTER TABLE `usr_telefonos`
  ADD CONSTRAINT `fk_tel_user` FOREIGN KEY (`user_uuid`) REFERENCES `usr_users` (`uuid`) ON DELETE CASCADE,
  ADD CONSTRAINT `usr_telefonos_ibfk_1` FOREIGN KEY (`user_uuid`) REFERENCES `usr_users` (`uuid`);

--
-- Filtros para la tabla `usr_users_login`
--
ALTER TABLE `usr_users_login`
  ADD CONSTRAINT `usr_users_login_ibfk_1` FOREIGN KEY (`user_uuid`) REFERENCES `usr_users` (`uuid`);

--
-- Filtros para la tabla `usr_usuarios_has_roles`
--
ALTER TABLE `usr_usuarios_has_roles`
  ADD CONSTRAINT `usr_usuarios_has_roles_ibfk_1` FOREIGN KEY (`id_rol`) REFERENCES `usr_roles` (`id_rol`),
  ADD CONSTRAINT `usr_usuarios_has_roles_ibfk_2` FOREIGN KEY (`user_uuid`) REFERENCES `usr_users` (`uuid`);

--
-- Filtros para la tabla `vistos_recientes`
--
ALTER TABLE `vistos_recientes`
  ADD CONSTRAINT `fk_vistos_catalogo` FOREIGN KEY (`id_catalogo`) REFERENCES `catalogo` (`id_catalogo`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_vistos_user` FOREIGN KEY (`user_id`) REFERENCES `usr_users` (`uuid`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
