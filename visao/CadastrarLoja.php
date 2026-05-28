<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'dono') {
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
            <a href="PaginaInicialDono.php">
                <img src="../imgs/gravity-ui--arrow-left.svg">
            </a>
            <h3>Nova empresa</h3>    
        </header> 
        <form id="form" action="../logica/processamento.php" method="POST">
            <h3>Nome da empresa</h3>
            <input class="inputExp" type="text" name="nome_loja" placeholder="Bar Esquina" required>
            
            <h3>Telefone</h3>
            <input class="inputExp" type="text" name="telefone_loja" placeholder="Inclua o DDD" required>
            
            <h3>Localização</h3>
            <input class="inputExp" type="text" name="local_loja" placeholder="Rua das Rosas, 282" required>
            
            <h3>CNPJ</h3>
            <input class="inputExp" type="text" name="cnpj_loja" placeholder="11.222.333/0001-81" required>
            
            <button class="btn" id="btnExp" type="submit" name="btn_adicionar_loja">CONFIRMAR</button>
        </form>   
    </section>
</body>
</html>