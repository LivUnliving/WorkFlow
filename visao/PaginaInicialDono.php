<?php
session_start();

// Trava de Segurança: Se não for dono, chuta de volta para o Login
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'dono') {
    header("Location: Login.php");
    exit();
}

require_once "../bd/funcoesBD.php";
$dono_id = $_SESSION['usuario_id'];
$codigo_loja = $_SESSION['usuario_loja'];

$conexao = conectarBD();

$sqlHoje = "SELECT * FROM turnos WHERE empresa_id = '$dono_id' AND data_turno = CURDATE() ORDER BY horario_inicio ASC";
$listaHoje = mysqli_query($conexao, $sqlHoje);


$sqlProximos = "SELECT * FROM turnos WHERE empresa_id = '$dono_id' AND data_turno > CURDATE() ORDER BY data_turno ASC, horario_inicio ASC";
$listaProximos = mysqli_query($conexao, $sqlProximos);

mysqli_close($conexao);
?>

<!DOCTYPE html>
<html lang="PT-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css">    
    <title>Painel da Empresa</title>
</head>
<body>
    <div id="grid">
        <aside>
            <a href="./PerfilDono.php">
                <img src="../imgs/qlementine-icons--user-16.svg">
            </a>
            <a href="./Funcionarios.php">
                <img src="../imgs/prime--users.svg">
            </a>
            <a href="./Login.php">
                <img src="../imgs/material-symbols--settings.svg">
            </a>
        </aside>
        
        <main>
            <h2>Turnos do dia</h2>
            <section class="turnos">
                <?php 
                if (mysqli_num_rows($listaHoje) > 0) {
                    while ($turnoHoje = mysqli_fetch_assoc($listaHoje)) {
                        ?>
                        <div class="turnoContainer">
                            <div class="card normal"> <div class="info">
                                    <h4>Função: <?php echo $turnoHoje['funcao']; ?></h4>
                                    <p>Horário: <?php echo $turnoHoje['horario_inicio']; ?></p>
                                    <p>Vagas Restantes: <?php echo $turnoHoje['vagas_disponiveis']; ?> de <?php echo $turnoHoje['vagas_necessarias']; ?></p>
                                    <p style="font-size: 13px; color: #777; margin-top: 5px;"><?php echo $turnoHoje['descricao']; ?></p>
                                </div>
                            </div>    
                        </div>
                        <?php 
                    }
                } else {
                    echo "<p style='color: #aaa; font-style: italic;'>Nenhum turno agendado para o dia de hoje.</p>";
                }
                ?>
            </section>

            <h2>Próximos turnos</h2>
            <section class="turnos">
                <?php 
                if (mysqli_num_rows($listaProximos) > 0) {
                    while ($turnoProximo = mysqli_fetch_assoc($listaProximos)) {
                        ?>
                        <div class="turnoContainer" style="margin-bottom: 10px;">
                            <div class="card normal">
                                <div class="info">
                                    <h4>Função: <?php echo $turnoProximo['funcao']; ?></h4>
                                    <p>Data: <?php echo date('d/m/Y', strtotime($turnoProximo['data_turno'])); ?></p>
                                    <p>Horário: <?php echo $turnoProximo['horario_inicio']; ?></p>
                                    <p>Vagas Restantes: <?php echo $turnoProximo['vagas_disponiveis']; ?> de <?php echo $turnoProximo['vagas_necessarias']; ?></p>
                                </div>
                            </div>
                        </div>
                        <?php 
                    }
                } else {
                    echo "<p style='color: #aaa; font-style: italic;'>Nenhum turno futuro cadastrado.</p>";
                }
                ?>
            </section>

            <a href="./CriarTurno.php">
                <button class="btn" id="novoTurno" >+</button>
            </a>
        </main>
    </div>
</body>
</html>