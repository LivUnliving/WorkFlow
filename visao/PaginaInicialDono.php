<?php
session_start();

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'dono') {
    header("Location: Login.php");
    exit();
}

require_once "../bd/funcoesBD.php";
$dono_id = $_SESSION['usuario_id'];
$listaTurnos = retornarTurnosDoDono($dono_id);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css">
    <title>Painel da Empresa</title>
</head>
<body>
    <div id="grid">
        <aside>
            <a href="PerfilDono.php" title="Perfil">
                <img src="../imgs/qlementine-icons--user-16.svg">
            </a>
            <a href="Funcionarios.php" title="Funcionários">
                <img src="../imgs/prime--users.svg">
            </a>
            <a href="Sair.php" title="Sair">
                <img src="../imgs/material-symbols--settings.svg">
            </a>
        </aside>

        <main>
            <h2>Turnos criados</h2>
            <section class="turnos">
                <?php
                if (mysqli_num_rows($listaTurnos) > 0) {
                    while ($turno = mysqli_fetch_assoc($listaTurnos)) {
                        $classeUrgencia = classeUrgenciaTurno($turno['data_turno']);
                        ?>
                        <div class="turnoContainer" style="margin-bottom: 10px;">
                            <div class="card <?php echo $classeUrgencia; ?>">
                                <div class="info">
                                    <h4>Função: <?php echo htmlspecialchars($turno['funcao']); ?></h4>
                                    <p>Empresa: <?php echo htmlspecialchars($turno['empresa_nome']); ?></p>
                                    <p>Data: <?php echo date('d/m/Y', strtotime($turno['data_turno'])); ?></p>
                                    <p>Horário: <?php echo substr($turno['horario_inicio'], 0, 5); ?></p>
                                    <p>Vagas restantes: <?php echo (int)$turno['vagas_disponiveis']; ?> de <?php echo (int)$turno['vagas_necessarias']; ?></p>
                                    <p style="font-size: 13px; color: #777; margin-top: 5px;"><?php echo htmlspecialchars($turno['descricao']); ?></p>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    echo "<p style='color: #777; font-style: italic;'>Nenhum turno cadastrado ainda.</p>";
                }
                ?>
            </section>

            <a href="CriarTurno.php">
                <button class="btn" id="novoTurno">+</button>
            </a>
        </main>
    </div>
</body>
</html>
