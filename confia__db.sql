-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 16-07-2025 a las 19:31:01
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `confia+_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `directores_institucion`
--

CREATE TABLE `directores_institucion` (
  `id` int(11) NOT NULL,
  `id_director` int(11) UNSIGNED NOT NULL,
  `id_institucion` int(11) UNSIGNED NOT NULL,
  `fecha_asignacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estado_animo`
--

CREATE TABLE `estado_animo` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) UNSIGNED NOT NULL,
  `nivel_animo` int(11) NOT NULL,
  `observaciones` text DEFAULT NULL,
  `fecha_registro` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `instituciones`
--

CREATE TABLE `instituciones` (
  `id` int(11) UNSIGNED NOT NULL,
  `nombre_institucion` varchar(255) NOT NULL,
  `region` varchar(100) DEFAULT NULL,
  `ciudad` varchar(100) DEFAULT NULL,
  `contacto_email` varchar(255) DEFAULT NULL,
  `fecha_registro` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `resultados_tests`
--

CREATE TABLE `resultados_tests` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) UNSIGNED NOT NULL,
  `id_test` int(11) UNSIGNED NOT NULL,
  `puntaje` int(11) NOT NULL,
  `resultado_texto` text DEFAULT NULL,
  `fecha_realizacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tests`
--

CREATE TABLE `tests` (
  `id` int(11) UNSIGNED NOT NULL,
  `nombre_test` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) UNSIGNED NOT NULL,
  `nombre_usuario` varchar(50) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `passwd` varchar(255) NOT NULL,
  `rol` enum('usuario','director','administrador') NOT NULL DEFAULT 'usuario',
  `fecha_registro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre_usuario`, `correo`, `passwd`, `rol`, `fecha_registro`) VALUES
(1, 'admi', 'Confia.contacto@gmail.com', '$2y$10$U3LG5EAYmkiWEnZQysub3eJsAM.FmeIv4z8kxZmCuwAS6pRcZYFyW', 'administrador', '2025-07-15 12:23:46'),
(2, 'Soledad', 'pachecosoledad968@gmail.com', '$2y$10$tYYktXlIn1D8zBy9H3jOy.MdQtDYM97JuI7HDFzEWNxlH6Fic/kM.', 'usuario', '2025-07-15 12:23:46'),
(4, 'Benjamin', 'benja.61798@gmail.com', '$2y$10$xmwf8.yYAZ/2eu3/c/rkAO7w0dliBUYJ88ewfroWsMBYU5IMgvS36', 'usuario', '2025-07-16 12:32:35');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `directores_institucion`
--
ALTER TABLE `directores_institucion`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `director_institucion_unique` (`id_director`,`id_institucion`),
  ADD KEY `id_institucion` (`id_institucion`);

--
-- Indices de la tabla `estado_animo`
--
ALTER TABLE `estado_animo`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `instituciones`
--
ALTER TABLE `instituciones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre_institucion` (`nombre_institucion`);

--
-- Indices de la tabla `resultados_tests`
--
ALTER TABLE `resultados_tests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_test` (`id_test`);

--
-- Indices de la tabla `tests`
--
ALTER TABLE `tests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre_test` (`nombre_test`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre_usuario` (`nombre_usuario`),
  ADD UNIQUE KEY `correo` (`correo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `directores_institucion`
--
ALTER TABLE `directores_institucion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `estado_animo`
--
ALTER TABLE `estado_animo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `instituciones`
--
ALTER TABLE `instituciones`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `resultados_tests`
--
ALTER TABLE `resultados_tests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tests`
--
ALTER TABLE `tests`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `directores_institucion`
--
ALTER TABLE `directores_institucion`
  ADD CONSTRAINT `directores_institucion_ibfk_1` FOREIGN KEY (`id_director`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `directores_institucion_ibfk_2` FOREIGN KEY (`id_institucion`) REFERENCES `instituciones` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `estado_animo`
--
ALTER TABLE `estado_animo`
  ADD CONSTRAINT `estado_animo_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `resultados_tests`
--
ALTER TABLE `resultados_tests`
  ADD CONSTRAINT `resultados_tests_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `resultados_tests_ibfk_2` FOREIGN KEY (`id_test`) REFERENCES `tests` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
