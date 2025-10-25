-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 23-09-2025 a las 05:09:32
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
-- Base de datos: `vela`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `precios_materiales`
--

CREATE TABLE `precios_materiales` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `precio_kg` decimal(10,2) NOT NULL,
  `tipo` enum('material','esencia') NOT NULL,
  `actualizado` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `precios_materiales`
--

INSERT INTO `precios_materiales` (`id`, `nombre`, `precio_kg`, `tipo`, `actualizado`) VALUES
(1, 'APF', 6500.00, 'material', '2025-09-23 02:40:42'),
(2, 'VPF', 6500.00, 'material', '2025-09-23 02:41:25'),
(3, 'Esencias', 108000.00, 'esencia', '2025-09-23 02:40:42'),
(4, 'Alcohol', 19000.00, 'material', '2025-09-23 03:00:48'),
(5, 'Esencia_Difusor', 99000.00, 'esencia', '2025-09-23 03:00:48');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `tipo` enum('vela','difusor') NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `material_g` decimal(10,2) NOT NULL,
  `material_apf_g` decimal(10,2) DEFAULT 0.00,
  `material_vpf_g` decimal(10,2) DEFAULT 0.00,
  `material_precio_kg` decimal(10,2) NOT NULL,
  `esc_g` decimal(10,2) NOT NULL,
  `escencias_precio_kg` decimal(10,2) NOT NULL,
  `costo_insumos` decimal(10,2) NOT NULL,
  `alcohol_ml` decimal(10,2) DEFAULT NULL,
  `alcohol_precio_l` decimal(10,2) DEFAULT NULL,
  `esencia_g` decimal(10,2) DEFAULT NULL,
  `esencia_precio_kg` decimal(10,2) DEFAULT NULL,
  `frascos_precio_unidad` decimal(10,2) DEFAULT NULL,
  `tipo_frasco_id` int(11) DEFAULT NULL,
  `pack_extras` decimal(10,2) NOT NULL,
  `costo_total` decimal(10,2) NOT NULL,
  `ganancia_porcentaje` int(11) NOT NULL DEFAULT 100,
  `precio_venta` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `tipo`, `nombre`, `material_g`, `material_apf_g`, `material_vpf_g`, `material_precio_kg`, `esc_g`, `escencias_precio_kg`, `costo_insumos`, `alcohol_ml`, `alcohol_precio_l`, `esencia_g`, `esencia_precio_kg`, `frascos_precio_unidad`, `tipo_frasco_id`, `pack_extras`, `costo_total`, `ganancia_porcentaje`, `precio_venta`) VALUES
(5, 'vela', 'corazon chico', 21.00, 21.00, 0.00, 9000.00, 2.00, 108000.00, 402.50, NULL, NULL, NULL, NULL, NULL, NULL, 50.00, 402.50, 100, 805.00),
(6, 'vela', 'peonia chica', 31.00, 31.00, 0.00, 9000.00, 3.00, 108000.00, 575.50, NULL, NULL, NULL, NULL, NULL, NULL, 50.00, 575.50, 100, 1151.00),
(7, 'vela', 'peonia mediana', 52.00, 52.00, 0.00, 9000.00, 3.00, 108000.00, 732.00, NULL, NULL, NULL, NULL, NULL, NULL, 70.00, 732.00, 100, 1464.00),
(8, 'vela', 'cilindro mediano', 107.00, 107.00, 0.00, 9000.00, 8.00, 108000.00, 1709.50, NULL, NULL, NULL, NULL, NULL, NULL, 150.00, 1709.50, 100, 3419.00),
(9, 'vela', 'cilindro dob media', 115.00, 115.00, 0.00, 9000.00, 9.00, 108000.00, 1869.50, NULL, NULL, NULL, NULL, NULL, NULL, 150.00, 1869.50, 100, 3739.00),
(10, 'vela', 'piramide burbujas', 83.00, 83.00, 0.00, 9000.00, 6.00, 108000.00, 1287.50, NULL, NULL, NULL, NULL, NULL, NULL, 100.00, 1287.50, 100, 2575.00),
(11, 'vela', 'cubo burbujas', 150.00, 150.00, 0.00, 9000.00, 12.00, 108000.00, 2451.00, NULL, NULL, NULL, NULL, NULL, NULL, 180.00, 2451.00, 100, 4902.00),
(13, 'vela', 'gobo mediano rosass', 105.00, 105.00, 0.00, 7000.00, 8.00, 108000.00, 1696.50, NULL, NULL, NULL, NULL, NULL, NULL, 150.00, 1696.50, 100, 3393.00),
(14, 'vela', 'globo chico rosas', 40.00, 40.00, 0.00, 7000.00, 4.00, 108000.00, 739.00, NULL, NULL, NULL, NULL, NULL, NULL, 47.00, 739.00, 100, 1478.00),
(15, 'vela', 'margarita', 18.00, 18.00, 0.00, 7000.00, 2.00, 108000.00, 358.00, NULL, NULL, NULL, NULL, NULL, NULL, 25.00, 358.00, 100, 716.00),
(16, 'vela', 'bouquet flores', 19.00, 19.00, 0.00, 7000.00, 2.00, 108000.00, 374.50, NULL, NULL, NULL, NULL, NULL, NULL, 35.00, 374.50, 100, 749.00),
(17, 'vela', 'Arco iris', 231.00, 231.00, 0.00, 7000.00, 18.00, 108000.00, 3475.50, NULL, NULL, NULL, NULL, NULL, NULL, 30.00, 3475.50, 100, 6951.00),
(18, 'vela', 'torneada globo', 176.00, 176.00, 0.00, 7000.00, 14.00, 108000.00, 2678.00, NULL, NULL, NULL, NULL, NULL, NULL, 22.00, 2678.00, 100, 5356.00),
(19, 'vela', 'adorno navidad', 127.00, 127.00, 0.00, 7000.00, 10.00, 108000.00, 1921.50, NULL, NULL, NULL, NULL, NULL, NULL, 16.00, 1921.50, 100, 3843.00),
(20, 'vela', 'grande esfera tipo gajos', 192.00, 192.00, 0.00, 7000.00, 15.00, 108000.00, 2898.00, NULL, NULL, NULL, NULL, NULL, NULL, 30.00, 2898.00, 100, 5796.00),
(21, 'vela', 'ostra chica', 33.00, 33.00, 0.00, 7000.00, 3.00, 108000.00, 580.50, NULL, NULL, NULL, NULL, NULL, NULL, 42.00, 580.50, 100, 1161.00),
(23, 'vela', 'cuenco madera', 45.00, 45.00, 0.00, 6500.00, 3.00, 108000.00, 3416.50, NULL, NULL, NULL, NULL, NULL, NULL, 2800.00, 3416.50, 100, 6833.00),
(24, 'vela', 'vaso tenese', 230.00, 230.00, 0.00, 6500.00, 18.00, 108000.00, 4739.00, NULL, NULL, NULL, NULL, NULL, NULL, 1300.00, 4739.00, 100, 9478.00),
(25, 'vela', 'cuenco vidreo globitos', 60.00, 60.00, 0.00, 6500.00, 6.00, 108000.00, 3838.00, NULL, NULL, NULL, NULL, NULL, NULL, 2800.00, 3838.00, 100, 7676.00),
(26, 'vela', 'cuenco ceramico', 54.00, 54.00, 0.00, 6500.00, 4.00, 108000.00, 2283.00, NULL, NULL, NULL, NULL, NULL, NULL, 1500.00, 2283.00, 100, 4566.00),
(27, 'vela', 'cono vidrio', 320.00, 320.00, 0.00, 6500.00, 16.00, 108000.00, 10808.00, NULL, NULL, NULL, NULL, NULL, NULL, 7000.00, 10808.00, 100, 21616.00),
(28, 'vela', 'lata rosa dior', 70.00, 70.00, 0.00, 6900.00, 9.00, 108000.00, 3427.00, NULL, NULL, NULL, NULL, NULL, NULL, 2000.00, 3427.00, 100, 6854.00),
(29, 'vela', 'lata bombe cobre', 66.00, 66.00, 0.00, 6900.00, 6.00, 108000.00, 3077.00, NULL, NULL, NULL, NULL, NULL, NULL, 2000.00, 3077.00, 100, 6154.00),
(30, 'vela', 'caramelera de soja', 206.00, 206.00, 0.00, 6500.00, 11.00, 108000.00, 5527.00, NULL, NULL, NULL, NULL, NULL, NULL, 3000.00, 5527.00, 100, 11054.00),
(31, 'vela', 'pimpollo de rosa', 97.00, 97.00, 0.00, 6500.00, 5.00, 108000.00, 1370.50, NULL, NULL, NULL, NULL, NULL, NULL, 200.00, 1370.50, 100, 2741.00),
(32, 'vela', 'osito con moño', 51.00, 51.00, 0.00, 6500.00, 4.00, 108000.00, 853.50, NULL, NULL, NULL, NULL, NULL, NULL, 90.00, 853.50, 100, 1707.00),
(33, 'vela', 'velon grandes', 611.00, 611.00, 0.00, 6500.00, 49.00, 108000.00, 10263.50, NULL, NULL, NULL, NULL, NULL, NULL, 1000.00, 10263.50, 100, 20527.00),
(34, 'vela', 'cilindro de 16x16', 288.00, 288.00, 0.00, 6500.00, 23.00, 108000.00, 4956.00, NULL, NULL, NULL, NULL, NULL, NULL, 600.00, 4956.00, 100, 9912.00),
(35, 'vela', 'cilindro chico', 172.00, 172.00, 0.00, 6500.00, 14.00, 108000.00, 3130.00, NULL, NULL, NULL, NULL, NULL, NULL, 500.00, 3130.00, 100, 6260.00),
(36, 'vela', 'cubo chico', 155.00, 155.00, 0.00, 6500.00, 12.00, 108000.00, 2803.50, NULL, NULL, NULL, NULL, NULL, NULL, 500.00, 2803.50, 100, 5607.00),
(37, 'vela', 'frasco campana', 160.00, 160.00, 0.00, 6500.00, 13.00, 108000.00, 7444.00, NULL, NULL, NULL, NULL, NULL, NULL, 5000.00, 7444.00, 100, 14888.00),
(38, 'vela', 'frasco copita', 190.00, 190.00, 0.00, 6500.00, 14.00, 108000.00, 6747.00, NULL, NULL, NULL, NULL, NULL, NULL, 4000.00, 6747.00, 100, 13494.00),
(39, 'vela', 'vaso chico vidrio', 128.00, 128.00, 0.00, 6500.00, 7.00, 108000.00, 3088.00, NULL, NULL, NULL, NULL, NULL, NULL, 1500.00, 3088.00, 100, 6176.00),
(40, 'vela', 'vaso mediano vidrio', 170.00, 170.00, 0.00, 6500.00, 10.00, 108000.00, 3685.00, NULL, NULL, NULL, NULL, NULL, NULL, 1500.00, 3685.00, 100, 7370.00),
(41, 'vela', 'vaso cristal', 200.00, 200.00, 0.00, 6500.00, 12.00, 108000.00, 4096.00, NULL, NULL, NULL, NULL, NULL, NULL, 1500.00, 4096.00, 100, 8192.00),
(42, 'vela', 'frasco pequeño', 180.00, 180.00, 0.00, 6500.00, 11.00, 108000.00, 3858.00, NULL, NULL, NULL, NULL, NULL, NULL, 1500.00, 3858.00, 100, 7716.00),
(43, 'vela', 'frasco mediano', 190.00, 190.00, 0.00, 6500.00, 13.00, 108000.00, 4139.00, NULL, NULL, NULL, NULL, NULL, NULL, 1500.00, 4139.00, 100, 8278.00),
(44, 'vela', 'frasco grande', 200.00, 200.00, 0.00, 6500.00, 14.00, 108000.00, 4312.00, NULL, NULL, NULL, NULL, NULL, NULL, 1500.00, 4312.00, 100, 8624.00),
(45, 'vela', 'frasco ovalado', 210.00, 210.00, 0.00, 6500.00, 15.00, 108000.00, 4485.00, NULL, NULL, NULL, NULL, NULL, NULL, 1500.00, 4485.00, 100, 8970.00),
(46, 'vela', 'frasco cuadrado', 220.00, 220.00, 0.00, 6500.00, 16.00, 108000.00, 4658.00, NULL, NULL, NULL, NULL, NULL, NULL, 1500.00, 4658.00, 100, 9316.00),
(47, 'vela', 'frasco estrella', 230.00, 230.00, 0.00, 6500.00, 17.00, 108000.00, 4831.00, NULL, NULL, NULL, NULL, NULL, NULL, 1500.00, 4831.00, 100, 9662.00),
(48, 'vela', 'frasco hexagonal', 240.00, 240.00, 0.00, 6500.00, 18.00, 108000.00, 5004.00, NULL, NULL, NULL, NULL, NULL, NULL, 1500.00, 5004.00, 100, 10008.00),
(49, 'vela', 'frasco piramidal', 250.00, 250.00, 0.00, 6500.00, 19.00, 108000.00, 5177.00, NULL, NULL, NULL, NULL, NULL, NULL, 1500.00, 5177.00, 100, 10354.00),
(50, 'difusor', 'perfumina x 250 vidrio', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 7780.00, 200.00, 19000.00, 20.00, 99000.00, 1540.00, 4, 1500.00, 7780.00, 100, 15560.00),
(51, 'difusor', 'perfuminas x 2oocm', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 6822.00, 160.00, 19000.00, 18.00, 99000.00, 1450.00, 4, 1500.00, 6822.00, 100, 13644.00),
(52, 'difusor', 'perfumina 150', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 5864.00, 120.00, 19000.00, 16.00, 99000.00, 1300.00, 4, 1500.00, 5864.00, 100, 11728.00),
(53, 'difusor', 'perfumina 125', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 4890.00, 100.00, 19000.00, 10.00, 99000.00, 1300.00, 4, 1500.00, 4890.00, 100, 9780.00),
(54, 'difusor', 'DIFUSOR x 250', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 12591.00, 250.00, 19000.00, 59.00, 99000.00, 1540.00, 4, 1500.00, 12591.00, 100, 25182.00),
(55, 'difusor', 'DIFUSOR x 200', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 10453.00, 200.00, 19000.00, 47.00, 99000.00, 1540.00, 4, 1500.00, 10453.00, 100, 20906.00),
(56, 'difusor', 'DIFUSOR x 125', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 7345.00, 125.00, 19000.00, 30.00, 99000.00, 1300.00, 4, 1500.00, 7345.00, 100, 14690.00),
(57, 'difusor', 'petacas c/t', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 10953.00, 200.00, 19000.00, 47.00, 99000.00, 1000.00, 5, 1500.00, 10953.00, 100, 21906.00),
(58, 'difusor', 'perfumero 200', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 10453.00, 200.00, 19000.00, 47.00, 99000.00, 4500.00, 4, 1500.00, 10453.00, 100, 20906.00),
(59, 'difusor', 'perfumero difu', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 10453.00, 200.00, 19000.00, 47.00, 99000.00, 6000.00, 4, 1500.00, 10453.00, 100, 20906.00),
(60, 'vela', 'globo burbujas', 98.00, 98.00, 0.00, 7000.00, 7.00, 108000.00, 1543.00, 0.00, 0.00, 0.00, 0.00, 0.00, NULL, 150.00, 1543.00, 100, 3086.00),
(73, 'vela', 'corazon chiquito', 20.00, 20.00, 0.00, 3000.00, 4.00, 11000.00, 582.00, 0.00, 0.00, 0.00, 0.00, 0.00, NULL, 20.00, 582.00, 100, 1164.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipos_frascos`
--

CREATE TABLE `tipos_frascos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `precio_unidad` decimal(10,2) NOT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `actualizado` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipos_frascos`
--

INSERT INTO `tipos_frascos` (`id`, `nombre`, `precio_unidad`, `activo`, `actualizado`) VALUES
(1, 'Frasco 50ml', 250.00, 1, '2025-09-23 02:53:02'),
(4, 'Frasco Premium 100ml', 500.00, 1, '2025-09-23 02:53:02'),
(5, 'Frasco premummm', 1000.00, 1, '2025-09-23 02:59:20');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `precios_materiales`
--
ALTER TABLE `precios_materiales`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre_tipo` (`nombre`,`tipo`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tipo_frasco` (`tipo_frasco_id`);

--
-- Indices de la tabla `tipos_frascos`
--
ALTER TABLE `tipos_frascos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `precios_materiales`
--
ALTER TABLE `precios_materiales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

--
-- AUTO_INCREMENT de la tabla `tipos_frascos`
--
ALTER TABLE `tipos_frascos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
