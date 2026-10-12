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
    <link rel="stylesheet" href="../css/style.css?v=76c42393c7">
    <title>Registrar Experiência</title>
</head>
<body>
    <section id="boxperfil">
        <header>
            <a href="PerfilFreela.php">
                <img src="../imgs/gravity-ui--arrow-left.svg">
            </a>
            <h3>Registrar experiência</h3>
        </header>

        <form id="form" action="../logica/processamento.php" method="POST">
            <h3>Função</h3>
            <input class="inputExp" type="text" name="cargo_experiencia" placeholder="Garçom, barista, etc." required>

            <h3>Local</h3>
            <input class="inputExp" type="text" name="local_experiencia" placeholder="Café das Rosas, Bar Esquina, etc." required>

            <h3>Duração</h3>
            <input class="inputExp" type="text" name="tempo_experiencia" placeholder="2 meses, 1 ano, etc." required>

            <h3>Descrição da experiência</h3>
            <textarea name="descricao_experiencia" placeholder="Descreva rapidamente o que você fazia nessa experiência profissional."></textarea>

            <button class="btn" id="btnExp" type="submit" name="btn_adicionar_experiencia">CONFIRMAR</button>
        </form>
    </section>
</body>
</html>
