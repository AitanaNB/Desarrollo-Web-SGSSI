-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Servidor: db
-- Tiempo de generación: 16-09-2020 a las 16:37:17
-- Versión del servidor: 10.5.5-MariaDB-1:10.5.5+maria~focal
-- Versión de PHP: 7.4.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `database`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` text NOT NULL,
  `apellidos` text NOT NULL,
  `dni` char(10) NOT NULL,
  `telefono` int(9) NOT NULL,
  `fecha_nacimiento` date NOT NULL,
  `email` text NOT NULL,
  `dinero` decimal(10,2) NOT NULL DEFAULT 0, 
  `username` text NOT NULL,
  `password` varchar(100) NOT NULL,
  `es_admin` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `usuarios`
--
INSERT INTO `usuarios` (`id`, `nombre`, `apellidos`, `dni`, `telefono`, `fecha_nacimiento`, `email`, `dinero`, `username`, `password`, `es_admin`) VALUES
(1, 'Admin', 'Sistema', '00000000-T', '600000000', '1990-01-01', 'admin@sistema.com', 50000.00, 'admin', 'admintest', 1),
(2, 'Aitor', 'Perez Garcia', '12345678-Z', '612345678', '1995-11-23', 'juan@example.com', 15000.00, 'juan', 'qwerty', 0);


--
-- Estructura de tabla para la tabla `coches`
--
CREATE TABLE `coches` (
  `id` int (11) NOT NULL,
  `matricula` varchar(10) NOT NULL,
  `modelo` varchar(50) NOT NULL,
  `marca` varchar(50) NOT NULL,
  `color` varchar(10) NOT NULL,
  `kilometraje` int(10) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `en_venta` tinyint(1) NOT NULL DEFAULT 0,
  `id_propietario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `coches`
--
INSERT INTO `coches` (`id`, `matricula`, `modelo`, `marca`, `color`, `kilometraje`, `precio`, `id_propietario`) VALUES
(1, '1234-ABC', 'Model S', 'Tesla', 'Rojo', 20000, 75000.00, 2),
(2, '5678-DEF', 'Civic', 'Honda', 'Azul', 50000, 20000.00, 2);


--
-- Índices para tablas volcadas
--


--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `dni` (`dni`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`);


--
-- Indices de la tabla `coches`
--
ALTER TABLE `coches`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `matricula` (`matricula`),
  ADD KEY `id_propietario` (`id_propietario`);


--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `coches`
--
ALTER TABLE `coches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;


--
-- Filtros para la tabla `coches`
--
ALTER TABLE `coches`
  ADD CONSTRAINT `coches_ibfk_1` FOREIGN KEY (`id_propietario`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;  

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;