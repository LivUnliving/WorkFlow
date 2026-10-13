-- SOMENTE se turnos ainda nao tiver a coluna horario_fim.
-- Verifique antes: SHOW COLUMNS FROM turnos LIKE 'horario_fim';
-- Se a coluna existir, NAO execute este arquivo.
USE `gestao_freelas`;
ALTER TABLE `turnos` ADD COLUMN `horario_fim` TIME NULL AFTER `horario_inicio`;
