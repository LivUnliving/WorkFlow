<?php
session_start();

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'dono') {
    header("Location: Login.php");
    exit();
}

require_once "../bd/funcoesBD.php";
$dono_id = $_SESSION['usuario_id'];
$listaConfirmados = retornarFuncionariosConfirmadosDoDono($dono_id);
$listaPendentes = retornarCandidaturasPendentesDoDono($dono_id);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css?v=76c42393c7">
    <title>Funcionários</title>
</head>
<body>
    <section id="boxperfil">
        <header>
            <a href="../app/">
                <img src="../imgs/gravity-ui--arrow-left.svg">
            </a>
            <h3>Funcionários</h3>
        </header>
        <main>
            <h2>Freelancers confirmados</h2>
            <section class="listaEquipe">
                <?php
                if (mysqli_num_rows($listaConfirmados) > 0) {
                    while ($funcionario = mysqli_fetch_assoc($listaConfirmados)) {
                        ?>
                        <div class="funcionarioContainer">
                            <div class="funcionario">
                                <div class="dadosFuncionario">
                                    <img id="fotoFuncionario" src="../imgs/qlementine-icons--user-16.svg">
                                    <div>
                                        <strong><?php echo htmlspecialchars($funcionario['nome']); ?></strong>
                                        <p style="margin: 0; font-size: 13px; color: #666;"><?php echo htmlspecialchars($funcionario['email']); ?></p>
                                        <p style="margin: 0; font-size: 13px; color: #666;">Empresa: <?php echo htmlspecialchars($funcionario['empresa_nome']); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    echo "<p style='color: #777; font-style: italic;'>Nenhum freelancer confirmado ainda.</p>";
                }
                ?>
            </section>

            <h2>Solicitações pendentes</h2>
            <section class="listaEquipe">
                <?php
                if (mysqli_num_rows($listaPendentes) > 0) {
                    while ($pendente = mysqli_fetch_assoc($listaPendentes)) {
                        ?>
                        <div class="funcionarioContainer">
                            <div class="funcionario">
                                <div class="dadosFuncionario">
                                    <img id="fotoFuncionario" src="../imgs/qlementine-icons--user-16.svg">
                                    <div>
                                        <strong><?php echo htmlspecialchars($pendente['nome']); ?></strong>
                                        <p style="margin: 0; font-size: 13px; color: #666;"><?php echo htmlspecialchars($pendente['email']); ?></p>
                                        <p style="margin: 0; font-size: 13px; color: #666;">Código enviado: <?php echo htmlspecialchars($pendente['codigo_loja']); ?></p>
                                        <p style="margin: 0; font-size: 13px; color: #666;">Empresa: <?php echo htmlspecialchars($pendente['empresa_nome']); ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="actionFuncionario">
                                <form action="../logica/processamento.php" method="POST">
                                    <input type="hidden" name="vinculo_id" value="<?php echo (int)$pendente['vinculo_id']; ?>">
                                    <button id="aceitarFuncionario" type="submit" name="btn_aceitar_funcionario">
                                        <img src="../imgs/mingcute--check-fill.svg">
                                    </button>
                                </form>
                                <form action="../logica/processamento.php" method="POST">
                                    <input type="hidden" name="vinculo_id" value="<?php echo (int)$pendente['vinculo_id']; ?>">
                                    <button id="recusarFuncionario" type="submit" name="btn_recusar_funcionario">
                                        <img src="../imgs/boxicons--x-filled.svg">
                                    </button>
                                </form>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    echo "<p style='color: #777; font-style: italic;'>Nenhuma solicitação pendente.</p>";
                }
                ?>
            </section>
        </main>
    </section>
</body>
</html>
