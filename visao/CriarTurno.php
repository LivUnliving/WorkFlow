<?php
session_start();

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'dono') {
    header("Location: Login.php");
    exit();
}

require_once "../bd/funcoesBD.php";
$dono_id = $_SESSION['usuario_id'];
$listaEmpresas = retornarEmpresasDoDono($dono_id);
$listaFuncionarios = retornarFuncionariosConfirmadosDoDono($dono_id);
$temEmpresas = mysqli_num_rows($listaEmpresas) > 0;
$temFuncionarios = mysqli_num_rows($listaFuncionarios) > 0;
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css">
    <title>Criar Novo Turno</title>
</head>
<body>
    <section id="boxperfil">
        <header>
            <a href="PaginaInicialDono.php">
                <img src="../imgs/gravity-ui--arrow-left.svg">
            </a>
            <h3>Criar novo turno</h3>
        </header>
        <form id="formTurno" action="../logica/processamento.php" method="POST">
            <div class="campo">
                <label>Estabelecimento</label>
                <select name="empresa_id" required style="height: 45px; border-radius: 8px; padding: 10px;">
                    <?php
                    if ($temEmpresas) {
                        while ($empresa = mysqli_fetch_assoc($listaEmpresas)) {
                            echo "<option value='" . (int)$empresa['id'] . "'>" . htmlspecialchars($empresa['nome']) . " - " . htmlspecialchars($empresa['codigo_loja']) . "</option>";
                        }
                    } else {
                        echo "<option value=''>Cadastre uma empresa primeiro</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="campo">
                <label>Data</label>
                <input type="date" name="data" required>
            </div>

            <div class="campo">
                <label>Horário</label>
                <div class="horario">
                    <input type="time" name="inicio" required>
                    <span>até</span>
                    <input type="time" name="fim">
                </div>
            </div>

            <div class="campo">
                <label>Cargo/Função</label>
                <input type="text" name="cargo" placeholder="Garçom, Caixa, Cozinheiro..." required>
            </div>

            <div class="campo">
                <label>Valor</label>
                <input type="number" step="0.01" name="valor" placeholder="Ex: 120.00">
            </div>

            <div class="campo">
                <label>Detalhes do turno</label>
                <textarea name="descricao" placeholder="Descreva informações importantes" required></textarea>
            </div>

            <div class="campo">
                <label>Funcionários que receberão o chamado</label>
                <div class="listaFuncionarios">
                    <?php
                    if ($temFuncionarios) {
                        while ($funcionario = mysqli_fetch_assoc($listaFuncionarios)) {
                            ?>
                            <label class="funcionarioCheck">
                                <input type="checkbox" name="funcionarios[]" value="<?php echo (int)$funcionario['freelancer_id']; ?>">
                                <?php echo htmlspecialchars($funcionario['nome']); ?>
                                <small style="color:#666;">(<?php echo htmlspecialchars($funcionario['empresa_nome']); ?>)</small>
                            </label>
                            <?php
                        }
                    } else {
                        echo "<p style='color: #777; font-style: italic;'>Nenhum freelancer aprovado ainda. Aprove solicitações na página de funcionários antes de criar um turno.</p>";
                    }
                    ?>
                </div>
            </div>

            <button class="btn" type="submit" name="btn_criar_turno" id="submit" href="PaginaInicialDono.php"<?php echo (!$temEmpresas || !$temFuncionarios) ? 'disabled' : ''; ?>>Publicar turno</button>
        </form>
    </section>
</body>
</html>
