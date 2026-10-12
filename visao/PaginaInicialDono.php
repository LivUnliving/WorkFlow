<?php
session_start();

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'dono') {
    header("Location: Login.php");
    exit();
}

require_once "../bd/funcoesBD.php";
require_once "../config/seguranca.php";
$naoLidas = contarNotificacoesNaoLidas((int)$_SESSION['usuario_id']);

$dono_id = $_SESSION['usuario_id'];
$listaTurnos = retornarTurnosDoDono($dono_id);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css?v=76c42393c7">
    <title>Painel da Empresa</title>
</head>
<body>
    <div id="grid">
        <aside>
            <a href="PerfilDono.php" title="Perfil">
                <img src="../imgs/qlementine-icons--user-16.svg" alt="Perfil">
            </a>

            <a href="Funcionarios.php" title="Funcionários">
                <img src="../imgs/prime--users.svg" alt="Funcionários">
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
            <h2>Turnos criados</h2>

            <section class="turnos">
                <?php if (mysqli_num_rows($listaTurnos) > 0): ?>

                    <?php while ($turno = mysqli_fetch_assoc($listaTurnos)): ?>
                        <?php
                            $classeUrgencia = classeUrgenciaTurno($turno['data_turno']);
                        ?>

                        <div class="turnoContainer" style="margin-bottom: 10px;">
                            <div class="card <?php echo htmlspecialchars($classeUrgencia); ?>">
                                <div class="info">
                                    <h4>
                                        Função: <?php echo htmlspecialchars($turno['funcao']); ?>
                                    </h4>

                                    <p>
                                        Empresa: <?php echo htmlspecialchars($turno['empresa_nome']); ?>
                                    </p>

                                    <p>
                                        Data: <?php echo date('d/m/Y', strtotime($turno['data_turno'])); ?>
                                    </p>

                                    <p>
                                        Horário: <?php echo substr($turno['horario_inicio'], 0, 5); ?>
                                    </p>

                                    <p>
                                        Vagas restantes:
                                        <?php echo (int)$turno['vagas_disponiveis']; ?>
                                        de
                                        <?php echo (int)$turno['vagas_necessarias']; ?>
                                    </p>

                                    <p style="font-size: 13px; color: #777; margin-top: 5px;">
                                        <?php echo htmlspecialchars($turno['descricao']); ?>
                                    </p>
                                </div>
                                <div class="acoes-turno">
                                    <a class="botao-acao" href="EditarTurno.php?id=<?php echo (int)$turno['id']; ?>">Editar</a>
                                    <form action="../logica/processamento.php" method="post" onsubmit="return confirm('Excluir este turno e seus convites?');">
                                        <input type="hidden" name="turno_id" value="<?php echo (int)$turno['id']; ?>">
                                        <input type="hidden" name="csrf_token" value="<?php echo esc(tokenCsrf()); ?>">
                                        <button class="botao-acao botao-perigo" type="submit" name="btn_excluir_turno">Excluir</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>

                <?php else: ?>

                    <p style="color: #777; font-style: italic;">
                        Nenhum turno cadastrado ainda.
                    </p>

                <?php endif; ?>
            </section>

            <a href="CriarTurno.php">
                <button class="btn" id="novoTurno" type="button">+</button>
            </a>
        </main>
    </div>
</body>
</html>