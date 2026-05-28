<?php
session_start();

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'freelancer') {
    header("Location: Login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css">
    <title>Sucesso - Nova Empresa</title>
</head>
<body>
    <section id="boxperfil">
        <header>
            <a href="NovaLoja.php">
                <img src="../imgs/gravity-ui--arrow-left.svg">
            </a>
            <h3>Sucesso</h3>
        </header>
        <main>
            <h1 id="mensagem">Solicitação enviada com sucesso!</h1>
            <p style="padding: 0 20px; color: #666;">Agora aguarde o dono da empresa aceitar sua candidatura na página de funcionários.</p>

            <a href="PaginaInicialFreela.php">
                <button class="btn" id="btnVoltar">VOLTAR AO PAINEL</button>
            </a>
            <a href="NovaLoja.php">
                <button class="btn" id="btnNovo">CADASTRAR OUTRA EMPRESA</button>
            </a>
        </main>
    </section>
</body>
</html>
