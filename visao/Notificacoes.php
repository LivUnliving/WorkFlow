<?php
session_start();
if (empty($_SESSION['usuario_id'])) { header('Location: Login.php'); exit; }
require_once '../bd/funcoesBD.php';
require_once '../config/seguranca.php';
$notificacoes = retornarNotificacoes((int)$_SESSION['usuario_id']);
$voltar = ($_SESSION['usuario_tipo'] ?? '') === 'dono' ? 'PaginaInicialDono.php' : 'PaginaInicialFreela.php';
?>
<!doctype html><html lang="pt-br"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Notificações - WorkFlow</title><link rel="stylesheet" href="../css/style.css?v=76c42393c7"></head><body>
<section id="boxperfil"><header><a href="<?php echo $voltar; ?>" aria-label="Voltar"><img src="../imgs/gravity-ui--arrow-left.svg" alt="Voltar"></a><h3>Notificações</h3></header><main>
<?php if (!$notificacoes): ?><p>Nenhuma notificação por enquanto.</p><?php endif; ?>
<?php foreach ($notificacoes as $n): ?>
<article class="notificacao <?php echo !$n['lida']?'nao-lida':''; ?>">
<h4><?php echo esc($n['titulo']); ?></h4><p><?php echo esc($n['mensagem']); ?></p>
<small><?php echo esc($n['criada_em']); ?></small><p><a href="<?php echo esc($n['link']); ?>">Ver assunto</a></p>
<?php if (!$n['lida']): ?><form method="post" action="../logica/processamento.php"><input type="hidden" name="csrf_token" value="<?php echo esc(tokenCsrf()); ?>"><input type="hidden" name="notificacao_id" value="<?php echo (int)$n['id']; ?>"><button class="botao-acao" name="btn_marcar_notificacao">Marcar como lida</button></form><?php endif; ?>
</article>
<?php endforeach; ?>
</main></section></body></html>
