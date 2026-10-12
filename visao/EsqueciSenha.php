<?php session_start(); ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css?v=76c42393c7">
    <title>Redefinir senha</title>
</head>
<body>
    <section id="box">
        <a href="Login.php">
            <img id="voltar" src="../imgs/gravity-ui--arrow-left.svg">
        </a>

        <h1>Redefinir senha</h1>
        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'enviado'): ?>
        <p class="aviso">Se o e-mail estiver cadastrado, você receberá um link de redefinição. Verifique sua caixa de entrada.</p>
        <?php if (!empty($_SESSION['demo_reset_url'])): ?>
        <p class="aviso"><strong>Modo demonstração local:</strong> <a href="<?php echo htmlspecialchars($_SESSION['demo_reset_url'], ENT_QUOTES, 'UTF-8'); ?>">Abrir link de teste</a>. Desative esse modo antes de publicar.</p>
        <?php endif; endif; ?>

        <?php if (isset($_GET['erro']) && $_GET['erro'] == 'email_nao_encontrado'): ?>
            <p style="color: #ff4d4d; text-align: center; font-weight: bold; font-size: 14px;">
                Não encontramos nenhuma conta cadastrada com esse e-mail.
            </p>
        <?php endif; ?>

        <?php if (isset($_GET['erro']) && $_GET['erro'] == 'token_invalido'): ?>
            <p style="color: #ff4d4d; text-align: center; font-weight: bold; font-size: 14px;">
                O link de redefinição é inválido ou expirou. Solicite um novo link.
            </p>
        <?php endif; ?>

        <form id="form" action="../logica/processamento.php" method="POST">
            <h3>Digite seu e-mail</h3>
            <input type="email" name="email_usuario" required>

            <button class="btn" id="submit" type="submit" name="btn_solicitar_redefinicao">
                ENVIAR
            </button>
        </form>
    </section>
</body>
</html>