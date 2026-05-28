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
    <title>Sucesso!</title>
</head>
<body>
    <section id="boxperfil">
        <header>
            <a href="RegistrarExp.php">
                <img src="../imgs/gravity-ui--arrow-left.svg">
            </a>
            <h3>Sucesso</h3>
        </header>
        <main>
            <h1 id="mensagem">Experiência registrada com sucesso!</h1>

            <a href="PaginaInicialFreela.php">
                <button id="btnVoltar" type="button" class="btn">VOLTAR AO PAINEL</button>
            </a>

            <a href="RegistrarExp.php">
                <button id="btnNovo" type="button" class="btn">REGISTRAR OUTRA EXPERIÊNCIA</button>
            </a>
        </main>
    </section>
</body>
</html>
