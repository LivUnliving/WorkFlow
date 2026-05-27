<?php
session_start();

// Controle de Acesso por Sessão (Trava de segurança de ADS!)
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'dono') {
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
    <title>Vincular Nova Loja</title>
</head>
<body>
    <section id="boxperfil">
        <header>
            <a href="PaginaInicialDono.php">
                <img src="../img/gravity-ui--arrow-left.svg">
            </a>
            <h3>Nova loja</h3>    
        </header>
        <main>
            <form id="form" action="../logica/processamento.php" method="POST" style="padding: 0; width: 100%;">
                <h1>Código da loja:</h1>
                
                <input class="inputLoja" type="text" name="codigo_loja" required placeholder="Digite o código da sala/loja">
                
                <button class="btn" id="submit" type="submit" name="btn_vincular_loja">CONFIRMAR</button> 
            </form>
        </main>
    </section>
</body>
</html>