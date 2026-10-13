<?php
session_start();
if (empty($_SESSION['usuario_id']) || ($_SESSION['usuario_tipo'] ?? '') !== 'dono') { header('Location: Login.php');exit; }
require_once '../bd/funcoesBD.php';
require_once '../config/seguranca.php';
$turno = buscarTurnoDoDono((int)($_GET['id']??0), (int)$_SESSION['usuario_id']);
if (!$turno) { http_response_code(404); die('Turno nao encontrado ou sem permissao.'); }
?>
<!doctype html><html lang="pt-br"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><link rel="stylesheet" href="../css/style.css?v=76c42393c7"><title>Editar turno</title></head>
<body><section id="boxperfil"><header><a href="PaginaInicialDono.php" aria-label="Voltar"><img src="../imgs/gravity-ui--arrow-left.svg" alt="Voltar"></a><h3>Editar turno</h3></header>
<form id="formTurno" method="post" action="../logica/processamento.php">
<input type="hidden" name="turno_id" value="<?php echo (int)$turno['id']; ?>"><input type="hidden" name="csrf_token" value="<?php echo esc(tokenCsrf()); ?>">
<p>Empresa: <strong><?php echo esc($turno['empresa_nome']); ?></strong></p>
<div class="campo"><label for="data">Data</label><input id="data" type="date" name="data" required value="<?php echo esc($turno['data_turno']); ?>"></div>
<div class="campo"><label for="inicio">Horário de início</label><input id="inicio" type="time" name="inicio" required value="<?php echo esc(substr($turno['horario_inicio'],0,5)); ?>"></div>
<div class="campo"><label for="fim">Horário de término</label><input id="fim" type="time" name="fim" required value="<?php echo esc(substr($turno['horario_fim'] ?? '',0,5)); ?>"></div>
<div class="campo"><label for="cargo">Função</label><input id="cargo" type="text" maxlength="50" name="cargo" required value="<?php echo esc($turno['funcao']); ?>"></div>
<div class="campo"><label for="valor">Valor (R$)</label><input id="valor" type="number" min="0" step="0.01" name="valor" required value="<?php echo esc($turno['valor']); ?>"></div>
<div class="campo"><label for="descricao">Descrição</label><textarea id="descricao" maxlength="150" name="descricao" required><?php echo esc($turno['descricao']); ?></textarea></div>
<p>Os freelancers convidados e a quantidade de vagas permanecem os mesmos nesta edição.</p>
<button class="btn" id="submit" type="submit" name="btn_editar_turno">Salvar alterações</button>
</form></section></body></html>
