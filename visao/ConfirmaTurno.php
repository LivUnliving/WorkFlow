<?php
session_start();

// Trava de Segurança de ADS: Só donos logados acessam o sucesso do turno
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
    <title>Turno Registrado</title>
</head>
<body>
    <section id="boxperfil">
        <header>
            <a href="PaginaInicialDono.php">
                <img src="../img/gravity-ui--arrow-left.svg">
            </a>
            <h3>Sucesso</h3>
        </header>
        <main>
            <h1 id="mensagem">Turno registrado com sucesso!</h1>
            
            <a href="PaginaInicialDono.php">
                <button id="btnVoltar" type="button" class="btn">VOLTAR AO PAINEL</button>
            </a>
            
            <a href="CriarTurno.php">
                <button id="btnNovo" type="button" class="btn">CRIAR OUTRO TURNO</button>
            </a>        
        </main>
    </section>
</body>
</html>