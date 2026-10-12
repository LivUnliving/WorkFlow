<?php
session_start();

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'freelancer') {
    header("Location: Login.php");
    exit();
}

require_once "../bd/funcoesBD.php";
$naoLidas = contarNotificacoesNaoLidas((int)$_SESSION['usuario_id']);
$freelancer_id = $_SESSION['usuario_id'];
$listaPendentes = retornarTurnosPendentesDoFreelancer($freelancer_id);
$listaRespondidos = retornarTurnosRespondidosDoFreelancer($freelancer_id);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css?v=76c42393c7">
    <title>Painel Freelancer</title>
</head>
<body>
    <div id="grid">
        <aside id="sidenav">
            <a href="PerfilFreela.php" title="Perfil">
                <img src="../imgs/qlementine-icons--user-16.svg">
            </a>
            <a class="nav-icone" href="Notificacoes.php" title="Notificações" aria-label="Notificações">
                <img src="../imgs/notificacoes.svg" alt="">
                <?php if ($naoLidas > 0): ?><span class="nav-contador"><?php echo $naoLidas; ?></span><?php endif; ?>
            </a>
            <a class="nav-icone" href="Configuracoes.php" title="Configurações" aria-label="Configurações">
                <img src="../imgs/material-symbols--settings.svg" alt="">
            </a>
            <a class="nav-icone" href="Sair.php" title="Sair" aria-label="Sair">
                <img src="../imgs/logout.svg" alt="">
            </a>
        </aside>
        <main>
            <h1>Turnos pendentes</h1>
            <section class="turnos">
                <?php
                if (mysqli_num_rows($listaPendentes) > 0) {
                    while ($turno = mysqli_fetch_assoc($listaPendentes)) {
                        $classeUrgencia = classeUrgenciaTurno($turno['data_turno']);
                        ?>
                        <div class="turnoContainer">
                            <div class="card <?php echo $classeUrgencia; ?>">
                                <div class="info">
                                    <h4>Função: <?php echo htmlspecialchars($turno['funcao']); ?></h4>
                                    <p>Empresa: <?php echo htmlspecialchars($turno['empresa_nome']); ?></p>
                                    <p>Horário: <?php echo substr($turno['horario_inicio'], 0, 5); ?></p>
                                    <p>Data: <?php echo date('d/m/Y', strtotime($turno['data_turno'])); ?></p>
                                    <p>Valor: R$ <?php echo number_format((float)$turno['valor'], 2, ',', '.'); ?></p>
                                    <p style="font-size: 13px; color: #777;"><?php echo htmlspecialchars($turno['descricao']); ?></p>
                                </div>
                            </div>
                            <div class="action">
                                <form action="../logica/processamento.php" method="POST">
                                    <input type="hidden" name="turno_id" value="<?php echo (int)$turno['id']; ?>">
                                    <button class="btn" id="aceitar" type="submit" name="btn_aceitar_turno">
                                        <img src="../imgs/mingcute--check-fill.svg">
                                    </button>
                                </form>
                                <form action="../logica/processamento.php" method="POST">
                                    <input type="hidden" name="turno_id" value="<?php echo (int)$turno['id']; ?>">
                                    <button class="btn" id="recusar" type="submit" name="btn_recusar_turno">
                                        <img src="../imgs/boxicons--x-filled.svg">
                                    </button>
                                </form>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    echo "<p style='color: #777; font-style: italic;'>Nenhum turno pendente no momento.</p>";
                }
                ?>
            </section>

            <h1>Turnos respondidos e realizados</h1>
            <section class="confirmados">
                <?php
                if (mysqli_num_rows($listaRespondidos) > 0) {
                    while ($turno = mysqli_fetch_assoc($listaRespondidos)) {
                        ?>
                        <div class="cardConfirmado" style="margin-bottom: 10px;">
                            <h4>Função: <?php echo htmlspecialchars($turno['funcao']); ?></h4>
                            <p>Empresa: <?php echo htmlspecialchars($turno['empresa_nome']); ?></p>
                            <p>Horário: <?php echo substr($turno['horario_inicio'], 0, 5); ?></p>
                            <p>Data: <?php echo date('d/m/Y', strtotime($turno['data_turno'])); ?></p>
                            <p>Status: <?php echo htmlspecialchars($turno['status']); ?></p>
                        </div>
                        <?php
                    }
                } else {
                    echo "<p style='color: #777; font-style: italic;'>Você ainda não respondeu nenhum turno.</p>";
                }
                ?>
            </section>
        </main>
    </div>
</body>
</html>
