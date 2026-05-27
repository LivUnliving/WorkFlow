<?php
session_start();

// Controle de Acesso por Sessão: Garante que apenas usuários logados vejam a tela
if (!isset($_SESSION['usuario_id'])) {
    header("Location: Login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css">
    <title>Sucesso - Vínculo de Loja</title>
</head>
<body>
    <section id="boxperfil">
        <header>
            <a href="NovaLoja.php">
                <img src="../img/gravity-ui--arrow-left.svg">
            </a>
            <h3>Sucesso</h3>
        </header>
        <main>
            <h1 id="mensagem">Solicitação enviada!<br>Aguarde a verificação do<br>sistema sobre sua loja!</h1>
            
            <a href="PaginaInicialDono.php">
                <button class="btn" id="btnNovo">VOLTAR AO PAINEL</button>
            </a>
            <a href="NovaLoja.php">
                <button class="btn" id="btnVoltar">VINCULAR OUTRA LOJA</button>
            </a>    
        </main>    
    </section>
</body>
</html>