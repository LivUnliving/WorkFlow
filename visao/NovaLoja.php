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
    <title>Cadastrar Nova Empresa</title>
</head>
<body>
    <section id="boxperfil">
        <header>
            <a href="PerfilFreela.php">
                <img src="../imgs/gravity-ui--arrow-left.svg">
            </a>
            <h3>Nova empresa</h3>
        </header>
        <main>
            <form id="form" action="../logica/processamento.php" method="POST" style="padding: 0; width: 100%;">
                <h1>Código da empresa:</h1>
                <p style="font-size: 14px; color: #666; margin-top: -10px;">Digite o código informado pelo dono da empresa. A solicitação ficará pendente até ele aceitar.</p>

                <input class="inputLoja" type="text" name="codigo_loja" required placeholder="Ex: LOJA01">

                <button class="btn" id="submit" type="submit" name="btn_vincular_loja">CONFIRMAR</button>
            </form>
        </main>
    </section>
</body>
</html>
