<?php
require_once "../bd/funcoesBD.php";

$token = $_GET['token'] ?? '';

if ($token === '') {
    header("Location: Login.php");
    exit();
}

$solicitacao = buscarSolicitacaoRedefinicaoValida($token);

if (!$solicitacao) {
    header("Location: EsqueciSenha.php?erro=token_invalido");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css">
    <title>Nova senha</title>
</head>
<body>
    <section id="box">
        <a href="Login.php">
            <img id="voltar" src="../imgs/gravity-ui--arrow-left.svg">
        </a>

        <h1>Nova senha</h1>

        <?php if(isset($_GET['erro']) && $_GET['erro'] == 'senhas_diferentes'): ?>
            <p style="color: #ff4d4d; text-align: center; font-weight: bold; font-size: 14px;">
                As senhas não conferem. Tente novamente.
            </p>
        <?php endif; ?>

        <form id="form" action="../logica/processamento.php" method="POST">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

            <h3>Nova senha</h3>
            <input type="password" name="nova_senha" required>

            <h3>Confirmar senha</h3>
            <input type="password" name="confirmar_senha" required>

            <button class="btn" id="submit" type="submit" name="btn_redefinir_senha">
                SALVAR NOVA SENHA
            </button>
        </form>
    </section>
</body>
</html>