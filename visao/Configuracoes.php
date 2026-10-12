<?php
session_start();
if (empty($_SESSION['usuario_id'])) { header('Location: Login.php'); exit; }
$voltar = ($_SESSION['usuario_tipo'] ?? '') === 'dono' ? 'PaginaInicialDono.php' : 'PaginaInicialFreela.php';
?>
<!doctype html><html lang="pt-br"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Configurações - WorkFlow</title><link rel="stylesheet" href="../css/style.css?v=76c42393c7"></head>
<body><section id="boxperfil"><header><a href="<?php echo $voltar; ?>" aria-label="Voltar"><img src="../imgs/gravity-ui--arrow-left.svg" alt="Voltar"></a><h3>Configurações</h3></header><main><p>Esta página será implementada futuramente.</p></main></section></body></html>
