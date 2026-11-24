-- phpMyAdmin SQL Dump
-- PrimeLab - Sistema de Reserva de Citas Médicas
-- Base de datos enriquecida con información más detallada
-- Versión 5.2.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS `bd_primelab` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci;
USE `bd_primelab`;

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- --------------------------------------------------------
-- Tabla `centro`
-- --------------------------------------------------------

CREATE TABLE `centro` (
  `codce` char(6) NOT NULL,
  `nomce` varchar(80) NOT NULL,
  `dirc` varchar(100) NOT NULL,
  `distrito` varchar(50) NOT NULL,
  `provincia` varchar(40) NOT NULL DEFAULT 'Lima',
  `departamento` varchar(40) NOT NULL DEFAULT 'Lima',
  `telefono` varchar(15) NOT NULL,
  `horario_atencion` varchar(60) NOT NULL DEFAULT 'Lunes a Viernes 07:00 - 17:00',
  `tipo` varchar(20) NOT NULL DEFAULT 'Público'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

INSERT INTO `centro` (`codce`, `nomce`, `dirc`, `distrito`, `provincia`, `departamento`, `telefono`, `horario_atencion`, `tipo`) VALUES
('CEN001', 'Centro de Salud San Martín de Porres', 'Av. Perú 1250, Urb. San Martín', 'Cercado de Lima', 'Lima', 'Lima', '014567890', 'Lunes a Sábado 07:00 - 19:00', 'Público'),
('CEN002', 'Centro de Salud Santa Rosa - SJL', 'Jr. Los Jardines 420, AA.HH. Santa Rosa', 'San Juan de Lurigancho', 'Lima', 'Lima', '014321876', 'Lunes a Viernes 07:00 - 17:00', 'Público'),
('CEN003', 'Centro Materno Infantil Surco', 'Av. Caminos del Inca 730, Urb. Los Álamos', 'Santiago de Surco', 'Lima', 'Lima', '012345678', 'Lunes a Sábado 07:00 - 18:00', 'Público'),
('CEN004', 'Centro de Salud Jesús María', 'Av. Horacio Urteaga 950, Residencial San Felipe', 'Jesús María', 'Lima', 'Lima', '014637281', 'Lunes a Viernes 07:00 - 17:00', 'Público'),
('CEN005', 'Policlínico Municipal Los Olivos', 'Av. Carlos Izaguirre 820, Urb. Sol de Oro', 'Los Olivos', 'Lima', 'Lima', '015238476', 'Lunes a Sábado 07:00 - 19:00', 'Público'),
('CEN006', 'Centro Médico Integral Villa El Salvador', 'Av. Revolución 1530, Sector 3 Grupo 10', 'Villa El Salvador', 'Lima', 'Lima', '012938475', 'Lunes a Viernes 07:00 - 17:00', 'Público'),
('CEN007', 'Centro de Salud Breña', 'Jr. Zorritos 1234', 'Breña', 'Lima', 'Lima', '014298763', 'Lunes a Viernes 07:00 - 17:00', 'Público'),
('CEN008', 'Centro Materno Infantil Comas', 'Av. Túpac Amaru 3450', 'Comas', 'Lima', 'Lima', '015432187', 'Lunes a Sábado 07:00 - 18:00', 'Público');

-- --------------------------------------------------------
-- Tabla `especialidad`
-- --------------------------------------------------------

CREATE TABLE `especialidad` (
  `codesp` char(6) NOT NULL,
  `nomesp` varchar(50) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

INSERT INTO `especialidad` (`codesp`, `nomesp`, `descripcion`) VALUES
('ESP001', 'Medicina General', 'Atención primaria integral, diagnóstico y tratamiento de enfermedades comunes'),
('ESP002', 'Cardiología', 'Diagnóstico y tratamiento de enfermedades del corazón y sistema cardiovascular'),
('ESP003', 'Pediatría', 'Atención integral a niños, niñas y adolescentes'),
('ESP004', 'Traumatología y Ortopedia', 'Lesiones óseas, articulares, musculares y rehabilitación'),
('ESP005', 'Ginecología y Obstetricia', 'Salud reproductiva femenina, embarazo, parto y postparto'),
('ESP006', 'Dermatología', 'Enfermedades de la piel, cabello, uñas y tratamientos dermatológicos'),
('ESP007', 'Nutrición y Dietética', 'Evaluación nutricional, dietoterapia y prevención de enfermedades'),
('ESP008', 'Oftalmología', 'Diagnóstico y tratamiento de enfermedades oculares y corrección visual'),
('ESP009', 'Odontología', 'Salud bucodental, prevención, diagnóstico y tratamiento'),
('ESP010', 'Psicología Clínica', 'Evaluación, diagnóstico y terapia psicológica integral'),
('ESP011', 'Neurología', 'Enfermedades del sistema nervioso central y periférico'),
('ESP012', 'Otorrinolaringología', 'Enfermedades de oído, nariz, garganta y cabeza-cuello'),
('ESP013', 'Urología', 'Tracto urinario masculino y femenino, sistema reproductor masculino'),
('ESP014', 'Medicina Interna', 'Atención integral al adulto con múltiples patologías'),
('ESP015', 'Endocrinología', 'Trastornos hormonales, diabetes, tiroides y metabolismo'),
('ESP016', 'Gastroenterología', 'Enfermedades del aparato digestivo y hepatobiliares'),
('ESP017', 'Neumología', 'Enfermedades respiratorias, asma, EPOC y tuberculosis');

-- --------------------------------------------------------
-- Tabla `centro_especialidad`
-- --------------------------------------------------------

CREATE TABLE `centro_especialidad` (
  `codce` char(6) NOT NULL,
  `codesp` char(6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

INSERT INTO `centro_especialidad` (`codce`, `codesp`) VALUES
('CEN001', 'ESP001'),('CEN001', 'ESP003'),('CEN001', 'ESP008'),('CEN001', 'ESP009'),('CEN001', 'ESP010'),('CEN001', 'ESP014'),
('CEN002', 'ESP001'),('CEN002', 'ESP005'),('CEN002', 'ESP009'),('CEN002', 'ESP012'),('CEN002', 'ESP014'),('CEN002', 'ESP016'),
('CEN003', 'ESP001'),('CEN003', 'ESP003'),('CEN003', 'ESP005'),('CEN003', 'ESP008'),('CEN003', 'ESP010'),('CEN003', 'ESP015'),
('CEN004', 'ESP001'),('CEN004', 'ESP002'),('CEN004', 'ESP007'),('CEN004', 'ESP011'),('CEN004', 'ESP014'),('CEN004', 'ESP016'),
('CEN005', 'ESP001'),('CEN005', 'ESP002'),('CEN005', 'ESP003'),('CEN005', 'ESP006'),('CEN005', 'ESP009'),('CEN005', 'ESP013'),('CEN005', 'ESP017'),
('CEN006', 'ESP001'),('CEN006', 'ESP004'),('CEN006', 'ESP005'),('CEN006', 'ESP010'),('CEN006', 'ESP012'),('CEN006', 'ESP007'),
('CEN007', 'ESP001'),('CEN007', 'ESP003'),('CEN007', 'ESP009'),('CEN007', 'ESP010'),('CEN007', 'ESP014'),
('CEN008', 'ESP001'),('CEN008', 'ESP003'),('CEN008', 'ESP005'),('CEN008', 'ESP007'),('CEN008', 'ESP008'),('CEN008', 'ESP009'),('CEN008', 'ESP017');

-- --------------------------------------------------------
-- Tabla `medico`
-- --------------------------------------------------------

CREATE TABLE `medico` (
  `codm` char(6) NOT NULL,
  `nomm` varchar(80) NOT NULL,
  `cmpm` varchar(10) NOT NULL,
  `codesp` char(6) NOT NULL,
  `dni` char(8) DEFAULT NULL,
  `telefono` varchar(9) DEFAULT NULL,
  `correo` varchar(80) DEFAULT NULL,
  `anios_experiencia` int(3) DEFAULT 0,
  `formacion` varchar(150) DEFAULT NULL,
  `contrasena` varchar(60) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

INSERT INTO `medico` (`codm`, `nomm`, `cmpm`, `codesp`, `dni`, `telefono`, `correo`, `anios_experiencia`, `formacion`) VALUES
('MED001', 'Dra. Ana Torres Mendoza', '10234', 'ESP001', '07896543', '987123456', 'ana.torres@primelab.pe', 15, 'UNMSM - Medicina Humana'),
('MED002', 'Dr. Luis Alberto Mendoza Quispe', '18542', 'ESP002', '09234156', '986234567', 'luis.mendoza@primelab.pe', 18, 'UNMSM - Medicina Humana, Especialización en Cardiología'),
('MED003', 'Dra. Rosa María Salazar Huamán', '21306', 'ESP003', '05678912', '985345678', 'rosa.salazar@primelab.pe', 12, 'UPCH - Medicina Humana, Especialización en Pediatría'),
('MED004', 'Dr. Carlos Eduardo Ramírez Castro', '24618', 'ESP004', '06789123', '984456789', 'carlos.ramirez@primelab.pe', 16, 'UNMSM - Traumatología y Ortopedia'),
('MED005', 'Dra. Elena Beatriz Paredes López', '27190', 'ESP005', '07891234', '983567890', 'elena.paredes@primelab.pe', 14, 'UPCH - Ginecología y Obstetricia'),
('MED006', 'Dr. Miguel Ángel Cárdenas Vega', '30412', 'ESP006', '08912345', '982678901', 'miguel.cardenas@primelab.pe', 10, 'UNMSM - Dermatología'),
('MED007', 'Dra. Patricia Lucía León Gutiérrez', '32874', 'ESP007', '09123456', '981789012', 'patricia.leon@primelab.pe', 9, 'UPCH - Nutrición Clínica'),
('MED008', 'Dr. Javier Fernando Quispe Yupanqui', '35210', 'ESP008', '09234567', '980890123', 'javier.quispe@primelab.pe', 11, 'UNMSM - Oftalmología'),
('MED009', 'Dra. Carmen Rosa Flores Aguirre', '36741', 'ESP009', '09345678', '979901234', 'carmen.flores@primelab.pe', 13, 'UPCH - Estomatología/Odontología'),
('MED010', 'Dr. Ricardo Martín Huamán Córdova', '38902', 'ESP010', '09456789', '978012345', 'ricardo.huaman@primelab.pe', 12, 'UNMSM - Psicología Clínica'),
('MED011', 'Dra. Sofía Alejandra Vargas Ponce', '40156', 'ESP011', '09567890', '977123456', 'sofia.vargas@primelab.pe', 17, 'UNMSM - Neurología'),
('MED012', 'Dr. Diego Armando Castillo Rojas', '42588', 'ESP012', '09678901', '976234567', 'diego.castillo@primelab.pe', 14, 'UPCH - Otorrinolaringología'),
('MED013', 'Dr. Fernando José Tello Morales', '44213', 'ESP013', '09789012', '975345678', 'fernando.tello@primelab.pe', 16, 'UNMSM - Urología'),
('MED014', 'Dra. Gabriela Inés Ortiz Villanueva', '45876', 'ESP014', '09890123', '974456789', 'gabriela.ortiz@primelab.pe', 19, 'UNMSM - Medicina Interna'),
('MED015', 'Dr. Hugo César Reyes Bustamante', '47624', 'ESP015', '09901234', '973567890', 'hugo.reyes@primelab.pe', 15, 'UPCH - Endocrinología'),
('MED016', 'Dra. Lucía Fernanda Prado Salinas', '49238', 'ESP016', '08123456', '972678901', 'lucia.prado@primelab.pe', 13, 'UNMSM - Gastroenterología'),
('MED017', 'Dr. Andrés Felipe Mena Zegarra', '51692', 'ESP017', '08234567', '971789012', 'andres.mena@primelab.pe', 14, 'UPCH - Neumología');

-- Contraseña inicial de demostración de los médicos (hash). Cámbiala desde el panel del médico.
UPDATE `medico` SET `contrasena` = '$2y$10$Gw9gsgBRiDIu8HdIRqDU/./TrDgUgxMoVvU7tr4F30XZ170rxRkpm';

-- --------------------------------------------------------
-- Tabla `paciente`
-- --------------------------------------------------------

CREATE TABLE `paciente` (
  `codp` char(6) NOT NULL,
  `nomp` varchar(40) NOT NULL,
  `apep` varchar(40) NOT NULL,
  `dni` char(8) NOT NULL,
  `telp` char(9) DEFAULT NULL,
  `correo` varchar(80) DEFAULT NULL,
  `contrasena` varchar(60) DEFAULT NULL,
  `seguro` varchar(10) NOT NULL DEFAULT 'SIS',
  `fechanac` date DEFAULT NULL,
  `direccion` varchar(120) DEFAULT NULL,
  `distrito` varchar(50) DEFAULT NULL,
  `fechareg` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

INSERT INTO `paciente` (`codp`, `nomp`, `apep`, `dni`, `telp`, `correo`, `contrasena`, `seguro`, `fechanac`, `direccion`, `distrito`, `fechareg`) VALUES
('PAC001', 'Ana', 'Torres Mendoza', '45678912', '987654321', 'ana.torres@correo.com', '$2y$10$8M./t7ZcBXAKC0xBcPTcpuWLoZ8rNIouEEpHvAJV.8F3e3akiPFvS', 'SIS', '1985-04-12', 'Av. Tacna 456', 'Cercado de Lima', '2026-11-01'),
('PAC002', 'Luis', 'Ramos Cárdenas', '41234567', '912345678', 'luis.ramos@correo.com', '$2y$10$WYljiz.HAh49vk6XJeIqm.f.tgKIHLnsZC5MW62u8n79Ijo4vWP42', 'EPS', '1990-08-25', 'Jr. Amazonas 789', 'San Juan de Lurigancho', '2026-11-05'),
('PAC003', 'Eidan', 'Valencia Zamora', '61161158', '994784726', 'estu61161158@institutobolivar.edu.pe', '$2y$10$4ipZtydN6241e4tLxK7lsuJCQ2YMpfYX1.kF2pF1A.NsGLz4QV8/y', 'Privado', '1992-12-03', 'Av. Benavides 1234', 'Santiago de Surco', '2026-11-12'),
('PAC004', 'María', 'Gonzales Flores', '42345678', '965432187', 'maria.gonzales@correo.com', '$2y$10$RmNFlm.mgpgepiJiaz6Wj.H3clkCbPTIMvdbSmN0sST25PAiW0mF2', 'SIS', '1978-06-18', 'Calle Los Pinos 234', 'Los Olivos', '2026-11-13'),
('PAC005', 'José', 'Pérez Rodríguez', '43456789', '954321876', 'jose.perez@correo.com', '$2y$10$Hh7j06mvDafxuPUWqTn6duu4ve.rVkhy6gxc1LihZDjhA9EQBGWGC', 'EPS', '1982-03-09', 'Av. Universitaria 567', 'Comas', '2026-11-14'),
('PAC006', 'Carmen', 'López Vásquez', '44567890', '943218765', 'carmen.lopez@correo.com', '$2y$10$yUyXdFgb58ifflN.HbE8ZOozTDzrobTdO/j.U0NvPGHIRkP9InZLW', 'Privado', '1988-11-22', 'Jr. Huancayo 890', 'Breña', '2026-11-15'),
('PAC007', 'Pedro', 'Martínez Silva', '45678901', '932187654', 'pedro.martinez@correo.com', '$2y$10$/MW5ZVgZECbP4jZHt77GV.DH89kZ76CZnlGpnlDdeqCF0NEwCcF.6', 'SIS', '1995-07-14', 'Av. El Sol 321', 'Villa El Salvador', '2026-11-16'),
('PAC008', 'Rosa', 'Castillo Herrera', '46789012', '921876543', 'rosa.castillo@correo.com', '$2y$10$0SfYAW.p3ye4XMf3IV7qpepwHu5SIkPE8axgz2EnWS6YGBcaX9say', 'EPS', '1986-09-30', 'Calle Las Flores 654', 'Jesús María', '2026-11-17'),
('PAC009', 'Ademir Andrew Alberto', 'Sanchez Chinchay', '61216468', '972135089', 'estu61216468@institutobolivar.edu.pe', '$2y$10$f7DPpJRKevO29vTGSU0IHuAYRIu5mIfHo56sG1UL60FgghFmt4ypy', 'SIS', NULL, NULL, NULL, '2026-11-16'),
('PAC010', 'Fabbio Giuliano Abedi', 'Flores Padilla', '74519247', '977473318', 'estu74519247@institutobolivar.edu.pe', '$2y$10$QOk/g1X7ChE4C8DDQOjzcOoeHIFfHPmmNm1/cBv7Vo2dyBsbn1IE2', 'SIS', NULL, NULL, NULL, '2026-11-16'),
('PAC011', 'Alexandro David', 'Villachica Rodriguez', '72850117', '987751145', 'estu72850117@institutobolivar.edu.pe', '$2y$10$8fSzEZqqA3qHBouGRR.L6.JBl6mOmSjtXf97EVewdrqQWsHCGP72S', 'SIS', NULL, NULL, NULL, '2026-11-16'),
('PAC012', 'Leandro Francesco', 'Alvarez Marres', '75532819', '902323479', 'estu75532819@institutobolivar.edu.pe', '$2y$10$51AlehEVrqhoshiYC2BU5OxfnnSF/KRIQHe0P79cLV7EVu00hWlF.', 'SIS', NULL, NULL, NULL, '2026-11-16'),
('PAC013', 'Humberto Vicente', 'Portal Retamoso', '72484597', '969424717', 'estu72484597@institutobolivar.edu.pe', '$2y$10$J1bFv6lwYbwQOK0ZTITpL.p0PeM7oFZ7sn2VeL/InJY69Gpy6fCOy', 'SIS', NULL, NULL, NULL, '2026-11-16'),
('PAC014', 'Estuar Sebastian', 'Aparco Bedia', '72689307', '904866021', 'estu72689307@institutobolivar.edu.pe', '$2y$10$/2jzBqUwIyUzF6YgLiUVZOnl2ADi36BlZsr0zEdnKXU/1Y1SEeO1G', 'SIS', NULL, NULL, NULL, '2026-11-16'),
('PAC015', 'Ana', 'García Chávez', '10000000', '900100000', 'usuario01@example.com', '$2y$10$Lg89IXYcAOWXs0KkdhU4duIY1PuDmmUX83WR18BK6OCQcxcVsp6Fy', 'SIS', NULL, NULL, NULL, '2026-11-18'),
('PAC016', 'Luis', 'Rodríguez Ortiz', '10000001', '900100001', 'usuario02@example.com', '$2y$10$i/Do3NCfx2SAY4JzJxoQNOBXNBIScTavy2.JueU7qU8Daanlms7/q', 'SIS', NULL, NULL, NULL, '2026-11-18'),
('PAC017', 'María', 'Pérez Silva', '10000002', '900100002', 'usuario03@example.com', '$2y$10$AD940PMMrspVrEYS2TLKQOPDqhWgqURN3f/eVzX.uP8GsKpAnsuV6', 'SIS', NULL, NULL, NULL, '2026-11-18'),
('PAC018', 'Carlos', 'López Cárdenas', '10000003', '900100003', 'usuario04@example.com', '$2y$10$8hQ8kDHpdq2X9vKWFk4nguY6HgDCP.KDgmVQoMiqWI1GshdbFm.Jm', 'SIS', NULL, NULL, NULL, '2026-11-18'),
('PAC019', 'Sofía', 'Torres Cabrera', '10000004', '900100004', 'usuario05@example.com', '$2y$10$WVZuRtqkvPRd86QTqxi/cuEDcuvPpKRlskbTqK9leFpkpUzsEBjJG', 'SIS', NULL, NULL, NULL, '2026-11-18'),
('PAC020', 'José', 'Flores Tapia', '10000005', '900100005', 'usuario06@example.com', '$2y$10$Afg43kQb8CKHXD80UnFohOaal8O.I1Y9V/hh4.HZc7OJQnxCoyXeK', 'SIS', NULL, NULL, NULL, '2026-11-18'),
('PAC021', 'Valeria', 'Ramírez Flores', '10000006', '900100006', 'usuario07@example.com', '$2y$10$D92QaRUED5WBfpvOZDlMk.bcLBmg1zSmxwl.LwWaGyCmXWqKChMQG', 'SIS', NULL, NULL, NULL, '2026-11-18'),
('PAC022', 'Miguel', 'Sánchez Mendoza', '10000007', '900100007', 'usuario08@example.com', '$2y$10$XmJgeMLl3v9ItU4KjYSX9Oh42Kk2d48upebBKHXpCBiZz88Sh.v5m', 'SIS', NULL, NULL, NULL, '2026-11-18'),
('PAC023', 'Camila', 'Díaz Morales', '10000008', '900100008', 'usuario09@example.com', '$2y$10$Y4RVu.CWJdHNEGF76A8CMOg8MvMRlqssCeyANhBkoUyUH.FyzzZnW', 'SIS', NULL, NULL, NULL, '2026-11-18'),
('PAC024', 'Jorge', 'Castro Huamán', '10000009', '900100009', 'usuario10@example.com', '$2y$10$C91UF2uIRAncCHbsefXJaec45pLxw/VkBzR/X1D3c3PlP40NZWqzy', 'SIS', NULL, NULL, NULL, '2026-11-18'),
('PAC025', 'Lucía', 'Vargas Salazar', '10000010', '900100010', 'usuario11@example.com', '$2y$10$aYnJxkBfLxmKAqqhp2qvt.Zzyp2IMKA/n13IXJXlLzUxgnNi.SYKO', 'SIS', NULL, NULL, NULL, '2026-11-18'),
('PAC026', 'Diego', 'Rojas Valdez', '10000011', '900100011', 'usuario12@example.com', '$2y$10$siHHRg.QnfGZn8IiwAVNKu0/6IEPpJkr53UuL3lzua5hLEA8Vi1Xe', 'SIS', NULL, NULL, NULL, '2026-11-18'),
('PAC027', 'Daniela', 'Mendoza Palacios', '10000012', '900100012', 'usuario13@example.com', '$2y$10$OvxIgs13RmqzdBRH5ekys.hyLAK2bUSlKPIc87xre42WYaJr42Sea', 'SIS', NULL, NULL, NULL, '2026-11-18'),
('PAC028', 'Pedro', 'Chávez Torres', '10000013', '900100013', 'usuario14@example.com', '$2y$10$8sHBxO/0Mse2hZIS2J9GPOlP6y9DA3Rd8QlduOA/4A3q9DRC6Q4I.', 'SIS', NULL, NULL, NULL, '2026-11-18'),
('PAC029', 'Andrea', 'Ruiz Rojas', '10000014', '900100014', 'usuario15@example.com', '$2y$10$Dw0B1SpZ4Ss.YQZMNgp2U.XdJwLg9uzsCGBOVGFwJBEmtrp8mMpEq', 'SIS', NULL, NULL, NULL, '2026-11-18'),
('PAC030', 'Ricardo', 'Herrera Reyes', '10000015', '900100015', 'usuario16@example.com', '$2y$10$oYL3LHYWZTEWuINVlJZYien9w.oIdlcJzn6ahZa8bZxBlFkdn.Ay2', 'SIS', NULL, NULL, NULL, '2026-11-18'),
('PAC031', 'Paola', 'Medina Quispe', '10000016', '900100016', 'usuario17@example.com', '$2y$10$wxZ4UM5l4dj2G2j.KcPu4.P49VADVUCJmZf7CLfvimQaankl/Y8Ai', 'SIS', NULL, NULL, NULL, '2026-11-18'),
('PAC032', 'Fernando', 'Cruz Navarro', '10000017', '900100017', 'usuario18@example.com', '$2y$10$SmPe9Mn4NZA2O55GDOhCx.KL9toVkjidrV.vmWy0jwJ.g2UHMrMI2', 'SIS', NULL, NULL, NULL, '2026-11-18'),
('PAC033', 'Gabriela', 'Reyes Peña', '10000018', '900100018', 'usuario19@example.com', '$2y$10$6f0iCEnr5HVXb44fcEq3x.sZp0uWTNZgHF5xloibGibGjfYorGCom', 'SIS', NULL, NULL, NULL, '2026-11-18'),
('PAC034', 'Alonso', 'Morales Suárez', '10000019', '900100019', 'usuario20@example.com', '$2y$10$f5khYYV.rIcvmfyE9EtqreIrnggixDmnWoNfuRf3Lsr6CzjI.qjOy', 'SIS', NULL, NULL, NULL, '2026-11-18'),
('PAC035', 'Claudia', 'Ortiz López', '10000020', '900100020', 'usuario21@example.com', '$2y$10$7V4Tbn1DfaKb/ZceR4fNSer4YIUK01Ow2Wt1eb54Dw9ohe0lnLxkC', 'SIS', NULL, NULL, NULL, '2026-11-18'),
('PAC036', 'Martín', 'Gutiérrez Vargas', '10000021', '900100021', 'usuario22@example.com', '$2y$10$F3OPlXNHaiQL18keKDE3Dufsvy1Rc6fU/bhCCNxGGJqvvzidqRiO6', 'SIS', NULL, NULL, NULL, '2026-11-18'),
('PAC037', 'Natalia', 'Vega Cruz', '10000022', '900100022', 'usuario23@example.com', '$2y$10$PYGJt1fwei.63kyS.tzPSesHS1F9PygnEEwJQiwEKVBlrotO5UZSa', 'SIS', NULL, NULL, NULL, '2026-11-18'),
('PAC038', 'Ángel', 'Ramos Paredes', '10000023', '900100023', 'usuario24@example.com', '$2y$10$LEuDGRcd99z5VodlY4YtqeXMbfw0LEx/96CJTKx3AXOGk9OBSpW0.', 'SIS', NULL, NULL, NULL, '2026-11-18'),
('PAC039', 'Rosa', 'Paredes Fernández', '10000024', '900100024', 'usuario25@example.com', '$2y$10$7FbVwXPCMU/J9a085yOYTutX8LbMnLvOicH51dTbBqHCFD2L2YE7.', 'SIS', NULL, NULL, NULL, '2026-11-18'),
('PAC040', 'Kevin', 'Quispe Molina', '10000025', '900100025', 'usuario26@example.com', '$2y$10$wq3K.Zn5irEGDvH/597XAe2Hzy3dkZOo8ihM1hQnmH9u03zCF4PZK', 'SIS', NULL, NULL, NULL, '2026-11-18'),
('PAC041', 'Fiorella', 'Huamán Miranda', '10000026', '900100026', 'usuario27@example.com', '$2y$10$EqEE4254uphK/KYmMip5.upFS/3m4gYwYK/q0VgVFjQO5MYJ82un2', 'SIS', NULL, NULL, NULL, '2026-11-18'),
('PAC042', 'Bruno', 'Silva Pérez', '10000027', '900100027', 'usuario28@example.com', '$2y$10$n6JpbaWw/EyC4g8SPjGk/ukpkLTrd2.bi3NVxXWl25M6/BgN6wHyu', 'SIS', NULL, NULL, NULL, '2026-11-18'),
('PAC043', 'Patricia', 'Espinoza Castro', '10000028', '900100028', 'usuario29@example.com', '$2y$10$/LPbBvAHBwWzaBRXcd0SIe4cLInHDPvHVjAFRthGRl3gpeEM1M0VC', 'SIS', NULL, NULL, NULL, '2026-11-18'),
('PAC044', 'Andrés', 'Campos Medina', '10000029', '900100029', 'usuario30@example.com', '$2y$10$OJvXzd7lCCe8KIoQk55teOs6ORL/oFBeXXFzUmbOncxbI2JBNX9BK', 'SIS', NULL, NULL, NULL, '2026-11-18'),
('PAC045', 'Milagros', 'Aguilar Ramos', '10000030', '900100030', 'usuario31@example.com', '$2y$10$DRLEjowZNEpnRqaz.iN6.OTaX/WLylb.1NHaIgNkdY/ZNiEPdDXTq', 'SIS', NULL, NULL, NULL, '2026-11-18'),
('PAC046', 'Eduardo', 'Fernández Aguilar', '10000031', '900100031', 'usuario32@example.com', '$2y$10$/xCvP5OgyXuJhybpkxkABeRz2ySQ5fDh04yjJJswJlIM6HmgzjqP2', 'SIS', NULL, NULL, NULL, '2026-11-18'),
('PAC047', 'Mónica', 'Navarro Acosta', '10000032', '900100032', 'usuario33@example.com', '$2y$10$aVreIedWkJ7V.gTlIOnOyuXSAky/7DNoEm4xJCdqDVX8EU.fuQnBu', 'SIS', NULL, NULL, NULL, '2026-11-18'),
('PAC048', 'Sebastián', 'Salazar León', '10000033', '900100033', 'usuario34@example.com', '$2y$10$wXtllT/Fi0oB4jwXYmq/JOK8f.C2aFuZXUZUJp9luzUMwS.cGtXUi', 'SIS', NULL, NULL, NULL, '2026-11-18'),
('PAC049', 'Ximena', 'Cárdenas Rodríguez', '10000034', '900100034', 'usuario35@example.com', '$2y$10$UG.E3i3ce4kZxySnv/tSk.pC8hVQmfAGoQp4mkiHeBxVdPf7xRuCW', 'SIS', NULL, NULL, NULL, '2026-11-18'),
('PAC050', 'Raúl', 'Delgado Díaz', '10000035', '900100035', 'usuario36@example.com', '$2y$10$b5aSPAvOliRVBzHeN0Ela.eeu/egI.dzId5iQwwNrbS4R5FO1srDe', 'SIS', NULL, NULL, NULL, '2026-11-18'),
('PAC051', 'Isabel', 'Romero Herrera', '10000036', '900100036', 'usuario37@example.com', '$2y$10$KnqdNiyFDKMf2yhbwFCYweUYq5XJDcMN2luwOi4szsKgbTwjA43XO', 'SIS', NULL, NULL, NULL, '2026-11-18'),
('PAC052', 'Franco', 'Acosta Vega', '10000037', '900100037', 'usuario38@example.com', '$2y$10$rYoUhqQbRLmjlXrqTwwtde9MvFNAx19VJb7Yp0tfCTYyow59suMWe', 'SIS', NULL, NULL, NULL, '2026-11-18'),
('PAC053', 'Elena', 'Molina Campos', '10000038', '900100038', 'usuario39@example.com', '$2y$10$ZWFSGmyX5sD2jHxe5JXe3ecgWP0ldhix78gek0UyqwAVDaSs0hx32', 'SIS', NULL, NULL, NULL, '2026-11-18'),
('PAC054', 'Marco', 'Peña Romero', '10000039', '900100039', 'usuario40@example.com', '$2y$10$3lMVXTKXHvux0Y8dLBtRduU4hXujUoQZIIEc5/59ohzFfe/WLgBoC', 'SIS', NULL, NULL, NULL, '2026-11-18'),
('PAC055', 'Carmen', 'Valdez Fuentes', '10000040', '900100040', 'usuario41@example.com', '$2y$10$XFc1aZn9yI3bOgodzRW59.Mpmq3BWAW4B6XmCy0/KJyRAQpXwwPfS', 'SIS', NULL, NULL, NULL, '2026-11-18'),
('PAC056', 'Óscar', 'Cabrera García', '10000041', '900100041', 'usuario42@example.com', '$2y$10$n3ONrrNSThAffrrYb6KPj.9.DcXRawGijObzeoS9rNid8LFSlN82e', 'SIS', NULL, NULL, NULL, '2026-11-18'),
('PAC057', 'Adriana', 'Bravo Sánchez', '10000042', '900100042', 'usuario43@example.com', '$2y$10$sh7KaKoAlDVmuILZMxYV6O54A39b9N2e715NvUTXMAFo3xkhz5iBK', 'SIS', NULL, NULL, NULL, '2026-11-18'),
('PAC058', 'Piero', 'Fuentes Ruiz', '10000043', '900100043', 'usuario44@example.com', '$2y$10$fuTslS.h4tcg.OvTizp33uEwbjQPOlO5B4wuwcLCDG322L0TkwVT.', 'SIS', NULL, NULL, NULL, '2026-11-18'),
('PAC059', 'Silvia', 'León Gutiérrez', '10000044', '900100044', 'usuario45@example.com', '$2y$10$VQevtPFTZ5DJHxODFIyH4OuUSWte.Z2zpu3IrMBPbg35A6pjqxDze', 'SIS', NULL, NULL, NULL, '2026-11-18'),
('PAC060', 'Renzo', 'Miranda Espinoza', '10000045', '900100045', 'usuario46@example.com', '$2y$10$KTspPDpOIMTQcd6CFir68OydnJtQla3gYj59uQzjNR6AjW5NdG/YK', 'SIS', NULL, NULL, NULL, '2026-11-18'),
('PAC061', 'Teresa', 'Suárez Delgado', '10000046', '900100046', 'usuario47@example.com', '$2y$10$Ob6Y.hXtrj0fwOnwNDVmzuyUGitB6ZkUb8WP9j4W1iu4F/izaPr46', 'SIS', NULL, NULL, NULL, '2026-11-18'),
('PAC062', 'Gustavo', 'Palacios Bravo', '10000047', '900100047', 'usuario48@example.com', '$2y$10$u4s1uHXnwXsV7ycwKrV3lOUvFIAxq0CHfWVTxYE9T9F245dmOy2fC', 'SIS', NULL, NULL, NULL, '2026-11-18'),
('PAC063', 'Juliana', 'Tapia Núñez', '10000048', '900100048', 'usuario49@example.com', '$2y$10$Py84UPUhTsQvOxDDVVwYJe/wWk8w69HQmwGujVIgyE9CRQd.UH.BO', 'SIS', NULL, NULL, NULL, '2026-11-18'),
('PAC064', 'Héctor', 'Núñez Ramírez', '10000049', '900100049', 'usuario50@example.com', '$2y$10$jTT9.6Hn7XcUzoajW5ao2OHtZ06jF5dNmmG2vH.PdCkxaonNNXqNW', 'SIS', NULL, NULL, NULL, '2026-11-18');

-- --------------------------------------------------------
-- Tabla `administrador`
-- --------------------------------------------------------

CREATE TABLE `administrador` (
  `idadm` int(11) NOT NULL AUTO_INCREMENT,
  `usuario` varchar(30) NOT NULL,
  `correo` varchar(80) NOT NULL,
  `contrasena` varchar(60) NOT NULL,
  `nombre` varchar(80) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`idadm`),
  UNIQUE KEY `uq_admin_usuario` (`usuario`),
  UNIQUE KEY `uq_admin_correo` (`correo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

INSERT INTO `administrador` (`idadm`, `usuario`, `correo`, `contrasena`, `nombre`, `activo`) VALUES
(1, 'prime', 'primelab@centrosalud.pe', '$2y$10$tPsWC630D3zhX8rmTU/tLe5Eg5rF5ypx1yqFpW7SAa122sS81UteS', 'Administrador principal', 1),
(2, 'admin2', 'gestion@centrosalud.pe', '$2y$10$xlJPf6l7Jt/H5XAROOfe2.iUZnn2ezWBc0m0cGPWHtx9OkRSiJgJO', 'Coordinación de citas', 1),
(3, 'admin3', 'soporte@centrosalud.pe', '$2y$10$xlJPf6l7Jt/H5XAROOfe2.iUZnn2ezWBc0m0cGPWHtx9OkRSiJgJO', 'Soporte técnico', 1);

-- --------------------------------------------------------
-- Tabla `cita`
-- --------------------------------------------------------

CREATE TABLE `cita` (
  `codci` char(6) NOT NULL,
  `fechaci` date NOT NULL,
  `horaci` time NOT NULL,
  `motivo` varchar(255) NOT NULL,
  `estado` varchar(15) NOT NULL DEFAULT 'pendiente',
  `costo` decimal(8,2) NOT NULL DEFAULT 0.00,
  `codp` char(6) NOT NULL,
  `codm` char(6) NOT NULL,
  `codce` char(6) NOT NULL,
  `fechareg` datetime NOT NULL,
  `observaciones` varchar(255) DEFAULT NULL,
  `diagnostico` text DEFAULT NULL,
  `indicaciones` text DEFAULT NULL,
  `motivo_reprogramacion` varchar(255) DEFAULT NULL,
  `reprogramada_de` char(6) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

INSERT INTO `cita` (`codci`, `fechaci`, `horaci`, `motivo`, `estado`, `costo`, `codp`, `codm`, `codce`, `fechareg`, `observaciones`, `diagnostico`, `indicaciones`, `motivo_reprogramacion`, `reprogramada_de`) VALUES
('CIT001', '2026-11-30', '09:00:00', 'Control general y dolor de cabeza recurrente por 3 días.', 'pendiente', 0.00, 'PAC001', 'MED001', 'CEN001', '2026-11-08 10:15:00', NULL, NULL, NULL, NULL, NULL),
('CIT002', '2026-12-01', '10:30:00', 'Evaluación pediátrica de control anual. Crecimiento y desarrollo.', 'pendiente', 20.00, 'PAC002', 'MED003', 'CEN003', '2026-11-09 16:40:00', NULL, NULL, NULL, NULL, NULL),
('CIT003', '2026-10-30', '08:00:00', 'Chequeo cardiológico anual - Electrocardiograma preventivo.', 'cancelada', 0.00, 'PAC001', 'MED002', 'CEN004', '2026-10-25 09:05:00', 'Cancelada por el paciente - Motivos laborales', NULL, NULL, 'Inasistencia del paciente', NULL),
('CIT004', '2026-11-26', '11:00:00', 'Consulta por malestar general, fiebre y dolor muscular.', 'pendiente', 50.00, 'PAC003', 'MED002', 'CEN004', '2026-11-12 14:51:49', NULL, NULL, NULL, NULL, NULL),
('CIT005', '2026-12-02', '09:30:00', 'Control de diabetes mellitus tipo 2. Revisión de exámenes.', 'pendiente', 0.00, 'PAC004', 'MED014', 'CEN001', '2026-11-13 08:20:00', NULL, NULL, NULL, NULL, NULL),
('CIT006', '2026-12-03', '14:00:00', 'Consulta ginecológica - Control prenatal rutinario.', 'pendiente', 20.00, 'PAC005', 'MED005', 'CEN002', '2026-11-14 11:35:00', NULL, NULL, NULL, NULL, NULL),
('CIT007', '2026-11-24', '15:30:00', 'Evaluación dermatológica - Lesión cutánea en brazo derecho.', 'pendiente', 50.00, 'PAC006', 'MED006', 'CEN005', '2026-11-15 09:45:00', NULL, NULL, NULL, NULL, NULL),
('CIT008', '2026-11-21', '10:00:00', 'Consulta odontológica - Dolor en molar inferior derecho.', 'atendida', 0.00, 'PAC007', 'MED009', 'CEN008', '2026-11-16 13:10:00', 'Atención completada', 'Caries dental en pieza 46, inflamación gingival leve.', 'Obturación realizada, ibuprofeno 400 mg cada 8h por 3 días, enjuague bucal y control en 15 días.', NULL, NULL),
('CIT009', '2026-11-25', '16:00:00', 'Evaluación nutricional para control de peso.', 'pendiente', 20.00, 'PAC008', 'MED007', 'CEN004', '2026-11-17 10:25:00', NULL, NULL, NULL, NULL, NULL),
('CIT010', '2026-11-27', '08:30:00', 'Consulta traumatológica - Dolor de rodilla tras caída.', 'pendiente', 50.00, 'PAC003', 'MED004', 'CEN006', '2026-11-18 15:40:00', NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------
-- Tabla `cita_historial`
-- --------------------------------------------------------

CREATE TABLE `cita_historial` (
  `idh` int(11) NOT NULL,
  `codci` char(6) NOT NULL,
  `accion` varchar(20) NOT NULL,
  `motivo` varchar(255) NOT NULL,
  `fecha` datetime NOT NULL,
  `usuario_accion` varchar(40) DEFAULT 'Sistema'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

INSERT INTO `cita_historial` (`idh`, `codci`, `accion`, `motivo`, `fecha`, `usuario_accion`) VALUES
(1, 'CIT003', 'cancelacion', 'El paciente no pudo asistir por motivos laborales.', '2026-10-28 18:20:00', 'PAC001');

-- --------------------------------------------------------
-- Índices
-- --------------------------------------------------------

ALTER TABLE `centro`
  ADD PRIMARY KEY (`codce`);

ALTER TABLE `especialidad`
  ADD PRIMARY KEY (`codesp`);

ALTER TABLE `centro_especialidad`
  ADD PRIMARY KEY (`codce`,`codesp`),
  ADD KEY `fk_ce_especialidad` (`codesp`);

ALTER TABLE `medico`
  ADD PRIMARY KEY (`codm`),
  ADD UNIQUE KEY `uq_medico_cmp` (`cmpm`),
  ADD KEY `fk_medico_especialidad` (`codesp`);

ALTER TABLE `paciente`
  ADD PRIMARY KEY (`codp`),
  ADD UNIQUE KEY `uq_paciente_dni` (`dni`),
  ADD UNIQUE KEY `uq_paciente_correo` (`correo`);

ALTER TABLE `cita`
  ADD PRIMARY KEY (`codci`),
  ADD KEY `fk_cita_paciente` (`codp`),
  ADD KEY `fk_cita_medico` (`codm`),
  ADD KEY `fk_cita_centro` (`codce`),
  ADD KEY `ix_cita_agenda` (`codm`,`fechaci`,`horaci`);

ALTER TABLE `cita_historial`
  ADD PRIMARY KEY (`idh`),
  ADD KEY `fk_historial_cita` (`codci`);

-- --------------------------------------------------------
-- AUTO_INCREMENT
-- --------------------------------------------------------

ALTER TABLE `cita_historial`
  MODIFY `idh` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

-- --------------------------------------------------------
-- Restricciones (claves foráneas)
-- --------------------------------------------------------

ALTER TABLE `centro_especialidad`
  ADD CONSTRAINT `fk_ce_centro` FOREIGN KEY (`codce`) REFERENCES `centro` (`codce`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ce_especialidad` FOREIGN KEY (`codesp`) REFERENCES `especialidad` (`codesp`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `medico`
  ADD CONSTRAINT `fk_medico_especialidad` FOREIGN KEY (`codesp`) REFERENCES `especialidad` (`codesp`) ON UPDATE CASCADE;

ALTER TABLE `cita`
  ADD CONSTRAINT `fk_cita_centro` FOREIGN KEY (`codce`) REFERENCES `centro` (`codce`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_cita_medico` FOREIGN KEY (`codm`) REFERENCES `medico` (`codm`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_cita_paciente` FOREIGN KEY (`codp`) REFERENCES `paciente` (`codp`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `cita_historial`
  ADD CONSTRAINT `fk_historial_cita` FOREIGN KEY (`codci`) REFERENCES `cita` (`codci`) ON DELETE CASCADE ON UPDATE CASCADE;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
