-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 22-11-2025 a las 02:24:38
-- Versión del servidor: 10.4.28-MariaDB
-- Versión de PHP: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `TiendaPlus`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carrito`
--

CREATE TABLE `carrito` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `talla` varchar(10) DEFAULT NULL COMMENT 'Talla seleccionada por el cliente',
  `fecha_agregado` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `carrito`
--

INSERT INTO `carrito` (`id`, `usuario_id`, `producto_id`, `cantidad`, `talla`, `fecha_agregado`) VALUES
(13, 1, 5, 1, NULL, '2025-11-20 19:39:16'),
(17, 9, 10, 2, 'XXL', '2025-11-20 20:54:42'),
(22, 3, 10, 5, 'XXXL', '2025-11-20 22:26:41'),
(23, 3, 10, 2, 'XXL', '2025-11-20 22:27:27');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id`, `nombre`) VALUES
(1, 'Blusas'),
(2, 'Pantalones'),
(3, 'Vestidos'),
(4, 'Zapatos'),
(5, 'Accesorios');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_pedido`
--

CREATE TABLE `detalle_pedido` (
  `id` int(11) NOT NULL,
  `pedido_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `talla` varchar(10) DEFAULT NULL COMMENT 'Talla del producto en el pedido'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `detalle_pedido`
--

INSERT INTO `detalle_pedido` (`id`, `pedido_id`, `producto_id`, `cantidad`, `precio`, `talla`) VALUES
(2, 1, 2, 1, 89999.98, NULL),
(4, 3, 1, 2, 65000.00, NULL),
(5, 4, 1, 2, 65000.00, NULL),
(8, 5, 11, 2, 0.06, NULL),
(9, 7, 1, 1, 65000.00, NULL),
(10, 7, 6, 1, 72000.00, NULL),
(11, 8, 10, 1, 58000.00, NULL),
(12, 9, 11, 1, 95000.00, NULL),
(13, 10, 10, 1, 58000.00, NULL),
(14, 11, 5, 1, 30000.00, NULL),
(15, 11, 10, 1, 58000.00, NULL),
(16, 12, 10, 7, 58000.00, NULL),
(17, 13, 10, 1, 58000.00, NULL),
(18, 1, 9, 1, 0.08, NULL),
(19, 15, 5, 1, 30000.00, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos`
--

CREATE TABLE `pedidos` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `fecha` datetime DEFAULT current_timestamp(),
  `total` decimal(10,2) NOT NULL,
  `estado` varchar(50) DEFAULT 'pendiente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pedidos`
--

INSERT INTO `pedidos` (`id`, `usuario_id`, `fecha`, `total`, `estado`) VALUES
(1, 3, '2025-11-19 18:00:58', 229999.98, 'completado'),
(2, 3, '2025-11-19 19:05:27', 75000.00, 'pendiente'),
(3, 3, '2025-11-19 22:08:24', 140000.00, 'pendiente'),
(4, 3, '2025-11-19 22:21:03', 140000.00, 'pendiente'),
(5, 5, '2025-11-20 12:07:38', 145000.00, 'pendiente'),
(7, 3, '2025-11-20 14:04:22', 147000.00, 'pendiente'),
(8, 7, '2025-11-20 20:12:51', 68000.00, 'pendiente'),
(9, 8, '2025-11-20 20:31:15', 105000.00, 'pendiente'),
(10, 9, '2025-11-20 20:39:21', 68000.00, 'pendiente'),
(11, 3, '2025-11-20 21:03:10', 103000.00, 'pendiente'),
(12, 3, '2025-11-20 21:04:44', 416000.00, 'pendiente'),
(13, 3, '2025-11-20 22:17:58', 68000.00, 'pendiente'),
(14, 7, '2025-11-21 18:47:50', 0.16, 'procesando'),
(15, 11, '2025-11-21 19:29:07', 40000.00, 'pendiente');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(200) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio` decimal(10,2) NOT NULL,
  `categoria_id` int(11) DEFAULT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `destacado` tinyint(1) DEFAULT 0,
  `stock` int(11) NOT NULL DEFAULT 10,
  `tallas` varchar(100) DEFAULT NULL COMMENT 'Tallas disponibles separadas por comas (ej: S,M,L,XL)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `nombre`, `descripcion`, `precio`, `categoria_id`, `imagen`, `destacado`, `stock`, `tallas`) VALUES
(1, 'oversize ', 'Blusa fresca estampada perfecta para el día a día.', 65000.00, 1, 'oversize.png', 1, 10, 'XL,XXL,XXXL'),
(2, 'Jean Stretch Talla Grande', 'Pantalón jean stretch de alta comodidad.', 89999.98, 2, 'pantalon.png', 1, 10, 'XL,XXL,XXXL'),
(3, 'Vestido Elegante Curvy', 'Vestido largo color blanco.', 120000.00, 3, 'vestido.jpg', 1, 10, 'XL,XXL,XXXL'),
(4, 'Gafas', 'Gafas para sol ', 45000.00, 5, 'gafas.png', 0, 10, ''),
(5, 'Aretes Dorado', 'Accesorio moderno para combinar con tu outfit.', 30000.00, 5, 'aretes.png', 0, 10, ''),
(6, 'Reloj', 'Casual elegante', 72000.00, 5, 'reloj.png', 0, 10, ''),
(7, 'Botas ', 'Botas color negro elegantes.', 45000.00, 4, 'to.png', 0, 10, '35,36,37,38,39,40,41,42'),
(8, 'Bolso Cruzado Moderno', 'Bolso pequeño tipo crossbody con diseño moderno.', 52000.00, 5, 'bolso.png', 0, 10, ''),
(9, 'Vestido', 'Vestido estilo oversize para mujer.\r\nColo negro', 110000.00, 3, 'catalogo.jpg', 0, 10, 'XL,XXL,XXXL'),
(10, 'Basica', 'Basica de algodón stretch para tallas grandes.', 58000.00, 1, 'basica.png', 0, 10, 'XL,XXL,XXXL'),
(11, 'Blusa ', 'Elegante color negra ', 95000.00, 1, 'blusa.jpg', 0, 10, 'XL,XXL,XXXL'),
(12, 'Zapatillaselegantes Mujer', 'Zapatillas elegantes para eventos especiales', 95000.00, 4, 'img4.jpg', 0, 10, '35,36,37,38,39,40,41,42');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `resenas`
--

CREATE TABLE `resenas` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `comentario` text DEFAULT NULL,
  `calificacion` int(11) DEFAULT NULL,
  `fecha` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `resenas`
--

INSERT INTO `resenas` (`id`, `usuario_id`, `producto_id`, `comentario`, `calificacion`, `fecha`) VALUES
(1, 3, 4, 'muy bueno el material', 5, '2025-11-19 22:06:00'),
(2, 1, 7, 'excelente', 4, '2025-11-21 18:46:16');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('admin','cliente') DEFAULT 'cliente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `email`, `password`, `rol`) VALUES
(1, 'Administrador', 'admin@tiendaplus.com', 'admin123', 'admin'),
(2, 'Carlos Cliente', 'cliente@correo.com', '$2y$10$1hZqhaUkXp8ltdh8cR6IPuGfV2ZclK/3CqAbWTkS30yfUo15LdeL2', 'cliente'),
(3, 'Bleidis Cervantes', 'bleis@tiendaplus.com', '$2y$10$tRPoucJJ6DIEesro0wxF9emHEE5aAM6M2adWyTjPBfHBOsjpzms5O', 'cliente'),
(4, 'Laura Gómez', 'laugomez02@gmail.com', '$2y$10$JUmXeqnMXe5nSDFOaQuWHeEfsx9f.y0u8FPDFhe61QoiEVkH16FCi', 'cliente'),
(5, 'Yeimi Yojana', 'yeimipalma01@gmail.com', '$2y$10$yszjGXdE96or/blXZi717O6jdt94eX7PxsO3mgibE2k2ImJ3RsX2O', 'cliente'),
(6, 'Laura Gómez', 'lau@gmail.com', '$2y$10$JNdgo.PMJ/Iar5rLonfQp.PPDnjWF9LIXh1nvx54dyRjj.bML4V7m', 'cliente'),
(7, 'Laura Gómez', 'laura@gmail.com', '$2y$10$iYE.U2Dl41G2WGfzbgx1KOjIXetI77VN.x9UmhGmjb9NBDzKK6tNi', 'cliente'),
(8, 'Bleidis Cervantes', 'bleis56@tiendaplus.com', '$2y$10$YI3KwPx1/yjz.PUyV2X2cuMYCOMixCTsvazibe9omFMz.AuFnCEpS', 'cliente'),
(9, 'Bleidis Cervantes', 'bleis@23tiendaplus.com', '$2y$10$lJBiLLLzfQ38d.FBL/aFzu4Q66aoV4.N2SF15yWAZie7RC.m7XZQm', 'cliente'),
(10, 'Juan Pérez', 'juan@test.com', '$2y$10$KPojkWLwrBYzTGppUwjH7eOCQ9Ub8WWmDmbY1Ei2JzEvYCPyCD3XG', 'cliente'),
(11, 'juan pepito', 'pepito234@gmail.com', '$2y$10$besJItFXjgQlk.PE0seT2eAOsprYfS.q0CMPL5W2QjYn8Ig.FttGi', 'cliente');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `carrito`
--
ALTER TABLE `carrito`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `detalle_pedido`
--
ALTER TABLE `detalle_pedido`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pedido_id` (`pedido_id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- Indices de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `categoria_id` (`categoria_id`);

--
-- Indices de la tabla `resenas`
--
ALTER TABLE `resenas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `carrito`
--
ALTER TABLE `carrito`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `detalle_pedido`
--
ALTER TABLE `detalle_pedido`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `resenas`
--
ALTER TABLE `resenas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `carrito`
--
ALTER TABLE `carrito`
  ADD CONSTRAINT `carrito_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `carrito_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`);

--
-- Filtros para la tabla `detalle_pedido`
--
ALTER TABLE `detalle_pedido`
  ADD CONSTRAINT `detalle_pedido_ibfk_1` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`),
  ADD CONSTRAINT `detalle_pedido_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`);

--
-- Filtros para la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD CONSTRAINT `pedidos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`);

--
-- Filtros para la tabla `resenas`
--
ALTER TABLE `resenas`
  ADD CONSTRAINT `resenas_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `resenas_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
