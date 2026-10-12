<?php
session_start();

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'dono') {
    header("Location: Login.php");
    exit();
}

require_once "../bd/funcoesBD.php";
$dono_id = $_SESSION['usuario_id'];
$dadosDono = buscarUsuarioPorId($dono_id);
$listaLojas = retornarEmpresasDoDono($dono_id);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css?v=76c42393c7">
    <title>Meu Perfil - Empresa</title>
</head>
<body>
    <section id="boxperfil">
        <header>
            <a href="PaginaInicialDono.php">
                <img src="../imgs/gravity-ui--arrow-left.svg">
            </a>
            <h3>Meu perfil</h3>
        </header>

        <main>
            <img id="foto" src="../imgs/qlementine-icons--user-16.svg" alt="Foto de Perfil" style="display: block; margin: 0 auto; width: 80px;">
            <h2 style="text-align: center; margin-top: 10px; color: #333;"><?php echo htmlspecialchars($dadosDono['nome']); ?></h2>

            <div class="infoPerfil" style="margin-top: 20px;">
                <h3>E-mail da Conta</h3>
                <p><?php echo htmlspecialchars($dadosDono['email']); ?></p>
            </div>

            <div class="infoPerfil">
                <h3>Telefone</h3>
                <p><?php echo !empty($dadosDono['telefone']) ? htmlspecialchars($dadosDono['telefone']) : "Não cadastrado"; ?></p>
            </div>

            <div class="infoPerfil">
                <h3>Cidade</h3>
                <p><?php echo !empty($dadosDono['cidade']) ? htmlspecialchars($dadosDono['cidade']) : "Não cadastrada"; ?></p>
            </div>

            <div class="infoPerfil">
                <h3>CNPJ</h3>
                <p><?php echo !empty($dadosDono['cnpj']) ? htmlspecialchars($dadosDono['cnpj']) : "Não cadastrado"; ?></p>
            </div>

            <div class="empresas">
                <div class="linha">
                    <h3>Meus Estabelecimentos:</h3>
                    <a href="CadastrarLoja.php">
                        <button class="btn" id="perfilbtn">Nova Empresa +</button>
                    </a>
                </div>
                <?php
                if (mysqli_num_rows($listaLojas) > 0) {
                    while ($loja = mysqli_fetch_assoc($listaLojas)) {
                        ?>
                        <div style="background: #f9f9f9; padding: 10px; border-radius: 5px; margin-bottom: 8px; border-left: 4px solid #28a745;">
                            <strong style="color: #333;"><?php echo htmlspecialchars($loja['nome']); ?></strong>
                            <p style="font-size: 13px; color: #666; margin: 3px 0 0 0;">
                                Código: <strong><?php echo htmlspecialchars($loja['codigo_loja']); ?></strong><br>
                                <?php echo htmlspecialchars($loja['localizacao']); ?> | <?php echo htmlspecialchars($loja['telefone']); ?><br>
                                CNPJ: <?php echo htmlspecialchars($loja['cnpj']); ?>
                            </p>
                        </div>
                        <?php
                    }
                } else {
                    echo "<p style='color: #777; font-style: italic; font-size: 14px;'>Nenhuma empresa registrada ainda.</p>";
                }
                ?>
            </div>
        </main>
    </section>
</body>
</html>
