-- Execute no banco gestao_freelas EXISTENTE, pela aba SQL do phpMyAdmin.
-- Seguro para rodar novamente: cria tabelas somente quando ainda nao existem.
USE `gestao_freelas`;

CREATE TABLE IF NOT EXISTS `redefinicoes_senha` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `usuario_id` int NOT NULL,
  `token_hash` varchar(255) NOT NULL,
  `expira_em` datetime NOT NULL,
  `usado` tinyint(1) NOT NULL DEFAULT 0,
  `criado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX (`usuario_id`),
  CONSTRAINT `fk_redef_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `notificacoes` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `usuario_id` int NOT NULL,
  `titulo` varchar(120) NOT NULL,
  `mensagem` varchar(255) NOT NULL,
  `link` varchar(160) NOT NULL,
  `lida` tinyint(1) NOT NULL DEFAULT 0,
  `criada_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX (`usuario_id`, `lida`),
  CONSTRAINT `fk_notif_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
