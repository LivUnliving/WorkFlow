<?php
session_start();

// Controle de Acesso por Sessão
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'freelancer') {
    header("Location: Login.php");
    exit();
}

require_once "../bd/funcoesBD.php";
$freelancer_id = $_SESSION['usuario_id'];

// Pegamos o código da loja/sala que esse freelancer está vinculado na sessão
$codigo_loja = $_SESSION['usuario_loja']; 

$conexao = conectarBD();

// 1. SELECT CORRIGIDO: Busca turnos usando os nomes reais das colunas da sua tabela
$sqlDisponiveis = "SELECT * FROM turnos 
                   WHERE empresa_id = '$codigo_loja' 
                   AND vagas_necessarias > 0 
                   AND id NOT IN (SELECT turno_id FROM candidaturas WHERE freelancer_id = '$freelancer_id')";
$listaDisponiveis = mysqli_query($conexao, $sqlDisponiveis);

// 2. SELECT CORRIGIDO: Busca turnos agendados juntando as tabelas
$sqlAgendados = "SELECT t.* FROM turnos t 
                 INNER JOIN candidaturas c ON t.id = c.turno_id 
                 WHERE c.freelancer_id = '$freelancer_id'";
$listaAgendados = mysqli_query($conexao, $sqlAgendados);

mysqli_close($conexao);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css"> 
    <title>Painel Freelancer</title>
</head>
<body>
    <div id="grid">
        <aside id="sidenav">
            <a href="PerfilFreela.php">
                <img src="../img/qlementine-icons--user-16.svg">    
            </a>
            <a href="Login.php">
                <img src="../img/material-symbols--settings.svg">    
            </a>
        </aside>
        <main>
            <div class="filtros">
                <button class="btn" id="filtroAtivo">Todos</button>
                <button class="btn" id="filtro">Hoje</button>
                <button class="btn" id="filtro">Esta semana</button>
            </div>
            
            <h1>Turnos disponíveis</h1>
            <section class="turnos">
                <?php 
                if (mysqli_num_rows($listaDisponiveis) > 0) {
                    while ($turno = mysqli_fetch_assoc($listaDisponiveis)) {
                        // Lógica de cores baseada em proximidade de data ajustada para a coluna correta
                        $dataTurno = new DateTime($turno['data_turno']);
                        $hoje = new DateTime();
                        $diferenca = $hoje->diff($dataTurno)->days;

                        // Define a classe de urgência do CSS baseada nos dias restantes
                        if ($diferenca <= 1) {
                            $classeUrgencia = "urgente"; // Vermelho
                        } elseif ($diferenca <= 3) {
                            $classeUrgencia = "atencao"; // Amarelo
                        } else {
                            $classeUrgencia = "normal";   // Verde
                        }
                        ?>
                        <div class="turnoContainer">
                            <div class="card <?php echo $classeUrgencia; ?>">
                                <div class="info">
                                    <h4>Função: <?php echo $turno['funcao']; ?></h4>
                                    <p>Horario: <?php echo $turno['horario_inicio']; ?></p>
                                    <p>Data: <?php echo date('d/m/Y', strtotime($turno['data_turno'])); ?></p>
                                    <p>Valor: R$ <?php echo number_format($turno['valor'], 2, ',', '.'); ?></p>
                                </div>
                            </div>
                            <div class="action">
                                <form action="../logica/processamento.php" method="POST" style="display: inline;">
                                    <input type="hidden" name="turno_id" value="<?php echo $turno['id']; ?>">
                                    <button class="btn" id="aceitar" type="submit" name="btn_aceitar_turno">
                                        <img src="../img/mingcute--check-fill.svg">
                                    </button>
                                </form>
                                <button class="btn" id="recusar">
                                    <img src="../img/boxicons--x-filled.svg">
                                </button>
                            </div>        
                        </div>
                        <?php 
                    }
                } else {
                    echo "<p style='color: #aaa; font-style: italic;'>Nenhum turno disponível para sua loja no momento.</p>";
                }
                ?>
            </section>
            
            <h1>Turnos agendados</h1>
            <section class="confirmados">
                <?php 
                if (mysqli_num_rows($listaAgendados) > 0) {
                    while ($agendado = mysqli_fetch_assoc($listaAgendados)) {
                        ?>
                        <div class="cardConfirmado" style="margin-bottom: 10px;">
                            <h4>Função: <?php echo $agendado['func']; ?></h4>
                            <p>Horario: <?php echo $agendado['horario_inicio']; ?></p>
                            <p>Data: <?php echo date('d/m/Y', strtotime($agendado['data_turno'])); ?></p>
                            <p>Status: Confirmado</p>
                        </div>
                        <?php 
                    }
                } else {
                    echo "<p style='color: #aaa; font-style: italic;'>Você não tem turnos agendados.</p>";
                }
                ?>
            </section>
        </main>
    </div>
</body>
</html>