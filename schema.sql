-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 26-07-2026 a las 16:11:53
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
-- Base de datos: `taller_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `actividad_inventario`
--

CREATE TABLE `actividad_inventario` (
  `id` int(11) NOT NULL,
  `tipo` varchar(20) DEFAULT NULL,
  `texto` varchar(255) DEFAULT NULL,
  `fecha` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `actividad_inventario`
--

INSERT INTO `actividad_inventario` (`id`, `tipo`, `texto`, `fecha`) VALUES
(1, 'del', 'Usado en Orden #7: -1 unidades de Aceite Motor 5W-30', '2026-07-13 19:19:11'),
(2, 'del', 'Usado en Orden #8: -2 unidades de Filtro de Aire', '2026-07-20 10:44:08'),
(3, 'del', 'Usado en Orden #8: -1 unidades de Filtro de Aceite', '2026-07-20 10:44:08'),
(4, 'del', 'Usado en Orden #8: -1 unidades de Bujías NGK', '2026-07-20 10:44:08'),
(5, 'del', 'Usado en Orden #8: -1 unidades de Pastillas de Freno', '2026-07-20 10:44:08'),
(6, 'del', 'Usado en Orden #9: -1 unidades de Bujías NGK', '2026-07-20 18:05:36'),
(7, 'del', 'Usado en Orden #9: -1 unidades de Aceite Motor 5W-30', '2026-07-20 18:05:37'),
(8, 'del', 'Usado en Orden #9: -1 unidades de Filtro de Aire', '2026-07-20 18:05:37');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `creado_en` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id`, `nombre`, `telefono`, `email`, `creado_en`) VALUES
(2, 'juan p', '111111111', 'doyem19959@hazhab.com', '2026-05-30 18:10:26'),
(4, 'pedro', '666666666666', 'chaufita@gmail.com', '2026-06-05 20:18:53'),
(5, 'juan', '00000000000000', 'soyprimero@gmail.com', '2026-06-05 20:19:44');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `gastos_inventario`
--

CREATE TABLE `gastos_inventario` (
  `id` int(11) NOT NULL,
  `inventario_id` int(11) DEFAULT NULL,
  `nombre` varchar(120) DEFAULT NULL,
  `cantidad_agregada` int(11) DEFAULT NULL,
  `costo_compra` decimal(10,2) DEFAULT NULL,
  `fecha` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inventario`
--

CREATE TABLE `inventario` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `categoria` varchar(50) DEFAULT NULL,
  `cantidad` int(11) DEFAULT 0,
  `cantidad_minima` int(11) DEFAULT 5,
  `precio` decimal(10,2) DEFAULT 0.00,
  `creado_en` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `inventario`
--

INSERT INTO `inventario` (`id`, `nombre`, `categoria`, `cantidad`, `cantidad_minima`, `precio`, `creado_en`) VALUES
(1, 'Aceite Motor 5W-30', 'Lubricantes', 15, 10, 25.00, '2026-06-15 13:27:33'),
(2, 'Filtro de Aceite', 'Filtros', 5, 5, 15.00, '2026-06-15 13:27:33'),
(3, 'Pastillas de Freno', 'Frenos', 70, 6, 45.00, '2026-06-15 13:27:33'),
(4, 'Bujías NGK', 'Eléctrico', 24, 8, 8.50, '2026-06-15 13:27:33'),
(5, 'Filtro de Aire', 'Filtros', 41, 5, 20.00, '2026-06-15 13:27:33');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ordenes`
--

CREATE TABLE `ordenes` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) DEFAULT NULL,
  `vehiculo` varchar(100) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `estado` varchar(50) DEFAULT NULL,
  `costo` decimal(10,2) DEFAULT 0.00,
  `mano_obra` decimal(10,2) DEFAULT 0.00,
  `costo_repuestos` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ordenes`
--

INSERT INTO `ordenes` (`id`, `cliente_id`, `vehiculo`, `descripcion`, `fecha`, `estado`, `costo`, `mano_obra`, `costo_repuestos`) VALUES
(1, 2, 'toyota', 'cambio de freno\ncambio de aceite ', '2026-05-30', 'Finalizado', 0.00, 0.00, 0.00),
(2, 2, 'hilux', 'motor\naceite \nfreno ', '2026-07-06', 'Finalizado', 0.00, 0.00, 0.00),
(3, 4, 'WASD125 NISSAN  Kicks 2016', 'FRENO \nACEITE', '2026-07-06', 'Finalizado', 0.00, 0.00, 0.00),
(4, 2, 'accd354 toyota hilux 2022', 'Cambio  aceite', '2026-07-13', 'Completado', 574.00, 20.00, 554.00),
(5, 4, 'WASD125 NISSAN  Kicks 2016', 'cambio de filtros aire y aceite \ncambio de pastillas de freno ', '2026-07-14', 'Pendiente', 110.00, 15.00, 95.00),
(6, 2, 'accd354 toyota hilux 2022', 'cambio de bujias', '2026-07-14', 'En Proceso', 8.50, 0.00, 8.50),
(7, 2, 'accd354 toyota hilux 2022', 'cambio de aceite ', '2026-07-14', 'En Proceso', 35.00, 10.00, 25.00),
(8, 2, 'accd354 toyota hilux 2022', '-', '2026-07-20', 'Pendiente', 168.50, 60.00, 108.50),
(9, 4, 'WASD125 NISSAN  Kicks 2016', '.', '2026-07-20', 'Pendiente', 113.50, 60.00, 53.50);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `orden_repuestos`
--

CREATE TABLE `orden_repuestos` (
  `id` int(11) NOT NULL,
  `orden_id` int(11) DEFAULT NULL,
  `inventario_id` int(11) DEFAULT NULL,
  `nombre` varchar(150) DEFAULT NULL,
  `cantidad` int(11) DEFAULT NULL,
  `precio_unitario` decimal(10,2) DEFAULT NULL,
  `subtotal` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `orden_repuestos`
--

INSERT INTO `orden_repuestos` (`id`, `orden_id`, `inventario_id`, `nombre`, `cantidad`, `precio_unitario`, `subtotal`) VALUES
(1, 5, 2, 'Filtro de Aceite', 2, 15.00, 30.00),
(2, 5, 5, 'Filtro de Aire', 1, 20.00, 20.00),
(3, 5, 3, 'Pastillas de Freno', 1, 45.00, 45.00),
(4, 6, 4, 'Bujías NGK', 1, 8.50, 8.50),
(5, 7, 1, 'Aceite Motor 5W-30', 1, 25.00, 25.00),
(6, 8, 5, 'Filtro de Aire', 2, 20.00, 40.00),
(7, 8, 2, 'Filtro de Aceite', 1, 15.00, 15.00),
(8, 8, 4, 'Bujías NGK', 1, 8.50, 8.50),
(9, 8, 3, 'Pastillas de Freno', 1, 45.00, 45.00),
(10, 9, 4, 'Bujías NGK', 1, 8.50, 8.50),
(11, 9, 1, 'Aceite Motor 5W-30', 1, 25.00, 25.00),
(12, 9, 5, 'Filtro de Aire', 1, 20.00, 20.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `perfil_taller`
--

CREATE TABLE `perfil_taller` (
  `id` int(11) NOT NULL DEFAULT 1,
  `nombre` varchar(120) DEFAULT NULL,
  `telefono` varchar(40) DEFAULT NULL,
  `email` varchar(120) DEFAULT NULL,
  `direccion` varchar(200) DEFAULT NULL,
  `ciudad` varchar(80) DEFAULT NULL,
  `ruc` varchar(20) DEFAULT NULL,
  `horario` varchar(120) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `perfil_taller`
--

INSERT INTO `perfil_taller` (`id`, `nombre`, `telefono`, `email`, `direccion`, `ciudad`, `ruc`, `horario`) VALUES
(1, 'Multiservicios Cárdenas', '123456789', '', '', 'Chincha Alta', '', '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `permisos_rol`
--

CREATE TABLE `permisos_rol` (
  `rol` varchar(50) NOT NULL,
  `permisos` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `permisos_rol`
--

INSERT INTO `permisos_rol` (`rol`, `permisos`) VALUES
('admin', '[\"dashboard\",\"clientes\",\"vehiculos\",\"ordenes\",\"inventario\",\"reportes\",\"usuarios\",\"perfil\"]'),
('Administrador', '[\"dashboard\",\"clientes\",\"vehiculos\",\"ordenes\",\"inventario\",\"reportes\",\"usuarios\",\"perfil\"]'),
('Gerente', '[\"dashboard\",\"clientes\",\"vehiculos\",\"ordenes\",\"inventario\",\"reportes\",\"usuarios\",\"perfil\"]'),
('Mecánico', '[\"dashboard\",\"vehiculos\",\"ordenes\"]');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `usuario` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `rol` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `usuario`, `password`, `rol`) VALUES
(1, 'Administrador', 'admin', '$2b$10$nUIr4pwKGAszCiEsJQOVhOr55VvJOgPnw1lLlvFltcGCoUUnSXr0W', 'admin'),
(2, 'Recepcionista', 'recepcion', '1234', 'recepcionista');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vehiculos`
--

CREATE TABLE `vehiculos` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `placa` varchar(20) NOT NULL,
  `marca` varchar(50) DEFAULT NULL,
  `modelo` varchar(50) DEFAULT NULL,
  `anio` int(11) DEFAULT NULL,
  `color` varchar(30) DEFAULT NULL,
  `creado_en` datetime DEFAULT current_timestamp(),
  `imagen` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `vehiculos`
--

INSERT INTO `vehiculos` (`id`, `cliente_id`, `placa`, `marca`, `modelo`, `anio`, `color`, `creado_en`, `imagen`) VALUES
(1, 2, 'accd354', 'toyota', 'hilux', 2022, 'negro', '2026-06-15 13:39:40', 'https://postimg.cc/fkMxQvyN');
INSERT INTO `vehiculos` (`id`, `cliente_id`, `placa`, `marca`, `modelo`, `anio`, `color`, `creado_en`, `imagen`) VALUES
(2, 4, 'WASD125', 'NISSAN', ' Kicks', 2016, 'blanco', '2026-07-06 16:42:02', 'https://postimg.cc/Dm7q3c02');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `actividad_inventario`
--
ALTER TABLE `actividad_inventario`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `gastos_inventario`
--
ALTER TABLE `gastos_inventario`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `inventario`
--
ALTER TABLE `inventario`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `ordenes`
--
ALTER TABLE `ordenes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`);

--
-- Indices de la tabla `orden_repuestos`
--
ALTER TABLE `orden_repuestos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `perfil_taller`
--
ALTER TABLE `perfil_taller`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `permisos_rol`
--
ALTER TABLE `permisos_rol`
  ADD PRIMARY KEY (`rol`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `vehiculos`
--
ALTER TABLE `vehiculos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `actividad_inventario`
--
ALTER TABLE `actividad_inventario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `gastos_inventario`
--
ALTER TABLE `gastos_inventario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `inventario`
--
ALTER TABLE `inventario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `ordenes`
--
ALTER TABLE `ordenes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `orden_repuestos`
--
ALTER TABLE `orden_repuestos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `vehiculos`
--
ALTER TABLE `vehiculos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `ordenes`
--
ALTER TABLE `ordenes`
  ADD CONSTRAINT `ordenes_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`);

--
-- Filtros para la tabla `vehiculos`
--
ALTER TABLE `vehiculos`
  ADD CONSTRAINT `vehiculos_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
