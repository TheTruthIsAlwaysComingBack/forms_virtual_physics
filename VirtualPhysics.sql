-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 03-06-2025 a las 02:12:27
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
-- Base de datos: `virtualphysics`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `formularios`
--

CREATE TABLE `formularios` (
  `id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `formularios`
--

INSERT INTO `formularios` (`id`, `titulo`, `descripcion`, `fecha_creacion`, `activo`) VALUES
(1, 'Cuestionario de Física Básica', 'Evaluación sobre conceptos fundamentales de física', '2025-06-02 17:34:13', 1),
(2, 'Encuesta de Satisfacción', 'Evaluación sobre la experiencia del usuario', '2025-06-02 17:34:13', 1),
(3, 'Experimentos de Laboratorio Virtual', 'Cuestionario sobre experimentos de física que puedes realizar', '2025-06-02 17:42:01', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `preguntas`
--

CREATE TABLE `preguntas` (
  `id` int(11) NOT NULL,
  `formulario_id` int(11) NOT NULL,
  `pregunta` text NOT NULL,
  `tipo` enum('texto','multiple','checkbox') DEFAULT 'texto',
  `opciones` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`opciones`)),
  `orden` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `preguntas`
--

INSERT INTO `preguntas` (`id`, `formulario_id`, `pregunta`, `tipo`, `opciones`, `orden`) VALUES
(4, 1, '¿Cuál es la unidad de medida de la fuerza en el Sistema Internacional?', 'texto', NULL, 1),
(5, 1, '¿Qué es la velocidad en el Movimiento Rectilíneo Uniforme (MRU)?', 'texto', NULL, 2),
(6, 1, '¿Cuál es la fórmula para calcular la aceleración en MRUA?', 'texto', NULL, 3),
(7, 1, '¿Cuánto vale aproximadamente la aceleración de la gravedad en la Tierra?', 'texto', NULL, 4),
(8, 1, 'En caída libre, ¿qué tipo de movimiento describe un objeto?', 'texto', NULL, 5),
(9, 1, 'En el tiro vertical, ¿cuál es la velocidad en el punto más alto de la trayectoria?', 'texto', NULL, 6),
(10, 1, '¿Cómo se llama la aceleración que experimenta un objeto en movimiento circular uniforme?', 'texto', NULL, 7),
(11, 1, 'En el tiro parabólico, ¿cuáles son las dos componentes del movimiento?', 'texto', NULL, 8),
(12, 1, '¿Qué instrumento se usa comúnmente para medir tiempo en experimentos de cinemática?', 'texto', NULL, 9),
(13, 1, 'Describe brevemente qué observarías en un experimento de MRU con un carrito en un riel', 'texto', NULL, 10),
(14, 2, '¿Cómo calificarías la interfaz del sistema?', 'texto', NULL, 1),
(15, 2, '¿Te parecen útiles los cuestionarios de física?', 'texto', NULL, 2),
(16, 2, '¿Qué tema de física te resulta más interesante?', 'texto', NULL, 3),
(17, 3, '¿Qué materiales necesitarías para un experimento de MRU?', 'texto', NULL, 1),
(18, 3, 'Describe cómo harías un experimento de caída libre con una pelota', 'texto', NULL, 2),
(19, 3, '¿Cómo medirías la aceleración centrípeta en un movimiento circular?', 'texto', NULL, 3),
(20, 3, '¿Qué variables medirías en un experimento de tiro parabólico?', 'texto', NULL, 4),
(21, 3, '¿Cómo usarías un cronómetro para medir la velocidad en MRUA?', 'texto', NULL, 5);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `respuestas`
--

CREATE TABLE `respuestas` (
  `id` int(11) NOT NULL,
  `usuario_codigo` varchar(50) NOT NULL,
  `formulario_id` int(11) NOT NULL,
  `pregunta_id` int(11) NOT NULL,
  `respuesta` text DEFAULT NULL,
  `fecha_respuesta` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `respuestas`
--

INSERT INTO `respuestas` (`id`, `usuario_codigo`, `formulario_id`, `pregunta_id`, `respuesta`, `fecha_respuesta`) VALUES
(1, 'admin-001', 2, 14, 'buena ', '2025-06-02 18:03:44'),
(2, 'admin-001', 2, 15, 'si', '2025-06-02 18:03:44'),
(3, 'admin-001', 2, 16, 'mru', '2025-06-02 18:03:44'),
(4, 'admin-001', 3, 17, 'mesas', '2025-06-02 19:42:51'),
(5, 'admin-001', 3, 18, 'soltando una canica', '2025-06-02 19:42:51'),
(6, 'admin-001', 3, 19, 'la verdad no lo se', '2025-06-02 19:42:51'),
(7, 'admin-001', 3, 20, 'la variable x', '2025-06-02 19:42:51'),
(8, 'admin-001', 3, 21, 'con ayuda del docente', '2025-06-02 19:42:51');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `codigo` varchar(50) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `fechaRegistro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`codigo`, `nombre`, `correo`, `contrasena`, `fechaRegistro`) VALUES
('64b6cc27-0584-470d-81c9-e6d17131ad3f', 'izai', 'izi@gmail.com', '469d8d5a94414bd9e33815377ea9284f5a426236ce6c17d1ab417ba4b2f529c4', '2025-06-02 16:24:40'),
('admin-001', 'Administrador', 'admin@virtualphysics.com', '240be518fabd2724ddb6f04eeb1da5967448d7e831c08c8fa822809f74c720a9', '2025-06-02 18:02:05'),
('test-001', 'Usuario Test', 'test@test.com', '8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92', '2025-06-02 18:02:05');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `formularios`
--
ALTER TABLE `formularios`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `preguntas`
--
ALTER TABLE `preguntas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `formulario_id` (`formulario_id`);

--
-- Indices de la tabla `respuestas`
--
ALTER TABLE `respuestas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_codigo` (`usuario_codigo`),
  ADD KEY `formulario_id` (`formulario_id`),
  ADD KEY `pregunta_id` (`pregunta_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`codigo`),
  ADD UNIQUE KEY `correo` (`correo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `formularios`
--
ALTER TABLE `formularios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `preguntas`
--
ALTER TABLE `preguntas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `respuestas`
--
ALTER TABLE `respuestas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `preguntas`
--
ALTER TABLE `preguntas`
  ADD CONSTRAINT `preguntas_ibfk_1` FOREIGN KEY (`formulario_id`) REFERENCES `formularios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `respuestas`
--
ALTER TABLE `respuestas`
  ADD CONSTRAINT `respuestas_ibfk_1` FOREIGN KEY (`usuario_codigo`) REFERENCES `usuarios` (`codigo`) ON DELETE CASCADE,
  ADD CONSTRAINT `respuestas_ibfk_2` FOREIGN KEY (`formulario_id`) REFERENCES `formularios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `respuestas_ibfk_3` FOREIGN KEY (`pregunta_id`) REFERENCES `preguntas` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
