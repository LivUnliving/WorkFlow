-- Banco de dados do sistema Gestão Freelas
-- Importe este arquivo no phpMyAdmin para criar a estrutura completa do projeto.

CREATE DATABASE IF NOT EXISTS `gestao_freelas` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `gestao_freelas`;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `candidaturas`;
DROP TABLE IF EXISTS `turnos`;
DROP TABLE IF EXISTS `experiencias`;
DROP TABLE IF EXISTS `usuario_empresas`;
DROP TABLE IF EXISTS `empresas`;
DROP TABLE IF EXISTS `usuarios`;
DROP TABLE IF EXISTS `usuario_empresas`;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `tipo` enum('dono','freelancer') NOT NULL,
  `status_vinculo` varchar(20) DEFAULT 'Sem vínculo',
  `cpf` varchar(14) DEFAULT NULL,
  `cnpj` varchar(18) DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `cidade` varchar(100) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `empresas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dono_id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `telefone` varchar(20) NOT NULL,
  `localizacao` varchar(150) NOT NULL,
  `cnpj` varchar(18) NOT NULL,
  `codigo_loja` varchar(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `codigo_loja` (`codigo_loja`),
  KEY `dono_id` (`dono_id`),
  CONSTRAINT `empresas_ibfk_1` FOREIGN KEY (`dono_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `usuario_empresas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `empresa_id` int(11) NOT NULL,
  `freelancer_id` int(11) NOT NULL,
  `status` enum('Pendente','Aprovado','Recusado') NOT NULL DEFAULT 'Pendente',
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuario_empresas_unico` (`empresa_id`, `freelancer_id`),
  KEY `freelancer_id` (`freelancer_id`),
  CONSTRAINT `usuario_empresas_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `usuario_empresas_ibfk_2` FOREIGN KEY (`freelancer_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `experiencias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `freelancer_id` int(11) NOT NULL,
  `cargo` varchar(50) NOT NULL,
  `tempo` varchar(30) NOT NULL,
  `descricao` varchar(250) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `freelancer_id` (`freelancer_id`),
  CONSTRAINT `experiencias_ibfk_1` FOREIGN KEY (`freelancer_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `turnos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `empresa_id` int(11) NOT NULL,
  `descricao` varchar(150) NOT NULL,
  `data_turno` date NOT NULL,
  `horario_inicio` time NOT NULL,
  `valor` decimal(10,2) NOT NULL DEFAULT 0.00,
  `funcao` varchar(50) NOT NULL,
  `vagas_necessarias` int(11) NOT NULL,
  `vagas_disponiveis` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `empresa_id` (`empresa_id`),
  CONSTRAINT `turnos_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `candidaturas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `turno_id` int(11) NOT NULL,
  `freelancer_id` int(11) NOT NULL,
  `status` enum('Pendente','Confirmado','Recusado') NOT NULL DEFAULT 'Pendente',
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `turno_freelancer_unico` (`turno_id`, `freelancer_id`),
  KEY `freelancer_id` (`freelancer_id`),
  CONSTRAINT `candidaturas_ibfk_1` FOREIGN KEY (`turno_id`) REFERENCES `turnos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `candidaturas_ibfk_2` FOREIGN KEY (`freelancer_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `usuario_empresas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `empresa_id` int(11) NOT NULL,
  `freelancer_id` int(11) NOT NULL,
  `status` enum('Pendente','Aprovado','Recusado') NOT NULL DEFAULT 'Pendente',
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuario_empresa_unico` (`empresa_id`, `freelancer_id`),
  KEY `freelancer_id` (`freelancer_id`),
  CONSTRAINT `usuario_empresas_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `usuario_empresas_ibfk_2` FOREIGN KEY (`freelancer_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
