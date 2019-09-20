-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 20-09-2019 a las 11:36:30
-- Versión del servidor: 10.2.27-MariaDB-log
-- Versión de PHP: 7.2.7

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `futbolitocom_afer`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `abm_yesno`
--

CREATE TABLE `abm_yesno` (
  `id` int(11) NOT NULL,
  `valor` varchar(256) COLLATE utf8_spanish2_ci NOT NULL,
  `enabled` tinyint(4) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Volcado de datos para la tabla `abm_yesno`
--

INSERT INTO `abm_yesno` (`id`, `valor`, `enabled`) VALUES
(0, 'No', 1),
(1, 'Si', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sys_mail_queue`
--

CREATE TABLE `sys_mail_queue` (
  `id` int(11) NOT NULL,
  `from` varchar(256) COLLATE utf8_spanish2_ci NOT NULL,
  `to` varchar(1024) COLLATE utf8_spanish2_ci NOT NULL,
  `to_bcc` varchar(1024) COLLATE utf8_spanish2_ci NOT NULL,
  `subject` text COLLATE utf8_spanish2_ci NOT NULL,
  `text` text COLLATE utf8_spanish2_ci NOT NULL,
  `status` tinyint(4) NOT NULL,
  `timestamp` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sys_modulos`
--

CREATE TABLE `sys_modulos` (
  `id` int(11) NOT NULL,
  `icon` varchar(64) COLLATE utf8_spanish2_ci NOT NULL,
  `group_id` int(11) NOT NULL,
  `valor` varchar(256) COLLATE utf8_spanish2_ci NOT NULL,
  `filename` varchar(256) COLLATE utf8_spanish2_ci NOT NULL,
  `sort` int(11) NOT NULL,
  `public` tinyint(1) NOT NULL DEFAULT 0,
  `enabled` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Volcado de datos para la tabla `sys_modulos`
--

INSERT INTO `sys_modulos` (`id`, `icon`, `group_id`, `valor`, `filename`, `sort`, `public`, `enabled`) VALUES
(1, 'fa fa-lock', 0, 'Cerrar Sesion', 'cerrar-sesion', 1, 1, 1),
(17, 'fa fa-clock-o', 0, 'Estado', 'estado', 0, 1, 0),
(48, '', 0, 'Sin registrar', 'null', 0, 0, 0),
(49, 'fa fa-database', 0, 'Administración Usuarios', 'administracion-usuarios', 1, 0, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sys_modulos_groups`
--

CREATE TABLE `sys_modulos_groups` (
  `id` int(11) NOT NULL,
  `icon` varchar(64) COLLATE utf8_spanish2_ci NOT NULL,
  `valor` varchar(256) COLLATE utf8_spanish2_ci NOT NULL,
  `sort` int(11) NOT NULL,
  `public` tinyint(1) NOT NULL DEFAULT 0,
  `enabled` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sys_seguridad`
--

CREATE TABLE `sys_seguridad` (
  `id` int(11) NOT NULL,
  `id_modulo` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Volcado de datos para la tabla `sys_seguridad`
--

INSERT INTO `sys_seguridad` (`id`, `id_modulo`, `id_usuario`) VALUES
(1, 49, 12),
(2, 49, 18);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sys_sync_log`
--

CREATE TABLE `sys_sync_log` (
  `id` int(11) NOT NULL,
  `datetime` datetime NOT NULL,
  `status` varchar(8) COLLATE utf8_bin NOT NULL,
  `status_desc` text COLLATE utf8_bin NOT NULL,
  `compress` decimal(19,2) NOT NULL,
  `transfer` decimal(19,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Volcado de datos para la tabla `sys_sync_log`
--

INSERT INTO `sys_sync_log` (`id`, `datetime`, `status`, `status_desc`, `compress`, `transfer`) VALUES
(4293, '2019-09-20 00:42:11', 'BAD', 'GZIP SQL Can\'t create /var/www/html/@sync/files/kpos_generalroca_2019-09-20_0-42-11.sql.gz', 0.00, 0.00),
(4294, '2019-09-20 00:43:42', 'BAD', 'GZIP SQL Can\'t create /home/futbolitocom/public_html/@sync/files/kpos_generalroca_2019-09-20_0-43-42.sql.gz', 0.00, 0.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sys_usuarios`
--

CREATE TABLE `sys_usuarios` (
  `id` int(11) NOT NULL,
  `username` varchar(256) NOT NULL,
  `password` varchar(256) NOT NULL,
  `type` tinyint(4) NOT NULL DEFAULT 1,
  `last-ip` text NOT NULL,
  `session-id` text NOT NULL,
  `enabled` tinyint(4) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `sys_usuarios`
--

INSERT INTO `sys_usuarios` (`id`, `username`, `password`, `type`, `last-ip`, `session-id`, `enabled`) VALUES
(12, 'sistemas', 'aba142703de5def64ae7740f32e8f673', 2, '186.123.210.106', '2a0847e9ea13ddc365b3f2ea75159d51', 1),
(18, 'elanzara', 'eb1a723b5e7fe98770d199212a1d054d', 0, '186.123.210.106', '643493728eed80f9f8c594e1c62713a8', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sys_usuarios_datos`
--

CREATE TABLE `sys_usuarios_datos` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `nombre` varchar(256) COLLATE utf8_spanish2_ci NOT NULL,
  `apellido` varchar(256) COLLATE utf8_spanish2_ci NOT NULL,
  `email` varchar(512) COLLATE utf8_spanish2_ci NOT NULL,
  `fecha_nacimiento` date NOT NULL,
  `envia_saludo` tinyint(1) NOT NULL DEFAULT 1,
  `pv_facelec` int(11) NOT NULL,
  `pv_tktfiscal` int(11) NOT NULL,
  `pv_remito` int(11) NOT NULL,
  `limite_efectivo` decimal(19,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Volcado de datos para la tabla `sys_usuarios_datos`
--

INSERT INTO `sys_usuarios_datos` (`id`, `id_usuario`, `nombre`, `apellido`, `email`, `fecha_nacimiento`, `envia_saludo`, `pv_facelec`, `pv_tktfiscal`, `pv_remito`, `limite_efectivo`) VALUES
(8, 12, 'Usuario', 'Sistemas', 'garin@royaloilcompany.com.ar', '2018-10-01', 1, 0, 0, 0, 0.00),
(14, 18, 'Eduardo', 'Lanzara', 'edulanzara@gmail.com', '1973-10-23', 1, 0, 0, 0, 0.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sys_version`
--

CREATE TABLE `sys_version` (
  `software` decimal(19,2) NOT NULL,
  `ddbb` decimal(19,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `sys_version`
--

INSERT INTO `sys_version` (`software`, `ddbb`) VALUES
(0.90, 0.90);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `abm_yesno`
--
ALTER TABLE `abm_yesno`
  ADD PRIMARY KEY (`id`),
  ADD KEY `enabled` (`enabled`),
  ADD KEY `valor` (`valor`(255));

--
-- Indices de la tabla `sys_mail_queue`
--
ALTER TABLE `sys_mail_queue`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id` (`id`);

--
-- Indices de la tabla `sys_modulos`
--
ALTER TABLE `sys_modulos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id` (`id`),
  ADD KEY `group_id` (`group_id`),
  ADD KEY `enabled` (`enabled`),
  ADD KEY `public` (`public`),
  ADD KEY `sort` (`sort`),
  ADD KEY `filename` (`filename`(255));

--
-- Indices de la tabla `sys_modulos_groups`
--
ALTER TABLE `sys_modulos_groups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id` (`id`),
  ADD KEY `sort` (`sort`),
  ADD KEY `enabled` (`enabled`),
  ADD KEY `public` (`public`);

--
-- Indices de la tabla `sys_seguridad`
--
ALTER TABLE `sys_seguridad`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id` (`id`),
  ADD KEY `id_modulo` (`id_modulo`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `sys_sync_log`
--
ALTER TABLE `sys_sync_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id` (`id`);

--
-- Indices de la tabla `sys_usuarios`
--
ALTER TABLE `sys_usuarios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `username` (`username`),
  ADD KEY `password` (`password`),
  ADD KEY `id` (`id`),
  ADD KEY `enabled` (`enabled`);

--
-- Indices de la tabla `sys_usuarios_datos`
--
ALTER TABLE `sys_usuarios_datos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id` (`id`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `abm_yesno`
--
ALTER TABLE `abm_yesno`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `sys_mail_queue`
--
ALTER TABLE `sys_mail_queue`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `sys_modulos`
--
ALTER TABLE `sys_modulos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT de la tabla `sys_modulos_groups`
--
ALTER TABLE `sys_modulos_groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `sys_seguridad`
--
ALTER TABLE `sys_seguridad`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `sys_sync_log`
--
ALTER TABLE `sys_sync_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4295;

--
-- AUTO_INCREMENT de la tabla `sys_usuarios`
--
ALTER TABLE `sys_usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `sys_usuarios_datos`
--
ALTER TABLE `sys_usuarios_datos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
